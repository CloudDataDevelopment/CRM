<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\models\Lead;
use app\models\SalesTracking;
use app\models\Quote;
use app\models\Status;
use app\models\User;
use app\models\Report;
use app\components\ErrorManager;

class LeadController extends Controller
{
    public $layout = 'main';

    /**
     * 🔥 Obtiene el ID del status "Nuevo" de forma dinámica.
     */
    protected function getStatusNuevoId()
    {
        $status = Status::find()->where(['status' => 'Nuevo'])->one();
        
        if (!$status) {
            $status = new Status();
            $status->status = 'Nuevo';
            $status->description = 'Lead nuevo sin contactar';
            if (!$status->save()) {
                Yii::error('No se pudo crear el status "Nuevo": ' . implode(', ', $status->getFirstErrors()), 'lead-status');
                return 19;
            }
        }
        
        return $status->id_status;
    }

    /**
     * 🔥 Obtiene el ID del status "Inactivo" (papelera).
     */
    protected function getStatusInactivoId()
    {
        $status = Status::find()->where(['status' => 'Inactivo'])->one();
        return $status ? $status->id_status : null;
    }

    /**
     * 🔥 Obtiene el ID del status "Cancelado".
     */
    protected function getStatusCanceladoId()
    {
        $status = Status::find()->where(['status' => 'Cancelado'])->one();
        return $status ? $status->id_status : null;
    }

