<?php

use yii\helpers\Html;
use yii\helpers\Url;

$model = isset($model) ? $model : null;
$error = isset($error) ? $error : null;
$esPapelera = isset($esPapelera) ? $esPapelera : false;

if ($error) {
    echo '<div class="slide-panel-content">
            <div class="slide-panel-header">
                <div class="d-flex justify-content-end align-items-center">
                    <button type="button" class="btn-close-panel" data-panel-close><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="slide-panel-body">
                <div class="text-center text-danger py-5">
                    <i class="fas fa-exclamation-triangle fa-3x d-block mb-3"></i>
                    <p>' . Html::encode($error) . '</p>
                </div>
            </div>
            <div class="slide-panel-footer">
                <button class="btn-footer btn-footer-secondary" data-panel-close><i class="fas fa-times"></i> Cerrar</button>
            </div>
        </div>';
    return;
}

if (!$model) {
    echo '<div class="slide-panel-content">
            <div class="slide-panel-header">
                <div class="d-flex justify-content-end align-items-center">
                    <button type="button" class="btn-close-panel" data-panel-close><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="slide-panel-body">
                <div class="text-center text-muted py-5">
                    <i class="fas fa-inbox fa-3x d-block mb-3"></i>
                    <p>Lead no encontrado</p>
                </div>
            </div>
            <div class="slide-panel-footer">
                <button class="btn-footer btn-footer-secondary" data-panel-close><i class="fas fa-times"></i> Cerrar</button>
            </div>
        </div>';
    return;
}

// ============================================
// DATOS BÁSICOS
// ============================================
try { $agentName = $model->getAgentName(); } catch (\Exception $e) { $agentName = 'Sin asignar'; }

try {
    $createdAt = strtotime($model->created_at);
    $daysSince = floor((time() - $createdAt) / (60 * 60 * 24));
} catch (\Exception $e) {
    $daysSince = 0;
}

$statusName = $model->getStatusName();
$phone = $model->phone;
$whatsappLink = $phone ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $phone) : '#';
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

        <!-- 🔥 AVISO DE PAPELERA -->
        <?php if ($esPapelera): ?>
            <div class="alert alert-warning d-flex align-items-center mb-3" role="alert" style="border-radius: 8px;">
                <i class="fas fa-trash me-2" style="font-size: 1.1rem;"></i>
                <div>
                    <strong>Lead en papelera</strong><br>
                    <small>Este lead está eliminado. Solo se muestra información básica.</small>
                </div>
            </div>
        <?php endif; ?>

        <!-- PERFIL -->
        <div class="profile-section">
            <div class="profile-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="profile-name">
                <?= Html::encode($model->name . ' ' . $model->lastname) ?>
            </div>
            <div class="profile-status">
                <span class="badge bg-<?= $esPapelera ? 'dark' : $model->getStatusBadgeClass() ?>">
                    <i class="fas <?= $esPapelera ? 'fa-ban' : $model->getStatusIcon() ?>"></i>
                    <?= $esPapelera ? 'Eliminado' : Html::encode($statusName) ?>
                </span>
            </div>
            <div class="profile-phone">
                <i class="fas fa-phone"></i> <?= Html::encode($phone) ?>
            </div>
        </div>

        <hr class="section-divider">

        <!-- INFORMACIÓN BÁSICA -->
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
            <?php if (!empty($model->comments)): ?>
            <div class="info-row info-row-comments">
                <span class="info-label"><i class="fas fa-sticky-note"></i> Observaciones</span>
                <span class="info-value"><?= nl2br(Html::encode($model->comments)) ?></span>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- FOOTER -->
    <div class="slide-panel-footer">
        <?php if ($esPapelera): ?>
            <!-- 🔥 SOLO CERRAR CUANDO ESTÁ EN PAPELERA -->
            <button class="btn-footer btn-footer-secondary" data-panel-close>
                <i class="fas fa-times"></i> Cerrar
            </button>
        <?php else: ?>
            <!-- 🔥 ACCIONES NORMALES CUANDO NO ESTÁ EN PAPELERA -->
            <a href="<?= Url::to(['lead/details', 'id' => $model->id_lead]) ?>"
               class="btn-footer btn-footer-secondary"
               data-panel-close-go="<?= Url::to(['lead/details', 'id' => $model->id_lead]) ?>">
                <i class="fas fa-external-link-alt"></i> Abrir
            </a>
            <?php if ($phone): ?>
                <a href="<?= $whatsappLink ?>" target="_blank" class="btn-footer btn-footer-success">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>