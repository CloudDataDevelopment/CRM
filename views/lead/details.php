<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\StringHelper;
use yii\widgets\ActiveForm;

$this->title = 'Detalles del Lead - ' . $model->name . ' ' . $model->lastname;
$this->params['breadcrumbs'][] = ['label' => 'Leads', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Registrar CSS
$this->registerCssFile('@web/css/leads.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerCssFile('@web/css/dashboard.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerCssFile('@web/css/lead-details.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class], 'position' => \yii\web\View::POS_HEAD]);

// Variables
$statusList = isset($statusList) ? $statusList : [];
$trackings = $model->salesTrackings ?? [];
$quotes = $model->quotes ?? [];
$totalTrackings = count($trackings);
$totalQuotes = count($quotes);

// Funciones de estado
function getStatusColor($statusName) {
    $colors = [
        'Nuevo' => '#4e73df',
        'Contactado' => '#17a2b8',
        'Procesando' => '#f6c23e',
        'Calificado' => '#1cc88a',
        'Cliente' => '#1cc88a',
        'Cancelado' => '#6c757d',
        'Perdido' => '#e74a3b',
        'Sin Estado' => '#6c757d',
    ];
    return $colors[trim($statusName)] ?? '#6c757d';
}

function getStatusIcon($statusName) {
    $icons = [
        'Nuevo' => 'fa-plus-circle',
        'Contactado' => 'fa-phone',
        'Procesando' => 'fa-spinner',
        'Calificado' => 'fa-star',
        'Cliente' => 'fa-user-check',
        'Cancelado' => 'fa-ban',
        'Perdido' => 'fa-times-circle',
        'Sin Estado' => 'fa-question-circle',
    ];
    return $icons[trim($statusName)] ?? 'fa-circle';
}

$statusName = $model->getStatusName();
$statusColor = getStatusColor(trim($statusName));
$statusIcon = getStatusIcon(trim($statusName));
$phone = $model->phone;
$whatsappLink = $phone ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $phone) : '#';

// 🔥 Usar el método del modelo que ya verifica isAgent()
$agentName = $model->getAgentName();

$createdAt = strtotime($model->created_at);
$daysSince = floor((time() - $createdAt) / (60 * 60 * 24));
?>

<div class="lead-details">
    <div class="lead-details-container">
        
        <!-- HEADER -->
        <div class="leads-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Contactos</span><span class="separator">›</span>
                    <span>Leads</span><span class="separator">›</span>
                    <span class="current">Detalles</span>
                </div>
                <h1 class="page-title">
                    <?= Html::encode($model->name . ' ' . $model->lastname) ?>
                    <span class="badge ms-2" style="background-color: <?= $statusColor ?>; font-size: 14px; padding: 4px 14px;">
                        <i class="fas <?= $statusIcon ?>"></i> <?= $statusName ?>
                    </span>
                </h1>
            </div>
            <div class="header-actions">
                <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['index'], ['class' => 'btn btn-secondary btn-sm btn-header-action']) ?>
            </div>
        </div>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="row g-3">
            
            <!-- COLUMNA IZQUIERDA: INFORMACIÓN -->
            <div class="col-lg-4">
                
                <!-- Perfil -->
                <div class="card details-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user-circle"></i> Perfil</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="profile-avatar-large">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <h4 class="profile-name-large mt-2"><?= Html::encode($model->name . ' ' . $model->lastname) ?></h4>
                        <p class="profile-phone-large"><i class="fas fa-phone"></i> <?= Html::encode($phone) ?></p>
                        
                        <div class="profile-actions-large">
                            <?php if ($phone): ?>
                                <a href="tel:<?= Html::encode($phone) ?>" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-phone"></i> Llamar
                                </a>
                                <a href="<?= $whatsappLink ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                </a>
                            <?php endif; ?>
                            <a href="<?= Url::to(['sales-tracking/create', 'leadId' => $model->id_lead]) ?>" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-calendar-plus"></i> Seguimiento
                            </a>
                            <a href="<?= Url::to(['quote/create', 'leadId' => $model->id_lead]) ?>" class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-file-invoice"></i> Cotización
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Información -->
                <div class="card details-card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información</h5>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-calendar-alt"></i> Registro</span>
                                <span class="info-value"><?= date('d/m/Y H:i', strtotime($model->created_at)) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-user"></i> Agente</span>
                                <span class="info-value"><?= Html::encode($agentName) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-clock"></i> Días activo</span>
                                <span class="info-value"><?= $daysSince ?> días</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-tag"></i> Estado</span>
                                <span class="info-value">
                                    <span class="badge" style="background-color: <?= $statusColor ?>;">
                                        <i class="fas <?= $statusIcon ?>"></i> <?= $statusName ?>
                                    </span>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-building"></i> Empresa</span>
                                <span class="info-value"><?= $model->company ? Html::encode($model->company->name) : 'Sin empresa' ?></span>
                            </div>
                            <?php if (!empty($model->comments)): ?>
                            <div class="info-item info-item-full">
                                <span class="info-label"><i class="fas fa-sticky-note"></i> Observaciones</span>
                                <span class="info-value"><?= nl2br(Html::encode($model->comments)) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="card details-card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Estadísticas</h5>
                    </div>
                    <div class="card-body">
                        <div class="stats-grid-large">
                            <div class="stat-item-large">
                                <div class="stat-number"><?= $totalTrackings ?></div>
                                <div class="stat-label">Seguimientos</div>
                            </div>
                            <div class="stat-item-large">
                                <div class="stat-number"><?= $totalQuotes ?></div>
                                <div class="stat-label">Cotizaciones</div>
                            </div>
                            <div class="stat-item-large">
                                <div class="stat-number"><?= $daysSince ?></div>
                                <div class="stat-label">Días activo</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA: ACTIVIDADES -->
            <div class="col-lg-8">
                
                <!-- Seguimientos -->
                <div class="card details-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-history"></i> Seguimientos</h5>
                        <div>
                            <span class="badge bg-secondary"><?= $totalTrackings ?></span>
                            <a href="<?= Url::to(['sales-tracking/create', 'leadId' => $model->id_lead]) ?>" class="btn btn-sm btn-outline-primary ms-2">
                                <i class="fas fa-plus"></i> Nuevo
                            </a>
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
                                                        'class' => 'btn btn-info btn-sm btn-action',
                                                        'title' => 'Ver'
                                                    ]) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                                No hay seguimientos registrados
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php if ($totalTrackings > 5): ?>
                    <div class="card-footer text-end">
                        <a href="<?= Url::to(['sales-tracking/index', 'search' => $model->id_lead]) ?>" class="btn btn-sm btn-outline-primary">
                            Ver todos los seguimientos <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Cotizaciones -->
                <div class="card details-card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-file-invoice"></i> Cotizaciones</h5>
                        <div>
                            <span class="badge bg-secondary"><?= $totalQuotes ?></span>
                            <a href="<?= Url::to(['quote/create', 'leadId' => $model->id_lead]) ?>" class="btn btn-sm btn-outline-primary ms-2">
                                <i class="fas fa-plus"></i> Nueva
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 details-table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Fecha</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th>Pendiente</th>
                                        <th style="width: 60px;" class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($quotes)): ?>
                                        <?php foreach ($quotes as $index => $quote): ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td><?= date('d/m/Y', strtotime($quote->date_quote)) ?></td>
                                                <td><strong>$<?= number_format($quote->total_amount, 0, '.', ',') ?></strong></td>
                                                <td>
                                                    <span class="badge bg-<?= $quote->getStatusBadgeClass() ?>">
                                                        <?= $quote->getStatusName() ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($quote->pending_payment > 0): ?>
                                                        <span class="text-danger">$<?= number_format($quote->pending_payment, 0, '.', ',') ?></span>
                                                    <?php else: ?>
                                                        <span class="text-success"><i class="fas fa-check-circle"></i> $0</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?= Html::a('<i class="fas fa-eye"></i>', ['quote/view', 'id' => $quote->id_quote], [
                                                        'class' => 'btn btn-info btn-sm btn-action',
                                                        'title' => 'Ver'
                                                    ]) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                                No hay cotizaciones registradas
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php if ($totalQuotes > 5): ?>
                    <div class="card-footer text-end">
                        <a href="<?= Url::to(['quote/index', 'search' => $model->id_lead]) ?>" class="btn btn-sm btn-outline-primary">
                            Ver todas las cotizaciones <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

    </div>
</div>