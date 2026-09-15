<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Nuevo Reporte';
$this->params['breadcrumbs'][] = ['label' => 'Reportes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$statusOptions = isset($statusOptions) ? $statusOptions : [];
$companyList = isset($companyList) ? $companyList : [];
$isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;

function getReportBadgeClass($statusName) {
    $badges = [
        'activo' => 'success',
        'completado' => 'info',
        'pendiente' => 'warning',
        'cancelado' => 'danger',
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
                        <i class="fas fa-file-alt"></i> <?= Html::encode($this->title) ?>
                    </h3>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin(); ?>

                    <?= $form->field($model, 'report_name')->textInput([
                        'maxlength' => 50,
                        'placeholder' => 'Nombre del reporte'
                    ])->label('Nombre') ?>

                    <?= $form->field($model, 'report_type')->textInput([
                        'maxlength' => 50,
                        'placeholder' => 'Ej: Ventas, Leads, Financiero...'
                    ])->label('Tipo de Reporte') ?>

                    <?= $form->field($model, 'date_report')->input('date')->label('Fecha') ?>

                    <?php if ($isSuperAdmin && !empty($companyList)): ?>
                        <?= $form->field($model, 'id_company')->dropDownList(
                            $companyList,
                            ['prompt' => 'Seleccione una empresa']
                        )->label('Empresa') ?>
                    <?php endif; ?>

                    <?= $form->field($model, 'id_status')->dropDownList(
                        $statusOptions,
                        ['prompt' => 'Seleccione un estado']
                    )->label('Estado') ?>

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
                        <li>Nombre</li>
                        <li>Estado</li>
                    </ul>
                    <hr>
                    <p><strong>Estados disponibles:</strong></p>
                    <ul class="list-unstyled">
                        <?php foreach ($statusOptions as $id => $nombre): ?>
                            <li>
                                <span class="badge bg-<?= getReportBadgeClass($nombre) ?>">
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