<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Editar Lead: ' . $model->name . ' ' . $model->lastname;
$this->params['breadcrumbs'][] = ['label' => 'Leads', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name . ' ' . $model->lastname, 'url' => ['view', 'id' => $model->id_lead]];
$this->params['breadcrumbs'][] = 'Editar';

// Variables del controlador
$statusList = isset($statusList) ? $statusList : [];
?>

<div class="lead-update">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title mb-0">
                <i class="fas fa-edit"></i> <?= Html::encode($this->title) ?>
            </h3>
        </div>
        <div class="card-body">
            <?php $form = ActiveForm::begin(); ?>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'name')->textInput([
                        'maxlength' => true, 
                        'placeholder' => 'Nombre'
                    ])->label('Nombre') ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'lastname')->textInput([
                        'maxlength' => true, 
                        'placeholder' => 'Apellido'
                    ])->label('Apellido') ?>
                </div>
            </div>

            <?= $form->field($model, 'phone')->textInput([
                'type' => 'tel', 
                'placeholder' => '10 dígitos', 
                'maxlength' => 10
            ])->label('Teléfono') ?>

            <?= $form->field($model, 'id_status')->dropDownList(
                $statusList,
                ['prompt' => 'Seleccione un estado']
            )->label('Estado') ?>

            <?= $form->field($model, 'created_at')->input('date', ['placeholder' => 'YYYY-MM-DD'])->label('Fecha de Registro') ?>

            <?= $form->field($model, 'comments')->textarea([
                'rows' => 3, 
                'placeholder' => 'Comentarios adicionales...'
            ])->label('Observaciones') ?>

            <div class="form-group mt-4">
                <?= Html::submitButton('<i class="fas fa-save"></i> Actualizar', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('<i class="fas fa-arrow-left"></i> Regresar', ['index'], ['class' => 'btn btn-default']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>