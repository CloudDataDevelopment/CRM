<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use app\models\SalesTracking;
use app\models\Lead;
use app\models\Status;
use app\models\Authentication;
use app\models\Role;

class SalesTrackingController extends Controller
{
    public $layout = 'main';

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    // ============================================
    // OBTENER INFO DEL USUARIO Y SU ROL
    // ============================================
    private function getUserInfo()
    {
        $user = Yii::$app->user->identity;
        if (!$user) {
            return null;
        }

        $auth = Authentication::find()
            ->where(['id_user' => $user->id_user])
            ->one();

        $isAdmin = false;
        $isAgent = false;
        $isSuperAdmin = false;

        if ($auth) {
            $role = Role::find()
                ->where(['id_role' => $auth->id_role])
                ->one();
            
            if ($role) {
                $roleName = strtolower(trim($role->role_type ?? ''));
                $isSuperAdmin = ($roleName === 'super_admin');
                $isAdmin = ($roleName === 'admin' || $roleName === 'super_admin');
                $isAgent = ($roleName === 'agente' || $roleName === 'agent');
            }
        }

        return [
            'user' => $user,
            'userId' => $user->id_user,
            'companyId' => $user->id_company ?? null,
            'isAdmin' => $isAdmin,
            'isAgent' => $isAgent,
            'isSuperAdmin' => $isSuperAdmin,
            'empresaSeleccionada' => Yii::$app->session->get('empresa_id'),
        ];
    }

