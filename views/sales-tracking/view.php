<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'Detalle de Seguimiento #' . $model->id_sales_tracking;
$this->params['breadcrumbs'][] = ['label' => 'Seguimientos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// CSS
$this->registerCssFile('@web/css/sales-tracking.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerCssFile('@web/css/leads.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

// ============================================
// OBTENER EL LEAD ASOCIADO
// ============================================
$lead = $model->lead;

$statusName = $model->getStatusName();
$badgeClass = $model->getStatusBadgeClass();

$statusColors = [
    'warning' => '#f6c23e',
    'info' => '#36b9cc',
    'primary' => '#4e73df',
    'success' => '#1cc88a',
    'danger' => '#e74a3b',
    'secondary' => '#6c757d',
];

$statusIcons = [
    'warning' => 'fa-clock',
    'info' => 'fa-calendar-check',
    'primary' => 'fa-spinner',
    'success' => 'fa-check-circle',
    'danger' => 'fa-times-circle',
    'secondary' => 'fa-circle',
];

$isAdmin = Yii::$app->user->identity->isAdmin();
$isAgent = Yii::$app->user->identity->isAgent();
$isSuperAdmin = Yii::$app->user->identity->isSuperAdmin();
?>

<!-- 🔥 CONTENEDOR PRINCIPAL CON SOMBRA -->
<div class="sales-tracking-view">
    <div class="sales-tracking-wrapper">

        <!-- HEADER -->
        <div class="sales-tracking-header">
            <div class="header-left">
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Ventas</span><span class="separator">›</span>
                    <span>Seguimientos</span><span class="separator">›</span>
                    <span class="current">Detalle</span>
                </div>
                <h1 class="page-title">
                    Seguimiento #<?= $model->id_sales_tracking ?>
                    <span class="badge ms-2" style="background-color: <?= $statusColors[$badgeClass] ?? '#6c757d' ?>; font-size: 14px; padding: 4px 14px;">
                        <i class="fas <?= $statusIcons[$badgeClass] ?? 'fa-circle' ?>"></i> <?= $statusName ?>
                    </span>
                    <small><?= date('d/m/Y H:i', strtotime($model->date_s . ' ' . ($model->hour ?? '00:00'))) ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['index'], ['class' => 'btn btn-secondary btn-sm btn-tracking-action']) ?>
                <?= Html::a('<i class="fas fa-print"></i> Imprimir', ['print', 'id' => $model->id_sales_tracking], [
                    'class' => 'btn btn-info btn-sm btn-tracking-action',
                    'target' => '_blank'
                ]) ?>
            </div>
        </div>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="row g-3">

            <!-- COLUMNA IZQUIERDA -->
            <div class="col-lg-4">

                <!-- ESTADO ACTUAL -->
                <div class="card details-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-tag"></i> Estado Actual</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="p-3 rounded-3" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                            <span class="badge bg-<?= $badgeClass ?> p-3 d-inline-block" style="font-size: 1.1rem; border-radius: 12px;">
                                <i class="fas <?= $statusIcons[$badgeClass] ?? 'fa-circle' ?> me-2"></i>
                                <?= $statusName ?>
                            </span>
                        </div>
                        <small class="text-muted d-block mt-3">
                            <i class="far fa-calendar-alt"></i>
                            <?= date('d/m/Y', strtotime($model->date_s)) ?>
                            <?php if (!empty($model->hour)): ?>
                                · <i class="far fa-clock"></i> <?= date('H:i', strtotime($model->hour)) ?>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>

                <!-- INFORMACIÓN DEL SEGUIMIENTO -->
                <div class="card details-card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información</h5>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-calendar-alt"></i> Fecha</span>
                                <span class="info-value"><?= date('d/m/Y', strtotime($model->date_s)) ?></span>
                            </div>
                            <?php if (!empty($model->hour)): ?>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-clock"></i> Hora</span>
                                <span class="info-value"><?= date('H:i', strtotime($model->hour)) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-user-tie"></i> Registrado por</span>
                                <span class="info-value">
                                    <?= $model->user ? Html::encode($model->user->name . ' ' . $model->user->lastname1) : 'N/A' ?>
                                </span>
                            </div>
                            <?php if (!empty($model->date_f)): ?>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-calendar-check"></i> Próximo</span>
                                <span class="info-value">
                                    <span class="badge bg-warning text-dark">
                                        <?= date('d/m/Y', strtotime($model->date_f)) ?>
                                    </span>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- LEAD ASOCIADO -->
                <?php if ($lead): ?>
                <div class="card details-card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user"></i> Lead Asociado</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="profile-avatar-large">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <h5 class="profile-name-large mt-2"><?= Html::encode($lead->name . ' ' . $lead->lastname) ?></h5>
                            <p class="profile-phone-large">
                                <i class="fas fa-phone"></i> <?= Html::encode($lead->phone) ?>
                            </p>
                            <span class="badge bg-<?= $lead->getStatusBadgeClass() ?>">
                                <?= $lead->getStatusName() ?>
                            </span>
                        </div>
                        <div class="profile-actions-large">
                            <a href="tel:<?= Html::encode($lead->phone) ?>" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-phone"></i> Llamar
                            </a>
                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $lead->phone) ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>
                            <?= Html::a('<i class="fas fa-eye"></i> Ver', ['lead/details', 'id' => $lead->id_lead], [
                                'class' => 'btn btn-sm btn-outline-primary'
                            ]) ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ACCIONES RÁPIDAS -->
                <div class="card details-card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bolt"></i> Acciones Rápidas</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php if ($lead): ?>
                                <?= Html::a('<i class="fas fa-plus"></i> Nuevo Seguimiento', 
                                    ['sales-tracking/create', 'leadId' => $lead->id_lead], [
                                    'class' => 'btn btn-success btn-sm'
                                ]) ?>
                            <?php endif; ?>
                            <?= Html::a('<i class="fas fa-list"></i> Ver Todos los Seguimientos', 
                                ['sales-tracking/index'], [
                                'class' => 'btn btn-outline-secondary btn-sm'
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA -->
            <div class="col-lg-8">

                <!-- CAMBIAR ESTADO -->
                <div class="card details-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-exchange-alt"></i> Cambiar Estado</h5>
                    </div>
                    <div class="card-body">
                        <?php $form = ActiveForm::begin([
                            'action' => ['sales-tracking/update-status', 'id' => $model->id_sales_tracking],
                            'method' => 'post',
                            'id' => 'change-status-form',
                        ]); ?>

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-tag me-1"></i> Nuevo Estado
                                </label>
                                <?php 
                                $statusOptions = \app\models\Status::find()
                                    ->select(['status', 'id_status'])
                                    ->indexBy('id_status')
                                    ->column();
                                ?>
                                <?= Html::dropDownList('status_id', $model->id_status, $statusOptions, [
                                    'class' => 'form-select',
                                    'id' => 'status-select',
                                ]) ?>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <?= Html::submitButton('<i class="fas fa-save"></i> Actualizar', [
                                    'class' => 'btn btn-success w-100',
                                    'id' => 'btn-update-status'
                                ]) ?>
                            </div>
                        </div>

                        <div class="mt-3" id="comments-group" style="display:none;">
                            <label class="form-label fw-bold">
                                <i class="fas fa-comment me-1"></i> Comentario del Cambio
                            </label>
                            <?= Html::textarea('status_comment', '', [
                                'class' => 'form-control',
                                'rows' => 2,
                                'placeholder' => 'Razón del cambio de estado...'
                            ]) ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>

                <!-- OBSERVACIONES -->
                <div class="card details-card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-sticky-note"></i> Observaciones del Seguimiento</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($model->comments)): ?>
                            <p class="mb-0" style="white-space: pre-wrap;"><?= nl2br(Html::encode($model->comments)) ?></p>
                        <?php else: ?>
                            <p class="text-muted text-center mb-0">
                                <i class="fas fa-info-circle"></i> Sin observaciones
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- HISTORIAL DEL LEAD -->
                <?php if ($lead): ?>
                <?php
                $trackings = $lead->salesTrackings ?? [];
                $trackingsCount = count($trackings);
                ?>
                <div class="card details-card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-history"></i> Historial del Lead</h5>
                        <div>
                            <span class="badge bg-secondary"><?= $trackingsCount ?></span>
                            <?= Html::a('<i class="fas fa-plus"></i> Nuevo', 
                                ['sales-tracking/create', 'leadId' => $lead->id_lead], [
                                'class' => 'btn btn-sm btn-outline-primary ms-2'
                            ]) ?>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 details-table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Fecha</th>
                                        <th>Hora</th>
                                        <th>Estado</th>
                                        <th>Comentarios</th>
                                        <th style="width: 60px;" class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($trackings)): ?>
                                        <?php foreach ($trackings as $index => $tracking): ?>
                                            <?php
                                            $isCurrent = ($tracking->id_sales_tracking == $model->id_sales_tracking);
                                            ?>
                                            <tr class="<?= $isCurrent ? 'table-info' : '' ?>">
                                                <td>
                                                    <?= $index + 1 ?>
                                                    <?php if ($isCurrent): ?>
                                                        <i class="fas fa-star text-warning ms-1" title="Seguimiento actual"></i>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= date('d/m/Y', strtotime($tracking->date_s)) ?></td>
                                                <td><?= date('H:i', strtotime($tracking->hour)) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $tracking->getStatusBadgeClass() ?>">
                                                        <?= $tracking->getStatusName() ?>
                                                    </span>
                                                </td>
                                                <td><?= \yii\helpers\StringHelper::truncate(Html::encode($tracking->comments ?? ''), 40, '...') ?></td>
                                                <td class="text-center">
                                                    <?php if ($isCurrent): ?>
                                                        <span class="badge bg-info">Actual</span>
                                                    <?php else: ?>
                                                        <?= Html::a('<i class="fas fa-eye"></i>', 
                                                            ['sales-tracking/view', 'id' => $tracking->id_sales_tracking], [
                                                            'class' => 'btn btn-info btn-sm btn-action',
                                                            'title' => 'Ver'
                                                        ]) ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                                No hay seguimientos asociados
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>

    </div><!-- /.sales-tracking-wrapper -->
</div><!-- /.sales-tracking-view -->

<?php
$js = <<<JS
$(document).ready(function() {
    $('#status-select').on('change', function() {
        var currentStatus = '<?= $model->id_status ?>';
        var newStatus = $(this).val();
        
        if (newStatus != currentStatus) {
            $('#comments-group').slideDown();
        } else {
            $('#comments-group').slideUp();
        }
    });

    $('#change-status-form').on('submit', function(e) {
        var currentStatus = '<?= $model->id_status ?>';
        var newStatus = $('#status-select').val();
        
        if (newStatus != currentStatus) {
            var statusName = $('#status-select option:selected').text();
            if (!confirm('¿Estás seguro de cambiar el estado a "' + statusName + '"?')) {
                e.preventDefault();
            }
        }
    });
});
JS;
$this->registerJs($js);
?>