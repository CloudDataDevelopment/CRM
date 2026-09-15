<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\models\SalesTracking;
use app\models\Quote;
use app\models\Lead;
use app\models\Task;
use app\models\Status;
use app\components\ErrorManager;

class CalendarController extends Controller
{
    public $layout = 'main';

    // ============================================
    // 🔥 RANGO DE AÑOS PERMITIDOS
    // ============================================
    const YEAR_START = 2020;
    const YEAR_END = 2035;

    public function actionIndex()
    {
        $user = Yii::$app->user->identity;
        $empresaId = Yii::$app->session->get('empresa_id');
        
        if (!$user) {
            return $this->redirect(['site/login']);
        }

        if ($user->isSuperAdmin() && empty($empresaId)) {
            Yii::$app->session->setFlash('warning', 'Por favor, selecciona una empresa para continuar.');
            return $this->redirect(['empresa/index']);
        }

        // ============================================
        // 🔥 VALIDAR MES (ACEPTA CUALQUIER MES DEL RANGO)
        // ============================================
        $selectedMonth = Yii::$app->request->get('month', date('Y-m'));

        // Validar formato YYYY-MM
        if (!preg_match('/^(\d{4})-(\d{2})$/', $selectedMonth, $matches)) {
            $selectedMonth = date('Y-m');
        } else {
            $year = (int)$matches[1];
            $month = (int)$matches[2];

            // Validar rango de año
            if ($year < self::YEAR_START || $year > self::YEAR_END) {
                $selectedMonth = date('Y-m');
            }
            // Validar rango de mes
            elseif ($month < 1 || $month > 12) {
                $selectedMonth = date('Y-m');
            }
        }

        // ============================================
        // 🔥 VALIDAR DÍA
        // ============================================
        $selectedDay = Yii::$app->request->get('day', date('Y-m-d'));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDay)) {
            $selectedDay = date('Y-m-d');
        } else {
            $dayYear = (int)substr($selectedDay, 0, 4);
            if ($dayYear < self::YEAR_START || $dayYear > self::YEAR_END) {
                $selectedDay = date('Y-m-d');
            }
        }

        // Si el día seleccionado no pertenece al mes seleccionado, ajustarlo
        if (substr($selectedDay, 0, 7) !== $selectedMonth) {
            // Mantener el mismo día del mes si es posible, si no usar el día 1
            $dayNum = (int)substr($selectedDay, 8, 2);
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int)substr($selectedMonth, 5, 2), (int)substr($selectedMonth, 0, 4));
            if ($dayNum < 1 || $dayNum > $daysInMonth) {
                $dayNum = 1;
            }
            $selectedDay = $selectedMonth . '-' . str_pad($dayNum, 2, '0', STR_PAD_LEFT);
        }

        // ============================================
        // 🔥 OBTENER EVENTOS DEL MES
        // ============================================
        $events = $this->getMonthEvents($selectedMonth, $user, $empresaId);

        // ============================================
        // 🔥 LISTA DE MESES DISPONIBLES (TODO EL RANGO)
        // ============================================
        $months = $this->getMonthOptions();

        return $this->render('index', [
            'months' => $months,
            'selectedMonth' => $selectedMonth,
            'selectedDay' => $selectedDay,
            'events' => $events,
            'isAdmin' => $user->isAdmin(),
            'isAgent' => $user->isAgent(),
            'isSuperAdmin' => $user->isSuperAdmin(),
        ]);
    }

    /**
     * 🔥 GENERAR LISTA DE MESES DEL RANGO COMPLETO
     * @return array
     */
    private function getMonthOptions()
    {
        $months = [];
        $mesesEspanol = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        for ($year = self::YEAR_START; $year <= self::YEAR_END; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                $value = sprintf('%04d-%02d', $year, $month);
                $label = $mesesEspanol[$month] . ' ' . $year;
                $months[$value] = $label;
            }
        }

        return $months;
    }

    /**
     * 🔥 ACCIÓN PARA OBTENER DETALLE DEL DÍA (AJAX)
     */
    public function actionDayDetail($day)
    {
        Yii::$app->response->format = Response::FORMAT_HTML;
        
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');
            
            if (!$user) {
                return $this->renderPartial('_day_detail', ['error' => 'Usuario no autenticado']);
            }
            
            // Validar día
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $day)) {
                return $this->renderPartial('_day_detail', ['error' => 'Fecha inválida']);
            }
            
            // Obtener eventos del día
            $events = $this->getDayEvents($day, $user, $empresaId);
            
            // Ordenar por hora
            usort($events, function($a, $b) {
                $timeA = strtotime($a['date']);
                $timeB = strtotime($b['date']);
                return $timeA - $timeB;
            });
            
            return $this->renderPartial('_day_detail', [
                'events' => $events,
                'day' => $day,
                'isAdmin' => $user->isAdmin(),
                'isAgent' => $user->isAgent(),
                'isSuperAdmin' => $user->isSuperAdmin(),
            ]);
            
        } catch (\Exception $e) {
            Yii::error('Error en actionDayDetail: ' . $e->getMessage(), 'calendar');
            return $this->renderPartial('_day_detail', ['error' => 'Error al cargar las actividades: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtener eventos de un día específico
     */
    private function getDayEvents($day, $user, $empresaId)
    {
        $startDate = $day . ' 00:00:00';
        $endDate = $day . ' 23:59:59';
        
        $events = [];
        
        // ============================================
        // SEGUIMIENTOS
        // ============================================
        $trackingSql = "SELECT 
            st.id_sales_tracking,
            st.comments,
            st.date_s,
            st.id_status,
            st.id_lead,
            l.name as lead_name,
            l.lastname as lead_lastname,
            l.phone as lead_phone,
            s.status as status_name
        FROM Sales_tracking st
        LEFT JOIN Lead l ON st.id_lead = l.id_lead
        LEFT JOIN Status s ON st.id_status = s.id_status
        WHERE st.date_s BETWEEN :start_date AND :end_date";
        
        $params = [
            ':start_date' => $startDate,
            ':end_date' => $endDate,
        ];
        
        if ($user->isAgent()) {
            $trackingSql .= " AND st.id_user = :id_user";
            $params[':id_user'] = $user->id_user;
        } elseif ($user->isSuperAdmin()) {
            if (!empty($empresaId)) {
                $trackingSql .= " AND l.id_company = :id_company";
                $params[':id_company'] = $empresaId;
            }
        } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
            $trackingSql .= " AND l.id_company = :id_company";
            $params[':id_company'] = $user->id_company;
        }
        
        $trackingSql .= " ORDER BY st.date_s ASC";
        
        $trackings = SalesTracking::findBySql($trackingSql, $params)->asArray()->all();
        foreach ($trackings as $tracking) {
            $leadName = trim($tracking['lead_name'] . ' ' . $tracking['lead_lastname']);
            if (empty($leadName)) {
                $leadName = 'Lead eliminado';
            }
            $events[] = [
                'id' => 'tracking_' . $tracking['id_sales_tracking'],
                'date' => $tracking['date_s'],
                'title' => $tracking['comments'] ?? 'Seguimiento',
                'type' => 'seguimiento',
                'status' => $tracking['status_name'] ?? 'Sin estado',
                'lead_name' => $leadName,
                'lead_phone' => $tracking['lead_phone'] ?? 'N/A',
                'color' => $this->getStatusColor($tracking['status_name'] ?? ''),
                'icon' => 'fa-phone',
                'url' => ['sales-tracking/details', 'id' => $tracking['id_sales_tracking']],
            ];
        }
        
        // ============================================
        // PRÓXIMOS SEGUIMIENTOS
        // ============================================
        $nextTrackingSql = "SELECT 
            st.id_sales_tracking,
            st.comments,
            st.date_f,
            st.id_status,
            st.id_lead,
            l.name as lead_name,
            l.lastname as lead_lastname,
            l.phone as lead_phone,
            s.status as status_name
        FROM Sales_tracking st
        LEFT JOIN Lead l ON st.id_lead = l.id_lead
        LEFT JOIN Status s ON st.id_status = s.id_status
        WHERE st.date_f IS NOT NULL
        AND st.date_f BETWEEN :start_date AND :end_date";
        
        if ($user->isAgent()) {
            $nextTrackingSql .= " AND st.id_user = :id_user";
        } elseif ($user->isSuperAdmin()) {
            if (!empty($empresaId)) {
                $nextTrackingSql .= " AND l.id_company = :id_company";
            }
        } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
            $nextTrackingSql .= " AND l.id_company = :id_company";
        }
        
        $nextTrackings = SalesTracking::findBySql($nextTrackingSql, $params)->asArray()->all();
        foreach ($nextTrackings as $tracking) {
            $leadName = trim($tracking['lead_name'] . ' ' . $tracking['lead_lastname']);
            if (empty($leadName)) {
                $leadName = 'Lead eliminado';
            }
            $events[] = [
                'id' => 'next_tracking_' . $tracking['id_sales_tracking'],
                'date' => $tracking['date_f'],
                'title' => 'Próximo: ' . ($tracking['comments'] ?? 'Seguimiento'),
                'type' => 'proximo_seguimiento',
                'status' => $tracking['status_name'] ?? 'Sin estado',
                'lead_name' => $leadName,
                'lead_phone' => $tracking['lead_phone'] ?? 'N/A',
                'color' => '#ff6b6b',
                'icon' => 'fa-clock',
                'url' => ['sales-tracking/details', 'id' => $tracking['id_sales_tracking']],
            ];
        }
        
        // ============================================
        // COTIZACIONES
        // ============================================
        $quoteSql = "SELECT 
            q.id_quote,
            q.comments,
            q.date_quote,
            q.id_status,
            q.total_amount,
            q.id_lead,
            l.name as lead_name,
            l.lastname as lead_lastname,
            l.phone as lead_phone,
            s.status as status_name
        FROM Quote q
        LEFT JOIN Lead l ON q.id_lead = l.id_lead
        LEFT JOIN Status s ON q.id_status = s.id_status
        WHERE q.date_quote BETWEEN :start_date AND :end_date";
        
        if ($user->isAgent()) {
            $quoteSql .= " AND l.id_user = :id_user";
        } elseif ($user->isSuperAdmin()) {
            if (!empty($empresaId)) {
                $quoteSql .= " AND l.id_company = :id_company";
            }
        } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
            $quoteSql .= " AND l.id_company = :id_company";
        }
        
        $quotes = Quote::findBySql($quoteSql, $params)->asArray()->all();
        foreach ($quotes as $quote) {
            $leadName = trim($quote['lead_name'] . ' ' . $quote['lead_lastname']);
            if (empty($leadName)) {
                $leadName = 'Lead eliminado';
            }
            $events[] = [
                'id' => 'quote_' . $quote['id_quote'],
                'date' => $quote['date_quote'],
                'title' => 'Cotización: ' . ($quote['comments'] ?? 'Sin comentarios'),
                'type' => 'cotizacion',
                'status' => $quote['status_name'] ?? 'Sin estado',
                'lead_name' => $leadName,
                'lead_phone' => $quote['lead_phone'] ?? 'N/A',
                'color' => $this->getQuoteColor($quote['status_name'] ?? ''),
                'icon' => 'fa-file-invoice',
                'total_amount' => $quote['total_amount'],
                'url' => ['quote/details', 'id' => $quote['id_quote']],
            ];
        }
        
        // ============================================
        // LEADS NUEVOS
        // ============================================
        $leadSql = "SELECT 
            l.id_lead,
            l.name,
            l.lastname,
            l.created_at,
            l.id_status,
            l.phone,
            s.status as status_name
        FROM Lead l
        LEFT JOIN Status s ON l.id_status = s.id_status
        WHERE l.created_at BETWEEN :start_date AND :end_date";
        
        if ($user->isAgent()) {
            $leadSql .= " AND l.id_user = :id_user";
        } elseif ($user->isSuperAdmin()) {
            if (!empty($empresaId)) {
                $leadSql .= " AND l.id_company = :id_company";
            }
        } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
            $leadSql .= " AND l.id_company = :id_company";
        }
        
        $leads = Lead::findBySql($leadSql, $params)->asArray()->all();
        foreach ($leads as $lead) {
            $events[] = [
                'id' => 'lead_' . $lead['id_lead'],
                'date' => $lead['created_at'],
                'title' => 'Nuevo Lead: ' . $lead['name'] . ' ' . $lead['lastname'],
                'type' => 'lead',
                'status' => $lead['status_name'] ?? 'Sin estado',
                'lead_name' => $lead['name'] . ' ' . $lead['lastname'],
                'lead_phone' => $lead['phone'] ?? 'N/A',
                'color' => $this->getLeadColor($lead['status_name'] ?? ''),
                'icon' => 'fa-user',
                'url' => ['lead/details', 'id' => $lead['id_lead']],
            ];
        }
        
        return $events;
    }

    /**
     * Obtener eventos del mes
     */
    private function getMonthEvents($month, $user, $empresaId)
    {
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $events = [];

        // ============================================
        // SEGUIMIENTOS
        // ============================================
        $trackingSql = "SELECT 
            st.id_sales_tracking,
            st.comments,
            st.date_s,
            st.id_status,
            st.id_lead,
            l.name as lead_name,
            l.lastname as lead_lastname,
            l.phone as lead_phone,
            s.status as status_name
        FROM Sales_tracking st
        LEFT JOIN Lead l ON st.id_lead = l.id_lead
        LEFT JOIN Status s ON st.id_status = s.id_status
        WHERE st.date_s BETWEEN :start_date AND :end_date";
        
        $params = [
            ':start_date' => $startDate,
            ':end_date' => $endDate,
        ];

        if ($user->isAgent()) {
            $trackingSql .= " AND st.id_user = :id_user";
            $params[':id_user'] = $user->id_user;
        } elseif ($user->isSuperAdmin()) {
            if (!empty($empresaId)) {
                $trackingSql .= " AND l.id_company = :id_company";
                $params[':id_company'] = $empresaId;
            }
        } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
            $trackingSql .= " AND l.id_company = :id_company";
            $params[':id_company'] = $user->id_company;
        }

        $trackingSql .= " ORDER BY st.date_s ASC";

        $trackings = SalesTracking::findBySql($trackingSql, $params)->asArray()->all();
        foreach ($trackings as $tracking) {
            $leadName = trim($tracking['lead_name'] . ' ' . $tracking['lead_lastname']);
            if (empty($leadName)) {
                $leadName = 'Lead eliminado';
            }
            $events[] = [
                'id' => 'tracking_' . $tracking['id_sales_tracking'],
                'date' => $tracking['date_s'],
                'title' => $tracking['comments'] ?? 'Seguimiento',
                'type' => 'seguimiento',
                'status' => $tracking['status_name'] ?? 'Sin estado',
                'lead_name' => $leadName,
                'lead_phone' => $tracking['lead_phone'] ?? 'N/A',
                'color' => $this->getStatusColor($tracking['status_name'] ?? ''),
                'icon' => 'fa-phone',
                'url' => ['sales-tracking/details', 'id' => $tracking['id_sales_tracking']],
            ];
        }

        // ============================================
        // PRÓXIMOS SEGUIMIENTOS
        // ============================================
        $nextTrackingSql = "SELECT 
            st.id_sales_tracking,
            st.comments,
            st.date_f,
            st.id_status,
            st.id_lead,
            l.name as lead_name,
            l.lastname as lead_lastname,
            l.phone as lead_phone,
            s.status as status_name
        FROM Sales_tracking st
        LEFT JOIN Lead l ON st.id_lead = l.id_lead
        LEFT JOIN Status s ON st.id_status = s.id_status
        WHERE st.date_f IS NOT NULL
        AND st.date_f BETWEEN :start_date AND :end_date";
        
        if ($user->isAgent()) {
            $nextTrackingSql .= " AND st.id_user = :id_user";
        } elseif ($user->isSuperAdmin()) {
            if (!empty($empresaId)) {
                $nextTrackingSql .= " AND l.id_company = :id_company";
            }
        } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
            $nextTrackingSql .= " AND l.id_company = :id_company";
        }

        $nextTrackingSql .= " ORDER BY st.date_f ASC";

        $nextTrackings = SalesTracking::findBySql($nextTrackingSql, $params)->asArray()->all();
        foreach ($nextTrackings as $tracking) {
            $leadName = trim($tracking['lead_name'] . ' ' . $tracking['lead_lastname']);
            if (empty($leadName)) {
                $leadName = 'Lead eliminado';
            }
            $events[] = [
                'id' => 'next_tracking_' . $tracking['id_sales_tracking'],
                'date' => $tracking['date_f'],
                'title' => 'Próximo: ' . ($tracking['comments'] ?? 'Seguimiento'),
                'type' => 'proximo_seguimiento',
                'status' => $tracking['status_name'] ?? 'Sin estado',
                'lead_name' => $leadName,
                'lead_phone' => $tracking['lead_phone'] ?? 'N/A',
                'color' => '#ff6b6b',
                'icon' => 'fa-clock',
                'url' => ['sales-tracking/details', 'id' => $tracking['id_sales_tracking']],
            ];
        }

        // ============================================
        // COTIZACIONES
        // ============================================
        $quoteSql = "SELECT 
            q.id_quote,
            q.comments,
            q.date_quote,
            q.id_status,
            q.total_amount,
            q.id_lead,
            l.name as lead_name,
            l.lastname as lead_lastname,
            l.phone as lead_phone,
            s.status as status_name
        FROM Quote q
        LEFT JOIN Lead l ON q.id_lead = l.id_lead
        LEFT JOIN Status s ON q.id_status = s.id_status
        WHERE q.date_quote BETWEEN :start_date AND :end_date";
        
        if ($user->isAgent()) {
            $quoteSql .= " AND l.id_user = :id_user";
        } elseif ($user->isSuperAdmin()) {
            if (!empty($empresaId)) {
                $quoteSql .= " AND l.id_company = :id_company";
            }
        } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
            $quoteSql .= " AND l.id_company = :id_company";
        }

        $quoteSql .= " ORDER BY q.date_quote ASC";

        $quotes = Quote::findBySql($quoteSql, $params)->asArray()->all();
        foreach ($quotes as $quote) {
            $leadName = trim($quote['lead_name'] . ' ' . $quote['lead_lastname']);
            if (empty($leadName)) {
                $leadName = 'Lead eliminado';
            }
            $events[] = [
                'id' => 'quote_' . $quote['id_quote'],
                'date' => $quote['date_quote'],
                'title' => 'Cotización: ' . ($quote['comments'] ?? 'Sin comentarios'),
                'type' => 'cotizacion',
                'status' => $quote['status_name'] ?? 'Sin estado',
                'lead_name' => $leadName,
                'lead_phone' => $quote['lead_phone'] ?? 'N/A',
                'color' => $this->getQuoteColor($quote['status_name'] ?? ''),
                'icon' => 'fa-file-invoice',
                'total_amount' => $quote['total_amount'],
                'url' => ['quote/details', 'id' => $quote['id_quote']],
            ];
        }

        // ============================================
        // LEADS NUEVOS
        // ============================================
        $leadSql = "SELECT 
            l.id_lead,
            l.name,
            l.lastname,
            l.created_at,
            l.id_status,
            l.phone,
            s.status as status_name
        FROM Lead l
        LEFT JOIN Status s ON l.id_status = s.id_status
        WHERE l.created_at BETWEEN :start_date AND :end_date";
        
        if ($user->isAgent()) {
            $leadSql .= " AND l.id_user = :id_user";
        } elseif ($user->isSuperAdmin()) {
            if (!empty($empresaId)) {
                $leadSql .= " AND l.id_company = :id_company";
            }
        } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
            $leadSql .= " AND l.id_company = :id_company";
        }

        $leadSql .= " ORDER BY l.created_at ASC";

        $leads = Lead::findBySql($leadSql, $params)->asArray()->all();
        foreach ($leads as $lead) {
            $events[] = [
                'id' => 'lead_' . $lead['id_lead'],
                'date' => $lead['created_at'],
                'title' => 'Nuevo Lead: ' . $lead['name'] . ' ' . $lead['lastname'],
                'type' => 'lead',
                'status' => $lead['status_name'] ?? 'Sin estado',
                'lead_name' => $lead['name'] . ' ' . $lead['lastname'],
                'lead_phone' => $lead['phone'] ?? 'N/A',
                'color' => $this->getLeadColor($lead['status_name'] ?? ''),
                'icon' => 'fa-user',
                'url' => ['lead/details', 'id' => $lead['id_lead']],
            ];
        }

        // Ordenar por fecha
        usort($events, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        return $events;
    }

    // ============================================
    // MÉTODOS DE COLORES
    // ============================================
    
    private function getStatusColor($status)
    {
        $colors = [
            'pendiente' => '#f6c23e',
            'programado' => '#36b9cc',
            'en progreso' => '#4e73df',
            'en_progreso' => '#4e73df',
            'completado' => '#1cc88a',
            'cancelado' => '#e74a3b',
            'contactado' => '#0dcaf0',
            'interesado' => '#ffc107',
            'cotizacion' => '#0d6efd',
            'revision_cotizacion' => '#6c757d',
            'negociacion' => '#212529',
            'cliente' => '#198754',
            'perdido' => '#dc3545',
            'nuevo' => '#0dcaf0',
        ];
        
        $statusLower = strtolower(trim($status));
        return $colors[$statusLower] ?? '#6c757d';
    }

    private function getQuoteColor($status)
    {
        $colors = [
            'pendiente' => '#ffc107',
            'aprobada' => '#198754',
            'rechazada' => '#dc3545',
            'pagada' => '#0d6efd',
            'cancelada' => '#6c757d',
        ];
        $statusLower = strtolower(trim($status));
        return $colors[$statusLower] ?? '#6c757d';
    }

    private function getLeadColor($status)
    {
        $colors = [
            'nuevo' => '#0dcaf0',
            'contactado' => '#0d6efd',
            'interesado' => '#ffc107',
            'cliente' => '#198754',
            'perdido' => '#dc3545',
        ];
        $statusLower = strtolower(trim($status));
        return $colors[$statusLower] ?? '#6c757d';
    }
}