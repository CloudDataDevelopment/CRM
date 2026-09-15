<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Detalle de Tarea #' . $model->id_task;
$this->params['breadcrumbs'][] = ['label' => 'Tareas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="task-view">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-tasks"></i> 
                        Tarea #<?= $model->id_task ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>ID:</strong> <?= $model->id_task ?></p>
                            <p><strong>Descripción:</strong> <?= nl2br(Html::encode($model->comments ?? 'Sin descripción')) ?></p>
                            <p><strong>Estado:</strong> 
                                <?php
                                $statusName = $model->getStatusName();
                                $badgeClass = $model->getStatusBadgeClass();
                                ?>
                                <span class="badge bg-<?= $badgeClass ?>">
                                    <?= $statusName ?>
                                </span>
                            </p>
                            <p><strong>Asignado a:</strong> 
                                <?php if ($model->user): ?>
                                    <?= Html::encode($model->user->name . ' ' . $model->user->lastname1) ?>
                                <?php else: ?>
                                    <span class="text-muted">Sin asignar</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Seguimiento Asociado:</strong>
                                <?php if ($model->id_sales_tracking): ?>
                                    <?= Html::a(
                                        'Ver seguimiento #' . $model->id_sales_tracking,
                                        ['sales-tracking/view', 'id' => $model->id_sales_tracking],
                                        ['target' => '_blank', 'class' => 'btn btn-sm btn-success']
                                    ) ?>
                                <?php else: ?>
                                    <span class="text-muted">Sin seguimiento</span>
                                <?php endif; ?>
                            </p>
                            <p><strong>Fecha de Cita:</strong> <?= $model->getTrackingDate() ?></p>
                            <p><strong>Lead Asociado:</strong>
                                <?php if ($model->salesTracking && $model->salesTracking->lead): ?>
                                    <?= Html::a(
                                        Html::encode($model->getLeadName()),
                                        ['lead/view', 'id' => $model->salesTracking->lead->id_lead],
                                        ['target' => '_blank']
                                    ) ?>
                                <?php else: ?>
                                    <span class="text-muted">Sin lead asociado</span>
                                <?php endif; ?>
                            </p>
                            <p><strong>Teléfono del Lead:</strong> <?= $model->getLeadPhone() ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- CAMBIAR ESTADO -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exchange-alt me-2"></i>
                        Cambiar Estado
                    </h5>
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
                            'style' => 'font-weight: 500;'
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
                            <span class="badge bg-<?= $badgeClass ?>">
                                <?= $statusName ?>
                            </span>
                        </small>
                    </div>
                </div>
            </div>

            <!-- ACCIONES -->
            <div class="card mt-3">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        Acciones
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if ($isAdmin || ($isAgent && $model->id_user == Yii::$app->user->id)): ?>
                            <?= Html::a('<i class="fas fa-edit me-2"></i> Editar Tarea', 
                                ['update', 'id' => $model->id_task], 
                                ['class' => 'btn btn-primary']
                            ) ?>
                        <?php endif; ?>
                        
                        <?php if ($model->id_sales_tracking): ?>
                            <?= Html::a('<i class="fas fa-phone me-2"></i> Ver Seguimiento', 
                                ['sales-tracking/view', 'id' => $model->id_sales_tracking], 
                                [
                                    'class' => 'btn btn-success',
                                    'target' => '_blank'
                                ]
                            ) ?>
                        <?php endif; ?>
                        
                        <?= Html::a('<i class="fas fa-arrow-left me-2"></i> Volver al listado', 
                            ['index'], 
                            ['class' => 'btn btn-secondary']
                        ) ?>
                    </div>
                </div>
            </div>

            <!-- RESUMEN -->
            <div class="card mt-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Resumen
                    </h5>
                </div>
                <div class="card-body">
                    <p><strong>ID Tarea:</strong> <?= $model->id_task ?></p>
                    <p><strong>Estado:</strong> 
                        <span class="badge bg-<?= $badgeClass ?>">
                            <?= $statusName ?>
                        </span>
                    </p>
                    <p><strong>Asignado a:</strong> <?= $model->user ? Html::encode($model->user->name . ' ' . $model->user->lastname1) : 'N/A' ?></p>
                    <p><strong>Fecha Cita:</strong> <?= $model->getTrackingDate() ?></p>
                </div>
            </div>
        </div>
    </div>
</div>