<?php

use yii\helpers\Html;

$model = isset($model) ? $model : null;
$error = isset($error) ? $error : null;
$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;

if ($error) {
    echo '<div class="edit-panel-content">
            <div class="edit-panel-header">
                <div class="header-title"><i class="fas fa-exclamation-triangle text-danger"></i> Error</div>
                <button type="button" class="btn-close-panel" onclick="cerrarPanel()"><i class="fas fa-times"></i></button>
            </div>
            <div class="edit-panel-body">
                <div class="edit-panel-message">
                    <i class="fas fa-exclamation-triangle text-danger"></i>
                    <p>' . Html::encode($error) . '</p>
                    <button class="btn-message btn-message-secondary" onclick="cerrarPanel()"><i class="fas fa-times"></i> Cerrar</button>
                </div>
            </div>
        </div>';
    return;
}

if (!$model) {
    echo '<div class="edit-panel-content">
            <div class="edit-panel-header">
                <div class="header-title"><i class="fas fa-inbox text-muted"></i> Evaluación no encontrada</div>
                <button type="button" class="btn-close-panel" onclick="cerrarPanel()"><i class="fas fa-times"></i></button>
            </div>
            <div class="edit-panel-body">
                <div class="edit-panel-message">
                    <i class="fas fa-inbox"></i>
                    <p>Evaluación no encontrada</p>
                    <button class="btn-message btn-message-secondary" onclick="cerrarPanel()"><i class="fas fa-times"></i> Cerrar</button>
                </div>
            </div>
        </div>';
    return;
}

$statusName = $model->getStatusName();
$badgeClass = $model->getStatusBadgeClass();

$statusColors = [
    'success'   => '#1cc88a',
    'warning'   => '#f6c23e',
    'danger'    => '#e74a3b',
    'info'      => '#36b9cc',
    'secondary' => '#6c757d',
];

$statusIcons = [
    'success'   => 'fa-check-circle',
    'warning'   => 'fa-clock',
    'danger'    => 'fa-times-circle',
    'info'      => 'fa-bolt',
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
                <i class="fas fa-clipboard-check"></i>
            </div>

            <div class="profile-name">
                <?= Html::encode($model->report_name) ?>
            </div>

            <div class="profile-status">
                <span class="status-badge" style="background-color: <?= $statusColors[$badgeClass] ?? '#6c757d' ?>;">
                    <i class="fas <?= $statusIcons[$badgeClass] ?? 'fa-circle' ?>"></i>
                    <?= Html::encode($statusName) ?>
                </span>
            </div>
        </div>

        <hr class="section-divider">

        <div class="info-section">
            <div class="info-title"><i class="fas fa-info-circle"></i> Información</div>

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
                <span class="info-value"><?= $model->user ? Html::encode($model->user->name . ' ' . $model->user->lastname1) : 'Sin usuario' ?></span>
            </div>

            <?php if ($isSuperAdmin): ?>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-building"></i> Empresa</span>
                <span class="info-value"><?= $model->company ? Html::encode($model->company->name) : 'Sin empresa' ?></span>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($model->lead): ?>
        <hr class="section-divider">

        <div class="info-section">
            <div class="info-title"><i class="fas fa-user-tag"></i> Lead Asociado</div>

            <div class="info-row">
                <span class="info-label"><i class="fas fa-user"></i> Nombre</span>
                <span class="info-value">
                    <?= Html::a(
                        Html::encode($model->lead->name . ' ' . $model->lead->lastname),
                        ['lead/view', 'id' => $model->lead->id_lead],
                        ['class' => 'text-primary', 'target' => '_blank']
                    ) ?>
                </span>
            </div>

            <div class="info-row">
                <span class="info-label"><i class="fas fa-phone"></i> Teléfono</span>
                <span class="info-value">
                    <?= Html::encode($model->lead->phone) ?>
                    <?php if ($model->lead->phone): ?>
                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $model->lead->phone) ?>"
                           target="_blank" class="text-success ms-1" title="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    <?php endif; ?>
                </span>
            </div>

            <?php if ($model->lead->status): ?>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-tag"></i> Estado del Lead</span>
                <span class="info-value">
                    <span class="badge bg-<?= $model->lead->getStatusBadgeClass() ?>">
                        <?= Html::encode($model->lead->getStatusName()) ?>
                    </span>
                </span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>

    <div class="slide-panel-footer">
        <?php if ($isAdmin || $isSuperAdmin): ?>
            <button class="btn-footer btn-footer-primary"
                    onclick="event.preventDefault(); editarEvaluacion(<?= $model->id_report ?>);">
                <i class="fas fa-edit"></i> Editar
            </button>
        <?php endif; ?>
        <button class="btn-footer btn-footer-secondary" onclick="cerrarPanel()">
            <i class="fas fa-times"></i> Cerrar
        </button>
    </div>

</div>