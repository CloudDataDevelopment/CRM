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

$this->registerCssFile('@web/css/sales-tracking-edit-modal.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
    'position' => \yii\web\View::POS_HEAD,
]);

// ============================================
// ERROR
// ============================================
if ($error && !$model) {
    echo '<div class="edit-panel-content">
            <div class="edit-panel-header">
                <div class="header-title">
                    <i class="fas fa-exclamation-triangle text-danger"></i> Error
                </div>
                <button type="button" class="btn-close-panel" onclick="cerrarPanelSeguimiento()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="edit-panel-body">
                <div class="edit-panel-message">
                    <i class="fas fa-exclamation-triangle text-danger"></i>
                    <p>' . Html::encode($error) . '</p>
                    <button class="btn-message btn-message-secondary" onclick="cerrarPanelSeguimiento()">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>';
    return;
}

// ============================================
// SIN MODELO
// ============================================
if (!$model) {
    echo '<div class="edit-panel-content">
            <div class="edit-panel-header">
                <div class="header-title">
                    <i class="fas fa-inbox text-muted"></i> Seguimiento no encontrado
                </div>
                <button type="button" class="btn-close-panel" onclick="cerrarPanelSeguimiento()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="edit-panel-body">
                <div class="edit-panel-message">
                    <i class="fas fa-inbox text-muted"></i>
                    <p>Seguimiento no encontrado</p>
                    <button class="btn-message btn-message-secondary" onclick="cerrarPanelSeguimiento()">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>';
    return;
}
?>

