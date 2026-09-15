<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Iniciar Sesión';

// Registrar el archivo CSS
$this->registerCssFile('@web/css/login.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
]);
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
<body>
<?php $this->beginBody() ?>

<div class="login-container">
    <div class="login-card">
        <!-- Logo con círculo azul de fondo -->
        <div class="login-logo">
            <div class="logo-circle">
                <?= Html::img('@web/images/plotyx.png', [
                    'alt' => 'Logo'
                ]) ?>
            </div>
        </div>
        
        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger alert-custom">
                <i class="fas fa-exclamation-circle"></i> 
                <?= Yii::$app->session->getFlash('error') ?>
            </div>
        <?php endif; ?>

        <?php $form = ActiveForm::begin([
            'id' => 'login-form',
            'fieldConfig' => [
                'template' => "{label}\n{input}\n{error}",
                'labelOptions' => ['class' => 'form-label'],
                'inputOptions' => ['class' => 'form-control'],
                'errorOptions' => ['class' => 'text-danger help-block'],
            ],
        ]); ?>

        <?= $form->field($model, 'username')
            ->textInput([
                'autofocus' => true,
                'placeholder' => 'Ingresar Usuario'
            ])
            ->label('Usuario')
        ?>

        <?= $form->field($model, 'password')
            ->passwordInput([
                'placeholder' => 'Ingresa tu contraseña'
            ])
            ->label('Contraseña')
        ?>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <?= $form->field($model, 'rememberMe')
                ->checkbox([
                    'template' => "{input} {label}",
                    'labelOptions' => ['style' => 'font-weight: 400; color: #666; font-size: 14px;'],
                ])
            ?>
            <?= Html::a('¿Olvidaste tu contraseña?', ['site/request-password-reset'], [
                'class' => 'forgot-password',
                'style' => 'margin-top: 0;'
            ]) ?>
        </div>

        <?= Html::submitButton('Iniciar sesión', [
            'class' => 'btn btn-login',
            'name' => 'login-button'
        ]) ?>

        <?php ActiveForm::end(); ?>

        <div class="login-footer">
            <?= Html::a('¿No tienes una cuenta? Solicita acceso', ['site/request-access']) ?>
        </div>

        <div class="login-bottom">
            <div style="margin-bottom: 15px;">
                <?= Html::a('Cloud Data Development', 'https://clouddatadevelopment.com/', [
                    'target' => '_blank'
                ]) ?>
                <span style="color: #0091FF;">. Todos los derechos reservados.</span>
            </div>
            
            <div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
                <?= Html::a('Términos de servicio', ['site/terms']) ?>
                <span style="color: #0091FF;">|</span>
                <?= Html::a('Política de privacidad', ['site/privacy']) ?>
                <span style="color: #0091FF;">|</span>
                <?= Html::a('Soporte', ['site/support']) ?>
            </div>
        </div>
    </div>
</div>

<?php $this->endBody() ?>
</body>
</html>