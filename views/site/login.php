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

// ============================================
// 🔥 CONFIGURACIÓN PARA EL CORREO DE SOPORTE
// ============================================
$emailSoporte   = 'soporte@clouddatadevelopment.com'; // 🔥 Cambia por tu correo real
$nombreSistema  = 'CRM';
$fechaSolicitud = date('d/m/Y H:i');

// ============================================
// 🔥 1. CORREO DE "OLVIDÉ MI CONTRASEÑA"
// ============================================
$asuntoPassword = "Solicitud de restablecimiento de contraseña - {$nombreSistema}";

$cuerpoPassword = "Hola, equipo de soporte de {$nombreSistema}:\n\n" .
                  "Solicito restablecer mi contraseña de acceso al sistema.\n\n" .
                  "═══════════════════════════════\n" .
                  "📅 Fecha: {$fechaSolicitud}\n" .
                  "═══════════════════════════════\n\n" .
                  "👉 Datos del usuario:\n" .
                  "   • Usuario: ____________________\n" .
                  "   • Correo registrado: ____________________\n" .
                  "   • Empresa: ____________________\n\n" .
                  "👉 Motivo: Olvidé mi contraseña.\n\n" .
                  "Quedo atento a su respuesta.\n\n" .
                  "Gracias.";

$gmailUrlPassword = 'https://mail.google.com/mail/?view=cm&fs=1' .
                    '&to=' . urlencode($emailSoporte) .
                    '&su=' . urlencode($asuntoPassword) .
                    '&body=' . urlencode($cuerpoPassword);

$mailtoUrlPassword = 'mailto:' . $emailSoporte .
                     '?subject=' . urlencode($asuntoPassword) .
                     '&body=' . urlencode($cuerpoPassword);

// ============================================
// 🔥 2. CORREO DE "SOLICITAR ACCESO"
// ============================================
$asuntoAcceso = "Solicitud de acceso al sistema - {$nombreSistema}";

$cuerpoAcceso = "Hola, equipo de soporte de {$nombreSistema}:\n\n" .
                "Solicito acceso al sistema. A continuación mis datos:\n\n" .
                "═══════════════════════════════\n" .
                "📅 Fecha: {$fechaSolicitud}\n" .
                "═══════════════════════════════\n\n" .
                "👉 Datos del solicitante:\n" .
                "   • Nombre completo: ____________________\n" .
                "   • Correo electrónico: ____________________\n" .
                "   • Teléfono: ____________________\n" .
                "   • Empresa: ____________________\n" .
                "   • Puesto / cargo: ____________________\n" .
                "   • Motivo de la solicitud: ____________________\n\n" .
                "👉 Comentarios adicionales:\n" .
                "   __________________________________________\n" .
                "   __________________________________________\n\n" .
                "Quedo atento a su respuesta para completar el registro.\n\n" .
                "Gracias.";

$gmailUrlAcceso = 'https://mail.google.com/mail/?view=cm&fs=1' .
                  '&to=' . urlencode($emailSoporte) .
                  '&su=' . urlencode($asuntoAcceso) .
                  '&body=' . urlencode($cuerpoAcceso);

$mailtoUrlAcceso = 'mailto:' . $emailSoporte .
                   '?subject=' . urlencode($asuntoAcceso) .
                   '&body=' . urlencode($cuerpoAcceso);
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

            <!-- 🔥 BOTÓN "OLVIDASTE TU CONTRASEÑA" → Abre Gmail -->
            <?= Html::a(
                '<i class="fas fa-envelope"></i> ¿Olvidaste tu contraseña?',
                $gmailUrlPassword,
                [
                    'class' => 'forgot-password',
                    'style' => 'margin-top: 0;',
                    'target' => '_blank',
                    'rel' => 'noopener noreferrer',
                    'title' => 'Enviar solicitud de restablecimiento por Gmail',
                    'encode' => false,
                ]
            ) ?>
        </div>

        <?= Html::submitButton('Iniciar sesión', [
            'class' => 'btn btn-login',
            'name' => 'login-button'
        ]) ?>

        <?php ActiveForm::end(); ?>



        <!-- 🔥 ENLACE "SOLICITA ACCESO" → Abre Gmail -->
        <div class="login-footer">
            <?= Html::a(
                '<i class="fas fa-user-plus"></i> ¿No tienes una cuenta? Solicita acceso',
                $gmailUrlAcceso,
                [
                    'target' => '_blank',
                    'rel' => 'noopener noreferrer',
                    'title' => 'Enviar solicitud de acceso por Gmail',
                    'encode' => false,
                ]
            ) ?>
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