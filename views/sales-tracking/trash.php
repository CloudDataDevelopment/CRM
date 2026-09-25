<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = 'Papelera de Seguimientos';
$this->params['breadcrumbs'][] = ['label' => 'Seguimientos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// 🔥 Solo sales-tracking.css (ya incluye los estilos de papelera)
$this->registerCssFile('@web/css/sales-tracking.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

$dataProvider = isset($dataProvider) ? $dataProvider : null;
$trackings = isset($trackings) ? $trackings : [];
$totalTrackings = isset($totalTrackings) ? $totalTrackings : count($trackings);
$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isAgent = isset($isAgent) ? $isAgent : false;
$isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;
$search = isset($search) ? $search : '';
?>

<!-- 🔥 CONTENEDOR PRINCIPAL CON SOMBRA -->
<div class="sales-tracking-trash">
    <div class="sales-tracking-wrapper">

        <!-- HEADER -->
        <div class="sales-tracking-header">
            <div class="header-left">
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Seguimientos</span><span class="separator">›</span>
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
                    '<i class="fas fa-arrow-left"></i> Volver a Seguimientos',
                    ['index'],
                    ['class' => 'btn btn-secondary btn-sm btn-tracking-action']
                ) ?>
            </div>
        </div>

        <!-- ALERTA INFORMATIVA -->
        <div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
            <i class="fas fa-info-circle me-2" style="font-size: 1.2rem;"></i>
            <div>
                <strong>Papelera:</strong> Aquí se encuentran los seguimientos marcados como <strong>eliminados</strong>.
                Puedes restaurarlos para que vuelvan al listado activo.
            </div>
        </div>

        <!-- MÉTRICAS -->
        <div class="row g-2 mb-3">
            <div class="col-md-4 col-6">
                <div class="card tracking-stat-card tracking-stat-danger tracking-dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2">
                            <i class="fas fa-trash"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $totalTrackings ?></div>
                            <div class="stat-label">En Papelera</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="card tracking-filtros-card mb-3">
            <div class="card-body">
                <form method="get" action="<?= Url::to(['sales-tracking/trash']) ?>" id="form-filtros">
                    <div class="row align-items-end g-2">
                        <div class="col-md-1">
                            <label class="form-label fw-bold mb-0">Buscar</label>
                        </div>
                        <div class="col-md-5">
                            <input type="text"
                                   class="form-control"
                                   name="search"
                                   placeholder="Buscar por lead, teléfono, comentarios..."
                                   value="<?= Html::encode($search) ?>"
                                   id="search-input">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="<?= Url::to(['sales-tracking/trash']) ?>"
                               class="btn btn-secondary w-100"
                               title="Limpiar filtros">
                                <i class="fas fa-undo"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- TABLA -->
        <div class="card tracking-table-card">
            <div class="card-header">
                <div class="header-left">
                    <i class="fas fa-trash text-danger"></i>
                    <span>Seguimientos en Papelera</span>
                    <span class="badge bg-danger ms-2"><?= $dataProvider ? $dataProvider->getTotalCount() : 0 ?></span>
                </div>
                <div class="header-right">
                    <span class="badge bg-secondary">
                        Página <?= $dataProvider ? $dataProvider->getPagination()->getPage() + 1 : 1 ?>
                        de <?= $dataProvider ? $dataProvider->getPagination()->getPageCount() : 1 ?>
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="50">#</th>
                                <th>Lead</th>
                                <th>Comentarios</th>
                                <th>Fecha Seguimiento</th>
                                <th>Próximo</th>
                                <th>Usuario</th>
                                <?php if ($isSuperAdmin): ?><th>Empresa</th><?php endif; ?>
                                <th>Estado</th>
                                <th class="text-center" width="180">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($trackings)): ?>
                                <?php foreach ($trackings as $index => $tracking): ?>
                                    <tr class="tracking-row" data-id="<?= $tracking->id_sales_tracking ?>">
                                        <td><?= $dataProvider ? $dataProvider->getPagination()->getOffset() + $index + 1 : $index + 1 ?></td>
                                        <td>
                                            <?php if ($tracking->lead): ?>
                                                <span class="lead-badge">
                                                    <i class="fas fa-user"></i>
                                                    <?= Html::encode($tracking->lead->name . ' ' . $tracking->lead->lastname) ?>
                                                </span>
                                                <?php if ($tracking->lead->phone): ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-phone"></i> <?= Html::encode($tracking->lead->phone) ?>
                                                    </small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">Sin lead</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($tracking->comments)): ?>
                                                <?= Html::encode(mb_substr($tracking->comments, 0, 60)) ?>
                                                <?= mb_strlen($tracking->comments) > 60 ? '...' : '' ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($tracking->date_s): ?>
                                                <?= date('d/m/Y', strtotime($tracking->date_s)) ?>
                                                <?php if ($tracking->hour): ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="far fa-clock"></i> <?= Html::encode($tracking->hour) ?>
                                                    </small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($tracking->date_f): ?>
                                                <?= date('d/m/Y', strtotime($tracking->date_f)) ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($tracking->user): ?>
                                                <span class="badge bg-info">
                                                    <i class="fas fa-user"></i>
                                                    <?= Html::encode($tracking->user->name . ' ' . $tracking->user->lastname1) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">Sin asignar</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if ($isSuperAdmin): ?>
                                            <td>
                                                <?php
                                                $companyName = '-';
                                                if ($tracking->lead && $tracking->lead->company) {
                                                    $companyName = $tracking->lead->company->name;
                                                }
                                                echo Html::encode($companyName);
                                                ?>
                                            </td>
                                        <?php endif; ?>
                                        <td>
                                            <span class="badge bg-<?= $tracking->getStatusBadgeClass() ?>">
                                                <?= $tracking->getStatusName() ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <?php if ($isAdmin || $isSuperAdmin): ?>
                                                    <?= Html::a(
                                                        '<i class="fas fa-undo"></i> Restaurar',
                                                        ['restore', 'id' => $tracking->id_sales_tracking],
                                                        [
                                                            'class' => 'btn btn-sm btn-success btn-action',
                                                            'title' => 'Restaurar seguimiento',
                                                            'data' => [
                                                                'confirm' => '¿Restaurar este seguimiento?',
                                                                'method' => 'post',
                                                            ],
                                                        ]
                                                    ) ?>
                                                <?php endif; ?>
                                                <?= Html::a(
                                                    '<i class="fas fa-eye"></i>',
                                                    ['view', 'id' => $tracking->id_sales_tracking],
                                                    [
                                                        'class' => 'btn btn-sm btn-info btn-action',
                                                        'title' => 'Ver detalles',
                                                    ]
                                                ) ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?= $isSuperAdmin ? 9 : 8 ?>" class="text-center text-muted py-5">
                                        <i class="fas fa-trash fa-3x d-block mb-3 text-muted"></i>
                                        <p class="mb-2">No hay seguimientos en la papelera.</p>
                                        <small class="text-muted d-block mb-3">
                                            Los seguimientos eliminados aparecerán aquí.
                                        </small>
                                        <?= Html::a(
                                            '<i class="fas fa-arrow-left"></i> Volver a Seguimientos',
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
                        Mostrando <?= $dataProvider->getCount() ?> de <?= $totalTrackings ?> seguimientos
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

    </div><!-- /.sales-tracking-wrapper -->
</div><!-- /.sales-tracking-trash -->

<script>
document.getElementById('search-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('form-filtros').submit();
    }
});
</script>