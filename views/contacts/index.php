<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\StringHelper;
use yii\widgets\LinkPager;

$this->title = 'Contactos';
$this->params['breadcrumbs'][] = $this->title;

// Registrar CSS
$this->registerCssFile('@web/css/contacts.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerCssFile('@web/css/dashboard.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerCssFile('@web/css/contact-panel.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class], 'position' => \yii\web\View::POS_HEAD]);
$this->registerCssFile('@web/css/contact-edit-modal.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class], 'position' => \yii\web\View::POS_HEAD]);

// Registrar JS (solo jQuery)
$this->registerJsFile('https://code.jquery.com/3.6.0/jquery.min.js', ['position' => \yii\web\View::POS_HEAD]);

// Variables del controlador
$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isAgent = isset($isAgent) ? $isAgent : false;
$isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;
$contacts = isset($contacts) ? $contacts : [];
$dataProvider = isset($dataProvider) ? $dataProvider : null;
$search = isset($search) ? $search : '';
$status = isset($status) ? $status : '';
$type = isset($type) ? $type : '';
$statusList = isset($statusList) ? $statusList : [];
$typeList = isset($typeList) ? $typeList : [];
$totalContacts = isset($totalContacts) ? $totalContacts : 0;
$activeContacts = isset($activeContacts) ? $activeContacts : 0;
$inactiveContacts = isset($inactiveContacts) ? $inactiveContacts : 0;

// Variables de métricas
$metricas = [
    ['class' => 'primary', 'icon' => 'address-book', 'label' => 'Total Contactos', 'value' => $totalContacts],
    ['class' => 'success', 'icon' => 'user-check', 'label' => 'Activos', 'value' => $activeContacts],
    ['class' => 'secondary', 'icon' => 'user-slash', 'label' => 'Inactivos', 'value' => $inactiveContacts],
];
?>

<div class="contacts-index">
        
    <!-- HEADER -->
    <div class="contacts-header">
        <div>
            <div class="breadcrumb-custom">
                <span>CRM</span><span class="separator">›</span>
                <span>Contactos</span><span class="separator">›</span>
                <span class="current"><?= Html::encode($this->title) ?></span>
            </div>
            <h1 class="page-title"><?= Html::encode($this->title) ?> <small><?= date('d/m/Y H:i') ?></small></h1>
        </div>
        <div class="header-actions">
            <?php if ($isAdmin || $isSuperAdmin): ?>
                <?= Html::a('<i class="fas fa-tags"></i> Tipos', ['types'], ['class' => 'btn btn-outline-info btn-sm btn-header-action']) ?>
            <?php endif; ?>
            <?= Html::a('<i class="fas fa-plus"></i> Nuevo Contacto', ['create'], ['class' => 'btn btn-primary btn-sm btn-header-action']) ?>
        </div>
    </div>

    <!-- MÉTRICAS -->
    <div class="row g-2 mb-2">
        <?php foreach ($metricas as $metrica): ?>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card stat-card stat-card-<?= $metrica['class'] ?> dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2">
                            <i class="fas fa-<?= $metrica['icon'] ?>"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $metrica['value'] ?></div>
                            <div class="stat-label"><?= $metrica['label'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- FILTROS -->
    <div class="card filtros-card mb-3">
        <div class="card-body">
            <form method="get" action="<?= Url::to(['contacts/index']) ?>" id="form-filtros">
                <div class="row align-items-end">
                    <div class="col-md-1"><label class="form-label fw-bold mb-0">Filtrar</label></div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="Buscar..." value="<?= Html::encode($search) ?>" id="search-input">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="status" id="status-select">
                            <option value="">Todos los estados</option>
                            <?php foreach ($statusList as $id => $nombre): ?>
                                <option value="<?= $nombre ?>" <?= $status == $nombre ? 'selected' : '' ?>><?= ucfirst($nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="type" id="type-select">
                            <option value="">Todos los tipos</option>
                            <?php foreach ($typeList as $id => $nombre): ?>
                                <option value="<?= $nombre ?>" <?= $type == $nombre ? 'selected' : '' ?>><?= ucfirst($nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100" id="btn-filtrar"><i class="fas fa-search"></i></button>
                    </div>
                    <div class="col-md-1">
                        <a href="<?= Url::to(['contacts/index']) ?>" class="btn btn-secondary w-100" title="Limpiar"><i class="fas fa-undo"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- CONTENEDOR PRINCIPAL: TABLA + PANEL -->
    <div class="panel-container">
        <!-- Tabla -->
        <div class="table-wrapper" id="tableWrapper">
            <div class="card table-card">
                <div class="card-header">
                    <div class="header-left">
                        <i class="fas fa-list"></i>
                        <span>Listado de Contactos</span>
                        <span class="badge bg-primary ms-2"><?= $dataProvider ? $dataProvider->getTotalCount() : 0 ?></span>
                    </div>
                    <div class="header-right">
                        <span class="badge bg-secondary">
                            Página <?= $dataProvider ? $dataProvider->getPagination()->getPage() + 1 : 1 ?> de <?= $dataProvider ? $dataProvider->getPagination()->getPageCount() : 1 ?>
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="contacts-table">
                            <thead class="table-light">
                                <tr>
                                    <th><?= $dataProvider ? $dataProvider->getSort()->link('name') : 'Nombre' ?></th>
                                    <th>Teléfono</th>
                                    <th>Email</th>
                                    <th>Tipo</th>
                                    <th><?= $dataProvider ? $dataProvider->getSort()->link('id_status') : 'Estado' ?></th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($contacts)): ?>
                                    <?php foreach ($contacts as $contact): ?>
                                        <tr class="contact-row" data-id="<?= $contact->id_contact ?>">
                                            <td><strong><?= Html::encode($contact->getFullName()) ?></strong></td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span><?= $contact->phone ?></span>
                                                    <?php if ($contact->phone): ?>
                                                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $contact->phone) ?>" target="_blank" class="text-success" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($contact->email): ?>
                                                    <a href="mailto:<?= Html::encode($contact->email) ?>" class="text-primary">
                                                        <i class="fas fa-envelope"></i> <?= Html::encode($contact->email) ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge bg-info"><?= Html::encode($contact->getTypeContactName()) ?></span></td>
                                            <td>
                                                <span class="badge bg-<?= $contact->getStatusBadgeClass() ?>">
                                                    <i class="fas <?= $contact->getStatusIcon() ?>"></i>
                                                    <?= Html::encode($contact->getStatusName()) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <button class="btn btn-info btn-sm btn-action view-contact-btn" data-id="<?= $contact->id_contact ?>" title="Ver">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if ($isAdmin || $isSuperAdmin): ?>
                                                        <button class="btn btn-primary btn-sm btn-action edit-contact-btn" 
                                                                data-id="<?= $contact->id_contact ?>" 
                                                                title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $contact->id_contact], [
                                                            'class' => 'btn btn-danger btn-sm btn-action',
                                                            'title' => 'Eliminar',
                                                            'data' => [
                                                                'confirm' => '¿Eliminar este contacto?',
                                                                'method' => 'post',
                                                            ],
                                                        ]) ?>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                            <?= $isAgent ? 'No tienes contactos disponibles' : 'No hay contactos registrados' ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <small class="text-muted">
                                Mostrando <?= $dataProvider ? $dataProvider->getCount() : 0 ?> de <?= $dataProvider ? $dataProvider->getTotalCount() : 0 ?> contactos
                            </small>
                        </div>
                        <div class="col-md-6">
                            <?php if ($dataProvider): ?>
                                <?= LinkPager::widget([
                                    'pagination' => $dataProvider->getPagination(),
                                    'options' => ['class' => 'pagination justify-content-end'],
                                    'linkOptions' => ['class' => 'page-link'],
                                    'prevPageLabel' => 'Anterior',
                                    'nextPageLabel' => 'Siguiente',
                                    'maxButtonCount' => 5,
                                    'hideOnSinglePage' => true,
                                ]) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Panel Lateral -->
        <div class="panel-wrapper" id="panelWrapper">
            <div class="card slide-panel-card">
                <div class="card-body p-0" id="slidePanelContent"></div>
            </div>
        </div>
    </div>
</div>

<?php
$viewModalUrl = Url::to(['contacts/view-modal']);
$updateModalUrl = Url::to(['contacts/update-modal']);
?>

<script>
// ============================================
// ESPERAR A QUE JQUERY ESTÉ CARGADO
// ============================================
(function() {
    if (typeof jQuery === 'undefined') {
        console.error('❌ jQuery no está cargado. Intentando cargar...');
        var script = document.createElement('script');
        script.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
        script.onload = function() {
            console.log('✅ jQuery cargado manualmente');
            inicializar();
        };
        document.head.appendChild(script);
    } else {
        console.log('✅ jQuery ya está cargado');
        inicializar();
    }
})();

function inicializar() {
    var $ = jQuery;
    
    console.log('🚀 Inicializando panel lateral...');
    
    // ============================================
    // DELEGACIÓN DE EVENTOS
    // ============================================
    $(document).on('click', '.view-contact-btn', function(e) {
        e.stopPropagation();
        var contactId = $(this).data('id');
        if (contactId) openPanel(contactId);
    });

    $(document).on('click', '.contact-row', function(e) {
        if ($(e.target).closest('a, button').length > 0) return;
        var contactId = $(this).data('id');
        if (contactId) openPanel(contactId);
    });

    // ============================================
    // EDITAR CONTACTO EN PANEL LATERAL
    // ============================================
    $(document).on('click', '.edit-contact-btn', function(e) {
        e.stopPropagation();
        var contactId = $(this).data('id');
        if (contactId) {
            if (isOpen) {
                closePanel(function() {
                    setTimeout(function() {
                        openEditPanel(contactId);
                    }, 300);
                });
            } else {
                openEditPanel(contactId);
            }
        }
    });

    // ============================================
    // FILTROS AUTOMÁTICOS
    // ============================================
    document.getElementById('search-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') document.getElementById('form-filtros').submit();
    });
    document.getElementById('status-select').addEventListener('change', function() {
        document.getElementById('form-filtros').submit();
    });
    document.getElementById('type-select').addEventListener('change', function() {
        document.getElementById('form-filtros').submit();
    });
    document.getElementById('btn-filtrar').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('form-filtros').submit();
    });

    // ============================================
    // PANEL LATERAL
    // ============================================
    var currentContactId = null;
    var viewModalUrl = '<?= $viewModalUrl ?>';
    var updateModalUrl = '<?= $updateModalUrl ?>';
    var isOpen = false;

    function openPanel(contactId) {
        if (contactId === currentContactId && isOpen) {
            closePanel();
            return;
        }
        
        if (isOpen) {
            closePanel(function() {
                setTimeout(function() {
                    openPanel(contactId);
                }, 300);
            });
            return;
        }
        
        currentContactId = contactId;
        isOpen = true;
        
        var panel = document.getElementById('panelWrapper');
        var panelContent = document.getElementById('slidePanelContent');
        var tableWrapper = document.getElementById('tableWrapper');
        var filaSeleccionada = document.querySelector('.contact-row[data-id="' + contactId + '"]');
        
        if (!panel || !tableWrapper || !panelContent) {
            console.error('❌ Elementos no encontrados');
            return;
        }
        
        document.querySelectorAll('.contact-row').forEach(function(row) {
            row.classList.remove('contact-row-selected');
        });
        if (filaSeleccionada) {
            filaSeleccionada.classList.add('contact-row-selected');
        }
        
        tableWrapper.classList.add('with-panel');
        
        panel.style.display = 'block';
        panel.classList.add('visible');
        
        panelContent.innerHTML = '<div class="text-center text-muted py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-3">Cargando...</p></div>';
        
        fetch(viewModalUrl + '?id=' + contactId)
            .then(function(response) {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.text();
            })
            .then(function(data) {
                panelContent.innerHTML = data;
                console.log('✅ Contenido cargado');
            })
            .catch(function(error) {
                console.error('❌ Error:', error);
                panelContent.innerHTML = '<div class="text-center text-danger py-5"><i class="fas fa-exclamation-triangle fa-3x"></i><p>Error al cargar</p><button class="btn btn-secondary btn-sm mt-3" onclick="closePanel()">Cerrar</button></div>';
            });
    }

    function openEditPanel(contactId) {
        currentContactId = contactId;
        isOpen = true;
        
        var panel = document.getElementById('panelWrapper');
        var panelContent = document.getElementById('slidePanelContent');
        var tableWrapper = document.getElementById('tableWrapper');
        var filaSeleccionada = document.querySelector('.contact-row[data-id="' + contactId + '"]');
        
        if (!panel || !tableWrapper || !panelContent) {
            console.error('❌ Elementos no encontrados');
            return;
        }
        
        document.querySelectorAll('.contact-row').forEach(function(row) {
            row.classList.remove('contact-row-selected');
        });
        if (filaSeleccionada) {
            filaSeleccionada.classList.add('contact-row-selected');
        }
        
        tableWrapper.classList.add('with-panel');
        
        panel.style.display = 'block';
        panel.classList.add('visible');
        
        panelContent.innerHTML = '<div class="text-center text-muted py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-3">Cargando formulario de edición...</p></div>';
        
        fetch(updateModalUrl + '?id=' + contactId)
            .then(function(response) {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.text();
            })
            .then(function(data) {
                panelContent.innerHTML = data;
                console.log('✅ Formulario de edición cargado');
                
                var scripts = panelContent.getElementsByTagName('script');
                for (var i = 0; i < scripts.length; i++) {
                    var script = document.createElement('script');
                    script.text = scripts[i].text;
                    document.body.appendChild(script);
                }
            })
            .catch(function(error) {
                console.error('❌ Error:', error);
                panelContent.innerHTML = '<div class="text-center text-danger py-5"><i class="fas fa-exclamation-triangle fa-3x"></i><p>Error al cargar el formulario</p><button class="btn btn-secondary btn-sm mt-3" onclick="closePanel()">Cerrar</button></div>';
            });
    }

    function closePanel(callback) {
        var panel = document.getElementById('panelWrapper');
        var tableWrapper = document.getElementById('tableWrapper');
        
        if (!panel || !tableWrapper) {
            if (callback) callback();
            return;
        }
        
        isOpen = false;
        
        document.querySelectorAll('.contact-row').forEach(function(row) {
            row.classList.remove('contact-row-selected');
        });
        
        panel.classList.remove('visible');
        panel.classList.add('closing');
        tableWrapper.classList.remove('with-panel');
        
        setTimeout(function() {
            panel.style.display = 'none';
            panel.classList.remove('closing');
            var contenido = document.getElementById('slidePanelContent');
            if (contenido) {
                contenido.innerHTML = '';
            }
            currentContactId = null;
            if (callback) callback();
        }, 300);
    }

    window.openPanel = openPanel;
    window.openEditPanel = openEditPanel;
    window.closePanel = closePanel;

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isOpen) {
            closePanel();
        }
    });

    console.log('✅ Panel lateral inicializado correctamente');
}
</script>