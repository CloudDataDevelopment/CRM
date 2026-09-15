<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;

class ExportController extends Controller
{
    public $layout = false;

    private $modules = [
        'task'           => ['model' => 'app\models\Task',           'company' => null],
        'lead'           => ['model' => 'app\models\Lead',           'company' => 'id_company'],
        'user'           => ['model' => 'app\models\User',           'company' => 'id_company'],
        'quote'          => ['model' => 'app\models\Quote',          'company' => null],
        'sales-tracking' => ['model' => 'app\models\SalesTracking',  'company' => null],
        'reservation'    => ['model' => 'app\models\Reservation',    'company' => 'id_company'],
        'contact'        => ['model' => 'app\models\Contacts',       'company' => 'id_company'],
        'report'         => ['model' => 'app\models\Report',         'company' => 'id_company'],
        'campaign'       => ['model' => 'app\models\Campaign',       'company' => 'id_company'],
        'promotion'      => ['model' => 'app\models\Promotion',      'company' => 'id_company'],
        'company'        => ['model' => 'app\models\Company',        'company' => null],
        'contacts'       => ['model' => 'app\models\Contacts',       'company' => 'id_company'],
        'marketing'      => ['model' => 'app\models\Campaign',       'company' => 'id_company'],
        'empresa'        => ['model' => 'app\models\Company',        'company' => null],
    ];

    private $excludeColumns = [
        'password', 'password_hash', 'auth_key', 'access_token',
        'created_by', 'updated_by', 'deleted_at',
    ];

    // ============================================
    // VISTA HTML DEL REPORTE
    // ============================================
    public function actionPdf($module = null)
    {
        try {
            $user = Yii::$app->user->identity;
            if (!$user) {
                return $this->redirect(['site/login']);
            }

            if (!$module || !isset($this->modules[$module])) {
                Yii::$app->session->setFlash('error', 'Módulo no válido para exportar.');
                return $this->redirect(['dashboard/index']);
            }

            $config = $this->modules[$module];
            $modelClass = $config['model'];

            if (!class_exists($modelClass)) {
                throw new \Exception("Modelo $modelClass no encontrado.");
            }

            $query = $this->buildQuery($modelClass, $config, $user);
            $rows = $query->limit(5000)->all();

            $tableName = $modelClass::tableName();
            $columns = $this->getColumns($tableName, $this->excludeColumns);

            $title = 'Reporte General de ' . ucfirst(str_replace('-', ' ', $module));
            $filters = $this->getAppliedFilters();

            return $this->render('pdf', [
                'title'    => $title,
                'filters'  => $filters,
                'rows'     => $rows,
                'columns'  => $columns,
                'module'   => $module,
            ]);

        } catch (\Exception $e) {
            Yii::error('Error en ExportController: ' . $e->getMessage(), 'export');
            Yii::$app->session->setFlash('error', 'Error al generar el reporte: ' . $e->getMessage());
            return $this->redirect(['dashboard/index']);
        }
    }

    // ============================================
    // 🔥 DESCARGA REAL CON FPDF
    // ============================================
    public function actionDownload($module = null)
    {
        try {
            $user = Yii::$app->user->identity;
            if (!$user) {
                return $this->redirect(['site/login']);
            }

            if (!$module || !isset($this->modules[$module])) {
                Yii::$app->session->setFlash('error', 'Módulo no válido para exportar.');
                return $this->redirect(['dashboard/index']);
            }

            $config = $this->modules[$module];
            $modelClass = $config['model'];

            if (!class_exists($modelClass)) {
                throw new \Exception("Modelo $modelClass no encontrado.");
            }

            $query = $this->buildQuery($modelClass, $config, $user);
            $rows = $query->limit(5000)->all();

            $tableName = $modelClass::tableName();
            $columns = $this->getColumns($tableName, $this->excludeColumns);

            $title = 'Reporte General de ' . ucfirst(str_replace('-', ' ', $module));
            $filters = $this->getAppliedFilters();

            // 🔥 Generar PDF con FPDF
            $this->generateFpdf($title, $filters, $rows, $columns, $module);

            // Terminar ejecución
            Yii::$app->end();

        } catch (\Exception $e) {
            Yii::error('Error en actionDownload: ' . $e->getMessage(), 'export');
            Yii::$app->session->setFlash('error', 'Error al descargar el PDF: ' . $e->getMessage());
            return $this->redirect(['dashboard/index']);
        }
    }

