<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\SalesTracking;
use app\models\FormularioCita;
use app\models\Lead;
use app\components\ErrorManager;

class CitaController extends Controller
{
    // Lista de citas
    public function actionIndex()
    {
        try {
            $citas = SalesTracking::find()
                ->orderBy(['date_s' => SORT_DESC])
                ->all();

            return $this->render('index', [
                'citas' => $citas,
            ]);
        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al cargar las citas');
            return $this->render('index', ['citas' => []]);
        }
    }

    // Formulario para agendar nueva cita
    public function actionCreate()
    {
        $model = new FormularioCita();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            try {
                $tipoCita = SalesTracking::findOne($model->id_sales_tracking);

                if (!$tipoCita) {
                    throw new \yii\base\Exception('Tipo de cita no válido');
                }

                // Guardar en Lead
                $lead = new Lead();
                $lead->name = $model->name;
                $lead->lastname = $model->lastname;
                $lead->phone = $model->phone;
                $lead->comments = $model->comments;
                $lead->status = $tipoCita->status_sales;
                $lead->created_at = $model->fecha;
                $lead->id_user = Yii::$app->user->id ?: 1;

                if (!$lead->save()) {
                    throw new \yii\base\Exception('Error al guardar el cliente: ' . json_encode($lead->errors));
                }

                // Guardar cita en Sales_tracking
                $cita = new SalesTracking();
                $cita->id_lead = $lead->id_lead;
                $cita->id_user = Yii::$app->user->id ?: 1;
                $cita->date_s = $model->fecha . ' ' . $model->hora . ':00';
                $cita->hour = $model->hora;
                $cita->status_sales = $tipoCita->status_sales;
                $cita->comments = $model->comments;

                if (!$cita->save()) {
                    throw new \yii\base\Exception('Error al guardar la cita: ' . json_encode($cita->errors));
                }

                Yii::$app->session->setFlash('success', 'Cita agendada exitosamente');
                return $this->redirect(['create']);

            } catch (\yii\db\Exception $e) {
                Yii::$app->session->setFlash('error', 'Error de base de datos al agendar la cita.<br>Contacta con el administrador');
                Yii::error($e->getMessage(), 'cita\create');
            } catch (\Exception $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        // Obtener últimas 5 citas
        $ultimasCitas = SalesTracking::find()
            ->where(['not', ['date_s' => null]])
            ->orderBy(['date_s' => SORT_DESC])
            ->limit(5)
            ->all();

        // Agregar nombre del cliente a cada cita
        foreach ($ultimasCitas as $cita) {
            if ($cita->id_lead) {
                $lead = Lead::findOne($cita->id_lead);
                $cita->cliente = $lead ? $lead->name . ' ' . $lead->lastname : 'Sin cliente';
            } else {
                $cita->cliente = 'Sin cliente';
            }
        }

        return $this->render('create', [
            'model' => $model,
            'ultimasCitas' => $ultimasCitas,
        ]);
    }

    // Ver detalles de la cita
    public function actionView($id)
    {
        try {
            $model = SalesTracking::findOne($id);

            if (!$model) {
                throw new \yii\web\NotFoundHttpException('Cita no encontrada');
            }

            return $this->render('view', [
                'model' => $model,
            ]);
        } catch (\yii\web\NotFoundHttpException $e) {
            Yii::$app->session->setFlash('info', 'Cita no encontrada');
            return $this->redirect(['index']);
        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al cargar la cita');
            return $this->redirect(['index']);
        }
    }

    // Actualizar cita
    public function actionUpdate($id)
    {
        try {
            $cita = SalesTracking::findOne($id);

            if (!$cita) {
                throw new \yii\web\NotFoundHttpException('Cita no encontrada');
            }

            $lead = Lead::findOne($cita->id_lead);
            $model = new FormularioCita();
            $model->name = $lead->name ?? '';
            $model->lastname = $lead->lastname ?? '';
            $model->phone = $lead->phone ?? '';
            $model->comments = $cita->comments;
            $model->fecha = substr($cita->date_s, 0, 10);
            $model->hora = $cita->hour;

            $tipoCita = SalesTracking::find()
                ->where(['status_sales' => $cita->status_sales])
                ->one();
            $model->id_sales_tracking = $tipoCita->id_sales_tracking ?? null;

            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                try {
                    $nuevoTipoCita = SalesTracking::findOne($model->id_sales_tracking);

                    if ($lead) {
                        $lead->name = $model->name;
                        $lead->lastname = $model->lastname;
                        $lead->phone = $model->phone;
                        $lead->comments = $model->comments;
                        $lead->status = $nuevoTipoCita->status_sales;
                        $lead->save();
                    }

                    $cita->date_s = $model->fecha . ' ' . $model->hora . ':00';
                    $cita->hour = $model->hora;
                    $cita->status_sales = $nuevoTipoCita->status_sales;
                    $cita->comments = $model->comments;

                    if (!$cita->save()) {
                        throw new \yii\base\Exception('Error al actualizar la cita');
                    }

                    Yii::$app->session->setFlash('success', 'Cita actualizada exitosamente');
                    return $this->redirect(['index']);

                } catch (\Exception $e) {
                    Yii::$app->session->setFlash('error', $e->getMessage());
                }
            }

            return $this->render('update', [
                'model' => $model,
                'cita' => $cita,
            ]);

        } catch (\yii\web\NotFoundHttpException $e) {
            Yii::$app->session->setFlash('info', 'Cita no encontrada');
            return $this->redirect(['index']);
        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al actualizar la cita');
            return $this->redirect(['index']);
        }
    }

    // Eliminar cita
    public function actionDelete($id)
    {
        try {
            $cita = SalesTracking::findOne($id);
            if ($cita) {
                if ($cita->id_lead) {
                    Lead::findOne($cita->id_lead)->delete();
                }
                $cita->delete();
                Yii::$app->session->setFlash('success', 'Cita eliminada exitosamente');
            } else {
                Yii::$app->session->setFlash('info', 'Cita no encontrada');
            }
        } catch (\yii\db\Exception $e) {
            Yii::$app->session->setFlash('error', 'Error de base de datos al eliminar la cita');
            Yii::error($e->getMessage(), 'cita\delete');
        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al eliminar la cita');
        }

        return $this->redirect(['index']);
    }

    // Obtener horas disponibles (AJAX)
    public function actionGetHoras()
    {
        try {
            $fecha = Yii::$app->request->get('fecha');
            $horasDisponibles = FormularioCita::getHorasDisponibles();

            return $this->asJson([
                'success' => true,
                'horas' => $horasDisponibles,
                'fecha' => $fecha,
            ]);

        } catch (\Exception $e) {
            Yii::error($e->getMessage(), 'cita\ajax');
            return $this->asJson([
                'success' => false,
                'message' => 'Error al cargar las horas disponibles',
            ]);
        }
    }
}