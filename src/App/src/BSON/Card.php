<?php
namespace App\BSON;

class Card implements \MongoDB\BSON\Persistable
{
	const COLLECTION_NAME = 'card_charge';
	const CASHOUT_COLLECTION_NAME = 'card_cashout_charge';
	
    private $id;

    private $transaction_id;

    private $user = [
        'id' => null,
        'username' => null,
        'source' => null,
        'agent_id' => null
    ];

    private $card_pin;

    private $card_serial;

    private $card_type;

    private $amount = 0;

    private $gold = 0;

    private $provider = [
        'name' => null,
        'transaction_id' => null,
        'message' => null,
        'status' => null
    ];

    private $appId = null;

    private $status = 0;

    private $create_date;

    private $update_date;

    public function __construct($data = [])
    {
       	$this->id = new \MongoDB\BSON\ObjectID;
       	$this->transaction_id = strtoupper($this->id->__toString());

        // Get current time in milliseconds since the epoch
        $msec = floor(microtime(true) * 1000);
        $this->create_date = new \MongoDB\BSON\UTCDateTime($msec);
        $this->update_date = new \MongoDB\BSON\UTCDateTime($msec);
    }

    public function bsonSerialize()
    {
        return [
            '_id'         => $this->id,
            'transaction_id' => $this->transaction_id,
            'user'        => $this->user,
            'card_pin'    => $this->card_pin,
            'card_serial' => $this->card_serial,
            'card_type'   => $this->card_type,
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
        $this->transaction_id = $data['transaction_id'];
        $this->user = (array)$data['user'];
        $this->card_pin = $data['card_pin'];
        $this->card_serial = $data['card_serial'];
        $this->card_type = $data['card_type'];
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
    	return $this->id->__toString();
    }
    
    public function setId($id)
    {
    	$this->id = $id;
    }

    public function getTransactionId()
    {
        return $this->transaction_id;
    }

    public function setTransactionId($transId)
    {
        $this->transaction_id = $transId;
    }
    
    public function getUser()
    {
    	return $this->user;
    }
    
    public function setUser(array $user)
    {
    	$this->user = $user;
    }
    
    public function getCardPin()
    {
    	return $this->card_pin;
    }
    
    public function setCardPin($cardPin)
    {
    	$this->card_pin = $cardPin;
    }
    
    public function getCardSerial()
    {
    	return $this->card_serial;
    }
    
    public function setCardSerial($cardSerial)
    {
    	$this->card_serial = $cardSerial;
    }
    
    public function getCardType()
    {
    	return $this->card_type;
    }
    
    public function setCardType($cardType)
    {
    	$this->card_type = $cardType;
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
