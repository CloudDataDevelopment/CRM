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

    private function getStatusOptions()
    {
        $statusNames = ['Por hacer', 'En progreso', 'En revisión', 'Programado', 'Completado', 'Cancelado'];

        return Status::find()
            ->where(['IN', 'status', $statusNames])
            ->select(['status', 'id_status'])
            ->indexBy('id_status')
            ->column();
    }

    private function checkPermission($task, $user)
    {
        if ($user->isAdmin() || $user->isAgent()) {
            return true;
        }
        return false;
    }

    // ============================================
    // INDEX
    // ============================================

    public function actionIndex()
    {
        try {
            $user = Yii::$app->user->identity;

            if (!$user) {
                return $this->redirect(['site/login']);
            }

            $query = Task::find()
                ->alias('t')
                ->leftJoin('Status s', 't.id_status = s.id_status')
                ->with(['status']);

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

            $tasks        = $dataProvider->getModels();
            $totalTasks   = $dataProvider->getTotalCount();
            $statusOptions = $this->getStatusOptions();

            $statusCounts = [];
            foreach ($tasks as $task) {
                $statusName = $task->getStatusName();
                if (!isset($statusCounts[$statusName])) {
                    $statusCounts[$statusName] = 0;
                }
                $statusCounts[$statusName]++;
            }

            $withoutStatus = Task::find()
                ->where(['or', ['id_status' => null], ['id_status' => 0]])
                ->count();

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

            return $this->render('index', [
                'dataProvider'  => new ActiveDataProvider(['query' => Task::find()->where(['1' => '0'])]),
                'tasks'         => [],
                'totalTasks'    => 0,
                'statusCounts'  => [],
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

            $model = Task::find()
                ->with(['status'])
                ->where(['id_task' => $id])
                ->one();

            if (!$model) {
                throw new NotFoundHttpException('La tarea solicitada no existe.');
            }

            if (!$this->checkPermission($model, $user)) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para ver esta tarea.');
                return $this->redirect(['index']);
            }

            $statusOptions = $this->getStatusOptions();

            return $this->render('view', [
                'model'         => $model,
                'statusOptions' => $statusOptions,
                'isAdmin'       => $user->isAdmin(),
                'isAgent'       => $user->isAgent(),
            ]);

        } catch (\Exception $e) {
            Yii::error('Error en Task::actionView: ' . $e->getMessage(), 'task');
            Yii::$app->session->setFlash('error', 'Error al cargar la tarea.');
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

            if ($model->load(Yii::$app->request->post())) {
                try {
                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', 'Tarea creada exitosamente.');
                        return $this->redirect(['view', 'id' => $model->id_task]);
                    } else {
                        Yii::$app->session->setFlash('error', 'Error al guardar: ' . json_encode($model->getErrors()));
                    }
                } catch (\Exception $e) {
                    Yii::error('Error en actionCreate: ' . $e->getMessage(), 'task');
                    Yii::$app->session->setFlash('error', 'Error al crear la tarea: ' . $e->getMessage());
                }
            } else {
                // Valores por defecto al cargar el formulario
                $model->date_s = date('Y-m-d');
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

            $model = Task::find()
                ->with(['status'])
                ->where(['id_task' => $id])
                ->one();

            if (!$model) {
                throw new NotFoundHttpException('La tarea solicitada no existe.');
            }

            if (!$this->checkPermission($model, $user)) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para editar esta tarea.');
                return $this->redirect(['index']);
            }

            $statusOptions = $this->getStatusOptions();

            if ($model->load(Yii::$app->request->post())) {
                try {
                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', 'Tarea actualizada exitosamente.');
                        return $this->redirect(['view', 'id' => $model->id_task]);
                    } else {
                        Yii::$app->session->setFlash('error', 'Error al guardar: ' . json_encode($model->getErrors()));
                    }
                } catch (\Exception $e) {
                    Yii::error('Error en actionUpdate: ' . $e->getMessage(), 'task');
                    Yii::$app->session->setFlash('error', 'Error al actualizar la tarea.');
                }
            }

            return $this->render('update', [
                'model'         => $model,
                'statusOptions' => $statusOptions,
                'isAdmin'       => $user->isAdmin(),
                'isAgent'       => $user->isAgent(),
            ]);

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

            if (!$this->checkPermission($model, $user)) {
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
            Yii::$app->session->setFlash('error', 'Tarea no encontrada.');
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

            if (!$this->checkPermission($model, $user)) {
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
            Yii::$app->session->setFlash('error', 'Tarea no encontrada.');
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

            if (!$user->isAdmin()) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para eliminar tareas.');
                return $this->redirect(['index']);
            }

            if ($model->delete()) {
                Yii::$app->session->setFlash('success', 'Tarea eliminada exitosamente.');
            }

        } catch (NotFoundHttpException $e) {
            Yii::$app->session->setFlash('error', 'Tarea no encontrada.');
        } catch (\Exception $e) {
            Yii::error('Error en Task::actionDelete: ' . $e->getMessage(), 'task');
            Yii::$app->session->setFlash('error', 'Error al eliminar la tarea.');
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
            throw new NotFoundHttpException('La tarea solicitada no existe.');
        }
        return $model;
    }
}