    // ============================================
    // CONSTRUIR QUERY
    // ============================================
    private function buildQuery($modelClass, $config, $user)
    {
        $query = $modelClass::find();

        if (!empty($config['company'])) {
            $empresaId = Yii::$app->session->get('empresa_id');

            if ($user->isSuperAdmin() && !empty($empresaId)) {
                $query->andWhere([$config['company'] => $empresaId]);
            } elseif (!$user->isSuperAdmin() && !empty($user->id_company)) {
                $query->andWhere([$config['company'] => $user->id_company]);
            }
        }

        $search       = Yii::$app->request->get('search', '');
        $status       = Yii::$app->request->get('status', '');
        $type         = Yii::$app->request->get('type', '');
        $fecha_inicio = Yii::$app->request->get('fecha_inicio', '');
        $fecha_fin    = Yii::$app->request->get('fecha_fin', '');

        $tableSchema = Yii::$app->db->getTableSchema($modelClass::tableName(), true);
        if (!$tableSchema) {
            return $query;
        }

        if (!empty($search)) {
            $orConditions = ['or'];
            foreach ($tableSchema->columns as $col) {
                if (in_array($col->type, ['string', 'text'])) {
                    $orConditions[] = ['like', $col->name, $search];
                }
            }
            if (count($orConditions) > 1) {
                $query->andWhere($orConditions);
            }
        }

        if (!empty($status) && isset($tableSchema->columns['id_status'])) {
            $statusModel = \app\models\Status::find()->where(['status' => $status])->one();
            if ($statusModel) {
                $query->andWhere(['id_status' => $statusModel->id_status]);
            }
        }

        if (!empty($type) && isset($tableSchema->columns['id_type_contact'])) {
            $typeModel = \app\models\TypeContact::find()->where(['type_contact' => $type])->one();
            if ($typeModel) {
                $query->andWhere(['id_type_contact' => $typeModel->id_type_contact]);
            }
        }

        $dateColumns = ['created_at', 'date_s', 'date_quote', 'date_report', 'date_reservation', 'start_date'];
        foreach ($dateColumns as $dateCol) {
            if (isset($tableSchema->columns[$dateCol])) {
                if (!empty($fecha_inicio)) {
                    $query->andWhere(['>=', $dateCol, $fecha_inicio]);
                }
                if (!empty($fecha_fin)) {
                    $query->andWhere(['<=', $dateCol, $fecha_fin]);
                }
                break;
            }
        }

        $primaryKeys = $tableSchema->primaryKey;
        if (!empty($primaryKeys)) {
            $query->orderBy([$primaryKeys[0] => SORT_DESC]);
        }

        return $query;
    }

    // ============================================
    // OBTENER COLUMNAS
    // ============================================
    private function getColumns($tableName, $exclude = [])
    {
        $schema = Yii::$app->db->getTableSchema($tableName, true);
        if (!$schema) {
            return [];
        }

        $columns = [];
        foreach ($schema->columns as $name => $column) {
            if (in_array($name, $exclude)) {
                continue;
            }

            $columns[$name] = [
                'name'  => $name,
                'type'  => $column->type,
                'label' => $this->humanize($name),
            ];
        }

        return $columns;
    }

    private function humanize($name)
    {
        return ucwords(str_replace(['_', '-'], ' ', $name));
    }

