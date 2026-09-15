<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\User;
use app\models\Authentication;
use app\models\Status;
use app\components\ErrorManager;

class UserManagementController extends Controller
{
    public $layout = 'main';

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'toggle-status' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $user = Yii::$app->user->identity;
        $empresaId = Yii::$app->session->get('empresa_id');
        
        if (!$user || !$user->isAdmin()) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para acceder a esta sección.');
            return $this->redirect(['dashboard/index']);
        }

        if ($user->isSuperAdmin() && empty($empresaId)) {
            Yii::$app->session->setFlash('warning', 'Por favor, selecciona una empresa para continuar.');
            return $this->redirect(['empresa/index']);
        }

        $query = User::find()
            ->joinWith('authentication')
            ->joinWith('role')
            ->orderBy(['User.id_user' => SORT_ASC]);

        // 🔥 FILTRO POR EMPRESA PARA SUPER ADMIN
        if ($user->isSuperAdmin() && !empty($empresaId)) {
            $query->andWhere(['User.id_company' => $empresaId]);
        } elseif (!$user->isSuperAdmin()) {
            $query->andWhere(['User.id_company' => $user->id_company]);
            $query->andWhere(['<>', 'Authentication.id_role', 1]);
        }

        $users = $query->all();

        $totalUsers = count($users);
        $totalAdmins = 0;
        $totalAgents = 0;
        $totalClients = 0;
        $totalActive = 0;

        foreach ($users as $u) {
            if ($u->isAdmin()) {
                $totalAdmins++;
            } elseif ($u->isAgent()) {
                $totalAgents++;
            } elseif ($u->isClient()) {
                $totalClients++;
            }
            
            if ($u->isActive()) {
                $totalActive++;
            }
        }

        return $this->render('index', [
            'users' => $users,
            'totalUsers' => $totalUsers,
            'totalAdmins' => $totalAdmins,
            'totalAgents' => $totalAgents,
            'totalClients' => $totalClients,
            'totalActive' => $totalActive,
            'isSuperAdmin' => $user->isSuperAdmin(),
            'isAdmin' => $user->isAdmin(),
        ]);
    }

    public function actionView($id)
    {
        $user = Yii::$app->user->identity;
        $empresaId = Yii::$app->session->get('empresa_id');
        
        if (!$user || !$user->isAdmin()) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para acceder a esta sección.');
            return $this->redirect(['dashboard/index']);
        }

        $model = User::find()
            ->joinWith('authentication')
            ->joinWith('role')
            ->where(['User.id_user' => $id])
            ->one();

        if (!$model) {
            throw new NotFoundHttpException('El usuario solicitado no existe.');
        }

        // 🔥 VERIFICAR PERMISOS
        if ($user->isSuperAdmin() && !empty($empresaId) && $model->id_company != $empresaId) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para ver este usuario.');
            return $this->redirect(['index']);
        }

        if (!$user->isSuperAdmin() && $model->id_company != $user->id_company) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para ver este usuario.');
            return $this->redirect(['index']);
        }

        if (!$user->isSuperAdmin() && $model->isSuperAdmin()) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para ver este usuario.');
            return $this->redirect(['index']);
        }

        return $this->render('view', [
            'model' => $model,
            'isSuperAdmin' => $user->isSuperAdmin(),
        ]);
    }

    /**
     * 🔥 CAMBIAR ESTADO DEL USUARIO
     */
    public function actionToggleStatus()
    {
        $request = Yii::$app->request;
        $userId = $request->post('user_id');

        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');
            
            if (!$user || !$user->isAdmin()) {
                throw new \Exception('No tienes permiso para realizar esta acción.');
            }

            $model = User::findOne($userId);
            if (!$model) {
                throw new NotFoundHttpException('Usuario no encontrado.');
            }

            // 🔥 VERIFICAR PERMISOS
            if ($user->isSuperAdmin() && !empty($empresaId) && $model->id_company != $empresaId) {
                throw new \Exception('No tienes permiso para modificar este usuario.');
            }

            if (!$user->isSuperAdmin() && $model->id_company != $user->id_company) {
                throw new \Exception('No tienes permiso para modificar este usuario.');
            }

            if ($model->id_user == $user->id_user) {
                throw new \Exception('No puedes bloquear tu propia cuenta.');
            }

            if (!$user->isSuperAdmin() && $model->isSuperAdmin()) {
                throw new \Exception('No puedes bloquear a un Super Administrador.');
            }

            $auth = Authentication::findOne(['id_user' => $userId]);
            
            if (!$auth) {
                throw new \Exception('Autenticación no encontrada para este usuario.');
            }

            $statusActivo = Status::find()->where(['status' => 'Activo'])->one();
            $statusInactivo = Status::find()->where(['status' => 'Inactivo'])->one();

            if (!$statusActivo || !$statusInactivo) {
                throw new \Exception('Estados "Activo" o "Inactivo" no encontrados en la base de datos.');
            }

            $currentStatusId = $auth->id_status;
            $newStatusId = null;
            $statusText = '';

            if ($currentStatusId == $statusInactivo->id_status) {
                $newStatusId = $statusActivo->id_status;
                $statusText = 'activado';
            } else {
                $newStatusId = $statusInactivo->id_status;
                $statusText = 'bloqueado';
            }

            $auth->id_status = $newStatusId;
            
            if ($auth->save()) {
                Yii::$app->session->setFlash('success', "Usuario {$statusText} exitosamente.");
            } else {
                throw new \Exception('Error al cambiar el estado. Errores: ' . print_r($auth->errors, true));
            }

        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al cambiar estado');
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    public function actionDelete($id)
    {
        $user = Yii::$app->user->identity;
        $empresaId = Yii::$app->session->get('empresa_id');
        
        if (!$user || !$user->isSuperAdmin()) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para eliminar usuarios.');
            return $this->redirect(['index']);
        }

        try {
            $model = User::findOne($id);
            if (!$model) {
                throw new NotFoundHttpException('Usuario no encontrado.');
            }

            if ($model->id_user == $user->id_user) {
                throw new \Exception('No puedes eliminarte a ti mismo.');
            }

            // 🔥 VERIFICAR EMPRESA PARA SUPER ADMIN
            if (!empty($empresaId) && $model->id_company != $empresaId) {
                throw new \Exception('No tienes permiso para eliminar este usuario.');
            }

            Authentication::deleteAll(['id_user' => $id]);
            
            if ($model->delete()) {
                Yii::$app->session->setFlash('success', 'Usuario eliminado exitosamente.');
            } else {
                throw new \Exception('Error al eliminar el usuario.');
            }

        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al eliminar usuario');
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    public function actionChangeRole()
    {
        $request = Yii::$app->request;
        $userId = $request->post('user_id');
        $roleId = $request->post('role_id');

        $user = Yii::$app->user->identity;
        $empresaId = Yii::$app->session->get('empresa_id');
        
        if (!$user || !$user->isSuperAdmin()) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para cambiar roles.');
            return $this->redirect(['index']);
        }

        try {
            $model = User::findOne($userId);
            if (!$model) {
                throw new NotFoundHttpException('Usuario no encontrado.');
            }

            if ($model->id_user == $user->id_user) {
                throw new \Exception('No puedes cambiar tu propio rol.');
            }

            // 🔥 VERIFICAR EMPRESA PARA SUPER ADMIN
            if (!empty($empresaId) && $model->id_company != $empresaId) {
                throw new \Exception('No tienes permiso para cambiar el rol de este usuario.');
            }

            $auth = Authentication::findOne(['id_user' => $userId]);
            if (!$auth) {
                throw new \Exception('Autenticación no encontrada.');
            }

            $auth->id_role = $roleId;
            
            if ($auth->save()) {
                Yii::$app->session->setFlash('success', 'Rol actualizado exitosamente.');
            } else {
                throw new \Exception('Error al actualizar el rol.');
            }

        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al cambiar rol');
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(['index']);
    }
}