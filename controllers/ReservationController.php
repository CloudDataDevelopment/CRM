<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\Reservation;
use app\models\Lead;
use app\models\User;
use yii\helpers\ArrayHelper;

class ReservationController extends Controller
{
    public $layout = 'main';

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

    /**
     * Obtener información del rol del usuario
     */
    private function getUserRole()
    {
        $user = Yii::$app->user->identity;
        if (!$user) {
            return ['isAdmin' => false, 'isAgent' => false, 'isSuperAdmin' => false, 'userId' => null, 'user' => null];
        }

        $auth = \app\models\Authentication::find()
            ->where(['id_user' => $user->id_user])
            ->one();

        $isAdmin = false;
        $isAgent = false;
        $isSuperAdmin = false;
        $userId = $user->id_user;

        if ($auth) {
            $role = \app\models\Role::find()
                ->where(['id_role' => $auth->id_role])
                ->one();
            
            if ($role) {
                $roleName = strtolower(trim($role->role_type ?? ''));
                $isSuperAdmin = ($roleName === 'super_admin');
                $isAdmin = ($roleName === 'admin' || $roleName === 'super_admin');
                $isAgent = ($roleName === 'agente');
            }
        }

        return [
            'isAdmin' => $isAdmin,
            'isAgent' => $isAgent,
            'isSuperAdmin' => $isSuperAdmin,
            'userId' => $userId,
            'user' => $user,
            'empresaId' => $isSuperAdmin ? Yii::$app->session->get('empresa_id') : $user->id_company,
        ];
    }

