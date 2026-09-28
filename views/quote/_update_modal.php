<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$model = isset($model) ? $model : null;
$error = isset($error) ? $error : null;
$success = isset($success) ? $success : null;
$leadsList = isset($leadsList) ? $leadsList : [];
$statusOptions = isset($statusOptions) ? $statusOptions : [];
$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;

$cssPath = Yii::getAlias('@webroot/css/quote-edit-modal.css');
$cssVersion = file_exists($cssPath) ? filemtime($cssPath) : time();
$this->registerCssFile('@web/css/quote-edit-modal.css?v=' . $cssVersion, [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
    'position' => \yii\web\View::POS_HEAD,
]);

if ($error && !$model) {
    echo '<div class="edit-panel-content">
            <div class="edit-panel-header">
                <div class="header-title"><i class="fas fa-exclamation-triangle text-danger"></i> Error</div>
                <button type="button" class="btn-close-panel" data-panel-close><i class="fas fa-times"></i></button>
            </div>
            <div class="edit-panel-body">
                <div class="edit-panel-message">
                    <i class="fas fa-exclamation-triangle text-danger"></i>
                    <p>' . Html::encode($error) . '</p>
                    <button class="btn-message btn-message-secondary" data-panel-close><i class="fas fa-times"></i> Cerrar</button>
                </div>
            </div>
        </div>';
    return;
}

if (!$model) {
    echo '<div class="edit-panel-content">
            <div class="edit-panel-header">
                <div class="header-title"><i class="fas fa-inbox text-muted"></i> Cotización no encontrada</div>
                <button type="button" class="btn-close-panel" data-panel-close><i class="fas fa-times"></i></button>
            </div>
            <div class="edit-panel-body">
                <div class="edit-panel-message">
                    <i class="fas fa-inbox text-muted"></i>
                    <p>Cotización no encontrada</p>
                    <button class="btn-message btn-message-secondary" data-panel-close><i class="fas fa-times"></i> Cerrar</button>
                </div>
            </div>
        </div>';
    return;
}

// 🔥 Datos del lead para mostrar en modo solo lectura
$lead = $model->lead;
$leadName = $lead ? $lead->name . ' ' . $lead->lastname : 'Lead no disponible';
$leadPhone = $lead ? $lead->phone : '';
$leadStatus = $lead ? $lead->getStatusName() : '';
$leadBadgeClass = $lead ? $lead->getStatusBadgeClass() : 'secondary';
?>

