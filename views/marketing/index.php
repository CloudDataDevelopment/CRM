<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap5\Tabs;

$this->title = 'Marketing';
$this->params['breadcrumbs'][] = $this->title;

// Registrar CSS del marketing
$this->registerCssFile('@web/css/marketing.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
]);

$user = Yii::$app->user->identity;
$isAdmin = $user->isAdmin() || $user->isSuperAdmin();
$canCreate = $isAdmin;
?>

<div class="marketing-index">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><i class="fas fa-bullhorn text-primary me-2"></i> <?= Html::encode($this->title) ?></h1>
        <div class="header-actions d-flex gap-2">
            <?= $this->render('/layouts/_report_button') ?>
            <?php if ($canCreate): ?>
                <?= Html::a('<i class="fas fa-plus-circle"></i> Nueva Campaña o Promoción', ['create'], ['class' => 'btn btn-primary']) ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tarjetas de métricas -->
    <div class="row g-2 mb-3">
        <!-- Campañas -->
        <div class="col-xl-3 col-md-6 col-6">
            <div class="card stat-card stat-card-primary dashboard-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-2">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number"><?= $totalCampaigns ?></div>
                        <div class="stat-label">Total Campañas</div>
                        <div class="stat-sub">
                            <span class="text-success"><i class="fas fa-check-circle"></i> <?= $activeCampaigns ?> activas</span>
                            <span class="text-danger ms-2"><i class="fas fa-times-circle"></i> <?= $totalCampaigns - $activeCampaigns ?> inactivas</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Promociones -->
        <div class="col-xl-3 col-md-6 col-6">
            <div class="card stat-card stat-card-success dashboard-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-2">
                        <i class="fas fa-gift"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number"><?= $totalPromotions ?></div>
                        <div class="stat-label">Total Promociones</div>
                        <div class="stat-sub">
                            <span class="text-success"><i class="fas fa-check-circle"></i> <?= $activePromotions ?> activas</span>
                            <span class="text-danger ms-2"><i class="fas fa-times-circle"></i> <?= $totalPromotions - $activePromotions ?> inactivas</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Marketing -->
        <div class="col-xl-3 col-md-6 col-6">
            <div class="card stat-card stat-card-warning dashboard-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-2">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number"><?= $totalCampaigns + $totalPromotions ?></div>
                        <div class="stat-label">Total Marketing</div>
                        <div class="stat-sub">
                            <span class="text-success"><i class="fas fa-check-circle"></i> <?= $activeCampaigns + $activePromotions ?> activos</span>
                            <span class="text-danger ms-2"><i class="fas fa-times-circle"></i> <?= ($totalCampaigns + $totalPromotions) - ($activeCampaigns + $activePromotions) ?> inactivos</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Porcentaje de Actividad -->
        <div class="col-xl-3 col-md-6 col-6">
            <div class="card stat-card stat-card-info dashboard-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-2">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="flex-grow-1">
                        <?php
                        $totalItems = $totalCampaigns + $totalPromotions;
                        $activeItems = $activeCampaigns + $activePromotions;
                        $porcentajeActividad = $totalItems > 0 ? round(($activeItems / $totalItems) * 100) : 0;
                        ?>
                        <div class="stat-number"><?= $porcentajeActividad ?>%</div>
                        <div class="stat-label">Tasa de Actividad</div>
                        <div class="stat-sub">
                            <span class="text-success"><i class="fas fa-arrow-up"></i> <?= $activeItems ?> activos</span>
                            <span class="text-muted ms-2">de <?= $totalItems ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs para Campañas y Promociones -->
    <?php Pjax::begin(['id' => 'marketing-pjax']); ?>
    
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
            'options' => ['class' => 'mb-3'],
        ]); ?>
    </div>
    
    <?php Pjax::end(); ?>
</div>