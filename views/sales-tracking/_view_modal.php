<?php

use yii\helpers\Html;
use yii\helpers\Url;

$model = isset($model) ? $model : null;
$error = isset($error) ? $error : null;
$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isAgent = isset($isAgent) ? $isAgent : false;
$isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;

if ($error) {
    echo '<div class="slide-panel-content">
            <div class="slide-panel-header">
                <button type="button" class="btn-close-panel" onclick="closePanel()">
                    <i class="fas fa-times"></i>
                </button>
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
                <button type="button" class="btn-close-panel" onclick="closePanel()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="slide-panel-body">
                <div class="text-center text-muted py-5">
                    <i class="fas fa-inbox fa-3x d-block mb-3"></i>
                    <p>Seguimiento no encontrado</p>
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

$leadName = $model->lead ? $model->lead->name . ' ' . $model->lead->lastname : 'Lead no disponible';
$leadPhone = $model->lead ? $model->lead->phone : 'Sin teléfono';
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
?>

<div class="slide-panel-content">

    <div class="slide-panel-header">
        <button type="button" class="btn-close-panel" onclick="closePanel()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="slide-panel-body">

        <div class="profile-section">
            <div class="profile-avatar">
                <i class="fas fa-phone"></i>
            </div>
            
            <div class="profile-name">
                Seguimiento #<?= $model->id_sales_tracking ?>
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
                <i class="fas fa-info-circle"></i> Información del Seguimiento
            </div>
            
            <div class="info-row">
                <span class="info-label"><i class="fas fa-user"></i> Lead</span>
                <span class="info-value"><?= Html::encode($leadName) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-phone"></i> Teléfono</span>
                <span class="info-value"><?= Html::encode($leadPhone) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-calendar-alt"></i> Fecha</span>
                <span class="info-value"><?= date('d/m/Y', strtotime($model->date_s)) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-clock"></i> Hora</span>
                <span class="info-value"><?= date('H:i', strtotime($model->hour)) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-tag"></i> Estado</span>
                <span class="info-value">
                    <span class="badge bg-<?= $badgeClass ?>"><?= $statusName ?></span>
                </span>
            </div>
            <?php if (!empty($model->comments)): ?>
            <div class="info-row info-row-comments">
                <span class="info-label"><i class="fas fa-sticky-note"></i> Comentarios</span>
                <span class="info-value"><?= nl2br(Html::encode($model->comments)) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($model->date_f)): ?>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-calendar-check"></i> Próximo</span>
                <span class="info-value"><?= date('d/m/Y', strtotime($model->date_f)) ?></span>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <div class="slide-panel-footer">
        <a href="<?= Url::to(['sales-tracking/details', 'id' => $model->id_sales_tracking]) ?>" target="_blank" class="btn-footer btn-footer-primary">
            <i class="fas fa-external-link-alt"></i> Abrir
        </a>
        <?php if ($isAdmin || $isSuperAdmin): ?>
            <a href="#" class="btn-footer btn-footer-success" onclick="event.preventDefault(); closePanel(); setTimeout(function() { openEditPanel(<?= $model->id_sales_tracking ?>); }, 300);">
                <i class="fas fa-edit"></i> Editar
            </a>
        <?php endif; ?>
        <?php if ($model->lead): ?>
            <a href="<?= Url::to(['lead/details', 'id' => $model->id_lead]) ?>" target="_blank" class="btn-footer btn-footer-info">
                <i class="fas fa-user"></i> Ver Lead
            </a>
        <?php endif; ?>
        <button class="btn-footer btn-footer-secondary" onclick="closePanel()">
            <i class="fas fa-times"></i> Cerrar
        </button>
    </div>
</div>