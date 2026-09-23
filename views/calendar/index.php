<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Calendario';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/calendar.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

// ============================================
// VARIABLES DEL CONTROLADOR
// ============================================
$events = isset($events) ? $events : [];
$selectedMonth = isset($selectedMonth) ? $selectedMonth : date('Y-m');
$selectedDay = isset($selectedDay) ? $selectedDay : date('Y-m-d');
$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isAgent = isset($isAgent) ? $isAgent : false;
$isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;

// ============================================
// 🔥 VALIDAR MES (formato: YYYY-MM)
// ============================================
if (!preg_match('/^(\d{4})-(\d{2})$/', $selectedMonth, $matches)) {
    $year = (int)date('Y');
    $month = (int)date('m');
} else {
    $year = (int)$matches[1];
    $month = (int)$matches[2];
}

// Validar que mes y año estén en rango razonable
if ($month < 1 || $month > 12) {
    $month = (int)date('m');
}
if ($year < 2000 || $year > 2100) {
    $year = (int)date('Y');
}

$selectedMonth = sprintf('%04d-%02d', $year, $month);

// ============================================
// 🔥 CALCULAR DÍAS DEL MES (SIN DateTime)
// ============================================
// Días en el mes usando cal_days_in_month
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

// 🔥 Día de la semana del primer día del mes
// Usamos mktime() en lugar de DateTime (más estable con zonas horarias)
$firstDayTimestamp = mktime(0, 0, 0, $month, 1, $year);
$firstDayOfMonth = (int)date('N', $firstDayTimestamp); // 1=Lun, 7=Dom

// Offset: celdas vacías antes del día 1 (calendario empieza en Lunes)
$firstDayOffset = $firstDayOfMonth - 1;

// Total de celdas y filas
$totalCells = $daysInMonth + $firstDayOffset;
$totalRows = (int)ceil($totalCells / 7);

// ============================================
// AGRUPAR EVENTOS POR FECHA
// ============================================
$eventsByDate = [];
foreach ($events as $event) {
    if (empty($event['date'])) {
        continue;
    }
    $dateKey = date('Y-m-d', strtotime($event['date']));
    if (!isset($eventsByDate[$dateKey])) {
        $eventsByDate[$dateKey] = [];
    }
    $eventsByDate[$dateKey][] = $event;
}

// ============================================
// EVENTOS DEL DÍA SELECCIONADO
// ============================================
$selectedDayEvents = isset($eventsByDate[$selectedDay]) ? $eventsByDate[$selectedDay] : [];

// ============================================
// 🔥 MESES DISPONIBLES (2026 → 2030)
// ============================================
$monthOptions = [];
$yearStart = 2026;
$yearEnd = 2030;

$mesesEspanol = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

for ($y = $yearStart; $y <= $yearEnd; $y++) {
    for ($m = 1; $m <= 12; $m++) {
        $value = sprintf('%04d-%02d', $y, $m);
        $label = $mesesEspanol[$m] . ' ' . $y;
        $monthOptions[$value] = $label;
    }
}

// Si el mes seleccionado no está en las opciones, agregarlo al inicio
if (!isset($monthOptions[$selectedMonth])) {
    $labelTmp = ($mesesEspanol[$month] ?? 'Mes') . ' ' . $year;
    $monthOptions = [$selectedMonth => $labelTmp] + $monthOptions;
}

// ============================================
// HELPERS
// ============================================
$getBadgeClass = function($type) {
    $badges = [
        'seguimiento' => 'info',
        'proximo_seguimiento' => 'danger',
        'cotizacion' => 'warning',
        'lead' => 'primary',
    ];
    return $badges[$type] ?? 'secondary';
};

$getTypeLabel = function($type) {
    $labels = [
        'seguimiento' => 'Seguimiento',
        'proximo_seguimiento' => 'Próximo Seguimiento',
        'cotizacion' => 'Cotización',
        'lead' => 'Lead Nuevo',
    ];
    return $labels[$type] ?? 'Evento';
};

$getStatusBadge = function($status) {
    $badges = [
        'pendiente' => 'warning',
        'programado' => 'info',
        'en progreso' => 'primary',
        'completado' => 'success',
        'cancelado' => 'danger',
        'nuevo' => 'info',
        'contactado' => 'primary',
        'cliente' => 'success',
        'aprobada' => 'success',
        'rechazada' => 'danger',
        'pagada' => 'primary',
    ];
    $statusLower = strtolower(trim($status));
    return $badges[$statusLower] ?? 'secondary';
};

