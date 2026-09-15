<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Nueva Tarea';
$this->params['breadcrumbs'][] = ['label' => 'Tareas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$hasTrackings = isset($hasTrackings) ? $hasTrackings : false;
?>

<div class="task-create">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-plus"></i> 
                        <?= Html::encode($this->title) ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin(); ?>

                    <!-- Descripción -->
                    <?= $form->field($model, 'comments')->textarea([
                        'rows' => 4,
                        'placeholder' => 'Descripción de la tarea...'
                    ])->label('Descripción') ?>

                    <!-- Estado -->
                    <?= $form->field($model, 'id_status')->dropDownList(
                        $statusOptions,
                        ['prompt' => 'Seleccione un estado...', 'class' => 'form-control']
                    )->label('Estado') ?>

                    <!-- Seguimiento Asociado -->
                    <?= $form->field($model, 'id_sales_tracking')->dropDownList(
                        $trackingsList,
                        ['prompt' => 'Seleccione un seguimiento...', 'class' => 'form-control']
                    )->label('Seguimiento Asociado') ?>

                    <?php if ($isAgent): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            La tarea se asignará automáticamente a ti.
                        </div>
                    <?php endif; ?>

                    <!-- Botones -->
                    <div class="form-group mt-3">
                        <?= Html::submitButton('<i class="fas fa-save"></i> Guardar Tarea', [
                            'class' => 'btn btn-success'
                        ]) ?>
                        <?= Html::a('Cancelar', ['index'], [
                            'class' => 'btn btn-secondary'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Información de Seguimientos -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> 
                        Información
                    </h5>
                </div>
                <div class="card-body">
                    <p><strong>Seguimientos disponibles:</strong></p>
                    <?php if ($hasTrackings): ?>
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle"></i> <?= count($trackingsList) ?> seguimientos
                        </span>
                        <p class="text-muted small mt-2">
                            <i class="fas fa-info-circle"></i> 
                            Los seguimientos se muestran con el nombre del lead y la fecha.
                        </p>
                    <?php else: ?>
                        <span class="badge bg-warning">
                            <i class="fas fa-exclamation-triangle"></i> Sin seguimientos
                        </span>
                        <p class="text-muted small mt-2">
                            <i class="fas fa-info-circle"></i> 
                            No hay seguimientos disponibles. 
                            <a href="<?= \yii\helpers\Url::to(['sales-tracking/create']) ?>" class="btn btn-sm btn-primary mt-1">
                                <i class="fas fa-plus"></i> Crear seguimiento
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Estados disponibles -->
            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-tags"></i> 
                        Estados disponibles
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <?php foreach ($statusOptions as $id => $nombre): ?>
                            <?php
                            $badgeClass = 'secondary';
                            $nombreLower = strtolower($nombre);
                            if ($nombreLower == 'pendiente') $badgeClass = 'warning';
                            elseif ($nombreLower == 'en progreso' || $nombreLower == 'en_progreso') $badgeClass = 'info';
                            elseif ($nombreLower == 'completado' || $nombreLower == 'completada') $badgeClass = 'success';
                            elseif ($nombreLower == 'cancelado') $badgeClass = 'danger';
                            ?>
                            <li><span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($nombre) ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>