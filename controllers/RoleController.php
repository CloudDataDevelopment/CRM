<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;

class RoleController extends Controller
{
    public function actionCreateRoles()
    {
        $roles = [
            ['id_role' => 1, 'name' => 'Administrador'],
            ['id_role' => 2, 'name' => 'Usuario'],

        ];

        foreach ($roles as $roleData) {
            $role = new \app\models\Role();
            $role->id_role = $roleData['id_role'];
            $role->name = $roleData['name'];
            
            if ($role->save()) {
                echo "Rol '{$role->name}' creado correctamente.<br>";
            } else {
                echo "Error creando rol '{$role->name}': " . json_encode($role->errors) . "<br>";
            }
        }
        
        echo "<br><a href='".Yii::$app->homeUrl."'>Volver al inicio</a>";
    }
}