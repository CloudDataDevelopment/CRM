<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$this->title = 'Nueva Empresa';
$this->params['breadcrumbs'][] = ['label' => 'Empresas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Registrar CSS
$this->registerCssFile('@web/css/create-empresa.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
    'position' => \yii\web\View::POS_HEAD,
]);
?>

<div class="empresa-create">
    <!-- ============================================ -->
    <!-- CONTENEDOR BLANCO                            -->
    <!-- ============================================ -->
    <div class="create-content-wrapper">
        
        <!-- ============================================ -->
        <!-- HEADER CON BREADCRUMBS                       -->
        <!-- ============================================ -->
        <div class="empresas-header">
            <div>
                <!-- Ruta de navegación -->
                <div class="breadcrumb-custom">
                    <span>CRM</span>
                    <span class="separator">›</span>
                    <span>Administración</span>
                    <span class="separator">›</span>
                    <span>Empresas</span>
                    <span class="separator">›</span>
                    <span class="current">Nueva empresa</span>
                </div>
                <!-- Título principal -->
                <h1 class="page-title">
                    Nueva empresa
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= Html::submitButton('<i class="fas fa-save me-1"></i> Guardar', [
                    'class' => 'btn btn-success btn-sm',
                    'form' => 'empresa-form',
                ]) ?>
                
                <?= Html::resetButton('<i class="fas fa-undo me-1"></i> Limpiar', [
                    'class' => 'btn btn-outline-secondary btn-sm',
                    'form' => 'empresa-form',
                ]) ?>
                
                <?= Html::a('<i class="fas fa-arrow-left me-1"></i> Cancelar', ['index'], [
                    'class' => 'btn btn-secondary btn-sm'
                ]) ?>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- CONTENIDO PRINCIPAL                         -->
        <!-- ============================================ -->
        <div class="row">
            <div class="col-md-7">
                <!-- ============================================ -->
                <!-- FORMULARIO CON BORDE NEGRO                  -->
                <!-- ============================================ -->
                <div class="empresa-form-box">
                    <!-- TÍTULO DEL CUADRO (FRANJA) -->
                    <div class="empresa-form-title">
                        <i class="fas fa-building text-primary me-2"></i> Información de la Empresa
                    </div>
                    
                    <!-- CUERPO DEL FORMULARIO -->
                    <div class="empresa-form-body">
                        <?php $form = ActiveForm::begin([
                            'options' => ['class' => 'needs-validation', 'id' => 'empresa-form'],
                            'fieldConfig' => [
                                'options' => ['class' => 'form-group'],
                                'labelOptions' => ['class' => 'control-label'],
                                'errorOptions' => ['class' => 'help-block'],
                            ],
                        ]); ?>

                        <!-- NOMBRE Y DOMINIO -->
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'name')->textInput([
                                    'maxlength' => true,
                                    'placeholder' => 'Ej. Trivana',
                                    'class' => 'form-control',
                                ])->label('Nombre de empresa <span class="text-danger">*</span>') ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'domain')->textInput([
                                    'maxlength' => true,
                                    'placeholder' => 'Ej. trivana.com',
                                    'class' => 'form-control',
                                ])->label('Dominio <span class="text-danger">*</span>') ?>
                            </div>
                        </div>

                        <!-- TIPO DE EMPRESA -->
                        <?= $form->field($model, 'type')->dropDownList([
                            'Tecnología' => 'Tecnología',
                            'Marketing' => 'Marketing',
                            'Consultoría' => 'Consultoría',
                            'Finanzas' => 'Finanzas',
                            'Salud' => 'Salud',
                            'Educación' => 'Educación',
                            'Comercio' => 'Comercio',
                            'Otro' => 'Otro',
                        ], [
                            'prompt' => 'Seleccione un tipo',
                            'class' => 'form-select',
                        ])->label('Tipo de empresa <span class="text-danger">*</span>') ?>

                        <!-- DESCRIPCIÓN -->
                        <?= $form->field($model, 'description')->textarea([
                            'rows' => 4,
                            'placeholder' => 'Descripción de la empresa...',
                            'class' => 'form-control',
                            'style' => 'resize: vertical;',
                        ])->label('Descripción')
                        ->hint('Información general sobre la empresa', ['class' => 'text-muted']) ?>

                        <!-- ESTADO -->
                        <?php if (isset($statusList) && !empty($statusList)): ?>
                            <?= $form->field($model, 'id_status')->dropDownList(
                                $statusList,
                                [
                                    'prompt' => 'Seleccione un estado',
                                    'class' => 'form-select',
                                ]
                            )->label('Estado <span class="text-danger">*</span>') ?>
                        <?php endif; ?>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <!-- ============================================ -->
                <!-- MÓDULOS PARA ASIGNAR                        -->
                <!-- ============================================ -->
                <div class="modulos-card">
                    <!-- TÍTULO DEL CUADRO CON ENGRANAJE -->
                    <div class="modulos-card-title">
                        <i class="fas fa-cog text-primary me-2"></i> Módulos del CRM
                    </div>
                    
                    <div class="modulos-card-body">
                        <p class="text-muted small mb-3">
                            <i class="fas fa-info-circle"></i> 
                            Selecciona los módulos que estarán disponibles para esta empresa
                        </p>
                        
                        <div class="modulos-list">
                            <!-- Módulo 1: Gestión de contactos -->
                            <div class="modulo-item">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="modulo-contactos" checked>
                                    <label class="form-check-label" for="modulo-contactos">
                                        <i class="fas fa-address-book text-primary"></i>
                                        Gestión de contactos
                                    </label>
                                </div>
                                <small class="text-muted">Administra todos tus contactos y clientes</small>
                            </div>

                            <!-- Módulo 2: Leads -->
                            <div class="modulo-item">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="modulo-leads" checked>
                                    <label class="form-check-label" for="modulo-leads">
                                        <i class="fas fa-users text-success"></i>
                                        Leads
                                    </label>
                                </div>
                                <small class="text-muted">Gestiona tus leads y oportunidades de venta</small>
                            </div>

                            <!-- Módulo 3: Ventas -->
                            <div class="modulo-item">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="modulo-ventas" checked>
                                    <label class="form-check-label" for="modulo-ventas">
                                        <i class="fas fa-shopping-cart text-warning"></i>
                                        Ventas
                                    </label>
                                </div>
                                <small class="text-muted">Registro y seguimiento de ventas</small>
                            </div>

                            <!-- Módulo 4: Tareas -->
                            <div class="modulo-item">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="modulo-tareas" checked>
                                    <label class="form-check-label" for="modulo-tareas">
                                        <i class="fas fa-tasks text-info"></i>
                                        Tareas
                                    </label>
                                </div>
                                <small class="text-muted">Gestión de tareas y actividades</small>
                            </div>

                            <!-- Módulo 5: Calendario -->
                            <div class="modulo-item">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="modulo-calendario" checked>
                                    <label class="form-check-label" for="modulo-calendario">
                                        <i class="fas fa-calendar-alt text-danger"></i>
                                        Calendario
                                    </label>
                                </div>
                                <small class="text-muted">Calendario de eventos y citas</small>
                            </div>

                            <!-- Módulo 6: Reportes -->
                            <div class="modulo-item">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="modulo-reportes" checked>
                                    <label class="form-check-label" for="modulo-reportes">
                                        <i class="fas fa-chart-bar text-success"></i>
                                        Reportes
                                    </label>
                                </div>
                                <small class="text-muted">Análisis y reportes de gestión</small>
                            </div>

                            <!-- Módulo 7: Dashboard -->
                            <div class="modulo-item">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="modulo-dashboard" checked>
                                    <label class="form-check-label" for="modulo-dashboard">
                                        <i class="fas fa-tachometer-alt text-primary"></i>
                                        Dashboard
                                    </label>
                                </div>
                                <small class="text-muted">Panel de control con métricas clave</small>
                            </div>

                            <!-- Módulo 8: Marketing -->
                            <div class="modulo-item">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="modulo-marketing">
                                    <label class="form-check-label" for="modulo-marketing">
                                        <i class="fas fa-bullhorn text-info"></i>
                                        Marketing
                                    </label>
                                </div>
                                <small class="text-muted">Campañas y promociones</small>
                            </div>

                            <!-- Módulo 9: Cotizaciones -->
                            <div class="modulo-item">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="modulo-cotizaciones" checked>
                                    <label class="form-check-label" for="modulo-cotizaciones">
                                        <i class="fas fa-file-invoice text-warning"></i>
                                        Cotizaciones
                                    </label>
                                </div>
                                <small class="text-muted">Creación y gestión de cotizaciones</small>
                            </div>

                            <!-- Módulo 10: Seguimientos -->
                            <div class="modulo-item">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="modulo-seguimientos" checked>
                                    <label class="form-check-label" for="modulo-seguimientos">
                                        <i class="fas fa-phone text-success"></i>
                                        Seguimientos
                                    </label>
                                </div>
                                <small class="text-muted">Seguimiento de contactos y llamadas</small>
                            </div>
                        </div>
                    </div>
                    <div class="modulos-card-footer">
                        <span class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Los módulos pueden cambiarse después
                        </span>
                        <button type="button" class="btn-select-all" onclick="seleccionarTodos()">
                            <i class="fas fa-check-double"></i> Seleccionar todos
                        </button>
                    </div>
                </div>

                <!-- ============================================ -->
                <!-- CONSEJOS RÁPIDOS                            -->
                <!-- ============================================ -->
                <div class="tips-card mt-3">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-lightbulb text-warning me-2"></i>
                            Consejos Rápidos
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="tips-list">
                            <li>
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Nombre:</strong> Ingresa el nombre completo de la empresa.
                            </li>
                            <li>
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Dominio:</strong> El dominio web de la empresa.
                            </li>
                            <li>
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Tipo:</strong> Selecciona el tipo de negocio.
                            </li>
                            <li>
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Módulos:</strong> Activa los módulos necesarios.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para seleccionar todos los módulos -->
<script>
function seleccionarTodos() {
    const checkboxes = document.querySelectorAll('.modulo-item .form-check-input');
    const todosActivos = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(cb => {
        cb.checked = !todosActivos;
    });
}
</script>