<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\StringHelper;

$this->title = 'Detalles de Cotización - #' . $model->id_quote;
$this->params['breadcrumbs'][] = ['label' => 'Cotizaciones', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Registrar CSS
$this->registerCssFile('@web/css/quote.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerCssFile('@web/css/quote-details.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class], 'position' => \yii\web\View::POS_HEAD]);
$this->registerCssFile('@web/css/leads.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerCssFile('@web/css/dashboard.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

$total = $model->total_amount ?? 0;
$downPayment = $model->down_payment ?? 0;
$pending = $total - $downPayment;

// Obtener datos del lead
$lead = $model->lead;
$leadName = $lead ? $lead->name . ' ' . $lead->lastname : 'Lead no disponible';
$leadPhone = $lead ? $lead->phone : 'Sin teléfono';
$leadStatus = $lead ? $lead->getStatusName() : 'Sin estado';
$leadBadgeClass = $lead ? $lead->getStatusBadgeClass() : 'secondary';

// 🔥 Usar el método del modelo que ya verifica isAgent()
$agentName = $model->getAgentName();

// Obtener seguimientos del lead
$trackings = $lead ? $lead->salesTrackings ?? [] : [];

$statusName = $model->getStatusName();
$badgeClass = $model->getStatusBadgeClass();

$statusColors = [
    'success' => '#1cc88a',
    'warning' => '#f6c23e',
    'danger' => '#e74a3b',
    'info' => '#36b9cc',
    'secondary' => '#6c757d',
    'primary' => '#4e73df',
];

$statusIcons = [
    'success' => 'fa-check-circle',
    'warning' => 'fa-clock',
    'danger' => 'fa-times-circle',
    'info' => 'fa-dollar-sign',
    'secondary' => 'fa-circle',
    'primary' => 'fa-circle',
];
?>

<div class="quote-details">
    <div class="container-fluid">
        
        <!-- HEADER -->
        <div class="leads-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Ventas</span><span class="separator">›</span>
                    <span>Cotizaciones</span><span class="separator">›</span>
                    <span class="current">Detalles</span>
                </div>
                <h1 class="page-title">
                    Cotización #<?= $model->id_quote ?>
                    <span class="badge ms-2" style="background-color: <?= $statusColors[$badgeClass] ?? '#6c757d' ?>; font-size: 14px; padding: 4px 14px;">
                        <i class="fas <?= $statusIcons[$badgeClass] ?? 'fa-circle' ?>"></i> <?= $statusName ?>
                    </span>
                    <small>$<?= number_format($total, 0, '.', ',') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['index'], ['class' => 'btn btn-secondary btn-sm']) ?>
                <?= Html::a('<i class="fas fa-print"></i> Imprimir', ['print', 'id' => $model->id_quote], ['class' => 'btn btn-info btn-sm', 'target' => '_blank']) ?>
            </div>
        </div>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="row g-3">
            
            <!-- COLUMNA IZQUIERDA -->
            <div class="col-lg-4">
                
                <!-- Resumen de la Cotización -->
                <div class="card details-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-file-invoice"></i> Resumen de Cotización</h5>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-hashtag"></i> ID</span>
                                <span class="info-value">#<?= $model->id_quote ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-calendar-alt"></i> Fecha</span>
                                <span class="info-value"><?= date('d/m/Y', strtotime($model->date_quote)) ?></span>
                            </div>
                            <?php if (!empty($model->hour_quote)): ?>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-clock"></i> Hora</span>
                                <span class="info-value"><?= date('H:i', strtotime($model->hour_quote)) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-tag"></i> Estado</span>
                                <span class="info-value">
                                    <span class="badge bg-<?= $badgeClass ?>"><?= $statusName ?></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financiero -->
                <div class="card details-card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Financiero</h5>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-dollar-sign"></i> Total</span>
                                <span class="info-value" style="font-size: 1.3rem; font-weight: 700; color: #1cc88a;">
                                    $<?= number_format($total, 0, '.', ',') ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-hand-holding-usd"></i> Pago Inicial</span>
                                <span class="info-value">$<?= number_format($downPayment, 0, '.', ',') ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-hourglass-half"></i> Pendiente</span>
                                <span class="info-value" style="color: <?= $pending > 0 ? '#e74a3b' : '#1cc88a' ?>; font-weight: 600;">
                                    $<?= number_format($pending, 0, '.', ',') ?>
                                </span>
                            </div>
                            <?php if ($pending > 0): ?>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-percent"></i> Pagado</span>
                                <span class="info-value">
                                    <?= $total > 0 ? round(($downPayment / $total) * 100) : 0 ?>%
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Lead Asociado -->
                <div class="card details-card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user"></i> Lead Asociado</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <span class="lead-profile-avatar">
                                <i class="fas fa-user-circle"></i>
                            </span>
                            <h5 class="lead-profile-name"><?= Html::encode($leadName) ?></h5>
                            <p class="lead-profile-phone"><i class="fas fa-phone"></i> <?= Html::encode($leadPhone) ?></p>
                            <span class="badge bg-<?= $leadBadgeClass ?>"><?= $leadStatus ?></span>
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-user-tie"></i> Agente</span>
                                <span class="info-value"><?= Html::encode($agentName) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-calendar-alt"></i> Registro Lead</span>
                                <span class="info-value"><?= $lead ? date('d/m/Y', strtotime($lead->created_at)) : 'N/A' ?></span>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <?php if ($lead): ?>
                                <?= Html::a('<i class="fas fa-eye"></i> Ver Lead', ['lead/details', 'id' => $lead->id_lead], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                                <?= Html::a('<i class="fas fa-phone"></i> Llamar', 'tel:' . $leadPhone, ['class' => 'btn btn-outline-success btn-sm']) ?>
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $leadPhone) ?>" target="_blank" class="btn btn-outline-success btn-sm">
                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA -->
            <div class="col-lg-8">
                
                <!-- Observaciones -->
                <?php if (!empty($model->comments)): ?>
                <div class="card details-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-sticky-note"></i> Observaciones</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0"><?= nl2br(Html::encode($model->comments)) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Seguimientos del Lead -->
                <div class="card details-card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-history"></i> Seguimientos del Lead</h5>
                        <div>
                            <span class="badge bg-secondary"><?= count($trackings) ?></span>
                            <?php if ($lead): ?>
                                <?= Html::a('<i class="fas fa-plus"></i> Nuevo', ['sales-tracking/create', 'leadId' => $lead->id_lead], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Fecha</th>
                                        <th>Hora</th>
                                        <th>Estado</th>
                                        <th>Comentarios</th>
                                        <th class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($trackings)): ?>
                                        <?php foreach ($trackings as $index => $tracking): ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td><?= date('d/m/Y', strtotime($tracking->date_s)) ?></td>
                                                <td><?= date('H:i', strtotime($tracking->hour)) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $tracking->getStatusBadgeClass() ?>">
                                                        <?= $tracking->getStatusName() ?>
                                                    </span>
                                                </td>
                                                <td><?= StringHelper::truncate(Html::encode($tracking->comments ?? ''), 40, '...') ?></td>
                                                <td class="text-center">
                                                    <?= Html::a('<i class="fas fa-eye"></i>', ['sales-tracking/view', 'id' => $tracking->id_sales_tracking], [
                                                        'class' => 'btn btn-info btn-sm',
                                                        'title' => 'Ver'
                                                    ]) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-3">
                                                <i class="fas fa-inbox"></i> No hay seguimientos asociados
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php if (count($trackings) > 5): ?>
                    <div class="card-footer text-end">
                        <?php if ($lead): ?>
                            <?= Html::a('Ver todos los seguimientos', ['sales-tracking/index', 'search' => $lead->id_lead], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Cotizaciones del mismo lead -->
                <?php if ($lead && $lead->getQuotes()->count() > 1): ?>
                <div class="card details-card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-file-invoice"></i> Otras Cotizaciones del Lead</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Fecha</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lead->getQuotes()->limit(5)->all() as $quote): ?>
                                        <?php if ($quote->id_quote == $model->id_quote) continue; ?>
                                        <tr>
                                            <td>#<?= $quote->id_quote ?></td>
                                            <td><?= date('d/m/Y', strtotime($quote->date_quote)) ?></td>
                                            <td><strong>$<?= number_format($quote->total_amount ?? 0, 0, '.', ',') ?></strong></td>
                                            <td>
                                                <span class="badge bg-<?= $quote->getStatusBadgeClass() ?>">
                                                    <?= $quote->getStatusName() ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?= Html::a('<i class="fas fa-eye"></i>', ['quote/view', 'id' => $quote->id_quote], [
                                                    'class' => 'btn btn-info btn-sm',
                                                    'title' => 'Ver'
                                                ]) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>