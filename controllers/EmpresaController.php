<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\Company;
use app\models\Authentication;
use app\models\Status;
use app\components\ErrorManager;
use app\components\CompanyHelper;

class EmpresaController extends Controller
{
    // Lista de empresas - layout sin menú
    public function actionIndex()
    {
        try {
            // Verificar si es admin
            $auth = Authentication::find()
                ->where(['id_user' => Yii::$app->user->id])
                ->one();

            if (!$auth || $auth->id_role != 1) {
                // No es admin, ir a su empresa
                $empresaId = Yii::$app->user->identity->id_company ?? 1;
                Yii::$app->session->set('empresa_id', $empresaId);
                return $this->redirect(['dashboard', 'id' => $empresaId]);
            }

            // Es admin, mostrar todas las empresas
            $this->layout = 'main-simple';
            $empresas = Company::find()
                ->with('status')
                ->all();

            return $this->render('index', [
                'empresas' => $empresas,
            ]);

        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al cargar la lista de empresas');
            return $this->render('index', ['empresas' => []]);
        }
    }

    // Dashboard de una empresa - guardar empresa en sesión
    public function actionDashboard($id)
    {
        try {
            // Guardar empresa seleccionada en sesión
            Yii::$app->session->set('empresa_id', $id);
            
            $empresa = Company::findOne($id);
            if ($empresa) {
                Yii::$app->session->set('empresa_nombre', $empresa->name);
            }
            
            // Redirigir al dashboard principal
            return $this->redirect(['dashboard/index']);
            
        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al cargar el dashboard');
            return $this->redirect(['empresa/index']);
        }
    }

    // Crear nueva empresa
    public function actionCreate()
    {
        $this->layout = 'main-simple';
        $model = new Company();

        if ($model->load(Yii::$app->request->post())) {
            try {
                // Obtener el último ID y sumar 1
                $lastId = Company::find()
                    ->select(['id_company'])
                    ->orderBy(['id_company' => SORT_DESC])
                    ->scalar();
                
                $model->id_company = ($lastId ? $lastId : 0) + 1;

                // 🔥 Asignar id_status por defecto si no viene
                if (empty($model->id_status)) {
                    $statusActivo = Status::find()->where(['status' => 'Activo'])->one();
                    $model->id_status = $statusActivo ? $statusActivo->id_status : null;
                }

                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Empresa creada exitosamente');
                    return $this->redirect(['index']);
                } else {
                    $errors = json_encode($model->getErrors());
                    throw new \yii\base\Exception('Error de validación: ' . $errors);
                }

            } catch (\yii\db\Exception $e) {
                Yii::$app->session->setFlash('error', 'Error de base de datos al crear la empresa.<br>Contacta con el administrador');
                Yii::error($e->getMessage(), 'empresa\create');
            } catch (\Exception $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        // 🔥 Obtener lista de estados para el dropdown
        $statusList = Status::find()
            ->select(['status', 'id_status'])
            ->indexBy('id_status')
            ->column();

        return $this->render('create', [
            'model' => $model,
            'statusList' => $statusList,
        ]);
    }

    // Editar empresa
    public function actionUpdate($id)
    {
        $this->layout = 'main-simple';

        try {
            $model = Company::findOne($id);

            if (!$model) {
                throw new NotFoundHttpException('Empresa no encontrada');
            }

            if ($model->load(Yii::$app->request->post())) {
                // 🔥 Asegurar que id_status tenga valor
                if (empty($model->id_status)) {
                    $statusActivo = Status::find()->where(['status' => 'Activo'])->one();
                    $model->id_status = $statusActivo ? $statusActivo->id_status : null;
                }

                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Empresa actualizada exitosamente');
                    return $this->redirect(['index']);
                }
            }

            // 🔥 Obtener lista de estados para el dropdown
            $statusList = Status::find()
                ->select(['status', 'id_status'])
                ->indexBy('id_status')
                ->column();

            return $this->render('update', [
                'model' => $model,
                'statusList' => $statusList,
            ]);

        } catch (NotFoundHttpException $e) {
            Yii::$app->session->setFlash('info', 'Empresa no encontrada');
            return $this->redirect(['index']);
        } catch (\yii\db\Exception $e) {
            Yii::$app->session->setFlash('error', 'Error de base de datos al actualizar la empresa.<br>Contacta con el administrador');
            Yii::error($e->getMessage(), 'empresa\update');
        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al actualizar la empresa');
            return $this->redirect(['index']);
        }
    }

