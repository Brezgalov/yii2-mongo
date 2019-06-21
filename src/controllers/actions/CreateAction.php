<?php

namespace Brezgalov\Yii2Mongo\Controllers\Actions;

class CreateAction extends \yii\rest\CreateAction
{
    public function run()
    {
        $modelClass = $this->modelClass;
        $args = [];
        if ($this->scenario) {
            $args['scenario'] = $this->scenario;
        }
        $model = new $modelClass($args);

        $model->load(\Yii::$app->request->getBodyParams(), '');
        $model->save();

        return $model;
    }
}