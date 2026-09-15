<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\Task;
use app\models\User;
use app\components\ErrorManager;

class TaskAssignController extends Controller
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

    /**
     * Vista principal de asignación de tareas
     */
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;
        
        if (!$user || !$user->isAdmin()) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para acceder a esta sección.');
            return $this->redirect(['dashboard/index']);
        }

        $companyId = $user->id_company;

        // Obtener agentes de la empresa
        $agents = User::find()
            ->joinWith('authentication')
            ->where(['Authentication.id_role' => 3])
            ->andWhere(['User.id_company' => $companyId])
            ->andWhere(['<>', 'User.id_user', $user->id])
            ->orderBy(['User.name' => SORT_ASC])
            ->all();

        if ($user->isSuperAdmin()) {
            $agents = User::find()
                ->joinWith('authentication')
                ->where(['Authentication.id_role' => 3])
                ->orderBy(['User.name' => SORT_ASC])
                ->all();
        }

        if (empty($agents)) {
            Yii::$app->session->setFlash('warning', 'No hay agentes registrados en tu empresa. Crea agentes primero.');
        }

        // 🔥 Tareas sin asignar (usando alias 't')
        $unassignedTasks = Task::find()
            ->alias('t')
            ->where(['t.id_user' => null])
            ->orderBy(['t.id_task' => SORT_DESC])
            ->all();

        // 🔥 Tareas asignadas (usando alias 't' y 'u')
        $assignedTasks = Task::find()
            ->alias('t')
            ->joinWith('user u')
            ->where(['not', ['t.id_user' => null]])
            ->orderBy(['t.id_task' => SORT_DESC])
            ->all();

        return $this->render('index', [
            'unassignedTasks' => $unassignedTasks,
            'assignedTasks' => $assignedTasks,
            'agents' => $agents,
            'isSuperAdmin' => $user->isSuperAdmin(),
        ]);
    }

    /**
     * Asignar una tarea a un agente
     */
    public function actionAssign()
    {
        $request = Yii::$app->request;
        $taskId = $request->post('task_id');
        $agentId = $request->post('agent_id');

        try {
            $task = Task::findOne($taskId);
            if (!$task) {
                throw new NotFoundHttpException('Tarea no encontrada.');
            }

            $user = Yii::$app->user->identity;
            if (!$user->isAdmin()) {
                throw new \Exception('No tienes permiso para asignar tareas.');
            }

            $agent = User::findOne($agentId);
            if (!$agent) {
                throw new \Exception('El agente seleccionado no existe.');
            }
            if (!$agent->isAgent()) {
                throw new \Exception('El usuario seleccionado no es un agente válido.');
            }

            if (!$user->isSuperAdmin() && $agent->id_company != $user->id_company) {
                throw new \Exception('El agente seleccionado no pertenece a tu empresa.');
            }

            $task->id_user = $agentId;
            
            if ($task->save()) {
                Yii::$app->session->setFlash('success', 'Tarea asignada exitosamente al agente.');
            } else {
                throw new \Exception('Error al asignar la tarea.');
            }

        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al asignar tarea');
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Desasignar una tarea (quitar agente)
     */
    public function actionUnassign()
    {
        $taskId = Yii::$app->request->post('task_id');

        try {
            $task = Task::findOne($taskId);
            if (!$task) {
                throw new NotFoundHttpException('Tarea no encontrada.');
            }

            $user = Yii::$app->user->identity;
            if (!$user->isAdmin()) {
                throw new \Exception('No tienes permiso para desasignar tareas.');
            }

            $task->id_user = null;
            
            if ($task->save()) {
                Yii::$app->session->setFlash('success', 'Tarea desasignada exitosamente.');
            } else {
                throw new \Exception('Error al desasignar la tarea.');
            }

        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al desasignar tarea');
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Asignación masiva de tareas a un agente
     */
    public function actionMassAssign()
    {
        $request = Yii::$app->request;
        $taskIdsJson = $request->post('task_ids');
        $agentId = $request->post('agent_id');

        if (empty($taskIdsJson) || empty($agentId)) {
            Yii::$app->session->setFlash('error', 'Debes seleccionar al menos una tarea y un agente.');
            return $this->redirect(['index']);
        }

        try {
            $taskIds = json_decode($taskIdsJson, true);
            if (!is_array($taskIds)) {
                throw new \Exception('Formato de datos inválido.');
            }

            $user = Yii::$app->user->identity;
            
            $agent = User::findOne($agentId);
            if (!$agent || !$agent->isAgent()) {
                throw new \Exception('El usuario seleccionado no es un agente válido.');
            }

            $count = 0;
            $errors = [];

            foreach ($taskIds as $taskId) {
                $task = Task::findOne($taskId);
                if (!$task) {
                    $errors[] = "Tarea ID $taskId no encontrada.";
                    continue;
                }

                $task->id_user = $agentId;
                
                if ($task->save()) {
                    $count++;
                } else {
                    $errors[] = "Error al asignar tarea ID $taskId.";
                }
            }

            if ($count > 0) {
                $message = "Se asignaron {$count} tareas exitosamente.";
                if (!empty($errors)) {
                    $message .= " Errores: " . implode(' ', $errors);
                }
                Yii::$app->session->setFlash('success', $message);
            } else {
                Yii::$app->session->setFlash('error', 'No se pudo asignar ninguna tarea. ' . implode(' ', $errors));
            }

        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error en asignación masiva');
            Yii::$app->session->setFlash('error', 'Error al asignar tareas: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }
}