<div class="edit-panel-content" id="edit-panel-content">

    <!-- HEADER -->
    <div class="edit-panel-header">
        <div class="header-title">
            <i class="fas fa-edit text-primary"></i> Editar Seguimiento
        </div>
        <button type="button" class="btn-close-panel" onclick="cerrarPanelSeguimiento()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- BODY -->
    <div class="edit-panel-body">

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="position: sticky; top: 0; z-index: 10; border-radius: 8px; font-size: 0.8rem;">
                <i class="fas fa-check-circle"></i>
                <strong>¡Éxito!</strong> <?= Html::encode($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 8px; font-size: 0.8rem;">
                <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php $form = ActiveForm::begin([
            'options' => [
                'class' => 'needs-validation',
                'id' => 'update-tracking-form',
                'onsubmit' => 'return false;',
            ],
            'action' => Url::to(['sales-tracking/update-modal', 'id' => $model->id_sales_tracking]),
            'method' => 'post',
            'fieldConfig' => [
                'options' => ['class' => 'form-group'],
                'labelOptions' => ['class' => 'control-label'],
                'errorOptions' => ['class' => 'help-block'],
            ],
        ]); ?>

        <?= $form->field($model, 'id_lead')->dropDownList(
            $leadsList,
            ['prompt' => 'Seleccione un lead...', 'class' => 'form-select form-select-sm']
        )->label('Lead <span class="text-danger">*</span>') ?>

        <?= $form->field($model, 'id_status')->dropDownList(
            $statusOptions,
            ['prompt' => 'Seleccione un estado...', 'class' => 'form-select form-select-sm']
        )->label('Estado <span class="text-danger">*</span>') ?>

        <?= $form->field($model, 'comments')->textarea([
            'rows' => 3,
            'class' => 'form-control form-control-sm',
            'placeholder' => 'Comentarios del seguimiento...'
        ]) ?>

        <?= $form->field($model, 'date_f')->input('date', [
            'class' => 'form-control form-control-sm'
        ])->label('Próximo Seguimiento') ?>

        <?php ActiveForm::end(); ?>

    </div>

    <!-- FOOTER -->
    <div class="edit-panel-footer">
        <button type="button" class="btn-footer btn-footer-secondary" onclick="cerrarPanelSeguimiento()">
            <i class="fas fa-times"></i> Cancelar
        </button>
        <button type="button" class="btn-footer btn-footer-primary" id="btn-save-tracking">
            <i class="fas fa-save"></i> Guardar
        </button>
    </div>

</div>

<script>
// ============================================
// 🔥 FUNCIÓN DE CIERRE - Busca closePanel en varios lugares
// ============================================
function cerrarPanelSeguimiento() {
    console.log('🔴 Cerrando panel de seguimiento...');

    // 1️⃣ Intentar con closePanel global
    if (typeof window.closePanel === 'function') {
        console.log('✅ Usando window.closePanel');
        window.closePanel();
        return;
    }

    // 2️⃣ Intentar con closePanel en el padre (iframe)
    if (window.parent && typeof window.parent.closePanel === 'function') {
        console.log('✅ Usando window.parent.closePanel');
        window.parent.closePanel();
        return;
    }

    // 3️⃣ Fallback manual: ocultar el panel y restaurar tabla
    console.log('⚠️ closePanel no encontrado, aplicando fallback manual');
    fallbackCerrarPanel();
}

function fallbackCerrarPanel() {
    var panel = document.getElementById('panelWrapper');
    var tableWrapper = document.getElementById('tableWrapper');

    if (panel) {
        panel.classList.remove('visible');
        panel.classList.add('closing');
        panel.style.display = 'none';
    }
    if (tableWrapper) {
        tableWrapper.classList.remove('with-panel');
    }

    document.querySelectorAll('.tracking-row').forEach(function(row) {
        row.classList.remove('tracking-row-selected');
    });

    setTimeout(function() {
        if (panel) {
            panel.classList.remove('closing');
            var contenido = document.getElementById('slidePanelContent');
            if (contenido) contenido.innerHTML = '';
        }
    }, 300);
}

// Exponer globalmente
window.cerrarPanelSeguimiento = cerrarPanelSeguimiento;

// ============================================
// SUBMIT FORM
// ============================================
(function() {
    function bindSaveButton() {
        var btn = document.getElementById('btn-save-tracking');
        if (!btn) return;

        var newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);

        newBtn.addEventListener('click', function(e) {
            e.preventDefault();

            var form = document.getElementById('update-tracking-form');
            if (!form) return;

            var originalHTML = newBtn.innerHTML;
            newBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            newBtn.disabled = true;

            fetch(form.getAttribute('action'), {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.text();
            })
            .then(function(data) {
                var container = document.getElementById('edit-panel-content');
                var temp = document.createElement('div');
                temp.innerHTML = data;
                var newContent = temp.querySelector('.edit-panel-content');

                if (container && newContent) {
                    var datosActualizados = null;
                    var form2 = newContent.querySelector('#update-tracking-form');

                    if (form2) {
                        var statusEl = newContent.querySelector('#salestracking-id_status');
                        var commentsEl = newContent.querySelector('#salestracking-comments');
                        var idMatch = form2.getAttribute('action').match(/id=(\d+)/);

                        var statusName = '';
                        if (statusEl && statusEl.options[statusEl.selectedIndex]) {
                            statusName = statusEl.options[statusEl.selectedIndex].text;
                        }

                        datosActualizados = {
                            id: idMatch ? idMatch[1] : null,
                            status_name: statusName,
                            comments: commentsEl ? commentsEl.value : ''
                        };
                    }

                    container.parentNode.replaceChild(newContent, container);

                    newContent.querySelectorAll('script').forEach(function(s) {
                        var ns = document.createElement('script');
                        ns.textContent = s.textContent;
                        document.body.appendChild(ns);
                    });

                    if (datosActualizados && typeof window.onTrackingUpdated === 'function') {
                        window.onTrackingUpdated(datosActualizados);
                    }

                    setTimeout(function() {
                        var a = document.querySelector('.alert-success');
                        if (a) {
                            a.style.transition = 'opacity .5s';
                            a.style.opacity = '0';
                            setTimeout(function() {
                                if (a) a.style.display = 'none';
                            }, 500);
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
                console.error('❌ Error:', e);
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