<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
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

            // 🔥 Verificar empresa seleccionada para Super Admin
            if ($user->isSuperAdmin() && empty($empresaId)) {
                Yii::$app->session->setFlash('warning', 'Por favor, selecciona una empresa para continuar.');
                return $this->redirect(['empresa/index']);
            }

            $searchModel = new MarketingSearch();

            $params = Yii::$app->request->queryParams;

            $campaignDataProvider = $searchModel->searchCampaigns($params);
            $promotionDataProvider = $searchModel->searchPromotions($params);

            // 🔥 Obtener totales con filtro de empresa
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

            $companyList = [];
            if ($user->isSuperAdmin()) {
                $companyList = Company::find()
                    ->select(['name', 'id_company'])
                    ->indexBy('id_company')
                    ->column();
            }

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
                'companyList' => $companyList,
                'statusList' => $statusList,
                'canCreate' => $canCreate,
                'isAdmin' => $user->isAdmin(),
                'isSuperAdmin' => $user->isSuperAdmin(),
            ]);

        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al cargar el módulo de marketing');
            return $this->render('index', [
                'searchModel' => new MarketingSearch(),
                'campaignDataProvider' => new \yii\data\ArrayDataProvider(['allModels' => []]),
                'promotionDataProvider' => new \yii\data\ArrayDataProvider(['allModels' => []]),
                'totalCampaigns' => 0,
                'totalPromotions' => 0,
                'activeCampaigns' => 0,
                'activePromotions' => 0,
                'companyList' => [],
                'statusList' => [],
                'canCreate' => false,
                'isAdmin' => false,
                'isSuperAdmin' => false,
            ]);
        }
    }

    // ============================================
    // CREAR (Campaña o Promoción)
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

        // Asignar empresa
        if ($user->isSuperAdmin()) {
            if (!empty($empresaId)) {
                $model->id_company = $empresaId;
            }
        } else {
            $model->id_company = $user->id_company;
        }

        // Asignar estado por defecto
        $statusActivo = Status::find()->where(['status' => 'Activo'])->one();
        if ($statusActivo) {
            $model->id_status = $statusActivo->id_status;
        }

        // Si el tipo viene por GET (desde el botón)
        $type = Yii::$app->request->get('type');
        if ($type && in_array($type, [Marketing::TYPE_CAMPAIGN, Marketing::TYPE_PROMOTION])) {
            $model->type = $type;
        }

        if ($model->load(Yii::$app->request->post())) {
            try {
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', ucfirst($model->getTypeLabel()) . ' creada exitosamente.');
                    return $this->redirect(['index']);
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

        // 🔥 VERIFICAR PERMISOS
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
    // ACTUALIZAR
    // ============================================
    public function actionUpdate($id)
    {
        $user = Yii::$app->user->identity;
        $empresaId = Yii::$app->session->get('empresa_id');

        $model = Marketing::findOne(['id' => $id]);
        if (!$model) {
            throw new NotFoundHttpException('Elemento no encontrado.');
        }

        // 🔥 VERIFICAR PERMISOS
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
                    return $this->redirect(['index']);
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
    // ELIMINAR
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

            // 🔥 VERIFICAR PERMISOS
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

    // ============================================
    // FINDERS
    // ============================================
    protected function findCampaign($id)
    {
        $model = Campaign::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Campaña no encontrada.');
        }
        return $model;
    }

    protected function findPromotion($id)
    {
        $model = Promotion::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Promoción no encontrada.');
        }
        return $model;
    }
}