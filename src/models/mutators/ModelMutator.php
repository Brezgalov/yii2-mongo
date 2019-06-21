<?php

namespace Brezgalov\Yii2Mongo\Models\Mutators;

use app\modules\info\models\ActiveRecordsCache;
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
     * @var bool
     */
    protected $skipMissingKeys = false;

    /**
     * @var ActiveRecordsCache
     */
    protected $cache;

    /**
     * ModelMutator constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->cache = new ActiveRecordsCache([
            'id_field' => $this->target_attribute,
        ]);
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

        $newVals = [];
        foreach ($data[$field] as $id) {
            $instance = $this->cache->findItem($this->target_class, $id);
            if ($instance) {
                $newVals[] = $instance;
            }
        }
        $data[$newFieldName] = $newVals;
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
        $newVals = [];
        foreach ($data[$field] as $item) {
            if ($item instanceof \stdClass || $item instanceof ActiveRecord) {
                $newVals[] = $this->cache->findItem($this->target_class, @$item->{$attr});
            } elseif (is_array($data[$field])) {
                $newVals[] = $this->cache->findItem($this->target_class, @$item[$attr]);
            }
        }
        $data[$newFieldName] = $newVals;
    }
}