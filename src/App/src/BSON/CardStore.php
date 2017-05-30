<?php
namespace App\BSON;

class CardStore implements \MongoDB\BSON\Persistable
{
	const COLLECTION_NAME = "card_stores";
	
    private $id;

    private $card_pin;

    private $card_serial;

    private $card_type;

    private $card_value;

    private $user = [
        'id' => null,
        'username' => null
    ];

    private $gold; // buy card (card_value * rate)

    private $expired_date;

    private $status = 1;// 1: available; -1: used

    private $created_date;

    private $updated_date;

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
            'card_pin'    => $this->card_pin,
            'card_serial' => $this->card_serial,
            'card_type'   => $this->card_type,
            'card_value'  => $this->card_value,
            'user'        => $this->user,
            'gold'        => $this->gold,
            'expired_date'=> $this->expired_date,
            'status'      => $this->status,
            'created_date' => $this->created_date,
            'updated_date' => $this->updated_date
        ];
    }

    public function bsonUnserialize(array $data)
    {
        $this->id = $data['_id'];
        $this->card_pin = $data['card_pin'];
        $this->card_serial = $data['card_serial'];
        $this->card_type = $data['card_type'];
        $this->card_value = $data['card_value'];
        $this->user = $data['user'];
        $this->gold = $data['gold'];
        $this->expired_date = $data['expired_date'];
        $this->status = $data['status'];
        if (is_int($data['created_date'])) {
            $this->created_date = $data['created_date'];
        } else {
            $this->created_date = intval($data['created_date']->__toString()/1000);
        }

        if (is_int($data['updated_date'])) {
            $this->updated_date = $data['updated_date'];
        } else {
            $this->updated_date = intval($data['updated_date']->__toString()/1000);
        }
    }
    
    public function getCreatedDate()
    {
    	return $this->created_date;
    }
    
    public function setCreatedDate($date)
    {
    	$this->created_date = $date;
    }
    
    public function getUpdatedDate()
    {
    	return $this->updated_date;
    }
    
    public function setUpdatedDate($date)
    {
    	$this->updated_date = $date;
    }
}
