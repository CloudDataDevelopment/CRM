<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use yii\bootstrap5\Html;
use yii\bootstrap5\Breadcrumbs;

AppAsset::register($this);

$this->registerCssFile('@web/css/site.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php $this->beginBody() ?>

<!-- Navbar simple sin menú lateral -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= Yii::$app->homeUrl ?>">
            <i class="fas fa-cubes"></i> CRM Sistema
        </a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (Yii::$app->user->isGuest): ?>
                    <li class="nav-item">
                        <?= Html::a('<i class="fas fa-sign-in-alt"></i> Login', ['/site/login'], ['class' => 'nav-link']) ?>
                    </li>
                    <li class="nav-item">
                        <?= Html::a('<i class="fas fa-user-plus"></i> Registrarse', ['/site/register'], ['class' => 'nav-link']) ?>
                    </li>
                <?php else: ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?= Yii::$app->user->identity->username ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <?= Html::a('<i class="fas fa-user-cog"></i> Mi Perfil', ['/user/profile'], [
                                    'class' => 'dropdown-item'
                                ]) ?>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <?= Html::a('<i class="fas fa-sign-out-alt"></i> Cerrar Sesión', ['/site/logout'], [
                                    'class' => 'dropdown-item',
                                    'data-method' => 'post',
                                ]) ?>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Contenido principal (sin sidebar) -->
<main style="padding-top: 76px; min-height: 100vh; background: #f4f6f9;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
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
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>