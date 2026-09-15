<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Tareas';
$this->params['breadcrumbs'][] = $this->title;

$currentUser = Yii::$app->user->identity;
?>

<div class="task-index">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>
            <i class="fas fa-tasks text-primary me-2"></i> 
            <?= Html::encode($this->title) ?>
            <?php if ($isAgent): ?>
                <span class="badge bg-info ms-2">Mis Tareas</span>
            <?php endif; ?>
        </h1>
        <div>
            <?= Html::a('<i class="fas fa-plus"></i> Nueva Tarea', ['create'], [
                'class' => 'btn btn-success'
            ]) ?>
        </div>
    </div>

    <!-- ESTADÍSTICAS -->
    <div class="row g-2 mb-3">
        <div class="col-md-3 col-6">
            <div class="card stat-card stat-card-primary">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-2">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?= $totalTasks ?></div>
                        <div class="stat-label">Total Tareas</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card stat-card stat-card-warning">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-2">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?= $pendingTasks ?></div>
                        <div class="stat-label">Pendientes</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card stat-card stat-card-info">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-2">
                        <i class="fas fa-spinner"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?= $inProgressTasks ?></div>
                        <div class="stat-label">En Progreso</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card stat-card stat-card-success">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-2">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?= $completedTasks ?></div>
                        <div class="stat-label">Completadas</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($withoutStatus > 0): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> 
            <strong>Hay <?= $withoutStatus ?> tarea(s) sin estado asignado.</strong>
            Asigna un estado desde la vista de detalle.
        </div>
    <?php endif; ?>

    <!-- TABLA -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Asignado a</th>
                            <th>Fecha Cita</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($tasks)): ?>
                            <?php foreach ($tasks as $index => $task): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <?= Html::encode($task->comments ?? 'Sin descripción') ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusName = $task->getStatusName();
                                        $badgeClass = $task->getStatusBadgeClass();
                                        ?>
                                        <span class="badge bg-<?= $badgeClass ?>">
                                            <?= $statusName ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($task->user): ?>
                                            <?= Html::encode($task->user->name . ' ' . $task->user->lastname1) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Sin asignar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $task->getTrackingDate() ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $task->id_task], [
                                                'class' => 'btn btn-info btn-sm btn-action',
                                                'title' => 'Ver'
                                            ]) ?>
                                            
                                            <?php if ($isAdmin || ($isAgent && $task->id_user == $currentUser->id_user)): ?>
                                                <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $task->id_task], [
                                                    'class' => 'btn btn-primary btn-sm btn-action',
                                                    'title' => 'Editar'
                                                ]) ?>
                                            <?php endif; ?>
                                            
                                            <?php if ($isAdmin): ?>
                                                <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $task->id_task], [
                                                    'class' => 'btn btn-danger btn-sm btn-action',
                                                    'title' => 'Eliminar',
                                                    'data' => [
                                                        'confirm' => '¿Eliminar esta tarea?',
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
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    <?php if ($isAgent): ?>
                                        No tienes tareas asignadas
                                    <?php else: ?>
                                        No hay tareas registradas
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    border: none;
    border-radius: 10px;
    transition: all 0.3s ease;
    background: #fff;
    border-left: 3px solid transparent;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
}
.stat-card .card-body {
    padding: 0.5rem 0.7rem !important;
}
.stat-card .stat-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
}
.stat-card .stat-number {
    font-size: 1.2rem;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 0;
}
.stat-card .stat-label {
    font-size: 0.6rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    font-weight: 600;
    opacity: 0.7;
}
.stat-card-primary {
    border-left-color: #4e73df;
}
.stat-card-primary .stat-icon {
    background: rgba(78, 115, 223, 0.12);
    color: #4e73df;
}
.stat-card-primary .stat-number {
    color: #4e73df;
}
.stat-card-warning {
    border-left-color: #f6c23e;
}
.stat-card-warning .stat-icon {
    background: rgba(246, 194, 62, 0.12);
    color: #f6c23e;
}
.stat-card-warning .stat-number {
    color: #f6c23e;
}
.stat-card-info {
    border-left-color: #36b9cc;
}
.stat-card-info .stat-icon {
    background: rgba(54, 185, 204, 0.12);
    color: #36b9cc;
}
.stat-card-info .stat-number {
    color: #36b9cc;
}
.stat-card-success {
    border-left-color: #1cc88a;
}
.stat-card-success .stat-icon {
    background: rgba(28, 200, 138, 0.12);
    color: #1cc88a;
}
.stat-card-success .stat-number {
    color: #1cc88a;
}
.btn-action {
    padding: 0.15rem 0.4rem !important;
    font-size: 0.7rem !important;
    border-radius: 4px;
}
@media (max-width: 768px) {
    .stat-card .stat-number {
        font-size: 1rem !important;
    }
    .stat-card .stat-icon {
        width: 28px !important;
        height: 28px !important;
        font-size: 0.8rem !important;
    }
    .stat-card .stat-label {
        font-size: 0.5rem !important;
    }
}
</style>