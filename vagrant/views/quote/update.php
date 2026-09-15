<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Actualizar Cotización #' . $model->id_quote;
$this->params['breadcrumbs'][] = ['label' => 'Cotizaciones', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Cotización #' . $model->id_quote, 'url' => ['view', 'id' => $model->id_quote]];
$this->params['breadcrumbs'][] = 'Actualizar';

$returnUrl = isset($returnUrl) ? $returnUrl : ['index'];
?>

<div class="quote-update">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-edit"></i> 
                        <?= Html::encode($this->title) ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin(); ?>

                    <!-- Lead -->
                    <?= $form->field($model, 'id_lead')->dropDownList(
                        $leadsList,
                        ['prompt' => 'Seleccione un lead...', 'class' => 'form-control']
                    ) ?>

                    <!-- Monto Total -->
                    <?= $form->field($model, 'total_amount')->textInput([
                        'type' => 'number',
                        'step' => '1',
                        'class' => 'form-control',
                        'placeholder' => 'Ej: 1500000'
                    ])->label('Monto Total ($)') ?>

                    <!-- Pago Inicial -->
                    <?= $form->field($model, 'down_payment')->textInput([
                        'type' => 'number',
                        'step' => '1',
                        'class' => 'form-control',
                        'placeholder' => 'Ej: 500000'
                    ])->label('Pago Inicial ($) (Opcional)') ?>

                    <!-- 🔥 ESTADO USANDO id_status -->
                    <?= $form->field($model, 'id_status')->dropDownList(
                        $statusOptions,
                        ['prompt' => 'Seleccione un estado...', 'class' => 'form-control']
                    )->label('Estado') ?>

                    <!-- Observaciones -->
                    <?= $form->field($model, 'comments')->textarea([
                        'rows' => 3,
                        'class' => 'form-control',
                        'placeholder' => 'Observaciones adicionales...'
                    ]) ?>

                    <!-- Botones -->
                    <div class="form-group mt-3">
                        <?= Html::submitButton('<i class="fas fa-save"></i> Actualizar Cotización', [
                            'class' => 'btn btn-primary'
                        ]) ?>
                        
                        <?= Html::a('Cancelar', $returnUrl, [
                            'class' => 'btn btn-secondary'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>

        <!-- Información -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> 
                        Información
                    </h5>
                </div>
                <div class="card-body">
                    <p><strong>Campos obligatorios:</strong></p>
                    <ul>
                        <li>Lead</li>
                        <li>Monto Total</li>
                        <li>Estado</li>
                    </ul>
                    <hr>
                    <p><strong>Estados disponibles:</strong></p>
                    <ul class="list-unstyled">
                        <?php foreach ($statusOptions as $id => $nombre): ?>
                            <li><span class="badge bg-<?= \app\models\Quote::getStatusBadgeClass($nombre) ?>"><?= ucfirst($nombre) ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>