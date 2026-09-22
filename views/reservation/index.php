<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\StringHelper;

$this->title = 'Reservaciones';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/reservation.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isAgent = isset($isAgent) ? $isAgent : false;
$reservations = isset($reservations) ? $reservations : [];
$totalReservations = isset($totalReservations) ? $totalReservations : 0;
$todayCount = isset($todayCount) ? $todayCount : 0;
$upcomingCount = isset($upcomingCount) ? $upcomingCount : 0;
$pastCount = isset($pastCount) ? $pastCount : 0;
$search = isset($search) ? $search : '';
$fecha_inicio = isset($fecha_inicio) ? $fecha_inicio : '';
$fecha_fin = isset($fecha_fin) ? $fecha_fin : '';
?>

<div class="reservation-index">
    <div class="reservation-wrapper">
        
        <!-- HEADER -->
        <div class="reservation-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Reservaciones</span><span class="separator">›</span>
                    <span class="current"><?= Html::encode($this->title) ?></span>
                </div>
                <h1 class="page-title">
                    <?= Html::encode($this->title) ?>
                    <?php if ($isAgent): ?>
                        <span class="badge-role"><i class="fas fa-user"></i> Mis Reservaciones</span>
                    <?php endif; ?>
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= $this->render('/layouts/_report_button') ?>
                <?= Html::a('<i class="fas fa-plus"></i> Nueva Reservación', ['create'], [
                    'class' => 'btn btn-primary btn-sm btn-header-action'
                ]) ?>
            </div>
        </div>

        <!-- MÉTRICAS -->
        <div class="row g-2 mb-2">
            <div class="col-xl-3 col-md-4 col-6">
                <div class="card stat-card stat-card-primary dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-calendar-alt"></i></div>
                        <div>
                            <div class="stat-number"><?= $totalReservations ?></div>
                            <div class="stat-label">Total Reservaciones</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-4 col-6">
                <div class="card stat-card stat-card-warning dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-calendar-day"></i></div>
                        <div>
                            <div class="stat-number"><?= $todayCount ?></div>
                            <div class="stat-label">Hoy</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-4 col-6">
                <div class="card stat-card stat-card-success dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-calendar-week"></i></div>
                        <div>
                            <div class="stat-number"><?= $upcomingCount ?></div>
                            <div class="stat-label">Próximas</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-4 col-6">
                <div class="card stat-card stat-card-secondary dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-calendar-minus"></i></div>
                        <div>
                            <div class="stat-number"><?= $pastCount ?></div>
                            <div class="stat-label">Pasadas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="card filtros-card mb-3">
            <div class="card-body">
                <form method="get" action="<?= Url::to(['reservation/index']) ?>" id="form-filtros">
                    <div class="row align-items-end">
                        <div class="col-md-1">
                            <label class="form-label fw-bold mb-0" style="color: #4a5568; font-size: 0.75rem;">
                                <i class="fas fa-filter"></i> Filtrar
                            </label>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Buscar lead o reservación..." 
                                   value="<?= Html::encode($search) ?>" id="search-input">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="fecha_inicio" 
                                   value="<?= Html::encode($fecha_inicio) ?>" 
                                   placeholder="Fecha inicio" id="fecha-inicio">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="fecha_fin" 
                                   value="<?= Html::encode($fecha_fin) ?>" 
                                   placeholder="Fecha fin" id="fecha-fin">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100" id="btn-filtrar" style="border-radius: 6px;">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div class="col-md-1">
                            <a href="<?= Url::to(['reservation/index']) ?>" class="btn btn-secondary w-100" 
                               style="border-radius: 6px;" title="Limpiar">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- TABLA -->
        <div class="card table-card">
            <div class="card-header">
                <div class="header-left">
                    <i class="fas fa-list"></i>
                    <span>Listado de Reservaciones</span>
                    <span class="badge bg-primary"><?= $totalReservations ?></span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Lead</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($reservations)): ?>
                                <?php foreach ($reservations as $index => $reservation): ?>
                                    <tr>
                                        <td><span class="badge bg-light text-dark"><?= $index + 1 ?></span></td>
                                        <td>
                                            <strong><?= Html::encode($reservation->name_reservation) ?></strong>
                                        </td>
                                        <td>
                                            <?php if ($reservation->lead): ?>
                                                <strong><?= Html::encode($reservation->getLeadName()) ?></strong>
                                                <br><small class="text-muted"><i class="fas fa-phone"></i> <?= Html::encode($reservation->getLeadPhone()) ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">Lead no disponible</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $reservation->getFormattedDate() ?></td>
                                        <td><?= $reservation->getFormattedHourS() ?> - <?= $reservation->getFormattedHourF() ?></td>
                                        <td>
                                            <span class="badge-status bg-<?= $reservation->getStatusClass() ?>">
                                                <i class="fas fa-circle"></i>
                                                <?= $reservation->getStatusLabel() ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $reservation->id_reservation], [
                                                    'class' => 'btn-action btn-view',
                                                    'title' => 'Ver'
                                                ]) ?>
                                                <?php if ($isAdmin || ($isAgent && $reservation->id_user == Yii::$app->user->id)): ?>
                                                    <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $reservation->id_reservation], [
                                                        'class' => 'btn-action btn-edit',
                                                        'title' => 'Editar'
                                                    ]) ?>
                                                <?php endif; ?>
                                                <?php if ($isAdmin): ?>
                                                    <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $reservation->id_reservation], [
                                                        'class' => 'btn-action btn-delete',
                                                        'title' => 'Eliminar',
                                                        'data' => [
                                                            'confirm' => '¿Eliminar esta reservación?',
                                                            'method' => 'post',
                                                        ],
                                                    ]) ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <i class="fas fa-calendar-times"></i>
                                            </div>
                                            <h4>No hay reservaciones registradas</h4>
                                            <p>
                                                <?php if ($isAgent): ?>
                                                    No tienes reservaciones asignadas actualmente.
                                                <?php else: ?>
                                                    Comienza creando tu primera reservación.
                                                <?php endif; ?>
                                            </p>
                                            <?= Html::a('<i class="fas fa-plus"></i> Crear Reservación', ['create'], [
                                                'class' => 'btn btn-primary btn-sm',
                                                'style' => 'border-radius: 6px;'
                                            ]) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.getElementById('search-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') document.getElementById('form-filtros').submit();
});
document.getElementById('fecha-inicio').addEventListener('change', function() {
    document.getElementById('form-filtros').submit();
});
document.getElementById('fecha-fin').addEventListener('change', function() {
    document.getElementById('form-filtros').submit();
});
document.getElementById('btn-filtrar').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('form-filtros').submit();
});
</script>