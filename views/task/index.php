<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\StringHelper;
use yii\widgets\LinkPager;

$this->title = 'Actividades';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/task.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

$isAdmin       = $isAdmin ?? false;
$isAgent       = $isAgent ?? false;
$tasks         = $tasks ?? [];
$totalTasks    = $totalTasks ?? 0;
$withoutStatus = $withoutStatus ?? 0;
$search        = $search ?? '';
$status        = $status ?? '';
$fecha_inicio  = $fecha_inicio ?? '';
$fecha_fin     = $fecha_fin ?? '';
$statusOptions = $statusOptions ?? [];
$dataProvider  = $dataProvider ?? null;

$statusCounts = [
    'Por hacer'   => 0,
    'En progreso' => 0,
    'En revisión' => 0,
    'Programado'  => 0,
    'Completado'  => 0,
    'Cancelado'   => 0,
];

foreach ($tasks as $task) {
    try {
        $name = $task->getStatusName();
        if (isset($statusCounts[$name])) $statusCounts[$name]++;
    } catch (\Exception $e) {}
}

$ultimasTareas = array_slice($tasks, 0, 5);
?>

<div class="task-index">
    <div class="task-wrapper">

        <!-- HEADER -->
        <div class="task-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span class="current"><?= Html::encode($this->title) ?></span>
                </div>
                <h1 class="page-title">
                    <?= Html::encode($this->title) ?>
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= $this->render('/layouts/_report_button') ?>
                <?= Html::a('<i class="fas fa-plus"></i> Nueva Actividad', ['create'], ['class' => 'btn btn-primary btn-sm btn-header-action']) ?>
            </div>
        </div>

        <!-- MÉTRICAS PRINCIPALES -->
        <div class="row g-2 mb-2">
            <div class="col-xl-4 col-md-4 col-6">
                <div class="card stat-card stat-card-primary dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-tasks"></i></div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $totalTasks ?></div>
                            <div class="stat-label">Total Actividades</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-4 col-6">
                <div class="card stat-card stat-card-secondary dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-hourglass-start"></i></div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $statusCounts['Por hacer'] ?></div>
                            <div class="stat-label">Por hacer</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-4 col-6">
                <div class="card stat-card stat-card-info dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-spinner"></i></div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $statusCounts['En progreso'] ?></div>
                            <div class="stat-label">En progreso</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MÉTRICAS SECUNDARIAS -->
        <div class="row g-2 mb-3">
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card stat-card stat-card-purple dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-search"></i></div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $statusCounts['En revisión'] ?></div>
                            <div class="stat-label">En revisión</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card stat-card stat-card-warning dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-clock"></i></div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $statusCounts['Programado'] ?></div>
                            <div class="stat-label">Programado</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card stat-card stat-card-success dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-check-circle"></i></div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $statusCounts['Completado'] ?></div>
                            <div class="stat-label">Completado</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card stat-card stat-card-danger dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-times-circle"></i></div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $statusCounts['Cancelado'] ?></div>
                            <div class="stat-label">Cancelado</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card stat-card stat-card-danger dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-exclamation-triangle"></i></div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $withoutStatus ?></div>
                            <div class="stat-label">Sin Estado</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ALERTA SIN ESTADO -->
        <?php if ($withoutStatus > 0): ?>
            <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Hay <?= $withoutStatus ?> Actividad sin estado asignado.</strong>
                Asigna un estado desde la vista de detalle.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- FILTROS -->
        <div class="card filtros-card mb-3">
            <div class="card-body">
                <form method="get" action="<?= Url::to(['task/index']) ?>" id="form-filtros">
                    <div class="row align-items-end g-2">
                        <div class="col-md-1"><label class="form-label fw-bold mb-0">Filtrar</label></div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search" placeholder="Buscar..."
                                   value="<?= Html::encode($search) ?>" id="search-input">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="status" id="status-select">
                                <option value="">Todos los estados</option>
                                <?php foreach (array_keys($statusCounts) as $opt): ?>
                                    <option value="<?= $opt ?>" <?= $status === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="fecha_inicio"
                                   value="<?= Html::encode($fecha_inicio) ?>" id="fecha-inicio">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="fecha_fin"
                                   value="<?= Html::encode($fecha_fin) ?>" id="fecha-fin">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100" id="btn-filtrar"><i class="fas fa-search"></i></button>
                        </div>
                        <div class="col-md-1">
                            <a href="<?= Url::to(['task/index']) ?>" class="btn btn-secondary w-100" title="Limpiar"><i class="fas fa-undo"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- TABLA -->
        <div class="card table-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tasks-table">
                        <thead class="table-light">
                            <tr>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Fecha Actividad</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($tasks)): ?>
                                <?php foreach ($tasks as $index => $task): ?>
                                    <tr class="task-row" data-id="<?= $task->id_task ?>">
                                        <td><strong><?= StringHelper::truncate(Html::encode($task->comments ?? 'Sin descripción'), 60, '...') ?></strong></td>
                                        <td>
                                            <span class="badge bg-<?= $task->getStatusBadgeClass() ?>">
                                                <?= Html::encode($task->getStatusName()) ?>
                                            </span>
                                        </td>
                                        <td><?= Html::encode($task->getTrackingDate()) ?></td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $task->id_task], [
                                                    'class' => 'btn btn-info btn-sm btn-action', 'title' => 'Ver'
                                                ]) ?>
                                                <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $task->id_task], [
                                                    'class' => 'btn btn-primary btn-sm btn-action', 'title' => 'Editar'
                                                ]) ?>
                                                <?php if ($isAdmin): ?>
                                                    <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $task->id_task], [
                                                        'class' => 'btn btn-danger btn-sm btn-action',
                                                        'title' => 'Eliminar',
                                                        'data' => ['confirm' => '¿Eliminar esta actividad?', 'method' => 'post'],
                                                    ]) ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                        No hay Actividades registradas
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($dataProvider && $dataProvider->pagination->pageCount > 1): ?>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <small class="text-muted">Mostrando <?= count($tasks) ?> de <?= $totalTasks ?> Actividades</small>
                    <?= LinkPager::widget([
                        'pagination' => $dataProvider->pagination,
                        'options' => ['class' => 'pagination pagination-sm mb-0'],
                        'linkOptions' => ['class' => 'page-link'],
                        'prevPageLabel' => '<i class="fas fa-chevron-left"></i>',
                        'nextPageLabel' => '<i class="fas fa-chevron-right"></i>',
                        'maxButtonCount' => 5,
                    ]) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ACTIVIDADES + RESUMEN -->
        <div class="row g-2 mt-3 bottom-cards">
            <div class="col-md-6">
                <div class="card activities-card">
                    <div class="card-header">
                        <div class="header-left">
                            <i class="fas fa-history text-primary"></i>
                            <span>Últimas Actividades</span>
                        </div>
                        <span class="badge bg-primary"><?= count($ultimasTareas) ?></span>
                    </div>
                    <div class="card-body p-0">
                        <div class="activities-list">
                            <?php if (!empty($ultimasTareas)): ?>
                                <?php foreach ($ultimasTareas as $task): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon"><i class="fas fa-tasks text-primary"></i></div>
                                        <div class="activity-content">
                                            <div class="activity-title">
                                                <strong><?= StringHelper::truncate(Html::encode($task->comments ?? 'Sin descripción'), 40, '...') ?></strong>
                                                <span class="badge bg-<?= $task->getStatusBadgeClass() ?>"><?= Html::encode($task->getStatusName()) ?></span>
                                            </div>
                                            <div class="activity-meta">
                                                <span class="activity-date"><i class="far fa-calendar-alt"></i> <?= Html::encode($task->getTrackingDate()) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="activity-empty">
                                    <i class="fas fa-inbox fa-2x d-block mb-2 text-muted"></i>
                                    <p class="text-muted">No hay Actvidades recientes</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card funnel-card dashboard-card">
                    <div class="card-header">
                        <div class="header-left">
                            <i class="fas fa-chart-pie text-primary"></i>
                            <span>Resumen de Actividades</span>
                        </div>
                        <span class="badge bg-info text-white"><?= $totalTasks ?></span>
                    </div>
                    <div class="card-body d-flex align-items-center">
                        <div class="w-100">
                            <?php
                            $statusColors = [
                                'Por hacer'   => 'secondary',
                                'En progreso' => 'info',
                                'En revisión' => 'primary',
                                'Programado'  => 'warning',
                                'Completado'  => 'success',
                                'Cancelado'   => 'danger',
                            ];
                            $hasData = false;
                            foreach ($statusCounts as $c) { if ($c > 0) { $hasData = true; break; } }
                            ?>
                            <?php if ($hasData): ?>
                                <?php foreach ($statusCounts as $name => $count): ?>
                                    <?php if ($count == 0) continue; ?>
                                    <?php $color = $statusColors[$name] ?? 'secondary'; ?>
                                    <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.75rem;">
                                        <span><span class="badge bg-<?= $color ?>"><?= $name ?></span></span>
                                        <span><strong><?= $count ?></strong></span>
                                    </div>
                                    <div class="progress mb-2" style="height: 6px;">
                                        <div class="progress-bar bg-<?= $color ?>" style="width: <?= $totalTasks > 0 ? ($count / $totalTasks) * 100 : 0 ?>%;"></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center text-muted py-3"><i class="fas fa-inbox"></i> Sin datos</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.getElementById('search-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') document.getElementById('form-filtros').submit();
});
document.getElementById('status-select').addEventListener('change', function() {
    document.getElementById('form-filtros').submit();
});
document.getElementById('fecha-inicio').addEventListener('change', function() {
    document.getElementById('form-filtros').submit();
});
document.getElementById('fecha-fin').addEventListener('change', function() {
    document.getElementById('form-filtros').submit();
});
document.getElementById('btn-filtrar').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('form-filtros').submit();
});
</script>