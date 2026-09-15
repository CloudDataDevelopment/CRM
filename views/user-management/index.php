<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

$this->title = 'Gestión de Usuarios';
$this->params['breadcrumbs'][] = $this->title;

$rolesList = [
    1 => 'Super Administrador',
    2 => 'Administrador',
    3 => 'Agente',
    4 => 'Cliente',
];

$currentUser = Yii::$app->user->identity;
?>

<div class="user-management-index">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>
            <i class="fas fa-users-cog"></i> 
            <?= Html::encode($this->title) ?>
        </h1>
        <div>
            <?= Html::a('<i class="fas fa-user-plus"></i> Registrar Usuario', ['/site/register'], [
                'class' => 'btn btn-success'
            ]) ?>
        </div>
    </div>

    <!-- ESTADÍSTICAS -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h5 class="card-title">Total Usuarios</h5>
                    <h2 class="mb-0"><?= $totalUsers ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h5 class="card-title">Activos</h5>
                    <h2 class="mb-0"><?= $totalActive ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h5 class="card-title">Administradores</h5>
                    <h2 class="mb-0"><?= $totalAdmins ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h5 class="card-title">Agentes</h5>
                    <h2 class="mb-0"><?= $totalAgents ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA DE USUARIOS -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">
                <i class="fas fa-list"></i> 
                Lista de Usuarios
                <span class="badge bg-light text-dark float-end"><?= $totalUsers ?> registros</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Empresa</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user->id_user ?></td>
                                    <td>
                                        <strong><?= Html::encode($user->username) ?></strong>
                                    </td>
                                    <td><?= Html::encode($user->name . ' ' . $user->lastname1) ?></td>
                                    <td><?= Html::encode($user->email) ?></td>
                                    <td><?= Html::encode($user->phone) ?></td>
                                    <td>
                                        <?php
                                        $auth = $user->authentication;
                                        $roleId = $auth ? $auth->id_role : null;
                                        $roleName = 'Sin rol';
                                        $badgeClass = 'secondary';
                                        
                                        if ($roleId == 1) {
                                            $roleName = 'Super Admin';
                                            $badgeClass = 'danger';
                                        } elseif ($roleId == 2) {
                                            $roleName = 'Administrador';
                                            $badgeClass = 'warning';
                                        } elseif ($roleId == 3) {
                                            $roleName = 'Agente';
                                            $badgeClass = 'info';
                                        } elseif ($roleId == 4) {
                                            $roleName = 'Cliente';
                                            $badgeClass = 'success';
                                        }
                                        ?>
                                        <span class="badge bg-<?= $badgeClass ?>">
                                            <?= $roleName ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user->isActive()): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $company = $user->company;
                                        echo $company ? Html::encode($company->name) : '<span class="text-muted">Sin empresa</span>';
                                        ?>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <!-- Ver -->
                                        <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $user->id_user], [
                                            'class' => 'btn btn-info btn-sm',
                                            'title' => 'Ver detalle'
                                        ]) ?>
                                        
                                        <!-- Bloquear/Activar con FORMULARIO POST -->
                                        <?php if ($user->id_user != $currentUser->id_user): ?>
                                            <?php if (!$user->isSuperAdmin() || $isSuperAdmin): ?>
                                                <?php if ($user->isActive()): ?>
                                                    <?= Html::beginForm(['user-management/toggle-status'], 'post', ['style' => 'display:inline-block;']) ?>
                                                        <?= Html::hiddenInput('user_id', $user->id_user) ?>
                                                        <?= Html::submitButton('<i class="fas fa-lock"></i>', [
                                                            'class' => 'btn btn-warning btn-sm',
                                                            'title' => 'Bloquear usuario',
                                                            'data' => [
                                                                'confirm' => '¿Estás seguro de bloquear este usuario? No podrá iniciar sesión.',
                                                            ],
                                                        ]) ?>
                                                    <?= Html::endForm() ?>
                                                <?php else: ?>
                                                    <?= Html::beginForm(['user-management/toggle-status'], 'post', ['style' => 'display:inline-block;']) ?>
                                                        <?= Html::hiddenInput('user_id', $user->id_user) ?>
                                                        <?= Html::submitButton('<i class="fas fa-unlock"></i>', [
                                                            'class' => 'btn btn-success btn-sm',
                                                            'title' => 'Activar usuario',
                                                            'data' => [
                                                                'confirm' => '¿Estás seguro de activar este usuario? Podrá iniciar sesión.',
                                                            ],
                                                        ]) ?>
                                                    <?= Html::endForm() ?>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        
                                        <!-- Eliminar (solo Super Admin) -->
                                        <?php if ($isSuperAdmin && $user->id_user != $currentUser->id_user): ?>
                                            <?= Html::beginForm(['user-management/delete', 'id' => $user->id_user], 'post', ['style' => 'display:inline-block;']) ?>
                                                <?= Html::submitButton('<i class="fas fa-trash"></i>', [
                                                    'class' => 'btn btn-danger btn-sm',
                                                    'title' => 'Eliminar',
                                                    'data' => [
                                                        'confirm' => '¿Estás seguro de eliminar este usuario? Esta acción no se puede deshacer.',
                                                    ],
                                                ]) ?>
                                            <?= Html::endForm() ?>
                                        <?php endif; ?>
                                        
                                        <!-- Cambiar rol (solo Super Admin) -->
                                        <?php if ($isSuperAdmin && $user->id_user != $currentUser->id_user): ?>
                                            <button type="button" class="btn btn-primary btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#changeRoleModal"
                                                    data-user-id="<?= $user->id_user ?>"
                                                    data-user-name="<?= Html::encode($user->username) ?>"
                                                    data-current-role="<?= $roleId ?>">
                                                <i class="fas fa-user-tag"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-users fa-2x d-block mb-2"></i>
                                    No hay usuarios registrados en tu empresa
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PARA CAMBIAR ROL (SOLO SUPER ADMIN) -->
<?php if ($isSuperAdmin): ?>
<div class="modal fade" id="changeRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-tag"></i> Cambiar Rol
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <?= Html::beginForm(['user-management/change-role'], 'post', ['id' => 'change-role-form']) ?>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Cambiar el rol de un usuario afecta sus permisos en el sistema.
                    </div>
                    
                    <?= Html::hiddenInput('user_id', '', ['id' => 'change-role-user-id']) ?>
                    
                    <div class="form-group">
                        <label><strong>Usuario:</strong></label>
                        <p id="change-role-user-name" class="text-primary"></p>
                    </div>
                    
                    <div class="form-group">
                        <label for="change-role-select">Nuevo Rol</label>
                        <?= Html::dropDownList('role_id', null, $rolesList, [
                            'class' => 'form-control',
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    var changeRoleModal = document.getElementById('changeRoleModal');
    
    changeRoleModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var userId = button.getAttribute('data-user-id');
        var userName = button.getAttribute('data-user-name');
        var currentRole = button.getAttribute('data-current-role');
        
        document.getElementById('change-role-user-id').value = userId;
        document.getElementById('change-role-user-name').textContent = userName;
        document.getElementById('change-role-select').value = currentRole;
    });
});
</script>
<?php endif; ?>