    // ============================================
    // LISTA DE LEADS CON PAGINACIÓN
    // ============================================
    public function actionIndex()
    {
        try {
            $user = Yii::$app->user->identity;
            
            // 🔥 ESTADOS PERMITIDOS (SIN CANCELADO)
            $estadosPermitidos = ['Nuevo', 'Contactado', 'Procesando', 'Completado'];
            
            // 🔥 OBTENER LA EMPRESA SELECCIONADA EN SESIÓN
            $empresaId = Yii::$app->session->get('empresa_id');
            
            // Si es Super Admin y no tiene empresa seleccionada
            if ($user->isSuperAdmin() && empty($empresaId)) {
                Yii::$app->session->setFlash('warning', 'Por favor, selecciona una empresa para continuar.');
                return $this->redirect(['empresa/index']);
            }
            
            $search = Yii::$app->request->get('search');
            $status = Yii::$app->request->get('status');
            $fecha_inicio = Yii::$app->request->get('fecha_inicio');
            $fecha_fin = Yii::$app->request->get('fecha_fin');
            
            // 🔥 EXCLUIR PAPELERA (Inactivo) Y CANCELADOS
            $idPapelera = $this->getStatusInactivoId();
            $idCancelado = $this->getStatusCanceladoId();
            
            $excludeIds = [];
            if ($idPapelera) $excludeIds[] = $idPapelera;
            if ($idCancelado) $excludeIds[] = $idCancelado;
            
            // ============================================
            // 🔥 HELPER: Aplica filtro por rol/empresa
            // ============================================
            $applyLeadFilter = function($query) use ($user, $empresaId) {
                if ($user->isAgent()) {
                    $query->andWhere(['Lead.id_user' => $user->id_user]);
                } elseif ($user->isSuperAdmin()) {
                    if (!empty($empresaId)) {
                        $query->andWhere(['Lead.id_company' => $empresaId]);
                    } else {
                        $query->andWhere(['0' => '1']);
                    }
                } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
                    $query->andWhere(['Lead.id_company' => $user->id_company]);
                } else {
                    $query->andWhere(['0' => '1']);
                }
                return $query;
            };
            
            // ============================================
            // 🔥 CONSULTA CON DATAPROVIDER PARA PAGINACIÓN
            // ============================================
            $query = Lead::findWithDeleted()
                ->orderBy(['Lead.created_at' => SORT_DESC]);
            
            // 🔥 EXCLUIR PAPELERA Y CANCELADOS
            if (!empty($excludeIds)) {
                $query->andWhere(['not in', 'Lead.id_status', $excludeIds]);
            }
            
            // 🔥 FILTROS POR ROL Y EMPRESA
            $applyLeadFilter($query);
            
            // Filtros
            if (!empty($search)) {
                $query->andWhere(['or',
                    ['like', 'Lead.name', $search],
                    ['like', 'Lead.lastname', $search],
                    ['like', 'Lead.comments', $search],
                    ['like', 'Lead.phone', $search],
                ]);
            }
            
            if (!empty($status)) {
                $statusModel = Status::find()->where(['status' => $status])->one();
                if ($statusModel) {
                    $query->andWhere(['Lead.id_status' => $statusModel->id_status]);
                }
            }
            
            if (!empty($fecha_inicio)) {
                $query->andWhere(['>=', 'Lead.created_at', $fecha_inicio]);
            }
            
            if (!empty($fecha_fin)) {
                $query->andWhere(['<=', 'Lead.created_at', $fecha_fin]);
            }
            
            // 🔥 DATAPROVIDER CON PAGINACIÓN
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                    'pageSizeParam' => 'per-page',
                    'pageParam' => 'page',
                ],
                'sort' => [
                    'defaultOrder' => [
                        'created_at' => SORT_DESC,
                    ],
                    'attributes' => [
                        'name' => [
                            'asc' => ['name' => SORT_ASC],
                            'desc' => ['name' => SORT_DESC],
                        ],
                        'lastname' => [
                            'asc' => ['lastname' => SORT_ASC],
                            'desc' => ['lastname' => SORT_DESC],
                        ],
                        'phone' => [
                            'asc' => ['phone' => SORT_ASC],
                            'desc' => ['phone' => SORT_DESC],
                        ],
                        'created_at' => [
                            'asc' => ['created_at' => SORT_ASC],
                            'desc' => ['created_at' => SORT_DESC],
                        ],
                        'id_status' => [
                            'asc' => ['id_status' => SORT_ASC],
                            'desc' => ['id_status' => SORT_DESC],
                        ],
                    ],
                ],
            ]);

            $leads = $dataProvider->getModels();

            // Valores para la vista
            $search = Yii::$app->request->get('search') ?? '';
            $status = Yii::$app->request->get('status') ?? '';
            $fecha_inicio = Yii::$app->request->get('fecha_inicio') ?? '';
            $fecha_fin = Yii::$app->request->get('fecha_fin') ?? '';

            // 🔥 Lista de estados para el filtro (SOLO los permitidos)
            $statusList = Status::find()
                ->where(['in', 'status', $estadosPermitidos])
                ->select(['status', 'id_status'])
                ->indexBy('id_status')
                ->column();

            // ============================================
            // 🔥 MÉTRICAS POR ESTADO (DINÁMICAS)
            // ============================================
            
            $statusNuevo      = Status::find()->where(['status' => 'Nuevo'])->one();
            $statusContactado = Status::find()->where(['status' => 'Contactado'])->one();
            $statusProcesando = Status::find()->where(['status' => 'Procesando'])->one();
            
            $idStatusNuevo      = $statusNuevo      ? $statusNuevo->id_status      : null;
            $idStatusContactado = $statusContactado ? $statusContactado->id_status : null;
            $idStatusProcesando = $statusProcesando ? $statusProcesando->id_status : null;
            
            // TOTAL LEADS (excluyendo papelera y cancelados)
            $totalLeadsQuery = Lead::findWithDeleted();
            if (!empty($excludeIds)) {
                $totalLeadsQuery->andWhere(['not in', 'Lead.id_status', $excludeIds]);
            }
            $applyLeadFilter($totalLeadsQuery);
            $totalLeads = $totalLeadsQuery->count();
            
            // NUEVO
            $nuevoCount = 0;
            if ($idStatusNuevo) {
                $queryNuevo = Lead::findWithDeleted()->where(['Lead.id_status' => $idStatusNuevo]);
                $applyLeadFilter($queryNuevo);
                $nuevoCount = $queryNuevo->count();
            }
            
            // CONTACTADO
            $contactadoCount = 0;
            if ($idStatusContactado) {
                $queryContactado = Lead::findWithDeleted()->where(['Lead.id_status' => $idStatusContactado]);
                $applyLeadFilter($queryContactado);
                $contactadoCount = $queryContactado->count();
            }
            
            // PROCESANDO
            $procesandoCount = 0;
            if ($idStatusProcesando) {
                $queryProcesando = Lead::findWithDeleted()->where(['Lead.id_status' => $idStatusProcesando]);
                $applyLeadFilter($queryProcesando);
                $procesandoCount = $queryProcesando->count();
            }
            
            // PORCENTAJES
            $totalParaPorcentajes = $totalLeads > 0 ? $totalLeads : 1;
            
            $porcentajeNuevo      = round(($nuevoCount / $totalParaPorcentajes) * 100, 1);
            $porcentajeContactado = round(($contactadoCount / $totalParaPorcentajes) * 100, 1);
            $porcentajeProcesando = round(($procesandoCount / $totalParaPorcentajes) * 100, 1);
            
            // ALIAS PARA COMPATIBILIDAD CON LA VISTA
            $nuevosMes   = $nuevoCount;
            $contactados = $contactadoCount;
            $enProceso   = $procesandoCount;
            $calificados = 0;
            $convertidos = 0;
            $perdidos    = 0;

            // EMBUDO DE LEADS POR ETAPAS (SIN CANCELADOS)
            $nuevoProspecto = $nuevoCount;
            $contactado     = $contactadoCount;
            $calificado     = 0;
            $ganado         = 0;
            
            $totalEmbudo = $nuevoProspecto + $contactado + $calificado + $ganado;
            $embudo = [
                'nuevo' => (int)$nuevoProspecto,
                'contactado' => (int)$contactado,
                'calificado' => (int)$calificado,
                'ganado' => (int)$ganado,
                'total' => (int)$totalEmbudo,
            ];

            // ============================================
            // ACTIVIDADES RECIENTES
            // ============================================
            $actividadesRecientes = [];
            $proximasActividades = [];

            try {
                $trackingsQuery = SalesTracking::find()
                    ->alias('st')
                    ->leftJoin('Lead l', 'st.id_lead = l.id_lead');
                
                if (!empty($excludeIds)) {
                    $trackingsQuery->andWhere(['not in', 'l.id_status', $excludeIds]);
                }
                
                if ($user && $user->isAgent()) {
                    $trackingsQuery->andWhere(['l.id_user' => $user->id_user]);
                } elseif ($user && $user->isSuperAdmin()) {
                    if (!empty($empresaId)) {
                        $trackingsQuery->andWhere(['l.id_company' => $empresaId]);
                    }
                } elseif ($user && $user->isAdmin() && !$user->isSuperAdmin()) {
                    $trackingsQuery->andWhere(['l.id_company' => $user->id_company]);
                }
                
                $trackings = $trackingsQuery->orderBy(['st.date_s' => SORT_DESC, 'st.hour' => SORT_DESC])
                    ->limit(5)
                    ->all();
                
                foreach ($trackings as $tracking) {
                    if ($tracking->lead) {
                        $actividadesRecientes[] = [
                            'lead_name' => $tracking->lead->name . ' ' . $tracking->lead->lastname,
                            'status' => $tracking->getStatusName(),
                            'badge_color' => $tracking->getStatusBadgeClass(),
                            'description' => $tracking->comments ?? 'Seguimiento realizado',
                            'date' => $tracking->date_s . ' ' . ($tracking->hour ?? ''),
                            'user_name' => $tracking->user ? $tracking->user->name . ' ' . $tracking->user->lastname1 : '',
                            'icon' => 'comment',
                            'color' => 'primary',
                        ];
                    }
                }
            } catch (\Exception $e) {
                Yii::warning('Error al cargar actividades recientes: ' . $e->getMessage());
            }

            // ============================================
            // PRÓXIMAS ACTIVIDADES
            // ============================================
            try {
                $statusPendiente = Status::find()->where(['status' => 'Pendiente'])->one();
                if ($statusPendiente) {
                    $quotesQuery = Quote::find()
                        ->alias('q')
                        ->leftJoin('Lead l', 'q.id_lead = l.id_lead')
                        ->where(['q.id_status' => $statusPendiente->id_status]);
                    
                    if (!empty($excludeIds)) {
                        $quotesQuery->andWhere(['not in', 'l.id_status', $excludeIds]);
                    }
                    
                    if ($user && $user->isAgent()) {
                        $quotesQuery->andWhere(['l.id_user' => $user->id_user]);
                    } elseif ($user && $user->isSuperAdmin()) {
                        if (!empty($empresaId)) {
                            $quotesQuery->andWhere(['l.id_company' => $empresaId]);
                        }
                    } elseif ($user && $user->isAdmin() && !$user->isSuperAdmin()) {
                        $quotesQuery->andWhere(['l.id_company' => $user->id_company]);
                    }
                    
                    $quotes = $quotesQuery->orderBy(['q.date_quote' => SORT_ASC])
                        ->limit(3)
                        ->all();
                    
                    foreach ($quotes as $quote) {
                        if ($quote->lead) {
                            $proximasActividades[] = [
                                'lead_name' => $quote->lead->name . ' ' . $quote->lead->lastname,
                                'status' => 'Cotización Pendiente',
                                'badge_color' => 'warning',
                                'description' => 'Cotización por revisar: $' . number_format($quote->total_amount, 0, ',', '.'),
                                'date' => $quote->date_quote . ' ' . ($quote->hour_quote ?? ''),
                                'user_name' => $quote->lead->user ? $quote->lead->user->name . ' ' . $quote->lead->user->lastname1 : '',
                                'icon' => 'file-invoice',
                                'color' => 'warning',
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                Yii::warning('Error al cargar cotizaciones pendientes: ' . $e->getMessage());
            }

            // ============================================
            // 🔥 PRÓXIMOS SEGUIMIENTOS (FECHA FUTURA)
            // ============================================
            try {
                $fechaHoy = date('Y-m-d');

                $proximosTrackingsQuery = SalesTracking::find()
                    ->alias('st')
                    ->leftJoin('Lead l', 'st.id_lead = l.id_lead')
                    ->andWhere(['IS NOT', 'st.date_f', null])
                    ->andWhere(['>', 'st.date_f', $fechaHoy]);
                
                if (!empty($excludeIds)) {
                    $proximosTrackingsQuery->andWhere(['not in', 'l.id_status', $excludeIds]);
                }
                
                if ($user && $user->isAgent()) {
                    $proximosTrackingsQuery->andWhere(['l.id_user' => $user->id_user]);
                } elseif ($user && $user->isSuperAdmin()) {
                    if (!empty($empresaId)) {
                        $proximosTrackingsQuery->andWhere(['l.id_company' => $empresaId]);
                    }
                } elseif ($user && $user->isAdmin() && !$user->isSuperAdmin()) {
                    $proximosTrackingsQuery->andWhere(['l.id_company' => $user->id_company]);
                }
                
                $proximosTrackings = $proximosTrackingsQuery->orderBy(['st.date_f' => SORT_ASC])
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
                            'lead_name' => $tracking->lead->name . ' ' . $tracking->lead->lastname,
                            'status' => 'Próximo Seguimiento (' . $etiquetaFecha . ')',
                            'badge_color' => $badgeColor,
                            'description' => $tracking->comments ?? 'Seguimiento programado',
                            'date' => $tracking->date_f,
                            'dias_restantes' => $diasRestantes,
                            'user_name' => $tracking->user ? $tracking->user->name . ' ' . $tracking->user->lastname1 : '',
                            'icon' => 'calendar-check',
                            'color' => 'success',
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

            return $this->render('index', [
                'dataProvider' => $dataProvider,
                'leads' => $leads,
                'search' => $search,
                'status' => $status,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'isAdmin' => $user ? $user->isAdmin() : false,
                'isAgent' => $user ? $user->isAgent() : false,
                'statusList' => $statusList,
                'estadosPermitidos' => $estadosPermitidos,
                'totalLeads' => $totalLeads,
                'nuevosMes' => $nuevosMes,
                'contactados' => $contactados,
                'enProceso' => $enProceso,
                'calificados' => $calificados,
                'convertidos' => $convertidos,
                'perdidos' => $perdidos,
                'porcentajes' => [
                    'nuevo'      => $porcentajeNuevo,
                    'contactado' => $porcentajeContactado,
                    'procesando' => $porcentajeProcesando,
                    'cancelado'  => 0,
                ],
                'actividadesRecientes' => $actividadesRecientes,
                'proximasActividades' => $proximasActividades,
                'embudo' => $embudo,
            ]);
            
        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al cargar los leads');
            return $this->render('index', [
                'dataProvider' => new ActiveDataProvider(['query' => Lead::findWithDeleted()->where(['0' => '1'])]),
                'leads' => [],
                'search' => '',
                'status' => '',
                'fecha_inicio' => '',
                'fecha_fin' => '',
                'isAdmin' => false,
                'isAgent' => false,
                'statusList' => [],
                'estadosPermitidos' => ['Nuevo', 'Contactado', 'Procesando', 'Completado'],
                'totalLeads' => 0,
                'nuevosMes' => 0,
                'contactados' => 0,
                'enProceso' => 0,
                'calificados' => 0,
                'convertidos' => 0,
                'perdidos' => 0,
                'porcentajes' => ['nuevo' => 0, 'contactado' => 0, 'procesando' => 0, 'cancelado' => 0],
                'actividadesRecientes' => [],
                'proximasActividades' => [],
                'embudo' => ['nuevo' => 0, 'contactado' => 0, 'calificado' => 0, 'ganado' => 0, 'total' => 0],
            ]);
        }
    }

    // ============================================
    // VER LEAD EN MODAL (PANEL LATERAL)
    // ============================================
    public function actionViewModal($id)
    {
        try {
            Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
            
            $model = Lead::findWithDeleted()
                ->where(['id_lead' => $id])
                ->with('salesTrackings')
                ->one();
            
            if (!$model) {
                return $this->renderPartial('_view_modal', [
                    'model' => null,
                    'error' => 'Lead no encontrado'
                ]);
            }

            return $this->renderPartial('_view_modal', [
                'model' => $model,
                'error' => null,
            ]);
            
        } catch (\Exception $e) {
            Yii::error('Error en actionViewModal: ' . $e->getMessage(), 'lead-view-modal');
            Yii::error('Stack trace: ' . $e->getTraceAsString(), 'lead-view-modal');
            
            return $this->renderPartial('_view_modal', [
                'model' => null,
                'error' => 'Error al cargar el lead: ' . $e->getMessage()
            ]);
        }
    }

    // ============================================
    // ACTUALIZAR LEAD (MODAL Y PÁGINA COMPLETA)
    // ============================================
    public function actionUpdate($id)
    {
        try {
            $user = Yii::$app->user->identity;
            $isModal = Yii::$app->request->get('modal', false);
            $isAjax = Yii::$app->request->isAjax;
            
            $estadosPermitidos = ['Nuevo', 'Contactado', 'Procesando', 'Completado'];
            
            $model = Lead::findWithDeleted()
                ->where(['id_lead' => $id])
                ->one();

            if (!$model) {
                if ($isModal || $isAjax) {
                    return $this->renderPartial('_update_modal', [
                        'model' => null,
                        'error' => 'Lead no encontrado'
                    ]);
                }
                Yii::$app->session->setFlash('error', 'Lead no encontrado');
                return $this->redirect(['index']);
            }

            // ============================================
            // 🔥 VERIFICACIÓN DE PERMISOS
            // ============================================
            if ($user && $user->isAgent()) {
                if ($model->id_user != $user->id_user) {
                    if ($isModal || $isAjax) {
                        return $this->renderPartial('_update_modal', [
                            'model' => null,
                            'error' => 'No tienes permiso para editar este lead.'
                        ]);
                    }
                    Yii::$app->session->setFlash('error', 'No tienes permiso para editar este lead.');
                    return $this->redirect(['index']);
                }
            } elseif ($user && $user->isAdmin() && !$user->isSuperAdmin()) {
                if ($model->id_company != $user->id_company) {
                    if ($isModal || $isAjax) {
                        return $this->renderPartial('_update_modal', [
                            'model' => null,
                            'error' => 'No tienes permiso para editar este lead.'
                        ]);
                    }
                    Yii::$app->session->setFlash('error', 'No tienes permiso para editar este lead.');
                    return $this->redirect(['index']);
                }
            } elseif ($user && $user->isSuperAdmin()) {
                $empresaId = Yii::$app->session->get('empresa_id');
                if (!empty($empresaId) && $model->id_company != $empresaId) {
                    if ($isModal || $isAjax) {
                        return $this->renderPartial('_update_modal', [
                            'model' => null,
                            'error' => 'No tienes permiso para editar este lead.'
                        ]);
                    }
                    Yii::$app->session->setFlash('error', 'No tienes permiso para editar este lead.');
                    return $this->redirect(['index']);
                }
            }

            $returnUrl = Yii::$app->request->get('return', 'index');

            $statusList = Status::find()
                ->where(['in', 'status', $estadosPermitidos])
                ->select(['status', 'id_status'])
                ->indexBy('id_status')
                ->column();

            // ============================================
            // 🔥 PROCESAR POST
            // ============================================
            if ($model->load(Yii::$app->request->post())) {
                try {
                    if ($model->save()) {
                        // 🔥 RESPUESTA PARA MODAL/AJAX
                        if ($isModal || $isAjax) {
                            return $this->renderPartial('_update_modal', [
                                'model' => $model,
                                'statusList' => $statusList,
                                'estadosPermitidos' => $estadosPermitidos,
                                'returnUrl' => $returnUrl,
                                'success' => 'Lead actualizado exitosamente'
                            ]);
                        }
                        
                        // 🔥 RESPUESTA PARA VISTA COMPLETA
                        Yii::$app->session->setFlash('success', 'Lead actualizado exitosamente');
                        return $this->redirect(['view', 'id' => $model->id_lead]);
                        
                    } else {
                        // 🔥 ERROR DE VALIDACIÓN EN MODAL
                        if ($isModal || $isAjax) {
                            $errors = $model->getErrors();
                            $errorMessages = [];
                            foreach ($errors as $attribute => $errorList) {
                                $label = $model->getAttributeLabel($attribute);
                                $errorMessages[] = $label . ': ' . implode(', ', $errorList);
                            }
                            return $this->renderPartial('_update_modal', [
                                'model' => $model,
                                'statusList' => $statusList,
                                'estadosPermitidos' => $estadosPermitidos,
                                'returnUrl' => $returnUrl,
                                'error' => 'Error al actualizar:<br>' . implode('<br>', $errorMessages)
                            ]);
                        }
                        Yii::$app->session->setFlash('error', 'Error al actualizar el lead.');
                    }
                } catch (\Exception $e) {
                    if ($isModal || $isAjax) {
                        return $this->renderPartial('_update_modal', [
                            'model' => $model,
                            'statusList' => $statusList,
                            'estadosPermitidos' => $estadosPermitidos,
                            'returnUrl' => $returnUrl,
                            'error' => 'Error al actualizar: ' . $e->getMessage()
                        ]);
                    }
                    Yii::$app->session->setFlash('error', 'Error al actualizar el lead: ' . $e->getMessage());
                }
            }

            // ============================================
            // 🔥 RENDER INICIAL DEL FORMULARIO
            // ============================================
            if ($isModal || $isAjax) {
                return $this->renderPartial('_update_modal', [
                    'model' => $model,
                    'returnUrl' => $returnUrl,
                    'statusList' => $statusList,
                    'estadosPermitidos' => $estadosPermitidos,
                ]);
            }

            return $this->render('update', [
                'model' => $model,
                'returnUrl' => $returnUrl,
                'statusList' => $statusList,
                'estadosPermitidos' => $estadosPermitidos,
            ]);

        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al cargar el lead para editar.');
            $isModal = Yii::$app->request->get('modal', false);
            $isAjax = Yii::$app->request->isAjax;
            if ($isModal || $isAjax) {
                return $this->renderPartial('_update_modal', [
                    'model' => null,
                    'error' => 'Error al cargar el lead: ' . $e->getMessage()
                ]);
            }
            return $this->redirect(['index']);
        }
    }

    // ============================================
    // PAPELERA
    // ============================================
    public function actionTrash()
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');
            
            if (!$user || !$user->isAdmin()) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para acceder a la papelera.');
                return $this->redirect(['index']);
            }
            
            $idPapelera = $this->getStatusInactivoId();
            
            if (!$idPapelera) {
                Yii::$app->session->setFlash('warning', 'No se encontró el estado "Inactivo".');
                return $this->redirect(['index']);
            }
            
            // 🔥 USAR findWithDeleted() porque find() filtra por Cancelado
            $query = Lead::findWithDeleted()
                ->where(['Lead.id_status' => $idPapelera]);
            
            if ($user->isSuperAdmin()) {
                if (!empty($empresaId)) {
                    $query->andWhere(['Lead.id_company' => $empresaId]);
                }
            } elseif (!$user->isSuperAdmin()) {
                $query->andWhere(['Lead.id_company' => $user->id_company]);
            }
            
            $leads = $query->all();

            return $this->render('trash', [
                'leads' => $leads,
            ]);
            
        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al cargar la papelera');
            return $this->render('trash', [
                'leads' => [],
            ]);
        }
    }

    // ============================================
    // VER LEAD
    // ============================================
    public function actionView($id)
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');
            
            $model = Lead::findWithDeleted()
                ->where(['id_lead' => $id])
                ->with('salesTrackings')
                ->one();

            if (!$model) {
                Yii::$app->session->setFlash('error', 'El lead solicitado no existe.');
                return $this->redirect(['index']);
            }

            if ($user && $user->isAgent()) {
                if ($model->id_user != $user->id_user) {
                    Yii::$app->session->setFlash('error', 'No tienes permiso para ver este lead.');
                    return $this->redirect(['index']);
                }
            } elseif ($user && $user->isSuperAdmin()) {
                if (!empty($empresaId) && $model->id_company != $empresaId) {
                    Yii::$app->session->setFlash('error', 'No tienes permiso para ver este lead.');
                    return $this->redirect(['index']);
                }
            } elseif ($user && $user->isAdmin() && !$user->isSuperAdmin()) {
                if ($model->id_company != $user->id_company) {
                    Yii::$app->session->setFlash('error', 'No tienes permiso para ver este lead.');
                    return $this->redirect(['index']);
                }
            }

            $estadosPermitidos = ['Nuevo', 'Contactado', 'Procesando', 'Completado'];
            $statusList = Status::find()
                ->where(['in', 'status', $estadosPermitidos])
                ->select(['status', 'id_status'])
                ->indexBy('id_status')
                ->column();

            return $this->render('view', [
                'model' => $model,
                'statusList' => $statusList,
            ]);
            
        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al cargar la información del lead.');
            return $this->redirect(['index']);
        }
    }

    // ============================================
    // MOVER A PAPELERA
    // ============================================
    public function actionDelete($id)
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');
            
            if (!$user || !$user->isAdmin()) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para eliminar leads.');
                return $this->redirect(['index']);
            }
            
            $model = Lead::findWithDeleted()
                ->where(['id_lead' => $id])
                ->one();
            
            if ($model) {
                if ($user->isSuperAdmin()) {
                    if (!empty($empresaId) && $model->id_company != $empresaId) {
                        Yii::$app->session->setFlash('error', 'No tienes permiso para eliminar este lead.');
                        return $this->redirect(['index']);
                    }
                } elseif (!$user->isSuperAdmin() && $model->id_company != $user->id_company) {
                    Yii::$app->session->setFlash('error', 'No tienes permiso para eliminar este lead.');
                    return $this->redirect(['index']);
                }
                
                $idPapelera = $this->getStatusInactivoId();
                
                if (!$idPapelera) {
                    Yii::$app->session->setFlash('error', 'No se encontró el estado "Inactivo".');
                    return $this->redirect(['index']);
                }
                
                if ($model->id_status == $idPapelera) {
                    Yii::$app->session->setFlash('info', 'Este lead ya está en la papelera.');
                    return $this->redirect(['index']);
                }
                
                $model->id_status = $idPapelera;
                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'Lead movido a la papelera.');
                } else {
                    Yii::$app->session->setFlash('error', 'Error al mover el lead a la papelera.');
                }
            }
        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al eliminar el lead.');
        }

        return $this->redirect(['index']);
    }

    // ============================================
    // RESTAURAR
    // ============================================
    public function actionRestore($id)
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');
            
            if (!$user || !$user->isAdmin()) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para restaurar leads.');
                return $this->redirect(['index']);
            }
            
            $model = Lead::findWithDeleted()
                ->where(['id_lead' => $id])
                ->one();
            
            if ($model) {
                if ($user->isSuperAdmin()) {
                    if (!empty($empresaId) && $model->id_company != $empresaId) {
                        Yii::$app->session->setFlash('error', 'No tienes permiso para restaurar este lead.');
                        return $this->redirect(['trash']);
                    }
                } elseif (!$user->isSuperAdmin() && $model->id_company != $user->id_company) {
                    Yii::$app->session->setFlash('error', 'No tienes permiso para restaurar este lead.');
                    return $this->redirect(['trash']);
                }
                
                $idPapelera = $this->getStatusInactivoId();
                
                if ($model->id_status != $idPapelera) {
                    Yii::$app->session->setFlash('info', 'Este lead no está en la papelera.');
                    return $this->redirect(['index']);
                }
                
                $model->id_status = $this->getStatusNuevoId();
                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'Lead restaurado exitosamente con estado "Nuevo".');
                } else {
                    Yii::$app->session->setFlash('error', 'Error al restaurar el lead.');
                }
            }
        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al restaurar el lead.');
        }

        return $this->redirect(['trash']);
    }

    // ============================================
    // ACTUALIZAR ESTADO
    // ============================================
    public function actionUpdateStatus($id, $status)
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');
            
            $model = Lead::findWithDeleted()
                ->where(['id_lead' => $id])
                ->one();

            if (!$model) {
                Yii::$app->session->setFlash('error', 'Lead no encontrado.');
                return $this->redirect(['view', 'id' => $id]);
            }

            if ($user && $user->isAgent()) {
                if ($model->id_user != $user->id_user) {
                    Yii::$app->session->setFlash('error', 'No tienes permiso para cambiar el estado de este lead.');
                    return $this->redirect(['view', 'id' => $id]);
                }
            } elseif ($user && $user->isSuperAdmin()) {
                if (!empty($empresaId) && $model->id_company != $empresaId) {
                    Yii::$app->session->setFlash('error', 'No tienes permiso para cambiar el estado de este lead.');
                    return $this->redirect(['view', 'id' => $id]);
                }
            } elseif ($user && $user->isAdmin() && !$user->isSuperAdmin()) {
                if ($model->id_company != $user->id_company) {
                    Yii::$app->session->setFlash('error', 'No tienes permiso para cambiar el estado de este lead.');
                    return $this->redirect(['view', 'id' => $id]);
                }
            }

            $statusModel = Status::find()->where(['status' => $status])->one();
            if (!$statusModel) {
                Yii::$app->session->setFlash('error', 'Estado no válido.');
                return $this->redirect(['view', 'id' => $id]);
            }

            $oldStatus = $model->getStatusName();
            $model->id_status = $statusModel->id_status;
            
            if ($model->save(false)) {
                Yii::$app->session->removeAllFlashes();
                Yii::$app->session->setFlash('success', 'Estado actualizado de "' . $oldStatus . '" a "' . $status . '"');
                
                try {
                    $tracking = new SalesTracking();
                    $tracking->id_lead = $model->id_lead;
                    $tracking->id_status = $statusModel->id_status;
                    $tracking->comments = 'Estado cambiado de "' . $oldStatus . '" a "' . $status . '"';
                    $tracking->date_s = date('Y-m-d');
                    $tracking->hour = date('H:i');
                    $tracking->id_user = Yii::$app->user->id;
                    $tracking->save();
                } catch (\Exception $e) {
                    Yii::warning('Error al guardar seguimiento: ' . $e->getMessage());
                }
            }

            return $this->redirect(['view', 'id' => $id]);
            
        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al cambiar el estado.');
            return $this->redirect(['view', 'id' => $id]);
        }
    }

    // ============================================
    // CREAR LEAD
    // ============================================
    public function actionCreate()
    {
        $model = new Lead();

        $estadosPermitidos = ['Nuevo', 'Contactado', 'Procesando', 'Completado'];
        $idStatusNuevo = $this->getStatusNuevoId();

        $model->created_at = date('Y-m-d');
        $model->id_status = $idStatusNuevo;

        if ($model->load(Yii::$app->request->post())) {
            try {
                $user = Yii::$app->user->identity;
                $empresaId = Yii::$app->session->get('empresa_id');
                
                if ($user->isSuperAdmin()) {
                    if (!empty($empresaId)) {
                        $model->id_company = $empresaId;
                    } else {
                        $model->id_company = 1;
                    }
                } elseif ($user && !empty($user->id_company)) {
                    $model->id_company = $user->id_company;
                } else {
                    $model->id_company = 1;
                }
                
                $model->id_status = $idStatusNuevo;
                
                // 🔥 Validar created_at
                if (empty($model->created_at) || $model->created_at === '0000-00-00') {
                    $model->created_at = date('Y-m-d');
                }
                
                if ($user && $user->isAgent()) {
                    $model->id_user = $user->id_user;
                } else {
                    $model->id_user = Yii::$app->user->id ?: 1;
                }

                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Lead creado exitosamente.');
                    return $this->redirect(['create']);
                } else {
                    $errors = $model->getErrors();
                    $errorMessages = [];
                    foreach ($errors as $attribute => $errorList) {
                        $label = $model->getAttributeLabel($attribute);
                        $errorMessages[] = $label . ': ' . implode(', ', $errorList);
                    }
                    $errorMessage = implode('<br>', $errorMessages);
                    Yii::$app->session->setFlash('error', 'Error al guardar el lead:<br>' . $errorMessage);
                }

            } catch (\Exception $e) {
                ErrorManager::handle($e, 'Error al crear el lead.');
                Yii::$app->session->setFlash('error', 'Error: ' . $e->getMessage());
            }
        }

        $user = Yii::$app->user->identity;
        $empresaId = Yii::$app->session->get('empresa_id');
        
        $idPapelera = $this->getStatusInactivoId();
        $idCancelado = $this->getStatusCanceladoId();
        
        $excludeIds = [];
        if ($idPapelera) $excludeIds[] = $idPapelera;
        if ($idCancelado) $excludeIds[] = $idCancelado;
        
        $ultimosLeads = Lead::findWithDeleted();
        
        if (!empty($excludeIds)) {
            $ultimosLeads->andWhere(['not in', 'Lead.id_status', $excludeIds]);
        }
        
        if ($user && $user->isAgent()) {
            $ultimosLeads->andWhere(['Lead.id_user' => $user->id_user]);
        } elseif ($user && $user->isSuperAdmin()) {
            if (!empty($empresaId)) {
                $ultimosLeads->andWhere(['Lead.id_company' => $empresaId]);
            }
        } elseif ($user && !$user->isSuperAdmin()) {
            $ultimosLeads->andWhere(['Lead.id_company' => $user->id_company]);
        }
        
        $ultimosLeads = $ultimosLeads->orderBy(['id_lead' => SORT_DESC])->limit(5)->all();

        $statusList = Status::find()
            ->where(['in', 'status', $estadosPermitidos])
            ->select(['status', 'id_status'])
            ->indexBy('id_status')
            ->column();

        return $this->render('create', [
            'model' => $model,
            'ultimosLeads' => $ultimosLeads,
            'statusList' => $statusList,
            'estadosPermitidos' => $estadosPermitidos,
        ]);
    }

    // ============================================
    // DETALLES DEL LEAD (Vista completa)
    // ============================================
    public function actionDetails($id)
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');
            
            $model = Lead::findWithDeleted()
                ->where(['id_lead' => $id])
                ->with(['salesTrackings', 'quotes', 'user', 'company', 'status'])
                ->one();

            if (!$model) {
                Yii::$app->session->setFlash('error', 'El lead solicitado no existe.');
                return $this->redirect(['index']);
            }

            // ============================================
            // 🔥 VERIFICACIÓN DE PERMISOS
            // ============================================
            if ($user && $user->isAgent()) {
                if ($model->id_user != $user->id_user) {
                    Yii::$app->session->setFlash('error', 'No tienes permiso para ver este lead.');
                    return $this->redirect(['index']);
                }
            } elseif ($user && $user->isSuperAdmin()) {
                if (!empty($empresaId) && $model->id_company != $empresaId) {
                    Yii::$app->session->setFlash('error', 'No tienes permiso para ver este lead.');
                    return $this->redirect(['index']);
                }
            } elseif ($user && $user->isAdmin() && !$user->isSuperAdmin()) {
                if ($model->id_company != $user->id_company) {
                    Yii::$app->session->setFlash('error', 'No tienes permiso para ver este lead.');
                    return $this->redirect(['index']);
                }
            }

            // ============================================
            // 🔥 CARGAR EVALUACIONES DEL LEAD
            // ============================================
            $evaluaciones = $model->getReports()
                ->with(['status', 'user'])
                ->all();
            $totalEvaluaciones = count($evaluaciones);

            // ============================================
            // 🔥 CARGAR SEGUIMIENTOS Y COTIZACIONES
            // ============================================
            $trackings = $model->salesTrackings ?? [];
            $totalTrackings = count($trackings);

            $quotes = $model->quotes ?? [];
            $totalQuotes = count($quotes);

            // ============================================
            // 🔥 DATOS ADICIONALES
            // ============================================
            $agentName = $model->getAgentName();
            $statusName = $model->getStatusName();
            $statusIcon = $model->getStatusIcon();

            $statusColor = '#6c757d';
            $statusColors = [
                'Nuevo' => '#4e73df',
                'Contactado' => '#17a2b8',
                'Procesando' => '#f6c23e',
                'Calificado' => '#1cc88a',
                'Cliente' => '#1cc88a',
                'Cancelado' => '#6c757d',
                'Perdido' => '#e74a3b',
            ];
            $statusColor = $statusColors[trim($statusName)] ?? '#6c757d';

            $createdAt = strtotime($model->created_at);
            $daysSince = $createdAt ? floor((time() - $createdAt) / (60 * 60 * 24)) : 0;

            $statusList = Status::find()
                ->where(['in', 'status', ['Nuevo', 'Contactado', 'Procesando', 'Completado']])
                ->select(['status', 'id_status'])
                ->indexBy('id_status')
                ->column();

            return $this->render('details', [
                'model'             => $model,
                'evaluaciones'      => $evaluaciones,
                'totalEvaluaciones' => $totalEvaluaciones,
                'trackings'         => $trackings,
                'totalTrackings'    => $totalTrackings,
                'quotes'            => $quotes,
                'totalQuotes'       => $totalQuotes,
                'agentName'         => $agentName,
                'statusName'        => $statusName,
                'statusIcon'        => $statusIcon,
                'statusColor'       => $statusColor,
                'daysSince'         => $daysSince,
                'statusList'        => $statusList,
                'isAdmin'           => $user->isAdmin(),
                'isAgent'           => $user->isAgent(),
                'isSuperAdmin'      => $user->isSuperAdmin(),
            ]);
            
        } catch (\Exception $e) {
            Yii::error('Error en actionDetails: ' . $e->getMessage(), 'lead-details');
            Yii::error('Stack trace: ' . $e->getTraceAsString(), 'lead-details');
            Yii::$app->session->setFlash('error', 'Error al cargar la información del lead.');
            return $this->redirect(['index']);
        }
    }
}