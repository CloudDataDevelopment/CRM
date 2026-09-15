<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$this->title = 'Nueva Cotización';
$this->params['breadcrumbs'][] = ['label' => 'Cotizaciones', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Función auxiliar para obtener la clase del badge
function getBadgeClass($statusName) {
    $badges = [
        'pendiente' => 'warning',
        'aprobada' => 'success',
        'rechazada' => 'danger',
        'pagada' => 'info',
        'cancelada' => 'secondary',
    ];
    return $badges[strtolower(trim($statusName))] ?? 'secondary';
}
?>

<div class="quote-create">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-file-invoice"></i> <?= Html::encode($this->title) ?>
                    </h3>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin(); ?>

                    <?= $form->field($model, 'id_lead')->dropDownList(
                        $leadsList,
                        ['prompt' => 'Seleccione un lead']
                    )->label('Cliente') ?>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'date_quote')->input('date')->label('Fecha') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'hour_quote')->input('time')->label('Hora') ?>
                        </div>
                    </div>

                    <?= $form->field($model, 'total_amount')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'placeholder' => '0.00'
                    ])->label('Monto Total') ?>

                    <?= $form->field($model, 'down_payment')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'placeholder' => '0.00'
                    ])->label('Enganche') ?>

                    <?= $form->field($model, 'id_status')->dropDownList(
                        $statusOptions,
                        ['prompt' => 'Seleccione un estado']
                    )->label('Estado') ?>

                    <?= $form->field($model, 'comments')->textarea([
                        'rows' => 4,
                        'placeholder' => 'Comentarios adicionales...'
                    ])->label('Comentarios') ?>

                    <div class="form-group mt-4">
                        <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success']) ?>
                        <?= Html::a('<i class="fas fa-arrow-left"></i> Regresar', ['index'], ['class' => 'btn btn-secondary']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Información
                    </h4>
                </div>
                <div class="card-body">
                    <p><strong>Campos requeridos:</strong></p>
                    <ul>
                        <li>Cliente</li>
                        <li>Fecha</li>
                        <li>Hora</li>
                        <li>Monto Total</li>
                        <li>Estado</li>
                    </ul>
                    <hr>
                    <p><strong>Estados disponibles:</strong></p>
                    <ul class="list-unstyled">
                        <?php foreach ($statusOptions as $id => $nombre): ?>
                            <li>
                                <span class="badge bg-<?= getBadgeClass($nombre) ?>">
                                    <?= ucfirst($nombre) ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>