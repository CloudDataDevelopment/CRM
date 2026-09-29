<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = 'Gestión de Usuarios';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/user-management.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

$currentUser = Yii::$app->user->identity;

$dataProvider  = isset($dataProvider) ? $dataProvider : null;
$users         = isset($users) ? $users : [];
$totalUsers    = isset($totalUsers) ? $totalUsers : 0;
$totalAdmins   = isset($totalAdmins) ? $totalAdmins : 0;
$totalAgents   = isset($totalAgents) ? $totalAgents : 0;
$totalClients  = isset($totalClients) ? $totalClients : 0;
$totalActive   = isset($totalActive) ? $totalActive : 0;
$isSuperAdmin  = isset($isSuperAdmin) ? $isSuperAdmin : false;
$isAdmin       = isset($isAdmin) ? $isAdmin : false;
$rolesList     = isset($rolesList) ? $rolesList : [1 => 'Super Administrador', 2 => 'Administrador', 3 => 'Agente', 4 => 'Cliente'];
$statusList    = isset($statusList) ? $statusList : [];
$search        = isset($search) ? $search : '';
$role          = isset($role) ? $role : '';
$statusFilter  = isset($statusFilter) ? $statusFilter : '';

// 🔥 Permiso global para mostrar botones de cambio de contraseña
$canManagePasswords = $isAdmin || $isSuperAdmin;
?>

