<?php

namespace Brezgalov\Yii2Mongo\Controllers\Actions;

class DeleteAction extends \yii\rest\DeleteAction
{
    public function run($id)
    {
        $modelClass = $this->modelClass;
        $model = $modelClass::findById($id);
        $code = 404;
        if (!empty($model)) {
            if (!$model->delete() || $model->hasErrors()) {
                return $model;
            }
            $code = 204;
        }
        \Yii::$app->response->statusCode = $code;
    }
}