<!-- 🔥 Estilos para flechas de selects + tarjeta de lead bloqueado -->
<style>
    /* Flecha en los selects del panel */
    .edit-panel-content .form-select,
    .edit-panel-content select.form-control {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e") !important;
        background-repeat: no-repeat !important;
        background-position: right 0.75rem center !important;
        background-size: 16px 12px !important;
        padding-right: 2.25rem !important;
        border: 1px solid #ced4da;
        border-radius: 6px;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .edit-panel-content .form-select:focus {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    /* 🔥 Tarjeta de lead bloqueado */
    .lead-locked-card {
        background: linear-gradient(135deg, #f8f9fc 0%, #eef2ff 100%);
        border: 1px solid #d1d9f0;
        border-left: 4px solid #4e73df;
        border-radius: 8px;
        padding: 12px 14px;
        margin-bottom: 16px;
    }

    .lead-locked-card .lead-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 6px;
    }

    .lead-locked-card .lead-name {
        font-weight: 700;
        color: #2c3e50;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .lead-locked-card .lead-name i {
        color: #4e73df;
    }

    .lead-locked-card .lead-info {
        font-size: 0.75rem;
        color: #6c757d;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 4px;
    }

    .lead-locked-card .lead-info span {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .lead-locked-card .lock-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #fff3cd;
        color: #856404;
        font-size: 0.65rem;
        padding: 3px 8px;
        border-radius: 12px;
        font-weight: 600;
    }
</style>

<div class="edit-panel-content" id="edit-panel-content">

    <div class="edit-panel-header">
        <div class="header-title"><i class="fas fa-edit text-primary"></i> Editar Cotización</div>
        <button type="button" class="btn-close-panel" data-panel-close><i class="fas fa-times"></i></button>
    </div>

    <div class="edit-panel-body">
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="position: sticky; top: 0; z-index: 10; border-radius: 8px;">
                <i class="fas fa-check-circle"></i> <strong>¡Éxito!</strong> <?= Html::encode($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 8px;">
                <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php $form = ActiveForm::begin([
            'options' => ['class' => 'needs-validation', 'id' => 'update-quote-form'],
            'action' => Url::to(['quote/update-modal', 'id' => $model->id_quote]),
            'method' => 'post',
            'fieldConfig' => [
                'options' => ['class' => 'form-group'],
                'labelOptions' => ['class' => 'control-label'],
                'errorOptions' => ['class' => 'help-block'],
            ],
        ]); ?>

        <!-- 🔥 LEAD BLOQUEADO (solo lectura) -->
        <div class="form-group">
            <label class="control-label">Lead</label>

            <div class="lead-locked-card">
                <div class="lead-header">
                    <span class="lead-name">
                        <i class="fas fa-user-circle"></i>
                        <?= Html::encode($leadName) ?>
                    </span>
                </div>

                <?php if ($lead): ?>
                    <div class="lead-info">
                        <?php if ($leadPhone): ?>
                            <span>
                                <i class="fas fa-phone"></i>
                                <?= Html::encode($leadPhone) ?>
                            </span>
                        <?php endif; ?>

                        <?php if ($leadStatus): ?>
                            <span>
                                <i class="fas fa-tag"></i>
                                <span class="badge bg-<?= $leadBadgeClass ?>" style="font-size: 0.65rem;">
                                    <?= Html::encode($leadStatus) ?>
                                </span>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <small class="text-muted d-block mt-2" style="font-size: 0.7rem;">
                    <i class="fas fa-info-circle"></i>
                    El lead no puede modificarse desde aquí.
                </small>
            </div>

            <!-- 🔥 Campo oculto para que el POST siga enviando el id_lead -->
            <?= Html::activeHiddenInput($model, 'id_lead', ['value' => $model->id_lead]) ?>
        </div>

        <!-- MONTO TOTAL -->
        <?= $form->field($model, 'total_amount')->textInput([
            'type' => 'number',
            'step' => '1',
            'class' => 'form-control',
            'placeholder' => 'Ej: 1500000',
            'id' => 'quote-total_amount'
        ])->label('Monto Total <span class="text-danger">*</span>') ?>

        <!-- ESTADO (dropdown con flecha) -->
        <?= $form->field($model, 'id_status')->dropDownList(
            $statusOptions,
            [
                'prompt' => 'Seleccione un estado...',
                'class' => 'form-select',
                'id' => 'quote-id_status'
            ]
        )->label('Estado <span class="text-danger">*</span>') ?>

        <!-- OBSERVACIONES -->
        <?= $form->field($model, 'comments')->textarea([
            'rows' => 3,
            'class' => 'form-control',
            'placeholder' => 'Observaciones adicionales...',
            'id' => 'quote-comments',
            'value' => $model->getNotes()
        ])->label('Observaciones') ?>

        <?php ActiveForm::end(); ?>
    </div>

    <div class="edit-panel-footer">
        <button type="button" class="btn-footer btn-footer-secondary" data-panel-close><i class="fas fa-times"></i> Cancelar</button>
        <button type="button" class="btn-footer btn-footer-primary" id="btn-save-quote"><i class="fas fa-save"></i> Guardar</button>
    </div>
</div>

<script>
(function() {
    function bindSaveButton() {
        var btn = document.getElementById('btn-save-quote');
        if (!btn) return;

        var newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);

        newBtn.addEventListener('click', function(e) {
            e.preventDefault();

            var form = document.getElementById('update-quote-form');
            if (!form) return;

            var originalHTML = newBtn.innerHTML;
            newBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            newBtn.disabled = true;

            fetch(form.getAttribute('action'), {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.text(); })
            .then(function(data) {
                var container = document.getElementById('edit-panel-content');
                var temp = document.createElement('div');
                temp.innerHTML = data;
                var newContent = temp.querySelector('.edit-panel-content');

                if (container && newContent) {
                    var datosActualizados = null;
                    var form2 = newContent.querySelector('#update-quote-form');

                    if (form2) {
                        var totalEl = newContent.querySelector('#quote-total_amount');
                        var statusEl = newContent.querySelector('#quote-id_status');
                        var idMatch = form2.getAttribute('action').match(/id=(\d+)/);

                        datosActualizados = {
                            id_quote: idMatch ? idMatch[1] : null,
                            total_amount: totalEl ? totalEl.value : '',
                            status_name: statusEl ? (statusEl.options[statusEl.selectedIndex] ? statusEl.options[statusEl.selectedIndex].text : '') : ''
                        };
                    }

                    container.parentNode.replaceChild(newContent, container);

                    newContent.querySelectorAll('script').forEach(function(s) {
                        var ns = document.createElement('script');
                        ns.textContent = s.textContent;
                        document.body.appendChild(ns);
                    });

                    if (datosActualizados && typeof window.onQuoteUpdated === 'function') {
                        window.onQuoteUpdated(datosActualizados);
                    }

                    setTimeout(function() {
                        var a = document.querySelector('.alert-success');
                        if (a) {
                            a.style.transition = 'opacity .5s';
                            a.style.opacity = '0';
                            setTimeout(function() { if (a) a.style.display = 'none'; }, 500);
                        }
                    }, 3000);

                    bindSaveButton();
                } else {
                    location.reload();
                }
            })
            .catch(function(e) {
                newBtn.innerHTML = originalHTML;
                newBtn.disabled = false;
                alert('Error al guardar: ' + e.message);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindSaveButton);
    } else {
        bindSaveButton();
    }
})();
</script>