<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$this->title = 'Actualizar Evaluación #' . $model->id_report;
$this->params['breadcrumbs'][] = ['label' => 'Evaluaciones', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/evaluaciones.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;
?>

<div class="quote-container">
    <div class="quote-index">

        <div class="quote-header">
            <div class="header-left">
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Evaluaciones</span><span class="separator">›</span>
                    <span class="current">Actualizar</span>
                </div>
                <h1 class="page-title">
                    <i class="fas fa-edit text-primary me-2"></i>
                    <?= Html::encode($this->title) ?>
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['index'], ['class' => 'btn btn-secondary btn-sm']) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card table-card">
                    <div class="card-body">
                        <?php $form = ActiveForm::begin(); ?>

                        <?= $form->field($model, 'report_name')->textInput([
                            'placeholder' => 'Nombre de la evaluación'
                        ])->label('Nombre de la Evaluación <span class="text-danger">*</span>') ?>

                        <?= $form->field($model, 'report_type')->textInput([
                            'placeholder' => 'Tipo de evaluación'
                        ])->label('Tipo de Evaluación') ?>

                        <?= $form->field($model, 'date_report')->input('date', [
                            'class' => 'form-control'
                        ])->label('Fecha') ?>

                        <?= $form->field($model, 'id_status')->dropDownList(
                            $statusOptions,
                            ['prompt' => 'Seleccione un estado...', 'class' => 'form-select']
                        )->label('Estado <span class="text-danger">*</span>') ?>

                        <?= $form->field($model, 'id_lead')->dropDownList(
                            $leadsList,
                            ['prompt' => 'Seleccione un lead...', 'class' => 'form-select']
                        )->label('Lead Asociado')
                        ->hint('Vincula esta evaluación con un lead específico', ['class' => 'text-muted']) ?>

                        <?php if ($isSuperAdmin): ?>
                            <?= $form->field($model, 'id_company')->dropDownList(
                                \yii\helpers\ArrayHelper::map(\app\models\Company::find()->all(), 'id_company', 'name'),
                                ['prompt' => 'Seleccione empresa...', 'class' => 'form-select']
                            )->label('Empresa') ?>
                        <?php endif; ?>

                        <div class="form-group mt-3">
                            <?= Html::submitButton('<i class="fas fa-save"></i> Actualizar Evaluación', [
                                'class' => 'btn btn-primary'
                            ]) ?>
                            <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-secondary']) ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card table-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>ID:</strong> #<?= $model->id_report ?></p>
                        <p><strong>Estado actual:</strong>
                            <span class="badge bg-<?= $model->getStatusBadgeClass() ?>">
                                <?= Html::encode($model->getStatusName()) ?>
                            </span>
                        </p>
                        <p><strong>Usuario:</strong> <?= Html::encode($model->getUserName()) ?></p>
                        <p><strong>Empresa:</strong> <?= Html::encode($model->getCompanyName()) ?></p>
                        <p><strong>Lead Asociado:</strong>
                            <?php if ($model->lead): ?>
                                <?= Html::encode($model->lead->name . ' ' . $model->lead->lastname) ?>
                                <small class="text-muted">(<?= Html::encode($model->lead->phone) ?>)</small>
                            <?php else: ?>
                                <span class="text-muted">Sin lead asociado</span>
                            <?php endif; ?>
                        </p>
                        <p><strong>Fecha:</strong> <?= Html::encode($model->getFormattedDate()) ?></p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>