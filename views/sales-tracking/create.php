<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$this->title = 'Nuevo Seguimiento';
$this->params['breadcrumbs'][] = ['label' => 'Seguimientos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/sales-tracking.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
]);

$lead = isset($lead) ? $lead : null;
$leadId = isset($leadId) ? $leadId : null;
$leadsList = isset($leadsList) ? $leadsList : [];
$statusOptions = isset($statusOptions) ? $statusOptions : [];

if ($lead) {
    $this->title = 'Nuevo Seguimiento - ' . $lead->name . ' ' . $lead->lastname;
}
?>

<!-- 🔥 CONTENEDOR PRINCIPAL CON SOMBRA -->
<div class="sales-tracking-create">
    <div class="sales-tracking-wrapper">

        <!-- HEADER -->
        <div class="sales-tracking-header">
            <div class="header-left">
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Ventas</span><span class="separator">›</span>
                    <span>Seguimientos</span><span class="separator">›</span>
                    <span class="current">Nuevo</span>
                </div>
                <h1 class="page-title">
                    <?= Html::encode($this->title) ?>
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?php if ($lead): ?>
                    <?= Html::a('<i class="fas fa-arrow-left"></i> Cancelar', ['lead/view', 'id' => $lead->id_lead], [
                        'class' => 'btn btn-secondary btn-sm btn-tracking-action'
                    ]) ?>
                <?php else: ?>
                    <?= Html::a('<i class="fas fa-arrow-left"></i> Cancelar', ['index'], [
                        'class' => 'btn btn-secondary btn-sm btn-tracking-action'
                    ]) ?>
                <?php endif; ?>
                <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', [
                    'class' => 'btn btn-primary btn-sm btn-tracking-action',
                    'form' => 'create-tracking-form',
                ]) ?>
            </div>
        </div>

        <!-- CONTENIDO -->
        <div class="row g-3">
            <div class="col-md-8">
                <div class="card tracking-table-card">
                    <div class="card-body">
                        <?php $form = ActiveForm::begin([
                            'options' => ['id' => 'create-tracking-form'],
                            'fieldConfig' => [
                                'options' => ['class' => 'form-group mb-3'],
                                'labelOptions' => ['class' => 'form-label fw-bold'],
                            ],
                        ]); ?>

                        <?php if ($lead): ?>
                            <div class="lead-info-box mb-4">
                                <h6 class="lead-info-title">
                                    <i class="fas fa-user text-primary me-2"></i>
                                    Lead Asociado
                                </h6>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <span class="text-muted">Nombre:</span>
                                        <strong><?= Html::encode($lead->name . ' ' . $lead->lastname) ?></strong>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="text-muted">Teléfono:</span>
                                        <strong><?= Html::encode($lead->phone) ?></strong>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="text-muted">Estado:</span>
                                        <span class="badge bg-<?= $lead->getStatusBadgeClass() ?>">
                                            <?= $lead->getStatusName() ?>
                                        </span>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="text-muted">Registro:</span>
                                        <strong><?= date('d/m/Y', strtotime($lead->created_at)) ?></strong>
                                    </div>
                                </div>
                            </div>
                            <?= Html::activeHiddenInput($model, 'id_lead') ?>
                        <?php else: ?>
                            <?= $form->field($model, 'id_lead')->dropDownList(
                                $leadsList,
                                ['prompt' => 'Selecciona un lead...', 'class' => 'form-select']
                            )->label('Lead') ?>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'id_status')->dropDownList(
                                    $statusOptions,
                                    ['prompt' => 'Selecciona el estado...', 'class' => 'form-select']
                                )->label('Estado del Seguimiento') ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'date_f')->input('date', [
                                    'class' => 'form-control',
                                    'placeholder' => 'YYYY-MM-DD'
                                ])->label('Próximo Seguimiento') ?>
                            </div>
                        </div>

                        <?= $form->field($model, 'comments')->textarea([
                            'rows' => 4,
                            'placeholder' => 'Comentarios del seguimiento...',
                            'class' => 'form-control'
                        ])->label('Comentarios') ?>

                        <div class="form-group mt-4">
                            <?= Html::submitButton('<i class="fas fa-save"></i> Guardar Seguimiento', [
                                'class' => 'btn btn-success'
                            ]) ?>
                            <?php if ($lead): ?>
                                <?= Html::a('<i class="fas fa-arrow-left"></i> Cancelar', ['lead/view', 'id' => $lead->id_lead], [
                                    'class' => 'btn btn-secondary'
                                ]) ?>
                            <?php else: ?>
                                <?= Html::a('<i class="fas fa-arrow-left"></i> Cancelar', ['index'], [
                                    'class' => 'btn btn-secondary'
                                ]) ?>
                            <?php endif; ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card tracking-table-card mb-3">
                    <div class="card-header">
                        <div class="header-left">
                            <i class="fas fa-lightbulb text-warning"></i>
                            <span>Consejos Rápidos</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="tips-list">
                            <li>
                                <i class="fas fa-check-circle text-success"></i>
                                <strong>Estado:</strong> Selecciona el estado adecuado para el seguimiento.
                            </li>
                            <li>
                                <i class="fas fa-check-circle text-success"></i>
                                <strong>Comentarios:</strong> Describe detalladamente la interacción.
                            </li>
                            <li>
                                <i class="fas fa-check-circle text-success"></i>
                                <strong>Próximo:</strong> Programa la siguiente actividad si es necesario.
                            </li>
                            <li>
                                <i class="fas fa-check-circle text-success"></i>
                                <strong>Lead:</strong> Asegúrate de seleccionar el lead correcto.
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card tracking-table-card">
                    <div class="card-header">
                        <div class="header-left">
                            <i class="fas fa-tags text-primary"></i>
                            <span>Estados disponibles</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li><span class="badge bg-warning">Pendiente</span></li>
                            <li><span class="badge bg-info">Programado</span></li>
                            <li><span class="badge bg-primary">En Progreso</span></li>
                            <li><span class="badge bg-success">Completado</span></li>
                            <li><span class="badge bg-danger">Cancelado</span></li>
                        </ul>
                    </div>
                </div>

                <?php if ($lead): ?>
                <div class="card tracking-table-card mt-3">
                    <div class="card-header">
                        <div class="header-left">
                            <i class="fas fa-history text-primary"></i>
                            <span>Últimos Seguimientos</span>
                        </div>
                        <span class="badge bg-primary"><?= count($lead->salesTrackings ?? []) ?></span>
                    </div>
                    <div class="card-body p-0">
                        <div class="recent-list">
                            <?php if (!empty($lead->salesTrackings)): ?>
                                <?php
                                $recentTrackings = array_slice($lead->salesTrackings, 0, 3);
                                foreach ($recentTrackings as $track):
                                ?>
                                    <div class="recent-item">
                                        <span class="badge bg-<?= $track->getStatusBadgeClass() ?>">
                                            <?= $track->getStatusName() ?>
                                        </span>
                                        <span class="recent-date">
                                            <?= date('d/m/Y', strtotime($track->date_s)) ?>
                                        </span>
                                        <small class="text-muted d-block">
                                            <?= Html::encode(substr($track->comments ?? '', 0, 30)) ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="recent-empty">
                                    <i class="fas fa-inbox"></i>
                                    <span>Sin seguimientos previos</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /.sales-tracking-wrapper -->
</div><!-- /.sales-tracking-create -->