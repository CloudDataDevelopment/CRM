<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Detalle de Usuario: ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Gestión de Usuarios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/user-management.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

// Obtener el rol
$auth = $model->authentication;
$roleId = $auth ? $auth->id_role : null;
$roleName = 'Sin rol';
$badgeClass = 'secondary';
$roleIcon = 'fa-question';

if ($roleId == 1) {
    $roleName = 'Super Administrador';
    $badgeClass = 'danger';
    $roleIcon = 'fa-crown';
} elseif ($roleId == 2) {
    $roleName = 'Administrador';
    $badgeClass = 'warning';
    $roleIcon = 'fa-user-shield';
} elseif ($roleId == 3) {
    $roleName = 'Agente';
    $badgeClass = 'info';
    $roleIcon = 'fa-user-tie';
} elseif ($roleId == 4) {
    $roleName = 'Cliente';
    $badgeClass = 'success';
    $roleIcon = 'fa-user';
}

$company = $model->company;
$currentUser = Yii::$app->user->identity;
$isSuperAdmin = $currentUser->isSuperAdmin();
$isAdmin = $currentUser->isAdmin();

// 🔥 ¿Puede cambiar la contraseña de este usuario?
$canChangeThisPassword = false;
if (($isAdmin || $isSuperAdmin) && $model->id_user != $currentUser->id_user) {
    if (!$model->isSuperAdmin() || $isSuperAdmin) {
        if ($isSuperAdmin || $model->id_company == $currentUser->id_company) {
            $canChangeThisPassword = true;
        }
    }
}
?>

