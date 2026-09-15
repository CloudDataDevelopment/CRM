<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Lead;
use app\models\SalesTracking;
use app\models\Task;
use app\models\Status;
use app\models\Reservation;
use app\models\Quote;

class DashboardController extends Controller
{
    public $layout = 'main';

    public function actionIndex()
    {
        // 🔥 OBTENER LA EMPRESA SELECCIONADA EN SESIÓN
        $user = Yii::$app->user->identity;
        $empresaId = Yii::$app->session->get('empresa_id');
        
        // Si es Super Admin y no tiene empresa seleccionada
        if ($user->isSuperAdmin() && empty($empresaId)) {
            Yii::$app->session->setFlash('warning', 'Por favor, selecciona una empresa para continuar.');
            return $this->redirect(['empresa/index']);
        }

        // ========== OBTENER ID DE ESTADOS ==========
        $statusInactivo = Status::find()->where(['status' => 'Inactivo'])->one();
        $idInactivo = $statusInactivo ? $statusInactivo->id_status : null;
        
        $statusCliente = Status::find()->where(['status' => 'Cliente'])->one();
        $idCliente = $statusCliente ? $statusCliente->id_status : null;
        
        $statusNuevo = Status::find()->where(['status' => 'Nuevo'])->one();
        $idNuevo = $statusNuevo ? $statusNuevo->id_status : null;
        
        $statusContactado = Status::find()->where(['status' => 'Contactado'])->one();
        $idContactado = $statusContactado ? $statusContactado->id_status : null;
        
        $statusCalificado = Status::find()->where(['status' => 'Calificado'])->one();
        $idCalificado = $statusCalificado ? $statusCalificado->id_status : null;

        // 🔥 OBTENER ID DEL ESTADO COMPLETADO
        $statusCompletado = Status::find()->where(['status' => 'completado'])->one();
        $idCompletado = $statusCompletado ? $statusCompletado->id_status : null;

        // ========== LEADS - FILTRAR POR EMPRESA ==========
        $queryLeads = Lead::find();
        
        if ($user->isSuperAdmin() && !empty($empresaId)) {
            $queryLeads->where(['Lead.id_company' => $empresaId]);
        } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
            $queryLeads->where(['Lead.id_company' => $user->id_company]);
        } elseif ($user->isAgent()) {
            $queryLeads->where(['Lead.id_user' => $user->id_user]);
        }
        
        if ($idInactivo) {
            $queryLeads->andWhere(['<>', 'Lead.id_status', $idInactivo]);
        }
        $totalLeads = $queryLeads->count();

        $semanaPasada = date('Y-m-d', strtotime('-7 days'));
        $queryNuevosLeads = Lead::find()
            ->where(['>=', 'Lead.created_at', $semanaPasada]);
        
