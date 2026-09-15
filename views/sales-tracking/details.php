<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\StringHelper;

$this->title = 'Detalles de Seguimiento - #' . $model->id_sales_tracking;
$this->params['breadcrumbs'][] = ['label' => 'Seguimientos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Registrar CSS
$this->registerCssFile('@web/css/sales-tracking.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerCssFile('@web/css/quote-details.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class], 'position' => \yii\web\View::POS_HEAD]);
$this->registerCssFile('@web/css/leads.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerCssFile('@web/css/dashboard.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

// ============================================
// DATOS DEL SEGUIMIENTO
// ============================================
$lead = $model->lead;
$leadName = $lead ? $lead->name . ' ' . $lead->lastname : 'Lead no disponible';
$leadPhone = $lead ? $lead->phone : 'Sin teléfono';
$leadStatus = $lead ? $lead->getStatusName() : 'Sin estado';
$leadBadgeClass = $lead ? $lead->getStatusBadgeClass() : 'secondary';

// 🔥 Usar el método del modelo que ya verifica isAgent()
$agentName = $model->getAgentName();

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

// ============================================
// SEGUIMIENTOS DEL LEAD
// ============================================
$trackings = $lead ? $lead->salesTrackings ?? [] : [];
$trackingsCount = count($trackings);
?>

<div class="quote-details">
    <div class="container-fluid">
        
        <!-- ============================================ -->
        <!-- HEADER -->
        <!-- ============================================ -->
        <div class="leads-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Ventas</span><span class="separator">›</span>
                    <span>Seguimientos</span><span class="separator">›</span>
                    <span class="current">Detalles</span>
                </div>
                <h1 class="page-title">
                    Seguimiento #<?= $model->id_sales_tracking ?>
                    <span class="badge ms-2" style="background-color: <?= $statusColors[$badgeClass] ?? '#6c757d' ?>; font-size: 14px; padding: 4px 14px;">
                        <i class="fas <?= $statusIcons[$badgeClass] ?? 'fa-circle' ?>"></i> <?= $statusName ?>
                    </span>
                    <small><?= date('d/m/Y H:i', strtotime($model->date_s . ' ' . $model->hour)) ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['index'], ['class' => 'btn btn-secondary btn-sm']) ?>
                <?php if ($isAdmin || $isSuperAdmin): ?>
                    <?= Html::a('<i class="fas fa-edit"></i> Editar', ['update', 'id' => $model->id_sales_tracking], ['class' => 'btn btn-primary btn-sm']) ?>
                <?php endif; ?>
                <?= Html::a('<i class="fas fa-print"></i> Imprimir', ['print', 'id' => $model->id_sales_tracking], ['class' => 'btn btn-info btn-sm', 'target' => '_blank']) ?>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- CONTENIDO PRINCIPAL -->
        <!-- ============================================ -->
        <div class="row g-3">
            
            <!-- COLUMNA IZQUIERDA -->
            <div class="col-lg-4">
                
                <!-- Resumen del Seguimiento -->
                <div class="card details-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-phone"></i> Resumen del Seguimiento</h5>
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
                                <span class="info-label"><i class="fas fa-tag"></i> Estado</span>
                                <span class="info-value">
                                    <span class="badge bg-<?= $badgeClass ?>"><?= $statusName ?></span>
                                </span>
                            </div>
                            <?php if (!empty($model->date_f)): ?>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-calendar-check"></i> Próximo</span>
                                <span class="info-value"><?= date('d/m/Y', strtotime($model->date_f)) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-user-tie"></i> Registrado por</span>
                                <span class="info-value">
                                    <?= $model->user ? Html::encode($model->user->name . ' ' . $model->user->lastname1) : 'N/A' ?>
                                </span>
                            </div>
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
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-comments"></i> Seguimientos</span>
                                <span class="info-value">
                                    <span class="badge bg-primary"><?= $trackingsCount ?></span>
                                </span>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <?php if ($lead): ?>
                                <?= Html::a('<i class="fas fa-eye"></i> Ver Lead', ['lead/view', 'id' => $lead->id_lead], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                                <?= Html::a('<i class="fas fa-phone"></i> Llamar', 'tel:' . $leadPhone, ['class' => 'btn btn-outline-success btn-sm']) ?>
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $leadPhone) ?>" target="_blank" class="btn btn-outline-success btn-sm">
                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="card details-card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bolt"></i> Acciones Rápidas</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php if ($lead): ?>
                                <?= Html::a('<i class="fas fa-plus"></i> Nuevo Seguimiento', ['sales-tracking/create', 'leadId' => $lead->id_lead], [
                                    'class' => 'btn btn-success btn-sm'
                                ]) ?>
                            <?php endif; ?>
                            <?= Html::a('<i class="fas fa-list"></i> Ver Todos los Seguimientos', ['sales-tracking/index'], [
                                'class' => 'btn btn-outline-secondary btn-sm'
                            ]) ?>
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
                        <h5 class="mb-0"><i class="fas fa-sticky-note"></i> Observaciones del Seguimiento</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0"><?= nl2br(Html::encode($model->comments)) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Historial de Seguimientos -->
                <div class="card details-card <?= !empty($model->comments) ? 'mt-3' : '' ?>">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-history"></i> Historial de Seguimientos del Lead</h5>
                        <div>
                            <span class="badge bg-secondary"><?= $trackingsCount ?></span>
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
                                        <th>Próximo</th>
                                        <th class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($trackings)): ?>
                                        <?php foreach ($trackings as $index => $tracking): ?>
                                            <?php 
                                            $isCurrent = ($tracking->id_sales_tracking == $model->id_sales_tracking);
                                            $rowClass = $isCurrent ? 'table-info' : '';
                                            ?>
                                            <tr class="<?= $rowClass ?>">
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
                                                <td>
                                                    <?= StringHelper::truncate(Html::encode($tracking->comments ?? ''), 50, '...') ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($tracking->date_f)): ?>
                                                        <span class="text-warning">
                                                            <i class="fas fa-calendar-check"></i>
                                                            <?= date('d/m/Y', strtotime($tracking->date_f)) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($isCurrent): ?>
                                                        <span class="badge bg-info">Actual</span>
                                                    <?php else: ?>
                                                        <?= Html::a('<i class="fas fa-eye"></i>', ['sales-tracking/details', 'id' => $tracking->id_sales_tracking], [
                                                            'class' => 'btn btn-info btn-sm',
                                                            'title' => 'Ver Detalles'
                                                        ]) ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-3">
                                                <i class="fas fa-inbox"></i> No hay seguimientos asociados
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php if ($trackingsCount > 5): ?>
                    <div class="card-footer text-end">
                        <?php if ($lead): ?>
                            <?= Html::a('Ver todos los seguimientos', ['sales-tracking/index', 'search' => $lead->id_lead], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

    </div>
</div>