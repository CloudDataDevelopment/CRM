<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Company;

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
        'id_user', 'id_company', 'id_status', 'id_lead', 'id_contact',
        'id_quote', 'id_task', 'id_report', 'id_campaign', 'id_promotion',
        'id_reservation', 'id_sales_tracking', 'id_type_contact', 'id_role',
        'id_view', 'id_auth', 'id_permission',
    ];

    // ============================================
    // 🔥 DICCIONARIO DE ETIQUETAS AMIGABLES EN ESPAÑOL
    // ============================================
    private $fieldLabels = [
        // Identificadores comunes
        'id'            => 'ID',
        'id_lead'       => 'Lead',
        'id_company'    => 'Empresa',
        'id_status'     => 'Estado',
        'id_user'       => 'Usuario',
        'id_quote'      => 'Cotización',
        'id_task'       => 'Tarea',
        'id_contact'    => 'Contacto',
        'id_report'     => 'Evaluación',
        'id_reservation'=> 'Reservación',
        'id_campaign'   => 'Campaña',
        'id_promotion'  => 'Promoción',

        // Persona
        'name'          => 'Nombre',
        'lastname'      => 'Apellido',
        'last_name'     => 'Apellido',
        'lastname1'     => 'Primer Apellido',
        'lastname2'     => 'Segundo Apellido',
        'fullname'      => 'Nombre Completo',
        'phone'         => 'Teléfono',
        'email'         => 'Correo Electrónico',
        'address'       => 'Dirección',
        'city'          => 'Ciudad',
        'country'       => 'País',
        'birthdate'     => 'Fecha de Nacimiento',
        'gender'        => 'Género',
        'role'          => 'Rol',
        'role_type'     => 'Tipo de Rol',

        // Fechas
        'created_at'    => 'Fecha de Creación',
        'updated_at'    => 'Fecha de Actualización',
        'deleted_at'    => 'Fecha de Eliminación',
        'date'          => 'Fecha',
        'date_s'        => 'Fecha de Inicio',
        'date_f'        => 'Fecha de Fin',
        'date_quote'    => 'Fecha de Cotización',
        'date_report'   => 'Fecha de Evaluación',
        'date_reservation' => 'Fecha de Reservación',
        'start_date'    => 'Fecha de Inicio',
        'end_date'      => 'Fecha de Fin',
        'hour'          => 'Hora',
        'hour_s'        => 'Hora de Inicio',
        'hour_f'        => 'Hora de Fin',
        'hour_quote'    => 'Hora de Cotización',

        // Descripciones y comentarios
        'comments'      => 'Observaciones',
        'comment'       => 'Comentario',
        'description'   => 'Descripción',
        'notes'         => 'Notas',
        'title'         => 'Título',
        'subject'       => 'Asunto',
        'content'       => 'Contenido',
        'message'       => 'Mensaje',

        // Estados y tipos
        'status'        => 'Estado',
        'status_sales'  => 'Estado de Venta',
        'type'          => 'Tipo',
        'type_contact'  => 'Tipo de Contacto',
        'category'      => 'Categoría',
        'priority'      => 'Prioridad',
        'level'         => 'Nivel',
        'stage'         => 'Etapa',

        // Montos
        'amount'        => 'Monto',
        'total'         => 'Total',
        'total_amount'  => 'Monto Total',
        'down_payment'  => 'Pago Inicial',
        'pending_payment' => 'Saldo Pendiente',
        'price'         => 'Precio',
        'cost'          => 'Costo',
        'discount'      => 'Descuento',
        'tax'           => 'Impuesto',
        'subtotal'      => 'Subtotal',

        // Empresa
        'company_name'  => 'Nombre de la Empresa',
        'domain'        => 'Dominio Web',
        'logo'          => 'Logotipo',
        'rfc'           => 'RFC',
        'tax_id'        => 'Identificación Fiscal',

        // Marketing
        'campaign_name' => 'Nombre de la Campaña',
        'promotion_name'=> 'Nombre de la Promoción',
        'budget'        => 'Presupuesto',
        'start_campaign'=> 'Inicio de Campaña',
        'end_campaign'  => 'Fin de Campaña',

        // Evaluaciones
        'report_name'   => 'Nombre de la Evaluación',
        'report_type'   => 'Tipo de Evaluación',
        'score'         => 'Puntuación',
        'result'        => 'Resultado',

        // Tareas
        'task_name'     => 'Nombre de la Tarea',
        'task_type'     => 'Tipo de Tarea',
        'due_date'      => 'Fecha de Vencimiento',

        // Contactos
        'company'       => 'Empresa',
        'position'      => 'Puesto',
        'department'    => 'Departamento',

        // Otros
        'active'        => 'Activo',
        'verified'      => 'Verificado',
        'featured'      => 'Destacado',
        'visible'       => 'Visible',
        'order'         => 'Orden',
        'code'          => 'Código',
        'reference'     => 'Referencia',
        'url'           => 'URL',
        'link'          => 'Enlace',
        'image'         => 'Imagen',
        'file'          => 'Archivo',
        'quantity'      => 'Cantidad',
        'unit'          => 'Unidad',
        'sku'           => 'SKU',
    ];

    // ============================================
    // 🔥 ETIQUETAS ESPECÍFICAS POR MÓDULO
    //    (sobrescriben las del diccionario general)
    // ============================================
    private $moduleFieldLabels = [
        'lead' => [
            'name'     => 'Nombre del Lead',
            'lastname' => 'Apellido del Lead',
            'phone'    => 'Teléfono de Contacto',
            'comments' => 'Observaciones del Lead',
        ],
        'user' => [
            'name'      => 'Nombre del Usuario',
            'lastname1' => 'Primer Apellido',
            'lastname2' => 'Segundo Apellido',
            'email'     => 'Correo del Usuario',
            'phone'     => 'Teléfono del Usuario',
        ],
        'quote' => [
            'date_quote'      => 'Fecha de Cotización',
            'total_amount'    => 'Monto Total',
            'down_payment'    => 'Pago Inicial',
            'pending_payment' => 'Saldo Pendiente',
            'comments'        => 'Observaciones de la Cotización',
        ],
        'task' => [
            'comments' => 'Descripción de la Tarea',
            'date_s'   => 'Fecha Programada',
        ],
        'contact' => [
            'name'      => 'Nombre del Contacto',
            'last_name' => 'Apellido del Contacto',
            'email'     => 'Correo del Contacto',
            'phone'     => 'Teléfono del Contacto',
        ],
        'contacts' => [
            'name'      => 'Nombre del Contacto',
            'last_name' => 'Apellido del Contacto',
            'email'     => 'Correo del Contacto',
            'phone'     => 'Teléfono del Contacto',
        ],
        'sales-tracking' => [
            'comments' => 'Observaciones del Seguimiento',
            'date_s'   => 'Fecha del Seguimiento',
            'date_f'   => 'Próxima Fecha',
            'hour'     => 'Hora del Seguimiento',
        ],
        'report' => [
            'report_name' => 'Nombre de la Evaluación',
            'report_type' => 'Tipo de Evaluación',
            'date_report' => 'Fecha de la Evaluación',
        ],
        'reservation' => [
            'name_reservation' => 'Nombre de la Reservación',
            'date_reservation' => 'Fecha de la Reservación',
            'hour_s'           => 'Hora de Inicio',
            'hour_f'           => 'Hora de Fin',
        ],
        'empresa' => [
            'name'   => 'Nombre de la Empresa',
            'domain' => 'Dominio Web',
            'phone'  => 'Teléfono de la Empresa',
            'email'  => 'Correo de la Empresa',
        ],
        'company' => [
            'name'   => 'Nombre de la Empresa',
            'domain' => 'Dominio Web',
            'phone'  => 'Teléfono de la Empresa',
            'email'  => 'Correo de la Empresa',
        ],
    ];

    // ============================================
    // VALIDACIÓN DE PERMISOS
    // ============================================
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $user = Yii::$app->user->identity;

        if (!$user) {
            return $this->redirect(['site/login']);
        }

        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para generar reportes.');
            return $this->redirect(['dashboard/index']);
        }

        return true;
    }

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
            $columns = $this->getColumns($tableName, $this->excludeColumns, $module);

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
    // DESCARGA CON FPDF
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
            $columns = $this->getColumns($tableName, $this->excludeColumns, $module);

            $title = 'Reporte General de ' . ucfirst(str_replace('-', ' ', $module));
            $filters = $this->getAppliedFilters();

            $this->generateFpdf($title, $filters, $rows, $columns, $module);

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
    private function getColumns($tableName, $exclude = [], $module = null)
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
                'label' => $this->getFriendlyLabel($name, $module),
            ];
        }

        return $columns;
    }

    // ============================================
    // 🔥 OBTENER ETIQUETA AMIGABLE
    // ============================================
    private function getFriendlyLabel($fieldName, $module = null)
    {
        // 1) Buscar etiqueta específica del módulo
        if ($module && isset($this->moduleFieldLabels[$module][$fieldName])) {
            return $this->moduleFieldLabels[$module][$fieldName];
        }

        // 2) Buscar en el diccionario general
        if (isset($this->fieldLabels[$fieldName])) {
            return $this->fieldLabels[$fieldName];
        }

        // 3) Buscar variantes (con/sin guiones bajos)
        $normalized = str_replace(['-', ' '], '_', strtolower($fieldName));
        if (isset($this->fieldLabels[$normalized])) {
            return $this->fieldLabels[$normalized];
        }

        // 4) Fallback: humanize mejorado
        return $this->humanize($fieldName);
    }

    // ============================================
    // 🔥 HUMANIZE MEJORADO (con traducción de palabras comunes)
    // ============================================
    private function humanize($name)
    {
        // Palabras comunes a traducir
        $translations = [
            'id'      => 'ID',
            'name'    => 'Nombre',
            'date'    => 'Fecha',
            'time'    => 'Hora',
            'phone'   => 'Teléfono',
            'email'   => 'Correo',
            'type'    => 'Tipo',
            'status'  => 'Estado',
            'amount'  => 'Monto',
            'total'   => 'Total',
            'price'   => 'Precio',
            'user'    => 'Usuario',
            'company' => 'Empresa',
            'lead'    => 'Lead',
            'quote'   => 'Cotización',
            'task'    => 'Tarea',
            'report'  => 'Evaluación',
            'contact' => 'Contacto',
            'reservation' => 'Reservación',
            'campaign'    => 'Campaña',
            'promotion'   => 'Promoción',
            'start'   => 'Inicio',
            'end'     => 'Fin',
            'created' => 'Creado',
            'updated' => 'Actualizado',
            'deleted' => 'Eliminado',
            'at'      => 'en',
            'by'      => 'por',
            'number'  => 'Número',
            'code'    => 'Código',
            'quantity'=> 'Cantidad',
            'description' => 'Descripción',
            'comments'=> 'Observaciones',
        ];

        // Separar por guión bajo
        $parts = explode('_', $name);

        // Traducir cada parte
        $translated = array_map(function($part) use ($translations) {
            $lower = strtolower($part);
            if (isset($translations[$lower])) {
                return $translations[$lower];
            }
            return ucfirst($lower);
        }, $parts);

        // Unir con espacio
        $result = implode(' ', $translated);

        // Limpiar "ID" duplicado al inicio
        $result = preg_replace('/^ID\s+/', 'ID ', $result);

        return $result;
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
    // GENERAR PDF (DISEÑO TIPO WORD)
    // ============================================
    private function generateFpdf($title, $filters, $rows, $columns, $module)
    {
        // Cargar FPDF
        if (!class_exists('\\FPDF')) {
            $paths = [
                Yii::getAlias('@app/fpdf/fpdf.php'),
                Yii::getAlias('@app/fpdf186/fpdf.php'),
                Yii::getAlias('@app/vendor/fpdf/fpdf.php'),
                Yii::getAlias('@app/vendor/fpdf186/fpdf.php'),
                Yii::getAlias('@app/vendor/setasign/fpdf/fpdf.php'),
                Yii::getAlias('@webroot/../fpdf/fpdf.php'),
                Yii::getAlias('@webroot/../fpdf186/fpdf.php'),
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
                throw new \Exception('FPDF no está instalado.');
            }
        }

        if (!defined('FPDF_FONTPATH')) {
            $fontPaths = [
                Yii::getAlias('@app/fpdf/font/'),
                Yii::getAlias('@app/fpdf186/font/'),
                Yii::getAlias('@app/vendor/fpdf/font/'),
                Yii::getAlias('@app/vendor/fpdf186/font/'),
            ];

            if (!empty($fpdfPath)) {
                array_unshift($fontPaths, dirname($fpdfPath) . '/font/');
            }

            foreach ($fontPaths as $fontPath) {
                if (is_dir($fontPath) && file_exists($fontPath . 'helveticab.php')) {
                    define('FPDF_FONTPATH', $fontPath);
                    break;
                }
            }
        }

        // ============================================
        // DATOS DE LA EMPRESA
        // ============================================
        $empresaId = Yii::$app->session->get('empresa_id');
        $empresa = $empresaId ? Company::findOne($empresaId) : null;

        if (!$empresa) {
            $userCompanyId = Yii::$app->user->identity->id_company ?? null;
            $empresa = $userCompanyId ? Company::findOne($userCompanyId) : null;
        }

        $empresaNombre  = $empresa ? $empresa->name : 'Mi Empresa';
        $empresaEmail   = $empresa ? ($empresa->email ?? 'contacto@empresa.com') : 'contacto@empresa.com';
        $empresaPhone   = $empresa ? ($empresa->phone ?? 'N/A') : 'N/A';
        $empresaDomain  = $empresa ? ($empresa->domain ?? 'empresa.com') : 'empresa.com';
        $empresaLogo    = ($empresa && $empresa->hasLogo()) ? $empresa->getLogoPath() : null;

        $userName = Yii::$app->user->identity->name ?? 'N/A';
        $userRole = 'Usuario';
        try {
            $auth = Yii::$app->user->identity->authentication;
            if ($auth && $auth->role) {
                $userRole = $auth->role->role_type;
            }
        } catch (\Exception $e) {}

        // ============================================
        // CREAR PDF EN VERTICAL (A4 Portrait, como Word)
        // ============================================
        $pdf = new \FPDF('P', 'mm', 'A4');
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 30);
        $pdf->SetTitle($title);
        $pdf->AddPage();

        // Ancho útil: 210mm - 15 - 15 = 180mm
        $pageW = 180;
        $leftX = 15;

        // ============================================
        // HEADER: LOGO + EMPRESA (arriba izquierda)
        // ============================================
        $pdf->SetY(15);

        // Cuadro del logo (30x25 mm)
        $logoW = 30;
        $logoH = 25;
        $logoY = $pdf->GetY();

        $pdf->SetFillColor(248, 249, 250);
        $pdf->SetDrawColor(220, 220, 220);
        $pdf->Rect($leftX, $logoY, $logoW, $logoH, 'DF');

        if (!empty($empresaLogo) && file_exists($empresaLogo)) {
            try {
                $pdf->Image($empresaLogo, $leftX + 2, $logoY + 2, $logoW - 4, $logoH - 4);
            } catch (\Exception $e) {
                $pdf->SetFont('Arial', 'B', 16);
                $pdf->SetTextColor(13, 110, 253);
                $pdf->SetXY($leftX, $logoY + 5);
                $pdf->Cell($logoW, 10, strtoupper(substr($empresaNombre, 0, 1)), 0, 0, 'C');
            }
        } else {
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->SetTextColor(13, 110, 253);
            $pdf->SetXY($leftX, $logoY + 5);
            $pdf->Cell($logoW, 10, strtoupper(substr($empresaNombre, 0, 1)), 0, 0, 'C');
        }

        // Nombre de la empresa al lado del logo
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetTextColor(44, 62, 80);
        $pdf->SetXY($leftX + $logoW + 5, $logoY);
        $pdf->Cell(100, 8, $this->toLatin1($empresaNombre), 0, 1, 'L');

        // Título del reporte
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(13, 110, 253);
        $pdf->SetX($leftX + $logoW + 5);
        $pdf->Cell(100, 5, $this->toLatin1($title), 0, 1, 'L');

        // Cuenta + Documento oficial (arriba derecha, misma línea del logo)
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(44, 62, 80);
        $pdf->SetXY(120, $logoY);
        $pdf->Cell(75, 5, $this->toLatin1('Cuenta: ' . $userName), 0, 1, 'R');

        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetXY(120, $logoY + 5);
        $pdf->Cell(75, 5, $this->toLatin1($userRole), 0, 1, 'R');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(13, 110, 253);
        $pdf->SetXY(120, $logoY + 11);
        $pdf->Cell(75, 5, 'DOCUMENTO OFICIAL', 0, 1, 'R');

        // Bajar el cursor al final del header (logo + margen)
        $pdf->SetY($logoY + $logoH + 3);

        // ============================================
        // DOBLE LÍNEA SEPARADORA (azul + dorado)
        // ============================================
        $lineY = $pdf->GetY();

        $pdf->SetDrawColor(13, 110, 253);
        $pdf->SetLineWidth(1.0);
        $pdf->Line($leftX, $lineY, $leftX + $pageW, $lineY);

        $pdf->SetDrawColor(212, 175, 55);
        $pdf->SetLineWidth(0.4);
        $pdf->Line($leftX, $lineY + 1, $leftX + $pageW, $lineY + 1);

        $pdf->SetLineWidth(0.2);
        $pdf->SetDrawColor(0, 0, 0);

        $pdf->SetY($lineY + 5);

        // ============================================
        // FILTROS APLICADOS
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
            $pdf->MultiCell(0, 5, $this->toLatin1($filterText), 0, 'L');
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
            // Calcular anchos proporcionales
            $numColWidth = 8;
            $availableWidth = $pageW - $numColWidth;

            $colWidths = [];
            $weights = [];
            $totalWeight = 0;

            foreach ($columns as $col) {
                $weight = 1;

                if (in_array($col['name'], ['name', 'last_name', 'email', 'comments', 'description', 'report_name'])) {
                    $weight = 2;
                }
                if (in_array($col['type'], ['date', 'datetime', 'timestamp'])) {
                    $weight = 1.3;
                }
                // 🔥 Si la etiqueta es muy larga, dar más peso para que quepa
                if (mb_strlen($col['label']) > 18) {
                    $weight = max($weight, 1.6);
                }
                if (mb_strlen($col['label']) > 28) {
                    $weight = max($weight, 2.0);
                }

                $weights[$col['name']] = $weight;
                $totalWeight += $weight;
            }

            foreach ($columns as $col) {
                $colWidths[$col['name']] = ($availableWidth * $weights[$col['name']]) / $totalWeight;
            }

            // Header de la tabla
            $pdf->SetFillColor(13, 110, 253);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->SetDrawColor(13, 110, 253);

            $pdf->Cell($numColWidth, 8, '#', 1, 0, 'C', true);
            foreach ($columns as $col) {
                $pdf->Cell($colWidths[$col['name']], 8, $this->toLatin1($col['label']), 1, 0, 'C', true);
            }
            $pdf->Ln();

            // Filas
            $pdf->SetTextColor(50, 50, 50);
            $pdf->SetFont('Arial', '', 6.5);
            $pdf->SetDrawColor(220, 220, 220);

            $fill = false;
            foreach ($rows as $index => $row) {
                // Calcular altura según el contenido más alto
                $lineHeight = 5;
                $maxLines = 1;

                foreach ($columns as $col) {
                    $value = is_object($row) && isset($row->{$col['name']})
                        ? $row->{$col['name']}
                        : (is_array($row) ? ($row[$col['name']] ?? null) : null);

                    $formatted = $this->formatValueForPdf($value, $col['type']);
                    $textWidth = $pdf->GetStringWidth($this->toLatin1($formatted));

                    // Cuántas líneas necesita este texto
                    $colWidthAvail = $colWidths[$col['name']] - 2;
                    $lines = max(1, ceil($textWidth / $colWidthAvail));
                    $maxLines = max($maxLines, $lines);
                }

                $rowHeight = $maxLines * $lineHeight;

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

                    $x = $pdf->GetX();
                    $y = $pdf->GetY();

                    $pdf->Rect($x, $y, $colWidths[$col['name']], $rowHeight, 'DF');
                    $pdf->SetXY($x + 1, $y + 1);
                    $pdf->MultiCell($colWidths[$col['name']] - 2, $lineHeight, $this->toLatin1($formatted), 0, 'L');

                    $pdf->SetXY($x + $colWidths[$col['name']], $y);
                }

                $pdf->Ln();
                $fill = !$fill;
            }
        }

        // ============================================
        // FOOTER (en la parte inferior de la página)
        // ============================================
        $footerY = 250;

        // Verificar si necesitamos una nueva página para el footer
        if ($pdf->GetY() > $footerY - 20) {
            $pdf->AddPage();
            $footerY = 250;
        }

        $pdf->SetY($footerY);

        // Línea dorada
        $pdf->SetDrawColor(212, 175, 55);
        $pdf->SetLineWidth(0.4);
        $pdf->Line($leftX, $footerY, $leftX + $pageW, $footerY);

        // Línea azul
        $pdf->SetDrawColor(13, 110, 253);
        $pdf->SetLineWidth(0.8);
        $pdf->Line($leftX, $footerY + 1, $leftX + $pageW, $footerY + 1);

        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.2);

        $pdf->SetY($footerY + 3);

        // Datos de la empresa (izquierda)
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(44, 62, 80);
        $pdf->Cell(80, 4, $this->toLatin1($empresaNombre), 0, 1, 'L');

        $pdf->SetFont('Arial', '', 7);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(80, 3.5, $this->toLatin1('Tel: ' . $empresaPhone), 0, 1, 'L');
        $pdf->Cell(80, 3.5, $this->toLatin1('Email: ' . $empresaEmail), 0, 1, 'L');
        $pdf->Cell(80, 3.5, $this->toLatin1('Web: ' . $empresaDomain), 0, 1, 'L');

        // Volver arriba para el texto central y el rectángulo
        $pdf->SetY($footerY + 10);
        $pdf->SetFont('Arial', 'I', 7);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 4, $this->toLatin1('Reporte general | Generado por: ' . $userName), 0, 1, 'C');

        // Rectángulo azul con fecha (derecha)
        $rectW = 45;
        $rectH = 12;
        $rectX = $leftX + $pageW - $rectW;
        $rectY = $footerY + 3;

        $pdf->SetFillColor(13, 110, 253);
        $pdf->SetDrawColor(13, 110, 253);
        $pdf->Rect($rectX, $rectY, $rectW, $rectH, 'DF');

        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetXY($rectX, $rectY + 2);
        $pdf->Cell($rectW, 4, 'FECHA DE EMISION', 0, 2, 'C');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY($rectX, $rectY + 6);
        $pdf->Cell($rectW, 4, date('d/m/Y H:i'), 0, 2, 'C');

        // ============================================
        // DESCARGAR
        // ============================================
        $filename = 'reporte_' . $module . '_' . date('Ymd_His') . '.pdf';
        $pdf->Output('D', $filename);
    }

    // ============================================
    // FORMATEAR VALOR PARA PDF
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
                return mb_strlen($clean) > 100
                    ? mb_substr($clean, 0, 100) . '...'
                    : $clean;

            default:
                return (string)$value;
        }
    }

    // ============================================
    // CONVERTIR A LATIN1
    // ============================================
    private function toLatin1($text)
    {
        if (empty($text)) {
            return '';
        }

        $converted = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text);

        if ($converted === false) {
            $converted = str_replace(
                ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ', 'ü', 'Ü', '¿', '¡', '€'],
                ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N', 'u', 'U', '?', '!', 'E'],
                $text
            );
        }

        return $converted;
    }
}