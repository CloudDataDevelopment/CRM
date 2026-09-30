<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\StringHelper;
use yii\widgets\LinkPager;

$this->title = 'Papelera de Contactos';
$this->params['breadcrumbs'][] = ['label' => 'Contactos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/contacts.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerCssFile('@web/css/dashboard.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerCssFile('@web/css/contact-panel.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class], 'position' => \yii\web\View::POS_HEAD]);
$this->registerCssFile('@web/css/contact-edit-modal.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class], 'position' => \yii\web\View::POS_HEAD]);

$this->registerJsFile('https://code.jquery.com/jquery-3.6.0.min.js', ['position' => \yii\web\View::POS_HEAD]);

$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;
$contacts = isset($contacts) ? $contacts : [];
$dataProvider = isset($dataProvider) ? $dataProvider : null;
$search = isset($search) ? $search : '';
$totalContacts = isset($totalContacts) ? $totalContacts : 0;
?>

<div class="contacts-index">
    <div class="contacts-wrapper">

        <!-- HEADER -->
        <div class="contacts-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Contactos</span><span class="separator">›</span>
                    <span class="current">Papelera</span>
                </div>
                <h1 class="page-title">
                    <i class="fas fa-trash text-danger me-2"></i>
                    <?= Html::encode($this->title) ?>
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= Html::a(
                    '<i class="fas fa-arrow-left"></i> Volver a Contactos',
                    ['index'],
                    ['class' => 'btn btn-secondary btn-sm btn-header-action']
                ) ?>
            </div>
        </div>

        <!-- ALERTA INFORMATIVA -->
        <div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
            <i class="fas fa-info-circle me-2" style="font-size: 1.2rem;"></i>
            <div>
                <strong>Papelera:</strong> Aquí se encuentran los contactos con estado <strong>"Eliminado"</strong>.
                Puedes restaurarlos para que vuelvan al listado principal.
            </div>
        </div>

        <!-- MÉTRICAS -->
        <div class="row g-2 mb-3">
            <div class="col-md-4 col-6">
                <div class="card stat-card stat-card-danger dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2">
                            <i class="fas fa-trash"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $totalContacts ?></div>
                            <div class="stat-label">En Papelera</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="card filtros-card mb-3">
            <div class="card-body">
                <form method="get" action="<?= Url::to(['contacts/trash']) ?>" id="form-filtros">
                    <div class="row align-items-end g-2">
                        <div class="col-md-1">
                            <label class="form-label fw-bold mb-0">Buscar</label>
                        </div>
                        <div class="col-md-5">
                            <input type="text" 
                                   class="form-control" 
                                   name="search" 
                                   placeholder="Buscar por nombre, teléfono, email..." 
                                   value="<?= Html::encode($search) ?>" 
                                   id="search-input">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="<?= Url::to(['contacts/trash']) ?>" 
                               class="btn btn-secondary w-100" 
                               title="Limpiar filtros">
                                <i class="fas fa-undo"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- TABLA -->
        <div class="card table-card">
            <div class="card-header">
                <div class="header-left">
                    <i class="fas fa-trash text-danger"></i>
                    <span>Contactos Eliminados</span>
                    <span class="badge bg-danger ms-2"><?= $totalContacts ?></span>
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
                                <th width="50">#</th>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th class="text-center" width="180">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($contacts)): ?>
                                <?php foreach ($contacts as $index => $contact): ?>
                                    <tr class="contact-row" data-id="<?= $contact->id_contact ?>">
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <strong><?= Html::encode($contact->getFullName()) ?></strong>
                                        </td>
                                        <td>
                                            <?php if ($contact->phone): ?>
                                                <i class="fas fa-phone text-success"></i>
                                                <?= Html::encode($contact->phone) ?>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
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
                                        <td>
                                            <span class="badge bg-info"><?= Html::encode($contact->getTypeContactName()) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-dark">
                                                <i class="fas fa-ban"></i> Eliminado
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <!-- 🔥 BOTÓN VER (abre el panel lateral) -->
                                                <button type="button"
                                                        class="btn btn-info btn-sm btn-action view-contact-btn"
                                                        data-id="<?= $contact->id_contact ?>"
                                                        title="Ver contacto">
                                                    <i class="fas fa-eye"></i>
                                                </button>

                                                <!-- 🔥 BOTÓN RESTAURAR -->
                                                <?= Html::a(
                                                    '<i class="fas fa-undo"></i> Restaurar',
                                                    ['restore', 'id' => $contact->id_contact],
                                                    [
                                                        'class' => 'btn btn-sm btn-success',
                                                        'title' => 'Restaurar contacto',
                                                        'data' => [
                                                            'confirm' => '¿Restaurar este contacto? Volverá al listado activo con estado "Activo".',
                                                            'method' => 'post',
                                                        ],
                                                    ]
                                                ) ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="fas fa-trash fa-3x d-block mb-3 text-muted"></i>
                                        <p class="mb-2">No hay contactos en la papelera.</p>
                                        <small class="text-muted d-block mb-3">
                                            Los contactos con estado "Eliminado" aparecerán aquí.
                                        </small>
                                        <?= Html::a(
                                            '<i class="fas fa-arrow-left"></i> Volver a Contactos',
                                            ['index'],
                                            ['class' => 'btn btn-sm btn-primary']
                                        ) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($dataProvider && $dataProvider->pagination->pageCount > 1): ?>
                <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        Mostrando <?= $dataProvider->getCount() ?> de <?= $totalContacts ?> contactos
                    </small>
                    <?= LinkPager::widget([
                        'pagination' => $dataProvider->pagination,
                        'options' => ['class' => 'pagination pagination-sm mb-0'],
                        'linkOptions' => ['class' => 'page-link'],
                        'prevPageLabel' => '<i class="fas fa-chevron-left"></i>',
                        'nextPageLabel' => '<i class="fas fa-chevron-right"></i>',
                        'firstPageLabel' => '<i class="fas fa-angle-double-left"></i>',
                        'lastPageLabel' => '<i class="fas fa-angle-double-right"></i>',
                        'maxButtonCount' => 5,
                    ]) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- PANEL LATERAL -->
        <div class="panel-container">
            <div class="panel-wrapper" id="panelWrapper">
                <div class="card slide-panel-card">
                    <div class="card-body p-0" id="slidePanelContent"></div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
$viewModalUrl = Url::to(['contacts/view-modal']);
?>

<script>
(function() {
    if (typeof jQuery === 'undefined') {
        var script = document.createElement('script');
        script.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
        script.onload = function() { inicializar(); };
        document.head.appendChild(script);
    } else {
        inicializar();
    }
})();

function inicializar() {
    var $ = jQuery;

    // 🔥 ABRIR PANEL AL HACER CLIC EN "VER"
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

    // Búsqueda con Enter
    var searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('form-filtros').submit();
            }
        });
    }

    var currentContactId = null;
    var viewModalUrl = '<?= $viewModalUrl ?>';
    var isOpen = false;

    function openPanel(contactId) {
        if (contactId === currentContactId && isOpen) {
            closePanel();
            return;
        }
        if (isOpen) {
            closePanel(function() {
                setTimeout(function() { openPanel(contactId); }, 300);
            });
            return;
        }

        currentContactId = contactId;
        isOpen = true;

        var panel = document.getElementById('panelWrapper');
        var panelContent = document.getElementById('slidePanelContent');
        var tableWrapper = document.getElementById('tableWrapper');

        if (!panel || !panelContent) return;

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

                // Ejecutar scripts embebidos del partial
                var scripts = panelContent.getElementsByTagName('script');
                for (var i = 0; i < scripts.length; i++) {
                    var script = document.createElement('script');
                    script.text = scripts[i].text;
                    document.body.appendChild(script);
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                panelContent.innerHTML = '<div class="text-center text-danger py-5"><i class="fas fa-exclamation-triangle fa-3x"></i><p>Error al cargar el contacto</p><button class="btn btn-secondary btn-sm mt-3" onclick="closePanel()">Cerrar</button></div>';
            });
    }

    function closePanel(callback) {
        var panel = document.getElementById('panelWrapper');

        if (!panel) {
            if (callback) callback();
            return;
        }

        isOpen = false;
        panel.classList.remove('visible');
        panel.classList.add('closing');

        setTimeout(function() {
            panel.style.display = 'none';
            panel.classList.remove('closing');
            var contenido = document.getElementById('slidePanelContent');
            if (contenido) contenido.innerHTML = '';
            currentContactId = null;
            if (callback) callback();
        }, 300);
    }

    // Listener global para [data-panel-close]
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('[data-panel-close]');
        if (btn) {
            e.preventDefault();
            e.stopPropagation();
            closePanel();
        }
    }, true);

    // Listener global para [data-panel-close-go="url"]
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('[data-panel-close-go]');
        if (btn) {
            e.preventDefault();
            e.stopPropagation();
            var url = btn.getAttribute('data-panel-close-go');
            closePanel(function() {
                window.location.href = url;
            });
        }
    }, true);

    window.openPanel = openPanel;
    window.closePanel = closePanel;

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isOpen) closePanel();
    });
}
</script>