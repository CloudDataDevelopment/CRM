<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use app\models\User;
use app\models\Authentication;
use app\models\Status;
use app\models\Role;
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

    // ============================================
    // 🔥 OBTENER EL NOMBRE REAL DE LA COLUMNA DEL ROL
    // ============================================
    private function getRoleNameColumn()
    {
        try {
            $schema = Yii::$app->db->getTableSchema('Role', true);
            if (!$schema) {
                return 'role_type';
            }

            // Buscar en orden de preferencia
            $candidates = ['name', 'role_type', 'type', 'description', 'role_name'];
            foreach ($candidates as $col) {
                if (isset($schema->columns[$col])) {
                    return $col;
                }
            }

            // Si ninguna existe, devolver la primera columna string que no sea PK
            foreach ($schema->columns as $col) {
                if ($col->type === 'string' && !$col->isPrimaryKey) {
                    return $col->name;
                }
            }

            return 'role_type';
        } catch (\Exception $e) {
            Yii::warning('No se pudo detectar la columna del rol: ' . $e->getMessage(), 'user-management');
            return 'role_type';
        }
    }

    // ============================================
    // 🔥 OBTENER LISTA DE ROLES (con fallback seguro)
    // ============================================
    private function getRolesList()
    {
        try {
            $roleColumn = $this->getRoleNameColumn();

            $roles = Role::find()
                ->select([$roleColumn, 'id_role'])
                ->orderBy(['id_role' => SORT_ASC])
                ->indexBy('id_role')
                ->column();

            if (!empty($roles)) {
                return $roles;
            }
        } catch (\Exception $e) {
            Yii::warning('Error al cargar roles: ' . $e->getMessage(), 'user-management');
        }

        // 🔥 Fallback si la tabla Role está vacía o falla
        return [
            1 => 'Super Administrador',
            2 => 'Administrador',
            3 => 'Agente',
            4 => 'Cliente',
        ];
    }

    // ============================================
    // LISTA DE USUARIOS CON PAGINACIÓN
    // ============================================
    public function actionIndex()
    {
        try {
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

            // 🔥 FILTROS DE BÚSQUEDA
            $search       = Yii::$app->request->get('search', '');
            $roleFilter   = Yii::$app->request->get('role', '');
            $statusFilter = Yii::$app->request->get('status', '');

            // 🔥 QUERY BASE
            $query = User::find()
                ->joinWith(['authentication', 'role'])
                ->orderBy(['User.id_user' => SORT_ASC]);

            // 🔥 FILTRO POR EMPRESA
            if ($user->isSuperAdmin() && !empty($empresaId)) {
                $query->andWhere(['User.id_company' => $empresaId]);
            } elseif (!$user->isSuperAdmin()) {
                $query->andWhere(['User.id_company' => $user->id_company]);
                $query->andWhere(['<>', 'Authentication.id_role', 1]);
            }

            // 🔥 FILTRO POR BÚSQUEDA
            if (!empty($search)) {
                $query->andWhere([
                    'or',
                    ['like', 'User.name', $search],
                    ['like', 'User.lastname1', $search],
                    ['like', 'User.lastname2', $search],
                    ['like', 'User.email', $search],
                    ['like', 'User.phone', $search],
                    ['like', 'User.username', $search],
                ]);
            }

            // 🔥 FILTRO POR ROL
            if (!empty($roleFilter)) {
                $query->andWhere(['Authentication.id_role' => $roleFilter]);
            }

            // 🔥 FILTRO POR ESTADO
            if (!empty($statusFilter)) {
                $statusModel = Status::find()->where(['status' => $statusFilter])->one();
                if ($statusModel) {
                    $query->andWhere(['Authentication.id_status' => $statusModel->id_status]);
                }
            }

            // 🔥 DATAPROVIDER CON PAGINACIÓN
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                    'pageSizeParam' => 'per-page',
                    'pageParam' => 'page',
                ],
                'sort' => [
                    'defaultOrder' => [
                        'id_user' => SORT_ASC,
                    ],
                    'attributes' => [
                        'id_user' => [
                            'asc' => ['User.id_user' => SORT_ASC],
                            'desc' => ['User.id_user' => SORT_DESC],
                        ],
                        'name' => [
                            'asc' => ['User.name' => SORT_ASC],
                            'desc' => ['User.name' => SORT_DESC],
                        ],
                        'lastname1' => [
                            'asc' => ['User.lastname1' => SORT_ASC],
                            'desc' => ['User.lastname1' => SORT_DESC],
                        ],
                        'email' => [
                            'asc' => ['User.email' => SORT_ASC],
                            'desc' => ['User.email' => SORT_DESC],
                        ],
                        'phone' => [
                            'asc' => ['User.phone' => SORT_ASC],
                            'desc' => ['User.phone' => SORT_DESC],
                        ],
                    ],
                ],
            ]);

            $users = $dataProvider->getModels();

            // 🔥 MÉTRICAS
            $metricQuery = User::find()
                ->joinWith(['authentication', 'role']);

            if ($user->isSuperAdmin() && !empty($empresaId)) {
                $metricQuery->andWhere(['User.id_company' => $empresaId]);
            } elseif (!$user->isSuperAdmin()) {
                $metricQuery->andWhere(['User.id_company' => $user->id_company]);
                $metricQuery->andWhere(['<>', 'Authentication.id_role', 1]);
            }

            $allUsers = $metricQuery->all();
            $totalUsers = count($allUsers);

            $totalAdmins   = 0;
            $totalAgents   = 0;
            $totalClients  = 0;
            $totalActive   = 0;

            foreach ($allUsers as $u) {
                try {
                    if (method_exists($u, 'isAdmin') && $u->isAdmin()) {
                        $totalAdmins++;
                    } elseif (method_exists($u, 'isAgent') && $u->isAgent()) {
                        $totalAgents++;
                    } elseif (method_exists($u, 'isClient') && $u->isClient()) {
                        $totalClients++;
                    }

                    if (method_exists($u, 'isActive') && $u->isActive()) {
                        $totalActive++;
                    }
                } catch (\Exception $e) {
                    Yii::warning('Error procesando usuario ID ' . ($u->id_user ?? 'N/A') . ': ' . $e->getMessage(), 'user-management');
                }
            }

            // 🔥 LISTAS PARA FILTROS
            $rolesList = $this->getRolesList();

            $statusList = Status::find()
                ->select(['status', 'id_status'])
                ->where(['in', 'status', ['Activo', 'Inactivo']])
                ->indexBy('id_status')
                ->column();

            return $this->render('index', [
                'dataProvider'  => $dataProvider,
                'users'         => $users,
                'totalUsers'    => $totalUsers,
                'totalAdmins'   => $totalAdmins,
                'totalAgents'   => $totalAgents,
                'totalClients'  => $totalClients,
                'totalActive'   => $totalActive,
                'isSuperAdmin'  => $user->isSuperAdmin(),
                'isAdmin'       => $user->isAdmin(),
                'rolesList'     => $rolesList,
                'statusList'    => $statusList,
                'search'        => $search,
                'role'          => $roleFilter,
                'statusFilter'  => $statusFilter,
            ]);

        } catch (\Exception $e) {
            Yii::error('Error en UserManagementController::actionIndex: ' . $e->getMessage(), 'user-management');
            Yii::error('Stack: ' . $e->getTraceAsString(), 'user-management');

            Yii::$app->session->setFlash('error', 'Error al cargar los usuarios: ' . $e->getMessage());

            return $this->render('index', [
                'dataProvider'  => new ActiveDataProvider(['query' => User::find()->where('1=0')]),
                'users'         => [],
                'totalUsers'    => 0,
                'totalAdmins'   => 0,
                'totalAgents'   => 0,
                'totalClients'  => 0,
                'totalActive'   => 0,
                'isSuperAdmin'  => false,
                'isAdmin'       => false,
                'rolesList'     => [1 => 'Super Administrador', 2 => 'Administrador', 3 => 'Agente', 4 => 'Cliente'],
                'statusList'    => [],
                'search'        => '',
                'role'          => '',
                'statusFilter'  => '',
            ]);
        }
    }

    // ============================================
    // VER USUARIO
    // ============================================
    public function actionView($id)
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            if (!$user || !$user->isAdmin()) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para acceder a esta sección.');
                return $this->redirect(['dashboard/index']);
            }

            $model = User::find()
                ->joinWith(['authentication', 'role'])
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

            if (!$user->isSuperAdmin() && method_exists($model, 'isSuperAdmin') && $model->isSuperAdmin()) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para ver este usuario.');
                return $this->redirect(['index']);
            }

            return $this->render('view', [
                'model' => $model,
                'isSuperAdmin' => $user->isSuperAdmin(),
            ]);

        } catch (NotFoundHttpException $e) {
            Yii::$app->session->setFlash('error', 'Usuario no encontrado.');
            return $this->redirect(['index']);
        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al ver usuario');
            return $this->redirect(['index']);
        }
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

            if ($user->isSuperAdmin() && !empty($empresaId) && $model->id_company != $empresaId) {
                throw new \Exception('No tienes permiso para modificar este usuario.');
            }

            if (!$user->isSuperAdmin() && $model->id_company != $user->id_company) {
                throw new \Exception('No tienes permiso para modificar este usuario.');
            }

            if ($model->id_user == $user->id_user) {
                throw new \Exception('No puedes bloquear tu propia cuenta.');
            }

            if (!$user->isSuperAdmin() && method_exists($model, 'isSuperAdmin') && $model->isSuperAdmin()) {
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

    // ============================================
    // ELIMINAR USUARIO
    // ============================================
    public function actionDelete($id)
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            if (!$user || !$user->isSuperAdmin()) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para eliminar usuarios.');
                return $this->redirect(['index']);
            }

            $model = User::findOne($id);
            if (!$model) {
                throw new NotFoundHttpException('Usuario no encontrado.');
            }

            if ($model->id_user == $user->id_user) {
                throw new \Exception('No puedes eliminarte a ti mismo.');
            }

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

    // ============================================
    // CAMBIAR ROL
    // ============================================
    public function actionChangeRole()
    {
        $request = Yii::$app->request;
        $userId = $request->post('user_id');
        $roleId = $request->post('role_id');

        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            if (!$user || !$user->isSuperAdmin()) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para cambiar roles.');
                return $this->redirect(['index']);
            }

            $model = User::findOne($userId);
            if (!$model) {
                throw new NotFoundHttpException('Usuario no encontrado.');
            }

            if ($model->id_user == $user->id_user) {
                throw new \Exception('No puedes cambiar tu propio rol.');
            }

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