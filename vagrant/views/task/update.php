<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Actualizar Tarea #' . $model->id_task;
$this->params['breadcrumbs'][] = ['label' => 'Tareas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Tarea #' . $model->id_task, 'url' => ['view', 'id' => $model->id_task]];
$this->params['breadcrumbs'][] = 'Actualizar';
?>

<div class="task-update">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-edit"></i> 
                <?= Html::encode($this->title) ?>
            </h5>
        </div>
        <div class="card-body">
            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'comments')->textarea([
                'rows' => 4,
                'placeholder' => 'Descripción de la tarea...'
            ])->label('Descripción') ?>

            <?= $form->field($model, 'id_status')->dropDownList(
                $statusOptions,
                ['prompt' => 'Seleccione un estado...', 'class' => 'form-control']
            )->label('Estado') ?>

            <?= $form->field($model, 'id_sales_tracking')->dropDownList(
                $trackingsList,
                ['prompt' => 'Seleccione un seguimiento...', 'class' => 'form-control']
            )->label('Seguimiento Asociado') ?>

            <div class="form-group mt-3">
                <?= Html::submitButton('<i class="fas fa-save"></i> Actualizar Tarea', [
                    'class' => 'btn btn-primary'
                ]) ?>
                <?= Html::a('Cancelar', ['view', 'id' => $model->id_task], [
                    'class' => 'btn btn-secondary'
                ]) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>