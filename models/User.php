<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName()
    {
        return 'User';
    }

    public static function primaryKey()
    {
        return ['id_user'];
    }

    public function rules()
    {
        return [
            [['id_user', 'id_company'], 'integer'],
            [['username', 'password', 'name'], 'required'],
            [['username'], 'string', 'min' => 3, 'max' => 20],
            [['password'], 'string', 'min' => 4, 'max' => 255],
            [['email'], 'email'],
            [['email'], 'string', 'max' => 100],
            [['phone'], 'string', 'max' => 15],
            [['name', 'lastname1', 'lastname2'], 'string', 'max' => 50],
            ['username', 'unique', 'message' => 'Este usuario ya está registrado'],
            ['email', 'unique', 'message' => 'Este correo ya está registrado'],
            ['id_company', 'exist', 'targetClass' => Company::class, 'targetAttribute' => 'id_company', 'message' => 'La empresa seleccionada no es válida'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_user' => 'ID',
            'username' => 'Usuario',
            'password' => 'Contraseña',
            'email' => 'Correo Electrónico',
            'phone' => 'Teléfono',
            'name' => 'Nombre',
            'lastname1' => 'Apellido Paterno',
            'lastname2' => 'Apellido Materno',
            'id_company' => 'Empresa',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert && empty($this->id_user)) {
            $maxId = self::find()->select('MAX(id_user)')->scalar();
            $this->id_user = ($maxId === null) ? 1 : $maxId + 1;
        }

        return true;
    }

    // ============================================
    // IDENTITY INTERFACE
    // ============================================
    
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    public function getId()
    {
        return $this->id_user;
    }

    public function getAuthKey()
    {
        return '';
    }

    public function validateAuthKey($authKey)
    {
        return true;
    }

    // ============================================
    // MÉTODOS DE CONTRASEÑA
    // ============================================
    
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }
    
    public function setPassword($password)
    {
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }

    // ============================================
    // RELACIONES
    // ============================================
    
    public function getAuthentication()
    {
        return $this->hasOne(Authentication::class, ['id_user' => 'id_user']);
    }

    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id_company' => 'id_company']);
    }

    public function getRole()
    {
        return $this->hasOne(Role::class, ['id_role' => 'id_role'])
            ->via('authentication');
    }

    // ============================================
    // 🔥 MÉTODOS DE ROLES - AGREGADOS AQUÍ
    // ============================================
    
    public function isSuperAdmin()
    {
        $auth = $this->authentication;
        if (!$auth) {
            return false;
        }
        return $auth->id_role == 1;
    }

    public function isAdmin()
    {
        $auth = $this->authentication;
        if (!$auth) {
            return false;
        }
        return $auth->id_role == 1 || $auth->id_role == 2;
    }

    public function isAgent()
    {
        $auth = $this->authentication;
        if (!$auth) {
            return false;
        }
        return $auth->id_role == 3;
    }

    public function isClient()
    {
        $auth = $this->authentication;
        if (!$auth) {
            return false;
        }
        return $auth->id_role == 4;
    }

    public function isActive()
    {
        $auth = $this->authentication;
        if (!$auth) {
            return false;
        }
        return $auth->isActive();
    }

    public function getRoleName()
    {
        $auth = $this->authentication;
        if ($auth) {
            $role = $auth->role;
            if ($role && isset($role->role_type)) {
                return $role->role_type;
            }
        }
        return 'Sin rol';
    }
}