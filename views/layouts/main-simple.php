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

// 🔥 Registrar el mismo CSS que usa el main normal
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

// 🔥 Mismas funciones JavaScript que el main normal
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

<!-- 🔥 CONTENEDOR PRINCIPAL SIN SIDEBAR -->
<main class="main-content" style="margin-left: 0;">
    <!-- 🔥 BARRA DE BÚSQUEDA SUPERIOR - IGUAL AL MAIN NORMAL -->
    <div class="top-search-bar">
        <!-- Logo o título a la izquierda -->
        <div class="page-title">
            <i class="fas fa-rocket"></i>
            <span>CRM</span>
        </div>
        
        <!-- Barra de búsqueda - exactamente igual al main normal -->
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
        
        <!-- Información del usuario - exactamente igual al main normal -->
        <div class="user-info">
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
        </div>
    </div>

    <!-- Alertas y contenido -->
    <div class="page-content" style="padding-top: 10px;">
        <?php
        $flashes = Yii::$app->session->getAllFlashes();
        if (!empty($flashes)) {
            foreach ($flashes as $key => $message) {
                $alertClass = $key == 'danger' ? 'danger' : ($key == 'error' ? 'danger' : $key);
                $icon = $key == 'success' ? 'fa-check-circle' : ($key == 'danger' || $key == 'error' ? 'fa-exclamation-triangle' : 'fa-info-circle');
                echo '<div class="alert alert-' . $alertClass . ' alert-dismissible fade show" role="alert">';
                echo '<i class="fas ' . $icon . '"></i> ' . $message;
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                echo '</div>';
            }
        }
        ?>
        <?= $content ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>