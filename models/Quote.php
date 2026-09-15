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
            [['down_payment'], 'string', 'max' => 20],
            [['comments'], 'string', 'max' => 50],
            [['id_lead', 'date_quote', 'hour_quote', 'total_amount', 'id_status'], 'required'],
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

        if ($insert) {
            // Calcular pago pendiente
            $this->pending_payment = $this->total_amount - (int)$this->down_payment;
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
        return $this->hasOne(User::class, ['id_user' => 'id_user'])
            ->via('lead');
    }

    // 🔥 RELACIÓN CON STATUS
    public function getStatus()
    {
        return $this->hasOne(Status::class, ['id_status' => 'id_status']);
    }

    // ============================================
    // MÉTODOS DE ESTADO
    // ============================================
    
    public function getStatusName()
    {
        if ($this->status) {
            return $this->status->status;
        }
        return 'Sin Estado';
    }

    public function getStatusBadgeClass()
    {
        if (!$this->status) {
            return 'secondary';
        }
        
        $badges = [
            'pendiente' => 'warning',
            'aprobada' => 'success',
            'rechazada' => 'danger',
            'pagada' => 'info',
            'cancelada' => 'secondary',
        ];
        
        $statusName = strtolower($this->status->status ?? '');
        return $badges[$statusName] ?? 'secondary';
    }

    public static function getStatusOptions()
    {
        return Status::find()
            ->select(['status', 'id_status'])
            ->indexBy('id_status')
            ->column();
    }

    public function isPending()
    {
        if (!$this->status) {
            return false;
        }
        return strtolower($this->status->status ?? '') === 'pendiente';
    }

    public function isApproved()
    {
        if (!$this->status) {
            return false;
        }
        return strtolower($this->status->status ?? '') === 'aprobada';
    }

    public function isPaid()
    {
        if (!$this->status) {
            return false;
        }
        return strtolower($this->status->status ?? '') === 'pagada';
    }

    public function getFormattedTotal()
    {
        return '$' . number_format($this->total_amount, 0, ',', '.');
    }

    public function getFormattedPending()
    {
        return '$' . number_format($this->pending_payment, 0, ',', '.');
    }
}