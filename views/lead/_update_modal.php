<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$model = isset($model) ? $model : null;
$error = isset($error) ? $error : null;
$success = isset($success) ? $success : null;
$statusList = isset($statusList) ? $statusList : [];
$estadosPermitidos = isset($estadosPermitidos) ? $estadosPermitidos : [];
$returnUrl = isset($returnUrl) ? $returnUrl : 'index';

$cssPath = Yii::getAlias('@webroot/css/lead-edit-modal.css');
$cssVersion = file_exists($cssPath) ? filemtime($cssPath) : time();
$this->registerCssFile('@web/css/lead-edit-modal.css?v=' . $cssVersion, [
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
                <div class="header-title"><i class="fas fa-inbox text-muted"></i> Lead no encontrado</div>
                <button type="button" class="btn-close-panel" data-panel-close><i class="fas fa-times"></i></button>
            </div>
            <div class="edit-panel-body">
                <div class="edit-panel-message">
                    <i class="fas fa-inbox text-muted"></i>
                    <p>Lead no encontrado</p>
                    <button class="btn-message btn-message-secondary" data-panel-close><i class="fas fa-times"></i> Cerrar</button>
                </div>
            </div>
        </div>';
    return;
}
?>

<div class="edit-panel-content" id="edit-panel-content">

    <div class="edit-panel-header">
        <div class="header-title"><i class="fas fa-edit text-primary"></i> Editar Lead</div>
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
            'options' => ['class' => 'needs-validation', 'id' => 'update-lead-form'],
            'action' => Url::to(['lead/update', 'id' => $model->id_lead, 'modal' => 1]),
            'method' => 'post',
            'fieldConfig' => [
                'options' => ['class' => 'form-group'],
                'labelOptions' => ['class' => 'control-label'],
                'errorOptions' => ['class' => 'help-block'],
            ],
        ]); ?>

        <?= $form->field($model, 'name')->textInput(['class' => 'form-control form-control-sm', 'id' => 'lead-name'])->label('Nombre <span class="text-danger">*</span>') ?>
        <?= $form->field($model, 'lastname')->textInput(['class' => 'form-control form-control-sm', 'id' => 'lead-lastname'])->label('Apellido') ?>
        <?= $form->field($model, 'phone')->textInput(['class' => 'form-control form-control-sm', 'id' => 'lead-phone'])->label('Teléfono <span class="text-danger">*</span>') ?>
        <?= $form->field($model, 'id_status')->dropDownList($statusList, ['prompt' => 'Seleccione un estado...', 'class' => 'form-select form-select-sm', 'id' => 'lead-id_status'])->label('Estado <span class="text-danger">*</span>') ?>
        <?= $form->field($model, 'comments')->textarea(['rows' => 3, 'class' => 'form-control form-control-sm', 'id' => 'lead-comments', 'placeholder' => 'Observaciones...'])->label('Observaciones') ?>

        <?php ActiveForm::end(); ?>
    </div>

    <div class="edit-panel-footer">
        <button type="button" class="btn-footer btn-footer-secondary" data-panel-close><i class="fas fa-times"></i> Cancelar</button>
        <button type="button" class="btn-footer btn-footer-primary" id="btn-save-lead"><i class="fas fa-save"></i> Guardar</button>
    </div>
</div>

<script>
// ============================================
// 🔥 SUBMIT FORM - Auto-ejecutable
// Solo se encarga del guardado, el cierre lo maneja el listener global del padre
// ============================================
(function() {
    function bindSaveButton() {
        var btn = document.getElementById('btn-save-lead');
        if (!btn) return;

        // Clonar para eliminar listeners previos
        var newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);

        newBtn.addEventListener('click', function(e) {
            e.preventDefault();

            var form = document.getElementById('update-lead-form');
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
                    // Capturar datos antes de reemplazar
                    var datosActualizados = null;
                    var form2 = newContent.querySelector('#update-lead-form');

                    if (form2) {
                        var nameEl = newContent.querySelector('#lead-name');
                        var lastnameEl = newContent.querySelector('#lead-lastname');
                        var phoneEl = newContent.querySelector('#lead-phone');
                        var statusEl = newContent.querySelector('#lead-id_status');
                        var commentsEl = newContent.querySelector('#lead-comments');
                        var idMatch = form2.getAttribute('action').match(/id=(\d+)/);

                        datosActualizados = {
                            id_lead: idMatch ? idMatch[1] : null,
                            name: (nameEl ? nameEl.value : '') + ' ' + (lastnameEl ? lastnameEl.value : ''),
                            phone: phoneEl ? phoneEl.value : '',
                            status_name: statusEl ? (statusEl.options[statusEl.selectedIndex] ? statusEl.options[statusEl.selectedIndex].text : '') : '',
                            comments: commentsEl ? commentsEl.value : ''
                        };
                    }

                    container.parentNode.replaceChild(newContent, container);

                    // Ejecutar scripts embebidos
                    newContent.querySelectorAll('script').forEach(function(s) {
                        var ns = document.createElement('script');
                        ns.textContent = s.textContent;
                        document.body.appendChild(ns);
                    });

                    // Notificar al padre para actualización en tiempo real
                    if (datosActualizados && typeof window.onLeadUpdated === 'function') {
                        window.onLeadUpdated(datosActualizados);
                    }

                    // Auto-ocultar alerta
                    setTimeout(function() {
                        var a = document.querySelector('.alert-success');
                        if (a) {
                            a.style.transition = 'opacity .5s';
                            a.style.opacity = '0';
                            setTimeout(function() { if (a) a.style.display = 'none'; }, 500);
                        }
                    }, 3000);

                    // Re-bind
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