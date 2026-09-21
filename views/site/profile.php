<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Mi Perfil';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/profile.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

$initial = strtoupper(substr($user->name ?? 'U', 0, 1));
$fullName = trim(($user->name ?? '') . ' ' . ($user->lastname1 ?? '') . ' ' . ($user->lastname2 ?? ''));
$roleName = $auth && $auth->role ? $auth->role->role_type : 'Usuario';
$statusName = $auth && $auth->status ? $auth->status->status : 'Sin Estado';
$companyName = $auth && $auth->company ? $auth->company->name : 'Sin Empresa';
$createdAt = $user->created_at ?? null;
?>

<div class="profile-index">
    <div class="profile-wrapper">

        <!-- HEADER -->
        <div class="profile-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span>
                    <span class="separator">›</span>
                    <span class="current">Mi Perfil</span>
                </div>
                <h1 class="page-title">
                    <i class="fas fa-user-circle text-primary me-2"></i>
                    Mi Perfil
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['/dashboard/index'], ['class' => 'btn btn-secondary btn-sm']) ?>
            </div>
        </div>

        <div class="row g-3">
            <!-- COLUMNA IZQUIERDA -->
            <div class="col-md-4">
                <div class="card profile-card dashboard-card">
                    <div class="card-body text-center">
                        <div class="profile-avatar-large">
                            <?= $initial ?>
                        </div>

                        <h3 class="profile-name">
                            <?= Html::encode($fullName ?: $user->username) ?>
                        </h3>

                        <p class="profile-username">
                            <i class="fas fa-at"></i> <?= Html::encode($user->username) ?>
                        </p>

                        <div class="profile-role-badge">
                            <i class="fas fa-shield-alt"></i>
                            <?= Html::encode($roleName) ?>
                        </div>

                        <div class="profile-status-badge <?= strtolower($statusName) === 'activo' ? 'status-active' : 'status-inactive' ?>">
                            <i class="fas fa-circle" style="font-size: 6px;"></i>
                            <?= Html::encode($statusName) ?>
                        </div>
                    </div>

                    <div class="card-footer profile-card-footer">
                        <div class="profile-footer-item">
                            <i class="fas fa-building"></i>
                            <span><?= Html::encode($companyName) ?></span>
                        </div>
                        <?php if ($createdAt): ?>
                        <div class="profile-footer-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Miembro desde <?= date('d/m/Y', strtotime($createdAt)) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ESTADÍSTICAS -->
                <div class="card stats-card dashboard-card mt-3">
                    <div class="card-header">
                        <div class="header-left">
                            <i class="fas fa-chart-bar text-primary"></i>
                            <span>Mis Estadísticas</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="profile-stats-grid">
                            <div class="profile-stat-item">
                                <div class="profile-stat-icon profile-stat-primary">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="profile-stat-info">
                                    <div class="profile-stat-number"><?= $stats['leads_asignados'] ?></div>
                                    <div class="profile-stat-label">Leads Asignados</div>
                                </div>
                            </div>
                            <div class="profile-stat-item">
                                <div class="profile-stat-icon profile-stat-success">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="profile-stat-info">
                                    <div class="profile-stat-number"><?= $stats['seguimientos'] ?></div>
                                    <div class="profile-stat-label">Seguimientos</div>
                                </div>
                            </div>
                            <div class="profile-stat-item">
                                <div class="profile-stat-icon profile-stat-warning">
                                    <i class="fas fa-file-invoice"></i>
                                </div>
                                <div class="profile-stat-info">
                                    <div class="profile-stat-number"><?= $stats['cotizaciones'] ?></div>
                                    <div class="profile-stat-label">Cotizaciones</div>
                                </div>
                            </div>
                            <div class="profile-stat-item">
                                <div class="profile-stat-icon profile-stat-info">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <div class="profile-stat-info">
                                    <div class="profile-stat-number"><?= $stats['tareas_pendientes'] ?></div>
                                    <div class="profile-stat-label">Tareas Pendientes</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA -->
            <div class="col-md-8">
                <ul class="nav nav-tabs profile-tabs" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">
                            <i class="fas fa-user-circle"></i> Mi Información
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">
                            <i class="fas fa-key"></i> Cambiar Contraseña
                        </button>
                    </li>
                </ul>

                <div class="tab-content profile-tab-content">

                    <!-- TAB 1: MI INFORMACIÓN -->
                    <div class="tab-pane fade show active" id="info" role="tabpanel">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <div class="header-left">
                                    <i class="fas fa-user-circle text-primary"></i>
                                    <span>Mi Información</span>
                                </div>
                                <!-- 🔥 BOTÓN EDITAR (solo visible en modo lectura) -->
                                <button type="button" class="btn-edit-profile" id="btn-edit-profile">
                                    <i class="fas fa-pencil-alt"></i> Editar Información
                                </button>
                            </div>
                            <div class="card-body">

                                <!-- SECCIÓN: DATOS DE CUENTA (siempre readonly) -->
                                <h6 class="section-subtitle">
                                    <i class="fas fa-info-circle"></i> Datos de la Cuenta
                                </h6>

                                <div class="info-grid">
                                    <div class="info-grid-item">
                                        <span class="info-grid-label">Usuario</span>
                                        <span class="info-grid-value"><?= Html::encode($user->username) ?></span>
                                    </div>
                                    <div class="info-grid-item">
                                        <span class="info-grid-label">Rol</span>
                                        <span class="info-grid-value">
                                            <span class="badge bg-primary"><?= Html::encode($roleName) ?></span>
                                        </span>
                                    </div>
                                    <div class="info-grid-item">
                                        <span class="info-grid-label">Estado</span>
                                        <span class="info-grid-value">
                                            <span class="badge bg-<?= strtolower($statusName) === 'activo' ? 'success' : 'danger' ?>">
                                                <?= Html::encode($statusName) ?>
                                            </span>
                                        </span>
                                    </div>
                                    <div class="info-grid-item">
                                        <span class="info-grid-label">Empresa</span>
                                        <span class="info-grid-value"><?= Html::encode($companyName) ?></span>
                                    </div>
                                    <?php if ($createdAt): ?>
                                    <div class="info-grid-item">
                                        <span class="info-grid-label">Fecha de Registro</span>
                                        <span class="info-grid-value"><?= date('d/m/Y H:i', strtotime($createdAt)) ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <hr class="section-divider">

                                <!-- SECCIÓN: DATOS PERSONALES -->
                                <h6 class="section-subtitle">
                                    <i class="fas fa-user-edit"></i> Datos Personales
                                </h6>

                                <!-- MODO LECTURA -->
                                <div id="personal-read-mode">
                                    <div class="info-grid">
                                        <div class="info-grid-item">
                                            <span class="info-grid-label">Nombre de Usuario</span>
                                            <span class="info-grid-value">
                                                <i class="fas fa-at text-primary"></i>
                                                <?= Html::encode($user->username ?: '—') ?>
                                            </span>
                                        </div>
                                        <div class="info-grid-item">
                                            <span class="info-grid-label">Nombre</span>
                                            <span class="info-grid-value"><?= Html::encode($user->name ?: '—') ?></span>
                                        </div>
                                        <div class="info-grid-item">
                                            <span class="info-grid-label">Apellido Paterno</span>
                                            <span class="info-grid-value"><?= Html::encode($user->lastname1 ?: '—') ?></span>
                                        </div>
                                        <div class="info-grid-item">
                                            <span class="info-grid-label">Apellido Materno</span>
                                            <span class="info-grid-value"><?= Html::encode($user->lastname2 ?: '—') ?></span>
                                        </div>
                                        <div class="info-grid-item">
                                            <span class="info-grid-label">Correo Electrónico</span>
                                            <span class="info-grid-value"><?= Html::encode($user->email ?: '—') ?></span>
                                        </div>
                                        <div class="info-grid-item">
                                            <span class="info-grid-label">Teléfono</span>
                                            <span class="info-grid-value"><?= Html::encode($user->phone ?: '—') ?></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- MODO EDICIÓN (oculto por defecto) -->
                                <div id="personal-edit-mode" style="display: none;">
                                    <form id="profile-form" method="post" action="<?= Url::to(['site/update-profile']) ?>">
                                        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

                                        <div class="row g-3">
                                            <!-- 🔥 USERNAME EDITABLE -->
                                            <div class="col-md-6">
                                                <label class="form-label">
                                                    <i class="fas fa-at text-primary me-1"></i>
                                                    Nombre de Usuario <span class="text-danger">*</span>
                                                </label>
                                                <input type="text"
                                                       class="form-control"
                                                       id="profile-username"
                                                       name="username"
                                                       value="<?= Html::encode($user->username) ?>"
                                                       placeholder="ej: juan.perez"
                                                       pattern="[a-zA-Z0-9._-]{3,50}"
                                                       maxlength="50"
                                                       required>
                                                <small class="text-muted">
                                                    Solo letras, números, puntos, guiones y guiones bajos (mínimo 3 caracteres)
                                                </small>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">
                                                    Nombre <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control" name="name" value="<?= Html::encode($user->name) ?>" required>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Apellido Paterno</label>
                                                <input type="text" class="form-control" name="lastname1" value="<?= Html::encode($user->lastname1) ?>">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Apellido Materno</label>
                                                <input type="text" class="form-control" name="lastname2" value="<?= Html::encode($user->lastname2) ?>">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control" name="email" value="<?= Html::encode($user->email) ?>" required>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Teléfono</label>
                                                <input type="tel" class="form-control" name="phone" value="<?= Html::encode($user->phone) ?>" maxlength="15">
                                            </div>
                                        </div>

                                        <div class="alert-message" id="profile-alert" style="display:none;"></div>

                                        <div class="form-actions mt-3">
                                            <button type="submit" class="btn btn-primary" id="save-profile-btn">
                                                <i class="fas fa-save"></i> Guardar Cambios
                                            </button>
                                            <button type="button" class="btn btn-secondary" id="btn-cancel-edit">
                                                <i class="fas fa-times"></i> Cancelar
                                            </button>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: CAMBIAR CONTRASEÑA -->
                    <div class="tab-pane fade" id="password" role="tabpanel">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <div class="header-left">
                                    <i class="fas fa-key text-warning"></i>
                                    <span>Cambiar Contraseña</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <form id="password-form" method="post" action="<?= Url::to(['site/change-password']) ?>">
                                    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">Contraseña Actual <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" name="current_password" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Nueva Contraseña <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" name="new_password" required minlength="4">
                                            <small class="text-muted">Mínimo 4 caracteres</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" name="confirm_password" required minlength="4">
                                        </div>
                                    </div>

                                    <div class="alert-message" id="password-alert" style="display:none;"></div>

                                    <div class="form-actions mt-3">
                                        <button type="submit" class="btn btn-warning" id="save-password-btn">
                                            <i class="fas fa-key"></i> Cambiar Contraseña
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ============================================
    // BOTÓN EDITAR — Cambiar a modo edición
    // ============================================
    var btnEdit = document.getElementById('btn-edit-profile');
    var readMode = document.getElementById('personal-read-mode');
    var editMode = document.getElementById('personal-edit-mode');

    if (btnEdit) {
        btnEdit.addEventListener('click', function () {
            readMode.style.display = 'none';
            editMode.style.display = 'block';
            btnEdit.style.display = 'none';

            // Enfocar el primer input
            var firstInput = editMode.querySelector('input[name="name"]');
            if (firstInput) firstInput.focus();
        });
    }

    // ============================================
    // BOTÓN CANCELAR — Volver a modo lectura
    // ============================================
    var btnCancel = document.getElementById('btn-cancel-edit');
    if (btnCancel) {
        btnCancel.addEventListener('click', function () {
            readMode.style.display = 'block';
            editMode.style.display = 'none';
            btnEdit.style.display = 'inline-flex';

            // Ocultar alerta si existe
            var alert = document.getElementById('profile-alert');
            if (alert) alert.style.display = 'none';

            // Resetear el formulario
            var form = document.getElementById('profile-form');
            if (form) form.reset();
        });
    }

    // ============================================
    // GUARDAR PERFIL
    // ============================================
    var profileForm = document.getElementById('profile-form');
    if (profileForm) {
        profileForm.addEventListener('submit', function (e) {
            e.preventDefault();

            var btn = document.getElementById('save-profile-btn');
            var originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            btn.disabled = true;

            var formData = new FormData(profileForm);

            fetch(profileForm.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                btn.innerHTML = originalText;
                btn.disabled = false;

                var alert = document.getElementById('profile-alert');
                alert.style.display = 'block';
                alert.className = 'alert-message ' + (data.success ? 'alert-success' : 'alert-danger');
                alert.innerHTML = '<i class="fas ' + (data.success ? 'fa-check-circle' : 'fa-exclamation-triangle') + '"></i> ' + data.message;

                if (data.success) {
                    setTimeout(function () {
                        location.reload();
                    }, 1500);
                }
            })
            .catch(function (error) {
                btn.innerHTML = originalText;
                btn.disabled = false;
                var alert = document.getElementById('profile-alert');
                alert.style.display = 'block';
                alert.className = 'alert-message alert-danger';
                alert.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error: ' + error.message;
            });
        });
    }

    // ============================================
    // CAMBIAR CONTRASEÑA
    // ============================================
    var passwordForm = document.getElementById('password-form');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function (e) {
            e.preventDefault();

            var btn = document.getElementById('save-password-btn');
            var originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cambiando...';
            btn.disabled = true;

            var formData = new FormData(passwordForm);

            fetch(passwordForm.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                btn.innerHTML = originalText;
                btn.disabled = false;

                var alert = document.getElementById('password-alert');
                alert.style.display = 'block';
                alert.className = 'alert-message ' + (data.success ? 'alert-success' : 'alert-danger');
                alert.innerHTML = '<i class="fas ' + (data.success ? 'fa-check-circle' : 'fa-exclamation-triangle') + '"></i> ' + data.message;

                if (data.success) {
                    passwordForm.reset();
                    setTimeout(function () {
                        alert.style.display = 'none';
                    }, 3000);
                }
            })
            .catch(function (error) {
                btn.innerHTML = originalText;
                btn.disabled = false;
                var alert = document.getElementById('password-alert');
                alert.style.display = 'block';
                alert.className = 'alert-message alert-danger';
                alert.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error: ' + error.message;
            });
        });
    }
});
</script>