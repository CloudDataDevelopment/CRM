<?php

namespace app\models;

use yii\db\ActiveRecord;
use Yii;

class SalesTracking extends ActiveRecord
{
    public static function tableName()
    {
        return 'Sales_tracking';
    }

    public function rules()
    {
        return [
            [['id_user', 'id_lead', 'id_status'], 'required'],
            [['id_user', 'id_lead', 'id_status'], 'integer'],
            [['hour', 'date_s', 'date_f'], 'safe'],
            [['comments'], 'string', 'max' => 50],
            [['id_status'], 'exist', 'skipOnError' => true, 'targetClass' => Status::class, 'targetAttribute' => ['id_status' => 'id_status']],
            [['id_lead'], 'exist', 'skipOnError' => true, 'targetClass' => Lead::class, 'targetAttribute' => ['id_lead' => 'id_lead']],
            [['id_user'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['id_user' => 'id_user']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_sales_tracking' => 'ID Seguimiento',
            'id_user' => 'Usuario',
            'id_lead' => 'Lead',
            'hour' => 'Hora',
            'date_s' => 'Fecha de Seguimiento',
            'date_f' => 'Próximo Seguimiento',
            'comments' => 'Comentarios',
            'id_status' => 'Estado',
        ];
    }

    // ============================================
    // 🔥 BEFORE SAVE - ASIGNAR VALORES POR DEFECTO
    // ============================================
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert) {
            // Asignar usuario actual
            if (empty($this->id_user)) {
                $this->id_user = Yii::$app->user->id ?: 1;
            }
            
            // Asignar fecha y hora actual
            if (empty($this->date_s)) {
                $this->date_s = date('Y-m-d H:i:s');
            }
            if (empty($this->hour)) {
                $this->hour = date('H:i:s');
            }
            
            // Asignar estado por defecto si no viene
            if (empty($this->id_status)) {
                $statusDefault = Status::find()->where(['status' => 'Pendiente'])->one();
                if ($statusDefault) {
                    $this->id_status = $statusDefault->id_status;
                }
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
        return $this->hasOne(User::class, ['id_user' => 'id_user']);
    }

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
        
        $statusName = strtolower($this->status->status);
        
        $badges = [
            'pendiente' => 'warning',
            'programado' => 'info',
            'en progreso' => 'primary',
            'en_progreso' => 'primary',
            'completado' => 'success',
            'cancelado' => 'danger',
        ];
        
        return $badges[$statusName] ?? 'secondary';
    }

    /**
     * Obtener el nombre de la empresa a través del lead
     */
    public function getCompanyId()
    {
        if ($this->lead) {
            return $this->lead->id_company;
        }
        return null;
    }

    /**
     * Obtener el nombre de la empresa
     */
    public function getCompanyName()
    {
        if ($this->lead && $this->lead->company) {
            return $this->lead->company->name;
        }
        return 'Sin empresa';
    }

    // ============================================
    // MÉTODOS ESTÁTICOS
    // ============================================
    
    public static function getStatusOptions()
    {
        $statusNames = ['Pendiente', 'Programado', 'En Progreso', 'Completado', 'Cancelado'];
        
        return Status::find()
            ->where(['IN', 'status', $statusNames])
            ->select(['status', 'id_status'])
            ->indexBy('id_status')
            ->column();
    }

    public static function find()
    {
        return parent::find()->with(['lead', 'status', 'user']);
    }
}