<?php

namespace Brezgalov\Yii2Mongo\Models\Mutators;

use MongoDB\BSON\ObjectId;

/**
 * Class MongoIdMutator
 * Преобразует MongoId в string
 *
 * @package Brezgalov\Yii2Mongo\Models\Mutators
 */
class MongoIdMutator extends BaseMutator
{
    public $fields = ['_id' => 'id'];

    /**
     * {@inheritdoc}
     */
    public function performMutate($field, array &$data, $newFieldName)
    {
        if (!array_key_exists($field, $data)) {
            return;
        }

        if ($data[$field] instanceof ObjectId) {
            $data[$newFieldName] = (string)$data[$field];
        } elseif (is_array($data[$field])) {
            $data[$newFieldName] = $data[$field]['oid'];
        } else {
            $data[$newFieldName] = $data[$field];
        }
        unset($data[$field]);
    }
}