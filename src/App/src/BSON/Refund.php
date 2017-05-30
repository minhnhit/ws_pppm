<?php
namespace App\BSON;

class Refund implements \MongoDB\BSON\Persistable
{
    private $id;

    private $transaction_id;

    private $type;// charge type: card | atm | sms

    private $user = [
        'id' => null,
        'username' => null,
        'source' => null
    ];

    private $gold;

    private $status;

    private $create_date;

    private $update_date;

    public function __construct($data = [])
    {
        if(isset($data['id'])) {
        	$this->id = $data['id'];
        }else {
        	$this->id = new \MongoDB\BSON\ObjectID;
        }

        // Get current time in milliseconds since the epoch
        $msec = floor(microtime(true) * 1000);
        $this->create_date = new \MongoDB\BSON\UTCDateTime($msec);
        $this->update_date = new \MongoDB\BSON\UTCDateTime($msec);
    }

    public function bsonSerialize()
    {
        return [
            '_id'         => $this->id,
            'transaction_id'=> $this->transaction_id,
            'type'        => $this->type,
            'user'        => $this->user,
            'gold'        => $this->gold,
            'status'      => $this->status,
            'create_date' => $this->create_date,
            'update_date' => $this->update_date
        ];
    }

    public function bsonUnserialize(array $data)
    {
        $this->id = $data['_id'];
        $this->user = (array)$data['user'];
        $this->type = $data['type'];
        $this->transaction_id = $data['transaction_id'];
        $this->gold = $data['gold'];
        $this->status = $data['status'];
        if (is_int($data['create_date'])) {
            $this->create_date = $data['create_date'];
        } else {
            $this->create_date = intval($data['create_date']->__toString()/1000);
        }

        if (is_int($data['update_date'])) {
            $this->update_date = $data['update_date'];
        } else {
            $this->update_date = intval($data['update_date']->__toString()/1000);
        }
    }
}
