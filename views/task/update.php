<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Actualizar Tarea #' . $model->id_task;
$this->params['breadcrumbs'][] = ['label' => 'Actividades', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Tarea #' . $model->id_task, 'url' => ['view', 'id' => $model->id_task]];
$this->params['breadcrumbs'][] = 'Actualizar';

$this->registerCssFile('@web/css/task.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

$statusOptions = $statusOptions ?? [];
?>

<div class="task-update">
    <div class="task-wrapper">

        <div class="task-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Actividades</span><span class="separator">›</span>
                    <span class="current"><?= Html::encode($this->title) ?></span>
                </div>
                <h1 class="page-title">
                    <i class="fas fa-edit text-primary me-2"></i>
                    <?= Html::encode($this->title) ?>
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['view', 'id' => $model->id_task], ['class' => 'btn btn-secondary btn-sm btn-header-action']) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card table-card">
                    <div class="card-body">
                        <?php $form = ActiveForm::begin(); ?>

                        <!-- DESCRIPCIÓN -->
                        <?= $form->field($model, 'comments')->textarea([
                            'rows' => 4,
                            'placeholder' => 'Descripción de la tarea...',
                        ])->label('Descripción <span class="text-danger">*</span>') ?>

                        <div class="row">
                            <!-- ESTADO -->
                            <div class="col-md-6">
                                <?= $form->field($model, 'id_status')->dropDownList(
                                    $statusOptions,
                                    ['prompt' => 'Seleccione un estado...', 'class' => 'form-select']
                                )->label('Estado') ?>
                            </div>

                            <!-- 📅 FECHA MANUAL CON CALENDARIO -->
                            <div class="col-md-6">
                                <?= $form->field($model, 'date_s')->input('date', [
                                    'class' => 'form-control',
                                ])->label('Fecha de Actividad')
                                ->hint('Modifica la fecha desde el calendario', ['class' => 'text-muted']) ?>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <?= Html::submitButton('<i class="fas fa-save"></i> Actualizar Tarea', ['class' => 'btn btn-primary']) ?>
                            <?= Html::a('Cancelar', ['view', 'id' => $model->id_task], ['class' => 'btn btn-secondary']) ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card table-card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>ID Tarea:</strong> #<?= $model->id_task ?></p>
                        <p><strong>Estado actual:</strong>
                            <span class="badge bg-<?= $model->getStatusBadgeClass() ?>">
                                <?= Html::encode($model->getStatusName()) ?>
                            </span>
                        </p>
                        <p><strong>Fecha Actividad:</strong> <?= Html::encode($model->getTrackingDate()) ?></p>
                        <p><strong>Creada:</strong> <?= Html::encode($model->getFormattedDateTime()) ?></p>
                    </div>
                </div>

                <?php if ($isAdmin): ?>
                    <div class="card table-card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Zona de Administración</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <?= Html::a('<i class="fas fa-trash"></i> Eliminar Tarea', ['delete', 'id' => $model->id_task], [
                                    'class' => 'btn btn-danger',
                                    'data' => [
                                        'confirm' => '¿Eliminar esta tarea permanentemente?',
                                        'method' => 'post',
                                    ],
                                ]) ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>