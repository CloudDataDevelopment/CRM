<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Nuevo Tipo de Cita';
$this->params['breadcrumbs'][] = ['label' => 'Tipos de Cita', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="tipo-cita-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-info">
        <i class="fas fa-plus-circle"></i> Crea un nuevo tipo de cita para usar en el formulario de agendar citas.
    </div>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'status_sales')->textInput(['maxlength' => true])->label('Tipo de Cita') ?>

    <?= $form->field($model, 'comments')->textarea(['rows' => 3])->label('Descripción') ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>