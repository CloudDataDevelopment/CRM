<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$model = isset($model) ? $model : null;
$error = isset($error) ? $error : null;
$success = isset($success) ? $success : null;
$statusOptions = isset($statusOptions) ? $statusOptions : [];
$leadsList = isset($leadsList) ? $leadsList : [];

if ($error) {
    echo '<div class="edit-panel-content">
            <div class="edit-panel-header">
                <div class="header-title"><i class="fas fa-exclamation-triangle text-danger"></i> Error</div>
                <button type="button" class="btn-close-panel" onclick="cerrarPanel()"><i class="fas fa-times"></i></button>
            </div>
            <div class="edit-panel-body">
                <div class="edit-panel-message">
                    <i class="fas fa-exclamation-triangle text-danger"></i>
                    <p>' . $error . '</p>
                    <button class="btn-message btn-message-secondary" onclick="cerrarPanel()"><i class="fas fa-times"></i> Cerrar</button>
                </div>
            </div>
        </div>';
    return;
}

if (!$model) {
    echo '<div class="edit-panel-content">
            <div class="edit-panel-header">
                <div class="header-title"><i class="fas fa-inbox text-muted"></i> Evaluación no encontrada</div>
                <button type="button" class="btn-close-panel" onclick="cerrarPanel()"><i class="fas fa-times"></i></button>
            </div>
            <div class="edit-panel-body">
                <div class="edit-panel-message">
                    <i class="fas fa-inbox"></i>
                    <p>Evaluación no encontrada</p>
                    <button class="btn-message btn-message-secondary" onclick="cerrarPanel()"><i class="fas fa-times"></i> Cerrar</button>
                </div>
            </div>
        </div>';
    return;
}

// 🔥 Bloque reutilizable del formulario
$renderForm = function($model, $statusOptions, $leadsList, $success = null) {
    ob_start();
    ?>
    <div class="edit-form-container">
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 8px;">
                <i class="fas fa-check-circle"></i> <?= Html::encode($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php $form = ActiveForm::begin([
            'options' => [
                'id' => 'update-evaluacion-form',
                'onsubmit' => 'return false;',
            ],
            'action' => Url::to(['report/update-modal', 'id' => $model->id_report]),
            'method' => 'post',
        ]); ?>

        <?= $form->field($model, 'report_name')->textInput([
            'placeholder' => 'Nombre de la evaluación',
            'class' => 'form-control form-control-sm',
        ])->label('Nombre <span class="text-danger">*</span>') ?>

        <?= $form->field($model, 'report_type')->textInput([
            'placeholder' => 'Tipo de evaluación',
            'class' => 'form-control form-control-sm',
        ])->label('Tipo') ?>

        <?= $form->field($model, 'date_report')->input('date', [
            'class' => 'form-control form-control-sm',
        ])->label('Fecha') ?>

        <?= $form->field($model, 'id_status')->dropDownList(
            $statusOptions,
            ['prompt' => 'Seleccione un estado...', 'class' => 'form-select form-select-sm']
        )->label('Estado <span class="text-danger">*</span>') ?>

        <?= $form->field($model, 'id_lead')->dropDownList(
            $leadsList,
            ['prompt' => 'Seleccione un lead...', 'class' => 'form-select form-select-sm']
        )->label('Lead Asociado') ?>

        <?php ActiveForm::end(); ?>
    </div>
    <?php
    return ob_get_clean();
};
?>

<div class="edit-panel-content">

    <div class="edit-panel-header">
        <div class="header-title">
            <i class="fas fa-edit text-primary"></i> Editar Evaluación
        </div>
        <button type="button" class="btn-close-panel" onclick="cerrarPanel()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="edit-panel-body">
        <?= $renderForm($model, $statusOptions, $leadsList, $success ?? null) ?>
    </div>

    <div class="edit-panel-footer">
        <button class="btn-footer btn-footer-secondary" onclick="cerrarPanel()">
            <i class="fas fa-times"></i> Cancelar
        </button>
        <button class="btn-footer btn-footer-primary" onclick="submitForm()">
            <i class="fas fa-save"></i> Guardar
        </button>
    </div>

</div>

<script>
function submitForm() {
    var form = document.getElementById('update-evaluacion-form');
    if (!form) return;

    var formData = new FormData(form);
    var btn = document.querySelector('.btn-footer-primary');
    var originalText = btn ? btn.innerHTML : '';
    if (btn) {
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        btn.disabled = true;
    }

    fetch(form.getAttribute('action'), {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function (response) {
        if (!response.ok) throw new Error('HTTP ' + response.status);
        return response.text();
    })
    .then(function (data) {
        var panelContent = document.querySelector('.edit-panel-content');
        if (panelContent) {
            panelContent.outerHTML = data;
        } else {
            location.reload();
        }
    })
    .catch(function (error) {
        if (btn) {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
        alert('Error al guardar: ' + error.message);
    });
}

window.submitForm = submitForm;

// Auto-ocultar la alerta de éxito después de 3 segundos
setTimeout(function () {
    var alert = document.querySelector('.alert-success');
    if (alert) {
        alert.style.transition = 'opacity 0.5s ease';
        alert.style.opacity = '0';
        setTimeout(function () { alert.style.display = 'none'; }, 500);
    }
}, 3000);
</script>       