    // ============================================
    // LISTA DE SEGUIMIENTOS CON PAGINACIÓN
    // ============================================
    public function actionIndex()
    {
        $userInfo = $this->getUserInfo();
        if (!$userInfo) {
            return $this->redirect(['site/login']);
        }

        $userId = $userInfo['userId'];
        $companyId = $userInfo['companyId'];
        $isAdmin = $userInfo['isAdmin'];
        $isAgent = $userInfo['isAgent'];
        $isSuperAdmin = $userInfo['isSuperAdmin'];
        $empresaSeleccionada = $userInfo['empresaSeleccionada'];

        // 🔥 CONSTRUIR CONSULTA BASE
        $query = SalesTracking::find()
            ->alias('st')
            ->leftJoin('Lead l', 'st.id_lead = l.id_lead')
            ->leftJoin('Status s', 'st.id_status = s.id_status');

        // 🔥 FILTRO POR ROL Y EMPRESA
        if ($isSuperAdmin) {
            if (!empty($empresaSeleccionada)) {
                $leadIds = Lead::find()
                    ->select('id_lead')
                    ->where(['id_company' => $empresaSeleccionada])
                    ->column();
                
                if (!empty($leadIds)) {
                    $query->andWhere(['st.id_lead' => $leadIds]);
                } else {
                    $query->andWhere(['st.id_sales_tracking' => -1]);
                }
            }
        } elseif ($isAdmin && !$isSuperAdmin) {
            $leadIds = Lead::find()
                ->select('id_lead')
                ->where(['id_company' => $companyId])
                ->column();
            
            if (!empty($leadIds)) {
                $query->andWhere(['st.id_lead' => $leadIds]);
            } else {
                $query->andWhere(['st.id_sales_tracking' => -1]);
            }
        } elseif ($isAgent) {
            $query->andWhere(['st.id_user' => $userId]);
        }

        // 🔥 FILTROS DE BÚSQUEDA
        $search = Yii::$app->request->get('search', '');
        $status = Yii::$app->request->get('status', '');
        $fecha_inicio = Yii::$app->request->get('fecha_inicio', '');
        $fecha_fin = Yii::$app->request->get('fecha_fin', '');

        if (!empty($search)) {
            $query->andWhere([
                'or',
                ['like', 'l.name', $search],
                ['like', 'l.lastname', $search],
                ['like', 'l.phone', $search],
                ['like', 'st.comments', $search],
            ]);
        }

        if (!empty($status)) {
            $statusModel = Status::find()->where(['status' => $status])->one();
            if ($statusModel) {
                $query->andWhere(['st.id_status' => $statusModel->id_status]);
            }
        }

        if (!empty($fecha_inicio)) {
            $query->andWhere(['>=', 'st.date_s', $fecha_inicio . ' 00:00:00']);
        }

        if (!empty($fecha_fin)) {
            $query->andWhere(['<=', 'st.date_s', $fecha_fin . ' 23:59:59']);
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
                    'date_s' => SORT_DESC,
                    'hour' => SORT_DESC,
                ],
                'attributes' => [
                    'id_sales_tracking' => [
                        'asc' => ['st.id_sales_tracking' => SORT_ASC],
                        'desc' => ['st.id_sales_tracking' => SORT_DESC],
                    ],
                    'id_lead' => [
                        'asc' => ['st.id_lead' => SORT_ASC],
                        'desc' => ['st.id_lead' => SORT_DESC],
                    ],
                    'date_s' => [
                        'asc' => ['st.date_s' => SORT_ASC],
                        'desc' => ['st.date_s' => SORT_DESC],
                    ],
                    'hour' => [
                        'asc' => ['st.hour' => SORT_ASC],
                        'desc' => ['st.hour' => SORT_DESC],
                    ],
                    'id_status' => [
                        'asc' => ['st.id_status' => SORT_ASC],
                        'desc' => ['st.id_status' => SORT_DESC],
                    ],
                    'comments' => [
                        'asc' => ['st.comments' => SORT_ASC],
                        'desc' => ['st.comments' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $trackings = $dataProvider->getModels();

        // 🔥 CONTAR POR ESTADO
        $statusCounts = [];
        $statusList = SalesTracking::getStatusOptions();

        $countQuery = clone $query;
        $allTrackings = $countQuery->all();

        foreach ($allTrackings as $t) {
            $statusModel = Status::findOne($t->id_status);
            if ($statusModel) {
                $name = $statusModel->status;
                if (!isset($statusCounts[$name])) {
                    $statusCounts[$name] = 0;
                }
                $statusCounts[$name]++;
            }
        }

        // 🔥 ÚLTIMOS 5 SEGUIMIENTOS
        $ultimosQuery = clone $query;
        $ultimosSeguimientos = $ultimosQuery
            ->orderBy(['st.date_s' => SORT_DESC, 'st.hour' => SORT_DESC])
            ->limit(5)
            ->all();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'trackings' => $trackings,
            'totalTrackings' => $dataProvider->getTotalCount(),
            'statusCounts' => $statusCounts,
            'ultimosSeguimientos' => $ultimosSeguimientos,
            'isAdmin' => $isAdmin,
            'isAgent' => $isAgent,
            'isSuperAdmin' => $isSuperAdmin,
            'statusList' => $statusList,
            'search' => $search,
            'status' => $status,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
        ]);
    }

    // ============================================
    // 🔥 VER DETALLES DEL SEGUIMIENTO (Vista completa)
    // ============================================
    public function actionDetails($id)
    {
        $userInfo = $this->getUserInfo();
        if (!$userInfo) {
            return $this->redirect(['site/login']);
        }

        $model = SalesTracking::find()
            ->with(['lead', 'status', 'user'])
            ->where(['id_sales_tracking' => $id])
            ->one();
        
        if (!$model) {
            Yii::$app->session->setFlash('error', 'El seguimiento solicitado no existe.');
            return $this->redirect(['index']);
        }

        if (!$this->checkPermission($model, $userInfo)) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para ver este seguimiento.');
            return $this->redirect(['index']);
        }

        return $this->render('details', [
            'model' => $model,
            'isAdmin' => $userInfo['isAdmin'],
            'isAgent' => $userInfo['isAgent'],
            'isSuperAdmin' => $userInfo['isSuperAdmin'],
        ]);
    }

    // ============================================
    // VER SEGUIMIENTO - MODAL
    // ============================================
    public function actionViewModal($id)
    {
        Yii::$app->response->format = Response::FORMAT_HTML;
        
        $userInfo = $this->getUserInfo();
        if (!$userInfo) {
            return $this->renderPartial('_view_modal', ['error' => 'Usuario no autenticado']);
        }

        $model = SalesTracking::find()
            ->with(['lead', 'status', 'user'])
            ->where(['id_sales_tracking' => $id])
            ->one();
        
        if (!$model) {
            return $this->renderPartial('_view_modal', ['error' => 'El seguimiento no existe']);
        }

        if (!$this->checkPermission($model, $userInfo)) {
            return $this->renderPartial('_view_modal', ['error' => 'No tienes permiso para ver este seguimiento']);
        }

        return $this->renderPartial('_view_modal', [
            'model' => $model,
            'isAdmin' => $userInfo['isAdmin'],
            'isAgent' => $userInfo['isAgent'],
            'isSuperAdmin' => $userInfo['isSuperAdmin'],
        ]);
    }

