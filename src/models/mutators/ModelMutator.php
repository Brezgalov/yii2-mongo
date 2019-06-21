<?php

namespace Brezgalov\Yii2Mongo\Models\Mutators;

use yii\db\ActiveRecord;

class ModelMutator extends BaseMutator
{
    /**
     * @var bool
     */
    public $array = false;

    /**
     * @var string
     */
    public $target_class;

    /**
     * @var string
     */
    public $target_attribute = 'id';

    /**
     * false, если не преобразовывать
     * Массив, если нужно преобразовать в масссив - значение передается в ->select($asArray)->asArray()->all();
     * @var bool|array|string
     */
    public $as_array = false;

    /**
     * @var bool
     */
    protected $skipMissingKeys = false;

    /**
     * ModelMutator constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    protected function performMutate($field, array &$data, $newFieldName)
    {
        $idsField = $field . '_id';
        if ($this->array) {
            $idsField .= 's';
        }

        if (array_key_exists($idsField, $data)) {
            if ($this->array && is_string($data[$idsField])) {
                //"1,2,3" format
                $data[$idsField] = explode(',', str_replace(' ', '', $data[$idsField]));
            }
            $this->mutateOnIds($idsField, $data, $newFieldName);
            unset($data[$idsField]);
        } elseif (array_key_exists($field, $data)) {
            $this->mutateOnData($field, $data, $newFieldName);
        }

        if (
            !$this->array &&
            array_key_exists($newFieldName, $data) &&
            is_array($data[$newFieldName])
        ) {
            $resultValue = $data[$newFieldName];
            $data[$newFieldName] = empty($resultValue) ? null : array_shift($resultValue);
        }
    }

    /**
     * use given data as id
     * @param $field
     * @param array $data
     * @param $newFieldName
     */
    protected function mutateOnIds($field, array &$data, $newFieldName)
    {
        if (!$this->array) {
            $data[$field] = [ $data[$field] ];
        }

        $data[$newFieldName] = $this->getItems($data[$field]);
    }

    /**
     * @param $field
     * @param array $data
     * @param $newFieldName
     */
    protected function mutateOnData($field, array &$data, $newFieldName)
    {
        if (!$this->array) {
            $data[$field] = [ $data[$field] ];
        }

        $attr = $this->target_attribute;
        $ids = [];
        foreach ($data[$field] as $item) {
            if ($item instanceof \stdClass || $item instanceof ActiveRecord) {
                $ids[] = @$item->{$attr};
            } elseif (is_array($data[$field])) {
                $ids[] = @$item[$attr];
            }
        }
        $data[$newFieldName] = $this->getItems($ids);
    }

    /**
     * @param $ids
     * @return mixed
     */
    protected function getItems($ids)
    {
        $class = $this->target_class;
        $query = $class::find();
        $useArrays = (bool)$this->as_array;
        if ($useArrays and is_array($this->as_array) or is_string($this->as_array)) {
            $query->select($this->as_array);
        }
        $query->where(['in', $this->target_attribute, $ids]);
        if ($useArrays) {
            $query->asArray();
        }
        return $query->all();
    }
}