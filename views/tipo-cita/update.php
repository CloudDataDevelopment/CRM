<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Editar Tipo de Cita: ' . $model->status_sales;
$this->params['breadcrumbs'][] = ['label' => 'Tipos de Cita', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->status_sales, 'url' => ['view', 'id' => $model->id_sales_tracking]];
$this->params['breadcrumbs'][] = 'Editar';
?>

<div class="tipo-cita-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-info">
        <i class="fas fa-edit"></i> Modifica los datos del tipo de cita.
    </div>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'status_sales')->textInput(['maxlength' => true])->label('Tipo de Cita') ?>

    <?= $form->field($model, 'comments')->textarea(['rows' => 3])->label('Descripción') ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fas fa-save"></i> Actualizar', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>