    // Ver empresa
    public function actionView($id)
    {
        $this->layout = 'main-simple';

        try {
            $model = Company::find()
                ->with('status')
                ->where(['id_company' => $id])
                ->one();

            if (!$model) {
                throw new NotFoundHttpException('Empresa no encontrada');
            }

            return $this->render('view', [
                'model' => $model,
            ]);

        } catch (NotFoundHttpException $e) {
            Yii::$app->session->setFlash('info', 'Empresa no encontrada');
            return $this->redirect(['index']);
        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al ver la empresa');
            return $this->redirect(['index']);
        }
    }

    // Eliminar empresa
    public function actionDelete($id)
    {
        try {
            $model = Company::findOne($id);

            if (!$model) {
                throw new NotFoundHttpException('Empresa no encontrada');
            }

            // 🔥 Verificar que no tenga usuarios asociados antes de eliminar
            $usersCount = $model->getUsers()->count();
            if ($usersCount > 0) {
                Yii::$app->session->setFlash('error', 'No se puede eliminar la empresa porque tiene usuarios asociados.');
                return $this->redirect(['index']);
            }

            $model->delete();
            Yii::$app->session->setFlash('success', 'Empresa eliminada exitosamente');

        } catch (NotFoundHttpException $e) {
            Yii::$app->session->setFlash('info', 'Empresa no encontrada');
        } catch (\yii\db\Exception $e) {
            Yii::$app->session->setFlash('error', 'Error de base de datos al eliminar la empresa.<br>Contacta con el administrador');
            Yii::error($e->getMessage(), 'empresa\delete');
        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al eliminar la empresa');
        }

        return $this->redirect(['index']);
    }

    // Cambiar empresa (para admin)
    public function actionCambiar($id)
    {
        try {
            // Verificar si es admin
            $auth = Authentication::find()
                ->where(['id_user' => Yii::$app->user->id])
                ->one();

            if (!$auth || $auth->id_role != 1) {
                Yii::$app->session->setFlash('error', 'No tienes permisos para cambiar de empresa');
                return $this->redirect(['dashboard/index']);
            }

            // Guardar nueva empresa en sesión
            Yii::$app->session->set('empresa_id', $id);
            
            $empresa = Company::findOne($id);
            if ($empresa) {
                Yii::$app->session->set('empresa_nombre', $empresa->name);
            }

            Yii::$app->session->setFlash('success', 'Cambiaste a la empresa: ' . ($empresa ? $empresa->name : 'N/A'));
            return $this->redirect(['dashboard/index']);

        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al cambiar de empresa');
            return $this->redirect(['dashboard/index']);
        }
    }

    // 🔥 Obtener empresas para dropdown (API para selects)
    public function actionGetCompanies()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $companies = Company::find()
            ->select(['id_company', 'name'])
            ->orderBy(['name' => SORT_ASC])
            ->all();
        
        return $companies;
    }

    // 🔥 Obtener empresas activas (API)
    public function actionGetActiveCompanies()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $statusActivo = Status::find()->where(['status' => 'Activo'])->one();
        if (!$statusActivo) {
            return [];
        }
        
        $companies = Company::find()
            ->select(['id_company', 'name'])
            ->where(['id_status' => $statusActivo->id_status])
            ->orderBy(['name' => SORT_ASC])
            ->all();
        
        return $companies;
    }
}