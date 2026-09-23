<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Pagos de Cotización #' . $model->id_quote;
$this->params['breadcrumbs'][] = ['label' => 'Cotizaciones', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Cotización #' . $model->id_quote, 'url' => ['details', 'id' => $model->id_quote]];
$this->params['breadcrumbs'][] = 'Pagos';

$this->registerCssFile('@web/css/quote.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerCssFile('@web/css/dashboard.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

// ============================================
// DATOS CALCULADOS
// ============================================
$total = (int) $model->total_amount;
$enganche = (int) $model->down_payment;
$pagos = $model->getPayments();
$pagosTotal = $model->getPaymentsTotal();
$totalPagado = $model->getTotalPaid();
$pendiente = $model->getRealPending();
$porcentaje = $model->getPaymentPercentage();
$notas = $model->getNotes();

$lead = $model->lead;
$leadName = $lead ? $lead->name . ' ' . $lead->lastname : 'Sin lead';
$leadPhone = $lead ? $lead->phone : 'N/A';

$statusName = $model->getStatusName();
$badgeClass = $model->getStatusBadgeClass();

// Contar pagos totales (enganche + parciales)
$totalPagosCount = count($pagos) + ($enganche > 0 ? 1 : 0);
?>

<div class="quote-payments">
    <div class="container-fluid">

        <!-- ============================================ -->
        <!-- HEADER                                       -->
        <!-- ============================================ -->
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Ventas</span><span class="separator">›</span>
                    <span>Cotizaciones</span><span class="separator">›</span>
                    <span>Pagos</span>
                </div>
                <h1 class="page-title mb-0">
                    <i class="fas fa-money-bill-wave text-success me-2"></i>
                    Pagos de Cotización #<?= $model->id_quote ?>
                    <span class="badge bg-<?= $badgeClass ?> ms-2" style="font-size: 0.75rem;">
                        <?= Html::encode($statusName) ?>
                    </span>
                </h1>
                <small class="text-muted">
                    <i class="fas fa-user"></i> <?= Html::encode($leadName) ?>
                    <?php if ($leadPhone !== 'N/A'): ?>
                        &nbsp;|&nbsp;
                        <i class="fas fa-phone"></i> <?= Html::encode($leadPhone) ?>
                    <?php endif; ?>
                </small>
            </div>
            <div class="d-flex gap-2">
                <?= Html::a(
                    '<i class="fas fa-arrow-left"></i> Volver a Cotización',
                    ['details', 'id' => $model->id_quote],
                    ['class' => 'btn btn-secondary btn-sm']
                ) ?>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- RESUMEN FINANCIERO                           -->
        <!-- ============================================ -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <small class="d-block mb-1">Total Cotización</small>
                        <h3 class="mb-0">$<?= number_format($total, 0, '.', ',') ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <small class="d-block mb-1">Total Pagado</small>
                        <h3 class="mb-0">$<?= number_format($totalPagado, 0, '.', ',') ?></h3>
                        <small><?= $porcentaje ?>%</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card bg-<?= $pendiente > 0 ? 'warning' : 'success' ?> text-<?= $pendiente > 0 ? 'dark' : 'white' ?>">
                    <div class="card-body text-center">
                        <small class="d-block mb-1">Saldo Pendiente</small>
                        <h3 class="mb-0">$<?= number_format($pendiente, 0, '.', ',') ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <small class="d-block mb-1">Pagos Registrados</small>
                        <h3 class="mb-0"><?= $totalPagosCount ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- BARRA DE PROGRESO                            -->
        <!-- ============================================ -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <strong><i class="fas fa-chart-line"></i> Progreso de Pago</strong>
                    <strong><?= $porcentaje ?>%</strong>
                </div>
                <div class="progress" style="height: 25px;">
                    <div class="progress-bar bg-success" style="width: <?= $porcentaje ?>%;">
                        $<?= number_format($totalPagado, 0, '.', ',') ?> / $<?= number_format($total, 0, '.', ',') ?>
                    </div>
                </div>
                <?php if ($pendiente <= 0 && $total > 0): ?>
                    <div class="alert alert-success text-center mt-3 mb-0">
                        <i class="fas fa-trophy"></i> 
                        <strong>¡Cotización completamente pagada!</strong>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- HISTORIAL DE PAGOS                           -->
        <!-- ============================================ -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-history"></i> Historial de Pagos
                </h5>
                <?php if ($pendiente > 0): ?>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#paymentModal">
                        <i class="fas fa-plus"></i> Registrar Pago
                    </button>
                <?php else: ?>
                    <span class="badge bg-success">✅ Cotización Pagada</span>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="40">#</th>
                                <th>Fecha</th>
                                <th>Concepto</th>
                                <th>Método</th>
                                <th>Referencia</th>
                                <th class="text-end">Monto</th>
                                <th>Registrado por</th>
                                <th class="text-center" width="80">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- 🔥 ENGANCHE INICIAL -->
                            <?php if ($enganche > 0): ?>
                                <tr class="table-info">
                                    <td>—</td>
                                    <td><?= date('d/m/Y', strtotime($model->date_quote)) ?></td>
                                    <td>
                                        <strong>Enganche Inicial</strong>
                                        <small class="text-muted d-block">Pago al crear cotización</small>
                                    </td>
                                    <td>—</td>
                                    <td>—</td>
                                    <td class="text-end">
                                        <strong>$<?= number_format($enganche, 0, '.', ',') ?></strong>
                                    </td>
                                    <td><?= Html::encode($model->getAgentName()) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-info">Inicial</span>
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <!-- 🔥 PAGOS PARCIALES -->
                            <?php foreach ($pagos as $index => $pago): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= date('d/m/Y', strtotime($pago['fecha'] ?? 'now')) ?></td>
                                    <td>
                                        Pago parcial
                                        <?php if (!empty($pago['comentarios'])): ?>
                                            <small class="text-muted d-block"><?= Html::encode($pago['comentarios']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($pago['metodo'])): ?>
                                            <span class="badge bg-secondary"><?= Html::encode($pago['metodo']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($pago['referencia'])): ?>
                                            <code><?= Html::encode($pago['referencia']) ?></code>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success">$<?= number_format($pago['monto'] ?? 0, 0, '.', ',') ?></strong>
                                    </td>
                                    <td>
                                        <?= Html::encode($pago['usuario_nombre'] ?? 'N/A') ?>
                                        <?php if (!empty($pago['registrado'])): ?>
                                            <small class="text-muted d-block">
                                                <?= date('d/m/Y H:i', strtotime($pago['registrado'])) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?= Html::a(
                                            '<i class="fas fa-trash"></i>',
                                            ['remove-payment', 'id' => $model->id_quote, 'index' => $index],
                                            [
                                                'class' => 'btn btn-sm btn-danger',
                                                'title' => 'Eliminar pago',
                                                'data' => [
                                                    'confirm' => '¿Eliminar este pago? El saldo pendiente se recalculará.',
                                                    'method' => 'post',
                                                ],
                                            ]
                                        ) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <!-- SIN PAGOS -->
                            <?php if (empty($pagos) && $enganche == 0): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <i class="fas fa-inbox fa-3x d-block mb-3 text-muted"></i>
                                        <p class="mb-0">No hay pagos registrados aún.</p>
                                        <?php if ($pendiente > 0): ?>
                                            <small>Haz clic en <strong>"Registrar Pago"</strong> para agregar el primero.</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="5" class="text-end">TOTAL PAGADO:</th>
                                <th class="text-end text-success">$<?= number_format($totalPagado, 0, '.', ',') ?></th>
                                <th colspan="2"></th>
                            </tr>
                            <tr>
                                <th colspan="5" class="text-end">SALDO PENDIENTE:</th>
                                <th class="text-end <?= $pendiente > 0 ? 'text-danger' : 'text-success' ?>">
                                    $<?= number_format($pendiente, 0, '.', ',') ?>
                                </th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        <?= $totalPagosCount ?> pago(s) registrado(s) en total
                    </small>
                    <?php if ($pendiente > 0): ?>
                        <small class="text-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            Aún hay un saldo pendiente de $<?= number_format($pendiente, 0, '.', ',') ?>
                        </small>
                    <?php else: ?>
                        <small class="text-success">
                            <i class="fas fa-check-circle"></i>
                            Cotización pagada en su totalidad
                        </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- NOTAS DE LA COTIZACIÓN                       -->
        <!-- ============================================ -->
        <?php if (!empty($notas)): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-sticky-note"></i> Notas de la Cotización</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0"><?= nl2br(Html::encode($notas)) ?></p>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- ============================================ -->
<!-- MODAL REGISTRAR PAGO                         -->
<!-- ============================================ -->
<?php if ($pendiente > 0): ?>
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="paymentModalLabel">
                    <i class="fas fa-money-bill-wave"></i> Registrar Pago
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="payment-form">
                <div class="modal-body">

                    <!-- ALERTA DE SALDO -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Saldo pendiente:</strong> 
                        <span class="fw-bold text-danger">$<?= number_format($pendiente, 0, '.', ',') ?></span>
                    </div>

                    <!-- MONTO -->
                    <div class="mb-3">
                        <label class="form-label">
                            Monto a Pagar <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" 
                                   class="form-control" 
                                   name="monto" 
                                   min="1" 
                                   max="<?= $pendiente ?>" 
                                   value="<?= $pendiente ?>" 
                                   required
                                   autocomplete="off">
                        </div>
                        <small class="text-muted">
                            Máximo: $<?= number_format($pendiente, 0, '.', ',') ?>
                        </small>
                    </div>

                    <!-- FECHA -->
                    <div class="mb-3">
                        <label class="form-label">
                            Fecha de Pago <span class="text-danger">*</span>
                        </label>
                        <input type="date" 
                               class="form-control" 
                               name="fecha" 
                               value="<?= date('Y-m-d') ?>" 
                               max="<?= date('Y-m-d') ?>"
                               required>
                    </div>

                    <!-- MÉTODO -->
                    <div class="mb-3">
                        <label class="form-label">Método de Pago</label>
                        <select class="form-select" name="metodo">
                            <option value="Efectivo">💵 Efectivo</option>
                            <option value="Transferencia" selected>🏦 Transferencia</option>
                            <option value="Tarjeta">💳 Tarjeta</option>
                            <option value="Cheque">📝 Cheque</option>
                            <option value="Depósito">🏧 Depósito</option>
                            <option value="Otro">📌 Otro</option>
                        </select>
                    </div>

                    <!-- REFERENCIA -->
                    <div class="mb-3">
                        <label class="form-label">Referencia / Número de Operación</label>
                        <input type="text" 
                               class="form-control" 
                               name="referencia" 
                               placeholder="Ej: REF-12345, folio, etc."
                               maxlength="100">
                    </div>

                    <!-- COMENTARIOS -->
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" 
                                  name="comentarios" 
                                  rows="2" 
                                  placeholder="Notas adicionales sobre este pago..."
                                  maxlength="255"></textarea>
                    </div>

                    <!-- ALERTA DE RESULTADO -->
                    <div id="payment-alert"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success" id="btn-submit-payment">
                        <i class="fas fa-save"></i> Registrar Pago
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('payment-form').addEventListener('submit', function(e) {
    e.preventDefault();

    var form = this;
    var btn = document.getElementById('btn-submit-payment');
    var alertBox = document.getElementById('payment-alert');
    var originalText = btn.innerHTML;

    // Validaciones previas
    var monto = parseInt(form.querySelector('[name="monto"]').value) || 0;
    var maxPendiente = <?= (int) $pendiente ?>;

    if (monto <= 0) {
        alertBox.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> El monto debe ser mayor a 0.</div>';
        return;
    }

    if (monto > maxPendiente) {
        alertBox.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> El monto no puede exceder el pendiente de $' + maxPendiente.toLocaleString('es-MX') + '.</div>';
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    alertBox.innerHTML = '';

    var formData = new FormData(form);

    fetch('<?= Url::to(['quote/add-payment', 'id' => $model->id_quote]) ?>', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            var html = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' + data.message + '</div>';
            
            if (data.completado) {
                html += '<div class="alert alert-info"><i class="fas fa-trophy"></i> ¡Cotización completamente pagada!</div>';
            }
            
            alertBox.innerHTML = html;
            
            setTimeout(function() {
                location.reload();
            }, 1500);
        } else {
            alertBox.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> ' + data.message + '</div>';
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(function(error) {
        alertBox.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Error de conexión. Intenta de nuevo.</div>';
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
});

// 🔥 Auto-focus en el campo monto al abrir el modal
document.getElementById('paymentModal').addEventListener('shown.bs.modal', function() {
    this.querySelector('[name="monto"]').focus();
    this.querySelector('[name="monto"]').select();
});
</script>
<?php endif; ?>