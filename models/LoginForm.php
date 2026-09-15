<?php

namespace app\models;

use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;

    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Usuario',
            'password' => 'Contraseña',
            'rememberMe' => 'Recordarme',
        ];
    }

    /**
     * 🔥 VALIDAR CONTRASEÑA Y ESTADO DEL USUARIO (usando id_status)
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            
            if (!$user) {
                $this->addError($attribute, 'Usuario no encontrado.');
                return;
            }
            
            // 🔥 VERIFICAR QUE EL USUARIO ESTÉ ACTIVO (usando id_status)
            if (!$user->isActive()) {
                $this->addError($attribute, 'Tu cuenta está bloqueada o desactivada. Contacta al administrador.');
                return;
            }
            
            if (!$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Contraseña incorrecta.');
            }
        }
    }

    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        }
        return false;
    }

    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}