    /**
     * Obtener lista de leads según el rol del usuario
     */
    private function getLeadsList($user)
    {
        try {
            $query = Lead::find();
            $empresaId = Yii::$app->session->get('empresa_id');
            
            if ($user->isSuperAdmin()) {
                if (!empty($empresaId)) {
                    $query->andWhere(['id_company' => $empresaId]);
                }
            } elseif ($user->isAgent()) {
                $query->where(['id_user' => $user->id_user]);
            } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
                $query->andWhere(['id_company' => $user->id_company]);
            }
            
            $leads = $query->all();
            return ArrayHelper::map($leads, 'id_lead', function($lead) {
                return $lead->name . ' ' . $lead->lastname . ' (' . $lead->phone . ')';
            });
        } catch (\Exception $e) {
            Yii::error('Error al obtener leads: ' . $e->getMessage(), 'reservation');
            return [];
        }
    }

    /**
     * Obtener lista de usuarios para asignación
     */
    private function getUserList()
    {
        try {
            $userList = User::find()
                ->select(['CONCAT(User.name, " ", User.lastname1) AS fullname', 'User.id_user'])
                ->innerJoin('Authentication', 'Authentication.id_user = User.id_user')
                ->where(['Authentication.id_status' => 1])
                ->orderBy(['User.name' => SORT_ASC])
                ->indexBy('id_user')
                ->column();

            if (empty($userList)) {
                $userList = User::find()
                    ->select(['CONCAT(name, " ", lastname1) AS fullname', 'id_user'])
                    ->orderBy(['name' => SORT_ASC])
                    ->indexBy('id_user')
                    ->column();
            }
            
            return $userList;
        } catch (\Exception $e) {
            Yii::error('Error al obtener usuarios: ' . $e->getMessage(), 'reservation');
            return [];
        }
    }

    // ============================================
    // LISTA DE RESERVACIONES
    // ============================================
    public function actionIndex()
    {
        try {
            $roleInfo = $this->getUserRole();
            $isAdmin = $roleInfo['isAdmin'];
            $isAgent = $roleInfo['isAgent'];
            $isSuperAdmin = $roleInfo['isSuperAdmin'];
            $user = $roleInfo['user'];
            $empresaId = $roleInfo['empresaId'];

            // Si es Super Admin y no tiene empresa seleccionada
            if ($isSuperAdmin && empty($empresaId)) {
                Yii::$app->session->setFlash('warning', 'Por favor, selecciona una empresa para continuar.');
                return $this->redirect(['empresa/index']);
            }

            // 🔥 QUERY BASE - Ahora usamos id_company de la tabla reservations
            $query = Reservation::find()
                ->alias('r')
                ->leftJoin('Lead l', 'r.id_lead = l.id_lead')
                ->orderBy(['r.date_reservation' => SORT_DESC, 'r.hour_s' => SORT_DESC]);

            // 🔥 FILTRO POR ROL Y EMPRESA - Usando r.id_company
            if ($isAgent) {
                $query->andWhere(['r.id_user' => $user->id_user]);
            } elseif ($isSuperAdmin) {
                if (!empty($empresaId)) {
                    $query->andWhere(['r.id_company' => $empresaId]);
                } else {
                    $query->andWhere(['r.id_reservation' => -1]);
                }
            } elseif ($isAdmin) {
                if (!empty($user->id_company)) {
                    $query->andWhere(['r.id_company' => $user->id_company]);
                }
            }

            // Filtros de búsqueda
            $search = Yii::$app->request->get('search', '');
            $fecha_inicio = Yii::$app->request->get('fecha_inicio', '');
            $fecha_fin = Yii::$app->request->get('fecha_fin', '');

            if (!empty($search)) {
                $query->andWhere([
                    'or',
                    ['like', 'l.name', $search],
                    ['like', 'l.lastname', $search],
                    ['like', 'l.phone', $search],
                    ['like', 'r.name_reservation', $search],
                ]);
            }

            if (!empty($fecha_inicio)) {
                $query->andWhere(['>=', 'r.date_reservation', $fecha_inicio]);
            }

            if (!empty($fecha_fin)) {
                $query->andWhere(['<=', 'r.date_reservation', $fecha_fin]);
            }

            $reservations = $query->all();

            // Estadísticas
            $totalReservations = count($reservations);
            $todayCount = 0;
            $upcomingCount = 0;
            $pastCount = 0;

            foreach ($reservations as $res) {
                if ($res->isToday()) {
                    $todayCount++;
                } elseif ($res->isPast()) {
                    $pastCount++;
                } else {
                    $upcomingCount++;
                }
            }

            // Obtener leads para el selector de filtros
            $leadsList = $this->getLeadsList($user);

            return $this->render('index', [
                'reservations' => $reservations,
                'totalReservations' => $totalReservations,
                'todayCount' => $todayCount,
                'upcomingCount' => $upcomingCount,
                'pastCount' => $pastCount,
                'isAdmin' => $isAdmin,
                'isAgent' => $isAgent,
                'isSuperAdmin' => $isSuperAdmin,
                'search' => $search,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'leadsList' => $leadsList,
            ]);

        } catch (\Exception $e) {
            Yii::error('Error en actionIndex: ' . $e->getMessage(), 'reservation');
            return $this->render('index', [
                'reservations' => [],
                'totalReservations' => 0,
                'todayCount' => 0,
                'upcomingCount' => 0,
                'pastCount' => 0,
                'isAdmin' => false,
                'isAgent' => false,
                'isSuperAdmin' => false,
                'search' => '',
                'fecha_inicio' => '',
                'fecha_fin' => '',
                'leadsList' => [],
            ]);
        }
    }

    // ============================================
    // VER RESERVACIÓN
    // ============================================
    public function actionView($id)
    {
        try {
            $model = $this->findModel($id);
            $roleInfo = $this->getUserRole();
            $isAdmin = $roleInfo['isAdmin'];
            $isAgent = $roleInfo['isAgent'];
            $isSuperAdmin = $roleInfo['isSuperAdmin'];
            $user = $roleInfo['user'];
            $empresaId = $roleInfo['empresaId'];

            // Verificar permisos
            if ($isAgent) {
                if ($model->id_user != $user->id_user) {
                    Yii::$app->session->setFlash('error', 'No tienes permiso para ver esta reservación.');
                    return $this->redirect(['index']);
                }
            } elseif ($isSuperAdmin) {
                if (!empty($empresaId) && $model->id_company != $empresaId) {
                    Yii::$app->session->setFlash('error', 'No tienes permiso para ver esta reservación.');
                    return $this->redirect(['index']);
                }
            } elseif ($isAdmin && !$isSuperAdmin) {
                if ($model->id_company != $user->id_company) {
                    Yii::$app->session->setFlash('error', 'No tienes permiso para ver esta reservación.');
                    return $this->redirect(['index']);
                }
            }

            return $this->render('view', [
                'model' => $model,
                'isAdmin' => $isAdmin,
                'isAgent' => $isAgent,
                'isSuperAdmin' => $isSuperAdmin,
            ]);

        } catch (NotFoundHttpException $e) {
            Yii::$app->session->setFlash('error', 'Reservación no encontrada.');
            return $this->redirect(['index']);
        } catch (\Exception $e) {
            Yii::error('Error en actionView: ' . $e->getMessage(), 'reservation');
            Yii::$app->session->setFlash('error', 'Error al cargar la reservación.');
            return $this->redirect(['index']);
        }
    }

    // ============================================
    // CREAR RESERVACIÓN
    // ============================================
    public function actionCreate($leadId = null)
    {
        $model = new Reservation();
        $roleInfo = $this->getUserRole();
        $isAdmin = $roleInfo['isAdmin'];
        $isAgent = $roleInfo['isAgent'];
        $isSuperAdmin = $roleInfo['isSuperAdmin'];
        $user = $roleInfo['user'];
        $empresaId = $roleInfo['empresaId'];

        if ($isSuperAdmin && empty($empresaId)) {
            Yii::$app->session->setFlash('warning', 'Por favor, selecciona una empresa para continuar.');
            return $this->redirect(['empresa/index']);
        }

        // 🔥 PRE-ASIGNAR EMPRESA
        if ($isSuperAdmin && !empty($empresaId)) {
            $model->id_company = $empresaId;
        } elseif ($user && !empty($user->id_company)) {
            $model->id_company = $user->id_company;
        }

        // Obtener leads según rol
        $leadsList = $this->getLeadsList($user);

        // Si hay leadId, verificar permisos y asignar
        if ($leadId) {
            $lead = Lead::findOne($leadId);
            if ($lead) {
                if ($isAgent && $lead->id_user != $user->id_user) {
                    Yii::$app->session->setFlash('error', 'No tienes permiso para crear reservaciones para este lead.');
                    return $this->redirect(['lead/index']);
                }
                if ($isSuperAdmin && !empty($empresaId) && $lead->id_company != $empresaId) {
                    Yii::$app->session->setFlash('error', 'No tienes permiso para crear reservaciones para este lead.');
                    return $this->redirect(['lead/index']);
                }
                $model->id_lead = $leadId;
                // 🔥 Asignar empresa desde el lead
                $model->id_company = $lead->id_company;
            }
        }

        // Si es agente, asignar usuario automáticamente
        if ($isAgent) {
            $model->id_user = $user->id_user;
        }

        // Obtener usuarios para asignación (solo admin)
        $userList = [];
        if ($isAdmin) {
            $userList = $this->getUserList();
        }

        if ($model->load(Yii::$app->request->post())) {
            // Verificar permisos
            if ($isAgent && $model->id_lead) {
                $leadCheck = Lead::find()
                    ->where(['id_lead' => $model->id_lead, 'id_user' => $user->id_user])
                    ->exists();
                
                if (!$leadCheck) {
                    Yii::$app->session->setFlash('error', 'No puedes crear reservaciones para leads de otros agentes.');
                    return $this->redirect(['create']);
                }
            }
            
            if ($isSuperAdmin && $model->id_lead) {
                $leadCheck = Lead::find()
                    ->where(['id_lead' => $model->id_lead, 'id_company' => $empresaId])
                    ->exists();
                
                if (!$leadCheck) {
                    Yii::$app->session->setFlash('error', 'No puedes crear reservaciones para leads de otra empresa.');
                    return $this->redirect(['create']);
                }
            }

            // 🔥 Asegurar id_company desde el lead
            if ($model->id_lead) {
                $lead = Lead::findOne($model->id_lead);
                if ($lead) {
                    $model->id_company = $lead->id_company;
                }
            }

            if ($isAgent) {
                $model->id_user = $user->id_user;
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Reservación creada exitosamente.');
                return $this->redirect(['view', 'id' => $model->id_reservation]);
            } else {
                Yii::$app->session->setFlash('error', 'Error al crear la reservación: ' . json_encode($model->getErrors()));
            }
        }

        return $this->render('create', [
            'model' => $model,
            'leadsList' => $leadsList,
            'userList' => $userList,
            'isAdmin' => $isAdmin,
            'isAgent' => $isAgent,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }

    // ============================================
    // ACTUALIZAR RESERVACIÓN
    // ============================================
    public function actionUpdate($id)
    {
        try {
            $model = $this->findModel($id);
            $roleInfo = $this->getUserRole();
            $isAdmin = $roleInfo['isAdmin'];
            $isAgent = $roleInfo['isAgent'];
            $isSuperAdmin = $roleInfo['isSuperAdmin'];
            $user = $roleInfo['user'];
            $empresaId = $roleInfo['empresaId'];

            if ($isSuperAdmin && empty($empresaId)) {
                Yii::$app->session->setFlash('warning', 'Por favor, selecciona una empresa para continuar.');
                return $this->redirect(['empresa/index']);
            }

            // Verificar permisos
            if ($isAgent) {
                if ($model->id_user != $user->id_user) {
                    Yii::$app->session->setFlash('error', 'No tienes permiso para editar esta reservación.');
                    return $this->redirect(['index']);
                }
            } elseif ($isSuperAdmin) {
                if (!empty($empresaId) && $model->id_company != $empresaId) {
                    Yii::$app->session->setFlash('error', 'No tienes permiso para editar esta reservación.');
                    return $this->redirect(['index']);
                }
            } elseif ($isAdmin && !$isSuperAdmin) {
                if ($model->id_company != $user->id_company) {
                    Yii::$app->session->setFlash('error', 'No tienes permiso para editar esta reservación.');
                    return $this->redirect(['index']);
                }
            }

            // Obtener leads según rol
            $leadsList = $this->getLeadsList($user);

            // Obtener usuarios para asignación (solo admin)
            $userList = [];
            if ($isAdmin) {
                $userList = $this->getUserList();
            }

            if ($model->load(Yii::$app->request->post())) {
                // Verificar permisos
                if ($isAgent && $model->id_lead) {
                    $leadCheck = Lead::find()
                        ->where(['id_lead' => $model->id_lead, 'id_user' => $user->id_user])
                        ->exists();
                    
                    if (!$leadCheck) {
                        Yii::$app->session->setFlash('error', 'No puedes asignar esta reservación a leads de otros agentes.');
                        return $this->redirect(['update', 'id' => $id]);
                    }
                }
                
                if ($isSuperAdmin && $model->id_lead) {
                    $leadCheck = Lead::find()
                        ->where(['id_lead' => $model->id_lead, 'id_company' => $empresaId])
                        ->exists();
                    
                    if (!$leadCheck) {
                        Yii::$app->session->setFlash('error', 'No puedes asignar esta reservación a leads de otra empresa.');
                        return $this->redirect(['update', 'id' => $id]);
                    }
                }

                // 🔥 Asegurar id_company desde el lead
                if ($model->id_lead) {
                    $lead = Lead::findOne($model->id_lead);
                    if ($lead) {
                        $model->id_company = $lead->id_company;
                    }
                }

                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Reservación actualizada exitosamente.');
                    return $this->redirect(['view', 'id' => $model->id_reservation]);
                } else {
                    Yii::$app->session->setFlash('error', 'Error al actualizar la reservación: ' . json_encode($model->getErrors()));
                }
            }

            return $this->render('update', [
                'model' => $model,
                'leadsList' => $leadsList,
                'userList' => $userList,
                'isAdmin' => $isAdmin,
                'isAgent' => $isAgent,
                'isSuperAdmin' => $isSuperAdmin,
            ]);

        } catch (NotFoundHttpException $e) {
            Yii::$app->session->setFlash('error', 'Reservación no encontrada.');
            return $this->redirect(['index']);
        } catch (\Exception $e) {
            Yii::error('Error en actionUpdate: ' . $e->getMessage(), 'reservation');
            Yii::$app->session->setFlash('error', 'Error al actualizar la reservación.');
            return $this->redirect(['index']);
        }
    }

    // ============================================
    // ELIMINAR RESERVACIÓN
    // ============================================
    public function actionDelete($id)
    {
        try {
            $roleInfo = $this->getUserRole();
            $isAdmin = $roleInfo['isAdmin'];
            $isSuperAdmin = $roleInfo['isSuperAdmin'];
            $user = $roleInfo['user'];
            $empresaId = $roleInfo['empresaId'];

            if (!$isAdmin) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para eliminar reservaciones.');
                return $this->redirect(['index']);
            }

            $model = $this->findModel($id);

            // Verificar permisos
            if ($isSuperAdmin) {
                if (!empty($empresaId) && $model->id_company != $empresaId) {
                    Yii::$app->session->setFlash('error', 'No tienes permiso para eliminar esta reservación.');
                    return $this->redirect(['index']);
                }
            } elseif (!$isSuperAdmin && $model->id_company != $user->id_company) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para eliminar esta reservación.');
                return $this->redirect(['index']);
            }
            
            if ($model->delete()) {
                Yii::$app->session->setFlash('success', 'Reservación eliminada exitosamente.');
            } else {
                Yii::$app->session->setFlash('error', 'Error al eliminar la reservación.');
            }

        } catch (NotFoundHttpException $e) {
            Yii::$app->session->setFlash('error', 'Reservación no encontrada.');
        } catch (\Exception $e) {
            Yii::error('Error en actionDelete: ' . $e->getMessage(), 'reservation');
            Yii::$app->session->setFlash('error', 'Error al eliminar la reservación.');
        }

        return $this->redirect(['index']);
    }

    // ============================================
    // FINDER
    // ============================================
    protected function findModel($id)
    {
        $model = Reservation::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('La reservación solicitada no existe.');
        }
        return $model;
    }
}