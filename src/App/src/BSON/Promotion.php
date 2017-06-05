<?php
namespace App\BSON;

class Promotion implements \MongoDB\BSON\Persistable
{
	const COLLECTION_NAME = "promotions";
	
    private $id;

    private $user = [
        'id' => null,
        'username' => null
    ];

    private $promotion_id;

    private $code;

    private $gold;

    private $status = 1;

    private $create_date;

    private $update_date;

    public function __construct($data = [])
    {
        $this->id = new \MongoDB\BSON\ObjectID;

        // Get current time in milliseconds since the epoch
        $msec = floor(microtime(true) * 1000);
        $this->create_date = new \MongoDB\BSON\UTCDateTime($msec);
        $this->update_date = new \MongoDB\BSON\UTCDateTime($msec);
    }

    public function bsonSerialize()
    {
        return [
            '_id'         => $this->id,
            'user'        => $this->user,
            'promotion_id'=> $this->promotion_id,
            'code'        => $this->code,
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
        $this->promotion_id = $data['promotion_id'];
        $this->code = $data['code'];
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
    
    public function getId()
    {
    	return $this->id;
    }
    
    public function setId($id)
    {
    	$this->id = $id;
    }
    
    public function getUser()
    {
    	return $this->user;
    }
    
    public function setUser(array $user)
    {
    	$this->user = $user;
    }

    public function getPromotionId()
    {
        return $this->promotion_id;
    }

    public function setPromotionId($prid)
    {
        $this->promotion_id = $prid;
    }
    
    public function getCode()
    {
    	return $this->code;
    }
    
    public function setCode($code)
    {
    	$this->code = $code;
    }
    
    public function getGold()
    {
    	return $this->gold;
    }
    
    public function setGold($gold)
    {
    	$this->gold = $gold;
    }
    
    public function getStatus()
    {
    	return $this->status;
    }
    
    public function setStatus($status)
    {
    	$this->status = $status;
    }
    
    public function getCreateDate()
    {
    	return $this->create_date;
    }
    
    public function setCreateDate($date)
    {
    	$this->create_date = $date;
    }
    
    public function getUpdateDate()
    {
    	return $this->update_date;
    }
    
    public function setUpdateDate($date)
    {
    	$this->update_date = $date;
    }
}
