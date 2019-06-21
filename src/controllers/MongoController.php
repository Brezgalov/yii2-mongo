<?php

namespace Brezgalov\Yii2Mongo\Controllers;

use Brezgalov\Yii2Mongo\Controllers\Actions\IndexAction;
use Brezgalov\Yii2Mongo\Controllers\Actions\ViewAction;
use Brezgalov\Yii2Mongo\Controllers\Actions\CreateAction;
use Brezgalov\Yii2Mongo\Controllers\Actions\UpdateAction;
use Brezgalov\Yii2Mongo\Controllers\Actions\DeleteAction;
use yii\rest\ActiveController;

class MongoController extends ActiveController
{
    public function actions()
    {
        $acts = parent::actions();
        $acts['index']['class']     = IndexAction::className();
        $acts['view']['class']      = ViewAction::className();
        $acts['create']['class']    = CreateAction::className();
        $acts['update']['class']    = UpdateAction::className();
        $acts['delete']['class']    = DeleteAction::className();
        return $acts;
    }
}