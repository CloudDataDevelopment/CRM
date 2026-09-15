<?php

namespace app\models;

use yii\db\ActiveRecord;
use Yii;

class Lead extends ActiveRecord
{
    public static function tableName()
    {
        return 'Lead';
    }

    public function rules()
    {
        return [
            [['name', 'lastname', 'phone'], 'required'],
            [['name', 'lastname'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 20],
            [['comments'], 'string', 'max' =>255],
            [['id_status'], 'integer'],
            [['created_at'], 'safe'],
            [['id_user', 'id_company'], 'integer'],
            [['id_company'], 'integer', 'min' => 1, 'message' => 'La empresa debe ser válida.'],
            [['id_status'], 'default', 'value' => 19],
            [['id_status'], 'exist', 'skipOnError' => true, 'targetClass' => Status::class, 'targetAttribute' => ['id_status' => 'id_status']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_lead' => 'ID',
            'name' => 'Nombre',
            'lastname' => 'Apellido',
            'phone' => 'Teléfono',
            'comments' => 'Observaciones',
            'id_status' => 'Estado',
            'created_at' => 'Fecha de Registro',
            'id_user' => 'Agente Asignado',
            'id_company' => 'Empresa',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert) {
            $this->created_at = date('Y-m-d');
            
            if (empty($this->id_company) || $this->id_company == 0) {
                $user = Yii::$app->user->identity;
                if ($user && !empty($user->id_company)) {
                    $this->id_company = $user->id_company;
                } else {
                    $company = Company::find()->one();
                    $this->id_company = $company ? $company->id_company : 1;
                }
            }
            
            $user = Yii::$app->user->identity;
            
            if ($user) {
                if ($user->isAgent()) {
                    $this->id_user = $user->id_user;
                } else {
                    $this->id_user = null;
                }
            } else {
                $this->id_user = null;
            }
            
            $this->id_status = $this->id_status ?: 19;
        }
        
        return parent::beforeSave($insert);
    }

    // ============================================
    // RELACIONES
    // ============================================
    
    public function getUser()
    {
        return $this->hasOne(User::class, ['id_user' => 'id_user']);
    }

    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id_company' => 'id_company']);
    }

    public function getStatus()
    {
        return $this->hasOne(Status::class, ['id_status' => 'id_status']);
    }

    public function getSalesTrackings()
    {
        return $this->hasMany(SalesTracking::className(), ['id_lead' => 'id_lead'])
            ->orderBy(['date_s' => SORT_DESC, 'hour' => SORT_DESC]);
    }

    public function getLastTracking()
    {
        return $this->hasOne(SalesTracking::className(), ['id_lead' => 'id_lead'])
            ->orderBy(['date_s' => SORT_DESC, 'hour' => SORT_DESC]);
    }

    public function getQuotes()
    {
        return $this->hasMany(Quote::className(), ['id_lead' => 'id_lead'])
            ->orderBy(['date_quote' => SORT_DESC, 'hour_quote' => SORT_DESC]);
    }

    public function getLastQuote()
    {
        return $this->hasOne(Quote::className(), ['id_lead' => 'id_lead'])
            ->orderBy(['date_quote' => SORT_DESC, 'hour_quote' => SORT_DESC]);
    }

    // ============================================
    // MÉTODOS DE ESTADO
    // ============================================
    
    public function getStatusName()
    {
        if ($this->status && isset($this->status->status)) {
            return trim($this->status->status);
        }
        return 'Sin Estado';
    }

    public function getStatusBadgeClass()
    {
        if (!$this->status || !isset($this->status->status)) {
            return 'secondary';
        }
        
        $statusName = trim($this->status->status);
        
        $badges = [
            'Nuevo' => 'primary',
            'Contactado' => 'info',
            'Procesando' => 'warning',
            'Calificado' => 'success',
            'Cliente' => 'success',
            'Cancelado' => 'secondary',
            'Perdido' => 'danger',
        ];
        
        return $badges[$statusName] ?? 'secondary';
    }

    // 🔥 MÉTODO AGREGADO: Obtiene el icono del estado
    public function getStatusIcon()
    {
        if (!$this->status || !isset($this->status->status)) {
            return 'fa-circle';
        }
        
        $statusName = trim($this->status->status);
        
        $icons = [
            'Nuevo' => 'fa-plus-circle',
            'Contactado' => 'fa-phone',
            'Procesando' => 'fa-spinner',
            'Calificado' => 'fa-star',
            'Cliente' => 'fa-user-check',
            'Cancelado' => 'fa-ban',
            'Perdido' => 'fa-times-circle',
        ];
        
        return $icons[$statusName] ?? 'fa-circle';
    }

    public function isDeleted()
    {
        return $this->id_status == 10;
    }

    public function isActive()
    {
        if (!$this->status) {
            return true;
        }
        $statusName = trim($this->status->status);
        return $statusName !== 'Cancelado' && $statusName !== 'Perdido';
    }

    public function getAgentName()
    {
        if ($this->user) {
            return $this->user->name . ' ' . $this->user->lastname1;
        }
        return 'Sin asignar';
    }

    // ============================================
    // MÉTODOS ESTÁTICOS PARA CONSULTAS
    // ============================================
    
    /**
     * Obtiene todos los leads activos (excluye Cancelado y Perdido)
     * @return \yii\db\ActiveQuery
     */
    public static function findActive()
    {
        $statusCancelado = Status::find()->where(['status' => 'Cancelado'])->one();
        $statusPerdido = Status::find()->where(['status' => 'Perdido'])->one();
        
        $excludeIds = [];
        if ($statusCancelado) $excludeIds[] = $statusCancelado->id_status;
        if ($statusPerdido) $excludeIds[] = $statusPerdido->id_status;
        
        if (empty($excludeIds)) {
            return static::find()->orderBy(['created_at' => SORT_DESC]);
        }
        
        return static::find()
            ->where(['not in', 'id_status', $excludeIds])
            ->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * Obtiene todos los leads cancelados
     * @return \yii\db\ActiveQuery
     */
    public static function findDeleted()
    {
        $statusCancelado = Status::find()->where(['status' => 'Cancelado'])->one();
        if ($statusCancelado) {
            return static::find()
                ->where(['id_status' => $statusCancelado->id_status])
                ->orderBy(['created_at' => SORT_DESC]);
        }
        return static::find()->where(['0' => '1']);
    }

    /**
     * Obtiene leads sin asignar
     * @return \yii\db\ActiveQuery
     */
    public static function findUnassigned()
    {
        return self::findActive()
            ->where(['id_user' => null])
            ->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * Obtiene leads asignados a un agente específico
     * @param int $userId
     * @return \yii\db\ActiveQuery
     */
    public static function findAssignedToAgent($userId)
    {
        return self::findActive()
            ->where(['id_user' => $userId])
            ->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * Obtiene todos los leads asignados
     * @return \yii\db\ActiveQuery
     */
    public static function findAssigned()
    {
        return self::findActive()
            ->where(['not', ['id_user' => null]])
            ->orderBy(['created_at' => SORT_DESC]);
    }

    public static function find()
    {
        $statusCancelado = Status::find()->where(['status' => 'Cancelado'])->one();
        if ($statusCancelado) {
            return parent::find()
                ->where(['<>', 'id_status', $statusCancelado->id_status]);
        }
        return parent::find();
    }

    public static function findWithDeleted()
    {
        return parent::find();
    }

    // ============================================
    // MÉTODOS PARA ASIGNACIÓN DE AGENTES
    // ============================================
    
    public function hasAgent()
    {
        return !empty($this->id_user);
    }

    public function hasValidAgent()
    {
        if (!$this->user) {
            return false;
        }
        return $this->user->isAgent();
    }

    public function assignToAgent($agentId)
    {
        $agent = User::findOne($agentId);
        if (!$agent) {
            Yii::error('assignToAgent: Agente no encontrado ID: ' . $agentId, 'lead-assign');
            return false;
        }
        
        if (!$agent->isAgent()) {
            Yii::error('assignToAgent: Usuario no es agente ID: ' . $agentId, 'lead-assign');
            return false;
        }

        $this->id_user = $agentId;
        
        if ($this->save(false)) {
            Yii::info('assignToAgent: Asignación exitosa - Lead: ' . $this->id_lead . ' Agente: ' . $agentId, 'lead-assign');
            return true;
        } else {
            $errors = $this->getErrors();
            Yii::error('assignToAgent: Error al guardar - Lead: ' . $this->id_lead . ' Errores: ' . print_r($errors, true), 'lead-assign');
            return false;
        }
    }

    public function unassignAgent()
    {
        $this->id_user = null;
        return $this->save();
    }

    // ============================================
    // MÉTODO DE DEPURACIÓN
    // ============================================
    
    public function debugLead()
    {
        return [
            'id_lead' => $this->id_lead,
            'name' => $this->name,
            'lastname' => $this->lastname,
            'phone' => $this->phone,
            'id_user' => $this->id_user,
            'id_company' => $this->id_company,
            'id_status' => $this->id_status,
            'status_name' => $this->getStatusName(),
            'created_at' => $this->created_at,
            'errors' => $this->getErrors(),
            'has_user' => $this->user ? true : false,
            'user_is_agent' => $this->user ? $this->user->isAgent() : false,
        ];
    }
}