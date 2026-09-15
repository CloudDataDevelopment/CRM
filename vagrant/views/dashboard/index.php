<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js');

$this->registerCssFile('@web/css/dashboard.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
]);
?>

<div class="dashboard-index">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-1 pb-1 mb-2 border-bottom">
        <h6 class="h6 mb-0">
            <i class="fas fa-chart-pie text-primary me-1"></i> Dashboard
        </h6>
        <div class="d-flex gap-2 align-items-center">
            <span class="text-muted" style="font-size: 0.7rem;">
                <i class="far fa-calendar-alt me-1"></i> <?= date('d/m/Y') ?>
            </span>
            <span class="badge bg-success bg-opacity-10 text-success" style="font-size: 0.6rem;">
                <i class="fas fa-circle text-success me-1" style="font-size: 5px;"></i> Activo
            </span>
        </div>
    </div>

    <!-- Tarjetas de métricas -->
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
                        <div class="stat-label">Total Citas</div>
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

    <!-- ============================================ -->
    <!-- FILA 1: EMBUDO + VENTAS POR MES + TAREAS RECIENTES -->
    <!-- ============================================ -->
    <div class="row g-2 mb-2">
        <!-- Columna 1: EMBUDO DE VENTAS -->
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
                                'label' => 'Calificado', 
                                'count' => isset($embudo['calificado']) ? $embudo['calificado'] : 0, 
                                'color' => '#F97316', 
                                'icon' => 'fa-star',
                                'width' => 56,
                                'descripcion' => 'Leads calificados',
                                'border_size' => '10px'
                            ],
                            [
                                'label' => 'Ganado', 
                                'count' => isset($embudo['ganado']) ? $embudo['ganado'] : 0, 
                                'color' => '#8B5CF6', 
                                'icon' => 'fa-trophy',
                                'width' => 44,
                                'descripcion' => 'Leads convertidos en clientes',
                                'border_size' => '8px'
                            ],
                        ];
                        
                        foreach ($etapas as $index => $etapa):
                            $color = $etapa['color'];
                            $width = $etapa['width'];
                            $borderSize = $etapa['border_size'];
                        ?>
                            <div class="funnel-step-trapezoid">
                                <div class="funnel-block-trapezoid" style="
                                    width: <?= $width ?>%;
                                    background: <?= $color ?>;
                                    border-left: <?= $borderSize ?> solid transparent;
                                    border-right: <?= $borderSize ?> solid transparent;
                                    border-top: 0px solid transparent;
                                ">
                                    <div class="funnel-block-content-trapezoid">
                                        <div class="funnel-label-trapezoid">
                                            <i class="fas <?= $etapa['icon'] ?>"></i>
                                            <span class="funnel-label-text"><?= $etapa['label'] ?></span>
                                        </div>
                                        <div class="funnel-stats-trapezoid">
                                            <span class="funnel-count-trapezoid"><?= $etapa['count'] ?></span>
                                        </div>
                                    </div>
                                    <div class="funnel-tooltip-trapezoid">
                                        <i class="fas <?= $etapa['icon'] ?>"></i>
                                        <?= $etapa['label'] ?>: <?= $etapa['count'] ?> leads
                                        <br>
                                        <small><?= $etapa['descripcion'] ?></small>
                                    </div>
                                </div>
                                <?php if ($index < count($etapas) - 1): ?>
                                    <div class="funnel-connector-trapezoid">
                                        <span>▾</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna 2: VENTAS POR MES -->
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

        <!-- Columna 3: TAREAS RECIENTES (VENCIDAS) -->
        <div class="col-md-4">
            <div class="card task-preview-card dashboard-card h-100">
                <div class="card-header">
                    <div class="header-left">
                        <i class="fas fa-clock text-danger"></i>
                        <span>Tareas Recientes</span>
                    </div>
                    <span class="badge bg-danger"><?= count($tareasRecientes) ?></span>
                </div>
                <div class="card-body p-0">
                    <div class="task-list">
                        <?php if (!empty($tareasRecientes)): ?>
                            <?php foreach ($tareasRecientes as $tarea): ?>
                                <?php 
                                $fechaTarea = $tarea->salesTracking ? $tarea->salesTracking->date_s : null;
                                $horaTarea = $tarea->salesTracking ? $tarea->salesTracking->hour : null;
                                ?>
                                <div class="task-item task-item-overdue">
                                    <div class="task-check">
                                        <input type="checkbox" class="task-checkbox" data-id="<?= $tarea->id_task ?>">
                                    </div>
                                    <div class="task-content">
                                        <div class="task-title">
                                            <?= Html::encode($tarea->comments) ?>
                                            <?php if ($tarea->salesTracking && $tarea->salesTracking->lead): ?>
                                                <span class="task-lead">
                                                    <i class="fas fa-user"></i> <?= Html::encode($tarea->salesTracking->lead->name . ' ' . $tarea->salesTracking->lead->lastname) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="task-meta">
                                            <span class="badge-status badge-status-overdue">
                                                <i class="fas fa-exclamation-circle"></i> Vencida
                                            </span>
                                            <?php if ($fechaTarea): ?>
                                                <span class="task-date text-danger">
                                                    <i class="far fa-calendar-alt"></i> 
                                                    <?= date('d/m/Y', strtotime($fechaTarea)) ?>
                                                    <i class="far fa-clock ms-1"></i>
                                                    <?= $horaTarea ? substr($horaTarea, 0, 5) : '00:00' ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="task-date text-muted">Sin fecha</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="task-actions">
                                        <?= Html::a('<i class="fas fa-eye"></i>', ['/task/update', 'id' => $tarea->id_task], [
                                            'class' => 'btn btn-sm btn-outline-primary btn-action',
                                            'title' => 'Ver tarea'
                                        ]) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="task-empty">
                                <i class="fas fa-check-circle text-success"></i>
                                <p>¡No hay tareas vencidas!</p>
                                <span class="text-muted" style="font-size: 0.7rem;">Todas las tareas están al día</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <?= Html::a('Ver todas las tareas <i class="fas fa-arrow-right"></i>', ['/task/index'], [
                        'class' => 'btn btn-link btn-sm',
                        'style' => 'font-size: 0.7rem; text-decoration: none;'
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- FILA 2: PRÓXIMAS TAREAS + GRÁFICA DE LEADS   -->
    <!-- ============================================ -->
    <div class="row g-2 mb-2">
        <!-- Columna 1: PRÓXIMAS TAREAS -->
        <div class="col-md-6">
            <div class="card task-preview-card dashboard-card h-100">
                <div class="card-header">
                    <div class="header-left">
                        <i class="fas fa-calendar-check text-success"></i>
                        <span>Próximas Tareas</span>
                    </div>
                    <span class="badge bg-success"><?= count($proximasTareas) ?></span>
                </div>
                <div class="card-body p-0">
                    <div class="task-list">
                        <?php if (!empty($proximasTareas)): ?>
                            <?php foreach ($proximasTareas as $tarea): ?>
                                <?php 
                                $fechaTarea = $tarea->salesTracking ? $tarea->salesTracking->date_s : null;
                                $horaTarea = $tarea->salesTracking ? $tarea->salesTracking->hour : null;
                                ?>
                                <div class="task-item task-item-upcoming">
                                    <div class="task-check">
                                        <input type="checkbox" class="task-checkbox" data-id="<?= $tarea->id_task ?>">
                                    </div>
                                    <div class="task-content">
                                        <div class="task-title">
                                            <?= Html::encode($tarea->comments) ?>
                                            <?php if ($tarea->salesTracking && $tarea->salesTracking->lead): ?>
                                                <span class="task-lead">
                                                    <i class="fas fa-user"></i> <?= Html::encode($tarea->salesTracking->lead->name . ' ' . $tarea->salesTracking->lead->lastname) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="task-meta">
                                            <span class="badge-status badge-status-upcoming">
                                                <i class="fas fa-clock"></i> Próxima
                                            </span>
                                            <?php if ($fechaTarea): ?>
                                                <span class="task-date text-primary">
                                                    <i class="far fa-calendar-alt"></i> 
                                                    <?= date('d/m/Y', strtotime($fechaTarea)) ?>
                                                    <i class="far fa-clock ms-1"></i>
                                                    <?= $horaTarea ? substr($horaTarea, 0, 5) : '00:00' ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="task-date text-muted">Sin fecha</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="task-actions">
                                        <?= Html::a('<i class="fas fa-eye"></i>', ['/task/update', 'id' => $tarea->id_task], [
                                            'class' => 'btn btn-sm btn-outline-primary btn-action',
                                            'title' => 'Ver tarea'
                                        ]) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="task-empty">
                                <i class="fas fa-check-circle text-success"></i>
                                <p>¡No hay próximas tareas!</p>
                                <span class="text-muted" style="font-size: 0.7rem;">Todo completado</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <?= Html::a('Ver todas las tareas <i class="fas fa-arrow-right"></i>', ['/task/index'], [
                        'class' => 'btn btn-link btn-sm',
                        'style' => 'font-size: 0.7rem; text-decoration: none;'
                    ]) ?>
                </div>
            </div>
        </div>

        <!-- Columna 2: GRÁFICA DE LEADS POR ESTADO -->
        <div class="col-md-6">
            <div class="card chart-card dashboard-card h-100">
                <div class="card-header">
                    <div class="header-left">
                        <i class="fas fa-chart-pie text-primary"></i>
                        <span>Leads por Estado</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-with-legend">
                        <div class="chart-container-leads">
                            <canvas id="leadsStatusChart"></canvas>
                        </div>
                        <div class="legend-container">
                            <?php foreach ($leadsLabels as $index => $label): ?>
                                <div class="legend-item">
                                    <span class="legend-color" style="background-color: <?= $leadsColors[$index] ?>;"></span>
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
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- FILA 3: OBJETIVOS                            -->
    <!-- ============================================ -->
    <div class="row g-2">
        <div class="col-md-6">
            <div class="card goal-card dashboard-card">
                <div class="card-header">
                    <div class="header-left">
                        <i class="fas fa-bullseye text-danger"></i>
                        <span>Objetivo Mensual</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="progress-custom mb-2">
                        <div class="progress-bar bg-success" style="width: 65%;">
                            65%
                        </div>
                    </div>
                    <div class="row g-1">
                        <div class="col-4">
                            <div class="p-1 bg-light rounded-2 text-center">
                                <div class="text-muted" style="font-size: 0.5rem;">Meta</div>
                                <div class="fw-bold" style="font-size: 0.75rem;">$100K</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-1 bg-light rounded-2 text-center">
                                <div class="text-muted" style="font-size: 0.5rem;">Alcanzado</div>
                                <div class="fw-bold text-success" style="font-size: 0.75rem;">$65K</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-1 bg-light rounded-2 text-center">
                                <div class="text-muted" style="font-size: 0.5rem;">Restante</div>
                                <div class="fw-bold text-warning" style="font-size: 0.75rem;">$35K</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card goal-card dashboard-card">
                <div class="card-header">
                    <div class="header-left">
                        <i class="fas fa-arrows-left-right text-info"></i>
                        <span>Actual vs Target</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container-sm">
                        <canvas id="comparisonChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// ============================================
// PREPARAR DATOS PARA LA GRÁFICA DE LEADS
// ============================================
$leadsLabelsJson = json_encode($leadsLabels);
$leadsDataJson = json_encode($leadsData);
$leadsColorsJson = json_encode($leadsColors);

// Datos de Ventas
$ventasData = array_fill(0, 12, 0);
foreach ($ventasPorMes as $item) {
    if (isset($item['mes']) && isset($item['total'])) {
        $ventasData[$item['mes'] - 1] = (int)$item['total'];
    }
}
$ventasDataJson = json_encode($ventasData);
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // GRÁFICO DE LEADS POR ESTADO
    // ============================================
    const ctxLeads = document.getElementById('leadsStatusChart').getContext('2d');
    const leadsLabels = <?= $leadsLabelsJson ?>;
    const leadsData = <?= $leadsDataJson ?>;
    const leadsColors = <?= $leadsColorsJson ?>;

    const canvasLeads = document.getElementById('leadsStatusChart');
    const parentWidthLeads = canvasLeads.parentElement.clientWidth || 200;
    const dprLeads = window.devicePixelRatio || 1;

    canvasLeads.width = parentWidthLeads * dprLeads;
    canvasLeads.height = 200 * dprLeads;
    canvasLeads.style.width = parentWidthLeads + 'px';
    canvasLeads.style.height = '200px';

    const totalLeads = leadsData.reduce((a, b) => a + b, 0);

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
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(255,255,255,0.95)',
                    titleColor: '#2c3e50',
                    bodyColor: '#2c3e50',
                    borderColor: '#e9ecef',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 10,
                    bodyFont: {
                        size: 11,
                        family: "'Segoe UI', system-ui, sans-serif"
                    },
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
            animation: {
                animateRotate: true,
                duration: 600
            }
        }
    });

    // ============================================
    // GRÁFICO DE VENTAS POR MES
    // ============================================
    const ctxLine = document.getElementById('salesChart').getContext('2d');
    const ventasData = <?= $ventasDataJson ?>;

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
                label: 'Ventas 2025',
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
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#1cc88a',
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            devicePixelRatio: dprLine,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(255,255,255,0.95)',
                    titleColor: '#2c3e50',
                    bodyColor: '#2c3e50',
                    borderColor: '#e9ecef',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 10,
                    bodyFont: {
                        size: 11,
                        family: "'Segoe UI', system-ui, sans-serif"
                    },
                    callbacks: {
                        label: function(context) {
                            return '💰 Ventas: ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: {
                            size: 10,
                            weight: '500',
                            family: "'Segoe UI', system-ui, sans-serif"
                        },
                        color: '#6c757d'
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.06)',
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 10,
                            weight: '500',
                            family: "'Segoe UI', system-ui, sans-serif"
                        },
                        color: '#6c757d',
                        maxRotation: 0
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            },
            animation: {
                duration: 600,
                easing: 'easeOutQuart'
            }
        }
    });

    // Forzar redibujado
    setTimeout(function() {
        salesChart.resize();
    }, 200);

    // ============================================
    // GRÁFICO DE COMPARACIÓN
    // ============================================
    const ctxComparison = document.getElementById('comparisonChart').getContext('2d');

    const canvasComp = document.getElementById('comparisonChart');
    const parentWidthComp = canvasComp.parentElement.clientWidth || 220;
    const dprComp = window.devicePixelRatio || 1;

    canvasComp.width = parentWidthComp * dprComp;
    canvasComp.height = 140 * dprComp;
    canvasComp.style.width = parentWidthComp + 'px';
    canvasComp.style.height = '140px';

    new Chart(ctxComparison, {
        type: 'bar',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
            datasets: [
                {
                    label: 'Actual',
                    data: [65, 59, 80, 81, 56, 55],
                    backgroundColor: 'rgba(78, 115, 223, 0.8)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    borderWidth: 1,
                    borderRadius: 3,
                    maxBarThickness: 16
                },
                {
                    label: 'Target',
                    data: [70, 65, 85, 90, 70, 65],
                    backgroundColor: 'rgba(231, 74, 59, 0.8)',
                    borderColor: 'rgba(231, 74, 59, 1)',
                    borderWidth: 1,
                    borderRadius: 3,
                    maxBarThickness: 16
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
                        font: {
                            size: 9,
                            weight: '600',
                            family: "'Segoe UI', system-ui, sans-serif"
                        },
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 8,
                        boxWidth: 8
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: {
                            size: 9,
                            family: "'Segoe UI', system-ui, sans-serif"
                        },
                        stepSize: 20
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.04)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 9,
                            family: "'Segoe UI', system-ui, sans-serif"
                        }
                    }
                }
            },
            animation: {
                duration: 500
            }
        }
    });

    // ============================================
    // CHECKBOX PARA TAREAS
    // ============================================
    document.querySelectorAll('.task-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const taskId = this.getAttribute('data-id');
            const taskItem = this.closest('.task-item');
            
            if (this.checked) {
                taskItem.style.opacity = '0.5';
                taskItem.style.textDecoration = 'line-through';
                taskItem.style.transition = 'all 0.3s ease';
                
                setTimeout(function() {
                    taskItem.style.display = 'none';
                }, 1000);
            } else {
                taskItem.style.opacity = '1';
                taskItem.style.textDecoration = 'none';
            }
        });
    });

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