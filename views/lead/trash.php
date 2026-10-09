<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\StringHelper;
use yii\widgets\LinkPager;

$this->title = 'Papelera de Leads';
$this->params['breadcrumbs'][] = ['label' => 'Leads', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// 🔥 REGISTRAR TODOS LOS CSS
$this->registerCssFile('@web/css/leads.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerCssFile('@web/css/dashboard.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerCssFile('@web/css/quote.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerCssFile('@web/css/lead-panel.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class], 'position' => \yii\web\View::POS_HEAD]);

$this->registerJsFile('https://code.jquery.com/jquery-3.6.0.min.js', ['position' => \yii\web\View::POS_HEAD]);

$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;
$leads = isset($leads) ? $leads : [];
$dataProvider = isset($dataProvider) ? $dataProvider : null;
$totalLeads = isset($totalLeads) ? $totalLeads : 0;
$search = isset($search) ? $search : '';
$statusOptions = isset($statusOptions) ? $statusOptions : [];

$totalCount = $dataProvider ? $dataProvider->getTotalCount() : count($leads);
$currentCount = $dataProvider ? $dataProvider->getCount() : count($leads);
$pageCount = $dataProvider ? $dataProvider->getPagination()->getPageCount() : 1;
$currentPage = $dataProvider ? $dataProvider->getPagination()->getPage() + 1 : 1;
?>