<div class="user-management-view">
    <div class="user-view-wrapper">

        <!-- HEADER -->
        <div class="user-view-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Administración</span><span class="separator">›</span>
                    <a href="<?= Url::to(['index']) ?>" class="text-decoration-none">Usuarios</a>
                    <span class="separator">›</span>
                    <span class="current"><?= Html::encode($model->username) ?></span>
                </div>
                <h1 class="page-title">
                    <i class="fas fa-user-circle text-primary me-2"></i>
                    <?= Html::encode($model->name . ' ' . $model->lastname1) ?>
                    <small><?= Html::encode($model->username) ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['index'], [
                    'class' => 'btn btn-secondary btn-sm'
                ]) ?>
            </div>
        </div>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="row g-3">

            <!-- COLUMNA IZQUIERDA: INFO DEL USUARIO -->
            <div class="col-md-8">

                <!-- PERFIL -->
                <div class="card user-profile-card">
                    <div class="card-body">
                        <div class="user-profile-header">
                            <div class="user-avatar-large">
                                <?= strtoupper(substr($model->name ?? 'U', 0, 1)) ?>
                            </div>
                            <div class="user-profile-info">
                                <h3><?= Html::encode(trim($model->name . ' ' . $model->lastname1 . ' ' . $model->lastname2)) ?></h3>
                                <p class="user-profile-username">
                                    <i class="fas fa-at"></i> <?= Html::encode($model->username) ?>
                                </p>
                                <div class="user-profile-badges">
                                    <span class="badge-role bg-<?= $badgeClass ?>">
                                        <i class="fas <?= $roleIcon ?>"></i> <?= $roleName ?>
                                    </span>

                                    <?php if ($model->isActive()): ?>
                                        <span class="badge-status-active">
                                            <i class="fas fa-circle"></i> Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-status-inactive">
                                            <i class="fas fa-circle"></i> Inactivo
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- INFORMACIÓN PERSONAL -->
                <div class="card user-info-card mt-3">
                    <div class="card-header">
                        <i class="fas fa-info-circle text-primary"></i>
                        <span>Información Personal</span>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-hashtag"></i> ID Usuario</span>
                                <span class="info-value"><?= $model->id_user ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-user"></i> Usuario</span>
                                <span class="info-value"><?= Html::encode($model->username) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-envelope"></i> Email</span>
                                <span class="info-value">
                                    <a href="mailto:<?= Html::encode($model->email) ?>">
                                        <?= Html::encode($model->email) ?>
                                    </a>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-phone"></i> Teléfono</span>
                                <span class="info-value">
                                    <?php if ($model->phone): ?>
                                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $model->phone) ?>"
                                           target="_blank" class="text-success">
                                            <i class="fab fa-whatsapp"></i>
                                            <?= Html::encode($model->phone) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-building"></i> Empresa</span>
                                <span class="info-value">
                                    <?= $company ? Html::encode($company->name) : '<span class="text-muted">Sin empresa</span>' ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-user-tag"></i> Rol</span>
                                <span class="info-value">
                                    <span class="badge-role bg-<?= $badgeClass ?>">
                                        <i class="fas <?= $roleIcon ?>"></i> <?= $roleName ?>
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AUTENTICACIÓN -->
                <?php if ($auth): ?>
                <div class="card user-auth-card mt-3">
                    <div class="card-header">
                        <i class="fas fa-shield-alt text-success"></i>
                        <span>Información de Autenticación</span>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-key"></i> ID Autenticación</span>
                                <span class="info-value"><?= $auth->id_auth ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-toggle-on"></i> Estado Auth</span>
                                <span class="info-value">
                                    <?php if ($auth->isActive()): ?>
                                        <span class="badge-status-active">
                                            <i class="fas fa-circle"></i> Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-status-inactive">
                                            <i class="fas fa-circle"></i> Inactivo
                                        </span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- COLUMNA DERECHA: ACCIONES -->
            <div class="col-md-4">

                <!-- ACCIONES DISPONIBLES -->
                <div class="card user-actions-card">
                    <div class="card-header">
                        <i class="fas fa-cogs text-primary"></i>
                        <span>Acciones</span>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">

                            <?= Html::a('<i class="fas fa-arrow-left"></i> Volver al listado',
                                ['index'],
                                ['class' => 'btn btn-secondary btn-sm']
                            ) ?>

                            <!-- 🔥 CAMBIAR CONTRASEÑA (solo Admin/SuperAdmin) -->
                            <?php if ($canChangeThisPassword): ?>
                                <button type="button"
                                        class="btn btn-dark btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#changePasswordModal"
                                        data-user-id="<?= $model->id_user ?>"
                                        data-user-name="<?= Html::encode($model->username) ?>"
                                        data-user-fullname="<?= Html::encode($model->name . ' ' . $model->lastname1) ?>">
                                    <i class="fas fa-key"></i> Cambiar Contraseña
                                </button>
                            <?php endif; ?>

                            <!-- Activar/Desactivar -->
                            <?php if ($model->id_user != $currentUser->id_user): ?>
                                <?php if (!$model->isSuperAdmin() || $isSuperAdmin): ?>
                                    <?php if ($model->isActive()): ?>
                                        <?= Html::a('<i class="fas fa-ban"></i> Bloquear/Desactivar', ['toggle-status'], [
                                            'class' => 'btn btn-warning btn-sm',
                                            'data' => [
                                                'method' => 'post',
                                                'confirm' => '¿Estás seguro de bloquear/desactivar este usuario? No podrá iniciar sesión.',
                                                'params' => ['user_id' => $model->id_user],
                                            ],
                                        ]) ?>
                                    <?php else: ?>
                                        <?= Html::a('<i class="fas fa-check-circle"></i> Activar', ['toggle-status'], [
                                            'class' => 'btn btn-success btn-sm',
                                            'data' => [
                                                'method' => 'post',
                                                'confirm' => '¿Estás seguro de activar este usuario? Podrá iniciar sesión.',
                                                'params' => ['user_id' => $model->id_user],
                                            ],
                                        ]) ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>

                            <!-- Eliminar (solo Super Admin) -->
                            <?php if ($isSuperAdmin && $model->id_user != $currentUser->id_user): ?>
                                <?= Html::a('<i class="fas fa-trash"></i> Eliminar Usuario', ['delete', 'id' => $model->id_user], [
                                    'class' => 'btn btn-danger btn-sm',
                                    'data' => [
                                        'confirm' => '¿Estás seguro de eliminar este usuario? Esta acción no se puede deshacer.',
                                        'method' => 'post',
                                    ],
                                ]) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- RESUMEN RÁPIDO -->
                <div class="card user-summary-card mt-3">
                    <div class="card-header">
                        <i class="fas fa-chart-bar text-info"></i>
                        <span>Resumen</span>
                    </div>
                    <div class="card-body">
                        <div class="summary-item">
                            <span class="summary-label">ID Usuario</span>
                            <span class="summary-value"><?= $model->id_user ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Rol</span>
                            <span class="summary-value">
                                <span class="badge-role bg-<?= $badgeClass ?>">
                                    <?= $roleName ?>
                                </span>
                            </span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Estado</span>
                            <span class="summary-value">
                                <?= $model->isActive() ? 'Activo' : 'Inactivo' ?>
                            </span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Empresa</span>
                            <span class="summary-value">
                                <?= $company ? Html::encode($company->name) : 'N/A' ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ============================================ -->
