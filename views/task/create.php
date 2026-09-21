<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Nueva Actividad';
$this->params['breadcrumbs'][] = ['label' => 'Actividades', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/task.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

$statusOptions = $statusOptions ?? [];
?>

<div class="task-create">
    <div class="task-wrapper">

        <div class="task-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Actividades</span><span class="separator">›</span>
                    <span class="current"><?= Html::encode($this->title) ?></span>
                </div>
                <h1 class="page-title">
                    <i class="fas fa-plus text-success me-2"></i>
                    <?= Html::encode($this->title) ?>
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['index'], ['class' => 'btn btn-secondary btn-sm btn-header-action']) ?>
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
                            'placeholder' => 'Descripción de la Actividad...',
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
                                ])->label('Fecha de actividad')
                                ->hint('Selecciona la fecha desde el calendario', ['class' => 'text-muted']) ?>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <?= Html::submitButton('<i class="fas fa-save"></i> Crear Actividad', ['class' => 'btn btn-success']) ?>
                            <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-secondary']) ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card table-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-tags"></i> Estados disponibles</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li><span class="badge bg-secondary">Por hacer</span></li>
                            <li><span class="badge bg-info">En progreso</span></li>
                            <li><span class="badge bg-primary">En revisión</span></li>
                            <li><span class="badge bg-warning">Programado</span></li>
                            <li><span class="badge bg-success">Completado</span></li>
                            <li><span class="badge bg-danger">Cancelado</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>