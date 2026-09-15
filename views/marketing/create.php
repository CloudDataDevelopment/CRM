<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Nueva Campaña o Promoción';
$this->params['breadcrumbs'][] = ['label' => 'Marketing', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Registrar CSS del marketing
$this->registerCssFile('@web/css/marketing.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
]);
?>

<div class="marketing-create">
    <div class="card shadow marketing-form">
        <div class="card-header bg-primary text-white">
            <h4><i class="fas fa-plus-circle me-2"></i> <?= Html::encode($this->title) ?></h4>
        </div>
        <div class="card-body">
            <?php $form = ActiveForm::begin(); ?>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'type')->dropDownList(
                        \app\models\Marketing::getTypeList(),
                        ['prompt' => 'Selecciona el tipo', 'class' => 'form-select']
                    ) ?>
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

            <div class="form-group mt-3">
                <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('<i class="fas fa-arrow-left"></i> Cancelar', ['index'], ['class' => 'btn btn-secondary']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>