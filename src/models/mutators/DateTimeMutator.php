<?php

namespace Brezgalov\Yii2Mongo\Models\Mutators;

class DateTimeMutator extends BaseMutator
{
    protected function performMutate($field, array &$data, $newFieldName)
    {
        try {
            $data[$newFieldName] = new \DateTime($data[$field]);
        } catch (\Exception $ex) {
            $data[$newFieldName] = $data[$field];
        }

    }
}