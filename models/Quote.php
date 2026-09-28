<?php

namespace app\models;

use yii\db\ActiveRecord;
use Yii;

class Quote extends ActiveRecord
{
    public static function tableName()
    {
        return 'Quote';
    }

    public function rules()
    {
        return [
            [['date_quote', 'hour_quote'], 'safe'],
            [['total_amount', 'id_lead'], 'integer'],
            [['id_status'], 'integer'],
            [['comments'], 'string', 'max' => 5000],
            [['id_lead', 'date_quote', 'total_amount', 'id_status'], 'required'],
            [['id_status'], 'exist', 'skipOnError' => true, 'targetClass' => Status::class, 'targetAttribute' => ['id_status' => 'id_status']],
            [['total_amount'], 'integer', 'min' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_quote' => 'ID Cotización',
            'date_quote' => 'Fecha',
            'hour_quote' => 'Hora',
            'id_status' => 'Estado',
            'comments' => 'Observaciones',
            'total_amount' => 'Monto Total',
            'id_lead' => 'Lead',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->total_amount = (int) $this->total_amount;

        // 🔥 En INSERT: inicializar pendiente = total_amount
        if ($insert) {
            $parsed = $this->parseComments();
            if (!isset($parsed['pendiente']) || $parsed['pendiente'] === null) {
                $parsed['pendiente'] = $this->total_amount;
                $this->writeComments($parsed);
            }
        }

        // 🔥 Si el pendiente es 0 → cambiar a "Completado"
        $pendiente = $this->getRealPending();
        if ($pendiente <= 0 && $this->total_amount > 0) {
            $statusCompletado = Status::find()
                ->where(['or',
                    ['status' => 'completado'],
                    ['status' => 'Completado'],
                ])
                ->one();
            
            if ($statusCompletado && $this->id_status != $statusCompletado->id_status) {
                $this->id_status = $statusCompletado->id_status;
            }
        }

        return true;
    }

    // ============================================
    // RELACIONES
    // ============================================
    
    public function getLead()
    {
        return $this->hasOne(Lead::class, ['id_lead' => 'id_lead']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id_user' => 'id_user'])->via('lead');
    }

    public function getStatus()
    {
        return $this->hasOne(Status::class, ['id_status' => 'id_status']);
    }

    // ============================================
    // 🔥 PARSEO DEL CAMPO comments
    // ============================================

    /**
     * Parsea comments como JSON. Estructura:
     * {"notas": "...", "pendiente": 80000}
     */
    private function parseComments()
    {
        if (empty($this->comments)) {
            return ['notas' => '', 'pendiente' => null];
        }

        $decoded = json_decode($this->comments, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return [
                'notas' => $decoded['notas'] ?? '',
                'pendiente' => isset($decoded['pendiente']) ? (int)$decoded['pendiente'] : null,
            ];
        }

        // Formato antiguo: solo texto plano
        return ['notas' => $this->comments, 'pendiente' => null];
    }

    /**
     * Escribe la estructura JSON en comments.
     */
    private function writeComments($data)
    {
        $notas = $data['notas'] ?? '';
        $pendiente = $data['pendiente'];

        if (empty($notas) && $pendiente === null) {
            $this->comments = null;
            return;
        }

        $this->comments = json_encode([
            'notas' => $notas,
            'pendiente' => $pendiente,
        ], JSON_UNESCAPED_UNICODE);
    }

    // ============================================
    // 🔥 HELPERS DE PAGO
    // ============================================

    /**
     * 🔥 Saldo pendiente actual.
     */
    public function getRealPending()
    {
        $parsed = $this->parseComments();
        
        // Si no hay pendiente definido, usar total_amount
        if ($parsed['pendiente'] === null) {
            return (int) $this->total_amount;
        }
        
        return max(0, (int) $parsed['pendiente']);
    }

    /**
     * 🔥 Total pagado = total - pendiente.
     */
    public function getTotalPaid()
    {
        return max(0, (int) $this->total_amount - $this->getRealPending());
    }

    /**
     * 🔥 Porcentaje pagado.
     */
    public function getPaymentPercentage()
    {
        if ($this->total_amount <= 0) return 0;
        return round(($this->getTotalPaid() / $this->total_amount) * 100, 1);
    }

    /**
     * 🔥 ¿Está totalmente pagada?
     */
    public function isFullyPaid()
    {
        return $this->getRealPending() <= 0 && $this->total_amount > 0;
    }

    /**
     * 🔥 Registra un pago: reduce el pendiente.
     * @return int Monto aplicado
     */
    public function registerPayment($monto)
    {
        $monto = (int) $monto;
        if ($monto <= 0) return 0;

        $pendienteActual = $this->getRealPending();

        // No permitir pagar más del pendiente
        if ($monto > $pendienteActual) {
            $monto = $pendienteActual;
        }

        $nuevoPendiente = $pendienteActual - $monto;
        if ($nuevoPendiente < 0) $nuevoPendiente = 0;

        // Guardar en comments
        $parsed = $this->parseComments();
        $parsed['pendiente'] = $nuevoPendiente;
        $this->writeComments($parsed);

        return $monto;
    }

    /**
     * 🔥 Notas (texto plano).
     */
    public function getNotes()
    {
        $parsed = $this->parseComments();
        return $parsed['notas'];
    }

    public function setNotes($value)
    {
        $parsed = $this->parseComments();
        $parsed['notas'] = $value;
        $this->writeComments($parsed);
    }

    /**
     * 🔥 Métodos de compatibilidad.
     */
    public function getPayments() { return []; }
    public function getPaymentsTotal() { return 0; }
    public function getPaymentsCount() { return 0; }

    // ============================================
    // MÉTODOS DE ESTADO
    // ============================================
    
    public function getStatusName()
    {
        return $this->status ? $this->status->status : 'Sin Estado';
    }

    public function getStatusBadgeClass()
    {
        if (!$this->status) return 'secondary';
        
        $badges = [
            'pendiente'  => 'warning',
            'aprobada'   => 'success',
            'rechazada'  => 'danger',
            'pagada'     => 'info',
            'cancelada'  => 'secondary',
            'completado' => 'success',
        ];
        
        $statusName = strtolower($this->status->status ?? '');
        return $badges[$statusName] ?? 'secondary';
    }

    public function getAgentName()
    {
        if ($this->lead && $this->lead->user) {
            return trim($this->lead->user->name . ' ' . $this->lead->user->lastname1);
        }
        return 'Sin asignar';
    }

    public function getFormattedTotal()
    {
        return '$' . number_format($this->total_amount, 0, '.', ',');
    }

    public function getFormattedPending()
    {
        return '$' . number_format($this->getRealPending(), 0, '.', ',');
    }

    public static function getStatusOptions()
    {
        return Status::find()
            ->select(['status', 'id_status'])
            ->indexBy('id_status')
            ->column();
    }
}