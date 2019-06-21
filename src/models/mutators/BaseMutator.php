<?php

namespace Brezgalov\Yii2Mongo\Models\Mutators;

use yii\base\Model;

/**
 * Class BaseMutator
 * bool $perform_once - после первой мутации данных - прекратить мутировать
 * array $fields - какие поля подвергнуть мутации
 * bool $skipMissingKeys - не пытаться мутировать, если поле отсутствует в данных
 * bool $dropOldKey - если поле было переименованно - удалить старое значение из данных
 *
 * @package Brezgalov\Yii2Mongo\Models\Mutators
 */
abstract class BaseMutator extends Model
{
    /**
     * array of fields to mutate
     * @var array
     */
    public $fields = [];

    /**
     * @var bool
     */
    public $perform_once = false;

    /**
     * @var bool
     */
    protected $skipMissingKeys = true;

    /**
     * @var bool
     */
    protected $dropOldKey = true;

    /**
     * mutate data set
     * @param array $data
     * @return void
     */
     public function mutate(array &$data)
     {
         foreach ($this->fields as $field => $newName) {
             if (!is_string($field)) {
                 $field = $newName;
             }
             if ($this->skipMissingKeys && !array_key_exists($field, $data)) {
                continue;
             }

             $this->performMutate($field, $data, $newName);
             if ($this->dropOldKey && $field !== $newName) {
                 unset($data[$field]);
             }
             if ($this->perform_once) {
                 return;
             }
         }
     }

    /**
     * perform field mutation in data array
     * @param string $field
     * @param array $data
     * @param string $newFieldName
     * @return void
     */
     protected abstract function performMutate($field, array &$data, $newFieldName);
}