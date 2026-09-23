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

// 🔥 Registrar CSS del modal de edición con caché busting
$cssPath = Yii::getAlias('@webroot/css/quote-edit-modal.css');
$cssVersion = file_exists($cssPath) ? filemtime($cssPath) : time();

$this->registerCssFile('@web/css/quote-edit-modal.css?v=' . $cssVersion, [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
    'position' => \yii\web\View::POS_HEAD,
]);

// Si hay error, mostrar mensaje
if ($error) {
    echo '<div class="edit-panel-content">
            <div class="edit-panel-header">
                <div class="header-title">
                    <i class="fas fa-exclamation-triangle text-danger"></i> Error
                </div>
                <button type="button" class="btn-close-panel" onclick="closePanel()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="edit-panel-body">
                <div class="edit-panel-message">
                    <i class="fas fa-exclamation-triangle text-danger"></i>
                    <p>' . Html::encode($error) . '</p>
                    <button class="btn-message btn-message-secondary" onclick="closePanel()">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>';
    return;
}

if (!$model) {
    echo '<div class="edit-panel-content">
            <div class="edit-panel-header">
                <div class="header-title">
                    <i class="fas fa-inbox text-muted"></i> Cotización no encontrada
                </div>
                <button type="button" class="btn-close-panel" onclick="closePanel()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="edit-panel-body">
                <div class="edit-panel-message">
                    <i class="fas fa-inbox text-muted"></i>
                    <p>Cotización no encontrada</p>
                    <button class="btn-message btn-message-secondary" onclick="closePanel()">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>';
    return;
}

// 🔥 Si hay éxito, mostrar mensaje flotante y mantener el formulario
if ($success) {
    ob_start();
    ?>
    <div class="edit-panel-content">
        <!-- HEADER -->
        <div class="edit-panel-header">
            <div class="header-title">
                <i class="fas fa-edit text-primary"></i> Editar Cotización
            </div>
            <button type="button" class="btn-close-panel" onclick="closePanel()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- CUERPO -->
        <div class="edit-panel-body" id="edit-panel-body">
            <!-- 🔥 Mensaje de éxito flotante -->
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="position: sticky; top: 0; z-index: 10; border-radius: 8px;">
                <i class="fas fa-check-circle"></i> 
                <strong>¡Éxito!</strong> <?= Html::encode($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            
            <!-- 🔥 Formulario con los campos actualizados -->
            <div class="edit-form-container">
                <?php $form = ActiveForm::begin([
                    'options' => [
                        'class' => 'needs-validation', 
                        'id' => 'update-quote-form',
                        'onsubmit' => 'return false;',
                    ],
                    'action' => Url::to(['quote/update-modal', 'id' => $model->id_quote]),
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

                <?= $form->field($model, 'total_amount')->textInput([
                    'type' => 'number',
                    'step' => '1',
                    'class' => 'form-control form-control-sm',
                    'placeholder' => 'Ej: 1500000'
                ])->label('Monto Total <span class="text-danger">*</span>') ?>

                <?= $form->field($model, 'down_payment')->textInput([
                    'type' => 'number',
                    'step' => '1',
                    'class' => 'form-control form-control-sm',
                    'placeholder' => 'Ej: 500000'
                ])->label('Pago Inicial') ?>

                <?= $form->field($model, 'id_status')->dropDownList(
                    $statusOptions,
                    ['prompt' => 'Seleccione un estado...', 'class' => 'form-select form-select-sm']
                )->label('Estado <span class="text-danger">*</span>') ?>

                <?= $form->field($model, 'comments')->textarea([
                    'rows' => 3,
                    'class' => 'form-control form-control-sm',
                    'placeholder' => 'Observaciones adicionales...',
                    'value' => $model->getNotes(),
                ])->label('Observaciones') ?>

                <?php ActiveForm::end(); ?>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="edit-panel-footer">
            <button class="btn-footer btn-footer-secondary" onclick="closePanel()">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button class="btn-footer btn-footer-primary" onclick="submitForm()">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
    </div>
    
    <script>
        // 🔥 Ocultar el mensaje de éxito automáticamente después de 3 segundos
        setTimeout(function() {
            var alert = document.querySelector('.alert-success');
            if (alert) {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(function() {
                    if (alert) {
                        alert.style.display = 'none';
                    }
                }, 500);
            }
        }, 3000);

        // 🔥 Función para enviar el formulario vía AJAX
        function submitForm() {
            var form = document.getElementById('update-quote-form');
            if (!form) {
                console.error('❌ Formulario no encontrado');
                return;
            }
            
            var formData = new FormData(form);
            
            var btn = document.getElementById('submit-update');
            if (btn) {
                var originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
                btn.disabled = true;
            }
            
            var actionUrl = form.getAttribute('action');
            
            fetch(actionUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Error en la respuesta: ' + response.status);
                }
                return response.text();
            })
            .then(function(data) {
                var panelContent = document.querySelector('.edit-panel-content');
                if (panelContent) {
                    panelContent.outerHTML = data;
                    console.log('✅ Formulario actualizado correctamente');
                } else {
                    location.reload();
                }
            })
            .catch(function(error) {
                console.error('❌ Error:', error);
                if (btn) {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
                alert('Error al guardar: ' + error.message);
            });
        }

        window.submitForm = submitForm;

        // Prevenir envío normal del formulario y usar AJAX
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('update-quote-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitForm();
                    return false;
                });
            }
        });
    </script>
    <?php
    $output = ob_get_clean();
    echo $output;
    return;
}
?>

