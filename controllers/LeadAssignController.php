<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\Lead;
use app\models\User;
use app\components\ErrorManager;

class LeadAssignController extends Controller
{
    public $layout = 'main';

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'assign' => ['POST'],
                    'unassign' => ['POST'],
                    'mass-assign' => ['POST'],
                ],
            ],
        ];
    }

    // ============================================
    // LISTA DE ASIGNACIÓN CON FILTRO Y CONTEO
    // ============================================
    public function actionIndex()
    {
        try {
            $user = Yii::$app->user->identity;
            
            if (!$user || !$user->isAdmin()) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para acceder a esta sección.');
                return $this->redirect(['dashboard/index']);
            }

            $companyId = $user->id_company;

            // ============================================
            // 🔥 OBTENER AGENTES
            // ============================================
            $agentsQuery = User::find()
                ->joinWith('authentication')
                ->where(['Authentication.id_role' => 3]);

            if (!$user->isSuperAdmin()) {
                $agentsQuery->andWhere(['User.id_company' => $companyId]);
            }

            $agents = $agentsQuery
                ->orderBy(['User.name' => SORT_ASC])
                ->all();

            if (empty($agents)) {
                Yii::$app->session->setFlash('warning', 'No hay agentes registrados en tu empresa. Crea agentes primero.');
            }

            // ============================================
            // 🔥 OBTENER TODOS LOS LEADS ACTIVOS
            // ============================================
            $allLeadsQuery = Lead::find()
                ->where(['not in', 'Lead.id_status', [1, 10]])
                ->with('user');

            if (!$user->isSuperAdmin()) {
                $allLeadsQuery->andWhere(['Lead.id_company' => $companyId]);
            }

            $allLeads = $allLeadsQuery->all();

            // ============================================
            // 🔥 SEPARAR ASIGNADOS Y SIN ASIGNAR
            // ============================================
            $unassignedLeads = [];
            $assignedLeads = [];

            foreach ($allLeads as $lead) {
                if (empty($lead->id_user) || !$lead->user || !$lead->user->isAgent()) {
                    $unassignedLeads[] = $lead;
                } else {
                    $assignedLeads[] = $lead;
                }
            }

            // ============================================
            // 🔥 CONTAR LEADS POR AGENTE
            // ============================================
            $agentCounts = [];
            foreach ($agents as $agent) {
                $agentCounts[$agent->id_user] = 0;
            }
            foreach ($assignedLeads as $lead) {
                if (isset($agentCounts[$lead->id_user])) {
                    $agentCounts[$lead->id_user]++;
                }
            }
            // Ordenar por cantidad descendente
            arsort($agentCounts);

            // ============================================
            // 🔥 FILTRO POR AGENTE
            // ============================================
            $selectedAgentId = Yii::$app->request->get('agent_id', null);

            // Validar que el agente seleccionado existe
            if ($selectedAgentId !== null && $selectedAgentId !== '') {
                $agentExists = false;
                foreach ($agents as $agent) {
                    if ($agent->id_user == $selectedAgentId) {
                        $agentExists = true;
                        break;
                    }
                }
                if (!$agentExists) {
                    $selectedAgentId = null;
                }
            }

            // ============================================
            // 🔥 ORDENAR POR FECHA
            // ============================================
            usort($unassignedLeads, function($a, $b) {
                return strtotime($b->created_at) - strtotime($a->created_at);
            });
            usort($assignedLeads, function($a, $b) {
                return strtotime($b->created_at) - strtotime($a->created_at);
            });

            // ============================================
            // 🔥 TOTALES
            // ============================================
            $totalAssigned = count($assignedLeads);
            $totalUnassigned = count($unassignedLeads);

            return $this->render('index', [
                'unassignedLeads' => $unassignedLeads,
                'assignedLeads' => $assignedLeads,
                'agents' => $agents,
                'agentCounts' => $agentCounts,
                'selectedAgentId' => $selectedAgentId,
                'totalAssigned' => $totalAssigned,
                'totalUnassigned' => $totalUnassigned,
                'isSuperAdmin' => $user->isSuperAdmin(),
                'leadsTotal' => $allLeads,
            ]);

        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al cargar la asignación de leads');
            return $this->render('index', [
                'unassignedLeads' => [],
                'assignedLeads' => [],
                'agents' => [],
                'agentCounts' => [],
                'selectedAgentId' => null,
                'totalAssigned' => 0,
                'totalUnassigned' => 0,
                'isSuperAdmin' => false,
                'leadsTotal' => [],
            ]);
        }
    }

    // ============================================
    // ASIGNACIÓN MASIVA
    // ============================================
    public function actionMassAssign()
    {
        try {
            $request = Yii::$app->request;
            $leadIds = $request->post('lead_ids');
            $agentId = $request->post('agent_id');

            if (empty($leadIds) || empty($agentId)) {
                throw new \Exception('Debes seleccionar al menos un lead y un agente.');
            }

            // Asegurar que leadIds es un array
            if (!is_array($leadIds)) {
                $leadIds = [$leadIds];
            }

            if (empty($leadIds)) {
                throw new \Exception('Formato de datos inválido.');
            }

            $user = Yii::$app->user->identity;
            if (!$user->isAdmin()) {
                throw new \Exception('No tienes permiso para asignar leads.');
            }
            
            $agent = User::findOne($agentId);
            if (!$agent || !$agent->isAgent()) {
                throw new \Exception('El usuario seleccionado no es un agente válido.');
            }

            if (!$user->isSuperAdmin() && $agent->id_company != $user->id_company) {
                throw new \Exception('El agente seleccionado no pertenece a tu empresa.');
            }

            $count = 0;
            $errors = [];

            foreach ($leadIds as $leadId) {
                $lead = Lead::findOne($leadId);
                if (!$lead) {
                    $errors[] = "Lead ID $leadId no encontrado.";
                    continue;
                }

                if (!$user->isSuperAdmin() && $lead->id_company != $user->id_company) {
                    $errors[] = "Lead ID $leadId no pertenece a tu empresa.";
                    continue;
                }

                if ($lead->assignToAgent($agentId)) {
                    $count++;
                } else {
                    $errors[] = "Error al asignar lead ID $leadId.";
                }
            }

            if ($count > 0) {
                $message = "Se asignaron {$count} leads exitosamente al agente " . $agent->name . ".";
                if (!empty($errors)) {
                    $message .= " Errores: " . implode(' ', $errors);
                }
                Yii::$app->session->setFlash('success', $message);
            } else {
                Yii::$app->session->setFlash('error', 'No se pudo asignar ningún lead. ' . implode(' ', $errors));
            }

        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error en asignación masiva');
            Yii::$app->session->setFlash('error', 'Error al asignar leads: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    // ============================================
    // ASIGNAR UN LEAD INDIVIDUAL
    // ============================================
    public function actionAssign()
    {
        try {
            $leadId = Yii::$app->request->post('lead_id');
            $agentId = Yii::$app->request->post('agent_id');

            if (empty($leadId) || empty($agentId)) {
                throw new \Exception('Lead y agente son requeridos.');
            }

            $lead = Lead::findOne($leadId);
            if (!$lead) {
                throw new NotFoundHttpException('Lead no encontrado.');
            }

            $user = Yii::$app->user->identity;
            if (!$user->isAdmin()) {
                throw new \Exception('No tienes permiso para asignar leads.');
            }

            if (!$user->isSuperAdmin() && $lead->id_company != $user->id_company) {
                throw new \Exception('Este lead no pertenece a tu empresa.');
            }

            $agent = User::findOne($agentId);
            if (!$agent || !$agent->isAgent()) {
                throw new \Exception('El usuario seleccionado no es un agente válido.');
            }

            if (!$user->isSuperAdmin() && $agent->id_company != $user->id_company) {
                throw new \Exception('El agente seleccionado no pertenece a tu empresa.');
            }

            if ($lead->assignToAgent($agentId)) {
                Yii::$app->session->setFlash('success', 'Lead asignado exitosamente a ' . $agent->name . '.');
            } else {
                throw new \Exception('Error al asignar el lead.');
            }

        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al asignar lead');
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    // ============================================
    // DESASIGNAR LEAD
    // ============================================
    public function actionUnassign()
    {
        try {
            $leadId = Yii::$app->request->post('lead_id');

            if (!$leadId) {
                throw new \Exception('Lead requerido.');
            }

            $lead = Lead::findOne($leadId);
            if (!$lead) {
                throw new NotFoundHttpException('Lead no encontrado.');
            }

            $user = Yii::$app->user->identity;
            if (!$user->isAdmin()) {
                throw new \Exception('No tienes permiso para desasignar leads.');
            }

            if (!$user->isSuperAdmin() && $lead->id_company != $user->id_company) {
                throw new \Exception('Este lead no pertenece a tu empresa.');
            }

            if ($lead->unassignAgent()) {
                Yii::$app->session->setFlash('success', 'Lead desasignado exitosamente.');
            } else {
                throw new \Exception('Error al desasignar el lead.');
            }

        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al desasignar lead');
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    // ============================================
    // OBTENER LEADS DE UN AGENTE (AJAX)
    // ============================================
    public function actionGetAgentLeads($agentId)
    {
        try {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            
            $user = Yii::$app->user->identity;
            if (!$user->isAdmin()) {
                return ['error' => 'No tienes permiso.'];
            }

            $leads = Lead::find()
                ->where(['id_user' => $agentId])
                ->andWhere(['not in', 'id_status', [1, 10]])
                ->select(['id_lead', 'name', 'lastname', 'phone', 'id_status', 'created_at'])
                ->asArray()
                ->all();

            return $leads;

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // ============================================
    // BUSCAR LEADS SIN ASIGNAR (AJAX)
    // ============================================
    public function actionSearchUnassigned()
    {
        try {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            
            $user = Yii::$app->user->identity;
            if (!$user->isAdmin()) {
                return ['error' => 'No tienes permiso.'];
            }

            $search = Yii::$app->request->get('search');
            
            $query = Lead::find()
                ->where(['or',
                    ['id_user' => null],
                    ['id_user' => 0],
                ])
                ->andWhere(['not in', 'id_status', [1, 10]]);

            if (!$user->isSuperAdmin()) {
                $query->andWhere(['id_company' => $user->id_company]);
            }

            if ($search) {
                $query->andWhere(['or',
                    ['like', 'name', $search],
                    ['like', 'lastname', $search],
                    ['like', 'phone', $search],
                ]);
            }

            $leads = $query->limit(20)->all();
            
            $result = [];
            foreach ($leads as $lead) {
                $result[] = [
                    'id_lead' => $lead->id_lead,
                    'name' => $lead->name . ' ' . $lead->lastname,
                    'phone' => $lead->phone,
                    'status' => $lead->getStatusName(),
                ];
            }
            
            return $result;

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}