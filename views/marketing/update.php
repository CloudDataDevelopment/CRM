<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Editar ' . $model->typeLabel . ': ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Marketing', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';

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
                    <span class="current">Editar</span>
                </div>
                <h1 class="page-title">
                    <?= Html::encode($this->title) ?>
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= Html::a('<i class="fas fa-eye"></i> Ver', ['view', 'id' => $model->id], [
                    'class' => 'btn btn-info btn-sm btn-header-action'
                ]) ?>
                <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['index'], [
                    'class' => 'btn btn-secondary btn-sm btn-header-action'
                ]) ?>
            </div>
        </div>

        <!-- FORMULARIO -->
        <div class="marketing-form">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h4><i class="fas fa-edit me-2"></i> <?= Html::encode($this->title) ?></h4>
                </div>
                <div class="card-body">

                    <?php $form = ActiveForm::begin(); ?>

                    <!-- SECCIÓN: INFORMACIÓN GENERAL -->
                    <div class="form-section mb-4">
                        <h5 class="form-section-title">
                            <i class="fas fa-info-circle"></i> Información General
                        </h5>
                        <div class="row">
                            <div class="col-md-4">
                                <?= $form->field($model, 'type')->dropDownList(
                                    \app\models\Marketing::getTypeList(),
                                    ['prompt' => 'Selecciona el tipo', 'class' => 'form-select', 'disabled' => true]
                                ) ?>
                                <?= Html::activeHiddenInput($model, 'type') ?>
                                <div class="help-block text-muted">
                                    <i class="fas fa-info-circle"></i> No modificable
                                </div>
                            </div>
                            <div class="col-md-4">
                                <?= $form->field($model, 'name')->textInput([
                                    'maxlength' => true,
                                    'placeholder' => 'Nombre de la campaña/promoción'
                                ]) ?>
                            </div>
                            <div class="col-md-4">
                                <?= $form->field($model, 'id_status')->dropDownList(
                                    \app\models\Marketing::getStatusList(),
                                    ['prompt' => 'Selecciona el estado', 'class' => 'form-select']
                                ) ?>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN: VIGENCIA -->
                    <div class="form-section mb-4">
                        <h5 class="form-section-title">
                            <i class="fas fa-calendar-alt"></i> Vigencia
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'start_date')->input('date', [
                                    'class' => 'form-control',
                                    'placeholder' => 'YYYY-MM-DD'
                                ])->label('Fecha de Inicio') ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'end_date')->input('date', [
                                    'class' => 'form-control',
                                    'placeholder' => 'YYYY-MM-DD'
                                ])->label('Fecha de Fin') ?>
                            </div>
                        </div>
                    </div>

                    <!-- 🔥 SECCIÓN: DESCRIPCIÓN DETALLADA -->
                    <div class="form-section mb-4">
                        <h5 class="form-section-title">
                            <i class="fas fa-align-left"></i> Descripción Detallada
                        </h5>
                        <div class="row">
                            <div class="col-md-12">
                                <?= $form->field($model, 'comments')->textarea([
                                    'rows' => 14,
                                    'placeholder' => "Describe aquí toda la información de la campaña o promoción:\n\n" .
                                                     "• Objetivo de la campaña/promoción\n" .
                                                     "• Público objetivo\n" .
                                                     "• Mecánica o dinámica\n" .
                                                     "• Canales de difusión\n" .
                                                     "• Presupuesto asignado\n" .
                                                     "• Materiales o recursos necesarios\n" .
                                                     "• Métricas o KPIs esperados\n" .
                                                     "• Observaciones adicionales...",
                                    'class' => 'form-control description-textarea',
                                    'style' => 'min-height: 350px; font-size: 0.9rem; line-height: 1.6;'
                                ])->label(false) ?>
                            </div>
                        </div>
                    </div>

                    <!-- EMPRESA (OCULTA) -->
                    <?= Html::activeHiddenInput($model, 'id_company') ?>

                    <!-- BOTONES -->
                    <div class="form-group mt-4 d-flex gap-2 flex-wrap">
                        <?= Html::submitButton('<i class="fas fa-save"></i> Actualizar', ['class' => 'btn btn-warning']) ?>
                        <?= Html::a('<i class="fas fa-eye"></i> Ver', ['view', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
                        <?= Html::a('<i class="fas fa-arrow-left"></i> Cancelar', ['index'], ['class' => 'btn btn-secondary']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>

    </div>
</div>