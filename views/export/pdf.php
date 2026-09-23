<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var string $title */
/** @var array $filters */
/** @var array $rows */
/** @var array $columns */
/** @var string $module */

$this->title = $title;

// 🔥 Leer el CSS directamente del archivo (CSS embebido)
$cssPath = Yii::getAlias('@webroot/css/export-pdf.css');
$cssContent = '';
if (file_exists($cssPath)) {
    $cssContent = file_get_contents($cssPath);
} else {
    $cssPathAlt = Yii::getAlias('@app/../css/export-pdf.css');
    if (file_exists($cssPathAlt)) {
        $cssContent = file_get_contents($cssPathAlt);
    } else {
        Yii::error('No se encontró export-pdf.css en: ' . $cssPath . ' ni en: ' . $cssPathAlt, 'export-pdf');
    }
}

$downloadUrl = Url::to(array_merge(
    ['/export/download', 'module' => $module],
    Yii::$app->request->queryParams
));

// 🔥 Obtener datos de la empresa
$empresaId = Yii::$app->session->get('empresa_id');
$empresa = $empresaId ? \app\models\Company::findOne($empresaId) : null;

if (!$empresa) {
    $userCompanyId = Yii::$app->user->identity->id_company ?? null;
    $empresa = $userCompanyId ? \app\models\Company::findOne($userCompanyId) : null;
}

$empresaNombre  = $empresa ? $empresa->name : 'Mi Empresa';
$empresaEmail   = $empresa ? ($empresa->email ?? 'contacto@empresa.com') : 'contacto@empresa.com';
$empresaPhone   = $empresa ? ($empresa->phone ?? 'N/A') : 'N/A';
$empresaDomain  = $empresa ? ($empresa->domain ?? 'empresa.com') : 'empresa.com';
$empresaLogo    = ($empresa && $empresa->hasLogo()) ? $empresa->getLogoUrl() : null;

$userName = Yii::$app->user->identity->name ?? 'N/A';
$userRole = 'Usuario';
try {
    $auth = Yii::$app->user->identity->authentication;
    if ($auth && $auth->role) {
        $userRole = $auth->role->role_type ?? $auth->role->name ?? 'Usuario';
    }
} catch (\Exception $e) {}

// 🔥 Nombre sugerido para el PDF
$moduleTitle = ucfirst(str_replace('-', ' ', $module));
$pdfFileName = 'Reporte_' . $moduleTitle . '_' . date('Y-m-d_His');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode($pdfFileName) ?></title>

    <!-- 🔥 FontAwesome para los iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- 🔥 CSS embebido directamente -->
    <style>
        <?= $cssContent ?>
    </style>
</head>
<body>

<!-- ============================================ -->
<!-- BARRA DE ACCIONES FLOTANTE                    -->
<!-- ============================================ -->
<div class="action-bar">
    <button type="button" class="btn-action btn-download" title="Guardar como PDF" onclick="abrirDialogoImpresion()">
        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
        </svg>
        <span>Guardar como PDF</span>
    </button>

    <button class="btn-action btn-print" onclick="window.print();">
        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
            <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
        </svg>
        <span>Imprimir</span>
    </button>

    <button class="btn-action btn-close-window" onclick="window.close();">
        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
        </svg>
        <span>Cerrar</span>
    </button>
</div>

<!-- ============================================ -->
<!-- CONTENIDO DEL REPORTE                         -->
<!-- ============================================ -->
<div class="report-container">

    <!-- HEADER MEMBRETADO -->
    <div class="report-header">
        <div class="report-header-left">
            <div class="logo-box">
                <?php if ($empresaLogo): ?>
                    <img src="<?= $empresaLogo ?>" alt="<?= Html::encode($empresaNombre) ?>">
                <?php else: ?>
                    <div class="logo-initial"><?= strtoupper(substr($empresaNombre, 0, 1)) ?></div>
                <?php endif; ?>
            </div>

            <div class="company-info">
                <div class="company-name"><?= Html::encode($empresaNombre) ?></div>
                <div class="report-title"><?= Html::encode($title) ?></div>
            </div>
        </div>

        <div class="report-header-right">
            <div class="account-name"><?= Html::encode($userName) ?></div>
            <div class="account-role"><?= Html::encode($userRole) ?></div>
            <div class="official-badge">Documento Oficial</div>
        </div>
    </div>

    <!-- DOBLE LÍNEA SEPARADORA -->
    <div class="separator-double"></div>

    <!-- FILTROS -->
    <?php if (!empty($filters)): ?>
        <div class="report-filters">
            <strong>Filtros aplicados:</strong>
            <?php foreach ($filters as $label => $value): ?>
                <span class="filter-tag"><?= Html::encode($label) ?>: <?= Html::encode($value) ?></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- RESUMEN -->
    <div class="summary-box">
        <div><strong>Total de registros:</strong> <?= count($rows) ?></div>
        <div><strong>Columnas:</strong> <?= count($columns) ?></div>
        <div><strong>Módulo:</strong> <?= Html::encode(ucfirst(str_replace('-', ' ', $module))) ?></div>
    </div>

    <!-- TABLA -->
    <?php if (empty($rows)): ?>
        <div class="no-data">
            <p>📭 No hay registros que coincidan con los filtros.</p>
        </div>
    <?php else: ?>
        <table class="report-table">
            <thead>
                <tr>
                    <th width="4%">#</th>
                    <?php foreach ($columns as $col): ?>
                        <th><?= Html::encode($col['label']) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $index => $row): ?>
                    <tr>
                        <td style="text-align:center; font-weight:bold;">
                            <?= $index + 1 ?>
                        </td>
                        <?php foreach ($columns as $col): ?>
                            <?php
                            $value = is_object($row) && isset($row->{$col['name']})
                                ? $row->{$col['name']}
                                : (is_array($row) ? ($row[$col['name']] ?? null) : null);
                            ?>
                            <td><?= \app\controllers\ExportController::formatValue($value, $col['type']) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- FOOTER MEMBRETADO -->
    <div class="report-footer">
        <div class="separator-footer"></div>

        <div class="footer-content">
            <div class="footer-company">
                <div class="footer-company-name"><?= Html::encode($empresaNombre) ?></div>
                <div class="footer-company-data">
                    <div><i class="fas fa-phone"></i> <?= Html::encode($empresaPhone) ?></div>
                    <div><i class="fas fa-envelope"></i> <?= Html::encode($empresaEmail) ?></div>
                    <div><i class="fas fa-globe"></i> <?= Html::encode($empresaDomain) ?></div>
                </div>
            </div>

            <div class="footer-center">
                Reporte general | Generado por: <?= Html::encode($userName) ?>
            </div>

            <div class="footer-date-box">
                <div class="footer-date-label">Fecha de Emisión</div>
                <div class="footer-date-value"><?= date('d/m/Y H:i') ?></div>
            </div>
        </div>
    </div>

</div>

<!-- ============================================ -->
<!-- SCRIPTS                                        -->
<!-- ============================================ -->
<script>
// 🔥 Abrir diálogo de impresión para guardar como PDF
function abrirDialogoImpresion() {
    // Pequeño delay para asegurar que todo esté renderizado
    setTimeout(function() {
        window.print();
    }, 100);
}

// 🔥 Atajo Ctrl+P / Cmd+P
window.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
        e.preventDefault();
        window.print();
    }
});

// 🔥 Al cargar, asegurar que el título esté bien para el nombre del PDF
document.addEventListener('DOMContentLoaded', function() {
    // El navegador usará document.title como nombre sugerido del PDF
    console.log('📄 Reporte listo para guardar como PDF');
});
</script>

</body>
</html>