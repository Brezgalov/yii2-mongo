<?php

namespace app\modules\info\models\mongo;

use MongoDB\BSON\ObjectId;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\Query;
use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\WriteConcern;

class Manager
{
    /**
     * @param array $ids
     * @return array
     */
    public static function convertToObjectIds(array $ids)
    {
        $res = [];
        foreach ($ids as $id) {
            $res[] = new ObjectId($id);
        }
        return $res;
    }

    /**
     * создание менаджера
     * @return \MongoDB\Driver\Manager
     */
    public static function create()
    {
        return new \MongoDB\Driver\Manager('mongodb://@localhost:27017/slots-api-test', [
            "username" => "root",
            "password" => "root",
        ]);
    }

    /**
     * @param $namespace
     * @param Command $command
     * @return \MongoDB\Driver\Cursor
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public static function command($namespace, Command $command)
    {
        return self::create()->executeCommand($namespace, $command);
    }

    /**
     * @param $namespace
     * @param array $params
     * @param array $options
     * @return array
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public static function query($namespace, array $params, array $options = [])
    {
        $options['readConcern'] = new ReadConcern();
        return self::create()->executeQuery($namespace, new Query($params, $options))->toArray();
    }

    /**
     * @param $namespace
     * @param array $items
     * @return \MongoDB\Driver\WriteResult
     */
    public static function insert($namespace, array &$items)
    {
        if (empty($items)) {
            return false;
        }
        $bulk = new BulkWrite();
        if (!is_array(@$items[0])) {
            $items = [$items];
        }
        foreach ($items as $i => $item) {
            unset($item['id']);
            $items[$i]['id'] = (string)$bulk->insert($item);
        }
        return self::create()->executeBulkWrite($namespace, $bulk, new WriteConcern(1));
    }

    /**
     * @param $namespace
     * @param array $condition
     * @param array $item
     * @param array $params
     * @return \MongoDB\Driver\WriteResult
     */
    public static function update($namespace, array $condition, array $item, array $params = [])
    {
        if (empty($item)) {
            return;
        }
        $bulk = new BulkWrite();
        $bulk->update($condition, $item, $params);
        return self::create()->executeBulkWrite($namespace, $bulk, new WriteConcern(1));
    }

    /**
     * @param $namespace
     * @param array $conditions
     * @param array $options
     * @return \MongoDB\Driver\WriteResult
     */
    public static function delete($namespace, array $conditions, array $options = [])
    {
        $bulk = new BulkWrite();
        if (!array_key_exists(0, $conditions)) {
            $conditions = [$conditions];
            $options = [$options];
        }
        foreach ($conditions as $i => $condition) {
            $bulk->delete($condition, array_key_exists($i, $options) ? $options[$i] : $options);
        }
        return self::create()->executeBulkWrite($namespace, $bulk, new WriteConcern(1));
    }
}