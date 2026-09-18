    <?php

    use yii\helpers\Html;
    use yii\helpers\Url;

    /** @var string $title */
    /** @var array $filters */
    /** @var array $rows */
    /** @var array $columns */
    /** @var string $module */

    $this->title = $title;

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
            $userRole = $auth->role->role_type;
        }
    } catch (\Exception $e) {}
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= Html::encode($title) ?></title>
        <style>
            * { box-sizing: border-box; margin: 0; padding: 0; }

            body {
                font-family: Arial, Helvetica, sans-serif;
                font-size: 11px;
                color: #333;
                background: #f0f2f5;
                padding: 20px;
            }

            .report-container {
                background: #fff;
                max-width: 1400px;
                margin: 0 auto;
                padding: 30px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.08);
                border-radius: 8px;
            }

            /* ============================================
            HEADER MEMBRETADO
            ============================================ */
            .report-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                padding-bottom: 15px;
                margin-bottom: 20px;
            }

            .report-header-left {
                display: flex;
                gap: 15px;
                align-items: flex-start;
                flex: 1;
            }

            .logo-box {
                width: 100px;
                height: 80px;
                border: 1px solid #e0e0e0;
                border-radius: 8px;
                background: #f8f9fa;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                flex-shrink: 0;
            }

            .logo-box img {
                max-width: 90%;
                max-height: 90%;
                object-fit: contain;
            }

            .logo-box .logo-initial {
                font-size: 36px;
                font-weight: bold;
                color: #11223F;
            }

            .company-info {
                flex: 1;
                min-width: 0;
            }

            .company-name {
                font-size: 18px;
                font-weight: bold;
                color: #C5A059;
                margin-bottom: 4px;
            }

            .report-title {
                font-size: 14px;
                font-weight: bold;
                color: #11223F;
                margin-bottom: 2px;
            }

            .report-header-right {
                text-align: right;
                flex-shrink: 0;
            }

            .account-name {
                font-size: 13px;
                font-weight: bold;
                color: #2c3e50;
                margin-bottom: 3px;
            }

            .account-role {
                font-size: 11px;
                color: #6c757d;
                margin-bottom: 8px;
            }

            .official-badge {
                display: inline-block;
                font-size: 11px;
                font-weight: bold;
                color: #11223F;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            /* ============================================
            DOBLE LÍNEA SEPARADORA (azul + dorado)
            ============================================ */
            .separator-double {
                height: 4px;
                background: linear-gradient(to bottom,
                    #11223F 0%, #11223F 60%,
                    #d4af37 60%, #d4af37 100%);
                margin-bottom: 20px;
                border-radius: 2px;
            }

            /* ============================================
            FILTROS Y RESUMEN
            ============================================ */
            .report-filters {
                background: #f0f4ff;
                padding: 10px 15px;
                border-left: 4px solid #11223F;
                margin-bottom: 15px;
                font-size: 11px;
                border-radius: 4px;
            }

            .filter-tag {
                background: #11223F;
                color: #fff;
                padding: 3px 10px;
                border-radius: 3px;
                margin-right: 6px;
                margin-bottom: 4px;
                display: inline-block;
                font-size: 10px;
            }

            .summary-box {
                border: 1px solid #e0e0e0;
                padding: 12px 18px;
                margin-bottom: 20px;
                background-color: #f8f9fa;
                font-size: 12px;
                border-radius: 6px;
                display: flex;
                gap: 30px;
                flex-wrap: wrap;
            }

            .summary-box strong {
                color: #11223F;
            }

            /* ============================================
            TABLA
            ============================================ */
            table.report-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
                font-size: 10px;
            }

            table.report-table th {
                background-color: #11223F;
                color: #fff;
                padding: 8px 6px;
                text-align: left;
                border: 1px solid #11223F;
                font-weight: bold;
                font-size: 10px;
                position: sticky;
                top: 0;
            }

            table.report-table td {
                padding: 6px;
                border: 1px solid #e0e0e0;
                vertical-align: top;
                word-wrap: break-word;
            }

            table.report-table tr:nth-child(even) {
                background-color: #f8f9fa;
            }

            table.report-table tr:hover {
                background-color: #e7f1ff;
            }

            .no-data {
                text-align: center;
                color: #999;
                margin-top: 60px;
                padding: 40px;
                font-style: italic;
                font-size: 14px;
            }

            /* ============================================
            FOOTER MEMBRETADO
            ============================================ */
            .report-footer {
                margin-top: 30px;
                padding-top: 15px;
            }

            .separator-footer {
                height: 4px;
                background: linear-gradient(to bottom,
                    #d4af37 0%, #d4af37 40%,
                    #11223F 40%, #11223F 100%);
                margin-bottom: 12px;
                border-radius: 2px;
            }

            .footer-content {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 20px;
            }

            .footer-company {
                flex: 1;
            }

            .footer-company-name {
                font-size: 11px;
                font-weight: bold;
                color: #2c3e50;
                margin-bottom: 4px;
            }

            .footer-company-data {
                font-size: 9px;
                color: #6c757d;
                line-height: 1.5;
            }

            .footer-company-data i {
                width: 12px;
                color: #11223F;
            }

            .footer-center {
                text-align: center;
                font-size: 9px;
                color: #6c757d;
                font-style: italic;
                flex: 1;
                padding-top: 20px;
            }

            .footer-date-box {
                background: #11223F;
                color: #fff;
                padding: 8px 15px;
                border-radius: 6px;
                text-align: center;
                flex-shrink: 0;
                min-width: 140px;
            }

            .footer-date-label {
                font-size: 8px;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                opacity: 0.9;
                margin-bottom: 2px;
            }

            .footer-date-value {
                font-size: 11px;
                font-weight: bold;
            }

            /* ============================================
            BARRA DE ACCIONES FLOTANTE
            ============================================ */
            .action-bar {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                display: flex;
                gap: 10px;
                background: #fff;
                padding: 10px;
                border-radius: 10px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            }

            .btn-action {
                padding: 12px 20px;
                border: none;
                border-radius: 6px;
                font-size: 13px;
                font-weight: bold;
                cursor: pointer;
                color: #fff;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                transition: all 0.2s;
                border: none;
            }

            .btn-download { background: #198754; }
            .btn-download:hover {
                background: #146c43;
                color: white;
                transform: translateY(-1px);
                text-decoration: none;
            }

            .btn-print { background: #11223F; }
            .btn-print:hover {
                background: #11223F;
                transform: translateY(-1px);
            }

            .btn-close-window { background: #6c757d; }
            .btn-close-window:hover { background: #5a6268; }

            /* ============================================
            ESTILOS DE IMPRESIÓN
            ============================================ */
            @media print {
                body {
                    background: #fff;
                    padding: 0;
                    font-size: 10px;
                }

                .action-bar { display: none !important; }

                .report-container {
                    box-shadow: none;
                    padding: 0;
                    max-width: 100%;
                    border-radius: 0;
                }

                .logo-box {
                    border: 1px solid #999 !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                .separator-double,
                .separator-footer {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                .footer-date-box {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                table.report-table th {
                    background-color: #ddd !important;
                    color: #000 !important;
                    border: 1px solid #666 !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                table.report-table td {
                    border: 1px solid #ccc !important;
                }

                table.report-table tr:nth-child(even) {
                    background-color: #f5f5f5 !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                @page {
                    size: A4 landscape;
                    margin: 10mm;
                }
            }

            /* Responsive */
            @media screen and (max-width: 768px) {
                .report-container { padding: 15px; }

                .report-header {
                    flex-direction: column;
                    gap: 15px;
                }

                .report-header-right {
                    text-align: left;
                }

                .footer-content {
                    flex-direction: column;
                }

                .action-bar {
                    top: auto;
                    bottom: 20px;
                    right: 20px;
                }

                .btn-action span { display: none; }
                .btn-action {
                    padding: 14px;
                    border-radius: 50%;
                }
            }
        </style>
    </head>
    <body>

    <!-- ============================================
        BARRA DE ACCIONES FLOTANTE
        ============================================ -->
    <div class="action-bar">
        <a href="<?= $downloadUrl ?>" class="btn-action btn-download" title="Descargar PDF">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
            </svg>
            <span>Descargar PDF</span>
        </a>

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

    <!-- ============================================
        CONTENIDO DEL REPORTE
        ============================================ -->
    <div class="report-container">

        <!-- HEADER MEMBRETADO -->
        <div class="report-header">
            <div class="report-header-left">
                <!-- Cuadro del logo -->
                <div class="logo-box">
                    <?php if ($empresaLogo): ?>
                        <img src="<?= $empresaLogo ?>" alt="<?= Html::encode($empresaNombre) ?>">
                    <?php else: ?>
                        <div class="logo-initial"><?= strtoupper(substr($empresaNombre, 0, 1)) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Nombre de la empresa + título -->
                <div class="company-info">
                    <div class="company-name"><?= Html::encode($empresaNombre) ?></div>
                    <div class="report-title"><?= Html::encode($title) ?></div>
                </div>
            </div>

            <!-- Cuenta + Documento Oficial -->
            <div class="report-header-right">
                <div class="account-name"><?= Html::encode($userName) ?></div>
                <div class="account-role"><?= Html::encode($userRole) ?></div>
                <div class="official-badge">Documento Oficial</div>
            </div>
        </div>

        <!-- DOBLE LÍNEA SEPARADORA (azul + dorado) -->
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
            <!-- Línea separadora (dorado + azul) -->
            <div class="separator-footer"></div>

            <div class="footer-content">
                <!-- Datos de la empresa (izquierda) -->
                <div class="footer-company">
                    <div class="footer-company-name"><?= Html::encode($empresaNombre) ?></div>
                    <div class="footer-company-data">
                        <div><i class="fas fa-phone"></i> <?= Html::encode($empresaPhone) ?></div>
                        <div><i class="fas fa-envelope"></i> <?= Html::encode($empresaEmail) ?></div>
                        <div><i class="fas fa-globe"></i> <?= Html::encode($empresaDomain) ?></div>
                    </div>
                </div>

                <!-- Texto central -->
                <div class="footer-center">
                    Reporte general | Generado por: <?= Html::encode($userName) ?>
                </div>

                <!-- Rectángulo azul con fecha (derecha) -->
                <div class="footer-date-box">
                    <div class="footer-date-label">Fecha de Emisión</div>
                    <div class="footer-date-value"><?= date('d/m/Y H:i') ?></div>
                </div>
            </div>
        </div>

    </div>

    <script>
    window.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
            e.preventDefault();
            window.print();
        }
    });
    </script>

    </body>
    </html>