<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = 'Papelera de Evaluaciones';
$this->params['breadcrumbs'][] = ['label' => 'Evaluaciones', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/evaluaciones.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

$dataProvider = isset($dataProvider) ? $dataProvider : null;
$reports = isset($reports) ? $reports : [];
$totalReports = isset($totalReports) ? $totalReports : count($reports);
$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isAgent = isset($isAgent) ? $isAgent : false;
$isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;
$search = isset($search) ? $search : '';
?>

<div class="quote-container">
    <div class="quote-index">

        <!-- HEADER -->
        <div class="quote-header">
            <div class="header-left">
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Evaluaciones</span><span class="separator">›</span>
                    <span class="current">Papelera</span>
                </div>
                <h1 class="page-title">
                    <i class="fas fa-trash text-danger me-2"></i>
                    <?= Html::encode($this->title) ?>
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= Html::a('<i class="fas fa-arrow-left"></i> Volver a Evaluaciones', ['index'], ['class' => 'btn btn-secondary btn-sm']) ?>
            </div>
        </div>

        <!-- ALERTA -->
        <div class="alert alert-warning" role="alert" style="border-radius: 8px; border-left: 4px solid #f6c23e;">
            <i class="fas fa-info-circle"></i>
            <strong>Estas evaluaciones están en la papelera.</strong>
            Puedes restaurarlas para que vuelvan al listado principal.
        </div>

        <!-- MÉTRICAS -->
        <div class="row g-2 mb-3">
            <div class="col-md-4 col-6">
                <div class="card stat-card stat-card-danger dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-trash"></i></div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $totalReports ?></div>
                            <div class="stat-label">En Papelera</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="card filtros-card mb-3">
            <div class="card-body">
                <form method="get" action="<?= Url::to(['report/trash']) ?>" id="form-filtros">
                    <div class="row align-items-end">
                        <div class="col-md-1"><label class="form-label fw-bold mb-0">Filtrar</label></div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" placeholder="Buscar..." value="<?= Html::encode($search) ?>" id="search-input">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100" id="btn-filtrar"><i class="fas fa-search"></i></button>
                        </div>
                        <div class="col-md-2">
                            <a href="<?= Url::to(['report/trash']) ?>" class="btn btn-secondary w-100" title="Limpiar"><i class="fas fa-undo"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- TABLA -->
        <div class="card table-card">
            <div class="card-header">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-trash"></i>
                    <span>Evaluaciones Eliminadas</span>
                    <span class="badge bg-danger ms-2"><?= $dataProvider ? $dataProvider->getTotalCount() : 0 ?></span>
                </div>
                <div>
                    <span class="badge bg-secondary">
                        Página <?= $dataProvider ? $dataProvider->getPagination()->getPage() + 1 : 1 ?> de <?= $dataProvider ? $dataProvider->getPagination()->getPageCount() : 1 ?>
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Lead</th>
                                <th>Tipo</th>
                                <th>Fecha</th>
                                <?php if ($isSuperAdmin): ?><th>Empresa</th><?php endif; ?>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($reports)): ?>
                                <?php foreach ($reports as $index => $report): ?>
                                    <tr class="quote-row" data-id="<?= $report->id_report ?>" style="opacity: 0.85;">
                                        <td><?= $dataProvider ? $dataProvider->getPagination()->getOffset() + $index + 1 : $index + 1 ?></td>
                                        <td><strong><?= Html::encode($report->report_name) ?></strong></td>
                                        <td>
                                            <?php if ($report->lead): ?>
                                                <span class="lead-badge">
                                                    <i class="fas fa-user"></i>
                                                    <?= Html::encode($report->lead->name . ' ' . $report->lead->lastname) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">Sin lead</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= Html::encode($report->report_type ?? '-') ?></td>
                                        <td><?= $report->date_report ? date('d/m/Y', strtotime($report->date_report)) : '-' ?></td>
                                        <?php if ($isSuperAdmin): ?>
                                            <td><?= $report->company ? Html::encode($report->company->name) : '-' ?></td>
                                        <?php endif; ?>
                                        <td>
                                            <span class="badge bg-<?= $report->getStatusBadgeClass() ?>">
                                                <?= $report->getStatusName() ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <?php if ($isAdmin || $isSuperAdmin): ?>
                                                    <?= Html::a('<i class="fas fa-undo"></i>', ['restore', 'id' => $report->id_report], [
                                                        'class' => 'btn btn-success btn-sm btn-action',
                                                        'title' => 'Restaurar',
                                                        'data' => [
                                                            'confirm' => '¿Restaurar esta evaluación?',
                                                            'method' => 'post',
                                                        ],
                                                    ]) ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?= $isSuperAdmin ? 8 : 7 ?>" class="text-center text-muted py-5">
                                        <i class="fas fa-inbox fa-3x d-block mb-3"></i>
                                        <p class="mb-2">La papelera está vacía</p>
                                        <small>Las evaluaciones que muevas a la papelera aparecerán aquí.</small>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PAGINACIÓN -->
            <?php if ($dataProvider && $dataProvider->pagination->pageCount > 1): ?>
                <div class="card-footer">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <small class="text-muted">
                                Mostrando <?= count($reports) ?> de <?= $totalReports ?> evaluaciones
                                (página <?= $dataProvider->pagination->page + 1 ?> de <?= $dataProvider->pagination->pageCount ?>)
                            </small>
                        </div>
                        <div class="col-md-6">
                            <?= LinkPager::widget([
                                'pagination' => $dataProvider->pagination,
                                'options' => ['class' => 'pagination justify-content-end mb-0'],
                                'linkOptions' => ['class' => 'page-link'],
                                'prevPageLabel' => '<i class="fas fa-chevron-left"></i>',
                                'nextPageLabel' => '<i class="fas fa-chevron-right"></i>',
                                'maxButtonCount' => 5,
                                'activePageCssClass' => 'active',
                                'disabledPageCssClass' => 'disabled',
                            ]) ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') document.getElementById('form-filtros').submit();
        });
    }
    var btnFiltrar = document.getElementById('btn-filtrar');
    if (btnFiltrar) {
        btnFiltrar.addEventListener('click', function (e) {
            e.preventDefault();
            document.getElementById('form-filtros').submit();
        });
    }
});
</script>