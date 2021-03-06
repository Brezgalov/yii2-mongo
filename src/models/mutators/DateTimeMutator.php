<?php

namespace Brezgalov\Yii2Mongo\Models\Mutators;

/**
 * Class DateTimeMutator
 * Преобразует введенную дату в DateTime, если может
 *
 * @package Brezgalov\Yii2Mongo\Models\Mutators
 */
class DateTimeMutator extends BaseMutator
{
    protected function performMutate($field, array &$data, $newFieldName)
    {
        try {
            $data[$newFieldName] = is_string($data[$field]) ? new \DateTime($data[$field]) : $data[$field];
        } catch (\Exception $ex) {
            $data[$newFieldName] = $data[$field];
        }
    }
}