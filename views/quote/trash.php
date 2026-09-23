<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\StringHelper;
use yii\widgets\LinkPager;

$this->title = 'Papelera de Cotizaciones';
$this->params['breadcrumbs'][] = ['label' => 'Cotizaciones', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/quote.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerCssFile('@web/css/dashboard.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;
$quotes = isset($quotes) ? $quotes : [];
$dataProvider = isset($dataProvider) ? $dataProvider : null;
$totalQuotes = isset($totalQuotes) ? $totalQuotes : 0;
$search = isset($search) ? $search : '';
$statusOptions = isset($statusOptions) ? $statusOptions : [];

$totalCount = $dataProvider ? $dataProvider->getTotalCount() : 0;
$currentCount = $dataProvider ? $dataProvider->getCount() : 0;
$pageCount = $dataProvider ? $dataProvider->getPagination()->getPageCount() : 1;
$currentPage = $dataProvider ? $dataProvider->getPagination()->getPage() + 1 : 1;
?>

<div class="quote-container">
    <div class="quote-index">

        <!-- ============================================ -->
        <!-- HEADER                                       -->
        <!-- ============================================ -->
        <div class="quote-header">
            <div class="header-left">
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Ventas</span><span class="separator">›</span>
                    <span>Cotizaciones</span><span class="separator">›</span>
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
                    '<i class="fas fa-arrow-left"></i> Volver a Cotizaciones',
                    ['index'],
                    ['class' => 'btn btn-secondary btn-sm']
                ) ?>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- ALERTA INFORMATIVA                            -->
        <!-- ============================================ -->
        <div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
            <i class="fas fa-info-circle me-2" style="font-size: 1.2rem;"></i>
            <div>
                <strong>Papelera:</strong> Aquí se encuentran las cotizaciones marcadas como <strong>canceladas</strong>.
                Puedes restaurarlas para que vuelvan al listado activo.
            </div>
        </div>

        <!-- ============================================ -->
        <!-- MÉTRICAS                                      -->
        <!-- ============================================ -->
        <div class="row g-2 mb-3">
            <div class="col-md-4 col-6">
                <div class="card stat-card stat-card-danger dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2">
                            <i class="fas fa-trash"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $totalCount ?></div>
                            <div class="stat-label">En Papelera</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- FILTROS                                       -->
        <!-- ============================================ -->
        <div class="card filtros-card mb-3">
            <div class="card-body">
                <form method="get" action="<?= Url::to(['quote/trash']) ?>" id="form-filtros">
                    <div class="row align-items-end g-2">
                        <div class="col-md-1">
                            <label class="form-label fw-bold mb-0">Buscar</label>
                        </div>
                        <div class="col-md-5">
                            <input type="text" 
                                   class="form-control" 
                                   name="search" 
                                   placeholder="Buscar por cliente, teléfono, observaciones..." 
                                   value="<?= Html::encode($search) ?>" 
                                   id="search-input">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="<?= Url::to(['quote/trash']) ?>" 
                               class="btn btn-secondary w-100" 
                               title="Limpiar filtros">
                                <i class="fas fa-undo"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- TABLA                                         -->
        <!-- ============================================ -->
        <div class="card table-card">
            <div class="card-header">
                <div class="header-left">
                    <i class="fas fa-trash text-danger"></i>
                    <span>Cotizaciones en Papelera</span>
                    <span class="badge bg-danger ms-2"><?= $totalCount ?></span>
                </div>
                <div class="header-right">
                    <span class="badge bg-secondary">
                        Página <?= $currentPage ?> de <?= $pageCount ?>
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="quotes-table">
                        <thead class="table-light">
                            <tr>
                                <th width="50">#</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Motivo</th>
                                <th class="text-center" width="180">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($quotes)): ?>
                                <?php foreach ($quotes as $index => $quote): ?>
                                    <?php
                                    $lead = $quote->lead;
                                    $leadName = $lead ? $lead->name . ' ' . $lead->lastname : 'Lead eliminado';
                                    $leadPhone = $lead ? $lead->phone : null;
                                    $statusName = $quote->getStatusName();
                                    $badgeClass = $quote->getStatusBadgeClass();
                                    ?>
                                    <tr class="quote-row" data-id="<?= $quote->id_quote ?>">
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <strong><?= Html::encode($leadName) ?></strong>
                                            <?php if ($leadPhone): ?>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-phone"></i> <?= Html::encode($leadPhone) ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($quote->date_quote)) ?>
                                            <?php if (!empty($quote->hour_quote)): ?>
                                                <br>
                                                <small class="text-muted">
                                                    <?= date('H:i', strtotime($quote->hour_quote)) ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong>$<?= number_format($quote->total_amount ?? 0, 0, '.', ',') ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $badgeClass ?>">
                                                <?= Html::encode($statusName) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $notas = $quote->getNotes();
                                            echo $notas ? StringHelper::truncate(Html::encode($notas), 50, '...') : '<span class="text-muted">Sin motivo</span>';
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <?= Html::a(
                                                    '<i class="fas fa-undo"></i> Restaurar',
                                                    ['restore', 'id' => $quote->id_quote],
                                                    [
                                                        'class' => 'btn btn-sm btn-success',
                                                        'title' => 'Restaurar cotización',
                                                        'data' => [
                                                            'confirm' => '¿Restaurar esta cotización? Volverá al listado activo.',
                                                            'method' => 'post',
                                                        ],
                                                    ]
                                                ) ?>
                                                <?= Html::a(
                                                    '<i class="fas fa-eye"></i>',
                                                    ['details', 'id' => $quote->id_quote],
                                                    [
                                                        'class' => 'btn btn-sm btn-info',
                                                        'title' => 'Ver detalles',
                                                    ]
                                                ) ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="fas fa-trash fa-3x d-block mb-3 text-muted"></i>
                                        <p class="mb-2">No hay cotizaciones en la papelera.</p>
                                        <small class="text-muted d-block mb-3">
                                            Las cotizaciones canceladas aparecerán aquí.
                                        </small>
                                        <?= Html::a(
                                            '<i class="fas fa-arrow-left"></i> Volver a Cotizaciones',
                                            ['index'],
                                            ['class' => 'btn btn-sm btn-primary']
                                        ) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($dataProvider && $dataProvider->pagination->pageCount > 1): ?>
                <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        Mostrando <?= $currentCount ?> de <?= $totalCount ?> cotizaciones
                    </small>
                    <?= LinkPager::widget([
                        'pagination' => $dataProvider->pagination,
                        'options' => ['class' => 'pagination pagination-sm mb-0'],
                        'linkOptions' => ['class' => 'page-link'],
                        'prevPageLabel' => '<i class="fas fa-chevron-left"></i>',
                        'nextPageLabel' => '<i class="fas fa-chevron-right"></i>',
                        'firstPageLabel' => '<i class="fas fa-angle-double-left"></i>',
                        'lastPageLabel' => '<i class="fas fa-angle-double-right"></i>',
                        'maxButtonCount' => 5,
                    ]) ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
// 🔥 Búsqueda con Enter
document.getElementById('search-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('form-filtros').submit();
    }
});
</script>