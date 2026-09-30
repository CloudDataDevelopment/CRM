<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\StringHelper;
use yii\widgets\LinkPager;

$this->title = 'Papelera de Actividades';
$this->params['breadcrumbs'][] = ['label' => 'Actividades', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/task.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

$isAdmin       = $isAdmin ?? false;
$isAgent       = $isAgent ?? false;
$isSuperAdmin  = $isSuperAdmin ?? false;
$tasks         = $tasks ?? [];
$dataProvider  = $dataProvider ?? null;
$totalTasks    = $totalTasks ?? 0;
$search        = $search ?? '';
?>

<div class="task-index">
    <div class="task-wrapper">

        <!-- HEADER -->
        <div class="task-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Actividades</span><span class="separator">›</span>
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
                    '<i class="fas fa-arrow-left"></i> Volver a Actividades',
                    ['index'],
                    ['class' => 'btn btn-secondary btn-sm btn-header-action']
                ) ?>
            </div>
        </div>

        <!-- ALERTA INFORMATIVA -->
        <div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
            <i class="fas fa-info-circle me-2" style="font-size: 1.2rem;"></i>
            <div>
                <strong>Papelera:</strong> Aquí se encuentran las actividades con estado <strong>"Eliminado"</strong>.
                Puedes restaurarlas para que vuelvan al listado principal con estado "Por hacer".
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
                            <div class="stat-number"><?= $totalTasks ?></div>
                            <div class="stat-label">En Papelera</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="card filtros-card mb-3">
            <div class="card-body">
                <form method="get" action="<?= Url::to(['task/trash']) ?>" id="form-filtros">
                    <div class="row align-items-end g-2">
                        <div class="col-md-1">
                            <label class="form-label fw-bold mb-0">Buscar</label>
                        </div>
                        <div class="col-md-5">
                            <input type="text"
                                   class="form-control"
                                   name="search"
                                   placeholder="Buscar por descripción..."
                                   value="<?= Html::encode($search) ?>"
                                   id="search-input">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="<?= Url::to(['task/trash']) ?>"
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
        <div class="card table-card">
            <div class="card-header">
                <div class="header-left">
                    <i class="fas fa-trash text-danger"></i>
                    <span>Actividades en Papelera</span>
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
                    <table class="table table-hover mb-0" id="tasks-table">
                        <thead class="table-light">
                            <tr>
                                <th width="50">#</th>
                                <th>Descripción</th>
                                <th>Fecha Actividad</th>
                                <th>Estado</th>
                                <th class="text-center" width="180">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($tasks)): ?>
                                <?php foreach ($tasks as $index => $task): ?>
                                    <tr class="task-row" data-id="<?= $task->id_task ?>">
                                        <td><?= $dataProvider ? $dataProvider->getPagination()->getOffset() + $index + 1 : $index + 1 ?></td>
                                        <td>
                                            <strong><?= StringHelper::truncate(Html::encode($task->comments ?? 'Sin descripción'), 60, '...') ?></strong>
                                        </td>
                                        <td><?= Html::encode($task->getTrackingDate()) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $task->getStatusBadgeClass() ?>">
                                                <?= Html::encode($task->getStatusName()) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <?= Html::a(
                                                    '<i class="fas fa-eye"></i>',
                                                    ['view', 'id' => $task->id_task],
                                                    [
                                                        'class' => 'btn btn-info btn-sm btn-action',
                                                        'title' => 'Ver',
                                                    ]
                                                ) ?>

                                                <?= Html::a(
                                                    '<i class="fas fa-undo"></i> Restaurar',
                                                    ['restore', 'id' => $task->id_task],
                                                    [
                                                        'class' => 'btn btn-success btn-sm btn-action',
                                                        'title' => 'Restaurar actividad',
                                                        'data' => [
                                                            'confirm' => '¿Restaurar esta actividad? Volverá con estado "Por hacer".',
                                                            'method' => 'post',
                                                        ],
                                                    ]
                                                ) ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="fas fa-trash fa-3x d-block mb-3 text-muted"></i>
                                        <p class="mb-2">No hay actividades en la papelera.</p>
                                        <small class="text-muted d-block mb-3">
                                            Las actividades con estado "Eliminado" aparecerán aquí.
                                        </small>
                                        <?= Html::a(
                                            '<i class="fas fa-arrow-left"></i> Volver a Actividades',
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
                        Mostrando <?= $dataProvider->getCount() ?> de <?= $totalTasks ?> actividades
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
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') document.getElementById('form-filtros').submit();
        });
    }
});
</script>