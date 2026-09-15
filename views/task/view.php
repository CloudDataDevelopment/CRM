<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Detalle de Tarea #' . $model->id_task;
$this->params['breadcrumbs'][] = ['label' => 'Tareas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/task.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

$isAdmin       = $isAdmin ?? false;
$statusOptions = $statusOptions ?? [];
$statusName    = $model->getStatusName();
$badgeClass    = $model->getStatusBadgeClass();
?>

<div class="task-view">
    <div class="task-wrapper">

        <div class="task-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Tareas</span><span class="separator">›</span>
                    <span class="current"><?= Html::encode($this->title) ?></span>
                </div>
                <h1 class="page-title">
                    <i class="fas fa-tasks text-primary me-2"></i>
                    <?= Html::encode($this->title) ?>
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= Html::a('<i class="fas fa-edit"></i> Editar', ['update', 'id' => $model->id_task], ['class' => 'btn btn-primary btn-sm btn-header-action']) ?>
                <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['index'], ['class' => 'btn btn-secondary btn-sm btn-header-action']) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card table-card">
                    <div class="card-body">
                        <p><strong>ID:</strong> <span class="badge bg-secondary">#<?= $model->id_task ?></span></p>
                        <p><strong>Descripción:</strong></p>
                        <div class="p-2 bg-light rounded">
                            <?= nl2br(Html::encode($model->comments ?? 'Sin descripción')) ?>
                        </div>
                        <p class="mt-3"><strong>Estado:</strong>
                            <span class="badge bg-<?= $badgeClass ?>"><?= Html::encode($statusName) ?></span>
                        </p>
                        <p><strong>Fecha de Actividad:</strong> <?= Html::encode($model->getTrackingDate()) ?></p>
                        <p><strong>Fecha de creación:</strong> <?= Html::encode($model->getFormattedDateTime()) ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- CAMBIAR ESTADO -->
                <div class="card table-card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i> Cambiar Estado</h5>
                    </div>
                    <div class="card-body">
                        <?php $form = ActiveForm::begin([
                            'action' => ['task/update-status'],
                            'method' => 'post',
                        ]); ?>

                        <?= Html::hiddenInput('id_task', $model->id_task) ?>

                        <div class="form-group">
                            <label class="form-label fw-bold">Seleccionar Estado</label>
                            <?= Html::dropDownList('status_id', $model->id_status, $statusOptions, [
                                'class' => 'form-select',
                                'prompt' => 'Seleccione un estado...',
                            ]) ?>
                        </div>

                        <div class="d-grid gap-2 mt-3">
                            <?= Html::submitButton('<i class="fas fa-save me-2"></i> Actualizar Estado', [
                                'class' => 'btn btn-success',
                            ]) ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="text-center">
                            <small class="text-muted">Estado actual:
                                <span class="badge bg-<?= $badgeClass ?>"><?= Html::encode($statusName) ?></span>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- 📅 CAMBIAR FECHA RÁPIDA -->
                <div class="card table-card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> Cambiar Fecha</h5>
                    </div>
                    <div class="card-body">
                        <?php $form = ActiveForm::begin([
                            'action' => ['task/update-date'],
                            'method' => 'post',
                        ]); ?>

                        <?= Html::hiddenInput('id_task', $model->id_task) ?>

                        <div class="form-group">
                            <label class="form-label fw-bold">Seleccionar Fecha</label>
                            <input type="date" name="date_s" class="form-control"
                                   value="<?= Html::encode($model->date_s) ?>">
                        </div>

                        <div class="d-grid gap-2 mt-3">
                            <?= Html::submitButton('<i class="fas fa-save me-2"></i> Actualizar Fecha', [
                                'class' => 'btn btn-primary',
                            ]) ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="text-center">
                            <small class="text-muted">Fecha actual:
                                <strong><?= Html::encode($model->getTrackingDate()) ?></strong>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- RESUMEN -->
                <div class="card table-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i> Resumen</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>ID Tarea:</strong> #<?= $model->id_task ?></p>
                        <p><strong>Estado:</strong>
                            <span class="badge bg-<?= $badgeClass ?>"><?= Html::encode($statusName) ?></span>
                        </p>
                        <p><strong>Fecha Actividad:</strong> <?= Html::encode($model->getTrackingDate()) ?></p>
                        <?php if ($isAdmin): ?>
                            <hr>
                            <?= Html::a('<i class="fas fa-trash"></i> Eliminar', ['delete', 'id' => $model->id_task], [
                                'class' => 'btn btn-danger btn-sm w-100',
                                'data' => [
                                    'confirm' => '¿Eliminar esta tarea?',
                                    'method' => 'post',
                                ],
                            ]) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>