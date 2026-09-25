<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$this->title = 'Nueva Cotización';
$this->params['breadcrumbs'][] = ['label' => 'Cotizaciones', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/leads.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerCssFile('@web/css/quote.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class], 'position' => \yii\web\View::POS_HEAD]);

// 🔥 LEAD PRESELECCIONADO (si viene desde /quote/create?leadId=5)
$lead = isset($lead) ? $lead : null;
$leadId = isset($leadId) ? $leadId : null;

if ($lead) {
    $this->title = 'Nueva Cotización - ' . $lead->name . ' ' . $lead->lastname;
    $this->params['breadcrumbs'][] = [
        'label' => $lead->name . ' ' . $lead->lastname,
        'url' => ['lead/details', 'id' => $lead->id_lead]
    ];
}
$this->params['breadcrumbs'][] = $this->title;

// ============================================
// FUNCIÓN AUXILIAR PARA BADGE
// ============================================
if (!function_exists('getBadgeClass')) {
    function getBadgeClass($statusName) {
        $badges = [
            'pendiente' => 'warning',
            'aprobada' => 'success',
            'rechazada' => 'danger',
            'pagada' => 'info',
            'cancelada' => 'secondary',
            'completado' => 'success',
        ];
        return $badges[strtolower(trim($statusName))] ?? 'secondary';
    }
}

$statusOptions = isset($statusOptions) ? $statusOptions : [];
$leadsList = isset($leadsList) ? $leadsList : [];
$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isAgent = isset($isAgent) ? $isAgent : false;
$isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;
?>

<!-- 🔥 CONTENEDOR PRINCIPAL CON SOMBRA -->
<div class="quote-create">
    <div class="quote-wrapper">

        <!-- HEADER -->
        <div class="quote-header">
            <div class="header-left">
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Ventas</span><span class="separator">›</span>
                    <span>Cotizaciones</span><span class="separator">›</span>
                    <span class="current">Nueva</span>
                </div>
                <h1 class="page-title">
                    <i class="fas fa-file-invoice text-success me-2"></i>
                    <?= Html::encode($this->title) ?>
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?php if ($lead): ?>
                    <?= Html::a('<i class="fas fa-arrow-left"></i> Volver al Lead', 
                        ['lead/details', 'id' => $lead->id_lead], 
                        ['class' => 'btn btn-secondary btn-sm btn-quote-action']) ?>
                <?php else: ?>
                    <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', 
                        ['index'], 
                        ['class' => 'btn btn-secondary btn-sm btn-quote-action']) ?>
                <?php endif; ?>
                <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', [
                    'class' => 'btn btn-primary btn-sm btn-quote-action',
                    'form' => 'quote-create-form',
                ]) ?>
            </div>
        </div>

        <!-- CONTENIDO -->
        <div class="row g-3">
            <!-- COLUMNA IZQUIERDA: FORMULARIO -->
            <div class="col-md-8">
                <div class="card details-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-file-invoice"></i> Información de la Cotización
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php $form = ActiveForm::begin([
                            'options' => ['id' => 'quote-create-form'],
                            'fieldConfig' => [
                                'options' => ['class' => 'form-group mb-3'],
                                'labelOptions' => ['class' => 'control-label'],
                                'errorOptions' => ['class' => 'help-block'],
                            ],
                        ]); ?>

                        <!-- LEAD PRESELECCIONADO -->
                        <?php if ($lead): ?>
                            <div class="lead-locked-box mb-4">
                                <div class="lead-locked-header">
                                    <i class="fas fa-user"></i>
                                    <strong><?= Html::encode($lead->name . ' ' . $lead->lastname) ?></strong>
                                    <span class="badge bg-<?= $lead->getStatusBadgeClass() ?> ms-auto">
                                        <?= $lead->getStatusName() ?>
                                    </span>
                                </div>
                                <div class="lead-locked-info">
                                    <span>
                                        <i class="fas fa-phone"></i>
                                        <?= Html::encode($lead->phone) ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-calendar"></i>
                                        Registrado: <?= date('d/m/Y', strtotime($lead->created_at)) ?>
                                    </span>
                                    <?php if ($lead->company): ?>
                                        <span>
                                            <i class="fas fa-building"></i>
                                            <?= Html::encode($lead->company->name) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-lock"></i> Este lead está vinculado automáticamente a la cotización.
                                </small>
                            </div>
                            <?= Html::activeHiddenInput($model, 'id_lead', ['value' => $lead->id_lead]) ?>
                        <?php else: ?>
                            <!-- SELECTOR DE LEAD -->
                            <?= $form->field($model, 'id_lead')->dropDownList(
                                $leadsList,
                                ['prompt' => 'Seleccione un lead...', 'class' => 'form-select']
                            )->label('Lead <span class="text-danger">*</span>')
                            ->hint('Selecciona el cliente para esta cotización', ['class' => 'text-muted']) ?>
                        <?php endif; ?>

                        <!-- FECHA Y HORA -->
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'date_quote')->input('date', [
                                    'class' => 'form-control'
                                ])->label('Fecha <span class="text-danger">*</span>') ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'hour_quote')->input('time', [
                                    'class' => 'form-control'
                                ])->label('Hora <span class="text-danger">*</span>') ?>
                            </div>
                        </div>

                        <!-- MONTOS -->
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'total_amount')->textInput([
                                    'type' => 'number',
                                    'step' => '1',
                                    'min' => '0',
                                    'placeholder' => '0',
                                    'class' => 'form-control',
                                    'id' => 'total-amount-input',
                                ])->label('Monto Total <span class="text-danger">*</span>') ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'down_payment')->textInput([
                                    'type' => 'number',
                                    'step' => '1',
                                    'min' => '0',
                                    'placeholder' => '0',
                                    'class' => 'form-control',
                                    'id' => 'down-payment-input',
                                ])->label('Enganche')
                                ->hint('Pago inicial (opcional)', ['class' => 'text-muted']) ?>
                            </div>
                        </div>

                        <!-- CÁLCULO VISUAL DEL SALDO PENDIENTE -->
                        <div class="alert alert-info d-flex align-items-center mb-3" role="alert" style="border-radius: 8px; border-left: 4px solid #0091FF; padding: 10px 14px; font-size: 0.85rem;">
                            <i class="fas fa-calculator me-2" style="font-size: 1rem;"></i>
                            <div>
                                <strong>Saldo pendiente:</strong>
                                <span id="pending-payment-display">$0</span>
                            </div>
                        </div>

                        <!-- ESTADO -->
                        <?= $form->field($model, 'id_status')->dropDownList(
                            $statusOptions,
                            ['prompt' => 'Seleccione un estado...', 'class' => 'form-select']
                        )->label('Estado <span class="text-danger">*</span>') ?>

                        <!-- COMENTARIOS -->
                        <?= $form->field($model, 'comments')->textarea([
                            'rows' => 4,
                            'placeholder' => 'Comentarios adicionales...',
                            'class' => 'form-control'
                        ])->label('Comentarios') ?>

                        <!-- BOTONES -->
                        <div class="form-group mt-4 d-flex gap-2 flex-wrap">
                            <?= Html::submitButton('<i class="fas fa-save"></i> Guardar Cotización', [
                                'class' => 'btn btn-success'
                            ]) ?>
                            <?php if ($lead): ?>
                                <?= Html::a('<i class="fas fa-times"></i> Cancelar', 
                                    ['lead/details', 'id' => $lead->id_lead], 
                                    ['class' => 'btn btn-secondary']) ?>
                            <?php else: ?>
                                <?= Html::a('<i class="fas fa-times"></i> Cancelar', 
                                    ['index'], 
                                    ['class' => 'btn btn-secondary']) ?>
                            <?php endif; ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA: SIDEBAR -->
            <div class="col-md-4">

                <?php if ($lead): ?>
                    <!-- CARD DEL LEAD VINCULADO -->
                    <div class="card details-card mb-3">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-user-check text-primary"></i> Lead Vinculado
                            </h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="profile-avatar-large">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <h5 class="profile-name-large mt-2">
                                <?= Html::encode($lead->name . ' ' . $lead->lastname) ?>
                            </h5>
                            <p class="profile-phone-large">
                                <i class="fas fa-phone"></i> <?= Html::encode($lead->phone) ?>
                            </p>

                            <div class="mb-2">
                                <span class="badge bg-<?= $lead->getStatusBadgeClass() ?>">
                                    <i class="fas <?= $lead->getStatusIcon() ?>"></i>
                                    <?= $lead->getStatusName() ?>
                                </span>
                            </div>

                            <div class="profile-actions-large">
                                <a href="tel:<?= Html::encode($lead->phone) ?>" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-phone"></i> Llamar
                                </a>
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $lead->phone) ?>"
                                   target="_blank"
                                   class="btn btn-sm btn-outline-success">
                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                </a>
                                <?= Html::a('<i class="fas fa-eye"></i> Ver',
                                    ['lead/details', 'id' => $lead->id_lead],
                                    ['class' => 'btn btn-sm btn-outline-primary']) ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- CARD: INFORMACIÓN -->
                <div class="card details-card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle text-primary"></i> Información
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            El <strong>lead</strong> es obligatorio.
                        </p>
                        <p class="text-muted small mb-2">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            La <strong>fecha</strong> y <strong>hora</strong> son obligatorias.
                        </p>
                        <p class="text-muted small mb-2">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            El <strong>monto total</strong> debe ser mayor a $0.
                        </p>
                        <p class="text-muted small mb-0">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            El <strong>enganche</strong> es opcional.
                        </p>
                    </div>
                </div>

                <!-- CARD: ESTADOS DISPONIBLES -->
                <div class="card details-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-tags text-primary"></i> Estados Disponibles
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($statusOptions)): ?>
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($statusOptions as $id => $nombre): ?>
                                    <li class="mb-1">
                                        <span class="badge bg-<?= getBadgeClass($nombre) ?>">
                                            <?= ucfirst($nombre) ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted small mb-0">
                                <i class="fas fa-inbox"></i> No hay estados configurados
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>

    </div><!-- /.quote-wrapper -->
