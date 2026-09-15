<?php

use yii\helpers\Html;
use yii\helpers\Url;

$model = isset($model) ? $model : null;
$error = isset($error) ? $error : null;

if ($error) {
    echo '<div class="slide-panel-content">
            <div class="slide-panel-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-danger">Error</h5>
                    <button type="button" class="btn-close-panel" onclick="closePanel()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="slide-panel-body">
                <div class="text-center text-danger py-5">
                    <i class="fas fa-exclamation-triangle fa-3x d-block mb-3"></i>
                    <p>' . Html::encode($error) . '</p>
                    <button class="btn btn-secondary btn-sm mt-3" onclick="closePanel()">Cerrar</button>
                </div>
            </div>
            <div class="slide-panel-footer">
                <button class="btn-footer btn-footer-secondary" onclick="closePanel()">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>';
    return;
}

if (!$model) {
    echo '<div class="slide-panel-content">
            <div class="slide-panel-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-muted">Lead no encontrado</h5>
                    <button type="button" class="btn-close-panel" onclick="closePanel()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="slide-panel-body">
                <div class="text-center text-muted py-5">
                    <i class="fas fa-inbox fa-3x d-block mb-3"></i>
                    <p>Lead no encontrado</p>
                    <button class="btn btn-secondary btn-sm mt-3" onclick="closePanel()">Cerrar</button>
                </div>
            </div>
            <div class="slide-panel-footer">
                <button class="btn-footer btn-footer-secondary" onclick="closePanel()">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>';
    return;
}

// ============================================
// OBTENER DATOS
// ============================================

try {
    $totalQuotes = $model->getQuotes()->count();
} catch (\Exception $e) {
    $totalQuotes = 0;
}

try {
    $agentName = $model->getAgentName();
} catch (\Exception $e) {
    $agentName = 'Sin asignar';
}

try {
    $createdAt = strtotime($model->created_at);
    $daysSince = floor((time() - $createdAt) / (60 * 60 * 24));
} catch (\Exception $e) {
    $daysSince = 0;
}

// ============================================
// FUNCIONES DE ESTADO
// ============================================

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

// ============================================
// OBTENER DATOS DEL ESTADO
// ============================================

if (empty($model->id_status) || $model->id_status == 0) {
    $statusName = 'Sin Estado';
    $statusIcon = 'fa-question-circle';
    $statusColor = '#6c757d';
    $hasValidStatus = false;
} else {
    try {
        $statusName = $model->getStatusName();
    } catch (\Exception $e) {
        $statusName = 'Sin Estado';
    }

    try {
        $statusIcon = $model->getStatusIcon();
    } catch (\Exception $e) {
        $statusIcon = 'fa-question-circle';
    }

    $statusColor = getStatusColor(trim($statusName));
    $hasValidStatus = true;
}

$phone = $model->phone;
$whatsappLink = $phone ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $phone) : '#';

// ============================================
// OBTENER COTIZACIONES Y SEGUIMIENTOS
// ============================================

try {
    $quotes = $model->getQuotes()->limit(3)->all();
} catch (\Exception $e) {
    $quotes = [];
}

try {
    $trackings = $model->salesTrackings ?? [];
} catch (\Exception $e) {
    $trackings = [];
}

// ============================================
// FUNCIÓN PARA OBTENER ICONO DE ACTIVIDAD
// ============================================

function getActivityIcon($statusSales) {
    $icons = [
        'Llamada' => 'fa-phone',
        'Cita' => 'fa-calendar-check',
        'Reunión' => 'fa-users',
        'Correo' => 'fa-envelope',
        'WhatsApp' => 'fa-whatsapp',
        'Seguimiento' => 'fa-comment',
    ];
    return $icons[$statusSales] ?? 'fa-comment';
}

function getActivityColor($statusSales) {
    $colors = [
        'Llamada' => '#28a745',
        'Cita' => '#ffc107',
        'Reunión' => '#4e73df',
        'Correo' => '#17a2b8',
        'WhatsApp' => '#25D366',
        'Seguimiento' => '#6c757d',
    ];
    return $colors[$statusSales] ?? '#6c757d';
}
?>