    public static function formatValue($value, $type)
    {
        if ($value === null || $value === '') {
            return '<em style="color:#999;">—</em>';
        }

        switch ($type) {
            case 'date':
                $ts = strtotime($value);
                return $ts ? date('d/m/Y', $ts) : htmlspecialchars($value);

            case 'datetime':
            case 'timestamp':
                $ts = strtotime($value);
                return $ts ? date('d/m/Y H:i', $ts) : htmlspecialchars($value);

            case 'time':
                $ts = strtotime($value);
                return $ts ? date('H:i', $ts) : htmlspecialchars($value);

            case 'boolean':
                return $value ? 'Sí' : 'No';

            case 'integer':
            case 'bigint':
            case 'smallint':
                return number_format((int)$value);

            case 'decimal':
            case 'float':
            case 'double':
                return number_format((float)$value, 2);

            case 'text':
                $clean = strip_tags((string)$value);
                return mb_strlen($clean) > 150
                    ? htmlspecialchars(mb_substr($clean, 0, 150)) . '…'
                    : htmlspecialchars($clean);

            default:
                return htmlspecialchars((string)$value);
        }
    }

    private function getAppliedFilters()
    {
        $filters = [];
        $map = [
            'search'       => 'Búsqueda',
            'status'       => 'Estado',
            'type'         => 'Tipo',
            'fecha_inicio' => 'Desde',
            'fecha_fin'    => 'Hasta',
        ];

        foreach ($map as $key => $label) {
            $value = Yii::$app->request->get($key, '');
            if (!empty($value)) {
                $filters[$label] = $value;
            }
        }

        return $filters;
    }