</div><!-- /.quote-create -->

<script>
// ============================================
// 🔥 CÁLCULO DE SALDO PENDIENTE EN VIVO
// ============================================
(function() {
    var totalInput = document.getElementById('total-amount-input');
    var downInput = document.getElementById('down-payment-input');
    var display = document.getElementById('pending-payment-display');

    function updatePending() {
        if (!display) return;

        var total = parseInt(totalInput ? totalInput.value : 0) || 0;
        var down = parseInt(downInput ? downInput.value : 0) || 0;
        var pending = total - down;

        if (pending < 0) pending = 0;

        // Formatear con separador de miles
        var formatted = '$' + pending.toLocaleString('es-MX');

        display.textContent = formatted;

        // Cambiar color según el estado
        if (pending === 0 && total > 0) {
            display.style.color = '#1cc88a'; // verde
            display.style.fontWeight = '700';
        } else if (pending > 0) {
            display.style.color = '#f6c23e'; // amarillo
            display.style.fontWeight = '700';
        } else {
            display.style.color = '#6c757d'; // gris
            display.style.fontWeight = '500';
        }
    }

    if (totalInput) totalInput.addEventListener('input', updatePending);
    if (downInput) downInput.addEventListener('input', updatePending);

    // Inicializar
    updatePending();
})();
</script>