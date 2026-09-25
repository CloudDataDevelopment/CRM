<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js');

$this->registerCssFile('@web/css/dashboard.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
]);

// Variables con valores por defecto
$actividadesRecientes = isset($actividadesRecientes) ? $actividadesRecientes : [];
$proximasActividades = isset($proximasActividades) ? $proximasActividades : [];

// 🔥 Totales
$totalMes = isset($totalMes) ? $totalMes : ['venta' => 0, 'utilidad' => 0, 'cotizaciones' => 0];
$totalAnio = isset($totalAnio) ? $totalAnio : ['venta' => 0, 'utilidad' => 0, 'cotizaciones' => 0];
$totalGlobal = isset($totalGlobal) ? $totalGlobal : ['venta' => 0, 'utilidad' => 0, 'cotizaciones' => 0];
$porcentajeUtilidad = isset($porcentajeUtilidad) ? $porcentajeUtilidad : 25;

// 🔥 METAS
$metaMensual = isset($metaMensual) ? $metaMensual : 500000;
$ventasMesActual = isset($ventasMesActual) ? $ventasMesActual : 0;
$porcentajeAlcanzado = isset($porcentajeAlcanzado) ? $porcentajeAlcanzado : 0;
$montoRestante = isset($montoRestante) ? $montoRestante : 0;
$metaAlcanzada = isset($metaAlcanzada) ? $metaAlcanzada : false;

// 🔥 METAS UTILIDAD
$metaUtilidadMensual = isset($metaUtilidadMensual) ? $metaUtilidadMensual : 200000;
$utilidadMesActual = isset($utilidadMesActual) ? $utilidadMesActual : 0;
$porcentajeUtilidadAlcanzado = isset($porcentajeUtilidadAlcanzado) ? $porcentajeUtilidadAlcanzado : 0;
$utilidadRestante = isset($utilidadRestante) ? $utilidadRestante : 0;
$metaUtilidadAlcanzada = isset($metaUtilidadAlcanzada) ? $metaUtilidadAlcanzada : false;

// 🔥 Variables de rol
$esAdminOSuperAdmin = isset($esAdminOSuperAdmin) ? $esAdminOSuperAdmin : false;
$mostrarUtilidad = isset($mostrarUtilidad) ? $mostrarUtilidad : true;
$cantidadAgentes = isset($cantidadAgentes) ? $cantidadAgentes : 1;
$metaVentasBase = isset($metaVentasBase) ? $metaVentasBase : 500000;
$metaUtilidadBase = isset($metaUtilidadBase) ? $metaUtilidadBase : 200000;

// 🔥 Datos para gráfica de comparación
$perdidoData = isset($perdidoData) ? $perdidoData : [];
?>