    // ============================================
    // ACTUALIZAR SEGUIMIENTO - MODAL
    // ============================================
    public function actionUpdateModal($id)
    {
        Yii::$app->response->format = Response::FORMAT_HTML;
        
        $userInfo = $this->getUserInfo();
        if (!$userInfo) {
            return $this->renderPartial('_update_modal', ['error' => 'Usuario no autenticado']);
        }

        $model = SalesTracking::find()
            ->with(['lead', 'status'])
            ->where(['id_sales_tracking' => $id])
            ->one();
        
        if (!$model) {
            return $this->renderPartial('_update_modal', ['error' => 'El seguimiento no existe']);
        }

        if (!$this->checkPermission($model, $userInfo)) {
            return $this->renderPartial('_update_modal', ['error' => 'No tienes permiso para editar este seguimiento']);
        }

        $leadsList = $this->getLeadsList($userInfo);
        $statusOptions = SalesTracking::getStatusOptions();

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return $this->renderPartial('_update_modal', [
                    'model' => $model,
                    'leadsList' => $leadsList,
                    'statusOptions' => $statusOptions,
                    'isAdmin' => $userInfo['isAdmin'],
                    'isSuperAdmin' => $userInfo['isSuperAdmin'],
                    'success' => '✅ Seguimiento actualizado exitosamente'
                ]);
            } else {
                return $this->renderPartial('_update_modal', [
                    'model' => $model,
                    'leadsList' => $leadsList,
                    'statusOptions' => $statusOptions,
                    'isAdmin' => $userInfo['isAdmin'],
                    'isSuperAdmin' => $userInfo['isSuperAdmin'],
                    'error' => 'Error al guardar: ' . json_encode($model->getErrors())
                ]);
            }
        }

        return $this->renderPartial('_update_modal', [
            'model' => $model,
            'leadsList' => $leadsList,
            'statusOptions' => $statusOptions,
            'isAdmin' => $userInfo['isAdmin'],
            'isSuperAdmin' => $userInfo['isSuperAdmin'],
        ]);
    }

    // ============================================
    // VER SEGUIMIENTO (Vista completa)
    // ============================================
    public function actionView($id)
    {
        $userInfo = $this->getUserInfo();
        if (!$userInfo) {
            return $this->redirect(['site/login']);
        }

        $model = SalesTracking::find()
            ->with(['lead', 'status', 'user'])
            ->where(['id_sales_tracking' => $id])
            ->one();
        
        if (!$model) {
            throw new NotFoundHttpException('El seguimiento solicitado no existe.');
        }

        if (!$this->checkPermission($model, $userInfo)) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para ver este seguimiento.');
            return $this->redirect(['index']);
        }

        return $this->render('view', [
            'model' => $model,
            'isAdmin' => $userInfo['isAdmin'],
            'isAgent' => $userInfo['isAgent'],
            'isSuperAdmin' => $userInfo['isSuperAdmin'],
        ]);
    }

    // ============================================
    // CREAR SEGUIMIENTO
    // ============================================
    public function actionCreate($leadId = null)
    {
        $userInfo = $this->getUserInfo();
        if (!$userInfo) {
            return $this->redirect(['site/login']);
        }

        $model = new SalesTracking();

        if ($leadId) {
            $lead = Lead::findOne($leadId);
            if ($lead) {
                if (!$this->checkLeadPermission($lead, $userInfo)) {
                    Yii::$app->session->setFlash('error', 'No tienes permiso para crear seguimientos para este lead.');
                    return $this->redirect(['lead/index']);
                }
                $model->id_lead = $leadId;
            }
        }

        $leadsList = $this->getLeadsList($userInfo);
        $statusOptions = SalesTracking::getStatusOptions();

        if ($model->load(Yii::$app->request->post())) {
            if (empty($model->id_lead)) {
                Yii::$app->session->setFlash('error', 'Debes seleccionar un lead.');
                return $this->redirect(['create']);
            }

            $lead = Lead::findOne($model->id_lead);
            if (!$this->checkLeadPermission($lead, $userInfo)) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para este lead.');
                return $this->redirect(['create']);
            }

            if ($userInfo['isAgent']) {
                $model->id_user = $userInfo['userId'];
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Seguimiento creado exitosamente.');
                return $this->redirect(['view', 'id' => $model->id_sales_tracking]);
            } else {
                Yii::$app->session->setFlash('error', 'Error al guardar: ' . json_encode($model->getErrors()));
            }
        }

        return $this->render('create', [
            'model' => $model,
            'leadId' => $leadId,
            'leadsList' => $leadsList,
            'statusOptions' => $statusOptions,
            'isAdmin' => $userInfo['isAdmin'],
            'isAgent' => $userInfo['isAgent'],
            'isSuperAdmin' => $userInfo['isSuperAdmin'],
        ]);
    }

    // ============================================
    // ACTUALIZAR SEGUIMIENTO (Vista completa)
    // ============================================
    public function actionUpdate($id)
    {
        $userInfo = $this->getUserInfo();
        if (!$userInfo) {
            return $this->redirect(['site/login']);
        }

        $model = SalesTracking::find()
            ->with(['lead', 'status'])
            ->where(['id_sales_tracking' => $id])
            ->one();
        
        if (!$model) {
            throw new NotFoundHttpException('El seguimiento solicitado no existe.');
        }

        if (!$this->checkPermission($model, $userInfo)) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para editar este seguimiento.');
            return $this->redirect(['index']);
        }

        $leadsList = $this->getLeadsList($userInfo);
        $statusOptions = SalesTracking::getStatusOptions();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Seguimiento actualizado exitosamente.');
                return $this->redirect(['view', 'id' => $model->id_sales_tracking]);
            } else {
                Yii::$app->session->setFlash('error', 'Error al guardar: ' . json_encode($model->getErrors()));
            }
        }

        return $this->render('update', [
            'model' => $model,
            'leadsList' => $leadsList,
            'statusOptions' => $statusOptions,
            'isAdmin' => $userInfo['isAdmin'],
            'isAgent' => $userInfo['isAgent'],
            'isSuperAdmin' => $userInfo['isSuperAdmin'],
        ]);
    }

    // ============================================
    // ELIMINAR SEGUIMIENTO
    // ============================================
    public function actionDelete($id)
    {
        $userInfo = $this->getUserInfo();
        if (!$userInfo) {
            return $this->redirect(['site/login']);
        }

        if (!$userInfo['isAdmin'] && !$userInfo['isSuperAdmin']) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para eliminar seguimientos.');
            return $this->redirect(['index']);
        }

        $model = SalesTracking::findOne($id);
        
        if (!$model) {
            throw new NotFoundHttpException('El seguimiento solicitado no existe.');
        }

        if (!$this->checkPermission($model, $userInfo)) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para eliminar este seguimiento.');
            return $this->redirect(['index']);
        }

        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Seguimiento eliminado exitosamente.');
        } else {
            Yii::$app->session->setFlash('error', 'Error al eliminar el seguimiento.');
        }

        return $this->redirect(['index']);
    }

    // ============================================
    // MÉTODOS AUXILIARES
    // ============================================
    
    private function checkPermission($tracking, $userInfo)
    {
        if ($userInfo['isSuperAdmin']) {
            $empresaSeleccionada = $userInfo['empresaSeleccionada'];
            if (!empty($empresaSeleccionada) && $tracking->lead) {
                return $tracking->lead->id_company == $empresaSeleccionada;
            }
            return true;
        }

        if ($userInfo['isAdmin'] && !$userInfo['isSuperAdmin']) {
            if ($tracking->lead) {
                return $tracking->lead->id_company == $userInfo['companyId'];
            }
            return false;
        }

        if ($userInfo['isAgent']) {
            if ($tracking->lead) {
                return $tracking->lead->id_user == $userInfo['userId'];
            }
            return false;
        }

        return false;
    }

    private function checkLeadPermission($lead, $userInfo)
    {
        if (!$lead) {
            return false;
        }

        if ($userInfo['isSuperAdmin']) {
            $empresaSeleccionada = $userInfo['empresaSeleccionada'];
            if (!empty($empresaSeleccionada)) {
                return $lead->id_company == $empresaSeleccionada;
            }
            return true;
        }

        if ($userInfo['isAdmin'] && !$userInfo['isSuperAdmin']) {
            return $lead->id_company == $userInfo['companyId'];
        }

        if ($userInfo['isAgent']) {
            return $lead->id_user == $userInfo['userId'];
        }

        return false;
    }

    private function getLeadsList($userInfo)
    {
        $query = Lead::find();

        if ($userInfo['isSuperAdmin']) {
            $empresaSeleccionada = $userInfo['empresaSeleccionada'];
            if (!empty($empresaSeleccionada)) {
                $query->where(['id_company' => $empresaSeleccionada]);
            }
        } elseif ($userInfo['isAdmin'] && !$userInfo['isSuperAdmin']) {
            $query->where(['id_company' => $userInfo['companyId']]);
        } elseif ($userInfo['isAgent']) {
            $query->where(['id_user' => $userInfo['userId']]);
        }

        $leads = $query->all();
        return ArrayHelper::map($leads, 'id_lead', function($lead) {
            return $lead->name . ' ' . $lead->lastname . ' (' . $lead->phone . ')';
        });
    }
}