$getIconClass = function($type) {
    $icons = [
        'seguimiento' => 'fa-phone',
        'proximo_seguimiento' => 'fa-clock',
        'cotizacion' => 'fa-file-invoice',
        'lead' => 'fa-user',
    ];
    return $icons[$type] ?? 'fa-calendar';
};

// ============================================
// ESTADÍSTICAS DEL DÍA
// ============================================
$totalActividadesDia = count($selectedDayEvents);
$completadosDia = 0;
foreach ($selectedDayEvents as $e) {
    $status = strtolower($e['status'] ?? '');
    if ($status == 'completado' || $status == 'completada') {
        $completadosDia++;
    }
}

// Nombre del mes seleccionado
$mesActualNombre = $mesesEspanol[$month] ?? date('F', $firstDayTimestamp);
?>

<div class="calendar-index">
    <!-- ============================================ -->
    <!-- HEADER -->
    <!-- ============================================ -->
    <div class="calendar-header">
        <div>
            <div class="breadcrumb-custom">
                <span>CRM</span><span class="separator">›</span>
                <span>Calendario</span><span class="separator">›</span>
                <span class="current"><?= Html::encode($this->title) ?></span>
            </div>
            <h1 class="page-title">
                <?= Html::encode($this->title) ?>
                <small><?= date('d/m/Y H:i') ?></small>
            </h1>
        </div>
        <div class="header-actions">
            <button class="btn btn-outline-secondary btn-sm" id="btn-today">
                <i class="fas fa-calendar-day"></i> Hoy
            </button>
            <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['dashboard/index'], ['class' => 'btn btn-secondary btn-sm']) ?>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- SELECTOR DE MES Y AÑO -->
    <!-- ============================================ -->
    <div class="card filtros-card mb-3">
        <div class="card-body">
            <div class="row align-items-end g-2">
                <div class="col-md-2">
                    <label class="form-label fw-bold mb-0">
                        <i class="fas fa-calendar-alt"></i> Mes y Año:
                    </label>
                </div>
                <div class="col-md-5">
                    <select class="form-select" id="month-selector">
                        <?php foreach ($monthOptions as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $selectedMonth == $value ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 text-end">
                    <span class="badge bg-info text-white">
                        <i class="fas fa-info-circle"></i> Rango: 2026 - 2030
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- CALENDARIO MENSUAL -->
    <!-- ============================================ -->
    <div class="card calendar-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-calendar-alt"></i> <?= $mesActualNombre ?> <?= $year ?>
            </h5>
            <div>
                <span class="badge bg-primary">
                    <i class="fas fa-calendar-day"></i> <?= date('d/m/Y', strtotime($selectedDay)) ?>
                </span>
                <span class="badge bg-success ms-1">
                    <i class="fas fa-tasks"></i> <?= $totalActividadesDia ?> actividades
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 calendar-table">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 14.28%;">Lun</th>
                            <th class="text-center" style="width: 14.28%;">Mar</th>
                            <th class="text-center" style="width: 14.28%;">Mié</th>
                            <th class="text-center" style="width: 14.28%;">Jue</th>
                            <th class="text-center" style="width: 14.28%;">Vie</th>
                            <th class="text-center" style="width: 14.28%;">Sáb</th>
                            <th class="text-center" style="width: 14.28%;">Dom</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($row = 0; $row < $totalRows; $row++): ?>
                            <tr>
                                <?php for ($col = 0; $col < 7; $col++): ?>
                                    <?php
                                    // 🔥 Fórmula: día = (fila * 7) + columna - offset + 1
                                    $dayNumber = ($row * 7) + $col - $firstDayOffset + 1;
                                    $isValidDay = ($dayNumber >= 1 && $dayNumber <= $daysInMonth);

                                    $dateKey = $isValidDay
                                        ? sprintf('%04d-%02d-%02d', $year, $month, $dayNumber)
                                        : null;

                                    $dayEvents = ($dateKey && isset($eventsByDate[$dateKey]))
                                        ? $eventsByDate[$dateKey]
                                        : [];

                                    $isToday = ($dateKey === date('Y-m-d'));
                                    $isSelected = ($dateKey === $selectedDay);
                                    ?>
                                    <td class="align-top p-1 day-cell <?= $isValidDay ? 'day-cell-hover' : 'day-cell-empty' ?> <?= $isSelected ? 'day-cell-selected' : '' ?>"
                                        style="cursor: <?= $isValidDay ? 'pointer' : 'default' ?>;
                                               <?= $isValidDay ? 'min-height: 100px;' : '' ?>"
                                        data-date="<?= $dateKey ?>"
                                        onclick="<?= $isValidDay ? "openDayModal('$dateKey')" : '' ?>">
                                        <?php if ($isValidDay): ?>
                                            <div class="d-flex justify-content-between align-items-start">
                                                <span class="day-number <?= $isToday ? 'today' : '' ?>">
                                                    <?= $dayNumber ?>
                                                </span>
                                                <?php if (count($dayEvents) > 0): ?>
                                                    <span class="badge bg-primary rounded-pill day-badge">
                                                        <?= count($dayEvents) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mt-1" style="max-height: 60px; overflow: hidden;">
                                                <?php foreach (array_slice($dayEvents, 0, 3) as $event): ?>
                                                    <div class="small event-marker"
                                                         style="border-left: 3px solid <?= $event['color'] ?? '#6c757d' ?>;
                                                                padding: 1px 4px;
                                                                margin-bottom: 1px;
                                                                font-size: 8px;
                                                                background: #f8f9fa;
                                                                border-radius: 2px;
                                                                white-space: nowrap;
                                                                overflow: hidden;
                                                                text-overflow: ellipsis;"
                                                         title="<?= Html::encode($event['title'] ?? '') ?> - <?= Html::encode($event['lead_name'] ?? '') ?>">
                                                        <i class="fas <?= $event['icon'] ?? 'fa-calendar' ?>"></i>
                                                        <?= Html::encode(substr($event['title'] ?? '', 0, 15)) ?>
                                                    </div>
                                                <?php endforeach; ?>
                                                <?php if (count($dayEvents) > 3): ?>
                                                    <div class="small text-muted text-center" style="font-size: 8px;">
                                                        +<?= count($dayEvents) - 3 ?> más
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                <?php endfor; ?>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MODAL FLOTANTE PARA DETALLE DEL DÍA          -->
<!-- ============================================ -->
<div class="modal fade" id="dayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-day"></i>
                    <span id="modalDayTitle"><?= date('d/m/Y', strtotime($selectedDay)) ?></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalDayBody">
                <!-- Contenido dinámico -->
            </div>
        </div>
    </div>
</div>

<script>
// ============================================
// SELECTOR DE MES
// ============================================
(function() {
    var btnGoToMonth = document.getElementById('btn-go-to-month');
    var monthSelector = document.getElementById('month-selector');
    var btnToday = document.getElementById('btn-today');

    if (btnGoToMonth) {
        btnGoToMonth.addEventListener('click', function() {
            var month = monthSelector.value;
            window.location.href = '?r=calendar/index&month=' + month;
        });
    }

    if (monthSelector) {
        monthSelector.addEventListener('change', function() {
            window.location.href = '?r=calendar/index&month=' + this.value;
        });
    }

    if (btnToday) {
        btnToday.addEventListener('click', function() {
            window.location.href = '?r=calendar/index';
        });
    }
})();

// ============================================
// MODAL FLOTANTE - DETALLE DEL DÍA
// ============================================
function openDayModal(day) {
    var modalElement = document.getElementById('dayModal');
    if (!modalElement) return;

    var modal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: true
    });

    var body = document.getElementById('modalDayBody');
    var title = document.getElementById('modalDayTitle');

    var parts = day.split('-');
    var dateObj = new Date(parts[0], parts[1] - 1, parts[2]);
    var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    title.textContent = dateObj.toLocaleDateString('es-ES', options);

    body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Cargando actividades...</p></div>';

    fetch('<?= Url::to(['calendar/day-detail']) ?>?day=' + day)
        .then(function(response) {
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.text();
        })
        .then(function(data) {
            body.innerHTML = data;
            console.log('✅ Actividades del día cargadas: ' + day);
        })
        .catch(function(error) {
            console.error('❌ Error:', error);
            body.innerHTML = '<div class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle fa-3x d-block mb-3"></i><p>Error al cargar las actividades</p><button class="btn btn-secondary btn-sm mt-2" onclick="openDayModal(\'' + day + '\')">Reintentar</button></div>';
        });

    modal.show();
}

window.openDayModal = openDayModal;

console.log('✅ Calendario inicializado correctamente');
</script>