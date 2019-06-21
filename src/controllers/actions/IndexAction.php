<?php

namespace Brezgalov\Yii2Mongo\Controllers\Actions;

class IndexAction extends \yii\rest\IndexAction
{
    /**
     * @var int
     */
    public $page_size_default = 25;

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $modelClass = $this->modelClass;
        return $modelClass::find($this->prepareCondition(), [], $this->getOptions());
    }

    /**
     * prepare mongo condition for fetch
     * @return array
     */
    public function prepareCondition()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return [
            'limit' => $this->getLimit(),
            'skip'  => $this->getSkip(),
        ];
    }

    /**
     * @return mixed
     */
    public function getLimit()
    {
        return \Yii::$app->request->getQueryParam('limit', $this->page_size_default);
    }

    /**
     * @return mixed
     */
    public function getSkip()
    {
        return \Yii::$app->request->getQueryParam('skip', 0);
    }
}