<!-- 🔥 MODAL CAMBIAR CONTRASEÑA                  -->
<!-- ============================================ -->
<?php if ($canChangeThisPassword): ?>
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">
                    <i class="fas fa-key"></i> Cambiar Contraseña
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="change-password-form" method="post" action="<?= Url::to(['user-management/change-password']) ?>">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                <input type="hidden" name="user_id" id="change-password-user-id" value="">

                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Atención:</strong> La contraseña se cambiará inmediatamente. El usuario deberá usar la nueva contraseña en su próximo inicio de sesión.
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Usuario:</strong></label>
                        <p id="change-password-user-name" class="text-primary mb-0 fw-bold"></p>
                        <small id="change-password-user-fullname" class="text-muted"></small>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">
                            Nueva Contraseña <span class="text-danger">*</span>
                        </label>
                        <input type="password"
                               class="form-control"
                               id="new_password"
                               name="new_password"
                               minlength="4"
                               required
                               placeholder="Mínimo 4 caracteres">
                        <small class="text-muted">Mínimo 4 caracteres</small>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">
                            Confirmar Contraseña <span class="text-danger">*</span>
                        </label>
                        <input type="password"
                               class="form-control"
                               id="confirm_password"
                               name="confirm_password"
                               minlength="4"
                               required
                               placeholder="Repite la contraseña">
                    </div>

                    <div id="password-alert"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-dark" id="btn-submit-password">
                        <i class="fas fa-save"></i> Cambiar Contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var changePasswordModal = document.getElementById('changePasswordModal');
    if (changePasswordModal) {
        changePasswordModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            if (!button) return;
            var userId = button.getAttribute('data-user-id');
            var userName = button.getAttribute('data-user-name');
            var userFullname = button.getAttribute('data-user-fullname');

            document.getElementById('change-password-user-id').value = userId;
            document.getElementById('change-password-user-name').textContent = userName;
            document.getElementById('change-password-user-fullname').textContent = userFullname;

            // Limpiar campos
            document.getElementById('new_password').value = '';
            document.getElementById('confirm_password').value = '';
            document.getElementById('password-alert').innerHTML = '';
        });

        var passForm = document.getElementById('change-password-form');
        if (passForm) {
            passForm.addEventListener('submit', function(e) {
                e.preventDefault();

                var btn = document.getElementById('btn-submit-password');
                var alertBox = document.getElementById('password-alert');
                var originalText = btn.innerHTML;

                var newPass = document.getElementById('new_password').value;
                var confirmPass = document.getElementById('confirm_password').value;

                if (newPass.length < 4) {
                    alertBox.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> La contraseña debe tener al menos 4 caracteres.</div>';
                    return;
                }

                if (newPass !== confirmPass) {
                    alertBox.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Las contraseñas no coinciden.</div>';
                    return;
                }

                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cambiando...';
                alertBox.innerHTML = '';

                var formData = new FormData(passForm);

                fetch(passForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        alertBox.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' + data.message + '</div>';
                        setTimeout(function() {
                            var modal = bootstrap.Modal.getInstance(changePasswordModal);
                            if (modal) modal.hide();
                        }, 1500);
                    } else {
                        alertBox.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> ' + data.message + '</div>';
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                })
                .catch(function() {
                    alertBox.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Error de conexión.</div>';
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
            });
        }
    }
});
</script>
<?php endif; ?>