        if ($user->isSuperAdmin() && !empty($empresaId)) {
            $queryNuevosLeads->andWhere(['Lead.id_company' => $empresaId]);
        } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
            $queryNuevosLeads->andWhere(['Lead.id_company' => $user->id_company]);
        } elseif ($user->isAgent()) {
            $queryNuevosLeads->andWhere(['Lead.id_user' => $user->id_user]);
        }
        
        if ($idInactivo) {
            $queryNuevosLeads->andWhere(['<>', 'Lead.id_status', $idInactivo]);
        }
        $nuevosLeads = $queryNuevosLeads->count();

        // ========== LEADS POR ESTADO - GRÁFICA DE PASTEL ==========
        $estadosPermitidos = ['Nuevo', 'Contactado', 'Procesando', 'Cancelado'];
        
        $leadsLabels = [];
        $leadsData = [];
        $leadsColors = [];
        
        $colorMap = [
            'Nuevo' => '#00b209',
            'Contactado' => '#002afa',
            'Procesando' => '#ffc107',
            'Cancelado' => '#ff0000',
        ];
        
        foreach ($estadosPermitidos as $estado) {
            $status = Status::find()->where(['status' => $estado])->one();
            if ($status) {
                $query = Lead::find()
                    ->where(['Lead.id_status' => $status->id_status]);
                
                if ($user->isSuperAdmin() && !empty($empresaId)) {
                    $query->andWhere(['Lead.id_company' => $empresaId]);
                } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
                    $query->andWhere(['Lead.id_company' => $user->id_company]);
                } elseif ($user->isAgent()) {
                    $query->andWhere(['Lead.id_user' => $user->id_user]);
                }
                
                $count = $query->count();
                $leadsLabels[] = $estado;
                $leadsData[] = $count;
                $leadsColors[] = $colorMap[$estado] ?? '#ccc';
            } else {
                $leadsLabels[] = $estado;
                $leadsData[] = 0;
                $leadsColors[] = $colorMap[$estado] ?? '#ccc';
            }
        }

        // ========== 🔥 LEADS NUEVOS POR MES (para contador debajo de la gráfica) ==========
        $mesesNombresCorto = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $anioActualLeads = date('Y');
        $leadsMesesData = [];
        $totalLeadsAnio = 0;

        for ($mes = 1; $mes <= 12; $mes++) {
            $queryLeadsMes = Lead::find()
                ->where(['>=', 'Lead.created_at', sprintf('%04d-%02d-01 00:00:00', $anioActualLeads, $mes)])
                ->andWhere(['<=', 'Lead.created_at', sprintf('%04d-%02d-%02d 23:59:59', $anioActualLeads, $mes, cal_days_in_month(CAL_GREGORIAN, $mes, $anioActualLeads))]);
            
            if ($user->isSuperAdmin() && !empty($empresaId)) {
                $queryLeadsMes->andWhere(['Lead.id_company' => $empresaId]);
            } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
                $queryLeadsMes->andWhere(['Lead.id_company' => $user->id_company]);
            } elseif ($user->isAgent()) {
                $queryLeadsMes->andWhere(['Lead.id_user' => $user->id_user]);
            }
            
            if ($idInactivo) {
                $queryLeadsMes->andWhere(['<>', 'Lead.id_status', $idInactivo]);
            }
            
            $count = (int)$queryLeadsMes->count();
            $leadsMesesData[] = [
                'mes' => $mesesNombresCorto[$mes - 1],
                'mes_num' => $mes,
                'count' => $count
            ];
            $totalLeadsAnio += $count;
        }

        // ========== VENTAS / CITAS ==========
        $totalCitas = SalesTracking::find()->count();
        
        $citasHoy = SalesTracking::find()
            ->where(['date_s' => date('Y-m-d')])
            ->count();

        if ($idCliente) {
            $queryVentasCerradas = Lead::find()
                ->where(['Lead.id_status' => $idCliente]);
            
            if ($user->isSuperAdmin() && !empty($empresaId)) {
                $queryVentasCerradas->andWhere(['Lead.id_company' => $empresaId]);
            } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
                $queryVentasCerradas->andWhere(['Lead.id_company' => $user->id_company]);
            } elseif ($user->isAgent()) {
                $queryVentasCerradas->andWhere(['Lead.id_user' => $user->id_user]);
            }
            
            $ventasCerradas = $queryVentasCerradas->count();
        } else {
            $ventasCerradas = 0;
        }

        // ============================================
        // 🔥 VENTAS POR MES - BASADO EN COTIZACIONES COMPLETADAS
        // ============================================
        $ventasPorMes = [];
        
        try {
            $queryVentasPorMes = Quote::find()
                ->alias('q')
                ->leftJoin('Lead l', 'q.id_lead = l.id_lead')
                ->select(['MONTH(q.date_quote) as mes', 'COUNT(*) as total', 'SUM(q.total_amount) as monto'])
                ->where(['>=', 'q.date_quote', date('Y-01-01') . ' 00:00:00'])
                ->andWhere(['<=', 'q.date_quote', date('Y-12-31') . ' 23:59:59']);
            
            if ($idCompletado) {
                $queryVentasPorMes->andWhere(['q.id_status' => $idCompletado]);
            }
            
            if ($user->isSuperAdmin() && !empty($empresaId)) {
                $queryVentasPorMes->andWhere(['l.id_company' => $empresaId]);
            } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
                $queryVentasPorMes->andWhere(['l.id_company' => $user->id_company]);
            } elseif ($user->isAgent()) {
                $queryVentasPorMes->andWhere(['l.id_user' => $user->id_user]);
            }
            
            $queryVentasPorMes->groupBy('MONTH(q.date_quote)')
                ->orderBy(['MONTH(q.date_quote)' => SORT_ASC]);
            
            $ventasPorMes = $queryVentasPorMes->asArray()->all();
            
        } catch (\Exception $e) {
            Yii::error('Error al calcular ventas por mes: ' . $e->getMessage(), 'dashboard');
            $ventasPorMes = [];
        }

        // ============================================
        // 🔥 CÁLCULO DE METAS Y OBJETIVOS
        // ============================================
        
        // 🔥 META MENSUAL (configurable)
        $metaMensual = 100000;
        
        // 🔥 MES ACTUAL
        $mesActual = (int)date('n');
        $anioActual = date('Y');

        // 🔥 VENTAS DEL MES ACTUAL
        $queryVentasMesActual = Quote::find()
            ->alias('q')
            ->leftJoin('Lead l', 'q.id_lead = l.id_lead')
            ->where(['MONTH(q.date_quote)' => $mesActual])
            ->andWhere(['YEAR(q.date_quote)' => $anioActual]);

        if ($idCompletado) {
            $queryVentasMesActual->andWhere(['q.id_status' => $idCompletado]);
        }

        if ($user->isSuperAdmin() && !empty($empresaId)) {
            $queryVentasMesActual->andWhere(['l.id_company' => $empresaId]);
        } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
            $queryVentasMesActual->andWhere(['l.id_company' => $user->id_company]);
        } elseif ($user->isAgent()) {
            $queryVentasMesActual->andWhere(['l.id_user' => $user->id_user]);
        }

        $ventasMesActual = $queryVentasMesActual->sum('q.total_amount') ?? 0;

        // 🔥 PORCENTAJE ALCANZADO
        $porcentajeAlcanzado = $metaMensual > 0 ? round(($ventasMesActual / $metaMensual) * 100, 1) : 0;
        $porcentajeAlcanzado = min($porcentajeAlcanzado, 100);

        // 🔥 MONTO RESTANTE
        $montoRestante = max(0, $metaMensual - $ventasMesActual);

        // 🔥 ESTADO DE LA META
        $metaAlcanzada = $ventasMesActual >= $metaMensual;

        // ============================================
        // 🔥 ACTUAL VS TARGET (Últimos 6 meses)
        // ============================================
        $mesesLabels = [];
        $actualData = [];
        $targetData = [];

        for ($i = 5; $i >= 0; $i--) {
            $fecha = strtotime("-$i months");
            $mes = (int)date('n', $fecha);
            $anio = date('Y', $fecha);
            $mesLabel = date('M', $fecha);
            
            $mesesLabels[] = ucfirst($mesLabel);
            
            $queryVentas = Quote::find()
                ->alias('q')
                ->leftJoin('Lead l', 'q.id_lead = l.id_lead')
                ->where(['MONTH(q.date_quote)' => $mes])
                ->andWhere(['YEAR(q.date_quote)' => $anio]);
            
            if ($idCompletado) {
                $queryVentas->andWhere(['q.id_status' => $idCompletado]);
            }
            
            if ($user->isSuperAdmin() && !empty($empresaId)) {
                $queryVentas->andWhere(['l.id_company' => $empresaId]);
            } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
                $queryVentas->andWhere(['l.id_company' => $user->id_company]);
            } elseif ($user->isAgent()) {
                $queryVentas->andWhere(['l.id_user' => $user->id_user]);
            }
            
            $ventasMes = $queryVentas->sum('q.total_amount') ?? 0;
            
            $actualData[] = (int)$ventasMes;
            $targetData[] = $metaMensual;
        }

        // ============================================
        // 🔥 VENTAS TOTALES DEL AÑO
        // ============================================
        $queryVentasAnio = Quote::find()
            ->alias('q')
            ->leftJoin('Lead l', 'q.id_lead = l.id_lead')
            ->where(['YEAR(q.date_quote)' => $anioActual]);

        if ($idCompletado) {
            $queryVentasAnio->andWhere(['q.id_status' => $idCompletado]);
        }

        if ($user->isSuperAdmin() && !empty($empresaId)) {
            $queryVentasAnio->andWhere(['l.id_company' => $empresaId]);
        } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
            $queryVentasAnio->andWhere(['l.id_company' => $user->id_company]);
        } elseif ($user->isAgent()) {
            $queryVentasAnio->andWhere(['l.id_user' => $user->id_user]);
        }

        $ventasAnio = $queryVentasAnio->sum('q.total_amount') ?? 0;

        // 🔥 META ANUAL
        $metaAnual = $metaMensual * 12;
        $porcentajeAnual = $metaAnual > 0 ? round(($ventasAnio / $metaAnual) * 100, 1) : 0;

        // ========== TAREAS PENDIENTES ==========
        $statusPendiente = Status::find()->where(['status' => 'Pendiente'])->one();
        $idPendiente = $statusPendiente ? $statusPendiente->id_status : null;

        if ($idPendiente) {
            $tareasPendientes = Task::find()
                ->where(['id_status' => $idPendiente])
                ->count();
        } else {
            $tareasPendientes = 0;
        }

        if ($idCompletado) {
            $tareasCompletadas = Task::find()
                ->where(['id_status' => $idCompletado])
                ->count();
        } else {
            $tareasCompletadas = 0;
        }

        $totalTareas = Task::find()->count();
        $porcentajeTareas = $totalTareas > 0 ? round(($tareasCompletadas / $totalTareas) * 100) : 0;

        // ========== RESERVACIONES RECIENTES ==========
        $sqlRecientes = "SELECT r.* 
                FROM reservations r
                LEFT JOIN Lead l ON r.id_lead = l.id_lead
                WHERE 1=1";
        
        $paramsRecientes = [];
        
        if ($user->isSuperAdmin() && !empty($empresaId)) {
            $sqlRecientes .= " AND l.id_company = :empresaId";
            $paramsRecientes[':empresaId'] = $empresaId;
        } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
            $sqlRecientes .= " AND l.id_company = :empresaId";
            $paramsRecientes[':empresaId'] = $user->id_company;
        } elseif ($user->isAgent()) {
            $sqlRecientes .= " AND l.id_user = :userId";
            $paramsRecientes[':userId'] = $user->id_user;
        }
        
        $sqlRecientes .= " ORDER BY r.id_reservation DESC LIMIT 10";

        $reservacionesRecientes = Reservation::findBySql($sqlRecientes, $paramsRecientes)->all();

        // PRÓXIMAS RESERVACIONES
        $fechaActual = date('Y-m-d');
        $horaActual = date('H:i:s');

        $sqlProximas = "SELECT r.* 
                FROM reservations r
                LEFT JOIN Lead l ON r.id_lead = l.id_lead
                WHERE (r.date_reservation > :fecha_actual 
                       OR (r.date_reservation = :fecha_actual AND r.hour_f >= :hora_actual))";
        
        $paramsProximas = [
            ':fecha_actual' => $fechaActual,
            ':hora_actual' => $horaActual,
        ];
        
        if ($user->isSuperAdmin() && !empty($empresaId)) {
            $sqlProximas .= " AND l.id_company = :empresaId";
            $paramsProximas[':empresaId'] = $empresaId;
        } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
            $sqlProximas .= " AND l.id_company = :empresaId";
            $paramsProximas[':empresaId'] = $user->id_company;
        } elseif ($user->isAgent()) {
            $sqlProximas .= " AND l.id_user = :userId";
            $paramsProximas[':userId'] = $user->id_user;
        }
        
        $sqlProximas .= " ORDER BY r.date_reservation ASC, r.hour_s ASC LIMIT 10";

        $proximasReservaciones = Reservation::findBySql($sqlProximas, $paramsProximas)->all();

        // ============================================
        // 🔥 EMBUDO DE VENTAS: Nuevo → Contactado → Procesando → Completado
        // ============================================
        
        // Nuevo
        $nuevoProspecto = 0;
        if ($idNuevo) {
            $query = Lead::find()->where(['Lead.id_status' => $idNuevo]);
            if ($user->isSuperAdmin() && !empty($empresaId)) {
                $query->andWhere(['Lead.id_company' => $empresaId]);
            } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
                $query->andWhere(['Lead.id_company' => $user->id_company]);
            } elseif ($user->isAgent()) {
                $query->andWhere(['Lead.id_user' => $user->id_user]);
            }
            $nuevoProspecto = $query->count();
        }

        // Contactado
        $contactado = 0;
        if ($idContactado) {
            $query = Lead::find()->where(['Lead.id_status' => $idContactado]);
            if ($user->isSuperAdmin() && !empty($empresaId)) {
                $query->andWhere(['Lead.id_company' => $empresaId]);
            } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
                $query->andWhere(['Lead.id_company' => $user->id_company]);
            } elseif ($user->isAgent()) {
                $query->andWhere(['Lead.id_user' => $user->id_user]);
            }
            $contactado = $query->count();
        }

        // Procesando
        $procesando = 0;
        $statusProcesando = Status::find()->where(['status' => 'Procesando'])->one();
        if ($statusProcesando) {
            $query = Lead::find()->where(['Lead.id_status' => $statusProcesando->id_status]);
            if ($user->isSuperAdmin() && !empty($empresaId)) {
                $query->andWhere(['Lead.id_company' => $empresaId]);
            } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
                $query->andWhere(['Lead.id_company' => $user->id_company]);
            } elseif ($user->isAgent()) {
                $query->andWhere(['Lead.id_user' => $user->id_user]);
            }
            $procesando = $query->count();
        }

        // 🔥 Completado: contar leads ÚNICOS que tienen al menos 1 cotización con status 'completado'
        $completado = 0;
        $statusCompletadoQuote = Status::find()->where(['status' => 'completado'])->one();
        if ($statusCompletadoQuote) {
            $quoteSubQuery = Quote::find()
                ->alias('q')
                ->select(['q.id_lead'])
                ->distinct()
                ->where(['q.id_status' => $statusCompletadoQuote->id_status]);
            
            if ($user->isSuperAdmin() && !empty($empresaId)) {
                $quoteSubQuery->leftJoin('Lead l', 'q.id_lead = l.id_lead')
                              ->andWhere(['l.id_company' => $empresaId]);
            } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
                $quoteSubQuery->leftJoin('Lead l', 'q.id_lead = l.id_lead')
                              ->andWhere(['l.id_company' => $user->id_company]);
            } elseif ($user->isAgent()) {
                $quoteSubQuery->leftJoin('Lead l', 'q.id_lead = l.id_lead')
                              ->andWhere(['l.id_user' => $user->id_user]);
            }
            
            $completado = Lead::find()
                ->where(['Lead.id_lead' => $quoteSubQuery])
                ->count();
        }

        $totalEmbudo = $nuevoProspecto + $contactado + $procesando + $completado;
        $totalParaPorcentajes = $totalEmbudo > 0 ? $totalEmbudo : 1;
        
        $porcentajeNuevo = round(($nuevoProspecto / $totalParaPorcentajes) * 100);
        $porcentajeContactado = round(($contactado / $totalParaPorcentajes) * 100);
        $porcentajeProcesando = round(($procesando / $totalParaPorcentajes) * 100);
        $porcentajeCompletado = round(($completado / $totalParaPorcentajes) * 100);

        $embudo = [
            'nuevo' => (int)$nuevoProspecto,
            'contactado' => (int)$contactado,
            'procesando' => (int)$procesando,
            'completado' => (int)$completado,
            'porcentajes' => [
                'nuevo' => (int)$porcentajeNuevo,
                'contactado' => (int)$porcentajeContactado,
                'procesando' => (int)$porcentajeProcesando,
                'completado' => (int)$porcentajeCompletado,
            ],
            'total' => (int)$totalEmbudo,
        ];

        return $this->render('index', [
            'totalLeads' => $totalLeads,
            'nuevosLeads' => $nuevosLeads,
            'leadsLabels' => $leadsLabels,
            'leadsData' => $leadsData,
            'leadsColors' => $leadsColors,
            'leadsMesesData' => $leadsMesesData,
            'totalLeadsAnio' => $totalLeadsAnio,
            'anioActualLeads' => $anioActualLeads,
            'totalCitas' => $totalCitas,
            'ventasCerradas' => $ventasCerradas,
            'citasHoy' => $citasHoy,
            'ventasPorMes' => $ventasPorMes,
            'tareasPendientes' => $tareasPendientes,
            'porcentajeTareas' => $porcentajeTareas,
            'reservacionesRecientes' => $reservacionesRecientes,
            'proximasReservaciones' => $proximasReservaciones,
            'embudo' => $embudo,
            
            // 🔥 NUEVAS VARIABLES PARA METAS
            'metaMensual' => $metaMensual,
            'ventasMesActual' => $ventasMesActual,
            'porcentajeAlcanzado' => $porcentajeAlcanzado,
            'montoRestante' => $montoRestante,
            'metaAlcanzada' => $metaAlcanzada,
            
            // 🔥 ACTUAL VS TARGET
            'mesesLabels' => $mesesLabels,
            'actualData' => $actualData,
            'targetData' => $targetData,
            
            // 🔥 VENTAS ANUALES
            'ventasAnio' => $ventasAnio,
            'metaAnual' => $metaAnual,
            'porcentajeAnual' => $porcentajeAnual,
        ]);
    }
}