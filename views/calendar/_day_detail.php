<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\StringHelper;

$events = isset($events) ? $events : [];
$day = isset($day) ? $day : date('Y-m-d');
$error = isset($error) ? $error : null;

// Helper para clases
$getBadgeClass = function($type) {
    $badges = [
        'seguimiento' => 'info',
        'proximo_seguimiento' => 'danger',
        'cotizacion' => 'warning',
        'lead' => 'primary',
    ];
    return $badges[$type] ?? 'secondary';
};

$getTypeLabel = function($type) {
    $labels = [
        'seguimiento' => 'Seguimiento',
        'proximo_seguimiento' => 'Próximo Seguimiento',
        'cotizacion' => 'Cotización',
        'lead' => 'Lead Nuevo',
    ];
    return $labels[$type] ?? 'Evento';
};

$getStatusBadge = function($status) {
    $badges = [
        'Pendiente' => 'warning',
        'pendiente' => 'warning',
        'Programado' => 'info',
        'programado' => 'info',
        'En Progreso' => 'primary',
        'En progreso' => 'primary',
        'Completado' => 'success',
        'completado' => 'success',
        'Cancelado' => 'danger',
        'cancelado' => 'danger',
        'Nuevo' => 'info',
        'nuevo' => 'info',
        'Contactado' => 'primary',
        'contactado' => 'primary',
        'Cliente' => 'success',
        'cliente' => 'success',
        'Aprobada' => 'success',
        'aprobada' => 'success',
        'Rechazada' => 'danger',
        'rechazada' => 'danger',
        'Pagada' => 'primary',
        'pagada' => 'primary',
    ];
    $statusLower = strtolower(trim($status));
    return $badges[$statusLower] ?? 'secondary';
};

$getIconClass = function($type) {
    $icons = [
        'seguimiento' => 'fa-phone',
        'proximo_seguimiento' => 'fa-clock',
        'cotizacion' => 'fa-file-invoice',
        'lead' => 'fa-user',
    ];
    return $icons[$type] ?? 'fa-calendar';
};

if ($error) {
    echo '<div class="modal-empty">
            <i class="fas fa-exclamation-triangle text-danger"></i>
            <h5>Error</h5>
            <p>' . Html::encode($error) . '</p>
        </div>';
    return;
}

$total = count($events);
$completados = 0;
$pendientes = 0;
foreach ($events as $e) {
    $status = strtolower($e['status'] ?? '');
    if ($status == 'completado' || $status == 'completada') {
        $completados++;
    } elseif ($status == 'pendiente') {
        $pendientes++;
    }
}
?>

<!-- ESTADÍSTICAS -->
<div class="modal-stats">
    <div class="modal-stat">
        <div class="stat-number"><?= $total ?></div>
        <div class="stat-label">Total Actividades</div>
    </div>
    <div class="modal-stat">
        <div class="stat-number text-success"><?= $completados ?></div>
        <div class="stat-label">Completados</div>
    </div>
    <div class="modal-stat">
        <div class="stat-number text-warning"><?= $pendientes ?></div>
        <div class="stat-label">Pendientes</div>
    </div>
</div>

<!-- LISTA DE ACTIVIDADES -->
<div class="modal-activity-list">
    <?php if (!empty($events)): ?>
        <?php foreach ($events as $event): 
            $badgeClass = $getBadgeClass($event['type']);
            $iconClass = $getIconClass($event['type']);
            $typeLabel = $getTypeLabel($event['type']);
            $statusBadge = $getStatusBadge($event['status']);
        ?>
            <div class="modal-activity-item" onclick="window.location.href='<?= Url::to($event['url']) ?>'">
                <div class="activity-icon bg-<?= $badgeClass ?>">
                    <i class="fas <?= $iconClass ?>"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-title">
                        <?= Html::encode($event['lead_name']) ?>
                        <span class="badge bg-<?= $badgeClass ?>"><?= $typeLabel ?></span>
                        <span class="activity-time">
                            <i class="far fa-clock"></i> <?= date('H:i', strtotime($event['date'])) ?>
                        </span>
                    </div>
                    <div class="activity-desc">
                        <?= StringHelper::truncate(Html::encode($event['title']), 100, '...') ?>
                    </div>
                    <div class="activity-meta">
                        <span><i class="fas fa-phone"></i> <?= $event['lead_phone'] ?></span>
                        <?php if (!empty($event['status']) && $event['status'] != 'Sin estado'): ?>
                            <span class="badge bg-<?= $statusBadge ?>"><?= $event['status'] ?></span>
                        <?php endif; ?>
                        <?php if (isset($event['total_amount'])): ?>
                            <span class="badge bg-success">
                                $<?= number_format($event['total_amount'], 0, ',', '.') ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="modal-empty">
            <i class="fas fa-calendar-day"></i>
            <h5>No hay actividades</h5>
            <p>No hay actividades registradas para este día</p>
        </div>
    <?php endif; ?>
</div>