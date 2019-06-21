<?php

namespace Brezgalov\Yii2Mongo\Controllers\Actions;

class UpdateAction extends \yii\rest\UpdateAction
{
    public function run($id)
    {
        $modelClass = $this->modelClass;
        $model = $modelClass::findById($id);
        if (empty($model)) {
            \Yii::$app->response->setStatusCode(404);
            return;
        }

        if (!empty($this->checkAccess)) {
            call_user_func($this->checkAccess, $this->id, $model);
        }

        $model->load(\Yii::$app->request->getBodyParams(), '');
        $model->save();

        return $model;
    }
}