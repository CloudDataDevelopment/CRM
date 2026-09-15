<?php

use yii\helpers\Html;
use yii\helpers\Url;

$model = isset($model) ? $model : null;
$error = isset($error) ? $error : null;
$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;

if ($error) {
    echo '<div class="slide-panel-content">
            <div class="slide-panel-header">
                <button type="button" class="btn-close-panel" onclick="cerrarPanel()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="slide-panel-body">
                <div class="text-center text-danger py-5">
                    <i class="fas fa-exclamation-triangle fa-3x d-block mb-3"></i>
                    <p>' . Html::encode($error) . '</p>
                    <button class="btn btn-secondary btn-sm mt-3" onclick="cerrarPanel()">Cerrar</button>
                </div>
            </div>
            <div class="slide-panel-footer">
                <button class="btn-footer btn-footer-secondary" onclick="cerrarPanel()">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>';
    return;
}

if (!$model) {
    echo '<div class="slide-panel-content">
            <div class="slide-panel-header">
                <button type="button" class="btn-close-panel" onclick="cerrarPanel()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="slide-panel-body">
                <div class="text-center text-muted py-5">
                    <i class="fas fa-inbox fa-3x d-block mb-3"></i>
                    <p>Reporte no encontrado</p>
                    <button class="btn btn-secondary btn-sm mt-3" onclick="cerrarPanel()">Cerrar</button>
                </div>
            </div>
            <div class="slide-panel-footer">
                <button class="btn-footer btn-footer-secondary" onclick="cerrarPanel()">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>';
    return;
}

$statusName = $model->getStatusName();
$badgeClass = $model->getStatusBadgeClass();

$statusColors = [
    'success' => '#1cc88a',
    'warning' => '#f6c23e',
    'danger' => '#e74a3b',
    'info' => '#36b9cc',
    'secondary' => '#6c757d',
];

$statusIcons = [
    'success' => 'fa-check-circle',
    'warning' => 'fa-clock',
    'danger' => 'fa-times-circle',
    'info' => 'fa-bolt',
    'secondary' => 'fa-circle',
];
?>

<div class="slide-panel-content">

    <div class="slide-panel-header">
        <button type="button" class="btn-close-panel" onclick="cerrarPanel()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="slide-panel-body">

        <div class="profile-section">
            <div class="profile-avatar">
                <i class="fas fa-file-alt"></i>
            </div>

            <div class="profile-name">
                <?= Html::encode($model->report_name) ?>
            </div>

            <div class="profile-status">
                <span class="status-badge" style="background-color: <?= $statusColors[$badgeClass] ?? '#6c757d' ?>;">
                    <i class="fas <?= $statusIcons[$badgeClass] ?? 'fa-circle' ?>"></i>
                    <?= $statusName ?>
                </span>
            </div>
        </div>

        <hr class="section-divider">

        <div class="info-section">
            <div class="info-title">
                <i class="fas fa-info-circle"></i> Información del Reporte
            </div>

            <div class="info-row">
                <span class="info-label"><i class="fas fa-tag"></i> Tipo</span>
                <span class="info-value"><?= Html::encode($model->report_type ?? 'Sin especificar') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-calendar-alt"></i> Fecha</span>
                <span class="info-value"><?= $model->date_report ? date('d/m/Y', strtotime($model->date_report)) : 'Sin fecha' ?></span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-user"></i> Creado por</span>
                <span class="info-value"><?= $model->user ? Html::encode($model->user->name ?? $model->user->username ?? ('#' . $model->id_user)) : 'Sin usuario' ?></span>
            </div>
            <?php if ($isSuperAdmin): ?>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-building"></i> Empresa</span>
                <span class="info-value"><?= $model->company ? Html::encode($model->company->name) : 'Sin empresa' ?></span>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <div class="slide-panel-footer">
        <?php if ($isAdmin || $isSuperAdmin): ?>
            <a href="#" class="btn-footer btn-footer-success" onclick="event.preventDefault(); cerrarPanel(); setTimeout(function() { editarReporte(<?= $model->id_report ?>); }, 300);">
                <i class="fas fa-edit"></i> Editar
            </a>
        <?php endif; ?>
        <button class="btn-footer btn-footer-secondary" onclick="cerrarPanel()">
            <i class="fas fa-times"></i> Cerrar
        </button>
    </div>
</div>