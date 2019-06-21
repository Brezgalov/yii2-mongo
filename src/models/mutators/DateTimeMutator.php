<?php

namespace Brezgalov\Yii2Mongo\Models\Mutators;

class DateTimeMutator extends BaseMutator
{
    protected function performMutate($field, array &$data, $newFieldName)
    {
        $data[$newFieldName] = new \DateTime($data[$field]);
    }
}