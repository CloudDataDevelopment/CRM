<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$this->title = 'Nuevo Lead';
$this->params['breadcrumbs'][] = ['label' => 'Leads', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/leads.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
]);

$this->registerCssFile('@web/css/create-leads.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
    'position' => \yii\web\View::POS_HEAD,
]);

$estadosPermitidos = isset($estadosPermitidos) ? $estadosPermitidos : ['Nuevo', 'Contactado', 'Procesando', 'Cancelado'];
?>

<div class="lead-create">
    <div class="create-content-wrapper">

        <div class="leads-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span>
                    <span class="separator">›</span>
                    <span>Contactos</span>
                    <span class="separator">›</span>
                    <span class="current">Nuevo lead</span>
                </div>
                <h1 class="page-title">
                    Nuevo lead
                </h1>
            </div>
            <div class="header-actions">
                <?= Html::submitButton('<i class="fas fa-save me-1"></i> Guardar', [
                    'class' => 'btn btn-success btn-sm',
                    'form' => 'lead-form',
                ]) ?>
                
                <?= Html::resetButton('<i class="fas fa-undo me-1"></i> Limpiar', [
                    'class' => 'btn btn-outline-secondary btn-sm',
                    'form' => 'lead-form',
                ]) ?>
                
                <?= Html::a('<i class="fas fa-arrow-left me-1"></i> Cancelar', ['index'], [
                    'class' => 'btn btn-secondary btn-sm'
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-7">
                <div class="lead-form-box">
                    <div class="lead-form-title">
                        <i class="fas fa-user-circle text-primary me-2"></i> Información del Lead
                    </div>
                    
                    <div class="lead-form-body">
                        <?php $form = ActiveForm::begin([
                            'options' => ['class' => 'needs-validation', 'id' => 'lead-form'],
                            'fieldConfig' => [
                                'options' => ['class' => 'form-group'],
                                'labelOptions' => ['class' => 'control-label'],
                                'errorOptions' => ['class' => 'help-block'],
                            ],
                        ]); ?>

                        <!-- NOMBRE Y APELLIDO -->
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'name')->textInput([
                                    'maxlength' => true,
                                    'placeholder' => 'Ingresa el nombre',
                                    'class' => 'form-control',
                                ])->label('Nombre <span class="text-danger">*</span>') ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'lastname')->textInput([
                                    'maxlength' => true,
                                    'placeholder' => 'Ingresa el apellido',
                                    'class' => 'form-control',
                                ])->label('Apellido <span class="text-danger">*</span>') ?>
                            </div>
                        </div>

                        <!-- TELÉFONO -->
                        <?= $form->field($model, 'phone')->textInput([
                            'type' => 'tel',
                            'placeholder' => 'Ej: 5512345678',
                            'maxlength' => 10,
                            'class' => 'form-control',
                        ])->label('Teléfono <span class="text-danger">*</span>')
                        ->hint('Ingresa 10 dígitos sin espacios ni guiones', ['class' => 'text-muted']) ?>

                        <!-- 🔥 ESTADO FIJO: NUEVO (solo lectura) -->
                        <div class="status-info-box">
                            <div class="status-info-label">
                                <i class="fas fa-tag text-primary"></i>
                                <strong>Estado:</strong>
                            </div>
                            <div class="status-info-value">
                                <span class="badge bg-primary badge-status-fixed">
                                    <i class="fas fa-plus-circle"></i>
                                    Nuevo
                                </span>

                            </div>
                        </div>

                        <!-- 🔥 AVISO: FECHA AUTOMÁTICA -->
                        <div class="alert alert-info d-flex align-items-center" role="alert" style="border-radius: 8px; border-left: 4px solid #0dcaf0; padding: 10px 14px; font-size: 0.85rem;">
                            <i class="fas fa-calendar-check me-2" style="font-size: 1rem;"></i>
                            <div>
                                <strong>Fecha de registro:</strong>
                                Se asignará automáticamente la fecha de hoy
                                <strong><?= date('d/m/Y') ?></strong>
                                al guardar el lead.
                            </div>
                        </div>

                        <!-- OBSERVACIONES -->
                        <?= $form->field($model, 'comments')->textarea([
                            'rows' => 4,
                            'placeholder' => 'Escribe aquí cualquier información adicional sobre el lead...',
                            'class' => 'form-control',
                            'style' => 'resize: vertical;',
                        ])->label('Observaciones')
                        ->hint('Información adicional relevante sobre el lead', ['class' => 'text-muted']) ?>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card lead-sidebar-card">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history me-2"></i> Últimos Leads Registrados
                        </h5>
                        <span class="badge bg-light text-info">
                            <i class="fas fa-database me-1"></i> <?= count($ultimosLeads) ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($ultimosLeads)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0 lead-sidebar-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="35">#</th>
                                            <th>Nombre</th>
                                            <th>Teléfono</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ultimosLeads as $index => $lead): ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td>
                                                    <strong><?= Html::encode($lead->name . ' ' . $lead->lastname) ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="far fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($lead->created_at)) ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <i class="fas fa-phone text-success"></i>
                                                    <?= $lead->phone ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $badgeClass = $lead->getStatusBadgeClass();
                                                    ?>
                                                    <span class="badge bg-<?= $badgeClass ?>">
                                                        <i class="fas <?= $lead->getStatusIcon() ?>"></i>
                                                        <?= $lead->getStatusName() ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-3x d-block mb-3"></i>
                                <p>No hay leads registrados aún.</p>
                                <small>Comienza registrando tu primer lead</small>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-center">
                        <a href="<?= Url::to(['lead/index']) ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-arrow-right me-1"></i> Ver todos los leads
                        </a>
                    </div>
                </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>