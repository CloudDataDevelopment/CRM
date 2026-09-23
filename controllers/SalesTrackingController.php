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
                    'delete'  => ['POST', 'GET'],
                    'restore' => ['POST', 'GET'],
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
    // HELPER: Obtener ID del estado "Inactivo"
    // ============================================
    private function getTrashStatusId()
    {
        $status = Status::find()->where(['status' => 'Inactivo'])->one();
        return $status ? $status->id_status : null;
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

        // 🔥 EXCLUIR PAPELERA (estado "Inactivo")
        $trashId = $this->getTrashStatusId();
        if ($trashId) {
            $query->andWhere(['<>', 'st.id_status', $trashId]);
        }

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

        // 🔥 CONTAR PAPELERA
        $trashCount = 0;
        if ($trashId) {
            $trashQuery = SalesTracking::find()
                ->alias('st')
                ->leftJoin('Lead l', 'st.id_lead = l.id_lead')
                ->where(['st.id_status' => $trashId]);

            if ($isSuperAdmin) {
                if (!empty($empresaSeleccionada)) {
                    $leadIds = Lead::find()->select('id_lead')->where(['id_company' => $empresaSeleccionada])->column();
                    if (!empty($leadIds)) {
                        $trashQuery->andWhere(['st.id_lead' => $leadIds]);
                    } else {
                        $trashQuery->andWhere(['st.id_sales_tracking' => -1]);
                    }
                }
            } elseif ($isAdmin && !$isSuperAdmin) {
                $leadIds = Lead::find()->select('id_lead')->where(['id_company' => $companyId])->column();
                if (!empty($leadIds)) {
                    $trashQuery->andWhere(['st.id_lead' => $leadIds]);
                } else {
                    $trashQuery->andWhere(['st.id_sales_tracking' => -1]);
                }
            } elseif ($isAgent) {
                $trashQuery->andWhere(['st.id_user' => $userId]);
            }

            $trashCount = $trashQuery->count();
        }

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'trackings' => $trackings,
            'totalTrackings' => $dataProvider->getTotalCount(),
            'statusCounts' => $statusCounts,
            'ultimosSeguimientos' => $ultimosSeguimientos,
            'trashCount' => $trashCount,
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
    // 🔥 PAPELERA DE SEGUIMIENTOS
    // ============================================
    public function actionTrash()
    {
        $userInfo = $this->getUserInfo();
        if (!$userInfo) {
            return $this->redirect(['site/login']);
        }

        if (!$userInfo['isAdmin'] && !$userInfo['isSuperAdmin']) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para acceder a la papelera.');
            return $this->redirect(['index']);
        }

        $trashId = $this->getTrashStatusId();
        if (!$trashId) {
            Yii::$app->session->setFlash('warning', 'No se encontró el estado "Inactivo".');
            return $this->redirect(['index']);
        }

        $userId = $userInfo['userId'];
        $companyId = $userInfo['companyId'];
        $isAdmin = $userInfo['isAdmin'];
        $isSuperAdmin = $userInfo['isSuperAdmin'];
        $empresaSeleccionada = $userInfo['empresaSeleccionada'];

        // 🔥 QUERY BASE - Solo Inactivos
        $query = SalesTracking::find()
            ->alias('st')
            ->leftJoin('Lead l', 'st.id_lead = l.id_lead')
            ->leftJoin('Status s', 'st.id_status = s.id_status')
            ->andWhere(['st.id_status' => $trashId]);

        // FILTRO POR ROL Y EMPRESA
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
        }

        // Filtros
        $search = Yii::$app->request->get('search', '');
        if (!empty($search)) {
            $query->andWhere([
                'or',
                ['like', 'l.name', $search],
                ['like', 'l.lastname', $search],
                ['like', 'l.phone', $search],
                ['like', 'st.comments', $search],
            ]);
        }

        // DATAPROVIDER
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
            ],
        ]);

        $trackings = $dataProvider->getModels();
        $totalTrackings = $dataProvider->getTotalCount();

        return $this->render('trash', [
            'dataProvider' => $dataProvider,
            'trackings' => $trackings,
            'totalTrackings' => $totalTrackings,
            'search' => $search,
            'isAdmin' => $isAdmin,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }

    // ============================================
    // 🔥 VER DETALLES DEL SEGUIMIENTO
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
            // 🔥 Forzar id_user si es agente
            if ($userInfo['isAgent']) {
                $model->id_user = $userInfo['userId'];
            }
            
            if (empty($model->id_user)) {
                $model->id_user = $userInfo['userId'];
            }
            
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
                $errors = $model->getErrors();
                $errorMessages = [];
                foreach ($errors as $attribute => $errorList) {
                    $label = $model->getAttributeLabel($attribute);
                    $errorMessages[] = $label . ': ' . implode(', ', $errorList);
                }
                return $this->renderPartial('_update_modal', [
                    'model' => $model,
                    'leadsList' => $leadsList,
                    'statusOptions' => $statusOptions,
                    'isAdmin' => $userInfo['isAdmin'],
                    'isSuperAdmin' => $userInfo['isSuperAdmin'],
                    'error' => 'Error al guardar:<br>' . implode('<br>', $errorMessages)
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
        
        // 🔥 ASIGNAR USUARIO ACTUAL DESDE EL INICIO
        $model->id_user = $userInfo['userId'];

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
            
            // 🔥 FORZAR USUARIO ACTUAL
            if (empty($model->id_user)) {
                $model->id_user = $userInfo['userId'];
            }
            
            if ($userInfo['isAgent']) {
                $model->id_user = $userInfo['userId'];
            }
            
            if (empty($model->id_lead)) {
                Yii::$app->session->setFlash('error', 'Debes seleccionar un lead.');
                return $this->redirect(['create']);
            }

            $lead = Lead::findOne($model->id_lead);
            if (!$this->checkLeadPermission($lead, $userInfo)) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para este lead.');
                return $this->redirect(['create']);
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Seguimiento creado exitosamente.');
                return $this->redirect(['view', 'id' => $model->id_sales_tracking]);
            } else {
                $errors = $model->getErrors();
                $errorMessages = [];
                foreach ($errors as $attribute => $errorList) {
                    $label = $model->getAttributeLabel($attribute);
                    $errorMessages[] = $label . ': ' . implode(', ', $errorList);
                }
                Yii::$app->session->setFlash('error', 'Error al guardar:<br>' . implode('<br>', $errorMessages));
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
            
            if ($userInfo['isAgent']) {
                $model->id_user = $userInfo['userId'];
            }
            
            if (empty($model->id_user)) {
                $model->id_user = $userInfo['userId'];
            }
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Seguimiento actualizado exitosamente.');
                return $this->redirect(['view', 'id' => $model->id_sales_tracking]);
            } else {
                $errors = $model->getErrors();
                $errorMessages = [];
                foreach ($errors as $attribute => $errorList) {
                    $label = $model->getAttributeLabel($attribute);
                    $errorMessages[] = $label . ': ' . implode(', ', $errorList);
                }
                Yii::$app->session->setFlash('error', 'Error al guardar:<br>' . implode('<br>', $errorMessages));
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
    // 🔥 MOVER A PAPELERA (Cambio a Inactivo)
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

        $trashId = $this->getTrashStatusId();
        if (!$trashId) {
            Yii::$app->session->setFlash('error', 'No se encontró el estado "Inactivo" en la base de datos.');
            return $this->redirect(['index']);
        }

        $model->id_status = $trashId;

        if ($model->save(false)) {
            Yii::$app->session->setFlash('success', 'Seguimiento movido a la papelera.');
        } else {
            Yii::$app->session->setFlash('error', 'Error al mover el seguimiento a la papelera.');
        }

        return $this->redirect(['index']);
    }

    // ============================================
    // 🔥 RESTAURAR SEGUIMIENTO
    // ============================================
    public function actionRestore($id)
    {
        $userInfo = $this->getUserInfo();
        if (!$userInfo) {
            return $this->redirect(['site/login']);
        }

        if (!$userInfo['isAdmin'] && !$userInfo['isSuperAdmin']) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para restaurar seguimientos.');
            return $this->redirect(['trash']);
        }

        $model = SalesTracking::findOne($id);
        
        if (!$model) {
            throw new NotFoundHttpException('El seguimiento solicitado no existe.');
        }

        if (!$this->checkPermission($model, $userInfo)) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para restaurar este seguimiento.');
            return $this->redirect(['trash']);
        }

        $statusDefault = Status::find()->where(['status' => 'Pendiente'])->one();
        if (!$statusDefault) {
            Yii::$app->session->setFlash('error', 'No se encontró el estado "Pendiente".');
            return $this->redirect(['trash']);
        }

        $model->id_status = $statusDefault->id_status;

        if ($model->save(false)) {
            Yii::$app->session->setFlash('success', 'Seguimiento restaurado exitosamente.');
        } else {
            Yii::$app->session->setFlash('error', 'Error al restaurar el seguimiento.');
        }

        return $this->redirect(['trash']);
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