    // ============================================
    // 🔥 GENERAR PDF CON FPDF (CORREGIDO)
    // ============================================
    private function generateFpdf($title, $filters, $rows, $columns, $module)
    {
        // 🔥 PASO 1: Cargar FPDF desde múltiples ubicaciones
        if (!class_exists('\\FPDF')) {
            $paths = [
                // En hosting: /home/clouddatacancun/crm.clouddatacancun.com/
                Yii::getAlias('@app/fpdf/fpdf.php'),
                Yii::getAlias('@app/fpdf186/fpdf.php'),
                Yii::getAlias('@app/vendor/fpdf/fpdf.php'),
                Yii::getAlias('@app/vendor/fpdf186/fpdf.php'),
                Yii::getAlias('@app/vendor/setasign/fpdf/fpdf.php'),

                // Fallbacks absolutos
                Yii::getAlias('@webroot/../fpdf/fpdf.php'),
                Yii::getAlias('@webroot/../fpdf186/fpdf.php'),
                Yii::getAlias('@webroot/../vendor/fpdf/fpdf.php'),
                Yii::getAlias('@webroot/../vendor/fpdf186/fpdf.php'),
            ];

            $loaded = false;
            $fpdfPath = null;

            foreach ($paths as $path) {
                if (file_exists($path)) {
                    require_once $path;
                    $loaded = true;
                    $fpdfPath = $path;
                    break;
                }
            }

            if (!$loaded) {
                throw new \Exception('FPDF no está instalado. Sube la carpeta fpdf completa a la raíz del proyecto.');
            }
        }

        // 🔥 PASO 2: Definir FPDF_FONTPATH si no está definido
        if (!defined('FPDF_FONTPATH')) {
            // Detectar dónde está la carpeta font/
            $fontPaths = [
                // Junto al fpdf.php (por defecto)
                Yii::getAlias('@app/fpdf/font/'),
                Yii::getAlias('@app/fpdf186/font/'),
                Yii::getAlias('@app/vendor/fpdf/font/'),
                Yii::getAlias('@app/vendor/fpdf186/font/'),
                Yii::getAlias('@app/vendor/setasign/fpdf/font/'),
                Yii::getAlias('@webroot/../fpdf/font/'),
                Yii::getAlias('@webroot/../fpdf186/font/'),
                Yii::getAlias('@webroot/../vendor/fpdf/font/'),
                Yii::getAlias('@webroot/../vendor/fpdf186/font/'),
                Yii::getAlias('@webroot/../vendor/setasign/fpdf/font/'),
            ];

            // Si tenemos el path de fpdf.php cargado, buscar font/ junto a él
            if (!empty($fpdfPath)) {
                $fontPathNearFpdf = dirname($fpdfPath) . '/font/';
                array_unshift($fontPaths, $fontPathNearFpdf);
            }

            $fontPathFound = null;
            foreach ($fontPaths as $fontPath) {
                if (is_dir($fontPath) && file_exists($fontPath . 'helveticab.php')) {
                    $fontPathFound = $fontPath;
                    break;
                }
            }

            if (!$fontPathFound) {
                throw new \Exception(
                    'La carpeta font/ de FPDF no se encuentra. ' .
                    'Asegúrate de subir la carpeta font/ completa junto a fpdf.php. ' .
                    'Ruta buscada: ' . implode(', ', $fontPaths)
                );
            }

            define('FPDF_FONTPATH', $fontPathFound);
        }

        // 🔥 PASO 3: Crear PDF en horizontal (A4 landscape)
        $pdf = new \FPDF('L', 'mm', 'A4');
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();

        // ============================================
        // HEADER
        // ============================================
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetTextColor(13, 110, 253);
        $pdf->Cell(0, 8, $this->toLatin1($title), 0, 1, 'L');

        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 5, 'Sistema CRM', 0, 1, 'L');

        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 5, 'Generado: ' . date('d/m/Y H:i') . '  |  Usuario: ' . $this->toLatin1(Yii::$app->user->identity->name ?? 'N/A'), 0, 1, 'L');

        // Línea separadora
        $pdf->SetDrawColor(13, 110, 253);
        $pdf->SetLineWidth(0.5);
        $y = $pdf->GetY() + 1;
        $pdf->Line(10, $y, 287, $y);
        $pdf->Ln(3);

        // ============================================
        // FILTROS
        // ============================================
        if (!empty($filters)) {
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetTextColor(50, 50, 50);
            $pdf->Cell(0, 5, 'Filtros aplicados:', 0, 1, 'L');

            $pdf->SetFont('Arial', '', 8);
            $pdf->SetTextColor(80, 80, 80);

            $filterText = '';
            foreach ($filters as $label => $value) {
                $filterText .= $label . ': ' . $value . '   ';
            }
            $pdf->Cell(0, 5, $this->toLatin1($filterText), 0, 1, 'L');
            $pdf->Ln(2);
        }

        // ============================================
        // RESUMEN
        // ============================================
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(50, 50, 50);
        $pdf->Cell(0, 5, 'Total de registros: ' . count($rows) . '   |   Columnas: ' . count($columns), 0, 1, 'L');
        $pdf->Ln(2);

        // ============================================
        // TABLA
        // ============================================
        if (empty($rows)) {
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->SetTextColor(150, 150, 150);
            $pdf->Cell(0, 20, 'No hay registros que coincidan con los filtros.', 0, 1, 'C');
        } else {
            // 🔥 Calcular anchos de columna
            $pageWidth = 277; // A4 landscape (297mm) - márgenes
            $numColWidth = 8;
            $availableWidth = $pageWidth - $numColWidth;

            $colWidths = [];
            $weights = [];
            $totalWeight = 0;

            foreach ($columns as $col) {
                $weight = 1;

                // Más peso para textos largos
                if (in_array($col['name'], ['name', 'last_name', 'email', 'comments', 'description', 'report_name'])) {
                    $weight = 2;
                }
                // Menos peso para IDs
                if (strpos($col['name'], 'id_') === 0) {
                    $weight = 0.6;
                }
                // Fechas
                if (in_array($col['type'], ['date', 'datetime', 'timestamp'])) {
                    $weight = 0.9;
                }

                $weights[$col['name']] = $weight;
                $totalWeight += $weight;
            }

            foreach ($columns as $col) {
                $colWidths[$col['name']] = ($availableWidth * $weights[$col['name']]) / $totalWeight;
            }

            // Header
            $pdf->SetFillColor(13, 110, 253);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->SetDrawColor(13, 110, 253);

            $pdf->Cell($numColWidth, 7, '#', 1, 0, 'C', true);
            foreach ($columns as $col) {
                $pdf->Cell($colWidths[$col['name']], 7, $this->toLatin1($col['label']), 1, 0, 'L', true);
            }
            $pdf->Ln();

            // Filas
            $pdf->SetTextColor(50, 50, 50);
            $pdf->SetFont('Arial', '', 6.5);
            $pdf->SetDrawColor(220, 220, 220);

            $fill = false;
            foreach ($rows as $index => $row) {
                $rowHeight = 5;

                // Alternar colores
                if ($fill) {
                    $pdf->SetFillColor(248, 249, 250);
                } else {
                    $pdf->SetFillColor(255, 255, 255);
                }

                $pdf->Cell($numColWidth, $rowHeight, $index + 1, 1, 0, 'C', true);

                foreach ($columns as $col) {
                    $value = is_object($row) && isset($row->{$col['name']})
                        ? $row->{$col['name']}
                        : (is_array($row) ? ($row[$col['name']] ?? null) : null);

                    $formatted = $this->formatValueForPdf($value, $col['type']);

                    $pdf->Cell(
                        $colWidths[$col['name']],
                        $rowHeight,
                        $this->toLatin1($formatted),
                        1,
                        0,
                        'L',
                        true
                    );
                }
                $pdf->Ln();
                $fill = !$fill;
            }
        }

        // ============================================
        // FOOTER
        // ============================================
        $pdf->SetY(-15);
        $pdf->SetFont('Arial', 'I', 7);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->Cell(0, 5, 'Pagina ' . $pdf->PageNo(), 0, 0, 'C');

        // ============================================
        // DESCARGAR PDF
        // ============================================
        $filename = 'reporte_' . $module . '_' . date('Ymd_His') . '.pdf';

        // 🔥 Esto fuerza la descarga
        $pdf->Output('D', $filename);
    }

    // ============================================
    // FORMATEAR VALOR PARA PDF (sin HTML)
    // ============================================
    private function formatValueForPdf($value, $type)
    {
        if ($value === null || $value === '') {
            return '—';
        }

        switch ($type) {
            case 'date':
                $ts = strtotime($value);
                return $ts ? date('d/m/Y', $ts) : (string)$value;

            case 'datetime':
            case 'timestamp':
                $ts = strtotime($value);
                return $ts ? date('d/m/Y H:i', $ts) : (string)$value;

            case 'time':
                $ts = strtotime($value);
                return $ts ? date('H:i', $ts) : (string)$value;

            case 'boolean':
                return $value ? 'Si' : 'No';

            case 'integer':
            case 'bigint':
            case 'smallint':
                return number_format((int)$value);

            case 'decimal':
            case 'float':
            case 'double':
                return number_format((float)$value, 2);

            case 'text':
                $clean = strip_tags((string)$value);
                return mb_strlen($clean) > 80
                    ? mb_substr($clean, 0, 80) . '...'
                    : $clean;

            default:
                return (string)$value;
        }
    }

    // ============================================
    // CONVERTIR A LATIN1 (FPDF lo requiere)
    // ============================================
    private function toLatin1($text)
    {
        if (empty($text)) {
            return '';
        }

        // Convertir de UTF-8 a ISO-8859-1 (Latin1)
        $converted = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text);

        if ($converted === false) {
            // Fallback: reemplazar manualmente
            $converted = str_replace(
                ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ', 'ü', 'Ü', '¿', '¡', '€'],
                ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N', 'u', 'U', '?', '!', 'E'],
                $text
            );
        }

        return $converted;
    }
}