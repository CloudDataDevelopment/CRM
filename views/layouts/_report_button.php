<?php

/**
 * Botón "Generar Reporte"
 * Se incluye dentro del header-actions de cada index que tiene reporte disponible.
 * Solo visible para Admin y Super Admin, y solo en los módulos habilitados.
 */

use yii\helpers\Html;
use yii\helpers\Url;

$user = Yii::$app->user->isGuest ? null : Yii::$app->user->identity;

$currentController = Yii::$app->controller->id;
$currentAction = Yii::$app->controller->action ? Yii::$app->controller->action->id : 'index';

// Módulos con reporte disponible
$modulesWithReport = [
    'task', 'lead', 'user-management', 'quote', 'sales-tracking',
    'reservation', 'contacts', 'report', 'marketing', 'empresa',
];

// Mapeo: controlador → módulo
$moduleMap = [
    'user-management' => 'user',
    'contacts'        => 'contact',
    'marketing'       => 'campaign',
    'empresa'         => 'company',
];

$reportModule = $moduleMap[$currentController] ?? $currentController;

// Validación de rol
$puedeVerReporte = $user && ($user->isAdmin() || $user->isSuperAdmin());

if (
    in_array($currentController, $modulesWithReport) &&
    $currentAction === 'index' &&
    !Yii::$app->user->isGuest &&
    $puedeVerReporte
):
?>
    <?= Html::a('<i class="fas fa-file-pdf"></i> <span>Generar Reporte</span>',
        array_merge(['/export/pdf', 'module' => $reportModule], Yii::$app->request->queryParams),
        [
            'class' => 'btn btn-danger btn-sm btn-header-action btn-generar-reporte',
            'title' => 'Generar Reporte PDF',
            'target' => '_blank',
        ]
    ) ?>
<?php endif; ?>
