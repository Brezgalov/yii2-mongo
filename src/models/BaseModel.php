<?php

namespace Brezgalov\Yii2Mongo\Models;

use Brezgalov\Yii2Mongo\Manager;
use Brezgalov\Yii2Mongo\Models\Mutators\MongoIdMutator;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\Command;
use MongoDB\Driver\WriteResult;
use yii\helpers\ArrayHelper;

class BaseModel extends \yii\base\Model
{
    const COLLECTION_NAMESPACE = 'db.collection';

    /**
     * @var string - mongoID
     */
    public $id;

    /**
     * @param $command
     * @param array $params
     * @return Command
     */
    public static function createCommand($command, array $params)
    {
        $params = array_merge([
            $command => static::getCollectionName(),
        ], $params);

        if ($command === 'aggregate' && !array_key_exists('cursor', $params)) {
            $params['cursor'] = new \stdClass();
        }

        return new Command($params);
    }

    /**
     * @return mixed
     */
    public static function getCollectionName()
    {
        return @explode('.', static::COLLECTION_NAMESPACE)[1];
    }

    /**
     * @return mixed
     */
    public static function getDbName()
    {
        return explode('.', static::COLLECTION_NAMESPACE)[0];
    }

    /**
     * @param Command $command
     * @return \MongoDB\Driver\Cursor
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public static function raw(Command $command)
    {
        return Manager::command(static::getDbName(), $command);
    }

    /**
     * @param $id
     * @return self
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public static function findById($id)
    {
        $result = static::find(['_id' => new ObjectId($id)]);
        return array_shift($result);
    }

    /**
     * @param array $params
     * @param array $sort
     * @param array $options
     * @return array
     */
    public static function find(array $params = [], array $sort = [], array $options = [])
    {
        $res = [];
        if (!empty($sort)) {
            $options['sort'] = $sort;
        }
        $data = Manager::query(static::COLLECTION_NAMESPACE, $params, $options);
        foreach ($data as $item) {
            $res[] = new static((array)$item);
        }
        return $res;
    }

    /**
     * Delete all models
     * @param array $conditions
     */
    public static function deleteAll(array $conditions = [])
    {
        Manager::delete(static::COLLECTION_NAMESPACE, $conditions);
    }

    /**
     * Mass update
     * @param array $condition
     * @param array $fields
     * @return WriteResult
     */
    public static function updateAll(array $condition, array $fields)
    {
        return Manager::update(
            static::COLLECTION_NAMESPACE,
            $condition,
            ['$set' => $fields],
            [
                'upsert' => true,
                'multi' => true,
            ]
        );
    }

    /**
     * @return \MongoId|null
     */
    public function getMongoId()
    {
        return $this->id ? new \MongoId($this->id) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        // MongoDB\BSON\ObjectId плохо конвертируется через ArrayHelper. Вызывает бесконечную рекурсию
        if (array_key_exists('_id', $config) && is_object($config['_id'])) {
            $config['_id'] = (string)$config['_id'];
        }

        $config = ArrayHelper::toArray($config);
        $this->mutate($config);
        foreach ($config as $key => $val) {
            if (!property_exists($this, $key)) {
                unset($config[$key]);
            }
        }
        parent::__construct($config);
    }

    /**
     * @return array
     */
    public function getInputMutators()
    {
        return [
            new MongoIdMutator(),
        ];
    }

    /**
     * mutate input attributes
     * @param array $data
     */
    public function mutate(array &$data)
    {
        foreach ($this->getInputMutators() as $mutator) {
            $mutator->mutate($data);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load($data, $formName = null)
    {
        $this->mutate($data);
        return parent::load($data, $formName);
    }

    /**
     * @param array $fields
     * @return bool
     */
    public function save(array $fields = [])
    {
        if (!$this->validate() || $this->hasErrors()) {
            return false;
        }
        $data = $this->toArray($fields);
        if (isset($this->id)) {
            $res = $this->update($data);
            return $res instanceof WriteResult && ($res->getInsertedCount() || $res->getMatchedCount() || $res->getModifiedCount());
        } else {
            $res = $this->insert($data);
            return $res instanceof WriteResult && ($res->getInsertedCount() || $res->getModifiedCount());
        }
    }

    private function update(&$data)
    {
        $id = @$data['_id'] ?: @$data['id'];
        if (empty($id)) {
            return $this->insert($data);
        }
        $exists = static::findById($id);
        if (empty($exists)) {
            return $this->insert($data);
        }
        return Manager::update(
            static::COLLECTION_NAMESPACE,
            ['_id' => new ObjectId($this->id)],
            ['$set' => $data],
            ['upsert' => true]
        );
    }

    /**
     * @param $data
     * @return WriteResult
     */
    private function insert(&$data)
    {
        $res = Manager::insert(static::COLLECTION_NAMESPACE, $data);
        $this->id = $data[0]['id'];
        return $res;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        if (!isset($this->id)) {
            return false;
        }
        $result = Manager::delete(static::COLLECTION_NAMESPACE, ['_id' => new ObjectId($this->id)]);
        return $result instanceof WriteResult && (bool)$result->getDeletedCount();
    }
}