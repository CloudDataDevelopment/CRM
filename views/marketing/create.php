<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Nueva Campaña o Promoción';
$this->params['breadcrumbs'][] = ['label' => 'Marketing', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/marketing.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
]);
?>

<div class="marketing-index">
    <div class="marketing-wrapper">

        <!-- HEADER -->
        <div class="marketing-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Marketing</span><span class="separator">›</span>
                    <span class="current">Nuevo</span>
                </div>
                <h1 class="page-title">
                    <?= Html::encode($this->title) ?>
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['index'], [
                    'class' => 'btn btn-secondary btn-sm btn-header-action'
                ]) ?>
            </div>
        </div>

        <!-- FORMULARIO -->
        <div class="marketing-form">
            <div class="card">
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
                            <?= $form->field($model, 'name')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Nombre de la campaña/promoción'
                            ]) ?>
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
                            <?= $form->field($model, 'comments')->textarea([
                                'rows' => 3,
                                'placeholder' => 'Descripción',
                                'class' => 'form-control'
                            ])->label('Descripción') ?>
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

    </div>
</div>