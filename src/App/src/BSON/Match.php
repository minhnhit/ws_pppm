<?php
namespace App\BSON;

class Match implements \MongoDB\BSON\Persistable
{
	const COLLECTION_NAME = "matches";
	
    private $id;

    private $match_id;

    private $winner = [
        'id' => null,
        'username' => null
    ];

    private $loser = [
        'id' => null,
        'username' => null
    ];

    private $gold;

    private $fee;

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
            'match_id'    => $this->match_id,
            'winner'      => $this->winner,
            'loser'       => $this->loser,
            'gold'        => $this->gold,
            'fee'         => $this->fee,
            'status'      => $this->status,
            'create_date' => $this->create_date,
            'update_date' => $this->update_date
        ];
    }

    public function bsonUnserialize(array $data)
    {
        $this->id = $data['_id'];
        $this->match_id = $data['match_id'];
        $this->winner = (array)$data['winner'];
        $this->loser = (array)$data['loser'];
        $this->fee = $data['fee'];
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
    
    public function getMatchId()
    {
    	return $this->match_id;
    }
    
    public function setMatchId($matchId)
    {
    	$this->match_id = $matchId;
    }
    
    public function getWinner()
    {
    	return $this->winner;
    }
    
    public function setWinner(array $winner)
    {
    	$this->winner = $winner;
    }
    
    public function getLoser()
    {
    	return $this->loser;
    }
    
    public function setLoser($loser)
    {
    	$this->loser = $loser;
    }
    
    public function getGold()
    {
    	return $this->gold;
    }
    
    public function setGold($gold)
    {
    	$this->gold = $gold;
    }
    
    public function getFee()
    {
    	return $this->fee;
    }
    
    public function setFee($fee)
    {
    	$this->fee = $fee;
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
