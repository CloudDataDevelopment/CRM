<?php

use yii\helpers\Html;
use yii\helpers\Url;

$model = isset($model) ? $model : null;
$error = isset($error) ? $error : null;

if ($error) {
    echo '<div class="slide-panel-content">
            <div class="slide-panel-header">
                <div class="d-flex justify-content-end align-items-center">
                    <button type="button" class="btn-close-panel" data-panel-close>
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="slide-panel-body">
                <div class="text-center text-danger py-5">
                    <i class="fas fa-exclamation-triangle fa-3x d-block mb-3"></i>
                    <p>' . Html::encode($error) . '</p>
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
                <div class="d-flex justify-content-end align-items-center">
                    <button type="button" class="btn-close-panel" data-panel-close>
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="slide-panel-body">
                <div class="text-center text-muted py-5">
                    <i class="fas fa-inbox fa-3x d-block mb-3"></i>
                    <p>Contacto no encontrado</p>
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
// DATOS DEL CONTACTO
// ============================================
$statusName = $model->getStatusName();
$statusIcon = $model->getStatusIcon();
$isEliminado = ($statusName === 'Eliminado');

$statusColor = '#6c757d';
if ($model->status) {
    $statusColors = [
        'Activo' => '#1cc88a',
        'Inactivo' => '#dc3545',
        'Pendiente' => '#f6c23e',
        'Suspendido' => '#6c757d',
        'Eliminado' => '#343a40',
    ];
    $statusColor = $statusColors[trim($model->status->status)] ?? '#6c757d';
}

$phone = $model->phone;
$whatsappLink = $phone ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $phone) : '#';

// Verificar permisos del usuario actual
$user = Yii::$app->user->identity;
$isAdmin = $user && ($user->isAdmin() || $user->isSuperAdmin());
?>

<div class="slide-panel-content">

    <!-- HEADER -->
    <div class="slide-panel-header">
        <div class="d-flex justify-content-end align-items-center">
            <button type="button" class="btn-close-panel" data-panel-close>
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- CUERPO -->
    <div class="slide-panel-body">

        <!-- PERFIL -->
        <div class="profile-section">
            <div class="profile-avatar">
                <i class="fas fa-user-circle"></i>
            </div>

            <div class="profile-name">
                <?= Html::encode($model->getFullName()) ?>
            </div>

            <div class="profile-status">
                <span class="status-badge" style="background-color: <?= $statusColor ?>;">
                    <i class="fas <?= $statusIcon ?>"></i> <?= Html::encode($statusName) ?>
                </span>
            </div>

            <?php if ($phone): ?>
                <div class="profile-phone">
                    <i class="fas fa-phone"></i> <?= Html::encode($model->getFormattedPhone()) ?>
                </div>
            <?php endif; ?>

            <div class="profile-actions">
                <?php if ($phone): ?>
                    <a href="<?= $whatsappLink ?>" target="_blank" class="action-btn action-btn-whatsapp" title="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                        <span>WhatsApp</span>
                    </a>
                <?php endif; ?>
                <?php if ($model->email): ?>
                    <a href="mailto:<?= Html::encode($model->email) ?>" class="action-btn action-btn-email" title="Email">
                        <i class="fas fa-envelope"></i>
                        <span>Email</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <hr class="section-divider">

        <!-- INFORMACIÓN -->
        <div class="info-section">
            <div class="info-title">
                <i class="fas fa-info-circle"></i> Información del Contacto
            </div>

            <div class="info-row">
                <span class="info-label"><i class="fas fa-building"></i> Empresa</span>
                <span class="info-value"><?= Html::encode($model->getCompanyName()) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-tag"></i> Tipo</span>
                <span class="info-value">
                    <span class="badge bg-info"><?= Html::encode($model->getTypeContactName()) ?></span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-phone"></i> Teléfono</span>
                <span class="info-value"><?= Html::encode($model->getFormattedPhone()) ?></span>
            </div>
            <?php if ($model->email): ?>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-envelope"></i> Email</span>
                <span class="info-value"><?= Html::encode($model->email) ?></span>
            </div>
            <?php endif; ?>
            <div class="info-row">
                <span class="info-label"><i class="fas fa-tag"></i> Estado</span>
                <span class="info-value">
                    <span class="status-badge-small" style="background-color: <?= $statusColor ?>;">
                        <i class="fas <?= $statusIcon ?>"></i> <?= Html::encode($statusName) ?>
                    </span>
                </span>
            </div>
        </div>

    </div>

    <!-- FOOTER -->
    <div class="slide-panel-footer">
        <?php if ($isEliminado): ?>
            <!-- 🔥 EN PAPELERA: Solo Restaurar -->
            <?php if ($isAdmin): ?>
                <?= Html::a(
                    '<i class="fas fa-undo"></i> Restaurar',
                    ['restore', 'id' => $model->id_contact],
                    [
                        'class' => 'btn-footer btn-footer-success',
                        'data' => [
                            'confirm' => '¿Restaurar este contacto? Volverá al listado principal.',
                            'method' => 'post',
                            'panel-close-go' => Url::to(['trash']),
                        ],
                    ]
                ) ?>
            <?php endif; ?>
        <?php else: ?>
            <!-- 🔥 EN INDEX: Editar -->
            <?php if ($isAdmin): ?>
                <a href="<?= Url::to(['update', 'id' => $model->id_contact]) ?>"
                   class="btn-footer btn-footer-primary"
                   data-panel-close-go="<?= Url::to(['update', 'id' => $model->id_contact]) ?>">
                    <i class="fas fa-edit"></i> Editar
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <button class="btn-footer btn-footer-secondary" data-panel-close>
            <i class="fas fa-times"></i> Cerrar
        </button>
    </div>
</div>