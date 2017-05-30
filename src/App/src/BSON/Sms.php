<?php
namespace App\BSON;

class Sms implements \MongoDB\BSON\Persistable
{
	const COLLECTION_NAME = 'sms_charge';
	
    private $id;

    private $user = [
        'id' => null,
        'username' => null
    ];

    private $phone_number;

    private $short_code;

    private $command;

    private $amount = 0;

    private $gold = 0;

    private $provider = [
        'name' => null,
        'transaction_id' => null,
        'message' => null,
        'status' => null
    ];

    private $appId = null;

    private $status = 1;

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
            'user'        => $this->user,
            'phone_number'=> $this->phone_number,
            'short_code'  => $this->short_code,
            'command'     => $this->command,
            'amount'      => $this->amount,
            'gold'        => $this->gold,
            'provider'    => $this->provider,
            'appId'       => $this->appId,
            'status'      => $this->status,
            'create_date' => $this->create_date,
            'update_date' => $this->update_date,
        ];
    }

    public function bsonUnserialize(array $data)
    {
        $this->id = $data['_id'];
        $this->user = (array)$data['user'];
        $this->phone_number = $data['phone_number'];
        $this->short_code = $data['short_code'];
        $this->command = $data['command'];
        $this->amount = $data['amount'];
        $this->gold = $data['gold'];
        $this->provider = (array)$data['provider'];
        $this->appId = $data['appId'];
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
    
    public function getPhoneNumber()
    {
    	return $this->phone_number;
    }
    
    public function setPhoneNumber($phone)
    {
    	$this->phone_number = $phone;
    }
    
    public function getShortCode()
    {
    	return $this->short_code;
    }
    
    public function setShortCode($shortCode)
    {
    	$this->short_code = $shortCode;
    }
    
    public function getCommand()
    {
    	return $this->command;
    }
    
    public function setCommand($command)
    {
    	$this->command = $command;
    }
    
    public function getAmount()
    {
    	return $this->amount;
    }
    
    public function setAmount($amount)
    {
    	$this->amount = $amount;
    }
    
    public function getGold()
    {
    	return $this->gold;
    }
    
    public function setGold($gold)
    {
    	$this->gold = $gold;
    }
    
    public function getProvider()
    {
    	return $this->provider;
    }
    
    public function setProvider(array $provider)
    {
    	$this->provider = $provider;
    }
    
    public function getAppId()
    {
    	return $this->appId;
    }
    
    public function setAppId($appId)
    {
    	$this->appId = $appId;
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
