<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Lead;
use app\models\SalesTracking;
use app\models\Task;
use app\models\Status;
use app\models\Quote;

class DashboardController extends Controller
{
    public $layout = 'main';

    /**
     * 🔥 Helper: Devuelve el array de IDs de leads visibles para el usuario actual.
     * Esto permite filtrar tablas relacionadas (SalesTracking, Quote, Task) que
     * no tienen id_company directo.
     */
    private function getLeadIdsForUser($user, $empresaId)
    {
        $query = Lead::find()->select('id_lead');

        if ($user->isSuperAdmin()) {
            if (!empty($empresaId)) {
                $query->andWhere(['id_company' => $empresaId]);
            } else {
                // Sin empresa seleccionada, no mostrar nada
                return [];
            }
        } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
            $query->andWhere(['id_company' => $user->id_company]);
        } elseif ($user->isAgent()) {
            $query->andWhere(['id_user' => $user->id_user]);
        }

        return $query->column();
    }

    /**
     * 🔥 Helper: Aplica el filtro por empresa a una query de Quote.
     * Quote no tiene id_company, se filtra vía su Lead relacionado.
     */
    private function applyQuoteCompanyFilter($query, $user, $empresaId)
    {
        $query->leftJoin('Lead l', 'q.id_lead = l.id_lead');

        if ($user->isSuperAdmin()) {
            if (!empty($empresaId)) {
                $query->andWhere(['l.id_company' => $empresaId]);
            } else {
                $query->andWhere(['0' => '1']);
            }
        } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
            $query->andWhere(['l.id_company' => $user->id_company]);
        } elseif ($user->isAgent()) {
            $query->andWhere(['l.id_user' => $user->id_user]);
        }

        return $query;
    }

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
        // 🔥 IDs DE LEADS VISIBLES PARA EL USUARIO
        // ============================================
        $leadIds = $this->getLeadIdsForUser($user, $empresaId);

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

        $statusCompletado = Status::find()->where(['status' => 'completado'])->one();
        $idCompletado = $statusCompletado ? $statusCompletado->id_status : null;

        // ============================================
        // LEADS
        // ============================================
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

        // ============================================
        // LEADS POR ESTADO (GRÁFICO CIRCULAR)
        // ============================================
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

        // ============================================
        // LEADS NUEVOS POR MES
        // ============================================
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

        // ============================================
        // 🔥 VENTAS / CITAS (CON FILTRO POR EMPRESA)
        // ============================================
        $totalCitas = 0;
        $citasHoy = 0;

        if (!empty($leadIds)) {
            $totalCitas = SalesTracking::find()
                ->where(['id_lead' => $leadIds])
                ->count();

            $citasHoy = SalesTracking::find()
                ->where(['id_lead' => $leadIds])
                ->andWhere(['date_s' => date('Y-m-d')])
                ->count();
        }

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
        // ACTIVIDADES RECIENTES (SEGUIMIENTOS)
        // ============================================
        $actividadesRecientes = [];
        $proximasActividades = [];

        try {
            $trackingsQuery = SalesTracking::find()
                ->alias('st')
                ->leftJoin('Lead l', 'st.id_lead = l.id_lead')
                ->where(['not in', 'l.id_status', [1, 10]]);

            if ($user->isSuperAdmin() && !empty($empresaId)) {
                $trackingsQuery->andWhere(['l.id_company' => $empresaId]);
            } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
                $trackingsQuery->andWhere(['l.id_company' => $user->id_company]);
            } elseif ($user->isAgent()) {
                $trackingsQuery->andWhere(['l.id_user' => $user->id_user]);
            }

            $trackings = $trackingsQuery
                ->orderBy(['st.date_s' => SORT_DESC, 'st.hour' => SORT_DESC])
                ->limit(5)
                ->all();

            foreach ($trackings as $tracking) {
                if ($tracking->lead) {
                    $actividadesRecientes[] = [
                        'lead_name'   => $tracking->lead->name . ' ' . $tracking->lead->lastname,
                        'status'      => $tracking->getStatusName(),
                        'badge_color' => $tracking->getStatusBadgeClass(),
                        'description' => $tracking->comments ?? 'Seguimiento realizado',
                        'date'        => $tracking->date_s . ' ' . ($tracking->hour ?? ''),
                        'user_name'   => $tracking->user ? $tracking->user->name . ' ' . $tracking->user->lastname1 : '',
                        'icon'        => 'comment',
                        'color'       => 'primary',
                    ];
                }
            }
        } catch (\Exception $e) {
            Yii::warning('Error al cargar actividades recientes: ' . $e->getMessage());
        }

        // ============================================
        // PRÓXIMAS ACTIVIDADES (COTIZACIONES PENDIENTES)
        // ============================================
        try {
            $statusPendiente = Status::find()->where(['status' => 'Pendiente'])->one();
            if ($statusPendiente) {
                $quotesQuery = Quote::find()
                    ->alias('q')
                    ->leftJoin('Lead l', 'q.id_lead = l.id_lead')
                    ->where(['q.id_status' => $statusPendiente->id_status])
                    ->andWhere(['not in', 'l.id_status', [1, 10]]);

                if ($user->isSuperAdmin() && !empty($empresaId)) {
                    $quotesQuery->andWhere(['l.id_company' => $empresaId]);
                } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
                    $quotesQuery->andWhere(['l.id_company' => $user->id_company]);
                } elseif ($user->isAgent()) {
                    $quotesQuery->andWhere(['l.id_user' => $user->id_user]);
                }

                $quotes = $quotesQuery
                    ->orderBy(['q.date_quote' => SORT_ASC])
                    ->limit(3)
                    ->all();

                foreach ($quotes as $quote) {
                    if ($quote->lead) {
                        $proximasActividades[] = [
                            'lead_name'   => $quote->lead->name . ' ' . $quote->lead->lastname,
                            'status'      => 'Cotización Pendiente',
                            'badge_color' => 'warning',
                            'description' => 'Cotización por revisar: $' . number_format($quote->total_amount, 0, ',', '.'),
                            'date'        => $quote->date_quote . ' ' . ($quote->hour_quote ?? ''),
                            'user_name'   => $quote->lead->user ? $quote->lead->user->name . ' ' . $quote->lead->user->lastname1 : '',
                            'icon'        => 'file-invoice',
                            'color'       => 'warning',
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            Yii::warning('Error al cargar cotizaciones pendientes: ' . $e->getMessage());
        }

        // ============================================
        // PRÓXIMOS SEGUIMIENTOS (FECHA FUTURA)
        // ============================================
        try {
            $fechaHoy = date('Y-m-d');

            $proximosTrackingsQuery = SalesTracking::find()
                ->alias('st')
                ->leftJoin('Lead l', 'st.id_lead = l.id_lead')
                ->where(['not in', 'l.id_status', [1, 10]])
                ->andWhere(['IS NOT', 'st.date_f', null])
                ->andWhere(['>', 'st.date_f', $fechaHoy]);

            if ($user->isSuperAdmin() && !empty($empresaId)) {
                $proximosTrackingsQuery->andWhere(['l.id_company' => $empresaId]);
            } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
                $proximosTrackingsQuery->andWhere(['l.id_company' => $user->id_company]);
            } elseif ($user->isAgent()) {
                $proximosTrackingsQuery->andWhere(['l.id_user' => $user->id_user]);
            }

            $proximosTrackings = $proximosTrackingsQuery
                ->orderBy(['st.date_f' => SORT_ASC])
                ->limit(5)
                ->all();

            foreach ($proximosTrackings as $tracking) {
                if ($tracking->lead) {
                    $diasRestantes = (int) floor((strtotime($tracking->date_f) - strtotime($fechaHoy)) / 86400);

                    if ($diasRestantes == 0) {
                        $etiquetaFecha = 'Hoy';
                        $badgeColor = 'warning';
                    } elseif ($diasRestantes == 1) {
                        $etiquetaFecha = 'Mañana';
                        $badgeColor = 'info';
                    } elseif ($diasRestantes <= 7) {
                        $etiquetaFecha = 'En ' . $diasRestantes . ' días';
                        $badgeColor = 'info';
                    } else {
                        $etiquetaFecha = 'En ' . $diasRestantes . ' días';
                        $badgeColor = 'success';
                    }

                    $proximasActividades[] = [
                        'lead_name'      => $tracking->lead->name . ' ' . $tracking->lead->lastname,
                        'status'         => 'Próximo Seguimiento (' . $etiquetaFecha . ')',
                        'badge_color'    => $badgeColor,
                        'description'    => $tracking->comments ?? 'Seguimiento programado',
                        'date'           => $tracking->date_f,
                        'dias_restantes' => $diasRestantes,
                        'user_name'      => $tracking->user ? $tracking->user->name . ' ' . $tracking->user->lastname1 : '',
                        'icon'           => 'calendar-check',
                        'color'          => 'success',
                    ];
                }
            }
        } catch (\Exception $e) {
            Yii::warning('Error al cargar próximos seguimientos: ' . $e->getMessage());
        }

        usort($proximasActividades, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        $proximasActividades = array_slice($proximasActividades, 0, 5);

        // ============================================
        // 🔥 VENTAS POR MES (CON FILTRO)
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
        // METAS
        // ============================================
        $metaMensual = 100000;
        $mesActual = (int)date('n');
        $anioActual = date('Y');

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

        $porcentajeAlcanzado = $metaMensual > 0 ? round(($ventasMesActual / $metaMensual) * 100, 1) : 0;
        $porcentajeAlcanzado = min($porcentajeAlcanzado, 100);
        $montoRestante = max(0, $metaMensual - $ventasMesActual);
        $metaAlcanzada = $ventasMesActual >= $metaMensual;

        // ============================================
        // ACTUAL VS TARGET (ÚLTIMOS 6 MESES)
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
        // VENTAS ANUALES
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

        $metaAnual = $metaMensual * 12;
        $porcentajeAnual = $metaAnual > 0 ? round(($ventasAnio / $metaAnual) * 100, 1) : 0;

        // ============================================
        // 🔥 TAREAS (CON FILTRO POR EMPRESA)
        // ============================================
        $statusPendiente = Status::find()->where(['status' => 'Pendiente'])->one();
        $idPendiente = $statusPendiente ? $statusPendiente->id_status : null;

        // Obtener IDs de usuarios visibles
        $userIds = [];
        if ($user->isSuperAdmin() && !empty($empresaId)) {
            $userIds = \app\models\User::find()
                ->select('id_user')
                ->where(['id_company' => $empresaId])
                ->column();
        } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
            $userIds = \app\models\User::find()
                ->select('id_user')
                ->where(['id_company' => $user->id_company])
                ->column();
        } elseif ($user->isAgent()) {
            $userIds = [$user->id_user];
        }

        $tareasPendientes = 0;
        $tareasCompletadas = 0;
        $totalTareas = 0;

        if (!empty($userIds)) {
            if ($idPendiente) {
                $tareasPendientes = Task::find()
                    ->where(['id_status' => $idPendiente])
                    ->andWhere(['id_user' => $userIds])
                    ->count();
            }

            if ($idCompletado) {
                $tareasCompletadas = Task::find()
                    ->where(['id_status' => $idCompletado])
                    ->andWhere(['id_user' => $userIds])
                    ->count();
            }

            $totalTareas = Task::find()
                ->where(['id_user' => $userIds])
                ->count();
        }

        $porcentajeTareas = $totalTareas > 0 ? round(($tareasCompletadas / $totalTareas) * 100) : 0;

        // ============================================
        // EMBUDO
        // ============================================
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
            'nuevo'       => (int)$nuevoProspecto,
            'contactado'  => (int)$contactado,
            'procesando'  => (int)$procesando,
            'completado'  => (int)$completado,
            'porcentajes' => [
                'nuevo'      => (int)$porcentajeNuevo,
                'contactado' => (int)$porcentajeContactado,
                'procesando' => (int)$porcentajeProcesando,
                'completado' => (int)$porcentajeCompletado,
            ],
            'total' => (int)$totalEmbudo,
        ];

        return $this->render('index', [
            'totalLeads'        => $totalLeads,
            'nuevosLeads'       => $nuevosLeads,
            'leadsLabels'       => $leadsLabels,
            'leadsData'         => $leadsData,
            'leadsColors'       => $leadsColors,
            'leadsMesesData'    => $leadsMesesData,
            'totalLeadsAnio'    => $totalLeadsAnio,
            'anioActualLeads'   => $anioActualLeads,
            'totalCitas'        => $totalCitas,
            'ventasCerradas'    => $ventasCerradas,
            'citasHoy'          => $citasHoy,
            'ventasPorMes'      => $ventasPorMes,
            'tareasPendientes'  => $tareasPendientes,
            'porcentajeTareas'  => $porcentajeTareas,
            'actividadesRecientes' => $actividadesRecientes,
            'proximasActividades'  => $proximasActividades,
            'embudo'            => $embudo,
            'metaMensual'       => $metaMensual,
            'ventasMesActual'   => $ventasMesActual,
            'porcentajeAlcanzado' => $porcentajeAlcanzado,
            'montoRestante'     => $montoRestante,
            'metaAlcanzada'     => $metaAlcanzada,
            'mesesLabels'       => $mesesLabels,
            'actualData'        => $actualData,
            'targetData'        => $targetData,
            'ventasAnio'        => $ventasAnio,
            'metaAnual'         => $metaAnual,
            'porcentajeAnual'   => $porcentajeAnual,
        ]);
    }
}