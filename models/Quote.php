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
            [['pending_payment', 'total_amount', 'id_lead'], 'integer'],
            [['id_status'], 'integer'],
            [['down_payment'], 'integer', 'message' => 'El enganche debe ser un número entero'],
            [['comments'], 'string', 'max' => 5000],
            [['id_lead', 'date_quote', 'total_amount', 'id_status'], 'required'],
            [['id_status'], 'exist', 'skipOnError' => true, 'targetClass' => Status::class, 'targetAttribute' => ['id_status' => 'id_status']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_quote' => 'ID Cotización',
            'date_quote' => 'Fecha',
            'hour_quote' => 'Hora',
            'id_status' => 'Estado',
            'pending_payment' => 'Pago Pendiente',
            'down_payment' => 'Pago Inicial',
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
        $this->down_payment = (int) $this->down_payment;

        $pagado = $this->down_payment;
        $pendiente = $this->total_amount - $pagado;
        $this->pending_payment = $pendiente < 0 ? 0 : $pendiente;

        if ($this->pending_payment <= 0 && $this->total_amount > 0) {
            $statusCompletado = Status::find()->where(['status' => 'completado'])->one();
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
    // HELPERS
    // ============================================

    /**
     * Devuelve las notas en texto plano.
     * Si el valor guardado tiene formato JSON antiguo, extrae solo "notas".
     */
    public function getNotes()
    {
        if (empty($this->comments)) {
            return '';
        }

        $decoded = json_decode($this->comments, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded['notas'] ?? '';
        }

        return $this->comments;
    }

    /**
     * Setter de notas (texto plano).
     */
    public function setNotes($value)
    {
        $this->comments = $value;
    }

    // Compatibilidad con métodos antiguos (devuelven vacío)
    public function getPayments()
    {
        return [];
    }

    public function getPaymentsTotal()
    {
        return 0;
    }

    public function getTotalPaid()
    {
        return (int) $this->down_payment;
    }

    public function getRealPending()
    {
        $pendiente = (int) $this->total_amount - $this->getTotalPaid();
        return $pendiente < 0 ? 0 : $pendiente;
    }

    public function getPaymentPercentage()
    {
        if ($this->total_amount <= 0) return 0;
        return round(($this->getTotalPaid() / $this->total_amount) * 100, 1);
    }

    public function isFullyPaid()
    {
        return $this->getRealPending() <= 0;
    }

    public function getPaymentsCount()
    {
        return 0;
    }

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
        return '$' . number_format($this->pending_payment, 0, '.', ',');
    }

    public static function getStatusOptions()
    {
        return Status::find()
            ->select(['status', 'id_status'])
            ->indexBy('id_status')
            ->column();
    }
}