<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\StringHelper;

$this->title = 'Detalles de Cotización - #' . $model->id_quote;
$this->params['breadcrumbs'][] = ['label' => 'Cotizaciones', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/quote.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerCssFile('@web/css/quote-details.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class], 'position' => \yii\web\View::POS_HEAD]);
$this->registerCssFile('@web/css/leads.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerCssFile('@web/css/dashboard.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

// 🔥 Datos del modelo
$total = (int) $model->total_amount;
$totalPagado = $model->getTotalPaid();
$pending = $model->getRealPending();
$porcentajePagado = $model->getPaymentPercentage();
$completado = $model->isFullyPaid();

$lead = $model->lead;
$leadName = $lead ? $lead->name . ' ' . $lead->lastname : 'Lead no disponible';
$leadPhone = $lead ? $lead->phone : 'Sin teléfono';
$leadStatus = $lead ? $lead->getStatusName() : 'Sin estado';
$leadBadgeClass = $lead ? $lead->getStatusBadgeClass() : 'secondary';

$agentName = $model->getAgentName();
$trackings = $lead ? $lead->salesTrackings ?? [] : [];

$statusName = $model->getStatusName();
$badgeClass = $model->getStatusBadgeClass();
$notas = $model->getNotes();

// 🔥 DETECTAR SI ESTÁ EN PAPELERA
$isTrash = (strtolower(trim($statusName)) === 'eliminado');

// 🔥 DETECTAR SI YA ESTÁ COMPLETADO
$isCompletado = (strtolower(trim($statusName)) === 'completado');

$statusColors = [
    'success'   => '#1cc88a',
    'warning'   => '#f6c23e',
    'danger'    => '#e74a3b',
    'info'      => '#36b9cc',
    'secondary' => '#6c757d',
    'primary'   => '#4e73df',
];

$statusIcons = [
    'success'   => 'fa-check-circle',
    'warning'   => 'fa-clock',
    'danger'    => 'fa-times-circle',
    'info'      => 'fa-dollar-sign',
    'secondary' => 'fa-circle',
    'primary'   => 'fa-circle',
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
                <?php if ($isTrash): ?>
                    <!-- 🔥 EN PAPELERA: Volver a Papelera + Restaurar -->
                    <?= Html::a('<i class="fas fa-arrow-left"></i> Volver a Papelera', ['trash'], ['class' => 'btn btn-secondary btn-sm']) ?>
                    <?php if ($isAdmin || $isSuperAdmin): ?>
                        <?= Html::a(
                            '<i class="fas fa-undo"></i> Restaurar',
                            ['restore', 'id' => $model->id_quote],
                            [
                                'class' => 'btn btn-success btn-sm',
                                'data' => [
                                    'confirm' => '¿Restaurar esta cotización? Volverá al listado activo con estado "Pendiente".',
                                    'method'  => 'post',
                                ],
                            ]
                        ) ?>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- 🔥 EN INDEX: Volver + Imprimir -->
                    <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['index'], ['class' => 'btn btn-secondary btn-sm']) ?>
                    <?= Html::a('<i class="fas fa-print"></i> Imprimir', ['print', 'id' => $model->id_quote], ['class' => 'btn btn-info btn-sm', 'target' => '_blank']) ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- 🔥 ALERTA DE PAPELERA -->
        <?php if ($isTrash): ?>
            <div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
                <i class="fas fa-trash me-2" style="font-size: 1.2rem;"></i>
                <div>
                    <strong>Esta cotización está en la papelera.</strong>
                    Puedes restaurarla para que vuelva al listado activo con estado <strong>"Pendiente"</strong>.
                </div>
            </div>
        <?php endif; ?>

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

                        <!-- 🔥 BOTÓN CAMBIAR A COMPLETADO -->
                        <?php if (!$isTrash && !$isCompletado): ?>
                            <div class="mt-3 pt-3 border-top">
                                <?= Html::a(
                                    '<i class="fas fa-check-circle"></i> Marcar como Completado',
                                    ['update-status'],
                                    [
                                        'class' => 'btn btn-success btn-sm w-100',
                                        'data' => [
                                            'method'  => 'post',
                                            'params'  => [
                                                'id_quote' => $model->id_quote,
                                                'status'   => 'Completado',
                                            ],
                                            'confirm' => '¿Marcar esta cotización como COMPLETADA? Esta acción cambiará el estado.',
                                        ],
                                    ]
                                ) ?>
                            </div>
                        <?php elseif ($isCompletado): ?>
                            <div class="mt-3 pt-3 border-top">
                                <div class="alert alert-success text-center mb-0 py-2">
                                    <i class="fas fa-check-circle"></i>
                                    <strong>Cotización Completada</strong>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Financiero -->
                <div class="card details-card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Financiero</h5>
                        <?php if ($completado): ?>
                            <span class="badge bg-success"><i class="fas fa-check-circle"></i> Pagado</span>
                        <?php endif; ?>
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
                                <span class="info-label"><i class="fas fa-coins"></i> Total Pagado</span>
                                <span class="info-value" style="font-weight: 700; color: #4e73df;">
                                    $<?= number_format($totalPagado, 0, '.', ',') ?>
                                </span>
                            </div>
                            <?php if ($completado): ?>
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-check-circle"></i> Estado de Pago</span>
                                    <span class="info-value" style="color: #1cc88a; font-weight: 700;">
                                        ✅ Pagado en su totalidad
                                    </span>
                                </div>
                            <?php else: ?>
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-hourglass-half"></i> Pendiente</span>
                                    <span class="info-value" style="color: #e74a3b; font-weight: 600;">
                                        $<?= number_format($pending, 0, '.', ',') ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-percent"></i> Progreso</span>
                                    <span class="info-value"><?= $porcentajePagado ?>%</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Barra de progreso -->
                        <div class="progress mt-3" style="height: 8px;">
                            <div class="progress-bar bg-success"
                                 style="width: <?= $porcentajePagado ?>%"
                                 role="progressbar"></div>
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

                        <!-- 🔥 SOLO "VER LEAD" -->
                        <?php if ($lead): ?>
                            <div class="text-center mt-3">
                                <?= Html::a(
                                    '<i class="fas fa-eye"></i> Ver Lead',
                                    ['lead/details', 'id' => $lead->id_lead],
                                    ['class' => 'btn btn-outline-primary btn-sm']
                                ) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA -->
            <div class="col-lg-8">

                <!-- Observaciones -->
                <?php if (!empty($notas)): ?>
                    <div class="card details-card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-sticky-note"></i> Observaciones</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0"><?= nl2br(Html::encode($notas)) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Seguimientos del Lead -->
                <div class="card details-card <?= !empty($notas) ? 'mt-3' : '' ?>">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-history"></i> Seguimientos del Lead
                            <?php if (count($trackings) > 0): ?>
                                <span class="badge bg-primary ms-2"><?= count($trackings) ?></span>
                            <?php endif; ?>
                        </h5>
                        <?php if ($lead && !$isTrash): ?>
                            <?= Html::a(
                                '<i class="fas fa-plus"></i> Nuevo Seguimiento',
                                ['sales-tracking/create', 'leadId' => $lead->id_lead],
                                ['class' => 'btn btn-sm btn-outline-primary']
                            ) ?>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($trackings)): ?>
                            <div class="timeline">
                                <?php foreach (array_slice($trackings, 0, 10) as $tracking): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-marker">
                                            <i class="fas fa-comment"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="timeline-header">
                                                <strong><?= Html::encode($tracking->comments ?? 'Seguimiento') ?></strong>
                                                <span class="badge bg-<?= $tracking->getStatusBadgeClass() ?>">
                                                    <?= $tracking->getStatusName() ?>
                                                </span>
                                            </div>
                                            <div class="timeline-meta">
                                                <span>
                                                    <i class="fas fa-calendar"></i>
                                                    <?= date('d/m/Y', strtotime($tracking->date_s)) ?>
                                                    <?php if (!empty($tracking->hour)): ?>
                                                        <?= date('H:i', strtotime($tracking->hour)) ?>
                                                    <?php endif; ?>
                                                </span>
                                                <?php if ($tracking->user): ?>
                                                    <span>
                                                        <i class="fas fa-user"></i>
                                                        <?= Html::encode($tracking->user->name . ' ' . $tracking->user->lastname1) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($trackings) > 10): ?>
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        Mostrando 10 de <?= count($trackings) ?> seguimientos
                                    </small>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                <p class="mb-0">No hay seguimientos registrados para este lead</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 🔥 SOLO MOVER A PAPELERA -->
                <?php if (!$isTrash && ($isAdmin || $isSuperAdmin)): ?>
                    <div class="card details-card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-cog"></i> Acciones</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-2">
                                <?= Html::a(
                                    '<i class="fas fa-trash"></i> Mover a Papelera',
                                    ['delete', 'id' => $model->id_quote],
                                    [
                                        'class' => 'btn btn-danger btn-sm',
                                        'data' => [
                                            'confirm' => '¿Mover esta cotización a la papelera?',
                                            'method'  => 'post',
                                        ],
                                    ]
                                ) ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>