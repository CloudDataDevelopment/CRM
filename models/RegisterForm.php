<?php

namespace app\models;

use Yii;
use yii\base\Model;

class RegisterForm extends Model
{
    public $username;
    public $password;
    public $confirm_password;
    public $email;
    public $phone;
    public $name;
    public $lastname1;
    public $lastname2;
    public $id_role;
    public $id_company;

    // ============================================
    // 🔥 LISTAS DE ROLES SEPARADAS
    // ============================================
    
    /**
     * Lista completa de roles (SOLO para Super Admin)
     */
    public static function getFullRolesList()
    {
        return [
            1 => 'Super Administrador',
            2 => 'Administrador',
            3 => 'Agente',
            4 => 'Cliente',
        ];
    }

    /**
     * Lista de roles limitada (SOLO para Admin normal)
     * 🔥 ELIMINA COMPLETAMENTE Super Admin
     */
    public static function getLimitedRolesList()
    {
        return [
            2 => 'Administrador',
            3 => 'Agente',
            4 => 'Cliente',
        ];
    }

    /**
     * Lista de roles según el usuario logueado
     */
    public static function getRolesList()
    {
        $user = Yii::$app->user->identity;
        
        // Si es Super Admin, mostrar todos los roles
        if ($user && $user->isSuperAdmin()) {
            return self::getFullRolesList();
        }
        
        // Si es Admin normal, mostrar roles limitados
        return self::getLimitedRolesList();
    }

    // ============================================
    // REGLAS DE VALIDACIÓN
    // ============================================
    
    public function rules()
    {
        $user = Yii::$app->user->identity;
        $isSuperAdmin = $user && $user->isSuperAdmin();
        
        $rules = [
            [['username', 'password', 'confirm_password', 'email', 'name', 'id_role'], 'required'],
            [['username'], 'string', 'min' => 3, 'max' => 20],
            [['password', 'confirm_password'], 'string', 'min' => 4, 'max' => 20],
            ['confirm_password', 'compare', 'compareAttribute' => 'password', 'message' => 'Las contraseñas no coinciden'],
            [['email'], 'email'],
            [['email'], 'string', 'max' => 100],
            [['phone'], 'string', 'max' => 15],
            [['name'], 'string', 'max' => 50],
            [['lastname1', 'lastname2'], 'string', 'max' => 50],
            [['id_role', 'id_company'], 'integer'],
            ['username', 'validateUsername'],
            ['email', 'validateEmail'],
            ['id_role', 'validateRolePermission'],
        ];
        
        // Si es Super Admin, id_company es requerido
        if ($isSuperAdmin) {
            $rules[] = [['id_company'], 'required', 'message' => 'Debes seleccionar una empresa'];
            $rules[] = ['id_company', 'exist', 'targetClass' => Company::class, 'targetAttribute' => 'id_company', 'message' => 'La empresa seleccionada no existe'];
        }
        
        return $rules;
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Usuario',
            'password' => 'Contraseña',
            'confirm_password' => 'Confirmar Contraseña',
            'email' => 'Correo Electrónico',
            'phone' => 'Teléfono',
            'name' => 'Nombre',
            'lastname1' => 'Apellido Paterno',
            'lastname2' => 'Apellido Materno',
            'id_role' => 'Rol',
            'id_company' => 'Empresa',
        ];
    }

    public function validateUsername($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $exists = User::find()->where(['username' => $this->username])->exists();
            if ($exists) {
                $this->addError($attribute, 'Este usuario ya está registrado.');
            }
        }
    }

    public function validateEmail($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $exists = User::find()->where(['email' => $this->email])->exists();
            if ($exists) {
                $this->addError($attribute, 'Este correo electrónico ya está registrado.');
            }
        }
    }

    /**
     * 🔥 VALIDACIÓN DE SEGURIDAD EN BACKEND
     * Admin normal NO puede enviar id_role = 1
     */
    public function validateRolePermission($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = Yii::$app->user->identity;
            
            // Si el usuario logueado NO es Super Admin
            if (!$user || !$user->isSuperAdmin()) {
                // Y está intentando asignar Super Admin (id_role = 1)
                if ($this->id_role == 1) {
                    $this->addError($attribute, 'No tienes permisos para asignar este rol.');
                }
            }
        }
    }

    // ============================================
    // OBTENER LISTA DE EMPRESAS
    // ============================================
    
    /**
     * Obtener lista de empresas usando el modelo Company
     */
    public static function getCompaniesList()
    {
        return Company::getDropdownList();
    }

    /**
     * Verificar si existen empresas registradas
     */
    public static function hasCompanies()
    {
        return Company::find()->count() > 0;
    }

    // ============================================
    // REGISTRO
    // ============================================
    
    public function register()
    {
        if (!$this->validate()) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            // Si es Super Admin, usar la empresa seleccionada
            // Si no, usar la empresa del usuario logueado
            $userLogged = Yii::$app->user->identity;
            
            if ($userLogged && $userLogged->isSuperAdmin()) {
                // Super Admin: usar la empresa seleccionada en el formulario
                $companyId = $this->id_company;
            } else {
                // Otros usuarios: usar su propia empresa
                $companyId = $userLogged->id_company ?? 0;
            }

            // Crear User
            $user = new User();
            $user->username = $this->username;
            $user->email = $this->email;
            $user->phone = $this->phone;
            $user->name = $this->name;
            $user->lastname1 = $this->lastname1;
            $user->lastname2 = $this->lastname2;
            $user->id_company = $companyId;
            $user->setPassword($this->password);

            if (!$user->save()) {
                throw new \Exception('Error al guardar usuario: ' . json_encode($user->errors));
            }

            // Crear Authentication
            $auth = new Authentication();
            $auth->id_user = $user->id_user;
            $auth->id_role = $this->id_role;
            $auth->id_view = 1;
            $auth->phone = $this->phone;
            $auth->password = $user->password;

            // 'status' es una relación (foránea), no se puede asignar directo.
            // Se busca el id_status correspondiente al estado "Activo".
            $statusActivo = Status::find()->where(['status' => 'Activo'])->one();
            if (!$statusActivo) {
                throw new \Exception('No se encontró el estado "Activo" en la tabla Status.');
            }
            $auth->id_status = $statusActivo->id_status;

            $auth->id_company = $companyId;

            if (!$auth->save()) {
                throw new \Exception('Error al guardar autenticación: ' . json_encode($auth->errors));
            }

            $transaction->commit();
            return true;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Registration error: ' . $e->getMessage());
            $this->addError('username', 'Error en el registro: ' . $e->getMessage());
            return false;
        }
    }
}