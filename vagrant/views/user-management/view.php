<?php

use yii\helpers\Html;

$this->title = 'Detalle de Usuario: ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Gestión de Usuarios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Obtener el rol
$auth = $model->authentication;
$roleId = $auth ? $auth->id_role : null;
$roleName = 'Sin rol';
$badgeClass = 'secondary';

if ($roleId == 1) {
    $roleName = 'Super Administrador';
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

$company = $model->company;
$currentUser = Yii::$app->user->identity;
?>

<div class="user-management-view">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user"></i> 
                        Información del Usuario
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>ID:</strong> <?= $model->id_user ?></p>
                            <p><strong>Usuario:</strong> <?= Html::encode($model->username) ?></p>
                            <p><strong>Nombre:</strong> <?= Html::encode($model->name . ' ' . $model->lastname1 . ' ' . $model->lastname2) ?></p>
                            <p><strong>Email:</strong> <?= Html::encode($model->email) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Teléfono:</strong> <?= Html::encode($model->phone) ?></p>
                            <p><strong>Rol:</strong> 
                                <span class="badge bg-<?= $badgeClass ?>">
                                    <?= $roleName ?>
                                </span>
                            </p>
                            <p><strong>Estado:</strong> 
                                <?php if ($model->isActive()): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactivo</span>
                                <?php endif; ?>
                            </p>
                            <p><strong>Empresa:</strong> 
                                <?= $company ? Html::encode($company->name) : '<span class="text-muted">Sin empresa</span>' ?>
                            </p>
                        </div>
                    </div>

                    <?php if ($auth): ?>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>ID Autenticación:</strong> <?= $auth->id_auth ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Status Auth:</strong> 
                                    <?php if ($auth->status === 'active'): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactivo</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- ACCIONES                                    -->
        <!-- ============================================ -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs"></i> 
                        Acciones
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?= Html::a('<i class="fas fa-arrow-left"></i> Volver al listado', 
                            ['index'], 
                            ['class' => 'btn btn-secondary']
                        ) ?>
                        
                        <!-- Activar/Desactivar -->
                        <?php if ($model->id_user != $currentUser->id_user): ?>
                            <?php if (!$model->isSuperAdmin() || $isSuperAdmin): ?>
                                <?php if ($model->isActive()): ?>
                                    <?= Html::a('<i class="fas fa-ban"></i> Bloquear/Desactivar', ['toggle-status'], [
                                        'class' => 'btn btn-warning',
                                        'data' => [
                                            'method' => 'post',
                                            'confirm' => '¿Estás seguro de bloquear/desactivar este usuario? No podrá iniciar sesión.',
                                            'params' => ['user_id' => $model->id_user],
                                        ],
                                    ]) ?>
                                <?php else: ?>
                                    <?= Html::a('<i class="fas fa-check-circle"></i> Activar', ['toggle-status'], [
                                        'class' => 'btn btn-success',
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
                                'class' => 'btn btn-danger',
                                'data' => [
                                    'confirm' => '¿Estás seguro de eliminar este usuario? Esta acción no se puede deshacer.',
                                    'method' => 'post',
                                ],
                            ]) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Resumen -->
            <div class="card mt-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> 
                        Resumen
                    </h5>
                </div>
                <div class="card-body">
                    <p><strong>ID Usuario:</strong> <?= $model->id_user ?></p>
                    <p><strong>Rol:</strong> <?= $roleName ?></p>
                    <p><strong>Estado:</strong> <?= $model->isActive() ? 'Activo' : 'Inactivo' ?></p>
                    <p><strong>Empresa:</strong> <?= $company ? Html::encode($company->name) : 'N/A' ?></p>
                    <?php if ($auth): ?>
                        <hr>
                        <p><strong>Status Auth:</strong> <?= $auth->status === 'active' ? 'Activo' : 'Inactivo' ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>