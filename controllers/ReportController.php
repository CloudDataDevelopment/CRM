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
                    'delete'        => ['POST', 'GET'],
                    'restore'       => ['POST', 'GET'],
                    'update-status' => ['POST'],
                ],
            ],
        ];
    }

    // ============================================
    // 🔥 HELPER: Aplica filtro por rol y empresa
    // ============================================
    private function applyRoleFilter($query, $user, $empresaId)
    {
        if ($user->isSuperAdmin()) {
            // Super Admin: solo ve la empresa seleccionada en sesión
            if (!empty($empresaId)) {
                $query->andWhere(['r.id_company' => $empresaId]);
            } else {
                // Sin empresa seleccionada = sin resultados
                $query->andWhere(['0' => '1']);
            }
        } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
            // Admin: solo ve su empresa
            $query->andWhere(['r.id_company' => $user->id_company]);
        } elseif ($user->isAgent()) {
            // Agente: solo ve sus propios reports
            $query->andWhere(['r.id_user' => $user->id_user]);
        } else {
            // Sin rol válido = sin resultados
            $query->andWhere(['0' => '1']);
        }

        return $query;
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

            // 🔥 Validar Super Admin sin empresa
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

            // 🔥 APLICAR FILTRO POR ROL Y EMPRESA
            $this->applyRoleFilter($query, $user, $empresaId);

            // Filtros de búsqueda
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

            // 🔥 ESTADÍSTICAS (sobre toda la consulta filtrada, no solo la página)
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

            // 🔥 APLICAR FILTRO POR ROL Y EMPRESA
            $this->applyRoleFilter($query, $user, $empresaId);

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

            // 🔥 VALIDAR PERMISO CON HELPER
            if (!$this->canAccessReport($model, $user, $empresaId)) {
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

            // 🔥 VALIDAR PERMISO CON HELPER
            if (!$this->canAccessReport($model, $user, $empresaId)) {
                return $this->renderPartial('_update_modal', [
                    'error' => 'No tienes permiso para editar esta evaluación.'
                ]);
            }

            $statusOptions = Status::find()
                ->select(['status', 'id_status'])
                ->where(['IN', 'status', self::REPORT_STATUS_LIST])
                ->indexBy('id_status')
                ->column();

            // 🔥 Lista de leads filtrada por rol
            $leadsList = $this->getLeadsListForUser($user, $empresaId);

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

            // 🔥 ASIGNAR EMPRESA SEGÚN ROL
            if ($user->isSuperAdmin()) {
                if (!empty($empresaId)) {
                    $model->id_company = $empresaId;
                }
            } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
                $model->id_company = $user->id_company;
            } elseif ($user->isAgent()) {
                $model->id_company = $user->id_company;
            }

            $statusOptions = Status::find()
                ->select(['status', 'id_status'])
                ->where(['IN', 'status', self::REPORT_STATUS_LIST])
                ->indexBy('id_status')
                ->column();

            // 🔥 Lista de leads filtrada por rol
            $leadsList = $this->getLeadsListForUser($user, $empresaId);

            $companyList = [];
            if ($user->isSuperAdmin()) {
                $companyList = Company::find()
                    ->select(['name', 'id_company'])
                    ->indexBy('id_company')
                    ->column();
            }

            if ($model->load(Yii::$app->request->post())) {
                try {
                    // 🔥 FORZAR empresa según rol (evitar manipulación del POST)
                    if ($user->isSuperAdmin()) {
                        if (!empty($empresaId)) {
                            $model->id_company = $empresaId;
                        }
                    } elseif (!$user->isSuperAdmin()) {
                        $model->id_company = $user->id_company;
                    }

                    // 🔥 FORZAR id_user para agente
                    if ($user->isAgent()) {
                        $model->id_user = $user->id_user;
                    }

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

            // 🔥 VALIDAR PERMISO CON HELPER
            if (!$this->canAccessReport($model, $user, $empresaId)) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para editar esta evaluación.');
                return $this->redirect(['index']);
            }

            $statusOptions = Status::find()
                ->select(['status', 'id_status'])
                ->where(['IN', 'status', self::REPORT_STATUS_LIST])
                ->indexBy('id_status')
                ->column();

            $leadsList = $this->getLeadsListForUser($user, $empresaId);

            if ($model->load(Yii::$app->request->post())) {
                try {
                    // 🔥 FORZAR empresa según rol
                    if ($user->isSuperAdmin()) {
                        if (!empty($empresaId)) {
                            $model->id_company = $empresaId;
                        }
                    } elseif (!$user->isSuperAdmin()) {
                        $model->id_company = $user->id_company;
                    }

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
    // MOVER A PAPELERA
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

            // 🔥 VALIDAR PERMISO CON HELPER
            if (!$this->canAccessReport($model, $user, $empresaId)) {
                Yii::$app->session->setFlash('error', 'No tienes permiso.');
                return $this->redirect(['index']);
            }

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
    // RESTAURAR EVALUACIÓN
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

            // 🔥 VALIDAR PERMISO CON HELPER
            if (!$this->canAccessReport($model, $user, $empresaId)) {
                Yii::$app->session->setFlash('error', 'No tienes permiso.');
                return $this->redirect(['trash']);
            }

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

            // 🔥 VALIDAR PERMISO CON HELPER
            if (!$this->canAccessReport($model, $user, $empresaId)) {
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
    // 🔥 HELPER: Verificar si el usuario puede acceder al report
    // ============================================
    private function canAccessReport($model, $user, $empresaId)
    {
        if ($user->isSuperAdmin()) {
            if (!empty($empresaId) && $model->id_company != $empresaId) {
                return false;
            }
            return true;
        }

        if ($user->isAdmin() && !$user->isSuperAdmin()) {
            return $model->id_company == $user->id_company;
        }

        if ($user->isAgent()) {
            return $model->id_user == $user->id_user;
        }

        return false;
    }

    // ============================================
    // 🔥 HELPER: Obtener lista de leads filtrada por rol
    // ============================================
    private function getLeadsListForUser($user, $empresaId)
    {
        $leadsQuery = Lead::find();

        if ($user->isSuperAdmin()) {
            if (!empty($empresaId)) {
                $leadsQuery->andWhere(['id_company' => $empresaId]);
            }
        } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
            $leadsQuery->andWhere(['id_company' => $user->id_company]);
        } elseif ($user->isAgent()) {
            $leadsQuery->andWhere(['id_user' => $user->id_user]);
        }

        return ArrayHelper::map($leadsQuery->all(), 'id_lead', function($lead) {
            return $lead->name . ' ' . $lead->lastname . ' (' . $lead->phone . ')';
        });
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