<?php

/** @var yii\web\View $this */
/** @var string $content */

use yii\bootstrap5\Html;
use yii\helpers\Url;

// Agregar clase al body para identificar si es invitado
$bodyClass = Yii::$app->user->isGuest ? 'guest' : '';

$user = Yii::$app->user->identity;
$isSuperAdmin = $user ? $user->isSuperAdmin() : false;
$isAdminUser = $user ? $user->isAdmin() : false;
$hideSidebar = isset($this->params['hideSidebar']) && $this->params['hideSidebar'] === true;

// Registrar archivos CSS
$this->registerCssFile('@web/css/sidebar.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
]);

$this->registerCssFile('@web/css/site.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
]);

// Agregar meta token CSRF para AJAX
$this->registerCsrfMetaTags();

// Agregar meta tag con tiempo de última actividad
$lastActivity = Yii::$app->session->get('last_activity', time());
$this->registerMetaTag([
    'name' => 'last-activity',
    'content' => $lastActivity
]);

// Registrar script de session timeout
$jsPath = Yii::getAlias('@webroot/js/session-timeout.js');
if (file_exists($jsPath)) {
    $this->registerJsFile('@web/js/session-timeout.js', [
        'depends' => [\yii\web\JqueryAsset::class],
        'position' => \yii\web\View::POS_END,
    ]);
}

// Registrar script de gestión de sesión
$jsPath = Yii::getAlias('@webroot/js/session-manager.js');
if (file_exists($jsPath)) {
    $this->registerJsFile('@web/js/session-manager.js', [
        'depends' => [\yii\web\JqueryAsset::class],
        'position' => \yii\web\View::POS_END,
    ]);
}

