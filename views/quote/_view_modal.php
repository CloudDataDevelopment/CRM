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
                <button type="button" class="btn-close-panel" data-panel-close>
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="slide-panel-body">
                <div class="text-center text-danger py-3">
                    <i class="fas fa-exclamation-triangle fa-2x d-block mb-2"></i>
                    <p class="mb-0">' . Html::encode($error) . '</p>
                </div>
            </div>
            <div class="slide-panel-footer">
                <button class="btn-footer btn-footer-secondary" data-panel-close>
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>';
    return;
}

if (!$model) {
    echo '<div class="slide-panel-content">
            <div class="slide-panel-header">
                <button type="button" class="btn-close-panel" data-panel-close>
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="slide-panel-body">
                <div class="text-center text-muted py-3">
                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                    <p class="mb-0">Cotización no encontrada</p>
                </div>
            </div>
            <div class="slide-panel-footer">
                <button class="btn-footer btn-footer-secondary" data-panel-close>
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>';
    return;
}

// ============================================
// DATOS DEL LEAD ASOCIADO
// ============================================
$lead = $model->lead;
$leadName = $lead ? $lead->name . ' ' . $lead->lastname : 'Lead no disponible';
$leadPhone = $lead ? $lead->phone : 'Sin teléfono';
$leadStatus = $lead ? $lead->getStatusName() : 'Sin estado';
$leadBadgeClass = $lead ? $lead->getStatusBadgeClass() : 'secondary';

$statusName = $model->getStatusName();
$badgeClass = $model->getStatusBadgeClass();
$total = (int) $model->total_amount;
$notas = $model->getNotes();

// 🔥 DETECTAR SI ESTÁ EN PAPELERA (para quote/trash)
$isTrash = (strtolower(trim($statusName)) === 'eliminado');

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

<div class="slide-panel-content">

    <!-- HEADER -->
    <div class="slide-panel-header">
        <button type="button" class="btn-close-panel" data-panel-close>
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- BODY -->
    <div class="slide-panel-body">

        <!-- 🔥 ALERTA DE PAPELERA (solo si está eliminada) -->
        <?php if ($isTrash): ?>
            <div class="alert alert-warning d-flex align-items-center mb-3" role="alert" style="border-radius: 8px; padding: 10px 12px; font-size: 0.8rem;">
                <i class="fas fa-trash me-2" style="font-size: 1rem;"></i>
                <div>
                    <strong>En papelera</strong>
                    <div style="font-size: 0.75rem;">Restaura desde la tabla para volver al listado activo.</div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ESTADO -->
        <div class="profile-status text-center mb-2">
            <span class="status-badge" style="background-color: <?= $statusColors[$badgeClass] ?? '#6c757d' ?>;">
                <i class="fas <?= $statusIcons[$badgeClass] ?? 'fa-circle' ?>"></i>
                <?= Html::encode($statusName) ?>
            </span>
        </div>

        <hr class="section-divider">

        <!-- INFORMACIÓN DEL LEAD -->
        <div class="info-section">
            <div class="info-title">
                <i class="fas fa-user"></i> Lead
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-user"></i> Nombre</span>
                <span class="info-value"><?= Html::encode($leadName) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-phone"></i> Teléfono</span>
                <span class="info-value"><?= Html::encode($leadPhone) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-tag"></i> Estado</span>
                <span class="info-value">
                    <span class="badge bg-<?= $leadBadgeClass ?>"><?= Html::encode($leadStatus) ?></span>
                </span>
            </div>
        </div>

        <hr class="section-divider">

        <!-- INFORMACIÓN DE LA COTIZACIÓN -->
        <div class="info-section">
            <div class="info-title">
                <i class="fas fa-file-invoice"></i> Cotización
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-hashtag"></i> ID</span>
                <span class="info-value">#<?= $model->id_quote ?></span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-calendar-alt"></i> Fecha</span>
                <span class="info-value"><?= date('d/m/Y', strtotime($model->date_quote)) ?></span>
            </div>
            <?php if (!empty($model->hour_quote)): ?>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-clock"></i> Hora</span>
                <span class="info-value"><?= date('H:i', strtotime($model->hour_quote)) ?></span>
            </div>
            <?php endif; ?>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-tag"></i> Estado</span>
                <span class="info-value">
                    <span class="badge bg-<?= $badgeClass ?>"><?= Html::encode($statusName) ?></span>
                </span>
            </div>
        </div>

        <hr class="section-divider">

        <!-- FINANCIERO -->
        <div class="info-section">
            <div class="info-title">
                <i class="fas fa-chart-bar"></i> Financiero
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-dollar-sign"></i> Total</span>
                <span class="info-value" style="font-size: 1.1rem; font-weight: 700; color: #1cc88a;">
                    $<?= number_format($total, 0, '.', ',') ?>
                </span>
            </div>
        </div>

        <?php if (!empty($notas)): ?>
        <hr class="section-divider">
        <div class="info-section">
            <div class="info-title">
                <i class="fas fa-sticky-note"></i> Observaciones
            </div>
            <div class="info-row info-row-comments">
                <span class="info-value"><?= nl2br(Html::encode($notas)) ?></span>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- FOOTER -->
    <div class="slide-panel-footer">
        <?php if (!$isTrash): ?>
            <!-- 🔥 EN INDEX: Ver más + Editar -->
            <a href="<?= Url::to(['quote/details', 'id' => $model->id_quote]) ?>"
               class="btn-footer btn-footer-primary"
               data-panel-close-go="<?= Url::to(['quote/details', 'id' => $model->id_quote]) ?>">
                <i class="fas fa-external-link-alt"></i> Ver más
            </a>
            <?php if ($isAdmin || $isSuperAdmin): ?>
                <button class="btn-footer btn-footer-warning edit-quote-btn"
                        data-id="<?= $model->id_quote ?>"
                        title="Editar">
                    <i class="fas fa-edit"></i> Editar
                </button>
            <?php endif; ?>
        <?php endif; ?>

        <button class="btn-footer btn-footer-secondary" data-panel-close>
            <i class="fas fa-times"></i> Cerrar
        </button>
    </div>
</div>