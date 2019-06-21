<?php

namespace Brezgalov\Yii2Mongo\Models\Mutators;

/**
 * Class DtoMutator extends ModelMutator
 * Преобразует модель в массив, на основе которого создается DTO
 * @package Brezgalov\Yii2Mongo\Models\Mutators
 */
class DtoMutator extends ModelMutator
{
    /**
     * @var string
     */
    public $dto_class;

    /**
     * @inheritdoc
     */
    public function mutate(array &$data)
    {
        if (!(bool)$this->as_array) {
            $this->as_array = true;
        }

        return parent::mutate($data);
    }

    /**
     * @inheritdoc
     */
    protected function getItems($ids)
    {
        $items = parent::getItems($ids);
        foreach ($items as $i => $item) {
            $items[$i] = new $this->dto_class($item);
        }
        return $items;
    }
}