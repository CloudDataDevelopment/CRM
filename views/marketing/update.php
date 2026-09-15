<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Editar ' . $model->typeLabel . ': ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Marketing', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';

// Registrar CSS del marketing
$this->registerCssFile('@web/css/marketing.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
]);
?>

<div class="marketing-update">
    <div class="card shadow marketing-form">
        <div class="card-header bg-warning text-white">
            <h4><i class="fas fa-edit me-2"></i> <?= Html::encode($this->title) ?></h4>
        </div>
        <div class="card-body">
            <?php $form = ActiveForm::begin(); ?>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'type')->dropDownList(
                        \app\models\Marketing::getTypeList(),
                        ['prompt' => 'Selecciona el tipo', 'class' => 'form-select', 'disabled' => true]
                    ) ?>
                    <?= Html::activeHiddenInput($model, 'type') ?>
                    <div class="help-block text-muted">
                        <i class="fas fa-info-circle"></i> El tipo no se puede modificar después de crear
                    </div>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Nombre de la campaña/promoción']) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'start_date')->input('date', [
                        'class' => 'form-control',
                        'placeholder' => 'YYYY-MM-DD'
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'end_date')->input('date', [
                        'class' => 'form-control',
                        'placeholder' => 'YYYY-MM-DD'
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'id_status')->dropDownList(
                        \app\models\Marketing::getStatusList(),
                        ['prompt' => 'Selecciona el estado', 'class' => 'form-select']
                    ) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'comments')->textarea(['rows' => 3, 'placeholder' => 'Comentarios (opcional)', 'class' => 'form-control']) ?>
                </div>
            </div>

            <?= Html::activeHiddenInput($model, 'id_company') ?>

            <div class="alert alert-info mt-2">
                <i class="fas fa-info-circle me-1"></i> 
                La empresa está asignada automáticamente según tu perfil.
            </div>

            <div class="form-group mt-3">
                <?= Html::submitButton('<i class="fas fa-save"></i> Actualizar', ['class' => 'btn btn-warning']) ?>
                <?= Html::a('<i class="fas fa-arrow-left"></i> Cancelar', ['index'], ['class' => 'btn btn-secondary']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>