// Función JavaScript para logout y búsqueda
$this->registerJs("
    function handleLogout(url) {
        const csrfToken = document.querySelector('meta[name=\"csrf-token\"]');
        const csrfParam = document.querySelector('meta[name=\"csrf-param\"]');
        
        if (!csrfToken || !csrfParam) {
            window.location.href = url;
            return;
        }
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = csrfParam.getAttribute('content');
        tokenInput.value = csrfToken.getAttribute('content');
        form.appendChild(tokenInput);
        
        document.body.appendChild(form);
        form.submit();
    }

    // Función para búsqueda rápida en el menú
    function quickSearch() {
        const query = document.getElementById('quickSearchInput').value.toLowerCase().trim();
        const items = document.querySelectorAll('.searchable-item');
        
        if (!query) {
            items.forEach(item => {
                item.style.display = '';
            });
            return;
        }

        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            if (text.includes(query)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }

    // Función para búsqueda en tablas
    function searchTable(tableId, inputId) {
        const input = document.getElementById(inputId);
        const filter = input.value.toLowerCase();
        const table = document.getElementById(tableId);
        if (!table) return;
        const rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            let found = false;
            const cells = row.getElementsByTagName('td');
            
            for (let j = 0; j < cells.length; j++) {
                const cell = cells[j];
                if (cell) {
                    const text = cell.textContent.toLowerCase();
                    if (text.includes(filter)) {
                        found = true;
                        break;
                    }
                }
            }
            row.style.display = found ? '' : 'none';
        }
    }
", \yii\web\View::POS_HEAD);

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="<?= $bodyClass ?>">
<?php $this->beginBody() ?>

<!-- Menú lateral -->
<?php if (!$hideSidebar && !Yii::$app->user->isGuest): ?>
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-rocket"></i> <span>CRM</span></h3>
        </div>
        
        <div class="sidebar-scroll">
            <ul class="nav flex-column">
                <!-- Dashboard -->
                <li class="nav-item searchable-item">
                    <?= Html::a('<i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>', ['/dashboard/index'], [
                        'class' => 'nav-link' . (Yii::$app->controller->id == 'dashboard' ? ' active' : '')
                    ]) ?>
                </li>
                
                <!-- Empresas (Solo SuperAdmin) -->
                <?php if ($isSuperAdmin): ?>
                    <li class="nav-item searchable-item">
                        <?= Html::a('<i class="fas fa-building"></i> <span>Empresas</span>', ['/empresa/index'], [
                            'class' => 'nav-link' . (Yii::$app->controller->id == 'empresa' ? ' active' : '')
                        ]) ?>
                    </li>
                <?php endif; ?>
                
                <!-- Leads -->
                <li class="nav-item searchable-item">
                    <?= Html::a('<i class="fas fa-plus-circle"></i> <span>Crear Leads</span>', ['/lead/create'], [
                        'class' => 'nav-link' . (Yii::$app->controller->id == 'lead' && Yii::$app->controller->action->id == 'create' ? ' active' : '')
                    ]) ?>
                </li>
                
                <?php if ($isAdminUser): ?>
                    <li class="nav-item searchable-item">
                        <?= Html::a('<i class="fas fa-user-check"></i> <span>Asignación de Leads</span>', ['/lead-assign/index'], [
                            'class' => 'nav-link' . (Yii::$app->controller->id == 'lead-assign' ? ' active' : '')
                        ]) ?>
                    </li>
                <?php endif; ?>
                
                <li class="nav-item searchable-item">
                    <?= Html::a('<i class="fas fa-users"></i> <span>Gestor de Leads</span>', ['/lead/index'], [
                        'class' => 'nav-link' . (Yii::$app->controller->id == 'lead' && Yii::$app->controller->action->id == 'index' ? ' active' : '')
                    ]) ?>
                </li>
                
                <!-- Contactos -->
                <li class="nav-item searchable-item">
                    <?= Html::a('<i class="fas fa-address-book"></i> <span>Contactos</span>', ['/contacts/index'], [
                        'class' => 'nav-link' . (Yii::$app->controller->id == 'contacts' ? ' active' : '')
                    ]) ?>
                </li>
                
                <!-- Ventas -->
                <li class="nav-item searchable-item">
                    <?= Html::a('<i class="fas fa-phone"></i> <span>Seguimientos</span>', ['/sales-tracking/index'], [
                        'class' => 'nav-link' . (Yii::$app->controller->id == 'sales-tracking' ? ' active' : '')
                    ]) ?>
                </li>
                
                <li class="nav-item searchable-item">
                    <?= Html::a('<i class="fas fa-file-invoice"></i> <span>Cotizaciones</span>', ['/quote/index'], [
                        'class' => 'nav-link' . (Yii::$app->controller->id == 'quote' ? ' active' : '')
                    ]) ?>
                </li>
                
                <li class="nav-item searchable-item">
                    <?= Html::a('<i class="fas fa-shopping-cart"></i> <span>Registro de Ventas</span>', ['/sales/index'], [
                        'class' => 'nav-link' . (Yii::$app->controller->id == 'sales' ? ' active' : '')
                    ]) ?>
                </li>
                
                <!-- Tareas -->
                <li class="nav-item searchable-item">
                    <?= Html::a('<i class="fas fa-tasks"></i> <span>Tareas</span>', ['/task/index'], [
                        'class' => 'nav-link' . (Yii::$app->controller->id == 'task' ? ' active' : '')
                    ]) ?>
                </li>
                
                <!-- Calendario -->
                <li class="nav-item searchable-item">
                    <?= Html::a('<i class="fas fa-calendar-alt"></i> <span>Calendario</span>', ['/calendar/index'], [
                        'class' => 'nav-link' . (Yii::$app->controller->id == 'calendar' ? ' active' : '')
                    ]) ?>
                </li>
                
                <!-- Reportes -->
                <li class="nav-item searchable-item">
                    <?= Html::a('<i class="fas fa-chart-bar"></i> <span>Evaluaciones</span>', ['/report/index'], [
                        'class' => 'nav-link' . (Yii::$app->controller->id == 'report' ? ' active' : '')
                    ]) ?>
                </li>

                <!-- Marketing -->
                <li class="nav-item searchable-item">
                    <a class="nav-link <?= (Yii::$app->controller->id == 'marketing') ? 'active' : '' ?>" data-bs-toggle="collapse" href="#marketingMenu" role="button" aria-expanded="<?= (Yii::$app->controller->id == 'marketing') ? 'true' : 'false' ?>" aria-controls="marketingMenu">
                        <i class="fas fa-bullhorn"></i> <span>Marketing</span>
                        <i class="fas fa-chevron-down float-end" style="font-size: 0.7rem; margin-top: 5px;"></i>
                    </a>
                    <div class="collapse <?= (Yii::$app->controller->id == 'marketing') ? 'show' : '' ?>" id="marketingMenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item searchable-item">
                                <?= Html::a('<i class="fas fa-bullhorn"></i> <span>Campañas/Promociones</span>', ['/marketing/index'], [
                                    'class' => 'nav-link' . (Yii::$app->controller->id == 'marketing' && Yii::$app->request->get('tab') == 'campaigns' ? ' active' : '')
                                ]) ?>
                            </li>

                            <?php if ($isAdminUser || $isSuperAdmin): ?>
                                <li class="nav-item searchable-item">
                                    <?= Html::a('<i class="fas fa-plus-circle"></i> <span>Crear Campaña/Promoción</span>', ['/marketing/create'], [
                                        'class' => 'nav-link' . (Yii::$app->controller->id == 'marketing' && Yii::$app->controller->action->id == 'create' ? ' active' : '')
                                    ]) ?>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>

                <!-- Administración -->
                <?php if ($isAdminUser || $isSuperAdmin): ?>
                    <li class="nav-item searchable-item">
                        <a class="nav-link <?= (Yii::$app->controller->id == 'user-management') ? 'active' : '' ?>" data-bs-toggle="collapse" href="#adminMenu" role="button" aria-expanded="<?= (Yii::$app->controller->id == 'user-management') ? 'true' : 'false' ?>" aria-controls="adminMenu">
                            <i class="fas fa-cog"></i> <span>Administración</span>
                            <i class="fas fa-chevron-down float-end" style="font-size: 0.7rem; margin-top: 5px;"></i>
                        </a>
                        <div class="collapse <?= (Yii::$app->controller->id == 'user-management') ? 'show' : '' ?>" id="adminMenu">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item searchable-item">
                                    <?= Html::a('<i class="fas fa-users-cog"></i> <span>Gestión de Usuarios</span>', ['/user-management/index'], [
                                        'class' => 'nav-link' . (Yii::$app->controller->id == 'user-management' ? ' active' : '')
                                    ]) ?>
                                </li>
                                <?php if ($isAdminUser): ?>
                                    <li class="nav-item searchable-item">
                                        <?= Html::a('<i class="fas fa-user-plus"></i> <span>Registrar Usuario</span>', ['/site/register'], [
                                        'class' => 'nav-link' . (Yii::$app->controller->id == 'site' && Yii::$app->controller->action->id == 'register' ? ' active' : '')
                                    ]) ?>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </li>
                <?php endif; ?>
                
                <!-- Separador -->
                <li class="nav-item">
                    <hr class="sidebar-divider">
                </li>
                
                <!-- Mi Perfil -->
                <li class="nav-item searchable-item">
                    <?= Html::a('<i class="fas fa-user-circle"></i> <span>Mi Perfil</span>', ['/site/profile'], [
                        'class' => 'nav-link' . (Yii::$app->controller->id == 'site' && Yii::$app->controller->action->id == 'profile' ? ' active' : '')
                    ]) ?>
                </li>
                
                <!-- Cerrar Sesión -->
                <li class="nav-item">
                    <a class="nav-link logout" href="#" onclick="handleLogout('<?= Url::to(['/site/logout']) ?>'); return false;">
                        <i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
<?php endif; ?>

<!-- Contenido principal -->
<main class="main-content">
    <!-- Barra de búsqueda superior -->
    <div class="top-search-bar">
        <div class="search-wrapper">
            <div class="input-group">
                <span class="input-group-text" style="background: transparent; border: none; padding-right: 0;">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text" 
                       id="quickSearchInput" 
                       class="form-control" 
                       placeholder="Buscar en el menú..." 
                       onkeyup="quickSearch()"
                       style="border-left: none; padding-left: 5px;">
            </div>
        </div>
        
        <!-- Dropdown de usuario -->
        <div class="dropdown user-info-wrapper">
            <a href="#" 
               class="user-info dropdown-toggle" 
               id="userDropdown" 
               role="button" 
               data-bs-toggle="dropdown" 
               aria-expanded="false"
               title="Mi cuenta">
                <div class="avatar">
                    <?= strtoupper(substr(Yii::$app->user->identity->name ?? 'U', 0, 1)) ?>
                </div>
                <div class="user-details">
                    <span class="user-name"><?= Yii::$app->user->identity->name ?? 'Usuario' ?></span>
                    <span class="user-role">
                        <?php 
                        $auth = Yii::$app->user->identity->authentication;
                        $role = $auth ? $auth->role : null;
                        echo $role ? $role->role_type : 'Usuario';
                        ?>
                    </span>
                </div>
                <i class="fas fa-chevron-down dropdown-arrow"></i>
            </a>
            
            <ul class="dropdown-menu dropdown-menu-end user-dropdown-menu" aria-labelledby="userDropdown">
                <!-- Header del dropdown -->
                <li class="dropdown-header">
                    <div class="dropdown-user-header">
                        <div class="dropdown-avatar">
                            <?= strtoupper(substr(Yii::$app->user->identity->name ?? 'U', 0, 1)) ?>
                        </div>
                        <div class="dropdown-user-info">
                            <div class="dropdown-user-name">
                                <?= Yii::$app->user->identity->name ?? 'Usuario' ?>
                                <?= Yii::$app->user->identity->lastname1 ?? '' ?>
                            </div>
                            <div class="dropdown-user-email">
                                <?= Yii::$app->user->identity->email ?? '' ?>
                            </div>
                        </div>
                    </div>
                </li>
                
                <li><hr class="dropdown-divider"></li>
                
                <!-- Mi Perfil -->
                <li>
                    <a class="dropdown-item" href="<?= Url::to(['/site/profile']) ?>">
                        <i class="fas fa-user-circle text-primary"></i>
                        <span>Mi Perfil</span>
                    </a>
                </li>
                
                <!-- Configuración (solo admins) -->
                <?php if ($isAdminUser || $isSuperAdmin): ?>
                    <li>
                        <a class="dropdown-item" href="<?= Url::to(['/user-management/index']) ?>">
                            <i class="fas fa-cog text-secondary"></i>
                            <span>Configuración</span>
                        </a>
                    </li>
                <?php endif; ?>
                
                <li><hr class="dropdown-divider"></li>
                
                <!-- Cerrar Sesión -->
                <li>
                    <a class="dropdown-item text-danger" href="#" 
                       onclick="handleLogout('<?= Url::to(['/site/logout']) ?>'); return false;">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Alertas y contenido -->
    <div class="page-content">
        <?php
        $flashes = Yii::$app->session->getAllFlashes();
        if (!empty($flashes)) {
            foreach ($flashes as $key => $message) {
                $alertClass = $key == 'danger' ? 'danger' : ($key == 'error' ? 'danger' : $key);
                $icon = $key == 'success' ? 'fa-check-circle' : ($key == 'danger' || $key == 'error' ? 'fa-exclamation-triangle' : 'fa-info-circle');
                echo '<div class="alert alert-' . $alertClass . ' alert-dismissible fade show" role="alert">';
                echo '<i class="fas ' . $icon . '"></i>';
                echo '<span>' . $message . '</span>';
                echo '<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>';
                echo '</div>';
            }
        }
        ?>
        <?= $content ?>
    </div>
</main>

<?php
// ============================================
// BOTÓN FLOTANTE "EXPORTAR PDF"
// ============================================
$currentController = Yii::$app->controller->id;
$currentAction = Yii::$app->controller->action->id;

$modulesWithReport = [
    'task',
    'lead',
    'user-management',
    'quote',
    'sales-tracking',
    'reservation',
    'contacts',
    'report',
    'marketing',
    'empresa',
];

$moduleMap = [
    'user-management' => 'user',
    'contacts'        => 'contact',
    'marketing'       => 'campaign',
    'empresa'         => 'company',
];

$reportModule = $moduleMap[$currentController] ?? $currentController;

if (in_array($currentController, $modulesWithReport) && $currentAction === 'index' && !Yii::$app->user->isGuest):
?>
    <a href="<?= Url::to(array_merge(['/export/pdf', 'module' => $reportModule], Yii::$app->request->queryParams)) ?>"
       target="_blank"
       class="btn-pdf-floating"
       title="Exportar esta lista a PDF">
        <i class="fas fa-file-pdf"></i>
        <span>Exportar PDF</span>
    </a>

    <style>
        .btn-pdf-floating {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #dc3545, #b02a37);
            color: white;
            padding: 12px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-pdf-floating:hover {
            background: linear-gradient(135deg, #b02a37, #8b1a25);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.6);
            text-decoration: none;
        }

        .btn-pdf-floating i {
            font-size: 16px;
        }

        @media (max-width: 768px) {
            .btn-pdf-floating span {
                display: none;
            }
            .btn-pdf-floating {
                padding: 14px;
                border-radius: 50%;
            }
        }
    </style>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>