<div class="dashboard-index">
    <div class="dashboard-wrapper">
        
        <!-- HEADER -->
        <div class="dashboard-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span>
                    <span class="separator">›</span>
                    <span class="current">Dashboard</span>
                </div>
                <h1 class="page-title">
                    <?= Html::encode($this->title) ?>
                </h1>
            </div>
            <div class="header-actions">
                <span class="badge bg-success bg-opacity-10 text-success badge-status-active">
                    <i class="fas fa-circle text-success me-1"></i> Activo
                </span>
            </div>
        </div>

        <!-- TARJETAS DE MÉTRICAS -->
        <div class="row g-2 mb-2">
            <div class="col-xl-3 col-md-6 col-6">
                <div class="card stat-card stat-card-primary dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $totalLeads ?></div>
                            <div class="stat-label">Total Leads</div>
                            <div class="stat-sub text-success">
                                <i class="fas fa-arrow-up"></i> +<?= $nuevosLeads ?> sem
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 col-6">
                <div class="card stat-card stat-card-success dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $totalCitas ?></div>
                            <div class="stat-label">Total Seguimientos</div>
                            <div class="stat-sub text-primary">
                                <i class="fas fa-clock"></i> <?= $citasHoy ?> hoy
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 col-6">
                <div class="card stat-card stat-card-warning dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $tareasPendientes ?></div>
                            <div class="stat-label">Tareas Pendientes</div>
                            <div class="stat-sub text-danger">
                                <i class="fas fa-exclamation-circle"></i> <?= 100 - $porcentajeTareas ?>% restante
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 col-6">
                <div class="card stat-card stat-card-info dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $ventasCerradas ?></div>
                            <div class="stat-label">Ventas Cerradas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILA 1: EMBUDO + VENTAS POR MES + ACTIVIDADES RECIENTES -->
        <div class="row g-2 mb-2">
            <!-- EMBUDO DE VENTAS -->
            <div class="col-md-4">
                <div class="card funnel-card dashboard-card h-100">
                    <div class="card-header">
                        <div class="header-left">
                            <i class="fas fa-filter text-primary"></i>
                            <span>Embudo de Ventas</span>
                        </div>
                        <span class="badge bg-info text-white">
                            <?= isset($embudo['total']) ? $embudo['total'] : 0 ?>
                        </span>
                    </div>
                    <div class="card-body d-flex align-items-center">
                        <div class="funnel-container-trapezoid w-100">
                            <?php
                            $etapas = [
                                [
                                    'label' => 'Nuevo',  
                                    'count' => isset($embudo['nuevo']) ? $embudo['nuevo'] : 0, 
                                    'color' => '#007BFF', 
                                    'icon' => 'fa-plus-circle',
                                    'width' => 80,
                                    'descripcion' => 'Leads recién ingresados',
                                    'border_size' => '14px'
                                ],
                                [
                                    'label' => 'Contactado', 
                                    'count' => isset($embudo['contactado']) ? $embudo['contactado'] : 0, 
                                    'color' => '#14aa00', 
                                    'icon' => 'fa-phone',
                                    'width' => 68,
                                    'descripcion' => 'Leads con contacto inicial',
                                    'border_size' => '12px'
                                ],
                                [
                                    'label' => 'Procesando', 
                                    'count' => isset($embudo['procesando']) ? $embudo['procesando'] : 0, 
                                    'color' => '#F97316', 
                                    'icon' => 'fa-spinner',
                                    'width' => 56,
                                    'descripcion' => 'Leads en proceso de negociación',
                                    'border_size' => '10px'
                                ],
                                [
                                    'label' => 'Completado', 
                                    'count' => isset($embudo['completado']) ? $embudo['completado'] : 0, 
                                    'color' => '#1cc88a', 
                                    'icon' => 'fa-check-circle',
                                    'width' => 44,
                                    'descripcion' => 'Leads con cotizaciones completadas',
                                    'border_size' => '8px'
                                ],
                            ];
                            
                            foreach ($etapas as $index => $etapa):
                            ?>
                                <div class="funnel-step-row-wrapper">
                                    <div class="funnel-step-block-col">
                                        <div class="funnel-block-trapezoid funnel-block-dynamic"
                                             data-width="<?= $etapa['width'] ?>"
                                             data-color="<?= $etapa['color'] ?>"
                                             data-border="<?= $etapa['border_size'] ?>">
                                            <div class="funnel-tooltip-trapezoid">
                                                <i class="fas <?= $etapa['icon'] ?>"></i>
                                                <?= $etapa['label'] ?>: <?= $etapa['count'] ?> leads
                                                <br>
                                                <small><?= $etapa['descripcion'] ?></small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="funnel-step-info-col">
                                        <div class="funnel-step-label funnel-step-label-dynamic" data-color="<?= $etapa['color'] ?>">
                                            <i class="fas <?= $etapa['icon'] ?>"></i>
                                            <span><?= $etapa['label'] ?></span>
                                        </div>
                                        <div class="funnel-step-count funnel-step-count-dynamic" data-color="<?= $etapa['color'] ?>">
                                            <?= $etapa['count'] ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if ($index < count($etapas) - 1): ?>
                                    <div class="funnel-connector-trapezoid">
                                        <span>▾</span>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- VENTAS POR MES -->
            <div class="col-md-4">
                <div class="card chart-card dashboard-card h-100">
                    <div class="card-header">
                        <div class="header-left">
                            <i class="fas fa-chart-line text-success"></i>
                            <span>Ventas por Mes</span>
                        </div>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div class="chart-container">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACTIVIDADES RECIENTES -->
            <div class="col-md-4">
                <div class="card task-preview-card dashboard-card h-100">
                    <div class="card-header">
                        <div class="header-left">
                            <i class="fas fa-history text-primary"></i>
                            <span>Actividades Recientes</span>
                        </div>
                        <span class="badge bg-primary"><?= count($actividadesRecientes) ?></span>
                    </div>
                    <div class="card-body p-0">
                        <div class="activities-stack-list">
                            <?php if (!empty($actividadesRecientes)): ?>
                                <?php foreach ($actividadesRecientes as $index => $actividad): ?>
                                    <div class="activity-stack-item activity-stack-delay" data-delay="<?= $index * 0.05 ?>">
                                        <div class="activity-stack-icon">
                                            <i class="fas fa-<?= $actividad['icon'] ?> text-<?= $actividad['color'] ?>"></i>
                                        </div>
                                        <div class="activity-stack-content">
                                            <div class="activity-stack-title">
                                                <strong><?= Html::encode($actividad['lead_name']) ?></strong>
                                                <span class="badge bg-<?= $actividad['badge_color'] ?>">
                                                    <?= Html::encode($actividad['status']) ?>
                                                </span>
                                            </div>
                                            <div class="activity-stack-description">
                                                <?= Html::encode($actividad['description']) ?>
                                            </div>
                                            <div class="activity-stack-meta">
                                                <span class="activity-stack-date">
                                                    <i class="far fa-calendar-alt"></i> 
                                                    <?= $actividad['date'] ? date('d/m/Y H:i', strtotime($actividad['date'])) : 'Sin fecha' ?>
                                                </span>
                                                <?php if (!empty($actividad['user_name'])): ?>
                                                    <span class="activity-stack-user">
                                                        <i class="fas fa-user"></i> <?= Html::encode($actividad['user_name']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="activity-stack-empty">
                                    <i class="fas fa-inbox text-muted"></i>
                                    <p>No hay actividades recientes</p>
                                    <span class="text-muted activity-stack-empty-hint">Los seguimientos aparecerán aquí</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <?= Html::a('Ver todos los seguimientos <i class="fas fa-arrow-right"></i>', ['/sales-tracking/index'], [
                            'class' => 'btn btn-link btn-sm activity-footer-link'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILA 2: PRÓXIMAS ACTIVIDADES + GRÁFICA DE LEADS -->
        <div class="row g-2 mb-2">
            <!-- PRÓXIMAS ACTIVIDADES -->
            <div class="col-md-6">
                <div class="card task-preview-card dashboard-card h-100">
                    <div class="card-header">
                        <div class="header-left">
                            <i class="fas fa-calendar-check text-success"></i>
                            <span>Próximas Actividades</span>
                        </div>
                        <span class="badge bg-success"><?= count($proximasActividades) ?></span>
                    </div>
                    <div class="card-body p-0">
                        <div class="activities-stack-list">
                            <?php if (!empty($proximasActividades)): ?>
                                <?php foreach ($proximasActividades as $index => $actividad): ?>
                                    <div class="activity-stack-item activity-stack-upcoming activity-stack-delay" data-delay="<?= $index * 0.05 ?>">
                                        <div class="activity-stack-icon">
                                            <i class="fas fa-<?= $actividad['icon'] ?> text-<?= $actividad['color'] ?>"></i>
                                        </div>
                                        <div class="activity-stack-content">
                                            <div class="activity-stack-title">
                                                <strong><?= Html::encode($actividad['lead_name']) ?></strong>
                                                <span class="badge bg-<?= $actividad['badge_color'] ?>">
                                                    <?= Html::encode($actividad['status']) ?>
                                                </span>
                                            </div>
                                            <div class="activity-stack-description">
                                                <?= Html::encode($actividad['description']) ?>
                                            </div>
                                            <div class="activity-stack-meta">
                                                <span class="activity-stack-date text-warning">
                                                    <i class="far fa-clock"></i> 
                                                    <?= $actividad['date'] ? date('d/m/Y H:i', strtotime($actividad['date'])) : 'Sin fecha' ?>
                                                </span>
                                                <?php if (!empty($actividad['user_name'])): ?>
                                                    <span class="activity-stack-user">
                                                        <i class="fas fa-user"></i> <?= Html::encode($actividad['user_name']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="activity-stack-empty">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <p>No hay próximas actividades</p>
                                    <span class="text-muted activity-stack-empty-hint">Todo al día</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <?= Html::a('Ver todos los seguimientos <i class="fas fa-arrow-right"></i>', ['/sales-tracking/index'], [
                            'class' => 'btn btn-link btn-sm activity-footer-link'
                        ]) ?>
                    </div>
                </div>
            </div>

            <!-- GRÁFICA DE LEADS POR ESTADO + CONTADOR MENSUAL -->
            <div class="col-md-6">
                <div class="card chart-card dashboard-card h-100">
                    <div class="card-header">
                        <div class="header-left">
                            <i class="fas fa-chart-pie text-primary"></i>
                            <span>Leads por Estado</span>
                        </div>
                        <span class="badge bg-primary"><?= array_sum($leadsData) ?> total</span>
                    </div>
                    <div class="card-body leads-card-body">
                        <!-- GRÁFICA + LEYENDA -->
                        <div class="chart-with-legend">
                            <div class="chart-container-leads">
                                <canvas id="leadsStatusChart"></canvas>
                            </div>
                            <div class="legend-container">
                                <?php foreach ($leadsLabels as $index => $label): ?>
                                    <div class="legend-item">
                                        <span class="legend-color legend-color-dynamic" data-color="<?= $leadsColors[$index] ?>"></span>
                                        <span class="legend-label"><?= $label ?></span>
                                        <span class="legend-value"><?= $leadsData[$index] ?></span>
                                        <span class="legend-separator">|</span>
                                        <span class="legend-percentage">
                                            <?php 
                                            $total = array_sum($leadsData);
                                            $percentage = $total > 0 ? round(($leadsData[$index] / $total) * 100, 1) : 0;
                                            echo $percentage . '%';
                                            ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- CONTADOR DE LEADS NUEVOS POR MES -->
                        <div class="leads-monthly-counter">
                            <div class="leads-monthly-header">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Leads Nuevos por Mes - <?= $anioActualLeads ?></span>
                                <span class="badge bg-primary"><?= $totalLeadsAnio ?> total</span>
                            </div>
                            <div class="leads-monthly-grid">
                                <?php foreach ($leadsMesesData as $mesData): ?>
                                    <div class="leads-monthly-item <?= $mesData['count'] > 0 ? 'has-data' : '' ?>">
                                        <div class="leads-monthly-mes"><?= $mesData['mes'] ?></div>
                                        <div class="leads-monthly-count"><?= $mesData['count'] ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- 🔥 FILA 3: OBJETIVOS (VENTAS + UTILIDAD)    -->
        <!-- ============================================ -->
        <div class="row g-2 mb-2">

            <!-- 🎯 META DE VENTAS -->
            <div class="col-md-<?= $mostrarUtilidad ? '6' : '12' ?>">
                <div class="card goal-card dashboard-card h-100">
                    <div class="card-header">
                        <div class="header-left">
                            <i class="fas fa-shopping-cart text-primary"></i>
                            <span><?= $esAdminOSuperAdmin ? 'Meta de Ventas' : 'Mi Meta de Ventas' ?> - <?= date('F Y') ?></span>
                        </div>
                        <div class="header-right-badges">
                            <?php /* 🔥 Badge de agentes: SOLO para admin/superadmin */ ?>
                            <?php if ($esAdminOSuperAdmin && $cantidadAgentes > 0): ?>
                                <span class="badge bg-info text-white">
                                    <i class="fas fa-users"></i> <?= $cantidadAgentes ?> agentes
                                </span>
                            <?php endif; ?>
                            <span class="badge bg-primary">
                                $<?= number_format($metaMensual, 0, '.', ',') ?>
                            </span>
                            <span class="badge-percent <?= $metaAlcanzada ? 'bg-success' : ($porcentajeAlcanzado >= 50 ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                <?= $porcentajeAlcanzado ?>%
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Barra horizontal larga -->
                        <div class="progress-custom mb-2">
                            <div class="progress-bar progress-bar-dynamic <?= $porcentajeAlcanzado >= 100 ? 'bg-success' : ($porcentajeAlcanzado >= 50 ? 'bg-primary' : 'bg-warning') ?>"
                                 data-width="<?= min($porcentajeAlcanzado, 100) ?>">
                            </div>
                        </div>

                        <div class="row g-1">
                            <div class="col-4">
                                <div class="p-1 bg-light rounded-2 text-center">
                                    <div class="text-muted meta-label">Meta</div>
                                    <div class="fw-bold meta-value">
                                        $<?= number_format($metaMensual, 0, '.', ',') ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-1 bg-light rounded-2 text-center">
                                    <div class="text-muted meta-label">Alcanzado</div>
                                    <div class="fw-bold text-primary meta-value">
                                        $<?= number_format($ventasMesActual, 0, '.', ',') ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-1 bg-light rounded-2 text-center">
                                    <div class="text-muted meta-label">Restante</div>
                                    <div class="fw-bold <?= $montoRestante > 0 ? 'text-warning' : 'text-success' ?> meta-value">
                                        $<?= number_format($montoRestante, 0, '.', ',') ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-2 text-center meta-footer">
                            <span class="text-muted">
                                <i class="fas fa-chart-line"></i>
                                Progreso: <?= $porcentajeAlcanzado ?>%
                                <?php if ($metaAlcanzada): ?>
                                    <span class="text-success ms-2">
                                        <i class="fas fa-trophy"></i> ¡Meta alcanzada!
                                    </span>
                                <?php else: ?>
                                    <span class="text-warning ms-2">
                                        <i class="fas fa-hourglass-half"></i>
                                        Faltan $<?= number_format($montoRestante, 0, '.', ',') ?>
                                    </span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🎯 META DE UTILIDAD (solo admin) -->
            <?php if ($mostrarUtilidad): ?>
                <div class="col-md-6">
                    <div class="card goal-card dashboard-card h-100">
                        <div class="card-header">
                            <div class="header-left">
                                <i class="fas fa-hand-holding-usd text-success"></i>
                                <span>Meta de Utilidad - <?= date('F Y') ?></span>
                            </div>
                            <div class="header-right-badges">
                                <span class="badge bg-success">
                                    $<?= number_format($metaUtilidadMensual, 0, '.', ',') ?>
                                </span>
                                <span class="badge-percent <?= $metaUtilidadAlcanzada ? 'bg-success' : ($porcentajeUtilidadAlcanzado >= 50 ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                    <?= $porcentajeUtilidadAlcanzado ?>%
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="progress-custom mb-2">
                                <div class="progress-bar progress-bar-dynamic <?= $porcentajeUtilidadAlcanzado >= 100 ? 'bg-success' : ($porcentajeUtilidadAlcanzado >= 50 ? 'bg-info' : 'bg-danger') ?>"
                                     data-width="<?= min($porcentajeUtilidadAlcanzado, 100) ?>">
                                </div>
                            </div>

                            <div class="row g-1">
                                <div class="col-4">
                                    <div class="p-1 bg-light rounded-2 text-center">
                                        <div class="text-muted meta-label">Meta</div>
                                        <div class="fw-bold meta-value">
                                            $<?= number_format($metaUtilidadMensual, 0, '.', ',') ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-1 bg-light rounded-2 text-center">
                                        <div class="text-muted meta-label">Alcanzado</div>
                                        <div class="fw-bold text-success meta-value">
                                            $<?= number_format($utilidadMesActual, 0, '.', ',') ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-1 bg-light rounded-2 text-center">
                                        <div class="text-muted meta-label">Restante</div>
                                        <div class="fw-bold <?= $utilidadRestante > 0 ? 'text-warning' : 'text-success' ?> meta-value">
                                            $<?= number_format($utilidadRestante, 0, '.', ',') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-2 text-center meta-footer">
                                <span class="text-muted">
                                    <i class="fas fa-chart-line"></i>
                                    Progreso: <?= $porcentajeUtilidadAlcanzado ?>%
                                    <?php if ($metaUtilidadAlcanzada): ?>
                                        <span class="text-success ms-2">
                                            <i class="fas fa-trophy"></i> ¡Meta alcanzada!
                                        </span>
                                    <?php else: ?>
                                        <span class="text-warning ms-2">
                                            <i class="fas fa-hourglass-half"></i>
                                            Faltan $<?= number_format($utilidadRestante, 0, '.', ',') ?>
                                        </span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <!-- FILA 4: ACTUAL VS TARGET (solo admin) -->
        <?php if ($mostrarUtilidad): ?>
            <div class="row g-2 mb-2">
                <div class="col-md-12">
                    <div class="card goal-card dashboard-card h-100">
                        <div class="card-header">
                            <div class="header-left">
                                <i class="fas fa-arrows-left-right text-info"></i>
                                <span>Actual vs Target (Últimos 6 meses)</span>
                            </div>
                            <span class="badge bg-info text-white">
                                Meta: $<?= number_format($metaMensual, 0, '.', ',') ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="chart-container-sm">
                                <canvas id="comparisonChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- 🔥 FILA 5: VENTA TOTAL Y UTILIDAD TOTAL -->
        <div class="row g-2">

            <!-- VENTA TOTAL (visible para todos) -->
            <div class="col-md-<?= $mostrarUtilidad ? '6' : '12' ?>">
                <div class="card total-sale-card dashboard-card h-100">
                    <div class="card-header">
                        <div class="header-left">
                            <i class="fas fa-shopping-cart text-primary"></i>
                            <span>Venta Total</span>
                        </div>
                        <span class="badge bg-primary">
                            <?= $totalGlobal['cotizaciones'] ?> cotiz.
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="total-wrapper">
                            <div class="total-value total-value-sale">
                                $<?= number_format($totalGlobal['venta'], 2, '.', ',') ?>
                            </div>

                            <div class="total-mini-grid">
                                <div class="total-mini-item">
                                    <div class="total-mini-label">Este mes</div>
                                    <div class="total-mini-value">
                                        $<?= number_format($totalMes['venta'], 2, '.', ',') ?>
                                    </div>
                                </div>
                                <div class="total-mini-item">
                                    <div class="total-mini-label">Este año</div>
                                    <div class="total-mini-value">
                                        $<?= number_format($totalAnio['venta'], 2, '.', ',') ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- UTILIDAD TOTAL (solo admin) -->
            <?php if ($mostrarUtilidad): ?>
                <div class="col-md-6">
                    <div class="card total-profit-card dashboard-card h-100">
                        <div class="card-header">
                            <div class="header-left">
                                <i class="fas fa-hand-holding-usd text-success"></i>
                                <span>Utilidad (<?= number_format($porcentajeUtilidad, 0) ?>%)</span>
                            </div>
                            <span class="badge bg-success">
                                Margen
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="total-wrapper">
                                <div class="total-value total-value-profit">
                                    $<?= number_format($totalGlobal['utilidad'], 2, '.', ',') ?>
                                </div>

                                <div class="total-mini-grid">
                                    <div class="total-mini-item">
                                        <div class="total-mini-label">Este mes</div>
                                        <div class="total-mini-value total-mini-value-profit">
                                            $<?= number_format($totalMes['utilidad'], 2, '.', ',') ?>
                                        </div>
                                    </div>
                                    <div class="total-mini-item">
                                        <div class="total-mini-label">Este año</div>
                                        <div class="total-mini-value total-mini-value-profit">
                                            $<?= number_format($totalAnio['utilidad'], 2, '.', ',') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php
// ============================================
// PREPARAR DATOS PARA LAS GRÁFICAS
// ============================================
$leadsLabelsJson = json_encode($leadsLabels);
$leadsDataJson = json_encode($leadsData);
$leadsColorsJson = json_encode($leadsColors);

$ventasData = array_fill(0, 12, 0);
foreach ($ventasPorMes as $item) {
    if (isset($item['mes']) && isset($item['total'])) {
        $mesIndex = (int)$item['mes'] - 1;
        if ($mesIndex >= 0 && $mesIndex < 12) {
            $ventasData[$mesIndex] = (int)$item['total'];
        }
    }
}
$ventasDataJson = json_encode($ventasData);

$mesesLabelsJson = json_encode($mesesLabels);
$actualDataJson = json_encode($actualData);
$targetDataJson = json_encode($targetData);
$perdidoDataJson = json_encode($perdidoData);

// 🔥 Flag para saber si renderizar la gráfica de comparación
$renderComparisonChart = $mostrarUtilidad;
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // 🔥 HELPER: Formatear números grandes (K / M)
    // ============================================
    function formatMoneyShort(value) {
        if (value >= 1000000) {
            var m = value / 1000000;
            return '$' + (m % 1 === 0 ? m.toFixed(0) : m.toFixed(1)) + 'M';
        }
        if (value >= 1000) {
            var k = value / 1000;
            return '$' + (k % 1 === 0 ? k.toFixed(0) : k.toFixed(1)) + 'K';
        }
        return '$' + value;
    }

    // ============================================
    // 🔥 APLICAR ESTILOS DINÁMICOS
    // ============================================
    document.querySelectorAll('.funnel-block-dynamic').forEach(function(el) {
        var width = el.getAttribute('data-width');
        var color = el.getAttribute('data-color');
        var border = el.getAttribute('data-border');
        el.style.width = width + '%';
        el.style.background = color;
        el.style.borderLeft = border + ' solid transparent';
        el.style.borderRight = border + ' solid transparent';
        el.style.borderTop = '0px solid transparent';
    });

    document.querySelectorAll('.funnel-step-label-dynamic').forEach(function(el) {
        el.style.color = el.getAttribute('data-color');
    });

    document.querySelectorAll('.funnel-step-count-dynamic').forEach(function(el) {
        el.style.backgroundColor = el.getAttribute('data-color');
    });

    document.querySelectorAll('.legend-color-dynamic').forEach(function(el) {
        el.style.backgroundColor = el.getAttribute('data-color');
    });

    document.querySelectorAll('.progress-bar-dynamic').forEach(function(el) {
        el.style.width = el.getAttribute('data-width') + '%';
    });

    document.querySelectorAll('.activity-stack-delay').forEach(function(el) {
        var delay = parseFloat(el.getAttribute('data-delay')) || 0;
        el.style.animationDelay = delay + 's';
    });

    // ============================================
    // GRÁFICO DE LEADS POR ESTADO (PASTEL)
    // ============================================
    const ctxLeads = document.getElementById('leadsStatusChart').getContext('2d');
    const leadsLabels = <?= $leadsLabelsJson ?>;
    const leadsData = <?= $leadsDataJson ?>;
    const leadsColors = <?= $leadsColorsJson ?>;

    const canvasLeads = document.getElementById('leadsStatusChart');
    const parentWidthLeads = canvasLeads.parentElement.clientWidth || 200;
    const dprLeads = window.devicePixelRatio || 1;

    canvasLeads.width = parentWidthLeads * dprLeads;
    canvasLeads.height = 180 * dprLeads;
    canvasLeads.style.width = parentWidthLeads + 'px';
    canvasLeads.style.height = '180px';

    new Chart(ctxLeads, {
        type: 'doughnut',
        data: {
            labels: leadsLabels,
            datasets: [{
                data: leadsData,
                backgroundColor: leadsColors,
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            devicePixelRatio: dprLeads,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(255,255,255,0.95)',
                    titleColor: '#2c3e50',
                    bodyColor: '#2c3e50',
                    borderColor: '#e9ecef',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 10,
                    callbacks: {
                        label: function(context) {
                            const total = leadsData.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            cutout: '55%',
            animation: { animateRotate: true, duration: 600 }
        }
    });

    // ============================================
    // GRÁFICO DE VENTAS POR MES
    // ============================================
    const ctxLine = document.getElementById('salesChart').getContext('2d');
    const ventasData = <?= $ventasDataJson ?>;
    const maxVentas = Math.max(...ventasData, 1);

    const canvasLine = document.getElementById('salesChart');
    const parentWidthLine = canvasLine.parentElement.clientWidth || 300;
    const dprLine = window.devicePixelRatio || 1;

    canvasLine.width = parentWidthLine * dprLine;
    canvasLine.height = 240 * dprLine;
    canvasLine.style.width = parentWidthLine + 'px';
    canvasLine.style.height = '240px';

    const salesChart = new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            datasets: [{
                label: 'Ventas ' + new Date().getFullYear(),
                data: ventasData,
                borderColor: '#1cc88a',
                backgroundColor: 'rgba(28, 200, 138, 0.08)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#1cc88a',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            devicePixelRatio: dprLine,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(255,255,255,0.95)',
                    titleColor: '#2c3e50',
                    bodyColor: '#2c3e50',
                    borderColor: '#e9ecef',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 10,
                    callbacks: {
                        label: function(context) {
                            return '💰 Cotizaciones: ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: maxVentas + 1,
                    ticks: {
                        stepSize: Math.ceil(maxVentas / 5) || 1,
                        font: { size: 10, weight: '500' },
                        color: '#6c757d'
                    },
                    grid: { color: 'rgba(0,0,0,0.06)', drawBorder: false }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10, weight: '500' }, color: '#6c757d', maxRotation: 0 }
                }
            },
            interaction: { intersect: false, mode: 'index' },
            animation: { duration: 600, easing: 'easeOutQuart' }
        }
    });

    setTimeout(function() { salesChart.resize(); }, 200);

    // ============================================
    // 🔥 GRÁFICO DE COMPARACIÓN (solo admin)
    // ============================================
    <?php if ($renderComparisonChart): ?>
    const ctxComparison = document.getElementById('comparisonChart').getContext('2d');
    const mesesLabels = <?= $mesesLabelsJson ?>;
    const actualData = <?= $actualDataJson ?>;
    const targetData = <?= $targetDataJson ?>;
    const perdidoData = <?= $perdidoDataJson ?>;

    const canvasComp = document.getElementById('comparisonChart');
    const parentWidthComp = canvasComp.parentElement.clientWidth || 220;
    const dprComp = window.devicePixelRatio || 1;

    canvasComp.width = parentWidthComp * dprComp;
    canvasComp.height = 170 * dprComp;
    canvasComp.style.width = parentWidthComp + 'px';
    canvasComp.style.height = '170px';

    const maxComparacion = Math.max(...actualData, ...targetData, ...perdidoData, 1);

    new Chart(ctxComparison, {
        type: 'bar',
        data: {
            labels: mesesLabels,
            datasets: [
                {
                    label: 'Actual',
                    data: actualData,
                    backgroundColor: 'rgba(78, 115, 223, 0.85)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    borderWidth: 1,
                    borderRadius: 3,
                    maxBarThickness: 14
                },
                {
                    label: 'Perdido',
                    data: perdidoData,
                    backgroundColor: 'rgba(231, 74, 59, 0.85)',
                    borderColor: 'rgba(231, 74, 59, 1)',
                    borderWidth: 1,
                    borderRadius: 3,
                    maxBarThickness: 14
                },
                {
                    label: 'Target',
                    data: targetData,
                    backgroundColor: 'rgba(150, 150, 150, 0.6)',
                    borderColor: 'rgba(150, 150, 150, 1)',
                    borderWidth: 1,
                    borderRadius: 3,
                    maxBarThickness: 14
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            devicePixelRatio: dprComp,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: { size: 9, weight: '600' },
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 8,
                        boxWidth: 8
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255,255,255,0.95)',
                    titleColor: '#2c3e50',
                    bodyColor: '#2c3e50',
                    borderColor: '#e9ecef',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 10,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            let value = context.parsed.y || 0;
                            return label + ': $' + new Intl.NumberFormat('es-MX').format(value);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: maxComparacion * 1.1,
                    ticks: {
                        font: { size: 9 },
                        // 🔥 FORMATO: K para miles, M para millones
                        callback: function(value) {
                            if (value >= 1000000) {
                                var m = value / 1000000;
                                return '$' + (m % 1 === 0 ? m.toFixed(0) : m.toFixed(1)) + 'M';
                            }
                            if (value >= 1000) {
                                var k = value / 1000;
                                return '$' + (k % 1 === 0 ? k.toFixed(0) : k.toFixed(1)) + 'K';
                            }
                            return '$' + value;
                        }
                    },
                    grid: { color: 'rgba(0,0,0,0.04)' }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 9 } }
                }
            },
            animation: { duration: 500 }
        }
    });
    <?php endif; ?>

    // ============================================
    // RESIZE
    // ============================================
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            salesChart.resize();
        }, 250);
    });
});
</script>