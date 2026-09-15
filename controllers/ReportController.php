<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\Report;
use app\models\Status;
use app\models\Company;
use yii\web\Response;

class ReportController extends Controller
{
    public $layout = 'main';

    const REPORT_STATUS_LIST = ['activo', 'completado', 'pendiente', 'cancelado'];

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    // ============================================
    // LISTA DE REPORTES
    // ============================================
    public function actionIndex()
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            if (!$user) {
                return $this->redirect(['site/login']);
            }

            // 🔥 Verificar empresa seleccionada para Super Admin
            if ($user->isSuperAdmin() && empty($empresaId)) {
                Yii::$app->session->setFlash('warning', 'Por favor, selecciona una empresa para continuar.');
                return $this->redirect(['empresa/index']);
            }

            $sql = "SELECT r.* FROM Reports r WHERE 1=1";
            $params = [];

            $search = Yii::$app->request->get('search', '');
            $status = Yii::$app->request->get('status', '');
            $fecha_inicio = Yii::$app->request->get('fecha_inicio', '');
            $fecha_fin = Yii::$app->request->get('fecha_fin', '');

            // 🔥 FILTRO POR ROL Y EMPRESA
            if ($user->isSuperAdmin()) {
                if (!empty($empresaId)) {
                    $sql .= " AND r.id_company = :id_company";
                    $params[':id_company'] = $empresaId;
                }
            } elseif (!$user->isSuperAdmin()) {
                $sql .= " AND r.id_company = :id_company";
                $params[':id_company'] = $user->id_company;
            }

            if (!empty($search)) {
                $sql .= " AND r.report_name LIKE :search";
                $params[':search'] = '%' . $search . '%';
            }

            if (!empty($status)) {
                $statusModel = Status::find()->where(['status' => $status])->one();
                if ($statusModel) {
                    $sql .= " AND r.id_status = :id_status";
                    $params[':id_status'] = $statusModel->id_status;
                }
            }

            if (!empty($fecha_inicio)) {
                $sql .= " AND r.date_report >= :fecha_inicio";
                $params[':fecha_inicio'] = $fecha_inicio;
            }
            if (!empty($fecha_fin)) {
                $sql .= " AND r.date_report <= :fecha_fin";
                $params[':fecha_fin'] = $fecha_fin;
            }

            $sql .= " ORDER BY r.date_report DESC";

            $reports = Report::findBySql($sql, $params)->all();

