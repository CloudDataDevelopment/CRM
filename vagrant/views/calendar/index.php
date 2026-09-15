<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Calendario';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/calendar.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
]);

// Agrupar eventos por fecha
$eventsByDate = [];
foreach ($events as $event) {
    $dateKey = date('Y-m-d', strtotime($event['date']));
    if (!isset($eventsByDate[$dateKey])) {
        $eventsByDate[$dateKey] = [];
    }
    $eventsByDate[$dateKey][] = $event;
}

// Obtener días del mes seleccionado
$selectedDate = \DateTime::createFromFormat('Y-m', $selectedMonth);
$daysInMonth = $selectedDate->format('t');
$firstDayOfMonth = (int)$selectedDate->format('N');
?>

<div class="calendar-index">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>
            <i class="fas fa-calendar-alt"></i> 
            <?= Html::encode($this->title) ?>
        </h1>
    </div>

    <!-- ============================================ -->
    <!-- SELECTOR DE MESES                            -->
    <!-- ============================================ -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <label class="form-label fw-bold mb-0">Seleccionar Mes:</label>
                </div>
                <div class="col-md-6">
                    <select class="form-select" id="month-selector">
                        <?php foreach ($months as $month): ?>
                            <option value="<?= $month['value'] ?>" <?= $selectedMonth == $month['value'] ? 'selected' : '' ?>>
                                <?= $month['label'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-primary" id="btn-go-to-month">
                        <i class="fas fa-arrow-right"></i> Ir al mes
                    </button>
                    <button class="btn btn-info" id="btn-today">
                        <i class="fas fa-calendar-day"></i> Hoy
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- CALENDARIO MENSUAL                           -->
    <!-- ============================================ -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0 text-center">
                <?= ucfirst($selectedDate->format('F')) ?> <?= $selectedDate->format('Y') ?>
            </h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0" style="table-layout: fixed;">
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
                        <?php
                        $firstDayOffset = ($firstDayOfMonth == 7) ? 0 : $firstDayOfMonth;
                        $totalDays = $daysInMonth + $firstDayOffset;
                        $totalRows = ceil($totalDays / 7);
                        
                        for ($row = 0; $row < $totalRows; $row++): ?>
                            <tr style="height: 120px;">
                                <?php for ($col = 0; $col < 7; $col++): ?>
                                    <?php
                                    $dayNumber = ($row * 7) + $col - $firstDayOffset + 1;
                                    $isValidDay = ($dayNumber >= 1 && $dayNumber <= $daysInMonth);
                                    $dateKey = $isValidDay ? $selectedMonth . '-' . str_pad($dayNumber, 2, '0', STR_PAD_LEFT) : null;
                                    $dayEvents = ($dateKey && isset($eventsByDate[$dateKey])) ? $eventsByDate[$dateKey] : [];
                                    $isToday = ($dateKey == date('Y-m-d'));
                                    ?>
                                    <td class="align-top p-1 <?= $isValidDay ? 'day-cell' : 'bg-light' ?>" 
                                        style="cursor: <?= $isValidDay ? 'pointer' : 'default' ?>; <?= $isValidDay ? 'min-height: 120px;' : '' ?>"
                                        data-date="<?= $dateKey ?>">
                                        <?php if ($isValidDay): ?>
                                            <div class="d-flex justify-content-between align-items-start">
                                                <span class="fw-bold <?= $isToday ? 'text-danger bg-danger bg-opacity-10 px-2 rounded' : '' ?>">
                                                    <?= $dayNumber ?>
                                                </span>
                                                <?php if (count($dayEvents) > 0): ?>
                                                    <span class="badge bg-primary rounded-pill"><?= count($dayEvents) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mt-1" style="max-height: 80px; overflow-y: auto;">
                                                <?php foreach ($dayEvents as $event): ?>
                                                    <div class="event-item" 
                                                         style="border-left: 3px solid <?= $event['color'] ?>; 
                                                                padding: 2px 5px; 
                                                                margin-bottom: 2px; 
                                                                font-size: 10px; 
                                                                background: #f8f9fa; 
                                                                border-radius: 2px;
                                                                cursor: pointer;"
                                                         data-event-id="<?= $event['id'] ?>"
                                                         data-event-title="<?= Html::encode($event['title']) ?>"
                                                         data-event-type="<?= $event['type'] ?>"
                                                         data-event-status="<?= $event['status'] ?>"
                                                         data-event-lead="<?= Html::encode($event['lead_name']) ?>"
                                                         data-event-phone="<?= $event['lead_phone'] ?>"
                                                         data-event-url="<?= Url::to($event['url']) ?>"
                                                         <?php if (isset($event['total_amount'])): ?>
                                                             data-event-amount="<?= $event['total_amount'] ?>"
                                                         <?php endif; ?>
                                                         onclick="showEventDetail(this)">
                                                        <i class="fas <?= $event['icon'] ?>"></i>
                                                        <?= Html::encode(substr($event['title'], 0, 20)) ?>
                                                        <?php if (strlen($event['title']) > 20): ?>...<?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted"><?= $dayNumber ?></span>
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

    <!-- ============================================ -->
    <!-- LEYENDA                                      -->
    <!-- ============================================ -->
    <div class="card mt-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong><i class="fas fa-phone"></i> Seguimientos:</strong>
                    <span class="badge bg-info">Contactado</span>
                    <span class="badge bg-warning">Interesado</span>
                    <span class="badge bg-primary">Cotización</span>
                    <span class="badge bg-secondary">Revisión</span>
                    <span class="badge bg-dark">Negociación</span>
                    <span class="badge bg-success">Cliente</span>
                    <span class="badge bg-danger">Perdido</span>
                </div>
                <div class="col-md-3">
                    <strong><i class="fas fa-clock"></i> Próximos:</strong>
                    <span class="badge bg-danger">Próximo Seguimiento</span>
                </div>
                <div class="col-md-3">
                    <strong><i class="fas fa-file-invoice"></i> Cotizaciones:</strong>
                    <span class="badge bg-warning">Pendiente</span>
                    <span class="badge bg-success">Aprobada</span>
                    <span class="badge bg-danger">Rechazada</span>
                    <span class="badge bg-primary">Pagada</span>
                    <span class="badge bg-secondary">Cancelada</span>
                </div>
                <div class="col-md-3">
                    <strong><i class="fas fa-users"></i> Leads:</strong>
                    <span class="badge bg-info">Nuevo</span>
                    <span class="badge bg-primary">Contactado</span>
                    <span class="badge bg-warning">Interesado</span>
                    <span class="badge bg-success">Cliente</span>
                    <span class="badge bg-danger">Perdido</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MODAL PARA DETALLE DEL EVENTO                -->
<!-- ============================================ -->
<div class="modal fade" id="eventDetailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventDetailTitle">
                    <i class="fas fa-info-circle"></i> Detalle del Evento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="eventDetailBody">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <a href="#" id="eventDetailLink" class="btn btn-primary" target="_blank">
                    <i class="fas fa-eye"></i> Ver Detalle
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- SCRIPTS                                      -->
<!-- ============================================ -->
<?php
$js = <<<JS
// Selector de mes - Ir al mes
document.getElementById('btn-go-to-month').addEventListener('click', function() {
    var month = document.getElementById('month-selector').value;
    window.location.href = '?r=calendar/index&month=' + month;
});

// Selector de mes - Auto-redirección al cambiar
document.getElementById('month-selector').addEventListener('change', function() {
    var month = this.value;
    window.location.href = '?r=calendar/index&month=' + month;
});

// Botón Hoy
document.getElementById('btn-today').addEventListener('click', function() {
    window.location.href = '?r=calendar/index';
});

// Mostrar detalle del evento
function showEventDetail(element) {
    var title = element.getAttribute('data-event-title');
    var type = element.getAttribute('data-event-type');
    var status = element.getAttribute('data-event-status');
    var lead = element.getAttribute('data-event-lead');
    var phone = element.getAttribute('data-event-phone');
    var url = element.getAttribute('data-event-url');
    var amount = element.getAttribute('data-event-amount');
    
    var typeLabels = {
        'seguimiento': 'Seguimiento',
        'proximo_seguimiento': 'Próximo Seguimiento',
        'cotizacion': 'Cotización',
        'lead': 'Lead Nuevo'
    };
    
    var typeIcons = {
        'seguimiento': 'fa-phone',
        'proximo_seguimiento': 'fa-clock',
        'cotizacion': 'fa-file-invoice',
        'lead': 'fa-user'
    };
    
    // 🔥 Obtener el label usando el tipo, si no existe usar 'Evento'
    var label = typeLabels[type] || 'Evento';
    var icon = typeIcons[type] || 'fa-calendar';
    
    var body = `
        <div class="row">
            <div class="col-12">
                <p><strong><i class="fas fa-tag"></i> Tipo:</strong> 
                    <span class="badge bg-info">\${label}</span>
                </p>
                <p><strong><i class="fas fa-user"></i> Lead:</strong> \${lead}</p>
                <p><strong><i class="fas fa-phone"></i> Teléfono:</strong> \${phone}</p>
                <p><strong><i class="fas fa-info-circle"></i> Estado:</strong> 
                    <span class="badge bg-secondary">\${status}</span>
                </p>
                \${amount ? `<p><strong><i class="fas fa-money-bill-wave"></i> Monto:</strong> $\${new Intl.NumberFormat('es-ES').format(amount)}</p>` : ''}
                <p><strong><i class="fas fa-align-left"></i> Descripción:</strong> \${title}</p>
            </div>
        </div>
    `;
    
    document.getElementById('eventDetailTitle').innerHTML = 
        '<i class="fas ' + icon + '"></i> ' + label + ' - ' + lead;
    document.getElementById('eventDetailBody').innerHTML = body;
    
    var link = document.getElementById('eventDetailLink');
    if (url) {
        link.href = url;
        link.style.display = 'inline-block';
    } else {
        link.style.display = 'none';
    }
    
    var modal = new bootstrap.Modal(document.getElementById('eventDetailModal'));
    modal.show();
}
JS;
$this->registerJs($js);
?>