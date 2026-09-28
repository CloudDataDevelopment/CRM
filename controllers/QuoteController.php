<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use app\models\Quote;
use app\models\Lead;
use app\models\Status;
use app\components\ErrorManager;
use yii\web\Response;
use yii\helpers\ArrayHelper;

class QuoteController extends Controller
{
    public $layout = 'main';

    const QUOTE_STATUS_LIST = ['Pendiente', 'Aprobada', 'Rechazada', 'Pagada', 'Cancelado', 'Completado'];

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete'         => ['POST', 'GET'],
                    'restore'        => ['POST', 'GET'],
                    'update-status'  => ['POST'],
                ],
            ],
        ];
    }

    // ============================================
    // HELPERS
    // ============================================
    private function getCancelledStatusId()
    {
        $status = Status::find()->where(['status' => 'Cancelado'])->one();
        if ($status) return $status->id_status;

        foreach (['cancelado', 'Cancelada', 'cancelada'] as $name) {
            $status = Status::find()->where(['status' => $name])->one();
            if ($status) return $status->id_status;
        }
        return null;
    }

    private function getPendingStatusId()
    {
        $status = Status::find()->where(['status' => 'Pendiente'])->one();
        if ($status) return $status->id_status;

        foreach (['pendiente', 'pending'] as $name) {
            $status = Status::find()->where(['status' => $name])->one();
            if ($status) return $status->id_status;
        }
        return null;
    }

    // ============================================
    // LISTA DE COTIZACIONES
    // ============================================
    public function actionIndex()
    {
        try {
            $user = Yii::$app->user->identity;
            
            if (!$user) return $this->redirect(['site/login']);

            $empresaId = Yii::$app->session->get('empresa_id');

            if ($user->isSuperAdmin() && empty($empresaId)) {
                Yii::$app->session->setFlash('warning', 'Por favor, selecciona una empresa para continuar.');
                return $this->redirect(['empresa/index']);
            }

            $idCancelado = $this->getCancelledStatusId();

            $query = Quote::find()
                ->alias('q')
                ->leftJoin('Lead l', 'q.id_lead = l.id_lead')
                ->with(['lead', 'status']);

            if ($idCancelado) {
                $query->andWhere(['<>', 'q.id_status', $idCancelado]);
            }

            if ($user->isSuperAdmin()) {
                if (!empty($empresaId)) $query->andWhere(['l.id_company' => $empresaId]);
            } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
                $query->andWhere(['l.id_company' => $user->id_company]);
            } elseif ($user->isAgent()) {
                $query->andWhere(['l.id_user' => $user->id_user]);
            }

            $search = Yii::$app->request->get('search', '');
            $status = Yii::$app->request->get('status', '');
            $fecha_inicio = Yii::$app->request->get('fecha_inicio', '');
            $fecha_fin = Yii::$app->request->get('fecha_fin', '');

            if (!empty($search)) {
                $query->andWhere([
                    'or',
                    ['like', 'l.name', $search],
                    ['like', 'l.lastname', $search],
                    ['like', 'l.phone', $search],
                    ['like', 'q.comments', $search],
                ]);
            }

            if (!empty($status)) {
                $statusModel = Status::find()->where(['status' => $status])->one();
                if ($statusModel) $query->andWhere(['q.id_status' => $statusModel->id_status]);
            }

            if (!empty($fecha_inicio)) $query->andWhere(['>=', 'q.date_quote', $fecha_inicio . ' 00:00:00']);
            if (!empty($fecha_fin)) $query->andWhere(['<=', 'q.date_quote', $fecha_fin . ' 23:59:59']);

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                    'pageSizeParam' => 'per-page',
                    'pageParam' => 'page',
                ],
                'sort' => [
                    'defaultOrder' => ['date_quote' => SORT_DESC],
                    'attributes' => [
                        'date_quote' => ['asc' => ['q.date_quote' => SORT_ASC], 'desc' => ['q.date_quote' => SORT_DESC]],
                        'total_amount' => ['asc' => ['q.total_amount' => SORT_ASC], 'desc' => ['q.total_amount' => SORT_DESC]],
                        'id_status' => ['asc' => ['q.id_status' => SORT_ASC], 'desc' => ['q.id_status' => SORT_DESC]],
                    ],
                ],
            ]);

            $quotes = $dataProvider->getModels();
            $totalReal = $query->count();

            $statusPendiente = Status::find()->where(['status' => 'Pendiente'])->one();
            $statusCompletado = Status::find()->where(['status' => 'Completado'])->one();
            
            $idPendiente = $statusPendiente ? $statusPendiente->id_status : null;
            $idCompletado = $statusCompletado ? $statusCompletado->id_status : null;

            $totalPendientesGlobal = 0;
            $totalCompletadosGlobal = 0;
            $totalCanceladosGlobal = 0;
            $montoCompletados = 0;
            $montoCancelados = 0;
            $totalMonto = 0;

            $allQuotes = $query->all();
            
            foreach ($allQuotes as $q) {
                $totalMonto += $q->total_amount ?? 0;
                
                if ($idPendiente && $q->id_status == $idPendiente) {
                    $totalPendientesGlobal++;
                } elseif ($idCompletado && $q->id_status == $idCompletado) {
                    $totalCompletadosGlobal++;
                    $montoCompletados += $q->total_amount ?? 0;
                }
            }

            if ($idCancelado) {
                $canceladasQuery = Quote::find()
                    ->alias('q')
                    ->leftJoin('Lead l', 'q.id_lead = l.id_lead')
                    ->where(['q.id_status' => $idCancelado]);

                if ($user->isSuperAdmin()) {
                    if (!empty($empresaId)) $canceladasQuery->andWhere(['l.id_company' => $empresaId]);
                } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
                    $canceladasQuery->andWhere(['l.id_company' => $user->id_company]);
                } elseif ($user->isAgent()) {
                    $canceladasQuery->andWhere(['l.id_user' => $user->id_user]);
                }

                $canceladas = $canceladasQuery->all();
                $totalCanceladosGlobal = count($canceladas);
                foreach ($canceladas as $c) {
                    $montoCancelados += $c->total_amount ?? 0;
                }
            }

            $statusOptions = Status::find()
                ->select(['status', 'id_status'])
                ->where(['IN', 'status', self::QUOTE_STATUS_LIST])
                ->indexBy('id_status')
                ->column();

            $statusCounts = [];
            foreach ($allQuotes as $q) {
                $statusModel = Status::findOne($q->id_status);
                if ($statusModel) {
                    $name = $statusModel->status;
                    if (!isset($statusCounts[$name])) $statusCounts[$name] = 0;
                    $statusCounts[$name]++;
                }
            }

            return $this->render('index', [
                'dataProvider' => $dataProvider,
                'quotes' => $quotes,
                'totalQuotes' => $totalReal,
                'totalPendientes' => $totalPendientesGlobal,
                'totalCompletados' => $totalCompletadosGlobal,
                'totalCancelados' => $totalCanceladosGlobal,
                'montoCompletados' => $montoCompletados,
                'montoCancelados' => $montoCancelados,
                'totalMonto' => $totalMonto,
                'statusCounts' => $statusCounts,
                'isAdmin' => $user->isAdmin(),
                'isAgent' => $user->isAgent(),
                'isSuperAdmin' => $user->isSuperAdmin(),
                'statusOptions' => $statusOptions,
                'search' => $search,
                'status' => $status,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
            ]);
            
        } catch (\Exception $e) {
            Yii::error('Error en actionIndex: ' . $e->getMessage(), 'quote-controller');
            Yii::$app->session->setFlash('error', 'Error al cargar las cotizaciones: ' . $e->getMessage());
            
            return $this->render('index', [
                'dataProvider' => new ActiveDataProvider(['query' => Quote::find()->where(['1' => '0'])]),
                'quotes' => [],
                'totalQuotes' => 0,
                'totalPendientes' => 0,
                'totalCompletados' => 0,
                'totalCancelados' => 0,
                'montoCompletados' => 0,
                'montoCancelados' => 0,
                'totalMonto' => 0,
                'statusCounts' => [],
                'isAdmin' => false,
                'isAgent' => false,
                'isSuperAdmin' => false,
                'statusOptions' => [],
                'search' => '',
                'status' => '',
                'fecha_inicio' => '',
                'fecha_fin' => '',
            ]);
        }
    }

    // ============================================
    // PAPELERA
    // ============================================
    public function actionTrash()
    {
        try {
            $user = Yii::$app->user->identity;
            
            if (!$user) return $this->redirect(['site/login']);

            if (!$user->isAdmin() && !$user->isSuperAdmin()) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para acceder a la papelera.');
                return $this->redirect(['index']);
            }

            $empresaId = Yii::$app->session->get('empresa_id');
            $idCancelado = $this->getCancelledStatusId();

            $query = Quote::find()
                ->alias('q')
                ->leftJoin('Lead l', 'q.id_lead = l.id_lead')
                ->with(['lead', 'status']);

            if ($idCancelado) {
                $query->andWhere(['q.id_status' => $idCancelado]);
            } else {
                $query->andWhere(['0' => '1']);
            }

            if ($user->isSuperAdmin()) {
                if (!empty($empresaId)) $query->andWhere(['l.id_company' => $empresaId]);
            } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
                $query->andWhere(['l.id_company' => $user->id_company]);
            }

            $search = Yii::$app->request->get('search', '');
            if (!empty($search)) {
                $query->andWhere([
                    'or',
                    ['like', 'l.name', $search],
                    ['like', 'l.lastname', $search],
                    ['like', 'l.phone', $search],
                    ['like', 'q.comments', $search],
                ]);
            }

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                    'pageSizeParam' => 'per-page',
                    'pageParam' => 'page',
                ],
                'sort' => ['defaultOrder' => ['date_quote' => SORT_DESC]],
            ]);

            $quotes = $dataProvider->getModels();
            $totalQuotes = $dataProvider->getTotalCount();

            $statusOptions = Status::find()
                ->select(['status', 'id_status'])
                ->where(['IN', 'status', self::QUOTE_STATUS_LIST])
                ->indexBy('id_status')
                ->column();

            return $this->render('trash', [
                'dataProvider' => $dataProvider,
                'quotes' => $quotes,
                'totalQuotes' => $totalQuotes,
                'search' => $search,
                'statusOptions' => $statusOptions,
                'isAdmin' => $user->isAdmin(),
                'isSuperAdmin' => $user->isSuperAdmin(),
            ]);

        } catch (\Exception $e) {
            Yii::error('Error en actionTrash: ' . $e->getMessage(), 'quote-controller');
            Yii::$app->session->setFlash('error', 'Error al cargar la papelera: ' . $e->getMessage());
            return $this->redirect(['index']);
        }
    }

    // ============================================
    // RESTAURAR
    // ============================================
    public function actionRestore($id)
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            if (!$user->isAdmin() && !$user->isSuperAdmin()) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para restaurar cotizaciones.');
                return $this->redirect(['trash']);
            }

            $model = Quote::find()->with(['lead'])->where(['id_quote' => $id])->one();

            if (!$model) {
                Yii::$app->session->setFlash('error', 'Cotización no encontrada.');
                return $this->redirect(['trash']);
            }

            if ($user->isSuperAdmin() && !empty($empresaId) && $model->lead && $model->lead->id_company != $empresaId) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para restaurar esta cotización.');
                return $this->redirect(['trash']);
            }

            if ($user->isAdmin() && !$user->isSuperAdmin() && $model->lead && $model->lead->id_company != $user->id_company) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para restaurar esta cotización.');
                return $this->redirect(['trash']);
            }

            $idPendiente = $this->getPendingStatusId();
            
            if (!$idPendiente) {
                Yii::$app->session->setFlash('error', 'No se encontró el estado "Pendiente" en la base de datos.');
                return $this->redirect(['trash']);
            }

            $model->id_status = $idPendiente;

            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'Cotización restaurada exitosamente.');
            } else {
                Yii::$app->session->setFlash('error', 'Error al restaurar la cotización.');
            }

        } catch (\Exception $e) {
            Yii::error('Error en actionRestore: ' . $e->getMessage(), 'quote-controller');
            Yii::$app->session->setFlash('error', 'Error al restaurar la cotización.');
        }

        return $this->redirect(['trash']);
    }

    // ============================================
    // MOVER A PAPELERA
    // ============================================
    public function actionDelete($id)
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            if (!$user->isAdmin() && !$user->isSuperAdmin()) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para eliminar cotizaciones.');
                return $this->redirect(['index']);
            }

            $model = Quote::find()->with(['lead'])->where(['id_quote' => $id])->one();

            if (!$model) {
                Yii::$app->session->setFlash('error', 'Cotización no encontrada.');
                return $this->redirect(['index']);
            }

            if ($user->isSuperAdmin() && !empty($empresaId) && $model->lead && $model->lead->id_company != $empresaId) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para eliminar esta cotización.');
                return $this->redirect(['index']);
            }

            if ($user->isAdmin() && !$user->isSuperAdmin() && $model->lead && $model->lead->id_company != $user->id_company) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para eliminar esta cotización.');
                return $this->redirect(['index']);
            }

            $idCancelado = $this->getCancelledStatusId();
            
            if (!$idCancelado) {
                Yii::$app->session->setFlash('error', 'No se encontró el estado "Cancelado" en la base de datos.');
                return $this->redirect(['index']);
            }

            $model->id_status = $idCancelado;

            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'Cotización movida a la papelera.');
            } else {
                Yii::$app->session->setFlash('error', 'Error al mover la cotización a la papelera.');
            }

        } catch (\Exception $e) {
            Yii::error('Error en actionDelete: ' . $e->getMessage(), 'quote-controller');
            Yii::$app->session->setFlash('error', 'Error al eliminar la cotización: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    // ============================================
    // VER COTIZACIÓN - MODAL
    // ============================================
    public function actionViewModal($id)
    {
        Yii::$app->response->format = Response::FORMAT_HTML;
        
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            $model = Quote::find()->with(['lead', 'status'])->where(['id_quote' => $id])->one();
            
            if (!$model) return $this->renderPartial('_view_modal', ['error' => 'La cotización solicitada no existe.']);
            if (!$model->lead) return $this->renderPartial('_view_modal', ['error' => 'La cotización no tiene un lead asociado.']);
            
            if ($user->isAgent() && $model->lead->id_user != $user->id_user) {
                return $this->renderPartial('_view_modal', ['error' => 'No tienes permiso para ver esta cotización.']);
            }
            if ($user->isSuperAdmin() && !empty($empresaId) && $model->lead->id_company != $empresaId) {
                return $this->renderPartial('_view_modal', ['error' => 'No tienes permiso para ver esta cotización.']);
            }
            if ($user->isAdmin() && !$user->isSuperAdmin() && $model->lead->id_company != $user->id_company) {
                return $this->renderPartial('_view_modal', ['error' => 'No tienes permiso para ver esta cotización.']);
            }

            return $this->renderPartial('_view_modal', [
                'model' => $model,
                'isAdmin' => $user->isAdmin(),
                'isAgent' => $user->isAgent(),
                'isSuperAdmin' => $user->isSuperAdmin(),
            ]);
            
        } catch (\Exception $e) {
            Yii::error('Error en actionViewModal: ' . $e->getMessage(), 'quote-controller');
            return $this->renderPartial('_view_modal', ['error' => 'Error al cargar la cotización: ' . $e->getMessage()]);
        }
    }

    // ============================================
    // ACTUALIZAR COTIZACIÓN - MODAL (HTML)
    // ============================================
    public function actionUpdateModal($id)
    {
        Yii::$app->response->format = Response::FORMAT_HTML;
        
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            $model = Quote::find()->with(['lead', 'status'])->where(['id_quote' => $id])->one();
            
            if (!$model) return $this->renderPartial('_update_modal', ['error' => 'La cotización solicitada no existe.']);
            if (!$model->lead) return $this->renderPartial('_update_modal', ['error' => 'La cotización no tiene un lead asociado.']);

            if ($user->isAgent() && $model->lead->id_user != $user->id_user) {
                return $this->renderPartial('_update_modal', ['error' => 'No tienes permiso para editar esta cotización.']);
            }
            if ($user->isSuperAdmin() && !empty($empresaId) && $model->lead->id_company != $empresaId) {
                return $this->renderPartial('_update_modal', ['error' => 'No tienes permiso para editar esta cotización.']);
            }
            if ($user->isAdmin() && !$user->isSuperAdmin() && $model->lead->id_company != $user->id_company) {
                return $this->renderPartial('_update_modal', ['error' => 'No tienes permiso para editar esta cotización.']);
            }

            // 🔥 Guardar el id_lead original para restaurarlo después del POST
            $idLeadOriginal = $model->id_lead;

            $leadsQuery = Lead::find();
            
            if ($user->isAgent()) {
                $leadsQuery->andWhere(['id_user' => $user->id_user]);
            } elseif ($user->isSuperAdmin()) {
                if (!empty($empresaId)) $leadsQuery->andWhere(['id_company' => $empresaId]);
            } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
                $leadsQuery->andWhere(['id_company' => $user->id_company]);
            }
            
            $leads = $leadsQuery->all();
            $leadsList = ArrayHelper::map($leads, 'id_lead', function($lead) {
                return $lead->name . ' ' . $lead->lastname . ' (' . $lead->phone . ')';
            });

            $statusOptions = Status::find()
                ->select(['status', 'id_status'])
                ->where(['IN', 'status', self::QUOTE_STATUS_LIST])
                ->indexBy('id_status')
                ->column();

            // 🔥 GUARDAR
            if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
                try {
                    $model->total_amount = (int) $model->total_amount;

                    // 🔥 SEGURIDAD: Forzar que el id_lead no cambie
                    $model->id_lead = $idLeadOriginal;
                    
                    if ($model->save()) {
                        return $this->renderPartial('_update_modal', [
                            'model' => $model,
                            'leadsList' => $leadsList,
                            'statusOptions' => $statusOptions,
                            'isAdmin' => $user->isAdmin(),
                            'isSuperAdmin' => $user->isSuperAdmin(),
                            'success' => '✅ Cotización actualizada exitosamente'
                        ]);
                    } else {
                        $errors = $model->getErrors();
                        $errorMessages = [];
                        foreach ($errors as $attribute => $errorList) {
                            $label = $model->getAttributeLabel($attribute);
                            $errorMessages[] = $label . ': ' . implode(', ', $errorList);
                        }
                        return $this->renderPartial('_update_modal', [
                            'model' => $model,
                            'leadsList' => $leadsList,
                            'statusOptions' => $statusOptions,
                            'isAdmin' => $user->isAdmin(),
                            'isSuperAdmin' => $user->isSuperAdmin(),
                            'error' => 'Error al guardar:<br>' . implode('<br>', $errorMessages)
                        ]);
                    }
                } catch (\Exception $e) {
                    return $this->renderPartial('_update_modal', [
                        'model' => $model,
                        'leadsList' => $leadsList,
                        'statusOptions' => $statusOptions,
                        'isAdmin' => $user->isAdmin(),
                        'isSuperAdmin' => $user->isSuperAdmin(),
                        'error' => 'Error al actualizar: ' . $e->getMessage()
                    ]);
                }
            }

            return $this->renderPartial('_update_modal', [
                'model' => $model,
                'leadsList' => $leadsList,
                'statusOptions' => $statusOptions,
                'isAdmin' => $user->isAdmin(),
                'isSuperAdmin' => $user->isSuperAdmin(),
            ]);
            
        } catch (\Exception $e) {
            Yii::error('Error en actionUpdateModal: ' . $e->getMessage(), 'quote-controller');
            return $this->renderPartial('_update_modal', ['error' => 'Error al cargar el formulario: ' . $e->getMessage()]);
        }
    }

    // ============================================
    // VER COTIZACIÓN (Vista completa)
    // ============================================
    public function actionView($id)
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            $model = Quote::find()->with(['lead', 'status'])->where(['id_quote' => $id])->one();
            
            if (!$model) throw new NotFoundHttpException('La cotización solicitada no existe.');
            if (!$model->lead) {
                Yii::$app->session->setFlash('error', 'La cotización no tiene un lead asociado.');
                return $this->redirect(['index']);
            }
            
            if ($user->isAgent() && $model->lead->id_user != $user->id_user) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para ver esta cotización.');
                return $this->redirect(['index']);
            }
            if ($user->isSuperAdmin() && !empty($empresaId) && $model->lead->id_company != $empresaId) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para ver esta cotización.');
                return $this->redirect(['index']);
            }
            if ($user->isAdmin() && !$user->isSuperAdmin() && $model->lead->id_company != $user->id_company) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para ver esta cotización.');
                return $this->redirect(['index']);
            }

            $statusOptions = Status::find()
                ->select(['status', 'id_status'])
                ->where(['IN', 'status', self::QUOTE_STATUS_LIST])
                ->indexBy('id_status')
                ->column();

            return $this->render('view', [
                'model' => $model,
                'isAdmin' => $user->isAdmin(),
                'isAgent' => $user->isAgent(),
                'isSuperAdmin' => $user->isSuperAdmin(),
                'statusOptions' => $statusOptions,
            ]);
            
        } catch (\Exception $e) {
            Yii::error('Error en actionView: ' . $e->getMessage(), 'quote-controller');
            Yii::$app->session->setFlash('error', 'Error al cargar la cotización: ' . $e->getMessage());
            return $this->redirect(['index']);
        }
    }

    // ============================================
    // DETALLES
    // ============================================
    public function actionDetails($id)
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            $model = Quote::find()->with(['lead', 'status'])->where(['id_quote' => $id])->one();
            
            if (!$model) throw new NotFoundHttpException('La cotización solicitada no existe.');
            if (!$model->lead) {
                Yii::$app->session->setFlash('error', 'La cotización no tiene un lead asociado.');
                return $this->redirect(['index']);
            }
            
            if ($user->isAgent() && $model->lead->id_user != $user->id_user) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para ver esta cotización.');
                return $this->redirect(['index']);
            }
            if ($user->isSuperAdmin() && !empty($empresaId) && $model->lead->id_company != $empresaId) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para ver esta cotización.');
                return $this->redirect(['index']);
            }
            if ($user->isAdmin() && !$user->isSuperAdmin() && $model->lead->id_company != $user->id_company) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para ver esta cotización.');
                return $this->redirect(['index']);
            }

            return $this->render('details', [
                'model' => $model,
                'isAdmin' => $user->isAdmin(),
                'isAgent' => $user->isAgent(),
                'isSuperAdmin' => $user->isSuperAdmin(),
            ]);
            
        } catch (NotFoundHttpException $e) {
            Yii::$app->session->setFlash('error', 'La cotización solicitada no existe.');
            return $this->redirect(['index']);
        } catch (\Exception $e) {
            Yii::error('Error en actionDetails: ' . $e->getMessage(), 'quote-controller');
            Yii::$app->session->setFlash('error', 'Error al cargar los detalles de la cotización.');
            return $this->redirect(['index']);
        }
    }

    // ============================================
    // CREAR COTIZACIÓN
    // ============================================
    public function actionCreate($leadId = null)
    {
        try {
            $model = new Quote();
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            if ($user->isSuperAdmin() && empty($empresaId)) {
                Yii::$app->session->setFlash('warning', 'Por favor, selecciona una empresa para continuar.');
                return $this->redirect(['empresa/index']);
            }

            // 🔥 Solo inicializar campos que existen en la tabla
            $model->date_quote = date('Y-m-d');
            $model->hour_quote = date('H:i');
            $model->total_amount = 0;

            $idPendiente = $this->getPendingStatusId();
            if ($idPendiente) $model->id_status = $idPendiente;

            if ($leadId) {
                $lead = Lead::findOne($leadId);
                if ($lead) {
                    if ($user->isAgent() && $lead->id_user != $user->id_user) {
                        Yii::$app->session->setFlash('error', 'No tienes permiso para crear cotizaciones para este lead.');
                        return $this->redirect(['lead/index']);
                    }
                    if ($user->isSuperAdmin() && !empty($empresaId) && $lead->id_company != $empresaId) {
                        Yii::$app->session->setFlash('error', 'No tienes permiso para crear cotizaciones para este lead.');
                        return $this->redirect(['lead/index']);
                    }
                    $model->id_lead = $leadId;
                }
            }

            $statusOptions = Status::find()
                ->select(['status', 'id_status'])
                ->where(['IN', 'status', self::QUOTE_STATUS_LIST])
                ->indexBy('id_status')
                ->column();

            if ($model->load(Yii::$app->request->post())) {
                $transaction = Yii::$app->db->beginTransaction();
                
                try {
                    if (empty($model->id_lead)) {
                        Yii::$app->session->setFlash('error', 'Debes seleccionar un lead.');
                        return $this->redirect(['create']);
                    }

                    $model->total_amount = (int) $model->total_amount;

                    if ($model->total_amount <= 0) {
                        Yii::$app->session->setFlash('error', 'El monto total debe ser mayor a 0.');
                        return $this->redirect(['create']);
                    }

                    if (empty($model->id_status) && $idPendiente) $model->id_status = $idPendiente;

                    if (!empty($model->comments)) {
                        $decoded = json_decode($model->comments, true);
                        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                            $model->setNotes($model->comments);
                        }
                    }

                    if ($model->save()) {
                        $lead = Lead::findOne($model->id_lead);
                        if ($lead) {
                            $statusInteresado = Status::find()->where(['status' => 'Interesado'])->one();
                            if (!$statusInteresado) $statusInteresado = Status::find()->where(['status' => 'Contactado'])->one();
                            if ($statusInteresado) {
                                $lead->id_status = $statusInteresado->id_status;
                                $lead->save(false);
                            }
                        }
                        
                        $transaction->commit();
                        Yii::$app->session->setFlash('success', 'Cotización creada exitosamente.');
                        return $this->redirect(['details', 'id' => $model->id_quote]);
                    } else {
                        $transaction->rollBack();
                        $errors = $model->getErrors();
                        $errorMessages = [];
                        foreach ($errors as $attribute => $errorList) {
                            $label = $model->getAttributeLabel($attribute);
                            $errorMessages[] = $label . ': ' . implode(', ', $errorList);
                        }
                        Yii::$app->session->setFlash('error', 'Error al guardar:<br>' . implode('<br>', $errorMessages));
                    }
                    
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    Yii::error('Error en actionCreate: ' . $e->getMessage(), 'quote-controller');
                    Yii::$app->session->setFlash('error', 'Error al crear la cotización: ' . $e->getMessage());
                }
            }

            $leadsQuery = Lead::find()->where(['not in', 'id_status', [1, 10]]);
            
            if ($user->isAgent()) {
                $leadsQuery->andWhere(['id_user' => $user->id_user]);
            } elseif ($user->isSuperAdmin()) {
                if (!empty($empresaId)) $leadsQuery->andWhere(['id_company' => $empresaId]);
                else $leadsQuery->andWhere(['0' => '1']);
            } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
                $leadsQuery->andWhere(['id_company' => $user->id_company]);
            }
            
            $leads = $leadsQuery->orderBy(['name' => SORT_ASC])->all();
            $leadsList = ArrayHelper::map($leads, 'id_lead', function($lead) {
                return $lead->name . ' ' . $lead->lastname . ' (' . $lead->phone . ')';
            });

            return $this->render('create', [
                'model' => $model,
                'leadsList' => $leadsList,
                'statusOptions' => $statusOptions,
                'isAdmin' => $user->isAdmin(),
                'isAgent' => $user->isAgent(),
                'isSuperAdmin' => $user->isSuperAdmin(),
            ]);
            
        } catch (\Exception $e) {
            Yii::error('Error en actionCreate: ' . $e->getMessage(), 'quote-controller');
            Yii::$app->session->setFlash('error', 'Error al cargar el formulario: ' . $e->getMessage());
            return $this->redirect(['index']);
        }
    }

    // ============================================
    // ACTUALIZAR COTIZACIÓN (Vista completa)
    // ============================================
    public function actionUpdate($id)
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            $model = Quote::find()->with(['lead', 'status'])->where(['id_quote' => $id])->one();
            
            if (!$model) throw new NotFoundHttpException('La cotización solicitada no existe.');
            if (!$model->lead) {
                Yii::$app->session->setFlash('error', 'La cotización no tiene un lead asociado.');
                return $this->redirect(['index']);
            }

            if ($user->isAgent() && $model->lead->id_user != $user->id_user) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para editar esta cotización.');
                return $this->redirect(['index']);
            }
            if ($user->isSuperAdmin() && !empty($empresaId) && $model->lead->id_company != $empresaId) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para editar esta cotización.');
                return $this->redirect(['index']);
            }
            if ($user->isAdmin() && !$user->isSuperAdmin() && $model->lead->id_company != $user->id_company) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para editar esta cotización.');
                return $this->redirect(['index']);
            }

            $statusOptions = Status::find()
                ->select(['status', 'id_status'])
                ->where(['IN', 'status', self::QUOTE_STATUS_LIST])
                ->indexBy('id_status')
                ->column();

            if ($model->load(Yii::$app->request->post())) {
                try {
                    $model->total_amount = (int) $model->total_amount;
                    
                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', 'Cotización actualizada exitosamente.');
                        return $this->redirect(['details', 'id' => $model->id_quote]);
                    } else {
                        $errors = $model->getErrors();
                        $errorMessages = [];
                        foreach ($errors as $attribute => $errorList) {
                            $label = $model->getAttributeLabel($attribute);
                            $errorMessages[] = $label . ': ' . implode(', ', $errorList);
                        }
                        Yii::$app->session->setFlash('error', 'Error al guardar:<br>' . implode('<br>', $errorMessages));
                    }
                } catch (\Exception $e) {
                    Yii::error('Error en actionUpdate: ' . $e->getMessage(), 'quote-controller');
                    Yii::$app->session->setFlash('error', 'Error al actualizar: ' . $e->getMessage());
                }
            }

            $leadsQuery = Lead::find();
            
            if ($user->isAgent()) {
                $leadsQuery->andWhere(['id_user' => $user->id_user]);
            } elseif ($user->isSuperAdmin()) {
                if (!empty($empresaId)) $leadsQuery->andWhere(['id_company' => $empresaId]);
            } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
                $leadsQuery->andWhere(['id_company' => $user->id_company]);
            }
            
            $leads = $leadsQuery->all();
            $leadsList = ArrayHelper::map($leads, 'id_lead', function($lead) {
                return $lead->name . ' ' . $lead->lastname . ' (' . $lead->phone . ')';
            });

            return $this->render('update', [
                'model' => $model,
                'leadsList' => $leadsList,
                'statusOptions' => $statusOptions,
                'isAdmin' => $user->isAdmin(),
                'isAgent' => $user->isAgent(),
                'isSuperAdmin' => $user->isSuperAdmin(),
            ]);
            
        } catch (\Exception $e) {
            Yii::error('Error en actionUpdate: ' . $e->getMessage(), 'quote-controller');
            Yii::$app->session->setFlash('error', 'Error al cargar el formulario: ' . $e->getMessage());
            return $this->redirect(['index']);
        }
    }

    // ============================================
    // ACTUALIZAR ESTADO
    // ============================================
    public function actionUpdateStatus()
    {
        try {
            $request = Yii::$app->request;
            $id = $request->post('id_quote');
            $status = $request->post('status');

            $model = Quote::findOne($id);
            
            if (!$model) throw new NotFoundHttpException('La cotización solicitada no existe.');
            
            $user = Yii::$app->user->identity;

            if (!$model->lead) {
                Yii::$app->session->setFlash('error', 'La cotización no tiene un lead asociado.');
                return $this->redirect(['index']);
            }

            if ($user->isAgent() && $model->lead->id_user != $user->id_user) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para cambiar el estado.');
                return $this->redirect(['index']);
            }

            $statusModel = Status::find()->where(['status' => $status])->one();
            if (!$statusModel) {
                Yii::$app->session->setFlash('error', 'Estado no válido.');
                return $this->redirect(['index']);
            }

            $transaction = Yii::$app->db->beginTransaction();
            
            try {
                $model->id_status = $statusModel->id_status;
                
                if ($status == 'Aprobada' || $status == 'Pagada') {
                    $lead = Lead::findOne($model->id_lead);
                    if ($lead) {
                        $statusCliente = Status::find()->where(['status' => 'Cliente'])->one();
                        if ($statusCliente) {
                            $lead->id_status = $statusCliente->id_status;
                            $lead->save();
                        }
                    }
                }

                if ($model->save()) {
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Estado actualizado exitosamente.');
                } else {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', 'Error al actualizar el estado.');
                }
                
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error('Error en actionUpdateStatus: ' . $e->getMessage(), 'quote-controller');
                Yii::$app->session->setFlash('error', 'Error al actualizar el estado: ' . $e->getMessage());
            }

        } catch (\Exception $e) {
            Yii::error('Error en actionUpdateStatus: ' . $e->getMessage(), 'quote-controller');
            Yii::$app->session->setFlash('error', 'Error al actualizar el estado: ' . $e->getMessage());
        }

        return $this->redirect(['details', 'id' => $id]);
    }
}