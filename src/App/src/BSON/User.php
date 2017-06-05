<?php
namespace App\BSON;

class User implements \MongoDB\BSON\Persistable
{
    const STATUS_RETIRED = -1;

    private $id;

    private $username;

    private $password;

    private $fullname;

    private $address;

    private $city;

    private $birthday;

    private $source;

    private $agent;

    private $sex = 3;

    private $mobile;

    private $email;

    /**
     * @var
     *
     */
    private $identityNumber;

    private $oauth = [
        'google' => [],
        'facebook' => [],
        'twitter' => [],
        'yahoo' => []
    ];

    private $balance = [
        'gold' => 0,
        'point' => 0,
        'silver' => 0
    ];

    private $verification_code;

    private $create_date;

    private $update_date;

    private $first_login;

    private $last_login;

    private $status = 1; // 1:active|-1:banned|2:channeling_not_change_pass

    private $first_pay;

    private $last_pay;

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
            'username'    => $this->username,
            'password'    => $this->password,
            'fullname'    => $this->fullname,
            'address'     => $this->address,
            'city'        => $this->city,
            'birthday'    => $this->birthday,
            'source'      => $this->source,
            'agent'       => $this->agent,
            'sex'         => $this->sex,
            'mobile'      => $this->mobile,
            'email'       => $this->email,
            'identityNumber' => $this->identityNumber,
            'oauth'   => $this->oauth,
            'balance' => $this->balance,
            'verification_code' => $this->verification_code,
            'status'      => $this->status,
            'create_date' => $this->create_date,
            'update_date' => $this->update_date,
            'first_login' => $this->first_login,
            'last_login'  => $this->last_login,
            'first_pay' => $this->first_pay,
            'last_pay' => $this->last_pay
        ];
    }

    public function bsonUnserialize(array $data)
    {
        $this->id = $data['_id'];
        $this->username = $data['username'];
        $this->password = $data['password'];
        $this->fullname = $data['fullname'];
        $this->address = $data['address'];
        $this->city = $data['city'];
        if (isset($data['birthday'])) {
            $this->birthday  = intval($data['birthday']->__toString()/1000);
        }
        $this->source = $data['source'];
        $this->agent = $data['agent'];
        $this->sex = $data['sex'];
        $this->mobile = isset($data['mobile'])? $data['mobile'] : null;
        $this->email = isset($data['email']) ? $data['email'] : null;
        $this->identityNumber = $data['identityNumber'];

        if (isset($data['oauth'])) {
            $this->oauth = (array)$data['oauth'];
        }

        $this->balance = $data['balance'];
        $this->status = $data['status'];
        $this->verification_code = isset($data['verification_code'])? : null;
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
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param int $id
     */
    public function setUsername($username)
    {
        $this->username = (string)$username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @param string $email
     */
    public function setEmail($email_address)
    {
        $this->email = $email_address;
    }

    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getFullname()
    {
        return $this->fullname;
    }

    /**
     * @param string $first_name
     */
    public function setFullname($fullname)
    {
        $this->fullname = $fullname;
    }

    /**
     * @return string
     */
    public function getIdentityNumber()
    {
        return $this->identityNumber;
    }

    /**
     * @param string $last_name
     */
    public function setIdentityNumber($identityNumber)
    {
        $this->identityNumber = $identityNumber;
    }

    /**
     * Get role.
     *
     * @return array
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * Add a role to the user.
     *
     * @param Role $role
     *
     * @return void
     */
    public function setAgent($agent)
    {
        $this->agent = $agent;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getBirthday()
    {
        if (is_string($this->birthday)) {
            $this->birthday = strtotime($this->birthday);
        }
        return $this->birthday ? date('d-m-Y', $this->birthday) : null;
    }

    /**
     * @param string $country
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }

    /**
     * @return string
     */
    public function getSex()
    {
        return isset($this->sex)? $this->sex : 3;
    }

    /**
     * @param string $zip
     */
    public function setSex($sex)
    {
        $this->sex = $sex;
    }

    /**
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param string $phone
     */
    public function setMobile($phone)
    {
        $this->mobile = $phone;
    }

    public function getOauth()
    {
        return $this->oauth;
    }

    public function setOauth($oauth)
    {
        $this->oauth = $oauth;
    }

    /**
     * @return string
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param string $state
     */
    public function setBalance(array $balance)
    {
        $this->balance = $balance;
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
        //$this->create_date = new \MongoDB\BSON\UTCDateTime($is_int);
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
        //$this->update_date = new \MongoDB\BSON\UTCDateTime($is_int);

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

    public function getUserBasicInfo()
    {
        if($this->id instanceof \MongoDB\BSON\ObjectID) {
            $id = $this->id->__toString();
        }else {
            $id = $this->id;
        }
        return [
            'id' => $id,
            'username' => $this->username,
            'source' => $this->source
        ];
    }
}
