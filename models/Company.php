<?php

namespace app\models;

use yii\db\ActiveRecord;

class Company extends ActiveRecord
{
    public static function tableName()
    {
        return 'Company';
    }

    public static function primaryKey()
    {
        return ['id_company'];
    }

    public function rules()
    {
        return [
            [['id_company'], 'integer'],
            [['name'], 'string', 'max' => 10],
            [['description'], 'string', 'max' => 45],
            [['domain'], 'string', 'max' => 20],
            [['id_status'], 'integer'], // 🔥 Cambiado de 'status' a 'id_status'
            [['type'], 'string', 'max' => 10],
            [['name'], 'required', 'message' => 'El nombre de la empresa es obligatorio'],
            [['id_status'], 'exist', 'skipOnError' => true, 'targetClass' => Status::class, 'targetAttribute' => ['id_status' => 'id_status']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_company' => 'ID Empresa',
            'name' => 'Nombre de Empresa',
            'description' => 'Descripción',
            'domain' => 'Dominio',
            'id_status' => 'Estado', // 🔥 Cambiado de 'status' a 'id_status'
            'type' => 'Tipo',
        ];
    }

    // ============================================
    // RELACIONES
    // ============================================
    
    public function getUsers()
    {
        return $this->hasMany(User::class, ['id_company' => 'id_company']);
    }

    public function getLeads()
    {
        return $this->hasMany(Lead::class, ['id_company' => 'id_company']);
    }

    // 🔥 RELACIÓN CON STATUS
    public function getStatus()
    {
        return $this->hasOne(Status::class, ['id_status' => 'id_status']);
    }

    // ============================================
    // MÉTODOS DE ESTADO
    // ============================================
    
    /**
     * Obtener el nombre del estado
     */
    public function getStatusName()
    {
        if ($this->status && isset($this->status->status)) {
            return $this->status->status;
        }
        return 'Sin Estado';
    }

    /**
     * Obtener el badge del estado
     */
    public function getStatusBadge()
    {
        if (!$this->status) {
            return '<span class="badge bg-secondary">Sin Estado</span>';
        }
        
        $badges = [
            'Activo' => 'success',
            'Inactivo' => 'danger',
            'Suspendido' => 'warning',
            'activo' => 'success',
            'inactivo' => 'danger',
            'suspendido' => 'warning',
        ];
        
        $statusName = $this->status->status ?? 'Sin Estado';
        $class = $badges[$statusName] ?? 'secondary';
        
        return '<span class="badge bg-' . $class . '">' . $statusName . '</span>';
    }

    /**
     * Obtener opciones de estado para dropdown (desde tabla Status)
     */
    public static function getStatusOptions()
    {
        return Status::find()
            ->select(['status', 'id_status'])
            ->indexBy('id_status')
            ->column();
    }

    /**
     * Verificar si la empresa está activa
     */
    public function isActive()
    {
        if (!$this->status) {
            return false;
        }
        return strtolower($this->status->status) === 'activo';
    }

    /**
     * Obtener el nombre de la empresa
     */
    public function getDisplayName()
    {
        return $this->name ?? 'Empresa #' . $this->id_company;
    }

    /**
     * Obtener lista para dropdown
     */
    public static function getDropdownList()
    {
        $companies = self::find()
            ->orderBy(['name' => SORT_ASC])
            ->all();
        
        $list = [];
        foreach ($companies as $company) {
            $list[$company->id_company] = $company->getDisplayName();
        }
        return $list;
    }

    /**
     * Obtener empresas activas
     */
    public static function getActiveCompanies()
    {
        $statusActivo = Status::find()->where(['status' => 'Activo'])->one();
        if (!$statusActivo) {
            return self::find()->all();
        }
        
        return self::find()
            ->where(['id_status' => $statusActivo->id_status])
            ->orderBy(['name' => SORT_ASC])
            ->all();
    }

    /**
     * Obtener empresas para dropdown con estado
     */
    public static function getDropdownListWithStatus()
    {
        $companies = self::find()
            ->orderBy(['name' => SORT_ASC])
            ->all();
        
        $list = [];
        foreach ($companies as $company) {
            $status = $company->isActive() ? '✓' : '✗';
            $list[$company->id_company] = $company->getDisplayName() . " [$status]";
        }
        return $list;
    }

    // ============================================
    // MÉTODOS DE DEPURACIÓN
    // ============================================
    
    public function debug()
    {
        return [
            'id_company' => $this->id_company,
            'name' => $this->name,
            'id_status' => $this->id_status,
            'status_name' => $this->getStatusName(),
            'isActive' => $this->isActive(),
        ];
    }
}