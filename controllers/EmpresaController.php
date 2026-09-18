<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use app\models\Company;
use app\models\Authentication;
use app\models\Status;
use app\components\ErrorManager;

class EmpresaController extends Controller
{
    // ============================================
    // LISTA DE EMPRESAS
    // ============================================
    public function actionIndex()
    {
        try {
            $auth = Authentication::find()
                ->where(['id_user' => Yii::$app->user->id])
                ->one();

            if (!$auth || $auth->id_role != 1) {
                $empresaId = Yii::$app->user->identity->id_company ?? 1;
                Yii::$app->session->set('empresa_id', $empresaId);
                return $this->redirect(['dashboard', 'id' => $empresaId]);
            }

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

    // ============================================
    // DASHBOARD DE EMPRESA
    // ============================================
    public function actionDashboard($id)
    {
        try {
            Yii::$app->session->set('empresa_id', $id);
            
            $empresa = Company::findOne($id);
            if ($empresa) {
                Yii::$app->session->set('empresa_nombre', $empresa->name);
            }
            
            return $this->redirect(['dashboard/index']);
            
        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al cargar el dashboard');
            return $this->redirect(['empresa/index']);
        }
    }

    // ============================================
    // CREAR EMPRESA
    // ============================================
    public function actionCreate()
    {
        $this->layout = 'main-simple';
        $model = new Company();

        if ($model->load(Yii::$app->request->post())) {
            try {
                // Cargar el archivo del logo
                $model->logoFile = UploadedFile::getInstance($model, 'logoFile');

                // Obtener el último ID y sumar 1
                $lastId = Company::find()
                    ->select(['id_company'])
                    ->orderBy(['id_company' => SORT_DESC])
                    ->scalar();
                
                $model->id_company = ($lastId ? $lastId : 0) + 1;

                // Asignar id_status por defecto si no viene
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

        $statusList = Status::find()
            ->select(['status', 'id_status'])
            ->indexBy('id_status')
            ->column();

        return $this->render('create', [
            'model' => $model,
            'statusList' => $statusList,
        ]);
    }

    // ============================================
    // ACTUALIZAR EMPRESA
    // ============================================
    public function actionUpdate($id)
    {
        $this->layout = 'main-simple';

        try {
            $model = Company::findOne($id);

            if (!$model) {
                throw new NotFoundHttpException('Empresa no encontrada');
            }

            if ($model->load(Yii::$app->request->post())) {
                // 🔥 Cargar el archivo del logo
                $model->logoFile = UploadedFile::getInstance($model, 'logoFile');

                if (empty($model->id_status)) {
                    $statusActivo = Status::find()->where(['status' => 'Activo'])->one();
                    $model->id_status = $statusActivo ? $statusActivo->id_status : null;
                }

                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Empresa actualizada exitosamente');
                    return $this->redirect(['index']);
                } else {
                    $errors = json_encode($model->getErrors());
                    Yii::$app->session->setFlash('error', 'Error de validación: ' . $errors);
                }
            }

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
            Yii::$app->session->setFlash('error', 'Error de base de datos al actualizar la empresa.');
            Yii::error($e->getMessage(), 'empresa\update');
        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al actualizar la empresa');
            return $this->redirect(['index']);
        }
    }

    // ============================================
    // VER EMPRESA
    // ============================================
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

    // ============================================
    // ELIMINAR EMPRESA
    // ============================================
    public function actionDelete($id)
    {
        try {
            $model = Company::findOne($id);

            if (!$model) {
                throw new NotFoundHttpException('Empresa no encontrada');
            }

            // Verificar que no tenga usuarios asociados
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
            Yii::$app->session->setFlash('error', 'Error de base de datos al eliminar la empresa.');
            Yii::error($e->getMessage(), 'empresa\delete');
        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al eliminar la empresa');
        }

        return $this->redirect(['index']);
    }

    // ============================================
    // 🔥 ELIMINAR LOGO DE EMPRESA
    // ============================================
    public function actionDeleteLogo($id)
    {
        try {
            $model = Company::findOne($id);

            if (!$model) {
                throw new NotFoundHttpException('Empresa no encontrada');
            }

            $model->deleteLogo();

            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'Logotipo eliminado exitosamente');
            } else {
                Yii::$app->session->setFlash('error', 'Error al eliminar el logotipo');
            }

        } catch (NotFoundHttpException $e) {
            Yii::$app->session->setFlash('error', 'Empresa no encontrada');
        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al eliminar el logo');
        }

        return $this->redirect(['update', 'id' => $id]);
    }

    // ============================================
    // CAMBIAR EMPRESA
    // ============================================
    public function actionCambiar($id)
    {
        try {
            $auth = Authentication::find()
                ->where(['id_user' => Yii::$app->user->id])
                ->one();

            if (!$auth || $auth->id_role != 1) {
                Yii::$app->session->setFlash('error', 'No tienes permisos para cambiar de empresa');
                return $this->redirect(['dashboard/index']);
            }

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

    // ============================================
    // API: OBTENER EMPRESAS
    // ============================================
    public function actionGetCompanies()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $companies = Company::find()
            ->select(['id_company', 'name', 'logo'])
            ->orderBy(['name' => SORT_ASC])
            ->all();
        
        return $companies;
    }

    // ============================================
    // API: OBTENER EMPRESAS ACTIVAS
    // ============================================
    public function actionGetActiveCompanies()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $statusActivo = Status::find()->where(['status' => 'Activo'])->one();
        if (!$statusActivo) {
            return [];
        }
        
        $companies = Company::find()
            ->select(['id_company', 'name', 'logo'])
            ->where(['id_status' => $statusActivo->id_status])
            ->orderBy(['name' => SORT_ASC])
            ->all();
        
        return $companies;
    }
}