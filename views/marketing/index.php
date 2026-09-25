<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\bootstrap5\Tabs;

$this->title = 'Marketing';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/marketing.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
]);

$user = Yii::$app->user->identity;
$isAdmin = $user->isAdmin() || $user->isSuperAdmin();
$canCreate = $isAdmin;

$totalItems = $totalCampaigns + $totalPromotions;
$activeItems = $activeCampaigns + $activePromotions;
$porcentajeActividad = $totalItems > 0 ? round(($activeItems / $totalItems) * 100) : 0;
?>

<div class="marketing-index">
    <div class="marketing-wrapper">

        <!-- HEADER -->
        <div class="marketing-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Marketing</span><span class="separator">›</span>
                    <span class="current"><?= Html::encode($this->title) ?></span>
                </div>
                <h1 class="page-title">
                    <?= Html::encode($this->title) ?>
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= $this->render('/layouts/_report_button') ?>
                <?php if ($canCreate): ?>
                    <?= Html::a(
                        '<i class="fas fa-plus-circle"></i> Nueva Campaña o Promoción',
                        ['create'],
                        ['class' => 'btn btn-primary btn-sm btn-header-action']
                    ) ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- MENSAJE DE ÉXITO -->
        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i>
                <?= Yii::$app->session->getFlash('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- MÉTRICAS -->
        <div class="row g-2 mb-3">
            <div class="col-xl-3 col-md-6 col-6">
                <div class="card stat-card stat-card-primary dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-bullhorn"></i></div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $totalCampaigns ?></div>
                            <div class="stat-label">Total Campañas</div>
                            <div class="stat-sub">
                                <span class="text-success"><i class="fas fa-check-circle"></i> <?= $activeCampaigns ?> activas</span>
                                <span class="text-danger"><i class="fas fa-times-circle"></i> <?= $totalCampaigns - $activeCampaigns ?> inactivas</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 col-6">
                <div class="card stat-card stat-card-success dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-gift"></i></div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $totalPromotions ?></div>
                            <div class="stat-label">Total Promociones</div>
                            <div class="stat-sub">
                                <span class="text-success"><i class="fas fa-check-circle"></i> <?= $activePromotions ?> activas</span>
                                <span class="text-danger"><i class="fas fa-times-circle"></i> <?= $totalPromotions - $activePromotions ?> inactivas</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 col-6">
                <div class="card stat-card stat-card-warning dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-bullseye"></i></div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $totalItems ?></div>
                            <div class="stat-label">Total Marketing</div>
                            <div class="stat-sub">
                                <span class="text-success"><i class="fas fa-check-circle"></i> <?= $activeItems ?> activos</span>
                                <span class="text-danger"><i class="fas fa-times-circle"></i> <?= $totalItems - $activeItems ?> inactivos</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 col-6">
                <div class="card stat-card stat-card-info dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-chart-pie"></i></div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $porcentajeActividad ?>%</div>
                            <div class="stat-label">Tasa de Actividad</div>
                            <div class="stat-sub">
                                <span class="text-success"><i class="fas fa-arrow-up"></i> <?= $activeItems ?> activos</span>
                                <span class="text-muted">de <?= $totalItems ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABS -->
        <?php Pjax::begin(['id' => 'marketing-pjax', 'enablePushState' => false]); ?>

        <div class="marketing-tabs">
            <?= Tabs::widget([
                'items' => [
                    [
                        'label' => '<i class="fas fa-bullhorn"></i> Campañas',
                        'content' => $this->render('_campaigns_table', [
                            'dataProvider' => $campaignDataProvider,
                            'searchModel' => $searchModel,
                            'isAdmin' => $canCreate,
                        ]),
                        'active' => true,
                    ],
                    [
                        'label' => '<i class="fas fa-gift"></i> Promociones',
                        'content' => $this->render('_promotions_table', [
                            'dataProvider' => $promotionDataProvider,
                            'searchModel' => $searchModel,
                            'isAdmin' => $canCreate,
                        ]),
                    ],
                ],
                'encodeLabels' => false,
                'options' => ['class' => 'mb-0'],
            ]); ?>
        </div>

        <?php Pjax::end(); ?>

    </div>
</div>