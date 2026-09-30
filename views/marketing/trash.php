<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\bootstrap5\Tabs;
use yii\widgets\LinkPager;

$this->title = 'Papelera de Marketing';
$this->params['breadcrumbs'][] = ['label' => 'Marketing', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/marketing.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
]);

$isAdmin       = $isAdmin ?? false;
$isSuperAdmin  = $isSuperAdmin ?? false;
$totalTrash    = $totalTrash ?? 0;
$search        = $search ?? '';
$tipoFiltro    = $tipoFiltro ?? '';
$fecha_inicio  = $fecha_inicio ?? '';
$fecha_fin     = $fecha_fin ?? '';
$campaignDataProvider  = $campaignDataProvider ?? null;
$promotionDataProvider = $promotionDataProvider ?? null;
?>

<div class="marketing-index">
    <div class="marketing-wrapper">

        <!-- HEADER -->
        <div class="marketing-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Marketing</span><span class="separator">›</span>
                    <span class="current">Papelera</span>
                </div>
                <h1 class="page-title">
                    <i class="fas fa-trash text-danger me-2"></i>
                    <?= Html::encode($this->title) ?>
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= Html::a(
                    '<i class="fas fa-arrow-left"></i> Volver a Marketing',
                    ['index'],
                    ['class' => 'btn btn-secondary btn-sm btn-header-action']
                ) ?>
            </div>
        </div>

        <!-- ALERTA INFORMATIVA -->
        <div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
            <i class="fas fa-info-circle me-2" style="font-size: 1.2rem;"></i>
            <div>
                <strong>Papelera:</strong> Aquí se encuentran las campañas y promociones con estado <strong>"Eliminado"</strong>.
                Puedes restaurarlas para que vuelvan al listado principal con estado "Inactivo".
            </div>
        </div>

        <!-- MÉTRICAS -->
        <div class="row g-2 mb-3">
            <div class="col-md-4 col-6">
                <div class="card stat-card stat-card-danger dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2">
                            <i class="fas fa-trash"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $totalTrash ?></div>
                            <div class="stat-label">En Papelera</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 🔎 FILTROS -->
        <div class="card marketing-filtros-card mb-3">
            <div class="card-body">
                <form method="get" action="<?= Url::to(['marketing/trash']) ?>" id="form-filtros-trash">
                    <div class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label class="form-label fw-bold mb-0 small">Buscar</label>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control form-control-sm" name="search"
                                   placeholder="Buscar..."
                                   value="<?= Html::encode($search) ?>"
                                   id="trash-search-input">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control form-control-sm" name="fecha_inicio"
                                   value="<?= Html::encode($fecha_inicio) ?>"
                                   id="trash-fecha-inicio"
                                   title="Desde">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control form-control-sm" name="fecha_fin"
                                   value="<?= Html::encode($fecha_fin) ?>"
                                   id="trash-fecha-fin"
                                   title="Hasta">
                        </div>
                        <div class="col-auto d-flex gap-1">
                            <button type="submit" class="btn btn-primary btn-sm" id="trash-btn-filtrar" title="Filtrar">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="<?= Url::to(['marketing/trash']) ?>" class="btn btn-secondary btn-sm" title="Limpiar">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- TABS -->
        <?php Pjax::begin(['id' => 'marketing-trash-pjax', 'enablePushState' => false]); ?>

        <div class="marketing-tabs">
            <?= Tabs::widget([
                'items' => [
                    [
                        'label' => '<i class="fas fa-bullhorn"></i> Campañas Eliminadas',
                        'content' => $this->render('_campaigns_trash_table', [
                            'dataProvider' => $campaignDataProvider,
                            'isAdmin' => $isAdmin,
                        ]),
                        'active' => true,
                    ],
                    [
                        'label' => '<i class="fas fa-gift"></i> Promociones Eliminadas',
                        'content' => $this->render('_promotions_trash_table', [
                            'dataProvider' => $promotionDataProvider,
                            'isAdmin' => $isAdmin,
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
        document.getElementById('form-filtros-trash').submit();
    }

    var searchInput = document.getElementById('trash-search-input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); submitForm(); }
        });
    }

    ['trash-fecha-inicio', 'trash-fecha-fin'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('change', submitForm);
    });

    var btnFiltrar = document.getElementById('trash-btn-filtrar');
    if (btnFiltrar) {
        btnFiltrar.addEventListener('click', function(e) {
            e.preventDefault();
            submitForm();
        });
    }
})();
</script>