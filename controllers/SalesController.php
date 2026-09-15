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

class SalesController extends Controller
{
    public $layout = 'main';

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'update' => ['POST'],
                ],
            ],
        ];
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
        // 🔥 BUSCAR ESTADOS
        // ============================================
        $statusCompletado = Status::find()->where(['status' => 'completado'])->one();
        $statusCancelado = Status::find()->where(['status' => 'cancelado'])->one();
        $statusPagada = Status::find()->where(['status' => 'pagada'])->one();
        $statusAprobada = Status::find()->where(['status' => 'aprobada'])->one();
        
        $idStatusCompletado = $statusCompletado ? $statusCompletado->id_status : null;
        $idStatusCancelado = $statusCancelado ? $statusCancelado->id_status : null;
        $idStatusPagada = $statusPagada ? $statusPagada->id_status : null;
        $idStatusAprobada = $statusAprobada ? $statusAprobada->id_status : null;

        // ============================================
        // 🔥 OBTENER FILTROS
        // ============================================
        $search = Yii::$app->request->get('search', '');
        $status = Yii::$app->request->get('status', '');
        $fecha_inicio = Yii::$app->request->get('fecha_inicio', '');
        $fecha_fin = Yii::$app->request->get('fecha_fin', '');

        // ============================================
        // 🔥 IDs DE ESTADOS VENDIDOS
        // ============================================
        $vendidosIds = [];
        if ($idStatusCompletado) $vendidosIds[] = $idStatusCompletado;
        if ($idStatusPagada) $vendidosIds[] = $idStatusPagada;
        if ($idStatusAprobada) $vendidosIds[] = $idStatusAprobada;

        // ============================================
        // 🔥 DETECTAR SI EL FILTRO ES "CANCELADO"
        // ============================================
        $statusModelFilter = null;
        $filtrandoCancelado = false;
        
        if (!empty($status)) {
            $statusModelFilter = Status::find()->where(['status' => $status])->one();
            if ($statusModelFilter && $statusModelFilter->id_status == $idStatusCancelado) {
                $filtrandoCancelado = true;
            }
        }

        // ============================================
        // 🔥 SUBQUERY BASE PARA VENDIDOS
        // ============================================
        $subQueryVendidos = Quote::find()
            ->alias('q')
            ->select(['q.id_quote'])
            ->leftJoin('Lead l', 'q.id_lead = l.id_lead')
            ->where(['not in', 'l.id_status', [1, 10]])
            ->andWhere(['not in', 'q.id_status', [10]]);

        // Si está filtrando por "Cancelado", buscar SOLO cancelados
        // Si no, buscar los vendidos (completado/pagada/aprobada)
        if ($filtrandoCancelado) {
            if ($idStatusCancelado) {
                $subQueryVendidos->andWhere(['q.id_status' => $idStatusCancelado]);
            } else {
                $subQueryVendidos->andWhere(['0' => '1']);
            }
        } else {
            if (!empty($vendidosIds)) {
                $subQueryVendidos->andWhere(['q.id_status' => $vendidosIds]);
            } else {
                $subQueryVendidos->andWhere(['0' => '1']);
            }
            
            // Si hay un filtro de estado distinto a cancelado, aplicarlo
            if ($statusModelFilter && !$filtrandoCancelado) {
                $subQueryVendidos->andWhere(['q.id_status' => $statusModelFilter->id_status]);
            }
        }

        // FILTROS POR ROL Y EMPRESA
        if ($user->isSuperAdmin()) {
            if (!empty($empresaId)) {
                $subQueryVendidos->andWhere(['l.id_company' => $empresaId]);
            }
        } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
            $subQueryVendidos->andWhere(['l.id_company' => $user->id_company]);
        } elseif ($user->isAgent()) {
            $subQueryVendidos->andWhere(['l.id_user' => $user->id_user]);
        }

        // FILTROS DE BÚSQUEDA
        if (!empty($search)) {
            $subQueryVendidos->andWhere(['or',
                ['like', 'l.name', $search],
                ['like', 'l.lastname', $search],
                ['like', 'l.phone', $search],
                ['like', 'q.id_quote', $search],
            ]);
        }

        if (!empty($fecha_inicio)) {
            $subQueryVendidos->andWhere(['>=', 'q.date_quote', $fecha_inicio]);
        }
        if (!empty($fecha_fin)) {
            $subQueryVendidos->andWhere(['<=', 'q.date_quote', $fecha_fin]);
        }

        // ============================================
        // 🔥 QUERY PRINCIPAL PARA VENDIDOS (con subquery)
        // ============================================
        $queryVendidos = Quote::find()
            ->alias('q')
            ->select(['q.*'])
            ->where(['q.id_quote' => $subQueryVendidos])
            ->orderBy(['q.date_quote' => SORT_DESC, 'q.hour_quote' => SORT_DESC]);

        // ============================================
        // 🔥 DATAPROVIDER CON PAGINACIÓN
        // ============================================
        $dataProvider = new ActiveDataProvider([
            'query' => $queryVendidos,
            'pagination' => [
                'pageSize' => 10,
                'pageSizeParam' => 'per-page',
                'pageParam' => 'page',
            ],
            'sort' => [
                'defaultOrder' => [
                    'date_quote' => SORT_DESC,
                ],
                'attributes' => [
                    'date_quote' => [
                        'asc' => ['q.date_quote' => SORT_ASC],
                        'desc' => ['q.date_quote' => SORT_DESC],
                    ],
                    'total_amount' => [
                        'asc' => ['q.total_amount' => SORT_ASC],
                        'desc' => ['q.total_amount' => SORT_DESC],
                    ],
                    'id_quote' => [
                        'asc' => ['q.id_quote' => SORT_ASC],
                        'desc' => ['q.id_quote' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        // 🔥 OBTENER VENTAS PAGINADAS
        $sales = $dataProvider->getModels();

        // Cargar la relación lead manualmente (porque usamos leftJoin literal)
        foreach ($sales as $sale) {
            $sale->populateRelation('lead', $sale->getLead()->one());
        }

        // ============================================
        // 🔥 TODAS LAS VENTAS FILTRADAS (para métricas y últimas ventas)
        // ============================================
        $todasLasVentas = Quote::find()
            ->alias('q')
            ->select(['q.*'])
            ->where(['q.id_quote' => $subQueryVendidos])
            ->orderBy(['q.date_quote' => SORT_DESC, 'q.hour_quote' => SORT_DESC])
            ->all();

        foreach ($todasLasVentas as $sale) {
            $sale->populateRelation('lead', $sale->getLead()->one());
        }

        // ============================================
        // 🔥 SUBQUERY PARA PERDIDOS (SIEMPRE, para métricas globales)
        // ============================================
        $subQueryPerdidos = Quote::find()
            ->alias('q')
            ->select(['q.id_quote'])
            ->leftJoin('Lead l', 'q.id_lead = l.id_lead')
            ->where(['not in', 'l.id_status', [1, 10]])
            ->andWhere(['not in', 'q.id_status', [10]]);

        if ($idStatusCancelado) {
            $subQueryPerdidos->andWhere(['q.id_status' => $idStatusCancelado]);
        } else {
            $subQueryPerdidos->andWhere(['0' => '1']);
        }

        // FILTROS POR ROL Y EMPRESA para perdidos
        if ($user->isSuperAdmin()) {
            if (!empty($empresaId)) {
                $subQueryPerdidos->andWhere(['l.id_company' => $empresaId]);
            }
        } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
            $subQueryPerdidos->andWhere(['l.id_company' => $user->id_company]);
        } elseif ($user->isAgent()) {
            $subQueryPerdidos->andWhere(['l.id_user' => $user->id_user]);
        }

        $perdidos = Quote::find()
            ->alias('q')
            ->select(['q.*'])
            ->where(['q.id_quote' => $subQueryPerdidos])
            ->all();

        foreach ($perdidos as $p) {
            $p->populateRelation('lead', $p->getLead()->one());
        }

        // ============================================
        // 🔥 CALCULAR ESTADÍSTICAS DE VENDIDOS
        // 🔥 Importante: usar subquery "base" sin el filtro de estado
        //    para que las métricas siempre reflejen los totales reales
        // ============================================
        
        // Subquery SIN filtro de estado (para métricas)
        $subQueryVendidosMetricas = Quote::find()
            ->alias('q')
            ->select(['q.id_quote'])
            ->leftJoin('Lead l', 'q.id_lead = l.id_lead')
            ->where(['not in', 'l.id_status', [1, 10]])
            ->andWhere(['not in', 'q.id_status', [10]]);

        if (!empty($vendidosIds)) {
            $subQueryVendidosMetricas->andWhere(['q.id_status' => $vendidosIds]);
        } else {
            $subQueryVendidosMetricas->andWhere(['0' => '1']);
        }

        if ($user->isSuperAdmin()) {
            if (!empty($empresaId)) {
                $subQueryVendidosMetricas->andWhere(['l.id_company' => $empresaId]);
            }
        } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
            $subQueryVendidosMetricas->andWhere(['l.id_company' => $user->id_company]);
        } elseif ($user->isAgent()) {
            $subQueryVendidosMetricas->andWhere(['l.id_user' => $user->id_user]);
        }

        $ventasParaMetricas = Quote::find()
            ->alias('q')
            ->select(['q.*'])
            ->where(['q.id_quote' => $subQueryVendidosMetricas])
            ->all();

        $totalVendidos = count($ventasParaMetricas);
        $montoVendidos = 0;
        $totalPending = 0;
        $totalPendientesPago = 0;
        $thisMonthSales = 0;
        $thisMonth = date('Y-m');

        foreach ($ventasParaMetricas as $sale) {
            $montoVendidos += (int)$sale->total_amount;
            $pending = (int)$sale->pending_payment;
            if ($pending > 0) {
                $totalPending += $pending;
                $totalPendientesPago++;
            }
            if (substr($sale->date_quote, 0, 7) == $thisMonth) {
                $thisMonthSales++;
            }
        }

        $montoPromedio = $totalVendidos > 0 ? $montoVendidos / $totalVendidos : 0;

        // ============================================
        // 🔥 CALCULAR ESTADÍSTICAS DE PERDIDOS
        // ============================================
        $totalPerdidos = count($perdidos);
        $montoPerdidos = 0;
        foreach ($perdidos as $p) {
            $montoPerdidos += (int)$p->total_amount;
        }

        // ============================================
        // 🔥 LISTA DE ESTADOS PARA EL FILTRO
        // ============================================
        $statusList = [];
        if ($idStatusCompletado) $statusList[$idStatusCompletado] = 'Completado';
        if ($idStatusPagada) $statusList[$idStatusPagada] = 'Pagada';
        if ($idStatusAprobada) $statusList[$idStatusAprobada] = 'Aprobada';
        if ($idStatusCancelado) $statusList[$idStatusCancelado] = 'Cancelado';

        // Últimas 5 ventas (solo si no está filtrando por cancelado)
        if ($filtrandoCancelado) {
            // Si filtra por cancelado, mostrar los últimos cancelados
            $ultimasVentas = array_slice($perdidos, 0, 5);
        } else {
            // Si no, mostrar las últimas ventas exitosas
            $ultimasVentas = array_slice($ventasParaMetricas, 0, 5);
        }

        return $this->render('index', [
            'sales' => $sales,
            'dataProvider' => $dataProvider,
            'ultimasVentas' => $ultimasVentas,
            'totalVendidos' => $totalVendidos,
            'montoVendidos' => $montoVendidos,
            'totalPending' => $totalPending,
            'totalPerdidos' => $totalPerdidos,
            'montoPerdidos' => $montoPerdidos,
            'thisMonthSales' => $thisMonthSales,
            'totalPendientesPago' => $totalPendientesPago,
            'montoPromedio' => $montoPromedio,
            'isAdmin' => $user->isAdmin(),
            'isAgent' => $user->isAgent(),
            'isSuperAdmin' => $user->isSuperAdmin(),
            'search' => $search,
            'status' => $status,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'statusList' => $statusList,
        ]);
    }

    public function actionView($id)
    {
        $user = Yii::$app->user->identity;
        $empresaId = Yii::$app->session->get('empresa_id');
        
        if ($user->isSuperAdmin() && empty($empresaId)) {
            Yii::$app->session->setFlash('warning', 'Por favor, selecciona una empresa para continuar.');
            return $this->redirect(['empresa/index']);
        }

        // Verificar que sea una cotización vendida
        $statusCompletado = Status::find()->where(['status' => 'completado'])->one();
        $statusPagada = Status::find()->where(['status' => 'pagada'])->one();
        $statusAprobada = Status::find()->where(['status' => 'aprobada'])->one();
        
        $idsPermitidos = [];
        if ($statusCompletado) $idsPermitidos[] = $statusCompletado->id_status;
        if ($statusPagada) $idsPermitidos[] = $statusPagada->id_status;
        if ($statusAprobada) $idsPermitidos[] = $statusAprobada->id_status;

        $model = Quote::find()
            ->alias('q')
            ->select(['q.*'])
            ->leftJoin('Lead l', 'q.id_lead = l.id_lead')
            ->where(['q.id_quote' => $id])
            ->andWhere(['IN', 'q.id_status', $idsPermitidos])
            ->one();
        
        if (!$model) {
            throw new NotFoundHttpException('La cotización solicitada no existe o no está completada.');
        }

        $model->populateRelation('lead', $model->getLead()->one());
        
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

        return $this->render('view', [
            'model' => $model,
            'isAdmin' => $user->isAdmin(),
            'isAgent' => $user->isAgent(),
            'isSuperAdmin' => $user->isSuperAdmin(),
        ]);
    }

    public function actionUpdate($id)
    {
        $user = Yii::$app->user->identity;
        $empresaId = Yii::$app->session->get('empresa_id');
        
        if ($user->isSuperAdmin() && empty($empresaId)) {
            Yii::$app->session->setFlash('warning', 'Por favor, selecciona una empresa para continuar.');
            return $this->redirect(['empresa/index']);
        }

        // Verificar que sea una cotización vendida
        $statusCompletado = Status::find()->where(['status' => 'completado'])->one();
        $statusPagada = Status::find()->where(['status' => 'pagada'])->one();
        $statusAprobada = Status::find()->where(['status' => 'aprobada'])->one();
        
        $idsPermitidos = [];
        if ($statusCompletado) $idsPermitidos[] = $statusCompletado->id_status;
        if ($statusPagada) $idsPermitidos[] = $statusPagada->id_status;
        if ($statusAprobada) $idsPermitidos[] = $statusAprobada->id_status;

        $model = Quote::find()
            ->alias('q')
            ->select(['q.*'])
            ->leftJoin('Lead l', 'q.id_lead = l.id_lead')
            ->where(['q.id_quote' => $id])
            ->andWhere(['IN', 'q.id_status', $idsPermitidos])
            ->one();
        
        if (!$model) {
            throw new NotFoundHttpException('La cotización solicitada no existe o no está completada.');
        }

        $model->populateRelation('lead', $model->getLead()->one());

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

        if ($model->load(Yii::$app->request->post())) {
            $model->pending_payment = (int)$model->total_amount - (int)$model->down_payment;
            if ($model->pending_payment < 0) {
                $model->pending_payment = 0;
            }
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Cotización actualizada exitosamente.');
                return $this->redirect(['view', 'id' => $model->id_quote]);
            }
        }

        $statusList = Status::find()
            ->where(['IN', 'status', ['pendiente', 'aprobada', 'rechazada', 'pagada', 'cancelada', 'completado']])
            ->select(['status', 'id_status'])
            ->indexBy('id_status')
            ->column();

        return $this->render('update', [
            'model' => $model,
            'statusList' => $statusList,
            'isAdmin' => $user->isAdmin(),
            'isAgent' => $user->isAgent(),
            'isSuperAdmin' => $user->isSuperAdmin(),
        ]);
    }

    public function actionPrint($id)
    {
        Yii::$app->session->setFlash('info', 'Función de impresión en desarrollo.');
        return $this->redirect(['view', 'id' => $id]);
    }
}