<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use app\models\Report;
use app\models\Status;
use app\models\Company;
use app\models\Lead;

class ReportController extends Controller
{
    public $layout = 'main';

    const REPORT_STATUS_LIST = ['activo', 'completado', 'pendiente', 'cancelado', 'inactivo'];

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete'        => ['POST', 'GET'],  // 🔥 Acepta ambos
                    'restore'       => ['POST', 'GET'],  // 🔥 Acepta ambos
                    'update-status' => ['POST'],
                ],
            ],
        ];
    }

    // ============================================
    // LISTA DE EVALUACIONES (SIN PAPELERA)
    // ============================================
    public function actionIndex()
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            if (!$user) {
                return $this->redirect(['site/login']);
            }

            if ($user->isSuperAdmin() && empty($empresaId)) {
                Yii::$app->session->setFlash('warning', 'Por favor, selecciona una empresa para continuar.');
                return $this->redirect(['empresa/index']);
            }

            // 🔥 QUERY BASE
            $query = Report::find()->alias('r')->with(['status', 'user', 'company', 'lead']);

            // 🔥 EXCLUIR PAPELERA (Inactivo / Cancelado)
            $trashStatusIds = $this->getTrashStatusIds();
            if (!empty($trashStatusIds)) {
                $query->andWhere(['NOT IN', 'r.id_status', $trashStatusIds]);
            }

            // FILTRO POR ROL Y EMPRESA
            if ($user->isSuperAdmin()) {
                if (!empty($empresaId)) {
                    $query->andWhere(['r.id_company' => $empresaId]);
                }
            } elseif (!$user->isSuperAdmin()) {
                $query->andWhere(['r.id_company' => $user->id_company]);
            }

            // Filtros
            $search       = Yii::$app->request->get('search', '');
            $status       = Yii::$app->request->get('status', '');
            $fecha_inicio = Yii::$app->request->get('fecha_inicio', '');
            $fecha_fin    = Yii::$app->request->get('fecha_fin', '');

            if (!empty($search)) {
                $query->andWhere(['LIKE', 'r.report_name', $search]);
            }

            if (!empty($status)) {
                $statusModel = Status::find()->where(['status' => $status])->one();
                if ($statusModel) {
                    $query->andWhere(['r.id_status' => $statusModel->id_status]);
                }
            }

            if (!empty($fecha_inicio)) {
                $query->andWhere(['>=', 'r.date_report', $fecha_inicio]);
            }
            if (!empty($fecha_fin)) {
                $query->andWhere(['<=', 'r.date_report', $fecha_fin]);
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
                        'date_report' => SORT_DESC,
                    ],
                    'attributes' => [
                        'report_name' => [
                            'asc' => ['r.report_name' => SORT_ASC],
                            'desc' => ['r.report_name' => SORT_DESC],
                        ],
                        'report_type' => [
                            'asc' => ['r.report_type' => SORT_ASC],
                            'desc' => ['r.report_type' => SORT_DESC],
                        ],
                        'date_report' => [
                            'asc' => ['r.date_report' => SORT_ASC],
                            'desc' => ['r.date_report' => SORT_DESC],
                        ],
                        'id_status' => [
                            'asc' => ['r.id_status' => SORT_ASC],
                            'desc' => ['r.id_status' => SORT_DESC],
                        ],
                        'id_lead' => [
                            'asc' => ['r.id_lead' => SORT_ASC],
                            'desc' => ['r.id_lead' => SORT_DESC],
                        ],
                    ],
                ],
            ]);

            $reports = $dataProvider->getModels();

            // 🔥 ESTADÍSTICAS
            $countQuery = clone $query;
            $totalReports = $countQuery->count();

            $statusCounts = [];
            $allReports = $countQuery->all();
            foreach ($allReports as $r) {
                try {
                    $name = $r->getStatusName();
                    if (!isset($statusCounts[$name])) $statusCounts[$name] = 0;
                    $statusCounts[$name]++;
                } catch (\Exception $e) {}
            }

            $statusOptions = Status::find()
                ->select(['status', 'id_status'])
                ->where(['IN', 'status', self::REPORT_STATUS_LIST])
                ->indexBy('id_status')
                ->column();

            return $this->render('index', [
                'dataProvider'  => $dataProvider,
                'reports'       => $reports,
                'totalReports'  => $totalReports,
                'statusCounts'  => $statusCounts,
                'isAdmin'       => $user->isAdmin(),
                'isAgent'       => $user->isAgent(),
                'isSuperAdmin'  => $user->isSuperAdmin(),
                'statusOptions' => $statusOptions,
                'search'        => $search,
                'status'        => $status,
                'fecha_inicio'  => $fecha_inicio,
                'fecha_fin'     => $fecha_fin,
            ]);

        } catch (\Exception $e) {
            Yii::error('Error en actionIndex: ' . $e->getMessage(), 'report-controller');
            Yii::$app->session->setFlash('error', 'Error al cargar las evaluaciones: ' . $e->getMessage());

            return $this->render('index', [
                'dataProvider'  => new ActiveDataProvider([
                    'query' => Report::find()->where('1=0'),
                ]),
                'reports'       => [],
                'totalReports'  => 0,
                'statusCounts'  => [],
                'isAdmin'       => false,
                'isAgent'       => false,
                'isSuperAdmin'  => false,
                'statusOptions' => [],
                'search'        => '',
                'status'        => '',
                'fecha_inicio'  => '',
                'fecha_fin'     => '',
            ]);
        }
    }

    // ============================================
    // PAPELERA DE EVALUACIONES
    // ============================================
    public function actionTrash()
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            if (!$user) {
                return $this->redirect(['site/login']);
            }

            if ($user->isSuperAdmin() && empty($empresaId)) {
                Yii::$app->session->setFlash('warning', 'Por favor, selecciona una empresa para continuar.');
                return $this->redirect(['empresa/index']);
            }

            // 🔥 Estados considerados "papelera"
            $trashStatusIds = $this->getTrashStatusIds();

            if (empty($trashStatusIds)) {
                Yii::$app->session->setFlash('warning', 'No hay estados de papelera configurados (Inactivo/Cancelado).');
                return $this->redirect(['index']);
            }

            // 🔥 QUERY BASE
            $query = Report::find()
                ->alias('r')
                ->with(['status', 'user', 'company', 'lead'])
                ->andWhere(['IN', 'r.id_status', $trashStatusIds]);

            // FILTRO POR ROL Y EMPRESA
            if ($user->isSuperAdmin()) {
                if (!empty($empresaId)) {
                    $query->andWhere(['r.id_company' => $empresaId]);
                }
            } elseif (!$user->isSuperAdmin()) {
                $query->andWhere(['r.id_company' => $user->id_company]);
            }

            // Filtros
            $search = Yii::$app->request->get('search', '');
            if (!empty($search)) {
                $query->andWhere(['LIKE', 'r.report_name', $search]);
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
                        'date_report' => SORT_DESC,
                    ],
                ],
            ]);

            $reports = $dataProvider->getModels();
            $totalReports = $dataProvider->getTotalCount();

            return $this->render('trash', [
                'dataProvider'  => $dataProvider,
                'reports'       => $reports,
                'totalReports'  => $totalReports,
                'isAdmin'       => $user->isAdmin(),
                'isAgent'       => $user->isAgent(),
                'isSuperAdmin'  => $user->isSuperAdmin(),
                'search'        => $search,
            ]);

        } catch (\Exception $e) {
            Yii::error('Error en actionTrash: ' . $e->getMessage(), 'report-controller');
            Yii::$app->session->setFlash('error', 'Error al cargar la papelera: ' . $e->getMessage());
            return $this->redirect(['index']);
        }
    }

    // ============================================
    // VER EVALUACIÓN - MODAL (AJAX)
    // ============================================
    public function actionViewModal($id)
    {
        Yii::$app->response->format = Response::FORMAT_HTML;

        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            $model = Report::find()
                ->with(['status', 'user', 'company', 'lead'])
                ->where(['id_report' => $id])
                ->one();

            if (!$model) {
                return $this->renderPartial('_view_modal', [
                    'error' => 'La evaluación solicitada no existe.'
                ]);
            }

            if ($user->isSuperAdmin() && !empty($empresaId) && $model->id_company != $empresaId) {
                return $this->renderPartial('_view_modal', [
                    'error' => 'No tienes permiso para ver esta evaluación.'
                ]);
            }

            if (!$user->isSuperAdmin() && $model->id_company != $user->id_company) {
                return $this->renderPartial('_view_modal', [
                    'error' => 'No tienes permiso para ver esta evaluación.'
                ]);
            }

            return $this->renderPartial('_view_modal', [
                'model'        => $model,
                'isAdmin'      => $user->isAdmin(),
                'isAgent'      => $user->isAgent(),
                'isSuperAdmin' => $user->isSuperAdmin(),
            ]);

        } catch (\Exception $e) {
            Yii::error('Error en actionViewModal: ' . $e->getMessage(), 'report-controller');
            return $this->renderPartial('_view_modal', [
                'error' => 'Error al cargar la evaluación: ' . $e->getMessage()
            ]);
        }
    }

    // ============================================
    // ACTUALIZAR EVALUACIÓN - MODAL (AJAX)
    // ============================================
    public function actionUpdateModal($id)
    {
        Yii::$app->response->format = Response::FORMAT_HTML;

        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            $model = Report::find()
                ->with(['status', 'user', 'company', 'lead'])
                ->where(['id_report' => $id])
                ->one();

            if (!$model) {
                return $this->renderPartial('_update_modal', [
                    'error' => 'La evaluación solicitada no existe.'
                ]);
            }

            if ($user->isSuperAdmin() && !empty($empresaId) && $model->id_company != $empresaId) {
                return $this->renderPartial('_update_modal', [
                    'error' => 'No tienes permiso para editar esta evaluación.'
                ]);
            }

            if (!$user->isSuperAdmin() && $model->id_company != $user->id_company) {
                return $this->renderPartial('_update_modal', [
                    'error' => 'No tienes permiso para editar esta evaluación.'
                ]);
            }

            $statusOptions = Status::find()
                ->select(['status', 'id_status'])
                ->where(['IN', 'status', self::REPORT_STATUS_LIST])
                ->indexBy('id_status')
                ->column();

            // 🔥 Lista de leads
            $leadsQuery = Lead::find();
            if ($user->isSuperAdmin() && !empty($empresaId)) {
                $leadsQuery->andWhere(['id_company' => $empresaId]);
            } elseif (!$user->isSuperAdmin()) {
                $leadsQuery->andWhere(['id_company' => $user->id_company]);
            }
            $leadsList = ArrayHelper::map($leadsQuery->all(), 'id_lead', function($lead) {
                return $lead->name . ' ' . $lead->lastname . ' (' . $lead->phone . ')';
            });

            if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
                try {
                    if ($model->save()) {
                        return $this->renderPartial('_update_modal', [
                            'model'         => $model,
                            'statusOptions' => $statusOptions,
                            'leadsList'     => $leadsList,
                            'isAdmin'       => $user->isAdmin(),
                            'isSuperAdmin'  => $user->isSuperAdmin(),
                            'success'       => 'Evaluación actualizada exitosamente'
                        ]);
                    } else {
                        $errors = $model->getErrors();
                        $errorMessages = [];
                        foreach ($errors as $attribute => $errorList) {
                            $label = $model->getAttributeLabel($attribute);
                            $errorMessages[] = $label . ': ' . implode(', ', $errorList);
                        }
                        return $this->renderPartial('_update_modal', [
                            'model'         => $model,
                            'statusOptions' => $statusOptions,
                            'leadsList'     => $leadsList,
                            'isAdmin'       => $user->isAdmin(),
                            'isSuperAdmin'  => $user->isSuperAdmin(),
                            'error'         => 'Error al guardar:<br>' . implode('<br>', $errorMessages)
                        ]);
                    }
                } catch (\Exception $e) {
                    return $this->renderPartial('_update_modal', [
                        'model'         => $model,
                        'statusOptions' => $statusOptions,
                        'leadsList'     => $leadsList,
                        'isAdmin'       => $user->isAdmin(),
                        'isSuperAdmin'  => $user->isSuperAdmin(),
                        'error'         => 'Error: ' . $e->getMessage()
                    ]);
                }
            }

            return $this->renderPartial('_update_modal', [
                'model'         => $model,
                'statusOptions' => $statusOptions,
                'leadsList'     => $leadsList,
                'isAdmin'       => $user->isAdmin(),
                'isSuperAdmin'  => $user->isSuperAdmin(),
            ]);

        } catch (\Exception $e) {
            Yii::error('Error en actionUpdateModal: ' . $e->getMessage(), 'report-controller');
            return $this->renderPartial('_update_modal', [
                'error' => 'Error al cargar el formulario: ' . $e->getMessage()
            ]);
        }
    }

    // ============================================
    // CREAR EVALUACIÓN
    // ============================================
    public function actionCreate()
    {
        try {
            $model = new Report();
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            if ($user->isSuperAdmin() && empty($empresaId)) {
                Yii::$app->session->setFlash('warning', 'Por favor, selecciona una empresa para continuar.');
                return $this->redirect(['empresa/index']);
            }

            $model->date_report = date('Y-m-d');
            $model->id_user = $user->id_user;

            if ($user->isSuperAdmin()) {
                if (!empty($empresaId)) {
                    $model->id_company = $empresaId;
                }
            } else {
                $model->id_company = $user->id_company;
            }

            $statusOptions = Status::find()
                ->select(['status', 'id_status'])
                ->where(['IN', 'status', self::REPORT_STATUS_LIST])
                ->indexBy('id_status')
                ->column();

            // 🔥 Lista de leads
            $leadsQuery = Lead::find();
            if ($user->isSuperAdmin() && !empty($empresaId)) {
                $leadsQuery->andWhere(['id_company' => $empresaId]);
            } elseif (!$user->isSuperAdmin()) {
                $leadsQuery->andWhere(['id_company' => $user->id_company]);
            }
            $leadsList = ArrayHelper::map($leadsQuery->all(), 'id_lead', function($lead) {
                return $lead->name . ' ' . $lead->lastname . ' (' . $lead->phone . ')';
            });

            $companyList = [];
            if ($user->isSuperAdmin()) {
                $companyList = Company::find()
                    ->select(['name', 'id_company'])
                    ->indexBy('id_company')
                    ->column();
            }

            if ($model->load(Yii::$app->request->post())) {
                try {
                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', 'Evaluación creada exitosamente.');
                        return $this->redirect(['index']);
                    } else {
                        $errors = $model->getErrors();
                        $errorMessages = [];
                        foreach ($errors as $attribute => $errorList) {
                            $label = $model->getAttributeLabel($attribute);
                            $errorMessages[] = $label . ': ' . implode(', ', $errorList);
                        }
                        Yii::$app->session->setFlash('error', 'Error al guardar la evaluación:<br>' . implode('<br>', $errorMessages));
                    }
                } catch (\Exception $e) {
                    Yii::error('Error en actionCreate: ' . $e->getMessage(), 'report-controller');
                    Yii::$app->session->setFlash('error', 'Error al crear la evaluación: ' . $e->getMessage());
                }
            }

            return $this->render('create', [
                'model'         => $model,
                'statusOptions' => $statusOptions,
                'leadsList'     => $leadsList,
                'companyList'   => $companyList,
                'isAdmin'       => $user->isAdmin(),
                'isAgent'       => $user->isAgent(),
                'isSuperAdmin'  => $user->isSuperAdmin(),
            ]);

        } catch (\Exception $e) {
            Yii::error('Error en actionCreate: ' . $e->getMessage(), 'report-controller');
            Yii::$app->session->setFlash('error', 'Error al cargar el formulario: ' . $e->getMessage());
            return $this->redirect(['index']);
        }
    }

    // ============================================
    // ACTUALIZAR EVALUACIÓN (VISTA COMPLETA)
    // ============================================
    public function actionUpdate($id)
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            $model = Report::find()
                ->with(['status', 'user', 'company', 'lead'])
                ->where(['id_report' => $id])
                ->one();

            if (!$model) {
                throw new NotFoundHttpException('La evaluación solicitada no existe.');
            }

            if ($user->isSuperAdmin() && !empty($empresaId) && $model->id_company != $empresaId) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para editar esta evaluación.');
                return $this->redirect(['index']);
            }

            if (!$user->isSuperAdmin() && $model->id_company != $user->id_company) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para editar esta evaluación.');
                return $this->redirect(['index']);
            }

            $statusOptions = Status::find()
                ->select(['status', 'id_status'])
                ->where(['IN', 'status', self::REPORT_STATUS_LIST])
                ->indexBy('id_status')
                ->column();

            $leadsQuery = Lead::find();
            if ($user->isSuperAdmin() && !empty($empresaId)) {
                $leadsQuery->andWhere(['id_company' => $empresaId]);
            } elseif (!$user->isSuperAdmin()) {
                $leadsQuery->andWhere(['id_company' => $user->id_company]);
            }
            $leadsList = ArrayHelper::map($leadsQuery->all(), 'id_lead', function($lead) {
                return $lead->name . ' ' . $lead->lastname . ' (' . $lead->phone . ')';
            });

            if ($model->load(Yii::$app->request->post())) {
                try {
                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', 'Evaluación actualizada exitosamente.');
                        return $this->redirect(['index']);
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
                    Yii::error('Error en actionUpdate: ' . $e->getMessage(), 'report-controller');
                    Yii::$app->session->setFlash('error', 'Error al actualizar: ' . $e->getMessage());
                }
            }

            return $this->render('update', [
                'model'         => $model,
                'statusOptions' => $statusOptions,
                'leadsList'     => $leadsList,
                'isAdmin'       => $user->isAdmin(),
                'isAgent'       => $user->isAgent(),
                'isSuperAdmin'  => $user->isSuperAdmin(),
            ]);

        } catch (NotFoundHttpException $e) {
            Yii::$app->session->setFlash('error', 'Evaluación no encontrada.');
            return $this->redirect(['index']);
        } catch (\Exception $e) {
            Yii::error('Error en actionUpdate: ' . $e->getMessage(), 'report-controller');
            Yii::$app->session->setFlash('error', 'Error al actualizar la evaluación.');
            return $this->redirect(['index']);
        }
    }

    // ============================================
    // MOVER A PAPELERA (CAMBIO DE ESTADO A INACTIVO)
    // ============================================
    public function actionDelete($id)
    {
        try {
            $model = $this->findModel($id);

            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            if (!$user->isAdmin()) {
                Yii::$app->session->setFlash('error', 'No tienes permiso.');
                return $this->redirect(['index']);
            }

            if ($user->isSuperAdmin() && !empty($empresaId) && $model->id_company != $empresaId) {
                Yii::$app->session->setFlash('error', 'No tienes permiso.');
                return $this->redirect(['index']);
            }

            if (!$user->isSuperAdmin() && $model->id_company != $user->id_company) {
                Yii::$app->session->setFlash('error', 'No tienes permiso.');
                return $this->redirect(['index']);
            }

            // 🔥 Buscar estado "Inactivo" y asignarlo
            $statusInactivo = Status::find()->where(['status' => 'Inactivo'])->one();

            if (!$statusInactivo) {
                Yii::$app->session->setFlash('error', 'No se encontró el estado "Inactivo" en la base de datos.');
                return $this->redirect(['index']);
            }

            $model->id_status = $statusInactivo->id_status;

            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'Evaluación movida a la papelera.');
            } else {
                Yii::$app->session->setFlash('error', 'Error al actualizar el estado.');
            }

        } catch (NotFoundHttpException $e) {
            Yii::$app->session->setFlash('error', 'Evaluación no encontrada.');
        } catch (\Exception $e) {
            Yii::error('Error en actionDelete: ' . $e->getMessage(), 'report-controller');
            Yii::$app->session->setFlash('error', 'Error: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    // ============================================
    // RESTAURAR EVALUACIÓN DESDE PAPELERA
    // ============================================
    public function actionRestore($id)
    {
        try {
            $model = $this->findModel($id);

            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            if (!$user->isAdmin()) {
                Yii::$app->session->setFlash('error', 'No tienes permiso.');
                return $this->redirect(['trash']);
            }

            if ($user->isSuperAdmin() && !empty($empresaId) && $model->id_company != $empresaId) {
                Yii::$app->session->setFlash('error', 'No tienes permiso.');
                return $this->redirect(['trash']);
            }

            if (!$user->isSuperAdmin() && $model->id_company != $user->id_company) {
                Yii::$app->session->setFlash('error', 'No tienes permiso.');
                return $this->redirect(['trash']);
            }

            // 🔥 Restaurar al estado "Activo"
            $statusActivo = Status::find()->where(['status' => 'Activo'])->one();

            if (!$statusActivo) {
                Yii::$app->session->setFlash('error', 'No se encontró el estado "Activo".');
                return $this->redirect(['trash']);
            }

            $model->id_status = $statusActivo->id_status;

            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'Evaluación restaurada exitosamente.');
            } else {
                Yii::$app->session->setFlash('error', 'Error al restaurar la evaluación.');
            }

        } catch (NotFoundHttpException $e) {
            Yii::$app->session->setFlash('error', 'Evaluación no encontrada.');
        } catch (\Exception $e) {
            Yii::error('Error en actionRestore: ' . $e->getMessage(), 'report-controller');
            Yii::$app->session->setFlash('error', 'Error al restaurar la evaluación.');
        }

        return $this->redirect(['trash']);
    }

    // ============================================
    // ACTUALIZAR ESTADO (POST)
    // ============================================
    public function actionUpdateStatus()
    {
        $request = Yii::$app->request;
        $id = $request->post('id_report');
        $statusId = $request->post('id_status');

        try {
            $model = $this->findModel($id);
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            if ($user->isSuperAdmin() && !empty($empresaId) && $model->id_company != $empresaId) {
                Yii::$app->session->setFlash('error', 'No tienes permiso.');
                return $this->redirect(['index']);
            }
            if (!$user->isSuperAdmin() && $model->id_company != $user->id_company) {
                Yii::$app->session->setFlash('error', 'No tienes permiso.');
                return $this->redirect(['index']);
            }

            $statusModel = Status::findOne($statusId);
            if (!$statusModel) {
                Yii::$app->session->setFlash('error', 'Estado no válido.');
                return $this->redirect(['index']);
            }

            $oldStatus = $model->getStatusName();
            $model->id_status = $statusId;

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Estado actualizado de "' . $oldStatus . '" a "' . $statusModel->status . '"');
            } else {
                Yii::$app->session->setFlash('error', 'Error al actualizar el estado.');
            }

        } catch (NotFoundHttpException $e) {
            Yii::$app->session->setFlash('error', 'Evaluación no encontrada.');
        } catch (\Exception $e) {
            Yii::error('Error en actionUpdateStatus: ' . $e->getMessage(), 'report-controller');
            Yii::$app->session->setFlash('error', 'Error al actualizar el estado.');
        }

        return $this->redirect(['index']);
    }

    // ============================================
    // HELPERS
    // ============================================
    private function getTrashStatusIds()
    {
        $statusInactivo = Status::find()->where(['status' => 'Inactivo'])->one();
        $statusCancelado = Status::find()->where(['status' => 'Cancelado'])->one();

        $ids = [];
        if ($statusInactivo) $ids[] = $statusInactivo->id_status;
        if ($statusCancelado) $ids[] = $statusCancelado->id_status;

        return $ids;
    }

    // ============================================
    // FINDER
    // ============================================
    protected function findModel($id)
    {
        $model = Report::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('La evaluación solicitada no existe.');
        }
        return $model;
    }
}