            $totalReports = count($reports);
            $statusCounts = [];
            foreach ($reports as $r) {
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
                'reports' => $reports,
                'totalReports' => $totalReports,
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
            Yii::error('Error en actionIndex: ' . $e->getMessage(), 'report-controller');
            Yii::$app->session->setFlash('error', 'Error al cargar los reportes: ' . $e->getMessage());
            return $this->render('index', [
                'reports' => [],
                'totalReports' => 0,
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
    // VER REPORTE - MODAL (AJAX)
    // ============================================
    public function actionViewModal($id)
    {
        Yii::$app->response->format = Response::FORMAT_HTML;

        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            $model = Report::findOne($id);

            if (!$model) {
                return $this->renderPartial('_view_modal', [
                    'error' => 'El reporte solicitado no existe.'
                ]);
            }

            // 🔥 VERIFICAR PERMISOS
            if ($user->isSuperAdmin() && !empty($empresaId) && $model->id_company != $empresaId) {
                return $this->renderPartial('_view_modal', [
                    'error' => 'No tienes permiso para ver este reporte.'
                ]);
            }

            if (!$user->isSuperAdmin() && $model->id_company != $user->id_company) {
                return $this->renderPartial('_view_modal', [
                    'error' => 'No tienes permiso para ver este reporte.'
                ]);
            }

            return $this->renderPartial('_view_modal', [
                'model' => $model,
                'isAdmin' => $user->isAdmin(),
                'isAgent' => $user->isAgent(),
                'isSuperAdmin' => $user->isSuperAdmin(),
            ]);

        } catch (\Exception $e) {
            Yii::error('Error en actionViewModal: ' . $e->getMessage(), 'report-controller');
            return $this->renderPartial('_view_modal', [
                'error' => 'Error al cargar el reporte: ' . $e->getMessage()
            ]);
        }
    }

    // ============================================
    // ACTUALIZAR REPORTE - MODAL (AJAX)
    // ============================================
    public function actionUpdateModal($id)
    {
        Yii::$app->response->format = Response::FORMAT_HTML;

        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            $model = Report::findOne($id);

            if (!$model) {
                return $this->renderPartial('_update_modal', [
                    'error' => 'El reporte solicitado no existe.'
                ]);
            }

            // 🔥 VERIFICAR PERMISOS
            if ($user->isSuperAdmin() && !empty($empresaId) && $model->id_company != $empresaId) {
                return $this->renderPartial('_update_modal', [
                    'error' => 'No tienes permiso para editar este reporte.'
                ]);
            }

            if (!$user->isSuperAdmin() && $model->id_company != $user->id_company) {
                return $this->renderPartial('_update_modal', [
                    'error' => 'No tienes permiso para editar este reporte.'
                ]);
            }

            $statusOptions = Status::find()
                ->select(['status', 'id_status'])
                ->where(['IN', 'status', self::REPORT_STATUS_LIST])
                ->indexBy('id_status')
                ->column();

            if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
                try {
                    if ($model->save()) {
                        return $this->renderPartial('_update_modal', [
                            'model' => $model,
                            'statusOptions' => $statusOptions,
                            'isAdmin' => $user->isAdmin(),
                            'success' => 'Reporte actualizado exitosamente.'
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
                            'statusOptions' => $statusOptions,
                            'isAdmin' => $user->isAdmin(),
                            'error' => 'Error al guardar:<br>' . implode('<br>', $errorMessages)
                        ]);
                    }
                } catch (\Exception $e) {
                    Yii::error('Error en actionUpdateModal (POST): ' . $e->getMessage(), 'report-controller');
                    return $this->renderPartial('_update_modal', [
                        'model' => $model,
                        'statusOptions' => $statusOptions,
                        'isAdmin' => $user->isAdmin(),
                        'error' => 'Error al actualizar: ' . $e->getMessage()
                    ]);
                }
            }

            return $this->renderPartial('_update_modal', [
                'model' => $model,
                'statusOptions' => $statusOptions,
                'isAdmin' => $user->isAdmin(),
            ]);

        } catch (\Exception $e) {
            Yii::error('Error en actionUpdateModal: ' . $e->getMessage(), 'report-controller');
            return $this->renderPartial('_update_modal', [
                'error' => 'Error al cargar el formulario: ' . $e->getMessage()
            ]);
        }
    }

    // ============================================
    // CREAR REPORTE
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
                        Yii::$app->session->setFlash('success', 'Reporte creado exitosamente.');
                        return $this->redirect(['index']);
                    } else {
                        $errors = $model->getErrors();
                        $errorMessages = [];
                        foreach ($errors as $attribute => $errorList) {
                            $label = $model->getAttributeLabel($attribute);
                            $errorMessages[] = $label . ': ' . implode(', ', $errorList);
                        }
                        Yii::$app->session->setFlash('error', 'Error al guardar el reporte:<br>' . implode('<br>', $errorMessages));
                    }
                } catch (\Exception $e) {
                    Yii::error('Error en actionCreate: ' . $e->getMessage(), 'report-controller');
                    Yii::$app->session->setFlash('error', 'Error al crear el reporte: ' . $e->getMessage());
                }
            }

            return $this->render('create', [
                'model' => $model,
                'statusOptions' => $statusOptions,
                'companyList' => $companyList,
                'isAdmin' => $user->isAdmin(),
                'isAgent' => $user->isAgent(),
                'isSuperAdmin' => $user->isSuperAdmin(),
            ]);

        } catch (\Exception $e) {
            Yii::error('Error en actionCreate: ' . $e->getMessage(), 'report-controller');
            Yii::$app->session->setFlash('error', 'Error al cargar el formulario: ' . $e->getMessage());
            return $this->redirect(['index']);
        }
    }

    // ============================================
    // ELIMINAR REPORTE
    // ============================================
    public function actionDelete($id)
    {
        try {
            $model = Report::findOne($id);

            if (!$model) {
                throw new NotFoundHttpException('El reporte solicitado no existe.');
            }

            $user = Yii::$app->user->identity;

            if (!$user->isAdmin()) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para eliminar reportes.');
                return $this->redirect(['index']);
            }

            if ($model->delete()) {
                Yii::$app->session->setFlash('success', 'Reporte eliminado exitosamente.');
            } else {
                Yii::$app->session->setFlash('error', 'Error al eliminar el reporte.');
            }

        } catch (\Exception $e) {
            Yii::error('Error en actionDelete: ' . $e->getMessage(), 'report-controller');
            Yii::$app->session->setFlash('error', 'Error al eliminar el reporte: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }
}