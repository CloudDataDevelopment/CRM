<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$this->title = 'Detalle de Seguimiento #' . $model->id_sales_tracking;
$this->params['breadcrumbs'][] = ['label' => 'Seguimientos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Registrar Font Awesome
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css');

// ============================================
// 🔥 OBTENER EL LEAD ASOCIADO
// ============================================
$lead = $model->lead;  // ✅ Acceso a la relación

// ============================================
// 🔥 DATOS DE ESTADO DEL SEGUIMIENTO
// ============================================
$statusName = $model->getStatusName();
$badgeClass = $model->getStatusBadgeClass();

// Icono según estado del seguimiento
$icon = 'fa-circle';
$statusLower = strtolower($statusName);
if ($statusLower == 'completado') $icon = 'fa-check-circle';
elseif ($statusLower == 'cancelado') $icon = 'fa-times-circle';
elseif ($statusLower == 'en progreso' || $statusLower == 'en_progreso') $icon = 'fa-spinner';
elseif ($statusLower == 'programado') $icon = 'fa-calendar-check';
elseif ($statusLower == 'pendiente') $icon = 'fa-clock';
?>

<div class="sales-tracking-view">
    <div class="row">
        <div class="col-md-8">
            <!-- ============================================ -->
            <!-- CARD PRINCIPAL                              -->
            <!-- ============================================ -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-phone-alt me-2"></i>
                        Seguimiento #<?= $model->id_sales_tracking ?>
                    </h4>
                    <div>
                        <?= Html::a('<i class="fas fa-arrow-left"></i>', ['index'], [
                            'class' => 'btn btn-light btn-sm me-1',
                            'title' => 'Volver'
                        ]) ?>
                        <?php if ($isAdmin || ($isAgent && $lead && $lead->id_user == Yii::$app->user->id)): ?>
                            <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $model->id_sales_tracking], [
                                'class' => 'btn btn-warning btn-sm me-1',
                                'title' => 'Editar'
                            ]) ?>
                            <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $model->id_sales_tracking], [
                                'class' => 'btn btn-danger btn-sm',
                                'title' => 'Eliminar',
                                'data' => [
                                    'confirm' => '¿Estás seguro de eliminar este seguimiento?',
                                    'method' => 'post',
                                ],
                            ]) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <!-- ============================================ -->
                    <!-- ESTADO ACTUAL (GRANDE Y DESTACADO)          -->
                    <!-- ============================================ -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="p-3 rounded-3 text-center" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                <small class="text-muted text-uppercase fw-bold">Estado Actual</small>
                                <span class="badge bg-<?= $badgeClass ?> p-3 d-inline-block" style="font-size: 1.5rem; border-radius: 12px;">
                                    <i class="fas <?= $icon ?> me-2"></i>
                                    <?= $statusName ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- INFORMACIÓN DEL SEGUIMIENTO                 -->
                    <!-- ============================================ -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light h-100">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">
                                        <i class="fas fa-user me-2"></i> Lead Asociado
                                    </h6>
                                    <?php if ($lead): ?>
                                        <p class="mb-1"><strong><?= Html::encode($lead->name . ' ' . $lead->lastname) ?></strong></p>
                                        <p class="mb-0 text-muted">
                                            <i class="fas fa-phone me-1"></i> 
                                            <a href="tel:<?= Html::encode($lead->phone) ?>"><?= Html::encode($lead->phone) ?></a>
                                        </p>
                                        <p class="mb-0 text-muted">
                                            <i class="fas fa-tag me-1"></i> 
                                            <span class="badge bg-<?= $lead->getStatusBadgeClass() ?>">
                                                <?= $lead->getStatusName() ?>
                                            </span>
                                        </p>
                                        <?= Html::a('<i class="fas fa-external-link-alt"></i> Ver lead', ['lead/view', 'id' => $lead->id_lead], [
                                            'class' => 'btn btn-sm btn-outline-primary mt-2',
                                            'target' => '_blank'
                                        ]) ?>
                                    <?php else: ?>
                                        <p class="text-muted">Lead no disponible</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light h-100">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">
                                        <i class="fas fa-calendar-alt me-2"></i> Detalles del Seguimiento
                                    </h6>
                                    <p class="mb-1">
                                        <strong>Fecha:</strong> 
                                        <span class="badge bg-info"><?= date('d/m/Y', strtotime($model->date_s)) ?></span>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Hora:</strong> 
                                        <span class="badge bg-secondary"><?= $model->hour ? date('H:i', strtotime($model->hour)) : 'N/A' ?></span>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Usuario:</strong> 
                                        <?php 
                                        $user = $model->user;
                                        echo $user ? Html::encode($user->username) : '<span class="text-muted">N/A</span>';
                                        ?>
                                    </p>
                                    <?php if ($model->date_f): ?>
                                        <p class="mb-0">
                                            <strong>Próximo:</strong> 
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock me-1"></i>
                                                <?= date('d/m/Y', strtotime($model->date_f)) ?>
                                            </span>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- COMENTARIOS                                 -->
                    <!-- ============================================ -->
                    <div class="card bg-light mb-0">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0"><i class="fas fa-comment me-2"></i> Comentarios</h6>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($model->comments)): ?>
                                <p class="mb-0" style="white-space: pre-wrap;"><?= nl2br(Html::encode($model->comments)) ?></p>
                            <?php else: ?>
                                <p class="text-muted text-center mb-0">
                                    <i class="fas fa-info-circle"></i> Sin comentarios
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- COLUMNA LATERAL - CAMBIAR ESTADO             -->
        <!-- ============================================ -->
        <div class="col-md-4">
            <!-- ============================================ -->
            <!-- CARD PARA CAMBIAR ESTADO                     -->
            <!-- ============================================ -->
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exchange-alt me-2"></i>
                        Cambiar Estado
                    </h5>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'action' => ['sales-tracking/update-status', 'id' => $model->id_sales_tracking],
                        'method' => 'post',
                        'id' => 'change-status-form',
                    ]); ?>

                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-tag me-1"></i> Nuevo Estado
                        </label>
                        <?php 
                        // 🔥 OBTENER LISTA DE ESTADOS DISPONIBLES
                        $statusOptions = \app\models\Status::find()
                            ->select(['status', 'id_status'])
                            ->indexBy('id_status')
                            ->column();
                        
                        // Obtener el ID del estado actual
                        $currentStatusId = $model->id_status;
                        ?>
                        <?= Html::dropDownList('status_id', $currentStatusId, $statusOptions, [
                            'class' => 'form-select form-select-lg',
                            'id' => 'status-select',
                            'style' => 'font-weight: 500;'
                        ]) ?>
                    </div>

                    <div class="form-group mb-3" id="comments-group" style="display:none;">
                        <label class="form-label fw-bold">
                            <i class="fas fa-comment me-1"></i> Comentario del Cambio
                        </label>
                        <?= Html::textarea('status_comment', '', [
                            'class' => 'form-control',
                            'rows' => 2,
                            'placeholder' => 'Razón del cambio de estado...'
                        ]) ?>
                    </div>

                    <div class="d-grid gap-2">
                        <?= Html::submitButton('<i class="fas fa-save me-2"></i> Actualizar Estado', [
                            'class' => 'btn btn-success btn-lg',
                            'id' => 'btn-update-status'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
                <div class="card-footer bg-light">
                    <div class="row text-center">
                        <div class="col-6">
                            <small class="text-muted">Estado actual</small>
                            <br>
                            <span class="badge bg-<?= $badgeClass ?> fs-6">
                                <?= $statusName ?>
                            </span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Última actualización</small>
                            <br>
                            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($model->date_s . ' ' . ($model->hour ?? '00:00'))) ?></small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- RESUMEN DEL LEAD                             -->
            <!-- ============================================ -->
            <?php if ($lead): ?>
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Resumen del Lead
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Seguimientos</span>
                        <span class="fw-bold"><?= $lead->getSalesTrackings()->count() ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Estado Actual</span>
                        <span class="badge bg-<?= $lead->getStatusBadgeClass() ?>">
                            <?= $lead->getStatusName() ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Agente Asignado</span>
                        <span class="fw-bold"><?= $lead->getAgentName() ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Registro</span>
                        <span><?= date('d/m/Y', strtotime($lead->created_at)) ?></span>
                    </div>
                    <hr>
                    <?= Html::a('<i class="fas fa-user me-2"></i> Ver Lead Completo', ['lead/view', 'id' => $lead->id_lead], [
                        'class' => 'btn btn-outline-info w-100',
                        'target' => '_blank'
                    ]) ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- JAVASCRIPT                                   -->
<!-- ============================================ -->
<?php
$js = <<<JS
$(document).ready(function() {
    // Mostrar/ocultar campo de comentario
    $('#status-select').on('change', function() {
        var currentStatus = '<?= $model->id_status ?>';
        var newStatus = $(this).val();
        
        if (newStatus != currentStatus) {
            $('#comments-group').slideDown();
        } else {
            $('#comments-group').slideUp();
        }
    });

    // Confirmación antes de enviar
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