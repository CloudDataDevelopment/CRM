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
use app\models\Lead;
use app\models\SalesTracking;
use app\models\Quote;
use app\models\Task;
use app\models\Status;
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
                        'actions' => [
                            'logout', 'index', 'register', 'ver-task', 'keep-alive', 'check-session',
                            'profile',
                            'update-profile',
                            'change-password',
                        ],
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
                    'update-profile' => ['post'],
                    'change-password' => ['post'],
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
        Yii::$app->session->remove('last_activity');
        Yii::$app->session->remove('empresa_id');
        Yii::$app->session->remove('empresa_nombre');
        Yii::$app->session->remove('tab_id');

        Yii::$app->session->destroy();
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

    // ============================================
    // 🔥 PERFIL DE USUARIO
    // ============================================
    public function actionProfile()
    {
        try {
            $user = Yii::$app->user->identity;

            if (!$user) {
                return $this->redirect(['site/login']);
            }

            $auth = Authentication::find()
                ->where(['id_user' => $user->id_user])
                ->with(['role', 'status', 'company'])
                ->one();

            $stats = [
                'leads_asignados'   => 0,
                'seguimientos'      => 0,
                'cotizaciones'      => 0,
                'tareas_pendientes' => 0,
            ];

            try {
                $stats['leads_asignados'] = Lead::find()
                    ->where(['id_user' => $user->id_user])
                    ->count();

                $stats['seguimientos'] = SalesTracking::find()
                    ->where(['id_user' => $user->id_user])
                    ->count();

                $stats['cotizaciones'] = Quote::find()
                    ->alias('q')
                    ->leftJoin('Lead l', 'q.id_lead = l.id_lead')
                    ->where(['l.id_user' => $user->id_user])
                    ->count();

                $statusPendiente = Status::find()->where(['status' => 'Pendiente'])->one();
                if ($statusPendiente) {
                    $stats['tareas_pendientes'] = Task::find()
                        ->where(['id_status' => $statusPendiente->id_status])
                        ->count();
                }
            } catch (\Exception $e) {
                Yii::warning('Error al cargar estadísticas de perfil: ' . $e->getMessage());
            }

            return $this->render('profile', [
                'user'         => $user,
                'auth'         => $auth,
                'stats'        => $stats,
                'isAdmin'      => $user->isAdmin(),
                'isSuperAdmin' => $user->isSuperAdmin(),
            ]);

        } catch (\Exception $e) {
            Yii::error('Error en actionProfile: ' . $e->getMessage(), 'profile');
            Yii::$app->session->setFlash('error', 'Error al cargar el perfil.');
            return $this->redirect(['dashboard/index']);
        }
    }

    // ============================================
    // 🔥 ACTUALIZAR PERFIL (AJAX) — incluye username
    // ============================================
    public function actionUpdateProfile()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {
            $user = Yii::$app->user->identity;

            if (!$user) {
                return ['success' => false, 'message' => 'Sesión expirada.'];
            }

            if (Yii::$app->request->isPost) {
                $post = Yii::$app->request->post();

                // 🔥 Datos básicos
                $user->name      = trim($post['name'] ?? $user->name);
                $user->lastname1 = trim($post['lastname1'] ?? $user->lastname1);
                $user->lastname2 = trim($post['lastname2'] ?? $user->lastname2);
                $user->email     = trim($post['email'] ?? $user->email);
                $user->phone     = trim($post['phone'] ?? $user->phone);

                // 🔥 USERNAME (nuevo)
                if (isset($post['username']) && trim($post['username']) !== '') {
                    $newUsername = trim($post['username']);

                    // Validar formato: letras, números, punto, guion y guion bajo
                    if (!preg_match('/^[a-zA-Z0-9._-]{3,50}$/', $newUsername)) {
                        return [
                            'success' => false,
                            'message' => 'El nombre de usuario solo puede contener letras, números, puntos, guiones y guiones bajos (mínimo 3 caracteres).'
                        ];
                    }

                    // Verificar que no exista otro usuario con el mismo username
                    $existingUser = \app\models\User::find()
                        ->where(['username' => $newUsername])
                        ->andWhere(['<>', 'id_user', $user->id_user])
                        ->one();

                    if ($existingUser) {
                        return [
                            'success' => false,
                            'message' => 'El nombre de usuario "' . $newUsername . '" ya está en uso. Elige otro.'
                        ];
                    }

                    $user->username = $newUsername;
                }

                if ($user->save()) {
                    return [
                        'success' => true,
                        'message' => 'Perfil actualizado exitosamente.',
                    ];
                } else {
                    $errors = [];
                    foreach ($user->getErrors() as $attribute => $errorList) {
                        $errors[] = $user->getAttributeLabel($attribute) . ': ' . implode(', ', $errorList);
                    }
                    return [
                        'success' => false,
                        'message' => 'Error al guardar: ' . implode(' | ', $errors)
                    ];
                }
            }

            return ['success' => false, 'message' => 'Método no permitido.'];

        } catch (\Exception $e) {
            Yii::error('Error en actionUpdateProfile: ' . $e->getMessage(), 'profile');
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // ============================================
    // 🔥 CAMBIAR CONTRASEÑA (AJAX)
    // ============================================
    public function actionChangePassword()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {
            $user = Yii::$app->user->identity;

            if (!$user) {
                return ['success' => false, 'message' => 'Sesión expirada.'];
            }

            if (Yii::$app->request->isPost) {
                $post = Yii::$app->request->post();
                $currentPassword = $post['current_password'] ?? '';
                $newPassword     = $post['new_password'] ?? '';
                $confirmPassword = $post['confirm_password'] ?? '';

                if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                    return ['success' => false, 'message' => 'Todos los campos son obligatorios.'];
                }

                if ($newPassword !== $confirmPassword) {
                    return ['success' => false, 'message' => 'Las contraseñas nuevas no coinciden.'];
                }

                if (strlen($newPassword) < 4) {
                    return ['success' => false, 'message' => 'La nueva contraseña debe tener al menos 4 caracteres.'];
                }

                if (!$user->validatePassword($currentPassword)) {
                    return ['success' => false, 'message' => 'La contraseña actual es incorrecta.'];
                }

                $user->setPassword($newPassword);

                if ($user->save()) {
                    try {
                        $auth = Authentication::find()
                            ->where(['id_user' => $user->id_user])
                            ->one();
                        if ($auth) {
                            $auth->password = $user->password;
                            $auth->save(false);
                        }
                    } catch (\Exception $e) {
                        Yii::warning('Error al actualizar password en Authentication: ' . $e->getMessage());
                    }

                    return [
                        'success' => true,
                        'message' => 'Contraseña actualizada exitosamente.'
                    ];
                } else {
                    return ['success' => false, 'message' => 'Error al guardar la nueva contraseña.'];
                }
            }

            return ['success' => false, 'message' => 'Método no permitido.'];

        } catch (\Exception $e) {
            Yii::error('Error en actionChangePassword: ' . $e->getMessage(), 'profile');
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}