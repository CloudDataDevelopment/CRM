<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Authentication extends ActiveRecord
{
    public static function tableName()
    {
        return 'Authentication';
    }

    public static function primaryKey()
    {
        return ['id_auth'];
    }

    public function rules()
    {
        return [
            [['id_auth', 'id_user', 'id_role', 'id_view', 'id_company', 'id_status'], 'integer'],
            [['id_user', 'id_role'], 'required'],
            [['phone'], 'string', 'max' => 15],
            [['password'], 'string', 'max' => 255],
            [['id_status'], 'exist', 'skipOnError' => true, 'targetClass' => Status::class, 'targetAttribute' => ['id_status' => 'id_status']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_auth' => 'ID Autenticación',
            'id_user' => 'Usuario',
            'id_role' => 'Rol',
            'id_view' => 'Vista',
            'phone' => 'Teléfono',
            'password' => 'Contraseña',
            'id_status' => 'Estado',
            'id_company' => 'Empresa',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert && empty($this->id_auth)) {
            $maxId = self::find()->select('MAX(id_auth)')->scalar();
            $this->id_auth = ($maxId === null) ? 1 : $maxId + 1;
        }

        return true;
    }

    // ============================================
    // RELACIONES
    // ============================================
    
    public function getUser()
    {
        return $this->hasOne(User::class, ['id_user' => 'id_user']);
    }
    
    public function getRole()
    {
        return $this->hasOne(Role::class, ['id_role' => 'id_role']);
    }
    
    public function getView()
    {
        return $this->hasOne(View::class, ['id_view' => 'id_view']);
    }
    
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id_company' => 'id_company']);
    }

    // 🔥 RELACIÓN CON STATUS
    public function getStatus()
    {
        return $this->hasOne(Status::class, ['id_status' => 'id_status']);
    }

    // ============================================
    // MÉTODOS DE ESTADO (usando id_status)
    // ============================================
    
    /**
     * Verificar si el usuario está activo
     * Compara con el estado "Activo" en la tabla Status
     */
    public function isActive()
    {
        if (!$this->status) {
            return false;
        }
        return strtolower($this->status->status) === 'activo';
    }

    /**
     * Obtener el nombre del estado
     */
    public function getStatusName()
    {
        if ($this->status) {
            return $this->status->status;
        }
        return 'Sin Estado';
    }

    public function isSuperAdmin()
    {
        return $this->id_role == 1;
    }

    public function isAdmin()
    {
        return $this->id_role == 1 || $this->id_role == 2;
    }
}