<div class="slide-panel-content">

    <!-- ========================================== -->
    <!-- HEADER - Solo botón cerrar                 -->
    <!-- ========================================== -->
    <div class="slide-panel-header">
        <div class="d-flex justify-content-end align-items-center">
            <button type="button" class="btn-close-panel" onclick="closePanel()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- CUERPO                                     -->
    <!-- ========================================== -->
    <div class="slide-panel-body">

        <!-- ========================================== -->
        <!-- APARTADO 1: PERFIL DEL LEAD                -->
        <!-- ========================================== -->
        <div class="profile-section">
            <!-- Avatar -->
            <div class="profile-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            
            <!-- Nombre -->
            <div class="profile-name">
                <?= Html::encode($model->name . ' ' . $model->lastname) ?>
            </div>
            
            <!-- Estado -->
            <div class="profile-status">
                <span class="status-badge" style="background-color: <?= $statusColor ?>;">
                    <i class="fas <?= $statusIcon ?>"></i> <?= $statusName ?>
                </span>
            </div>
            
            <!-- Teléfono -->
            <div class="profile-phone">
                <i class="fas fa-phone"></i> <?= Html::encode($phone) ?>
            </div>
            
            <!-- Botones de acción -->
            <div class="profile-actions">
                <?php if ($phone): ?>
                    <a href="tel:<?= Html::encode($phone) ?>" class="action-btn action-btn-phone" title="Llamar">
                        <i class="fas fa-phone"></i>
                        <span>Llamar</span>
                    </a>
                    <a href="<?= $whatsappLink ?>" target="_blank" class="action-btn action-btn-whatsapp" title="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                        <span>WhatsApp</span>
                    </a>
                <?php endif; ?>
                <a href="#" class="action-btn action-btn-meeting" onclick="event.preventDefault(); closePanel(); window.location.href='<?= Url::to(['calendar/create', 'leadId' => $model->id_lead]) ?>';">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Reunión</span>
                </a>
                <a href="#" class="action-btn action-btn-email" onclick="event.preventDefault(); alert('Función en desarrollo');">
                    <i class="fas fa-envelope"></i>
                    <span>Email</span>
                </a>
            </div>
        </div>

        <!-- Separador -->
        <hr class="section-divider">

        <!-- ========================================== -->
        <!-- APARTADO 2: INFORMACIÓN DEL LEAD           -->
        <!-- ========================================== -->
        <div class="info-section">
            <div class="info-title">
                <i class="fas fa-info-circle"></i> Información del Lead
            </div>
            
            <div class="info-row">
                <span class="info-label"><i class="fas fa-calendar-alt"></i> Fecha de Registro</span>
                <span class="info-value"><?= date('d/m/Y H:i', strtotime($model->created_at)) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-user"></i> Agente Asignado</span>
                <span class="info-value"><?= Html::encode($agentName) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-clock"></i> Días en Proceso</span>
                <span class="info-value"><?= $daysSince ?> días</span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-phone"></i> Teléfono</span>
                <span class="info-value"><?= Html::encode($phone) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-tag"></i> Estado</span>
                <span class="info-value">
                    <span class="status-badge-small" style="background-color: <?= $statusColor ?>;">
                        <i class="fas <?= $statusIcon ?>"></i> <?= $statusName ?>
                    </span>
                </span>
            </div>
            <?php if (!empty($model->comments)): ?>
            <div class="info-row info-row-comments">
                <span class="info-label"><i class="fas fa-sticky-note"></i> Observaciones</span>
                <span class="info-value"><?= nl2br(Html::encode($model->comments)) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Separador -->
        <hr class="section-divider">

        <!-- ========================================== -->
        <!-- APARTADO 3: CALIFICACIÓN                   -->
        <!-- ========================================== -->
        <div class="rating-section">
            <div class="rating-header">
                <span><i class="fas fa-star"></i> Calificación</span>
                <span class="rating-score"><?= 35 ?>/100</span>
            </div>
            <div class="rating-stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star <?= $i <= 2 ? 'text-warning' : 'text-secondary' ?>" style="opacity: <?= $i <= 2 ? '1' : '0.3' ?>; font-size: 16px;"></i>
                <?php endfor; ?>
                <span class="rating-label">• 2 estrellas</span>
            </div>
            <div class="rating-metrics">
                <div class="metric-item">
                    <span class="metric-label">Valor Estimado</span>
                    <span class="metric-value">$150,000 MXN</span>
                </div>
                <div class="metric-item">
                    <span class="metric-label">Probabilidad</span>
                    <span class="metric-value">10%</span>
                </div>
                <div class="metric-item">
                    <span class="metric-label">Cierre Estimado</span>
                    <span class="metric-value">30/08/2024</span>
                </div>
            </div>
        </div>

        <!-- Separador -->
        <hr class="section-divider">

        <!-- ========================================== -->
        <!-- APARTADO 4: COTIZACIONES                   -->
        <!-- ========================================== -->
        <div class="list-section">
            <div class="list-header">
                <span><i class="fas fa-file-invoice"></i> Cotizaciones</span>
                <span class="list-badge"><?= $totalQuotes ?></span>
                <a href="<?= Url::to(['quote/create', 'leadId' => $model->id_lead]) ?>" target="_blank" class="list-add" onclick="closePanel();">
                    <i class="fas fa-plus"></i>
                </a>
            </div>
            <?php if (!empty($quotes)): ?>
                <?php foreach ($quotes as $quote): ?>
                <div class="list-item">
                    <span class="list-item-amount">$<?= number_format($quote->total_amount ?? 0, 0, ',', '.') ?></span>
                    <span class="list-item-status"><?= Html::encode($quote->getStatusName() ?? 'Pendiente') ?></span>
                    <span class="list-item-date"><?= date('d/m/Y', strtotime($quote->date_quote ?? 'now')) ?></span>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="list-empty">No hay cotizaciones</div>
            <?php endif; ?>
        </div>

        <!-- Separador -->
        <hr class="section-divider">

        <!-- ========================================== -->
        <!-- APARTADO 5: SEGUIMIENTO                    -->
        <!-- ========================================== -->
        <div class="list-section">
            <div class="list-header">
                <span><i class="fas fa-history"></i> Seguimiento</span>
                <span class="list-badge"><?= count($trackings) ?></span>
                <a href="<?= Url::to(['sales-tracking/create', 'leadId' => $model->id_lead]) ?>" target="_blank" class="list-add" onclick="closePanel();">
                    <i class="fas fa-plus"></i>
                </a>
            </div>
            <?php if (!empty($trackings)): ?>
                <?php foreach (array_slice($trackings, 0, 3) as $tracking): ?>
                <?php 
                    $statusSales = $tracking->status_sales ?? 'Seguimiento';
                    $activityIcon = getActivityIcon($statusSales);
                    $activityColor = getActivityColor($statusSales);
                ?>
                <div class="list-item-activity">
                    <span class="activity-icon" style="background: <?= $activityColor ?>;">
                        <i class="fas <?= $activityIcon ?>"></i>
                    </span>
                    <span class="activity-text"><?= Html::encode($statusSales) ?></span>
                    <span class="activity-date"><?= date('d/m/Y H:i', strtotime($tracking->date_s)) ?></span>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="list-empty">No hay seguimiento</div>
            <?php endif; ?>
        </div>

    </div>

    <!-- ========================================== -->
    <!-- FOOTER                                     -->
    <!-- ========================================== -->
    <div class="slide-panel-footer">
        <a href="<?= Url::to(['lead/update', 'id' => $model->id_lead, 'return' => 'index']) ?>" target="_blank" class="btn-footer btn-footer-primary" onclick="closePanel();">
            <i class="fas fa-edit"></i> Editar
        </a>
        <a href="<?= Url::to(['sales-tracking/create', 'leadId' => $model->id_lead]) ?>" target="_blank" class="btn-footer btn-footer-success" onclick="closePanel();">
            <i class="fas fa-calendar-plus"></i> Seguimiento
        </a>
        <a href="<?= Url::to(['quote/create', 'leadId' => $model->id_lead]) ?>" target="_blank" class="btn-footer btn-footer-info" onclick="closePanel();">
            <i class="fas fa-file-invoice"></i> Cotización
        </a>
        <a href="<?= Url::to(['lead/details', 'id' => $model->id_lead]) ?>" target="_blank" class="btn-footer btn-footer-secondary" onclick="closePanel();">
            <i class="fas fa-external-link-alt"></i> Abrir
        </a>
    </div>
</div>

<script>
// Asegurar que closePanel esté disponible
if (typeof closePanel === 'undefined') {
    window.closePanel = function() {
        if (window.parent && typeof window.parent.closePanel === 'function') {
            window.parent.closePanel();
        } else if (typeof window.opener !== 'undefined' && window.opener.closePanel) {
            window.opener.closePanel();
        }
    };
}
</script>