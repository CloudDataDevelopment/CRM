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
                    'delete' => ['POST'],
                ],
            ],
        ];
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

            // ============================================
            // QUERY BASE PARA CAMPAÑAS (PK: id_campaign)
            // ============================================
            $campaignQuery = Campaign::find()
                ->alias('c')
                ->orderBy(['c.id_campaign' => SORT_DESC]);

            if ($user->isSuperAdmin() && !empty($empresaId)) {
                $campaignQuery->andWhere(['c.id_company' => $empresaId]);
            } elseif (!$user->isSuperAdmin()) {
                $campaignQuery->andWhere(['c.id_company' => $user->id_company]);
            }

            // ============================================
            // QUERY BASE PARA PROMOCIONES (PK: id_promotion)
            // ============================================
            $promotionQuery = Promotion::find()
                ->alias('p')
                ->orderBy(['p.id_promotion' => SORT_DESC]);

            if ($user->isSuperAdmin() && !empty($empresaId)) {
                $promotionQuery->andWhere(['p.id_company' => $empresaId]);
            } elseif (!$user->isSuperAdmin()) {
                $promotionQuery->andWhere(['p.id_company' => $user->id_company]);
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
            // TOTALES
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

            $totalCampaigns = $totalCampaignsQuery->count();
            $totalPromotions = $totalPromotionsQuery->count();

            // ============================================
            // ACTIVOS
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
            // SEARCH MODEL
            // ============================================
            $searchModel = new MarketingSearch();

            $canCreate = $user->isAdmin() || $user->isSuperAdmin();

            return $this->render('index', [
                'searchModel' => $searchModel,
                'campaignDataProvider' => $campaignDataProvider,
                'promotionDataProvider' => $promotionDataProvider,
                'totalCampaigns' => $totalCampaigns,
                'totalPromotions' => $totalPromotions,
                'activeCampaigns' => $activeCampaigns,
                'activePromotions' => $activePromotions,
                'companyList' => [],
                'statusList' => [],
                'canCreate' => $canCreate,
                'isAdmin' => $user->isAdmin(),
                'isSuperAdmin' => $user->isSuperAdmin(),
            ]);

        } catch (\Exception $e) {
            Yii::error('❌ [MARKETING] ERROR: ' . $e->getMessage(), 'marketing-debug');
            Yii::error('❌ [MARKETING] Archivo: ' . $e->getFile() . ':' . $e->getLine(), 'marketing-debug');
            throw $e;
        }
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
    // ELIMINAR → Redirige a INDEX
    // ============================================
    public function actionDelete($id)
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            $model = Marketing::findOne(['id' => $id]);
            if (!$model) {
                throw new NotFoundHttpException('Elemento no encontrado.');
            }

            if ($user->isSuperAdmin() && !empty($empresaId) && $model->id_company != $empresaId) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para eliminar este elemento.');
                return $this->redirect(['index']);
            }

            if (!$user->isSuperAdmin() && $model->id_company != $user->id_company) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para eliminar este elemento.');
                return $this->redirect(['index']);
            }

            if ($model->delete()) {
                Yii::$app->session->setFlash('success', ucfirst($model->getTypeLabel()) . ' eliminada exitosamente.');
            }
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Error al eliminar: ' . $e->getMessage());
        }
        return $this->redirect(['index']);
    }
}