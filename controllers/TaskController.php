<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use app\models\Task;
use app\models\Status;
use app\models\User;

class TaskController extends Controller
{
    public $layout = 'main';

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete'        => ['POST'],
                    'update-status' => ['POST'],
                    'update-date'   => ['POST'],
                ],
            ],
        ];
    }

    // ============================================
    // HELPERS
    // ============================================

    /**
     * 🔥 Obtiene las opciones de estado disponibles
     */
    private function getStatusOptions()
    {
        $statusNames = ['Por hacer', 'En progreso', 'En revisión', 'Programado', 'Completado', 'Cancelado'];

        return Status::find()
            ->where(['IN', 'status', $statusNames])
            ->select(['status', 'id_status'])
            ->indexBy('id_status')
            ->column();
    }

    /**
     * 🔥 Verifica si el usuario tiene permiso para acceder a una tarea
     */
    private function checkPermission($task, $user, $empresaId = null)
    {
        if (!$user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            if (!empty($empresaId) && $task->user) {
                return $task->user->id_company == $empresaId;
            }
            return !empty($empresaId);
        }

        if ($user->isAdmin() && !$user->isSuperAdmin()) {
            return $task->user && $task->user->id_company == $user->id_company;
        }

        if ($user->isAgent()) {
            return $task->id_user == $user->id_user;
        }

        return false;
    }

    /**
     * 🔥 Devuelve los IDs de usuarios visibles para el usuario actual.
     * Se usa para filtrar la tabla Task (que no tiene id_company directo).
     */
    private function getUserIdsForUser($user, $empresaId)
    {
        $query = User::find()->select('id_user');

        if ($user->isSuperAdmin()) {
            if (!empty($empresaId)) {
                $query->andWhere(['id_company' => $empresaId]);
            } else {
                return [];
            }
        } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
            $query->andWhere(['id_company' => $user->id_company]);
        } elseif ($user->isAgent()) {
            $query->andWhere(['id_user' => $user->id_user]);
        } else {
            return [];
        }

        return $query->column();
    }

    // ============================================
    // INDEX
    // ============================================

    public function actionIndex()
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            if (!$user) {
                return $this->redirect(['site/login']);
            }

            // 🔥 Validar Super Admin sin empresa
            if ($user->isSuperAdmin() && empty($empresaId)) {
                Yii::$app->session->setFlash('warning', 'Por favor, selecciona una empresa para continuar.');
                return $this->redirect(['empresa/index']);
            }

            // 🔥 OBTENER IDs DE USUARIOS VISIBLES PARA ESTE USUARIO
            $userIds = $this->getUserIdsForUser($user, $empresaId);

            // 🔥 QUERY BASE
            $query = Task::find()
                ->alias('t')
                ->leftJoin('Status s', 't.id_status = s.id_status')
                ->leftJoin('User u', 't.id_user = u.id_user')
                ->with(['status', 'user'])
                ->andWhere(['>', 't.id_user', 0]);  // 🔥 Excluir huérfanas

            // 🔥 FILTRO POR ROL Y EMPRESA
            if ($user->isAgent()) {
                // 🔥 AGENTE: solo sus tareas
                $query->andWhere(['t.id_user' => $user->id_user]);
            } else {
                // 🔥 ADMIN/SUPER ADMIN: tareas de usuarios de su empresa
                if (empty($userIds)) {
                    $query->andWhere(['t.id_task' => -1]);
                } else {
                    $query->andWhere(['t.id_user' => $userIds]);
                }
            }

            // 🔥 FILTROS DE BÚSQUEDA
            $search       = Yii::$app->request->get('search', '');
            $status       = Yii::$app->request->get('status', '');
            $fecha_inicio = Yii::$app->request->get('fecha_inicio', '');
            $fecha_fin    = Yii::$app->request->get('fecha_fin', '');

            if (!empty($search)) {
                $query->andWhere(['LIKE', 't.comments', $search]);
            }

            if (!empty($status)) {
                $statusModel = Status::find()->where(['status' => $status])->one();
                if ($statusModel) {
                    $query->andWhere(['t.id_status' => $statusModel->id_status]);
                }
            }

            if (!empty($fecha_inicio)) {
                $query->andWhere(['>=', 't.date_s', $fecha_inicio]);
            }

            if (!empty($fecha_fin)) {
                $query->andWhere(['<=', 't.date_s', $fecha_fin]);
            }

            $query->orderBy(['t.id_task' => SORT_DESC]);

            // 🔥 DATAPROVIDER CON PAGINACIÓN
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                    'pageSizeParam' => 'per-page',
                    'pageParam' => 'page',
                ],
                'sort' => [
                    'defaultOrder' => ['id_task' => SORT_DESC],
                ],
            ]);

            $tasks         = $dataProvider->getModels();
            $totalTasks    = $dataProvider->getTotalCount();
            $statusOptions = $this->getStatusOptions();

            // 🔥 CONTAR POR ESTADO (sobre TODAS las tareas filtradas, no solo la página)
            $statusCounts = [
                'Por hacer'   => 0,
                'En progreso' => 0,
                'En revisión' => 0,
                'Programado'  => 0,
                'Completado'  => 0,
                'Cancelado'   => 0,
                'Sin Estado'  => 0,
            ];

            $allTasksQuery = clone $query;
            $allTasks = $allTasksQuery->all();

            foreach ($allTasks as $task) {
                $statusName = $task->getStatusName();
                if (!isset($statusCounts[$statusName])) {
                    $statusCounts[$statusName] = 0;
                }
                $statusCounts[$statusName]++;
            }

            // 🔥 TAREAS SIN ESTADO (filtradas por rol y empresa)
            $withoutStatus = $statusCounts['Sin Estado'] ?? 0;

            return $this->render('index', [
                'dataProvider'  => $dataProvider,
                'tasks'         => $tasks,
                'totalTasks'    => $totalTasks,
                'statusCounts'  => $statusCounts,
                'statusOptions' => $statusOptions,
                'withoutStatus' => $withoutStatus,
                'isAdmin'       => $user->isAdmin(),
                'isAgent'       => $user->isAgent(),
                'search'        => $search,
                'status'        => $status,
                'fecha_inicio'  => $fecha_inicio,
                'fecha_fin'     => $fecha_fin,
            ]);

        } catch (\Exception $e) {
            Yii::error('Error en Task::actionIndex: ' . $e->getMessage(), 'task');
            Yii::error('Stack: ' . $e->getTraceAsString(), 'task');

            return $this->render('index', [
                'dataProvider'  => new ActiveDataProvider(['query' => Task::find()->where(['1' => '0'])]),
                'tasks'         => [],
                'totalTasks'    => 0,
                'statusCounts'  => [
                    'Por hacer'   => 0,
                    'En progreso' => 0,
                    'En revisión' => 0,
                    'Programado'  => 0,
                    'Completado'  => 0,
                    'Cancelado'   => 0,
                    'Sin Estado'  => 0,
                ],
                'statusOptions' => [],
                'withoutStatus' => 0,
                'isAdmin'       => false,
                'isAgent'       => false,
                'search'        => '',
                'status'        => '',
                'fecha_inicio'  => '',
                'fecha_fin'     => '',
            ]);
        }
    }

    // ============================================
    // VIEW
    // ============================================

    public function actionView($id)
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            $model = Task::find()
                ->with(['status', 'user'])
                ->where(['id_task' => $id])
                ->one();

            if (!$model) {
                throw new NotFoundHttpException('La actividad solicitada no existe.');
            }

            if (!$this->checkPermission($model, $user, $empresaId)) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para ver esta actividad.');
                return $this->redirect(['index']);
            }

            $statusOptions = $this->getStatusOptions();

            return $this->render('view', [
                'model'         => $model,
                'statusOptions' => $statusOptions,
                'isAdmin'       => $user->isAdmin(),
                'isAgent'       => $user->isAgent(),
            ]);

        } catch (NotFoundHttpException $e) {
            Yii::$app->session->setFlash('error', 'Actividad no encontrada.');
            return $this->redirect(['index']);
        } catch (\Exception $e) {
            Yii::error('Error en Task::actionView: ' . $e->getMessage(), 'task');
            Yii::$app->session->setFlash('error', 'Error al cargar la actividad.');
            return $this->redirect(['index']);
        }
    }

    // ============================================
    // CREATE
    // ============================================

    public function actionCreate()
    {
        try {
            $model = new Task();
            $user  = Yii::$app->user->identity;

            $statusOptions = $this->getStatusOptions();

            // 🔥 PRE-ASIGNAR VALORES POR DEFECTO
            $model->id_user = $user->id_user;
            $model->date_s = date('Y-m-d');
            $model->date_time = date('Y-m-d H:i:s');

            // Estado por defecto: "Por hacer"
            $statusPorDefecto = Status::find()->where(['status' => 'Por hacer'])->one();
            if ($statusPorDefecto) {
                $model->id_status = $statusPorDefecto->id_status;
            }

            if ($model->load(Yii::$app->request->post())) {
                try {
                    // 🔥 FORZAR id_user para agentes (seguridad)
                    if ($user->isAgent()) {
                        $model->id_user = $user->id_user;
                    }

                    // 🔥 Si no tiene usuario asignado, auto-asignar al usuario actual
                    if (empty($model->id_user) || $model->id_user == 0) {
                        $model->id_user = $user->id_user;
                    }

                    // 🔥 Fechas automáticas si no vienen
                    if (empty($model->date_s)) {
                        $model->date_s = date('Y-m-d');
                    }
                    if (empty($model->date_time)) {
                        $model->date_time = date('Y-m-d H:i:s');
                    }

                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', 'Actividad creada exitosamente.');
                        return $this->redirect(['view', 'id' => $model->id_task]);
                    } else {
                        $errors = $model->getErrors();
                        $errorMessages = [];
                        foreach ($errors as $attribute => $errorList) {
                            $label = $model->getAttributeLabel($attribute);
                            $errorMessages[] = $label . ': ' . implode(', ', $errorList);
                        }
                        Yii::$app->session->setFlash('error', 'Error al guardar:<br>' . implode('<br>', $errorMessages));
                    }
                } catch (\Exception $e) {
                    Yii::error('Error en actionCreate: ' . $e->getMessage(), 'task');
                    Yii::$app->session->setFlash('error', 'Error al crear la actividad: ' . $e->getMessage());
                }
            }

            return $this->render('create', [
                'model'         => $model,
                'statusOptions' => $statusOptions,
                'isAdmin'       => $user->isAdmin(),
                'isAgent'       => $user->isAgent(),
            ]);

        } catch (\Exception $e) {
            Yii::error('Error en Task::actionCreate: ' . $e->getMessage(), 'task');
            Yii::$app->session->setFlash('error', 'Error al cargar el formulario.');
            return $this->redirect(['index']);
        }
    }

    // ============================================
    // UPDATE
    // ============================================

    public function actionUpdate($id)
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            $model = Task::find()
                ->with(['status', 'user'])
                ->where(['id_task' => $id])
                ->one();

            if (!$model) {
                throw new NotFoundHttpException('La actividad solicitada no existe.');
            }

            if (!$this->checkPermission($model, $user, $empresaId)) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para editar esta actividad.');
                return $this->redirect(['index']);
            }

            $statusOptions = $this->getStatusOptions();

            if ($model->load(Yii::$app->request->post())) {
                try {
                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', 'Actividad actualizada exitosamente.');
                        return $this->redirect(['view', 'id' => $model->id_task]);
                    } else {
                        $errors = $model->getErrors();
                        $errorMessages = [];
                        foreach ($errors as $attribute => $errorList) {
                            $label = $model->getAttributeLabel($attribute);
                            $errorMessages[] = $label . ': ' . implode(', ', $errorList);
                        }
                        Yii::$app->session->setFlash('error', 'Error al guardar:<br>' . implode('<br>', $errorMessages));
                    }
                } catch (\Exception $e) {
                    Yii::error('Error en actionUpdate: ' . $e->getMessage(), 'task');
                    Yii::$app->session->setFlash('error', 'Error al actualizar la actividad.');
                }
            }

            return $this->render('update', [
                'model'         => $model,
                'statusOptions' => $statusOptions,
                'isAdmin'       => $user->isAdmin(),
                'isAgent'       => $user->isAgent(),
            ]);

        } catch (NotFoundHttpException $e) {
            Yii::$app->session->setFlash('error', 'Actividad no encontrada.');
            return $this->redirect(['index']);
        } catch (\Exception $e) {
            Yii::error('Error en Task::actionUpdate: ' . $e->getMessage(), 'task');
            Yii::$app->session->setFlash('error', 'Error al cargar el formulario.');
            return $this->redirect(['index']);
        }
    }

    // ============================================
    // UPDATE STATUS (POST)
    // ============================================

    public function actionUpdateStatus()
    {
        $request  = Yii::$app->request;
        $id       = $request->post('id_task');
        $statusId = $request->post('status_id');

        try {
            $model = $this->findModel($id);
            $user  = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            if (!$this->checkPermission($model, $user, $empresaId)) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para cambiar el estado.');
                return $this->redirect(['index']);
            }

            $statusModel = Status::findOne($statusId);
            if (!$statusModel) {
                Yii::$app->session->setFlash('error', 'Estado no válido.');
                return $this->redirect(['view', 'id' => $id]);
            }

            $oldStatus = $model->getStatusName();
            $model->id_status = $statusId;

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Estado actualizado de "' . $oldStatus . '" a "' . $statusModel->status . '"');
            } else {
                Yii::$app->session->setFlash('error', 'Error al actualizar el estado.');
            }

        } catch (NotFoundHttpException $e) {
            Yii::$app->session->setFlash('error', 'Actividad no encontrada.');
        } catch (\Exception $e) {
            Yii::error('Error en Task::actionUpdateStatus: ' . $e->getMessage(), 'task');
            Yii::$app->session->setFlash('error', 'Error al actualizar el estado.');
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    // ============================================
    // UPDATE DATE (POST)
    // ============================================

    public function actionUpdateDate()
    {
        $id    = Yii::$app->request->post('id_task');
        $dateS = Yii::$app->request->post('date_s');

        try {
            $model = $this->findModel($id);
            $user  = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            if (!$this->checkPermission($model, $user, $empresaId)) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para cambiar la fecha.');
                return $this->redirect(['index']);
            }

            if (empty($dateS)) {
                Yii::$app->session->setFlash('error', 'Fecha no válida.');
                return $this->redirect(['view', 'id' => $id]);
            }

            $model->date_s = $dateS;

            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'Fecha actualizada a ' . date('d/m/Y', strtotime($dateS)));
            } else {
                Yii::$app->session->setFlash('error', 'Error al actualizar la fecha.');
            }

        } catch (NotFoundHttpException $e) {
            Yii::$app->session->setFlash('error', 'Actividad no encontrada.');
        } catch (\Exception $e) {
            Yii::error('Error en Task::actionUpdateDate: ' . $e->getMessage(), 'task');
            Yii::$app->session->setFlash('error', 'Error al actualizar la fecha.');
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    // ============================================
    // DELETE
    // ============================================

    public function actionDelete($id)
    {
        try {
            $model = $this->findModel($id);
            $user  = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            // 🔥 Solo admin y super admin pueden eliminar
            if (!$user->isAdmin()) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para eliminar actividades.');
                return $this->redirect(['index']);
            }

            // 🔥 Verificar permiso sobre la tarea específica
            if (!$this->checkPermission($model, $user, $empresaId)) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para eliminar esta actividad.');
                return $this->redirect(['index']);
            }

            if ($model->delete()) {
                Yii::$app->session->setFlash('success', 'Actividad eliminada exitosamente.');
            } else {
                Yii::$app->session->setFlash('error', 'Error al eliminar la actividad.');
            }

        } catch (NotFoundHttpException $e) {
            Yii::$app->session->setFlash('error', 'Actividad no encontrada.');
        } catch (\Exception $e) {
            Yii::error('Error en Task::actionDelete: ' . $e->getMessage(), 'task');
            Yii::$app->session->setFlash('error', 'Error al eliminar la actividad.');
        }

        return $this->redirect(['index']);
    }

    // ============================================
    // FINDER
    // ============================================

    protected function findModel($id)
    {
        $model = Task::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('La actividad solicitada no existe.');
        }
        return $model;
    }
}