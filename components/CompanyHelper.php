<?php

namespace app\components;

use Yii;
use app\models\Company;
use app\models\Authentication;

class CompanyHelper
{
    public static function getCurrentCompanyId()
    {
        // Verificar si está en sesión
        $empresaId = Yii::$app->session->get('empresa_id');
        if ($empresaId) {
            return $empresaId;
        }
        
        // Si no está en sesión, usar la empresa del usuario
        if (!Yii::$app->user->isGuest) {
            $user = Yii::$app->user->identity;
            $empresaId = $user->id_company ?? 1;
            // Guardar en sesión para futuras consultas
            Yii::$app->session->set('empresa_id', $empresaId);
            return $empresaId;
        }
        
        return 1;
    }
    
    public static function getCurrentCompanyName()
    {
        $nombre = Yii::$app->session->get('empresa_nombre');
        if ($nombre) {
            return $nombre;
        }
        
        $id = self::getCurrentCompanyId();
        $empresa = Company::findOne($id);
        return $empresa ? $empresa->name : 'Sin empresa';
    }
    
    public static function isAdmin()
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }
        
        $auth = Authentication::find()
            ->where(['id_user' => Yii::$app->user->id])
            ->one();
        
        return $auth && $auth->id_role == 1;
    }
    
    public static function getEmpresas()
    {
        if (self::isAdmin()) {
            return Company::find()->all();
        }
        return [];
    }
    
    // Obtener usuarios de la empresa actual
    public static function getUsersByCompany()
    {
        $empresaId = self::getCurrentCompanyId();
        return \app\models\User::find()
            ->where(['id_company' => $empresaId])
            ->select(['id_user'])
            ->column();
    }
}