<!-- 🔥 FORMULARIO NORMAL (SIN ÉXITO) -->
<div class="edit-panel-content">

    <!-- HEADER -->
    <div class="edit-panel-header">
        <div class="header-title">
            <i class="fas fa-edit text-primary"></i> Editar Cotización
        </div>
        <button type="button" class="btn-close-panel" onclick="closePanel()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- CUERPO -->
    <div class="edit-panel-body">
        <?php $form = ActiveForm::begin([
            'options' => [
                'class' => 'needs-validation', 
                'id' => 'update-quote-form',
                'onsubmit' => 'return false;',
            ],
            'action' => Url::to(['quote/update-modal', 'id' => $model->id_quote]),
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

        <?= $form->field($model, 'total_amount')->textInput([
            'type' => 'number',
            'step' => '1',
            'class' => 'form-control form-control-sm',
            'placeholder' => 'Ej: 1500000'
        ])->label('Monto Total <span class="text-danger">*</span>') ?>

        <?= $form->field($model, 'down_payment')->textInput([
            'type' => 'number',
            'step' => '1',
            'class' => 'form-control form-control-sm',
            'placeholder' => 'Ej: 500000'
        ])->label('Pago Inicial') ?>

        <?= $form->field($model, 'id_status')->dropDownList(
            $statusOptions,
            ['prompt' => 'Seleccione un estado...', 'class' => 'form-select form-select-sm']
        )->label('Estado <span class="text-danger">*</span>') ?>

        <?= $form->field($model, 'comments')->textarea([
            'rows' => 3,
            'class' => 'form-control form-control-sm',
            'placeholder' => 'Observaciones adicionales...',
            'value' => $model->getNotes(),
        ])->label('Observaciones') ?>

        <div class="form-group mt-3">
            <?= Html::submitButton('<i class="fas fa-save"></i> Actualizar', [
                'class' => 'btn-submit',
                'form' => 'update-quote-form',
                'id' => 'submit-update',
                'onclick' => 'submitForm(); return false;',
            ]) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

    <!-- FOOTER -->
    <div class="edit-panel-footer">
        <button class="btn-footer btn-footer-secondary" onclick="closePanel()">
            <i class="fas fa-times"></i> Cancelar
        </button>
        <button class="btn-footer btn-footer-primary" onclick="submitForm()">
            <i class="fas fa-save"></i> Guardar
        </button>
    </div>
</div>

<script>
// 🔥 Función para cerrar el panel
function closePanel() {
    if (window.parent && typeof window.parent.closePanel === 'function') {
        window.parent.closePanel();
    } else if (window.opener && typeof window.opener.closePanel === 'function') {
        window.opener.closePanel();
    }
}

// 🔥 Función para enviar el formulario vía AJAX
function submitForm() {
    var form = document.getElementById('update-quote-form');
    if (!form) {
        console.error('❌ Formulario no encontrado');
        return;
    }
    
    var formData = new FormData(form);
    
    var btn = document.getElementById('submit-update');
    if (btn) {
        var originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        btn.disabled = true;
    }
    
    var actionUrl = form.getAttribute('action');
    
    fetch(actionUrl, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function(response) {
        if (!response.ok) {
            throw new Error('Error en la respuesta: ' + response.status);
        }
        return response.text();
    })
    .then(function(data) {
        var panelContent = document.querySelector('.edit-panel-content');
        if (panelContent) {
            panelContent.outerHTML = data;
            console.log('✅ Formulario actualizado correctamente');
        } else {
            location.reload();
        }
    })
    .catch(function(error) {
        console.error('❌ Error:', error);
        if (btn) {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
        alert('Error al guardar: ' + error.message);
    });
}

window.submitForm = submitForm;
window.closePanel = closePanel;

// Prevenir envío normal del formulario y usar AJAX
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('update-quote-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm();
            return false;
        });
    }
});

// Cerrar panel con Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (typeof closePanel === 'function') {
            closePanel();
        }
    }
});
</script>