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
                <?= Html::a('<i class="fas fa-user-plus"></i> Registrar Usuario', ['/site/register'], [
                    'class' => 'btn btn-primary btn-sm'
                ]) ?>
            </div>
        </div>

        <!-- MÉTRICAS -->
        <div class="row g-2 mb-2">
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
                    <div class="row align-items-end">
                        <div class="col-md-1"><label class="form-label fw-bold mb-0">Filtrar</label></div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search" placeholder="Buscar..." value="<?= Html::encode($search) ?>" id="search-input">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="role" id="role-select">
                                <option value="">Todos los roles</option>
                                <?php foreach ($rolesList as $roleId => $roleName): ?>
                                    <option value="<?= $roleId ?>" <?= $role == $roleId ? 'selected' : '' ?>><?= Html::encode($roleName) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="status" id="status-select">
                                <option value="">Todos los estados</option>
                                <?php foreach ($statusList as $statusId => $statusName): ?>
                                    <option value="<?= $statusName ?>" <?= $statusFilter == $statusName ? 'selected' : '' ?>><?= Html::encode($statusName) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100" id="btn-filtrar"><i class="fas fa-search"></i></button>
                        </div>
                        <div class="col-md-1">
                            <a href="<?= Url::to(['user-management/index']) ?>" class="btn btn-secondary w-100" title="Limpiar"><i class="fas fa-undo"></i></a>
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
                        Página <?= $dataProvider ? $dataProvider->getPagination()->getPage() + 1 : 1 ?> de <?= $dataProvider ? $dataProvider->getPagination()->getPageCount() : 1 ?>
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Usuario</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Empresa</th>
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
                                    
                                    if ($roleId == 1) { $roleName = 'Super Admin'; $badgeClass = 'danger'; }
                                    elseif ($roleId == 2) { $roleName = 'Administrador'; $badgeClass = 'warning'; }
                                    elseif ($roleId == 3) { $roleName = 'Agente'; $badgeClass = 'info'; }
                                    elseif ($roleId == 4) { $roleName = 'Cliente'; $badgeClass = 'success'; }
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="user-cell">
                                                <div class="user-avatar-small">
                                                    <?= strtoupper(substr($user->name ?? 'U', 0, 1)) ?>
                                                </div>
                                                <strong><?= Html::encode($user->username) ?></strong>
                                            </div>
                                        </td>
                                        <td><?= Html::encode($user->name . ' ' . $user->lastname1) ?></td>
                                        <td>
                                            <a href="mailto:<?= Html::encode($user->email) ?>" class="text-primary">
                                                <i class="fas fa-envelope"></i> <?= Html::encode($user->email) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php if ($user->phone): ?>
                                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $user->phone) ?>" 
                                                   target="_blank" class="text-success" title="WhatsApp">
                                                    <i class="fab fa-whatsapp"></i>
                                                </a>
                                                <?= Html::encode($user->phone) ?>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $badgeClass ?>"><?= $roleName ?></span>
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
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                
                                                <?php if ($isSuperAdmin && $user->id_user != $currentUser->id_user): ?>
                                                    <button type="button" class="btn btn-primary btn-sm btn-action change-role-btn" 
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
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-users fa-2x d-block mb-2"></i>
                                        No hay usuarios registrados
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($dataProvider && $dataProvider->pagination->pageCount > 1): ?>
                <div class="card-footer">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <small class="text-muted">
                                Mostrando <?= count($users) ?> de <?= $totalUsers ?> usuarios
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

<!-- MODAL CAMBIAR ROL -->
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