<div class="user-management-index">
    <div class="user-management-wrapper">

        <!-- HEADER -->
        <div class="user-management-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Administración</span><span class="separator">›</span>
                    <span class="current">Gestión de Usuarios</span>
                </div>
                <h1 class="page-title">
                    <i class="fas fa-users-cog text-primary me-2"></i>
                    <?= Html::encode($this->title) ?>
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= $this->render('/layouts/_report_button') ?>

                <?php if ($isSuperAdmin): ?>
                    <?= Html::a('<i class="fas fa-user-plus"></i> Registrar Usuario', ['/site/register'], [
                        'class' => 'btn btn-primary btn-sm btn-header-action'
                    ]) ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- MÉTRICAS -->
        <div class="row g-2 mb-3">
            <div class="col-xl-3 col-md-3 col-6">
                <div class="card stat-card stat-card-primary dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-users"></i></div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $totalUsers ?></div>
                            <div class="stat-label">Total Usuarios</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-3 col-6">
                <div class="card stat-card stat-card-success dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-user-check"></i></div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $totalActive ?></div>
                            <div class="stat-label">Activos</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-3 col-6">
                <div class="card stat-card stat-card-warning dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-user-shield"></i></div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $totalAdmins ?></div>
                            <div class="stat-label">Administradores</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-3 col-6">
                <div class="card stat-card stat-card-info dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-user-tie"></i></div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $totalAgents ?></div>
                            <div class="stat-label">Agentes</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="card filtros-card mb-3">
            <div class="card-body">
                <form method="get" action="<?= Url::to(['user-management/index']) ?>" id="form-filtros">
                    <div class="row align-items-end g-2">
                        <div class="col-md-1">
                            <label class="form-label fw-bold mb-0">
                                <i class="fas fa-filter text-primary"></i> Filtrar
                            </label>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control form-control-sm" name="search" 
                                   placeholder="Buscar por nombre, email, usuario..." 
                                   value="<?= Html::encode($search) ?>" id="search-input">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select form-select-sm" name="role" id="role-select">
                                <option value="">Todos los roles</option>
                                <?php foreach ($rolesList as $roleId => $roleName): ?>
                                    <option value="<?= $roleId ?>" <?= $role == $roleId ? 'selected' : '' ?>>
                                        <?= Html::encode($roleName) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select form-select-sm" name="status" id="status-select">
                                <option value="">Todos los estados</option>
                                <?php foreach ($statusList as $statusId => $statusName): ?>
                                    <option value="<?= $statusName ?>" <?= $statusFilter == $statusName ? 'selected' : '' ?>>
                                        <?= Html::encode($statusName) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100" id="btn-filtrar">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="<?= Url::to(['user-management/index']) ?>" 
                               class="btn btn-secondary btn-sm w-100" title="Limpiar">
                                <i class="fas fa-undo"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- TABLA -->
        <div class="card table-card">
            <div class="card-header">
                <div class="header-content">
                    <i class="fas fa-list"></i>
                    <span>Lista de Usuarios</span>
                    <span class="badge bg-primary ms-2"><?= $dataProvider ? $dataProvider->getTotalCount() : 0 ?></span>
                </div>
                <div>
                    <span class="badge bg-secondary">
                        Página <?= $dataProvider ? $dataProvider->getPagination()->getPage() + 1 : 1 ?> 
                        de <?= $dataProvider ? $dataProvider->getPagination()->getPageCount() : 1 ?>
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 user-table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <?php
                                    $auth = $user->authentication;
                                    $roleId = $auth ? $auth->id_role : null;
                                    $roleName = 'Sin rol';
                                    $badgeClass = 'secondary';
                                    $roleIcon = 'fa-question';
                                    
                                    if ($roleId == 1) { 
                                        $roleName = 'Super Admin'; 
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

                                    // 🔥 Determinar si puede cambiar la contraseña de este usuario
                                    $canChangeThisPassword = false;
                                    if ($canManagePasswords && $user->id_user != $currentUser->id_user) {
                                        if (!$user->isSuperAdmin() || $isSuperAdmin) {
                                            $canChangeThisPassword = true;
                                        }
                                    }
                                    ?>
                                    <tr class="user-row">
                                        <td>
                                            <div class="user-cell">
                                                <div class="user-avatar-small">
                                                    <?= strtoupper(substr($user->name ?? 'U', 0, 1)) ?>
                                                </div>
                                                <div class="user-cell-info">
                                                    <strong><?= Html::encode($user->username) ?></strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="user-full-name">
                                                <?= Html::encode($user->name . ' ' . $user->lastname1) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="mailto:<?= Html::encode($user->email) ?>" class="user-email">
                                                <i class="fas fa-envelope"></i> <?= Html::encode($user->email) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php if ($user->phone): ?>
                                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $user->phone) ?>" 
                                                   target="_blank" class="user-phone" title="WhatsApp">
                                                    <i class="fab fa-whatsapp text-success"></i>
                                                    <?= Html::encode($user->phone) ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge-role bg-<?= $badgeClass ?>">
                                                <i class="fas <?= $roleIcon ?>"></i> <?= $roleName ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($user->isActive()): ?>
                                                <span class="badge-status-active">
                                                    <i class="fas fa-circle"></i> Activo
                                                </span>
                                            <?php else: ?>
                                                <span class="badge-status-inactive">
                                                    <i class="fas fa-circle"></i> Inactivo
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $user->id_user], [
                                                    'class' => 'btn btn-info btn-sm btn-action',
                                                    'title' => 'Ver detalle'
                                                ]) ?>
                                                
                                                <?php if ($user->id_user != $currentUser->id_user): ?>
                                                    <?php if (!$user->isSuperAdmin() || $isSuperAdmin): ?>
                                                        <?php if ($user->isActive()): ?>
                                                            <?= Html::beginForm(['user-management/toggle-status'], 'post', ['class' => 'action-form']) ?>
                                                                <?= Html::hiddenInput('user_id', $user->id_user) ?>
                                                                <?= Html::submitButton('<i class="fas fa-lock"></i>', [
                                                                    'class' => 'btn btn-warning btn-sm btn-action',
                                                                    'title' => 'Bloquear usuario',
                                                                    'data' => ['confirm' => '¿Estás seguro de bloquear este usuario?'],
                                                                ]) ?>
                                                            <?= Html::endForm() ?>
                                                        <?php else: ?>
                                                            <?= Html::beginForm(['user-management/toggle-status'], 'post', ['class' => 'action-form']) ?>
                                                                <?= Html::hiddenInput('user_id', $user->id_user) ?>
                                                                <?= Html::submitButton('<i class="fas fa-unlock"></i>', [
                                                                    'class' => 'btn btn-success btn-sm btn-action',
                                                                    'title' => 'Activar usuario',
                                                                    'data' => ['confirm' => '¿Estás seguro de activar este usuario?'],
                                                                ]) ?>
                                                            <?= Html::endForm() ?>
                                                        <?php endif; ?>

                                                        <?php /* 🔥 BOTÓN CAMBIAR CONTRASEÑA */ ?>
                                                        <?php if ($canChangeThisPassword): ?>
                                                            <button type="button" 
                                                                    class="btn btn-dark btn-sm btn-action change-password-btn" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#changePasswordModal"
                                                                    data-user-id="<?= $user->id_user ?>"
                                                                    data-user-name="<?= Html::encode($user->username) ?>"
                                                                    data-user-fullname="<?= Html::encode($user->name . ' ' . $user->lastname1) ?>"
                                                                    title="Cambiar contraseña">
                                                                <i class="fas fa-key"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                
                                                <?php if ($isSuperAdmin && $user->id_user != $currentUser->id_user): ?>
                                                    <button type="button" 
                                                            class="btn btn-primary btn-sm btn-action change-role-btn" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#changeRoleModal"
                                                            data-user-id="<?= $user->id_user ?>"
                                                            data-user-name="<?= Html::encode($user->username) ?>"
                                                            data-current-role="<?= $roleId ?>"
                                                            title="Cambiar rol">
                                                        <i class="fas fa-user-tag"></i>
                                                    </button>
                                                    
                                                    <?= Html::beginForm(['user-management/delete', 'id' => $user->id_user], 'post', ['class' => 'action-form']) ?>
                                                        <?= Html::submitButton('<i class="fas fa-trash"></i>', [
                                                            'class' => 'btn btn-danger btn-sm btn-action',
                                                            'title' => 'Eliminar',
                                                            'data' => ['confirm' => '¿Estás seguro de eliminar este usuario?'],
                                                        ]) ?>
                                                    <?= Html::endForm() ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="fas fa-users fa-3x d-block mb-3 text-muted"></i>
                                        <p class="mb-2">No hay usuarios registrados</p>
                                        <small class="text-muted">Los usuarios aparecerán aquí cuando se registren</small>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PAGINACIÓN -->
            <?php if ($dataProvider && $dataProvider->pagination->pageCount > 1): ?>
                <div class="card-footer">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Mostrando <?= $dataProvider->getCount() ?> de <?= $dataProvider->getTotalCount() ?> usuarios
                                (página <?= $dataProvider->pagination->page + 1 ?> de <?= $dataProvider->pagination->pageCount ?>)
                            </small>
                        </div>
                        <div class="col-md-6">
                            <?= LinkPager::widget([
                                'pagination' => $dataProvider->pagination,
                                'options' => ['class' => 'pagination justify-content-end mb-0'],
                                'linkOptions' => ['class' => 'page-link'],
                                'prevPageLabel' => '<i class="fas fa-chevron-left"></i>',
                                'nextPageLabel' => '<i class="fas fa-chevron-right"></i>',
                                'firstPageLabel' => '<i class="fas fa-angle-double-left"></i>',
                                'lastPageLabel' => '<i class="fas fa-angle-double-right"></i>',
                                'maxButtonCount' => 5,
                                'activePageCssClass' => 'active',
                                'disabledPageCssClass' => 'disabled',
                            ]) ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- ============================================ -->
