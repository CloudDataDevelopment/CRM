<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\models\LoginForm;
use app\models\RegisterForm;
use app\models\Authentication;
use app\components\ErrorManager;

class SiteController extends Controller
{
    public $layout = 'main';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'register', 'ver-task', 'keep-alive', 'check-session'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    Yii::$app->session->setFlash('error', 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
                    return Yii::$app->getResponse()->redirect(['site/login']);
                },
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                    'check-session' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        try {
            if (!Yii::$app->user->isGuest) {
                $auth = Authentication::find()
                    ->where(['id_user' => Yii::$app->user->id])
                    ->one();

                if ($auth && $auth->id_role == 1) {
                    return $this->redirect(['empresa/index']);
                }
                return $this->redirect(['dashboard/index']);
            }
            
            return $this->redirect(['site/login']);
        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al redirigir');
            return $this->render('login', ['model' => new LoginForm()]);
        }
    }

    public function actionLogin()
    {
        // Si ya está logueado, redirigir
        if (!Yii::$app->user->isGuest) {
            $auth = Authentication::find()
                ->where(['id_user' => Yii::$app->user->id])
                ->one();

            if ($auth && $auth->id_role == 1) {
                return $this->redirect(['empresa/index']);
            }
            
            $empresaId = Yii::$app->user->identity->id_company ?? 1;
            Yii::$app->session->set('empresa_id', $empresaId);
            Yii::$app->session->set('last_activity', time());
            Yii::$app->session->set('session_id', session_id());
            return $this->redirect(['dashboard/index']);
        }

        $this->layout = 'main-login';

        try {
            $model = new LoginForm();
            if ($model->load(Yii::$app->request->post()) && $model->login()) {
                Yii::$app->session->set('last_activity', time());
                Yii::$app->session->set('session_id', session_id());
                Yii::$app->session->set('tab_id', uniqid('tab_'));
                
                $auth = Authentication::find()
                    ->where(['id_user' => Yii::$app->user->id])
                    ->one();

                if ($auth && $auth->id_role == 1) {
                    return $this->redirect(['empresa/index']);
                } else {
                    $empresaId = Yii::$app->user->identity->id_company ?? 1;
                    Yii::$app->session->set('empresa_id', $empresaId);
                    return $this->redirect(['dashboard/index']);
                }
            }

            $model->password = '';
            return $this->render('login', [
                'model' => $model,
            ]);

        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al iniciar sesión');
            return $this->render('login', ['model' => new LoginForm()]);
        }
    }

    /**
     * Cerrar sesión
     */
    public function actionLogout()
    {
        // Limpiar variables de sesión
        Yii::$app->session->remove('last_activity');
        Yii::$app->session->remove('empresa_id');
        Yii::$app->session->remove('empresa_nombre');
        Yii::$app->session->remove('tab_id');
        
        // Destruir toda la sesión
        Yii::$app->session->destroy();
        
        // Cerrar sesión
        Yii::$app->user->logout(false);
        
        return $this->redirect(['site/login']);
    }

    /**
     * Verificar estado de la sesión (para cierre de pestaña)
     */
    public function actionCheckSession()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        if (Yii::$app->user->isGuest) {
            return [
                'success' => false,
                'message' => 'Sesión no activa',
                'redirect' => Yii::$app->urlManager->createUrl(['site/login'])
            ];
        }
        
        // Verificar si la sesión sigue activa
        $tabId = Yii::$app->session->get('tab_id');
        if (!$tabId) {
            Yii::$app->user->logout(false);
            return [
                'success' => false,
                'message' => 'Sesión inválida',
                'redirect' => Yii::$app->urlManager->createUrl(['site/login'])
            ];
        }
        
        return ['success' => true];
    }

    public function actionRegister()
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('error', 'Debes iniciar sesión para acceder a esta página.');
            return $this->redirect(['site/login']);
        }

        $user = Yii::$app->user->identity;
        $isSuperAdmin = false;
        $isAdmin = false;
        
        if ($user) {
            $isSuperAdmin = $user->isSuperAdmin();
            $isAdmin = $user->isAdmin() && !$isSuperAdmin;
        }

        $referrer = Yii::$app->request->referrer;
        $isFromEmpresa = false;
        $isFromCrm = false;
        $isFromMenu = false;
        
        if ($referrer) {
            $isFromEmpresa = strpos($referrer, 'empresa') !== false;
            $isFromCrm = strpos($referrer, 'dashboard') !== false || 
                         strpos($referrer, 'lead') !== false || 
                         strpos($referrer, 'user-management') !== false ||
                         strpos($referrer, 'task') !== false ||
                         strpos($referrer, 'calendar') !== false ||
                         strpos($referrer, 'report') !== false;
            
            $isFromMenu = strpos($referrer, 'site/register') !== false;
        }

        $useSimpleLayout = false;
        $hideSidebar = false;
        
        if ($isSuperAdmin && $isFromEmpresa) {
            $useSimpleLayout = true;
            $hideSidebar = true;
            $layout = 'main-simple';
            
            $rolesList = RegisterForm::getFullRolesList();
            $headerColor = 'bg-danger';
            $backUrl = ['/empresa/index'];
            $backLabel = 'Volver a Empresas';
            $showCompanyField = true;
            
        } elseif ($isSuperAdmin && ($isFromCrm || $isFromMenu)) {
            $useSimpleLayout = false;
            $hideSidebar = false;
            $layout = 'main';
            
            $rolesList = RegisterForm::getFullRolesList();
            $headerColor = 'bg-danger';
            $backUrl = ['/dashboard/index'];
            $backLabel = 'Volver al Dashboard';
            $showCompanyField = true;
            
        } elseif ($isAdmin && $isFromCrm) {
            $useSimpleLayout = false;
            $hideSidebar = false;
            $layout = 'main';
            
            $rolesList = RegisterForm::getLimitedRolesList();
            $headerColor = 'bg-primary';
            $backUrl = ['/user-management/index'];
            $backLabel = 'Volver a Usuarios';
            $showCompanyField = false;
            
        } else {
            $useSimpleLayout = false;
            $hideSidebar = false;
            $layout = 'main';
            
            $rolesList = RegisterForm::getLimitedRolesList();
            $headerColor = 'bg-secondary';
            $backUrl = ['/dashboard/index'];
            $backLabel = 'Volver al Dashboard';
            $showCompanyField = false;
        }

        $this->layout = $layout;
        $this->view->params['hideSidebar'] = $hideSidebar;

        $model = new RegisterForm();
        
        if (!$isSuperAdmin && $isAdmin) {
            $model->id_role = 2;
        }
        
        $companiesList = RegisterForm::getCompaniesList();

        if ($model->load(Yii::$app->request->post())) {
            if ($isSuperAdmin && empty($model->id_company)) {
                $model->addError('id_company', 'Debes seleccionar una empresa');
            }
            
            if ($model->register()) {
                Yii::$app->session->setFlash('success', 'Usuario registrado exitosamente.');
                return $this->redirect($backUrl);
            } else {
                if ($model->hasErrors()) {
                    $errors = $model->getErrors();
                    foreach ($errors as $field => $errorMessages) {
                        Yii::$app->session->setFlash('error', 'Error en ' . $model->getAttributeLabel($field) . ': ' . implode(', ', $errorMessages));
                        break;
                    }
                }
            }
        }

        return $this->render('register', [
            'model' => $model,
            'isSuperAdmin' => $isSuperAdmin,
            'isAdmin' => $isAdmin,
            'roleName' => $isSuperAdmin ? 'Super Administrador' : ($isAdmin ? 'Administrador' : 'Usuario'),
            'rolesList' => $rolesList,
            'headerColor' => $headerColor,
            'backUrl' => $backUrl,
            'backLabel' => $backLabel,
            'hideSidebar' => $hideSidebar,
            'useSimpleLayout' => $useSimpleLayout,
            'isFromEmpresa' => $isFromEmpresa,
            'isFromCrm' => $isFromCrm,
            'companiesList' => $companiesList,
            'showCompanyField' => $showCompanyField,
        ]);
    }

    public function actionVerTask()
    {
        try {
            $sql = "SHOW COLUMNS FROM Task";
            $columns = Yii::$app->db->createCommand($sql)->queryAll();

            echo "<pre>";
            echo "=== COLUMNAS DE TASK ===\n";
            foreach ($columns as $col) {
                echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
            }

            echo "\n=== DATOS DE TASK ===\n";
            $sql2 = "SELECT * FROM Task LIMIT 3";
            $data = Yii::$app->db->createCommand($sql2)->queryAll();
            print_r($data);
            echo "</pre>";

            exit;

        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al obtener información de Task');
            echo "Error al obtener información";
            exit;
        }
    }

    /**
     * Mantener sesión activa (AJAX)
     */
    public function actionKeepAlive()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        if (Yii::$app->user->isGuest) {
            return [
                'success' => false, 
                'message' => 'Sesión no activa', 
                'redirect' => Yii::$app->urlManager->createUrl(['site/login'])
            ];
        }
        
        Yii::$app->session->set('last_activity', time());
        
        return ['success' => true, 'message' => 'Sesión mantenida'];
    }
}