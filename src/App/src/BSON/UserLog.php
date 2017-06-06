<?php
namespace App\BSON;

class UserLog implements \MongoDB\BSON\Persistable
{
    const COLLECTION_NAME = "user_logs";

    private $id;//user_id

    private $first_login;

    private $last_login;

    private $first_pay;

    private $last_pay;

    private $cashout_first_pay;

    private $cashout_last_pay;

    private $sms_first_pay;

    private $sms_last_pay;

    private $create_date;

    private $update_date;

    public function __construct($data = [])
    {
        // Get current time in milliseconds since the epoch
        $msec = floor(microtime(true) * 1000);
        $this->create_date = new \MongoDB\BSON\UTCDateTime($msec);
		$this->update_date = new \MongoDB\BSON\UTCDateTime($msec);
    }

    public function bsonSerialize()
    {
        return [
            '_id'         => $this->id,
            'create_date' => $this->create_date,
            'update_date' => $this->update_date,
            'first_login' => $this->first_login,
            'last_login'  => $this->last_login,
            'first_pay' => $this->first_pay,
            'last_pay' => $this->last_pay,
            'cashout_first_pay' => $this->cashout_first_pay,
            'cashout_last_pay' => $this->cashout_last_pay,
            'sms_first_pay' => $this->sms_first_pay,
            'sms_last_pay' => $this->sms_last_pay
        ];
    }

    public function bsonUnserialize(array $data)
    {
        $this->id = $data['_id'];

        if(isset($data['create_date'])) {
            if (is_int($data['create_date'])) {
                $this->create_date = $data['create_date'];
            } else {
                $this->create_date = intval($data['create_date']->__toString() / 1000);
            }
        }

        if(isset($data['update_date'])) {
            if (is_int($data['update_date'])) {
                $this->update_date = $data['update_date'];
            } else {
                $this->update_date = intval($data['update_date']->__toString() / 1000);
            }
        }

        if(isset($data['last_login'])) {
            if (is_int($data['last_login'])) {
                $this->last_login = $data['last_login'];
            } else {
                $this->last_login = intval($data['last_login']->__toString() / 1000);
            }
        }

        if(isset($data['first_login'])) {
            if (is_int($data['first_login'])) {
                $this->first_login = $data['first_login'];
            } else {
                $this->first_login = intval($data['first_login']->__toString() / 1000);
            }
        }else {
            $this->first_login = $this->create_date;
        }

        if(isset($data['last_pay'])) {
            if (is_int($data['last_pay'])) {
                $this->last_pay = $data['last_pay'];
            } else {
                $this->last_pay = intval($data['last_pay']->__toString() / 1000);
            }
        }

        if(isset($data['first_pay'])) {
            if (is_int($data['first_pay'])) {
                $this->first_pay = $data['first_pay'];
            } else {
                $this->first_pay = intval($data['first_pay']->__toString() / 1000);
            }
        }
        // Cashout
        if(isset($data['cashout_last_pay'])) {
            if (is_int($data['cashout_last_pay'])) {
                $this->last_pay = $data['cashout_last_pay'];
            } else {
                $this->last_pay = intval($data['cashout_last_pay']->__toString() / 1000);
            }
        }

        if(isset($data['cashout_first_pay'])) {
            if (is_int($data['cashout_first_pay'])) {
                $this->first_pay = $data['cashout_first_pay'];
            } else {
                $this->first_pay = intval($data['cashout_first_pay']->__toString() / 1000);
            }
        }
        // SMS
        if(isset($data['sms_last_pay'])) {
            if (is_int($data['sms_last_pay'])) {
                $this->last_pay = $data['sms_last_pay'];
            } else {
                $this->last_pay = intval($data['sms_last_pay']->__toString() / 1000);
            }
        }

        if(isset($data['sms_first_pay'])) {
            if (is_int($data['sms_first_pay'])) {
                $this->first_pay = $data['sms_first_pay'];
            } else {
                $this->first_pay = intval($data['sms_first_pay']->__toString() / 1000);
            }
        }
    }

    public function toArray()
    {
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
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param int $id
     */
    public function setUser(array $user)
    {
        $this->user = $user;
    }

    /**
     * @return int
     */
    public function getFirstLogin()
    {
        return $this->first_login;
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

    public function getFirstPay()
    {
        return $this->first_pay;
    }

    public function setFirstPay($date)
    {
        $this->first_pay = $date;
    }

    public function getLastPay()
    {
        return $this->last_pay;
    }

    public function setLastPay($date)
    {
        $this->last_pay = $date;
    }

    public function getCashoutFirstPay()
    {
        return $this->cashout_first_pay;
    }

    public function setCashoutFirstPay($date)
    {
        $this->cashout_first_pay = $date;
    }

    public function getCashoutLastPay()
    {
        return $this->cashout_last_pay;
    }

    public function setCashoutLastPay($date)
    {
        $this->cashout_last_pay = $date;
    }

    public function getSmsFirstPay()
    {
        return $this->sms_first_pay;
    }

    public function setSmsFirstPay($date)
    {
        $this->sms_first_pay = $date;
    }

    public function getSmsLastPay()
    {
        return $this->sms_last_pay;
    }

    public function setSmsLastPay($date)
    {
        $this->sms_last_pay = $date;
    }
}