<!-- 🔥 MODAL CAMBIAR CONTRASEÑA                   -->
<!-- ============================================ -->
<?php if ($canManagePasswords): ?>
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
<?php endif; ?>

<!-- ============================================ -->
<!-- 🔥 MODAL CAMBIAR ROL (SOLO SUPERADMIN)       -->
<!-- ============================================ -->
<?php if ($isSuperAdmin): ?>
<div class="modal fade" id="changeRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user-tag"></i> Cambiar Rol</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <?= Html::beginForm(['user-management/change-role'], 'post', ['id' => 'change-role-form']) ?>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Cambiar el rol afecta los permisos del usuario.
                    </div>
                    
                    <?= Html::hiddenInput('user_id', '', ['id' => 'change-role-user-id']) ?>
                    
                    <div class="form-group mb-3">
                        <label><strong>Usuario:</strong></label>
                        <p id="change-role-user-name" class="text-primary mb-0"></p>
                    </div>
                    
                    <div class="form-group">
                        <label for="change-role-select">Nuevo Rol</label>
                        <?= Html::dropDownList('role_id', null, $rolesList, [
                            'class' => 'form-select',
                            'id' => 'change-role-select',
                            'required' => true
                        ]) ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <?= Html::submitButton('<i class="fas fa-save"></i> Actualizar Rol', ['class' => 'btn btn-primary']) ?>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // ============================================
    // 🔥 MODAL CAMBIAR CONTRASEÑA
    // ============================================
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

    // ============================================
    // MODAL CAMBIAR ROL
    // ============================================
    var changeRoleModal = document.getElementById('changeRoleModal');
    if (changeRoleModal) {
        changeRoleModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            if (!button) return;
            var userId = button.getAttribute('data-user-id');
            var userName = button.getAttribute('data-user-name');
            var currentRole = button.getAttribute('data-current-role');
            
            document.getElementById('change-role-user-id').value = userId;
            document.getElementById('change-role-user-name').textContent = userName;
            document.getElementById('change-role-select').value = currentRole;
        });
    }
});
</script>