<?php

use yii\helpers\Html;
use yii\helpers\Url;
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

// 🔎 Variables de filtro
$search = isset($search) ? $search : '';
$status = isset($status) ? $status : '';
$type = isset($type) ? $type : '';
$fecha_inicio = isset($fecha_inicio) ? $fecha_inicio : '';
$fecha_fin = isset($fecha_fin) ? $fecha_fin : '';
$statusList = isset($statusList) ? $statusList : \app\models\Marketing::getStatusList();
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
                        '<i class="fas fa-trash"></i> Papelera',
                        ['trash'],
                        ['class' => 'btn btn-outline-danger btn-sm btn-header-action']
                    ) ?>
                    <?= Html::a(
                        '<i class="fas fa-plus-circle"></i> Nueva Campaña o Promoción',
                        ['create'],
                        ['class' => 'btn btn-primary btn-sm btn-header-action']
                    ) ?>
                <?php endif; ?>
            </div>
        </div>

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

        <!-- 🔎 FILTROS -->
        <div class="card marketing-filtros-card mb-3">
            <div class="card-body">
                <form method="get" action="<?= Url::to(['marketing/index']) ?>" id="form-filtros-marketing">
                    <div class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label class="form-label fw-bold mb-0 small">Filtrar</label>
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control form-control-sm" name="search"
                                   placeholder="Buscar..."
                                   value="<?= Html::encode($search) ?>"
                                   id="marketing-search-input">
                        </div>

                        <div class="col-md-2">
                            <select class="form-select form-select-sm" name="status" id="marketing-status-select">
                                <option value="">Todos los estados</option>
                                <?php foreach ($statusList as $id => $nombre): ?>
                                    <option value="<?= Html::encode($nombre) ?>" <?= $status === $nombre ? 'selected' : '' ?>>
                                        <?= ucfirst($nombre) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <input type="date" class="form-control form-control-sm" name="fecha_inicio"
                                   value="<?= Html::encode($fecha_inicio) ?>"
                                   id="marketing-fecha-inicio"
                                   title="Desde">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control form-control-sm" name="fecha_fin"
                                   value="<?= Html::encode($fecha_fin) ?>"
                                   id="marketing-fecha-fin"
                                   title="Hasta">
                        </div>

                        <div class="col-auto d-flex gap-1">
                            <button type="submit" class="btn btn-primary btn-sm" id="marketing-btn-filtrar" title="Filtrar">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="<?= Url::to(['marketing/index']) ?>" class="btn btn-secondary btn-sm" title="Limpiar">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </form>
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

<script>
(function() {
    function submitForm() {
        document.getElementById('form-filtros-marketing').submit();
    }

    var searchInput = document.getElementById('marketing-search-input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); submitForm(); }
        });
    }

    ['marketing-status-select', 'marketing-fecha-inicio', 'marketing-fecha-fin']
        .forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener('change', submitForm);
        });

    var btnFiltrar = document.getElementById('marketing-btn-filtrar');
    if (btnFiltrar) {
        btnFiltrar.addEventListener('click', function(e) {
            e.preventDefault();
            submitForm();
        });
    }
})();
</script>