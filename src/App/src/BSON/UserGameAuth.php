<?php
namespace App\BSON;

class UserGameAuth implements \MongoDB\BSON\Persistable
{
    private $id;

    private $passport_id;

    private $source;

    private $create_date;

    private $update_date;

    private $first_login;

    private $last_login;

    private $status = 1;

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
        $this->first_login = new \MongoDB\BSON\UTCDateTime($msec);
    }

    public function bsonSerialize()
    {
        return [
                '_id'         => $this->id,
                'passport_id'    => $this->passport_id,
                'source'      => $this->source,
                'status'      => $this->status,
                'create_date' => $this->create_date,
                'update_date' => $this->update_date,
                'first_login' => $this->first_login,
                'last_login'  => $this->last_login,
        ];
    }

    public function bsonUnserialize(array $data)
    {
        $this->id = $data['_id'];
        $this->passport_id = $data['passport_id'];
        $this->source = $data['source'];
        $this->status = $data['status'];
        $this->create_date = intval($data['create_date']->__toString()/1000);
        $this->update_date = intval($data['update_date']->__toString()/1000);
        $this->last_login = intval($data['last_login']->__toString()/1000);
        $this->first_login = intval($data['first_login']->__toString()/1000);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getPassportId()
    {
        return $this->passport_id;
    }

    /**
     * @param int $id
     */
    public function setPassportId($passportId)
    {
        $this->passport_id = (int)$passportId;
    }

    /**
     * @return int
     */
    public function getFirstLogin()
    {
        return $this->first_login / 1000;
    }

    /**
     * @param int $billing_anniversary
     */
    public function setFirstLogin($is_int = false)
    {
        $msec = floor(microtime(true) * 1000);
        $this->first_login = new \MongoDB\BSON\UTCDateTime($msec);
        if ($is_int) {
            $this->first_login = intval($this->first_login->__toString()/1000);
        }
    }

    /**
     * @return int
     */
    public function getLastLogin()
    {
        return $this->last_login;
    }

    /**
     * @param int $has_profile_image
     */
    public function setLastLogin($is_int = false)
    {
        $msec = floor(microtime(true) * 1000);
        $this->last_login = new \MongoDB\BSON\UTCDateTime($msec);
        if ($is_int) {
            $this->last_login = intval($this->last_login->__toString()/1000);
        }
    }

    /**
     * @return mixed
     */
    public function getCreateDate()
    {
        if ($this->create_date instanceof \MongoDB\BSON\UTCDateTime) {
            return intval($this->create_date->__toString()/1000);
        }
        return $this->create_date;
    }

    /**
     * @param mixed $time_registered
     */
    public function setCreateDate($is_int = false)
    {
        $msec = floor(microtime(true) * 1000);
        $this->create_date = new \MongoDB\BSON\UTCDateTime($msec);
        if ($is_int) {
            $this->create_date = intval($this->create_date->__toString()/1000);
        }
    }

    /**
     * @return string
     */
    public function getUpdateDate()
    {
        return $this->update_date;
    }

    /**
     * @param string $company_id
     */
    public function setUpdateDate($is_int = false)
    {
        $msec = floor(microtime(true) * 1000);
        $this->update_date = new \MongoDB\BSON\UTCDateTime($msec);
        if ($is_int) {
            $this->update_date = intval($this->update_date->__toString()/1000);
        }
    }

    public function getSource()
    {
        return $this->source;
    }

    public function setSource($source)
    {
        $this->source = $source;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }
}
