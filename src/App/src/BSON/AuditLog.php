<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 6/7/2017
 * Time: 2:00 PM
 */

namespace App\BSON;


class AuditLog implements \MongoDB\BSON\Persistable
{
    private $id;

    private $action;

    private $originalData;

    private $data;

    private $create_date;

    private $update_date;

    public function bsonUnserialize(array $data)
    {
        // TODO: Implement bsonUnserialize() method.
    }

    public function bsonSerialize()
    {
        // TODO: Implement bsonSerialize() method.
    }

}