<div class="quote-container">
    <div class="quote-index">

        <!-- HEADER -->
        <div class="quote-header">
            <div class="header-left">
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Ventas</span><span class="separator">›</span>
                    <span>Leads</span><span class="separator">›</span>
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
                    '<i class="fas fa-arrow-left"></i> Volver a Leads',
                    ['index'],
                    ['class' => 'btn btn-secondary btn-sm']
                ) ?>
            </div>
        </div>

        <!-- ALERTA -->
        <div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
            <i class="fas fa-info-circle me-2" style="font-size: 1.2rem;"></i>
            <div>
                <strong>Papelera:</strong> Aquí se encuentran los leads marcados como <strong>eliminados</strong>.
                Puedes restaurarlos para que vuelvan al listado activo.
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
                            <div class="stat-number"><?= $totalCount ?></div>
                            <div class="stat-label">En Papelera</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="card filtros-card mb-3">
            <div class="card-body">
                <form method="get" action="<?= Url::to(['lead/trash']) ?>" id="form-filtros">
                    <div class="row align-items-end g-2">
                        <div class="col-md-1">
                            <label class="form-label fw-bold mb-0">Buscar</label>
                        </div>
                        <div class="col-md-5">
                            <input type="text" 
                                   class="form-control" 
                                   name="search" 
                                   placeholder="Buscar por nombre, teléfono, observaciones..." 
                                   value="<?= Html::encode($search) ?>" 
                                   id="search-input">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="<?= Url::to(['lead/trash']) ?>" 
                               class="btn btn-secondary w-100" 
                               title="Limpiar filtros">
                                <i class="fas fa-undo"></i> Limpiar
                            </a>
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
                            <i class="fas fa-trash text-danger"></i>
                            <span>Leads en Papelera</span>
                            <span class="badge bg-danger ms-2"><?= $totalCount ?></span>
                        </div>
                        <div class="header-right">
                            <span class="badge bg-secondary">
                                Página <?= $currentPage ?> de <?= $pageCount ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="leads-table">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Nombre</th>
                                        <th>Teléfono</th>
                                        <th>Estado</th>
                                        <th>Fecha Eliminación</th>
                                        <th class="text-center" width="220">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($leads)): ?>
                                        <?php foreach ($leads as $index => $lead): ?>
                                            <!-- 🔥 SIN clase lead-row ni data-id: no abre el panel al hacer clic -->
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td>
                                                    <strong><?= Html::encode($lead->name . ' ' . $lead->lastname) ?></strong>
                                                </td>
                                                <td>
                                                    <?php if ($lead->phone): ?>
                                                        <i class="fas fa-phone text-success"></i>
                                                        <?= Html::encode($lead->phone) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-dark">
                                                        <i class="fas fa-ban"></i> Eliminado
                                                    </span>
                                                </td>
                                                <td>
                                                    <?= date('d/m/Y', strtotime($lead->created_at)) ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex gap-1 justify-content-center">
                                                        <!-- 🔥 "Ver" abre el MODAL (view-modal) -->
                                                        <button type="button"
                                                                class="btn btn-sm btn-info view-lead-btn"
                                                                data-id="<?= $lead->id_lead ?>"
                                                                title="Ver">
                                                            <i class="fas fa-eye"></i> Ver
                                                        </button>

                                                        <!-- 🔥 "Restaurar" -->
                                                        <?= Html::a(
                                                            '<i class="fas fa-undo"></i> Restaurar',
                                                            ['restore', 'id' => $lead->id_lead],
                                                            [
                                                                'class' => 'btn btn-sm btn-success',
                                                                'title' => 'Restaurar lead',
                                                                'data' => [
                                                                    'confirm' => '¿Restaurar este lead? Volverá al listado activo.',
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
                                            <td colspan="6" class="text-center text-muted py-5">
                                                <i class="fas fa-trash fa-3x d-block mb-3 text-muted"></i>
                                                <p class="mb-2">No hay leads en la papelera.</p>
                                                <small class="text-muted d-block mb-3">
                                                    Los leads eliminados aparecerán aquí.
                                                </small>
                                                <?= Html::a(
                                                    '<i class="fas fa-arrow-left"></i> Volver a Leads',
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
                                Mostrando <?= $currentCount ?> de <?= $totalCount ?> leads
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
            </div>

            <!-- Panel Lateral -->
            <div class="panel-wrapper" id="panelWrapper">
                <div class="card slide-panel-card">
                    <div class="card-body p-0" id="slidePanelContent"></div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
$viewModalUrl = Url::to(['lead/view-modal']);
?>

<script>
// ============================================
// 🔥 EJECUTAR SCRIPTS EMBEBIDOS DEL MODAL
// ============================================
function executeScripts(container) {
    if (!container) return 0;
    var scripts = container.querySelectorAll('script');
    scripts.forEach(function(oldScript) {
        var newScript = document.createElement('script');
        if (oldScript.src) newScript.src = oldScript.src;
        else newScript.textContent = oldScript.textContent;
        document.body.appendChild(newScript);
        oldScript.remove();
    });
    return scripts.length;
}

// ============================================
// 🔧 INICIALIZACIÓN
// ============================================
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
    var viewModalUrl = '<?= $viewModalUrl ?>';
    var isOpen = false;
    var currentId = null;

    function closePanel(callback) {
        var panel = document.getElementById('panelWrapper');
        var tableWrapper = document.getElementById('tableWrapper');

        if (!panel) {
            if (callback) callback();
            return;
        }

        isOpen = false;

        panel.classList.remove('visible');
        panel.classList.add('closing');
        if (tableWrapper) tableWrapper.classList.remove('with-panel');

        setTimeout(function() {
            panel.style.display = 'none';
            panel.classList.remove('closing');
            var contenido = document.getElementById('slidePanelContent');
            if (contenido) contenido.innerHTML = '';
            currentId = null;
            if (callback) callback();
        }, 300);
    }

    function openPanel(id) {
        if (id === currentId && isOpen) {
            closePanel();
            return;
        }

        if (isOpen) {
            closePanel(function() {
                setTimeout(function() { openPanel(id); }, 300);
            });
            return;
        }

        currentId = id;
        isOpen = true;

        var panel = document.getElementById('panelWrapper');
        var panelContent = document.getElementById('slidePanelContent');
        var tableWrapper = document.getElementById('tableWrapper');

        if (!panel || !panelContent) return;

        if (tableWrapper) tableWrapper.classList.add('with-panel');
        panel.style.display = 'block';
        panel.classList.add('visible');

        panelContent.innerHTML = '<div class="text-center text-muted py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Cargando...</p></div>';

        fetch(viewModalUrl + '?id=' + id)
            .then(function(response) {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.text();
            })
            .then(function(data) {
                panelContent.innerHTML = data;
                executeScripts(panelContent);
            })
            .catch(function(error) {
                panelContent.innerHTML = '<div class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle fa-2x d-block mb-2"></i><p>Error al cargar</p><button class="btn btn-secondary btn-sm mt-2" data-panel-close>Cerrar</button></div>';
            });
    }

    // ============================================
    // LISTENER GLOBAL PARA CERRAR
    // ============================================
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('[data-panel-close]');
        if (btn) {
            e.preventDefault();
            e.stopPropagation();
            closePanel();
        }
    }, true);

    // ============================================
    // LISTENER GLOBAL PARA NAVEGAR Y CERRAR
    // ============================================
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

    // ============================================
    // CLICK EN "VER"
    // ============================================
    $(document).on('click', '.view-lead-btn', function(e) {
        e.stopPropagation();
        var id = $(this).data('id');
        if (id) openPanel(id);
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isOpen) {
            closePanel();
        }
    });

    window.openPanel = openPanel;
    window.closePanel = closePanel;

    var searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') document.getElementById('form-filtros').submit();
        });
    }
}
</script>