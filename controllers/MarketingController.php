<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use app\models\MarketingSearch;
use app\models\Marketing;
use app\models\Campaign;
use app\models\Promotion;
use app\models\Company;
use app\models\Status;
use app\components\ErrorManager;

class MarketingController extends Controller
{
    public $layout = 'main';

    const MARKETING_STATUS_LIST = ['Activo', 'Programado', 'Suspendido', 'Publicado', 'Inactivo', 'Cancelado'];

    // 🔥 Estado de papelera
    const TRASH_STATUS = 'Eliminado';

    // 🔥 Estado al restaurar
    const RESTORE_STATUS = 'Inactivo';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete'  => ['POST', 'GET'],
                    'restore' => ['POST', 'GET'],
                ],
            ],
        ];
    }

    // ============================================
    // HELPER: ID del estado "Eliminado" (papelera)
    // ============================================
    private function getTrashStatusId()
    {
        $status = Status::find()->where(['status' => self::TRASH_STATUS])->one();
        return $status ? $status->id_status : null;
    }

    // ============================================
    // HELPER: ID del estado al restaurar
    // ============================================
    private function getRestoreStatusId()
    {
        $status = Status::find()->where(['status' => self::RESTORE_STATUS])->one();
        return $status ? $status->id_status : null;
    }

    // ============================================
    // ÍNDICE
    // ============================================
    public function actionIndex()
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            if ($user->isSuperAdmin() && empty($empresaId)) {
                Yii::$app->session->setFlash('warning', 'Por favor, selecciona una empresa para continuar.');
                return $this->redirect(['empresa/index']);
            }

            $trashId = $this->getTrashStatusId();

            // ============================================
            // 🔥 FILTROS
            // ============================================
            $search       = Yii::$app->request->get('search', '');
            $status       = Yii::$app->request->get('status', '');
            $type         = Yii::$app->request->get('type', '');
            $fecha_inicio = Yii::$app->request->get('fecha_inicio', '');
            $fecha_fin    = Yii::$app->request->get('fecha_fin', '');

            // ============================================
            // 🔥 QUERY BASE PARA CAMPAÑAS (PK: id_campaign)
            // ============================================
            $campaignQuery = Campaign::find()
                ->alias('c')
                ->orderBy(['c.id_campaign' => SORT_DESC]);

            if ($user->isSuperAdmin() && !empty($empresaId)) {
                $campaignQuery->andWhere(['c.id_company' => $empresaId]);
            } elseif (!$user->isSuperAdmin()) {
                $campaignQuery->andWhere(['c.id_company' => $user->id_company]);
            }

            // 🔥 EXCLUIR PAPELERA
            if ($trashId) {
                $campaignQuery->andWhere(['<>', 'c.id_status', $trashId]);
            }

            // 🔥 FILTROS CAMPAÑAS
            if (!empty($search)) {
                $campaignQuery->andWhere(['like', 'c.campaign_name', $search]);
            }
            if (!empty($status)) {
                $statusModel = Status::find()->where(['status' => $status])->one();
                if ($statusModel) {
                    $campaignQuery->andWhere(['c.id_status' => $statusModel->id_status]);
                } else {
                    $campaignQuery->andWhere(['0' => '1']);
                }
            }
            if (!empty($fecha_inicio)) {
                $campaignQuery->andWhere(['>=', 'c.start_date', $fecha_inicio]);
            }
            if (!empty($fecha_fin)) {
                $campaignQuery->andWhere(['<=', 'c.end_date', $fecha_fin]);
            }

            // ============================================
            // 🔥 QUERY BASE PARA PROMOCIONES (PK: id_promotion)
            // ============================================
            $promotionQuery = Promotion::find()
                ->alias('p')
                ->orderBy(['p.id_promotion' => SORT_DESC]);

            if ($user->isSuperAdmin() && !empty($empresaId)) {
                $promotionQuery->andWhere(['p.id_company' => $empresaId]);
            } elseif (!$user->isSuperAdmin()) {
                $promotionQuery->andWhere(['p.id_company' => $user->id_company]);
            }

            // 🔥 EXCLUIR PAPELERA
            if ($trashId) {
                $promotionQuery->andWhere(['<>', 'p.id_status', $trashId]);
            }

            // 🔥 FILTROS PROMOCIONES
            if (!empty($search)) {
                $promotionQuery->andWhere(['like', 'p.promotion_name', $search]);
            }
            if (!empty($status)) {
                $statusModel = Status::find()->where(['status' => $status])->one();
                if ($statusModel) {
                    $promotionQuery->andWhere(['p.id_status' => $statusModel->id_status]);
                } else {
                    $promotionQuery->andWhere(['0' => '1']);
                }
            }
            if (!empty($fecha_inicio)) {
                $promotionQuery->andWhere(['>=', 'p.start_date', $fecha_inicio]);
            }
            if (!empty($fecha_fin)) {
                $promotionQuery->andWhere(['<=', 'p.end_date', $fecha_fin]);
            }

            // ============================================
            // DATAPROVIDER CAMPAÑAS
            // ============================================
            $campaignDataProvider = new ActiveDataProvider([
                'query' => $campaignQuery,
                'pagination' => [
                    'pageSize' => 10,
                    'pageSizeParam' => 'per-page',
                    'pageParam' => 'page_campaigns',
                ],
                'sort' => [
                    'defaultOrder' => ['id_campaign' => SORT_DESC],
                    'attributes' => [
                        'id_campaign' => [
                            'asc' => ['c.id_campaign' => SORT_ASC],
                            'desc' => ['c.id_campaign' => SORT_DESC],
                            'label' => 'ID',
                            'default' => SORT_DESC,
                        ],
                        'campaign_name' => [
                            'asc' => ['c.campaign_name' => SORT_ASC],
                            'desc' => ['c.campaign_name' => SORT_DESC],
                            'label' => 'Nombre',
                        ],
                        'start_date' => [
                            'asc' => ['c.start_date' => SORT_ASC],
                            'desc' => ['c.start_date' => SORT_DESC],
                            'label' => 'Fecha Inicio',
                        ],
                        'end_date' => [
                            'asc' => ['c.end_date' => SORT_ASC],
                            'desc' => ['c.end_date' => SORT_DESC],
                            'label' => 'Fecha Fin',
                        ],
                        'id_status' => [
                            'asc' => ['c.id_status' => SORT_ASC],
                            'desc' => ['c.id_status' => SORT_DESC],
                            'label' => 'Estado',
                        ],
                    ],
                ],
            ]);

            // ============================================
            // DATAPROVIDER PROMOCIONES
            // ============================================
            $promotionDataProvider = new ActiveDataProvider([
                'query' => $promotionQuery,
                'pagination' => [
                    'pageSize' => 10,
                    'pageSizeParam' => 'per-page',
                    'pageParam' => 'page_promotions',
                ],
                'sort' => [
                    'defaultOrder' => ['id_promotion' => SORT_DESC],
                    'attributes' => [
                        'id_promotion' => [
                            'asc' => ['p.id_promotion' => SORT_ASC],
                            'desc' => ['p.id_promotion' => SORT_DESC],
                            'label' => 'ID',
                            'default' => SORT_DESC,
                        ],
                        'promotion_name' => [
                            'asc' => ['p.promotion_name' => SORT_ASC],
                            'desc' => ['p.promotion_name' => SORT_DESC],
                            'label' => 'Nombre',
                        ],
                        'start_date' => [
                            'asc' => ['p.start_date' => SORT_ASC],
                            'desc' => ['p.start_date' => SORT_DESC],
                            'label' => 'Fecha Inicio',
                        ],
                        'end_date' => [
                            'asc' => ['p.end_date' => SORT_ASC],
                            'desc' => ['p.end_date' => SORT_DESC],
                            'label' => 'Fecha Fin',
                        ],
                        'id_status' => [
                            'asc' => ['p.id_status' => SORT_ASC],
                            'desc' => ['p.id_status' => SORT_DESC],
                            'label' => 'Estado',
                        ],
                    ],
                ],
            ]);

            // ============================================
            // TOTALES (SIEMPRE SIN FILTROS, EXCLUYENDO PAPELERA)
            // ============================================
            $totalCampaignsQuery = Campaign::find();
            $totalPromotionsQuery = Promotion::find();

            if ($user->isSuperAdmin() && !empty($empresaId)) {
                $totalCampaignsQuery->andWhere(['id_company' => $empresaId]);
                $totalPromotionsQuery->andWhere(['id_company' => $empresaId]);
            } elseif (!$user->isSuperAdmin()) {
                $totalCampaignsQuery->andWhere(['id_company' => $user->id_company]);
                $totalPromotionsQuery->andWhere(['id_company' => $user->id_company]);
            }

            if ($trashId) {
                $totalCampaignsQuery->andWhere(['<>', 'id_status', $trashId]);
                $totalPromotionsQuery->andWhere(['<>', 'id_status', $trashId]);
            }

            $totalCampaigns = $totalCampaignsQuery->count();
            $totalPromotions = $totalPromotionsQuery->count();

            // ============================================
            // ACTIVOS (SIEMPRE SIN FILTROS, EXCLUYENDO PAPELERA)
            // ============================================
            $statusActivo = Status::find()->where(['status' => 'Activo'])->one();
            $activeCampaigns = 0;
            $activePromotions = 0;

            if ($statusActivo) {
                $activeCampaignsQuery = Campaign::find()->where(['id_status' => $statusActivo->id_status]);
                $activePromotionsQuery = Promotion::find()->where(['id_status' => $statusActivo->id_status]);

                if ($user->isSuperAdmin() && !empty($empresaId)) {
                    $activeCampaignsQuery->andWhere(['id_company' => $empresaId]);
                    $activePromotionsQuery->andWhere(['id_company' => $empresaId]);
                } elseif (!$user->isSuperAdmin()) {
                    $activeCampaignsQuery->andWhere(['id_company' => $user->id_company]);
                    $activePromotionsQuery->andWhere(['id_company' => $user->id_company]);
                }

                $activeCampaigns = $activeCampaignsQuery->count();
                $activePromotions = $activePromotionsQuery->count();
            }

            // ============================================
            // 🔥 CONTAR PAPELERA
            // ============================================
            $trashCount = 0;
            if ($trashId) {
                $trashCampaignsQuery = Campaign::find()->andWhere(['id_status' => $trashId]);
                $trashPromotionsQuery = Promotion::find()->andWhere(['id_status' => $trashId]);

                if ($user->isSuperAdmin() && !empty($empresaId)) {
                    $trashCampaignsQuery->andWhere(['id_company' => $empresaId]);
                    $trashPromotionsQuery->andWhere(['id_company' => $empresaId]);
                } elseif (!$user->isSuperAdmin()) {
                    $trashCampaignsQuery->andWhere(['id_company' => $user->id_company]);
                    $trashPromotionsQuery->andWhere(['id_company' => $user->id_company]);
                }

                $trashCount = $trashCampaignsQuery->count() + $trashPromotionsQuery->count();
            }

            // ============================================
            // SEARCH MODEL Y LISTAS PARA FILTROS
            // ============================================
            $searchModel = new MarketingSearch();

            $statusList = Status::find()
                ->select(['status', 'id_status'])
                ->where(['IN', 'status', self::MARKETING_STATUS_LIST])
                ->indexBy('id_status')
                ->column();

            $canCreate = $user->isAdmin() || $user->isSuperAdmin();

            return $this->render('index', [
                'searchModel' => $searchModel,
                'campaignDataProvider' => $campaignDataProvider,
                'promotionDataProvider' => $promotionDataProvider,
                'totalCampaigns' => $totalCampaigns,
                'totalPromotions' => $totalPromotions,
                'activeCampaigns' => $activeCampaigns,
                'activePromotions' => $activePromotions,
                'trashCount' => $trashCount,
                'companyList' => [],
                'statusList' => $statusList,
                'canCreate' => $canCreate,
                'isAdmin' => $user->isAdmin(),
                'isSuperAdmin' => $user->isSuperAdmin(),

                // 🔥 Pasar filtros a la vista
                'search' => $search,
                'status' => $status,
                'type' => $type,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
            ]);

        } catch (\Exception $e) {
            Yii::error('❌ [MARKETING] ERROR: ' . $e->getMessage(), 'marketing-debug');
            Yii::error('❌ [MARKETING] Archivo: ' . $e->getFile() . ':' . $e->getLine(), 'marketing-debug');
            throw $e;
        }
    }

    // ============================================
    // 🔥 PAPELERA (solo "Eliminado")
    // ============================================
    public function actionTrash()
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            if (!$user) {
                return $this->redirect(['site/login']);
            }

            if (!$user->isAdmin() && !$user->isSuperAdmin()) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para acceder a la papelera.');
                return $this->redirect(['index']);
            }

            if ($user->isSuperAdmin() && empty($empresaId)) {
                Yii::$app->session->setFlash('warning', 'Por favor, selecciona una empresa para continuar.');
                return $this->redirect(['empresa/index']);
            }

            $trashId = $this->getTrashStatusId();
            if (!$trashId) {
                Yii::$app->session->setFlash('warning', 'No se encontró el estado "Eliminado".');
                return $this->redirect(['index']);
            }

            // ============================================
            // 🔥 FILTROS
            // ============================================
            $search       = Yii::$app->request->get('search', '');
            $tipoFiltro   = Yii::$app->request->get('tipo', ''); // 'campaign' | 'promotion' | ''
            $fecha_inicio = Yii::$app->request->get('fecha_inicio', '');
            $fecha_fin    = Yii::$app->request->get('fecha_fin', '');

            // ============================================
            // 🔥 QUERY CAMPAÑAS EN PAPELERA
            // ============================================
            $campaignQuery = Campaign::find()
                ->alias('c')
                ->andWhere(['c.id_status' => $trashId])
                ->orderBy(['c.id_campaign' => SORT_DESC]);

            if ($user->isSuperAdmin() && !empty($empresaId)) {
                $campaignQuery->andWhere(['c.id_company' => $empresaId]);
            } elseif (!$user->isSuperAdmin()) {
                $campaignQuery->andWhere(['c.id_company' => $user->id_company]);
            }

            if (!empty($search)) {
                $campaignQuery->andWhere(['like', 'c.campaign_name', $search]);
            }
            if (!empty($fecha_inicio)) {
                $campaignQuery->andWhere(['>=', 'c.start_date', $fecha_inicio]);
            }
            if (!empty($fecha_fin)) {
                $campaignQuery->andWhere(['<=', 'c.end_date', $fecha_fin]);
            }

            // ============================================
            // 🔥 QUERY PROMOCIONES EN PAPELERA
            // ============================================
            $promotionQuery = Promotion::find()
                ->alias('p')
                ->andWhere(['p.id_status' => $trashId])
                ->orderBy(['p.id_promotion' => SORT_DESC]);

            if ($user->isSuperAdmin() && !empty($empresaId)) {
                $promotionQuery->andWhere(['p.id_company' => $empresaId]);
            } elseif (!$user->isSuperAdmin()) {
                $promotionQuery->andWhere(['p.id_company' => $user->id_company]);
            }

            if (!empty($search)) {
                $promotionQuery->andWhere(['like', 'p.promotion_name', $search]);
            }
            if (!empty($fecha_inicio)) {
                $promotionQuery->andWhere(['>=', 'p.start_date', $fecha_inicio]);
            }
            if (!empty($fecha_fin)) {
                $promotionQuery->andWhere(['<=', 'p.end_date', $fecha_fin]);
            }

            // ============================================
            // 🔥 DataProviders
            // ============================================
            $campaignTrashProvider = new ActiveDataProvider([
                'query' => $campaignQuery,
                'pagination' => [
                    'pageSize' => 10,
                    'pageSizeParam' => 'per-page',
                    'pageParam' => 'page_campaigns',
                ],
                'sort' => ['defaultOrder' => ['id_campaign' => SORT_DESC]],
            ]);

            $promotionTrashProvider = new ActiveDataProvider([
                'query' => $promotionQuery,
                'pagination' => [
                    'pageSize' => 10,
                    'pageSizeParam' => 'per-page',
                    'pageParam' => 'page_promotions',
                ],
                'sort' => ['defaultOrder' => ['id_promotion' => SORT_DESC]],
            ]);

            // ============================================
            // 🔥 TOTAL
            // ============================================
            $totalTrash = $campaignTrashProvider->getTotalCount() + $promotionTrashProvider->getTotalCount();

            return $this->render('trash', [
                'campaignDataProvider'  => $campaignTrashProvider,
                'promotionDataProvider' => $promotionTrashProvider,
                'totalTrash'            => $totalTrash,
                'isAdmin'               => $user->isAdmin(),
                'isSuperAdmin'          => $user->isSuperAdmin(),
                'search'                => $search,
                'tipoFiltro'            => $tipoFiltro,
                'fecha_inicio'          => $fecha_inicio,
                'fecha_fin'             => $fecha_fin,
            ]);

        } catch (\Exception $e) {
            Yii::error('Error en actionTrash: ' . $e->getMessage(), 'marketing');
            Yii::$app->session->setFlash('error', 'Error al cargar la papelera: ' . $e->getMessage());
            return $this->redirect(['index']);
        }
    }

    // ============================================
    // 🔥 RESTAURAR (a "Inactivo")
    // ============================================
    public function actionRestore($id)
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            if (!$user->isAdmin() && !$user->isSuperAdmin()) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para restaurar elementos.');
                return $this->redirect(['trash']);
            }

            $model = Marketing::findOne(['id' => $id]);

            if (!$model) {
                Yii::$app->session->setFlash('error', 'Elemento no encontrado.');
                return $this->redirect(['trash']);
            }

            // 🔥 Verificar permisos
            if ($user->isSuperAdmin() && !empty($empresaId) && $model->id_company != $empresaId) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para restaurar este elemento.');
                return $this->redirect(['trash']);
            }

            if (!$user->isSuperAdmin() && $model->id_company != $user->id_company) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para restaurar este elemento.');
                return $this->redirect(['trash']);
            }

            $restoreId = $this->getRestoreStatusId();
            if (!$restoreId) {
                Yii::$app->session->setFlash('error', 'No se encontró el estado "' . self::RESTORE_STATUS . '".');
                return $this->redirect(['trash']);
            }

            $model->id_status = $restoreId;

            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', ucfirst($model->getTypeLabel()) . ' restaurada exitosamente.');
            } else {
                Yii::$app->session->setFlash('error', 'Error al restaurar el elemento.');
            }

        } catch (\Exception $e) {
            Yii::error('Error en actionRestore: ' . $e->getMessage(), 'marketing');
            Yii::$app->session->setFlash('error', 'Error al restaurar el elemento.');
        }

        return $this->redirect(['trash']);
    }

    // ============================================
    // CREAR → Redirige a VIEW
    // ============================================
    public function actionCreate()
    {
        $model = new Marketing();
        $user = Yii::$app->user->identity;
        $empresaId = Yii::$app->session->get('empresa_id');

        if ($user->isSuperAdmin() && empty($empresaId)) {
            Yii::$app->session->setFlash('warning', 'Por favor, selecciona una empresa para continuar.');
            return $this->redirect(['empresa/index']);
        }

        if ($user->isSuperAdmin()) {
            if (!empty($empresaId)) {
                $model->id_company = $empresaId;
            }
        } else {
            $model->id_company = $user->id_company;
        }

        $statusActivo = Status::find()->where(['status' => 'Activo'])->one();
        if ($statusActivo) {
            $model->id_status = $statusActivo->id_status;
        }

        $type = Yii::$app->request->get('type');
        if ($type && in_array($type, [Marketing::TYPE_CAMPAIGN, Marketing::TYPE_PROMOTION])) {
            $model->type = $type;
        }

        if ($model->load(Yii::$app->request->post())) {
            try {
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', ucfirst($model->getTypeLabel()) . ' creada exitosamente.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                Yii::$app->session->setFlash('error', 'Error al guardar: ' . $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    // ============================================
    // VER
    // ============================================
    public function actionView($id)
    {
        $user = Yii::$app->user->identity;
        $empresaId = Yii::$app->session->get('empresa_id');

        $model = Marketing::findOne(['id' => $id]);
        if (!$model) {
            throw new NotFoundHttpException('Elemento no encontrado.');
        }

        if ($user->isSuperAdmin() && !empty($empresaId) && $model->id_company != $empresaId) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para ver este elemento.');
            return $this->redirect(['index']);
        }

        if (!$user->isSuperAdmin() && $model->id_company != $user->id_company) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para ver este elemento.');
            return $this->redirect(['index']);
        }

        return $this->render('view', ['model' => $model]);
    }

    // ============================================
    // ACTUALIZAR → Redirige a VIEW
    // ============================================
    public function actionUpdate($id)
    {
        $user = Yii::$app->user->identity;
        $empresaId = Yii::$app->session->get('empresa_id');

        $model = Marketing::findOne(['id' => $id]);
        if (!$model) {
            throw new NotFoundHttpException('Elemento no encontrado.');
        }

        if ($user->isSuperAdmin() && !empty($empresaId) && $model->id_company != $empresaId) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para editar este elemento.');
            return $this->redirect(['index']);
        }

        if (!$user->isSuperAdmin() && $model->id_company != $user->id_company) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para editar este elemento.');
            return $this->redirect(['index']);
        }

        if ($model->load(Yii::$app->request->post())) {
            try {
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', ucfirst($model->getTypeLabel()) . ' actualizada exitosamente.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                Yii::$app->session->setFlash('error', 'Error al actualizar: ' . $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    // ============================================
    // 🔥 ELIMINAR → Mueve a PAPELERA (estado "Eliminado")
    // ============================================
    public function actionDelete($id)
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            if (!$user->isAdmin() && !$user->isSuperAdmin()) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para eliminar elementos.');
                return $this->redirect(['index']);
            }

            $model = Marketing::findOne(['id' => $id]);
            if (!$model) {
                throw new NotFoundHttpException('Elemento no encontrado.');
            }

            // 🔥 Verificar permisos
            if ($user->isSuperAdmin() && !empty($empresaId) && $model->id_company != $empresaId) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para eliminar este elemento.');
                return $this->redirect(['index']);
            }

            if (!$user->isSuperAdmin() && $model->id_company != $user->id_company) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para eliminar este elemento.');
                return $this->redirect(['index']);
            }

            $trashId = $this->getTrashStatusId();
            if (!$trashId) {
                Yii::$app->session->setFlash('error', 'No se encontró el estado "Eliminado" en la base de datos.');
                return $this->redirect(['index']);
            }

            $model->id_status = $trashId;

            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', ucfirst($model->getTypeLabel()) . ' movida a la papelera.');
            } else {
                Yii::$app->session->setFlash('error', 'Error al mover el elemento a la papelera.');
            }

        } catch (\Exception $e) {
            Yii::error('Error en actionDelete: ' . $e->getMessage(), 'marketing');
            Yii::$app->session->setFlash('error', 'Error al eliminar el elemento: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }
}