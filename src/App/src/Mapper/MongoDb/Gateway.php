<?php
namespace App\Mapper\MongoDb;

use MongoDB\Operation\FindOne;
use App\BSON\User;
use App\BSON\UserGameAuth;
use App\Provider\UserProviderInterface;
use App\Mapper\AbstractGateway;

class Gateway extends AbstractGateway implements UserProviderInterface
{
    private $db;

    private $col;

    /**
     * @param bool $master
     * @return \MongoDB\Client|null
     */
    public function getConnection($master = false)
    {
    	$config = $this->config['mongodb'];
    	try {
    		$client = new \MongoDB\Client(
    				$config['uri'],
    				$config['uri_options'],
    				$config['driver_options']
    			);
    		return $client;
    	} catch (\Exception $e) {
    		$subject = "System Error: MongoDB Exception";
    		$this->getMailService()->sendAlertEmail($subject, $e);
    	}
    	return null;
    }

    /**
     * @param $applicationId
     * @return \MongoDB\Collection
     */
    public function getApplicationPassportAuth($applicationId)
    {
    	return $this->getConnection()->selectDatabase(env('MONGO_DB_AUTH_SOURCE', 'ssg_passport'))->selectCollection(
    			strtolower($applicationId) . '_authentication'
    			);
    }

    /**
     * @param $applicationId
     * @param $passportId
     * @return array|null|object
     */
    public function getLoginId($applicationId, $passportId)
    {
    	$col = $this->getApplicationPassportAuth($applicationId);
    	return $col->findOne(['_id' => $passportId], ['login_id']);
    }

    /**
     * @param $db
     */
    public function setDb($db)
    {
        $this->db = $db;
    }

    /**
     * @return \MongoDB\Database
     */
    public function getDb()
    {
    	if(!$this->db) {
    		$this->db = $this->getConnection()->selectDatabase(env('MONGO_DB_AUTH_SOURCE', 'ssg_passport'));
    	}

        return $this->db;
    }

    /**
     * @param \MongoDB\Collection $col
     */
    public function setColection(\MongoDB\Collection $col)
    {
        $this->col = $col;
    }

    /**
     * @return \MongoDB\Collection
     */
    public function getCollection()
    {
    	if(!$this->col) {
    		$this->col = $this->getDb()->selectCollection(env('PASSPORT_COLLECTION_NAME', 'passport'));
    	}

        return $this->col;
    }

    /**
     * @param $username
     * @return array|bool|null|object
     */
    protected function filterUsername($username)
    {
    	try {
	    	$col = $this->getDb()->selectCollection(env('PASSPORT_COLLECTION_IGNORE_NAME', 'user_ignore'));
    		$col->createIndexes([
    				[ 'key' => [ 'username' => -1 ], 'unique' => true ],
    		]);
    		$userInfo = $col->findOne(['username' => $username]);
    		return $userInfo;
    	} catch (\Exception $e) {
    		$subject = "System Error: MongoDB Exception";
    		$this->getMailService()->sendAlertEmail($subject, $e);
    	}

    	return false;
    }

    /**
     * @param array $keys
     */
    private function setIndexes(array $keys)
    {
    	try {
    		$this->getCollection()->createIndexes($keys);
            $this->getCollection()->createIndexes([
				[ 'key' => [ 'username' => -1 ], 'unique' => true ],
    		    [ 'key' => [ 'status' => 1 ] ],
                [ 'key' => ['email' => -1], 'unique' => true,
                    'partialFilterExpression' => [
                        'email' => ['$type' => 2] // string (not null)
                    ]
                ]
          	]);
    	} catch (\Exception $e) {
    		$subject = "System Error: MongoDB Exception";
    		$this->getMailService()->sendAlertEmail($subject, $e);
    	}
    }

    /**
     * @param string $name
     * @return null|integer
     */
    public function getNextSequence($name)
    {
        try {
            $ret = $this->getDb()->selectCollection('counters')
                ->findOneAndUpdate(
                    ['_id' => $name],
                    ['$inc' => ['seq' => 1]],
                    [
                        'returnDocument' => \MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER,
                        'upsert' => true
                    ]
                );
            if(is_array($ret)) {
                return $ret['seq'];
            }

            return $ret->seq;
        } catch (\Exception $e) {
            $subject = "System Error: MongoDB Exception";
            $this->getMailService()->sendAlertEmail($subject, $e);
        }

        return null;
    }

    /**
     * @param array $data
     * @return array
     */
    public function login($data)
    {
        $msec = floor(microtime(true) * 1000);
        try {
            $user = $this->getCollection()->findOneAndUpdate(
                [
                	'username' => $data['username'],
                	'password' => md5($data['password'])
                ],
                [
                	'$set' => [
	                		'last_login' => new \MongoDB\BSON\UTCDateTime($msec),
	                        'update_date' => new \MongoDB\BSON\UTCDateTime($msec)
                        ]
                    ],
                [
                // 							'projection' => [ 'address' => 1 ],
                    'returnDocument' => \MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER
                ]
            );

            if(is_object($user)) {
                $user = (array)$user;
            }

            if (is_array($user)) {
                $u = new User();
                $u->bsonUnserialize($user);
                $user = $u;
            }

            if ($user->getStatus() == User::STATUS_RETIRED) {
            	return ['code' => -3001, 'msg' => 'user_banned'];
            }

            if ($user->getPassword() !== md5($data['password'])) {
            	return ['code' => -3003, 'msg' => 'password_not_match'];
            }

            return $this->generateJWT($user);
        } catch (\Exception $e) {
            $subject = "System Error: MongoDB Exception";
            $this->getMailService()->sendAlertEmail($subject, $e);
        }

        return ['code' => -3002, 'msg' => 'user_not_found'];
    }

    /**
     * @param string $username
     * @return User
     */
    public function findByUsername($username)
    {
        $user = new User();
        try {
            $userInfo = $this->getCollection()->findOne(['username' => $username]);
            if ($userInfo) {
                $user->bsonUnserialize($userInfo);
            }
        } catch (\Exception $e) {
            $subject = "System Error: MongoDB Exception";
            $this->getMailService()->sendAlertEmail($subject, $e);
        }
        return $user;
    }

    /**
     * @param string $email
     * @return User
     */
    public function findByEmail($email)
    {
        $user = new User();
        try {
            $userInfo = $this->getCollection()->findOne(['email' => $email]);
            if (!$userInfo) {
                return null;
            }
            $user->bsonUnserialize($userInfo);
        } catch (\Exception $e) {
            $subject = "System Error: MongoDB Exception";
            $this->getMailService()->sendAlertEmail($subject, $e);
        }
        return $user;
    }

    /**
     * @param $userId
     * @return null|object
     */
    public function getById($userId)
    {
        $user = null;
        try {
            $userInfo = $this->getCollection()->findOne(['_id' => $userId]);
            if ($userInfo) {
                $hydrator = new \Zend\Hydrator\ClassMethods();
                $user = $hydrator->hydrate($userInfo, new User());
            }
        } catch (\Exception $e) {
            $subject = "System Error: MongoDB Exception";
            $this->getMailService()->sendAlertEmail($subject, $e);
        }
        return $user;
    }

    /**
     * @param $data
     * @return array
     */
    public function insertUser($data)
    {
        // filter username
        $uname = $this->filterUsername($data['username']);
        if ($uname) {
            return ['code' => -1004, 'msg' => _t('illegal_username')];
        }

        // check username exists?
        $u = $this->getCollection()->findOne(['username' => $data['username']]);
        if ($u) {
            return ['code' => -3000, 'msg' => _t("username_exists")];
        }

        if(isset($data['email'])) {
            $email = $this->findByEmail($data['email']);
            if($email) {
                return ['code' => -3004, 'msg' => _t("email_exists")];
            }
        }

        $user = new User(['id' => $this->getNextSequence('passport_id')]);
        $user->setUsername($data['username']);
        $user->setPassword(md5($data['password']));
        $source = isset($data['source'])? : null;
        $user->setSource($source);
        if (isset($data['agent'])) {
            $user->setAgent($data['agent']);
        }
        if(isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if(isset($data['mobile'])) {
            $user->setMobile($data['mobile']);
        }
        $user->setFirstLogin();
        $user->setCreateDate();
        $user->setStatus(1);
        try {
            $result = $this->getCollection()->insertOne($user);
            if ($result->getInsertedId()) {
                $user->setId($result->getInsertedId());
            }
        } catch (\Exception $e) {
            $subject = "System Error: MongoDB Exception";
            $this->getMailService()->sendAlertEmail($subject, $e);
            return ['code' => -9999, 'msg' => _t('system_error')];
        }

        return $this->generateJWT($user);
    }

    /**
     * @param array $data
     * @return array
     */
    public function loginOauth($data)
    {
        if(!is_array($data['oauth_id'])) {
            $data['oauth_id'] = explode(",", $data['oauth_id']);
        }

        if(!isset($data['source'])) {
            $data['source'] = null;
        }

        $filter = ['oauth.' . $data['client'] => ['$in' => $data['oauth_id']]];

        $msec = floor(microtime(true) * 1000);
        // check username exists?
        try {
            $u = $this->getCollection()->findOneAndUpdate(
                $filter,
                [ '$set' => [
                        'last_login' => new \MongoDB\BSON\UTCDateTime($msec),
                        'update_date' => new \MongoDB\BSON\UTCDateTime($msec)
                    ]
                ],
                ['returnDocument' => \MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER]
            );
            if ($u) {
            	return $this->generateJWT($u);
            }
        } catch (\Exception $e) {
            $subject = "System Error: MongoDB Exception";
            $this->getMailService()->sendAlertEmail($subject, $e);
        }

        if(!isset($data['username'])) {
            $data['username'] = $data['client']  . time();
        }else {
            // check user exists?
            $u = $this->findByUsername($data['username']);
            if($u) {
                return ['code' => -3000];
            }
        }

        $oauth = [strtolower($data['client']) => $data['oauth_id']];

        $user = new User(['id' => $this->getNextSequence('passport_id')]);
        $user->setUsername($data['username']);
        $user->setOauth($oauth);
        $user->setSource($data['source']);
        if (isset($data['agent'])) {
            $user->setAgent($data['agent']);
        }
        $user->setFirstLogin();
        $user->setCreateDate();
        $user->setStatus(2);
        try {
            $result = $this->getCollection()->insertOne($user);
            if ($result->getInsertedId()) {
                $user->setId($result->getInsertedId());
                return $this->generateJWT($user);
            }
        } catch (\Exception $e) {
            $subject = "System Error: MongoDB Exception";
            $this->getMailService()->sendAlertEmail($subject, $e);
        }
        return ['code' => -3002, 'msg' => _t('user_not_found')];
    }

    public function update($entity)
    {
    }
    /**
     *
     * @param unknown $data
     * @param unknown $currentUser
     * @return number[]|NULL[]
     */
    public function updatePassword($data)
    {
        if(!isset($data['username']) || !isset($data['oldPassword']) ||
            !isset($data['newPassword'])
        ) {
            return ['code' => -1222];
        }

        $currentUser = $this->findByUsername($data['username']);
        if (md5($data['oldPassword']) != $currentUser->getPassword()) {
            return ['code' => -3003, 'msg' => _t("old_password_not_match")];
        }

        $msec = floor(microtime(true) * 1000);
        try {
            $filter = ['_id' => $currentUser->getId(), 'password' => md5($data['oldPassword'])];
            if ($currentUser->getStatus() == 2) {
                unset($filter['password']);
            }
            $user = $this->getCollection()->findOneAndUpdate(
                $filter,
                [ '$set' => [
                        'password' => md5($data['password']),
                        'status' => 1,
                        'update_date' => new \MongoDB\BSON\UTCDateTime($msec)
                    ]
                ],
                [
                   'returnDocument' => \MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER
                ]
            );
            if ($user) {
                if ($currentUser->getStatus() == 2) {
                    $currentUser->setStatus(1);
                }
                $currentUser->setPassword(md5($data['password']));
                return ['code' => 1, 'msg' => _t("change_password_success")];
            }
        } catch (\Exception $e) {
            $subject = "System Error: MongoDB Exception";
            $this->getMailService()->sendAlertEmail($subject, $e);
        }
        return ['code' => -3002, 'msg' => _t('user_not_found')];
    }

    public function updateEmail($data)
    {
        $currentUser = $this->findByUsername($data['username']);
        if(!$currentUser) {
            return ['code' => -3002];
        }

        if($currentUser->getEmail() != $data['email']) {
            // check email exists?
            $email = $this->findByEmail($data['email']);
            if($email) {
                return ['code' => -3004];
            }
        }

        $verification_code = strtoupper(substr(md5(microtime()), 0, 5));

        $msec = floor(microtime(true) * 1000);
        $dataToUpdate = [
            'email' => $data['email'],
            'verification_code' => $verification_code,
            'update_date' => new \MongoDB\BSON\UTCDateTime($msec)
        ];

        $filterWhere = ['_id' => $currentUser->getId()];

        try {
            $user = $this->getCollection()->updateOne(
                $filterWhere,
                [ '$set' => $dataToUpdate]
            );
            if ($user->getMatchedCount() > 0 && $user->getModifiedCount() > 0) {
                // send mail
                $this->getServiceManager()->get('mailService')->sendEmail($data['email'], $currentUser->getUsername(), $verification_code);

                return ['code' => 1, 'msg' => _t("change_email_success")];
            }
        } catch (\Exception $e) {
            $subject = "System Error: MongoDB Exception";
            $this->getMailService()->sendAlertEmail($subject, $e);
        }
        return ['code' => -9999, 'msg' => _t('change_email_fail')];
    }

    public function updateMobile($data)
    {
        $currentUser = $this->getById($data['passportId']);
        if(!$currentUser) {
            return ['code' => -3002];
        }

        $verification_code = strtoupper(substr(md5(microtime()), 0, 5));
        $msec = floor(microtime(true) * 1000);
        $dataToUpdate = [
            'mobile' => $data['mobile'],
            'verification_code' => $verification_code,
            'update_date' => new \MongoDB\BSON\UTCDateTime($msec)
        ];

        $filterWhere = ['_id' => $currentUser->getId()];

        try {
            $user = $this->getCollection()->findOneAndUpdate(
                $filterWhere,
                [ '$set' => $dataToUpdate],
                [
                    'returnDocument' => \MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER
                ]
            );
            if ($user) {
                if ($currentUser->getEmail()) {
                    // send mail
                    $this->getServiceManager()->get('mailService')->sendEmail($currentUser->getEmail(), $currentUser->getUsername(), $verification_code);
                }
                return ['code' => 1, 'msg' => _t("change_mobile_success")];
            } else {
                return ['code' => -3002, 'msg' => _t("user_not_found")];
            }
        } catch (\Exception $e) {
            $subject = "System Error: MongoDB Exception";
            $this->getMailService()->sendAlertEmail($subject, $e);
        }
        return ['code' => -9999, 'msg' => _t('change_mobile_fail')];
    }

    public function updateIdentityNumber($data)
    {
        $currentUser = $this->getById($data['passportId']);
        $mobileInfo = $currentUser->getMobile();
        $data = convertDotToArray($data, "__");
        $data['identityNumber']['status'] = 1;
        $date = floor(strtotime($data['identityNumber']['date']) * 1000);
        $data['identityNumber']['date'] = new \MongoDB\BSON\UTCDateTime($date);

        $msec = floor(microtime(true) * 1000);
        $dataToUpdate = [
            'update_date' => new \MongoDB\BSON\UTCDateTime($msec)
        ];

        $filterWhere = ['_id' => $currentUser->getId()];
        if (isset($data['old_email']) && isset($data['oldIdentityNumber'])) { // update
            $filterWhere['identityNumber.identity_number'] = $data['oldIdentityNumber'];
            $filterWhere['email_address.email'] = $data['old_email'];
            $dataToUpdate['mobile.verification_code'] = strtoupper(substr(md5(microtime()), 0, 5));
            $data['identityNumber']['status'] = -1;
            $mobileInfo['verification_code'] = $dataToUpdate['mobile.verification_code'];
        }

        $dataToUpdate['identityNumber'] = $data['identityNumber'];

        try {
            $user = $this->getCollection()->findOneAndUpdate(
                $filterWhere,
                [ '$set' => $dataToUpdate ],
                [
                        'returnDocument' => \MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER
                    ]
            );
            if ($user) {
                $data['identityNumber']['date'] = date(
                    'd/m/Y',
                    intval($data['identityNumber']['date']->__toString()/1000)
                );
                $currentUser->setIdentityNumber($data['identityNumber']);
                $currentUser->setMobile($mobileInfo);
                return ['code' => 1, 'msg' => _t("change_identity_number_success")];
            }
        } catch (\Exception $e) {
            $subject = "System Error: MongoDB Exception";
            $this->getMailService()->sendAlertEmail($subject, $e);
        }
        return ['code' => -1, 'msg' => _t('change_identity_number_fail')];
    }

    public function changeProfile($data, $currentUser)
    {
        $msec = floor(microtime(true) * 1000);
        $birthday = floor(strtotime($data['birthday']) * 1000);
        try {
            $user = $this->getCollection()->findOneAndUpdate(
                ['_id' => $currentUser->getId()],
                [ '$set' => [
                        'fullname' => $data['fullname'],
                        'address' => $data['address'],
                        'birthday' => new \MongoDB\BSON\UTCDateTime($birthday),
                        'city'  => $data['city'],
                        'sex' => $data['sex'],
                        'update_date' => new \MongoDB\BSON\UTCDateTime($msec)
                    ]
                ],
                [
                    'returnDocument' => \MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER
                ]
            );
            if ($user) {
                $currentUser->setFullname($data['fullname']);
                $currentUser->setAddress($data['address']);
                $currentUser->setCity($data['city']);
                $currentUser->setSex($data['sex']);
                $currentUser->setBirthday($data['birthday']);
                return ['code' => 1, 'msg' => _t("change_profile_success")];
            }

            return ['code' => -3002];
        } catch (\Exception $e) {
            $subject = "System Error: MongoDB Exception";
            $this->getMailService()->sendAlertEmail($subject, $e);
        }
        return ['code' => -9999, 'msg' => _t('change_profile_fail')];
    }

    public function protectAccount($data, $currentUser)
    {
        $status = 3;
        $msec = floor(microtime(true) * 1000);
        try {
            $user = $this->getCollection()->findOneAndUpdate(
                ['_id' => $currentUser->getId(), 'password' => md5($data['old_credential'])],
                [ '$set' => [
                        'status' => $status,
                        'update_date' => new \MongoDB\BSON\UTCDateTime($msec)
                    ]
                    ],
                [
                        'returnDocument' => \MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER
                    ]
            );
            if ($user) {
                $currentUser->setStatus($status);
                return ['code' => 1, 'msg' => _t("protect_account_success")];
            }
        } catch (\Exception $e) {
            $subject = "System Error: MongoDB Exception";
            $this->getMailService()->sendAlertEmail($subject, $e);
        }
        return ['code' => -1, 'msg' => _t('protect_account_fail')];
    }

    public function unlockProtectedAccount($data, $currentUser)
    {
        $msec = floor(microtime(true) * 1000);
        $ftype = ($data['ftype'] == 'email')? "email_address" : $data['ftype'];
        try {
            $user = $this->getCollection()->findOneAndUpdate(
                ['_id' => $currentUser->getId(), $ftype.'.verification_code' => $data['otp']],
                [ '$set' => [
                            'status' => 1,
                            $ftype.'.verification_code' => null,
                            'update_date' => new \MongoDB\BSON\UTCDateTime($msec)
                    ]
                    ],
                [
                            'returnDocument' => \MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER
                    ]
            );
            if ($user) {
                $currentUser->setStatus(1);
                return ['code' => 1, 'msg' => _t("protect_account_success")];
            }
        } catch (\Exception $e) {
            $subject = "System Error: MongoDB Exception";
            $this->getMailService()->sendAlertEmail($subject, $e);
        }
        return ['code' => -1, 'msg' => _t('protect_account_fail')];
    }

    /******************** ACTIVATE ********************************************/

    public function activateEmail($data, $currentUser)
    {
        $currentEmail = $currentUser->getEmailAddress();
        $currentMobileInfo = $currentUser->getMobile();
        $email_address = [
                'email' => $currentEmail['email'],
                'status' => 1,
                'verification_code' => null
        ];

        $msec = floor(microtime(true) * 1000);
        $dataToUpdate = [
                'update_date' => new \MongoDB\BSON\UTCDateTime($msec)
        ];

        $filterWhere = [
                '_id' => $currentUser->getId(),
                'email_address.email' => $currentEmail['email'],
        ];

        if ($currentEmail['status'] == 0) {
            $filterWhere['email_address.verification_code'] = $data['otp'];
            $filterWhere['email_address.status'] = 0;
        } else {
            // update
            $filterWhere['mobile.verification_code'] = $data['otp'];
            $filterWhere['email_address.status'] = -1;
            $currentMobileInfo['verification_code'] = null;
        }

        $dataToUpdate['email_address'] = $email_address;
        $dataToUpdate['mobile'] = $currentMobileInfo;

        try {
            $user = $this->getCollection()->updateOne(
                $filterWhere,
                [ '$set' => $dataToUpdate]
            );

            if ($user->getMatchedCount() > 0 && $user->getModifiedCount() > 0) {
                $currentUser->setEmailAddress($email_address);
                $currentUser->setMobile($currentMobileInfo);
                return ['code' => 1, 'msg' => _t("activate_email_success")];
            }
        } catch (\Exception $e) {
            $subject = "System Error: MongoDB Exception";
            $this->getMailService()->sendAlertEmail($subject, $e);
        }
        return ['code' => -1, 'msg' => _t('activate_email_fail')];
    }

    public function activateMobile($data, $currentUser)
    {
        $currentEmail = $currentUser->getEmailAddress();
        $currentMobile = $currentUser->getMobile();

        $mobileInfo = [
            'mobile' => $currentMobile['mobile'],
            'status' => 1,
            'verification_code' => null
        ];

        $msec = floor(microtime(true) * 1000);
        $dataToUpdate = [
                'update_date' => new \MongoDB\BSON\UTCDateTime($msec)
        ];

        $filterWhere = [
            '_id' => $currentUser->getId(),
            'mobile.mobile' => $currentMobile['mobile'],
        ];

        if ($currentMobile['status'] == -1) { // only verify by mobile code
            $filterWhere['mobile.verification_code'] = $data['otp'];
            $filterWhere['mobile.status'] = -1;
        } else { // verify both email & mobile code
            $filterWhere['mobile.status'] = 0;
            $filterWhere['email_address.verification_code'] = $data['otp_email'];
            $filterWhere['mobile.verification_code'] = $data['otp_mobile'];
            $currentEmail['verification_code'] = null;
// 			$mobileInfo['status'] = -1;
// 			$mobileInfo['verification_code'] = strtoupper(substr(md5(microtime()), 0, 5));
        }

        $dataToUpdate['email_address'] = $currentEmail;
        $dataToUpdate['mobile'] = $mobileInfo;

        try {
            $user = $this->getCollection()->updateOne($filterWhere, ['$set' => $dataToUpdate]);
            if ($user->getMatchedCount() > 0 && $user->getModifiedCount() > 0) {
                $currentUser->setMobile($mobileInfo);
                $currentUser->setEmailAddress($currentEmail);
                return ['code' => 1, 'msg' => _t("activate_mobile_success")];
            }
        } catch (\Exception $e) {
            $subject = "System Error: MongoDB Exception";
            $this->getMailService()->sendAlertEmail($subject, $e);
        }
        return ['code' => -1, 'msg' => _t('activate_mobile_fail')];
    }

    public function activateIdentityNumber($data, $currentUser)
    {
        $identityNumber = $currentUser->getIdentityNumber();
        $mobileInfo = $currentUser->getMobile();

        $date = floor(strtotime($identityNumber['date']) * 1000);
        $identityNumber['date'] = new \MongoDB\BSON\UTCDateTime($date);
        $identityNumber['status'] = 1;

        $msec = floor(microtime(true) * 1000);
        $dataToUpdate = [
                'update_date' => new \MongoDB\BSON\UTCDateTime($msec)
        ];

        $filterWhere = [
                '_id' => $currentUser->getId(),
                'identityNumber.status' => -1,
                'mobile.verification_code' => $data['otp']
        ];

        $mobileInfo['verification_code'] = null;

        $dataToUpdate['identityNumber'] = $identityNumber;
        $dataToUpdate['mobile'] = $mobileInfo;

        try {
            $user = $this->getCollection()->updateOne($filterWhere, ['$set' => $dataToUpdate]);
            if ($user->getMatchedCount() > 0 && $user->getModifiedCount() > 0) {
                $currentUser->setMobile($mobileInfo);
                $identityNumber['date'] = intval($identityNumber['date']->__toString()/1000);
                $currentUser->setIdentityNumber($identityNumber);
                return ['code' => 1, 'msg' => _t("activate_identity_number_success")];
            }
        } catch (\Exception $e) {
            $subject = "System Error: MongoDB Exception";
            $this->getMailService()->sendAlertEmail($subject, $e);
        }
        return ['code' => -1, 'msg' => _t('activate_identity_number_fail')];
    }

    /**************************************************************************/

    /**
     *
     * @param unknown $identity
     * @param unknown $emailOrMobile
     * @param string $type
     * @return boolean
     */
    public function getVerificationCode($identity, $emailOrMobile, $type = 'email')//mobile
    {
        $ctype = $type;
        if ($type == 'email') {
            $ctype = "email_address";
        }
        try {
            $ret = $this->getCollection()->findOne(
                ['username' => $identity, $ctype . '.' . $type => $emailOrMobile],
                ['projection' => [$ctype . '.verification_code' => true]]
            );
            if ($ret) {
                return $ret[$ctype]['verification_code'];
            }
        } catch (\Exception $e) {
            $subject = "System Error: MongoDB Exception";
            $this->getMailService()->sendAlertEmail($subject, $e);
        }
        return false;
    }

    /**
     *
     * @param unknown $username
     * @param string $type
     * @return boolean
     */
    public function forgotPassword($username)
    {
        $code = strtoupper(substr(md5(microtime()), 0, 5));
        try {
            $ret = $this->getCollection()->findOneAndUpdate(
                ['username' => $username],
                [ '$set' => [
                        'verification_code' => $code,
                    ]
                ],
                [
                            // 							'projection' => [ 'address' => 1 ],
                    'returnDocument' => \MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER
                ]
            );
            if ($ret) {
                if(is_object($ret)) {
                    $user = (array)$ret;
                }else {
                    $user = $ret;
                }

                $u = new User();
                $u->bsonUnserialize($user);
                $email = $u->getEmail();
                if ($email) {
                    //send email
                    $this->getServiceManager()->get('mailService')->sendEmail($email, $username, $code);
                }
                return ['code' => 1, 'email' => $email, 'code' => $code];
            }
        } catch (\Exception $e) {
            $subject = "System Error: MongoDB Exception";
            $this->getMailService()->sendAlertEmail($subject, $e);
        }
        return false;
    }

    /**
     *
     * @param unknown $username
     * @param unknown $password
     * @param unknown $code
     * @param string $type
     * @return boolean
     */
    public function resetPassword($data)
    {
        try {
            $user = $this->getCollection()->updateOne(
                ['username' => $data['username'], 'verification_code' => $data['code']],
                ['$set' =>
                    [
                        'password' => md5($data['password']),
                        'verification_code' => null
                    ]
                ]
            );
            if ($user->getMatchedCount() > 0 && $user->getModifiedCount() > 0) {
                return ['code' => 1];
            }
        } catch (\Exception $e) {
            $subject = "System Error: MongoDB Exception";
            $this->getMailService()->sendAlertEmail($subject, $e);
        }

        return ['code' => -3002];
    }

    public function getOtp($ftype, $currentUser)
    {
        $code = strtoupper(substr(md5(microtime()), 0, 5));
        $filterData = ['_id' => $currentUser->getId()];//, 'status' => 3];
        if ($ftype == 'email') {
            $ftype = 'email_address';
            $filterData[$ftype.'.email'] = ['$ne' => null];
        } else {
            $filterData[$ftype.'.mobile'] = ['$ne' => null];
        }
        $filterData['status'] = ['$ne' => -1];
        try {
            $ret = $this->getCollection()->findOneAndUpdate(
                $filterData,
                [ '$set' => [$ftype . '.verification_code' => $code]],
                [
                    'returnDocument' => \MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER
                ]
            );
            if ($ret) {
                if ($ftype == 'email_address') {
                    //send email
                    $emailAddress = $ret->getEmailAddress();
                    $this->getServiceManager()->get('mailService')->sendEmail($emailAddress['email'], $ret->getUsername(), $code);
                }
                return true;
            }
        } catch (\Exception $e) {
            $subject = "System Error: MongoDB Exception";
            $this->getMailService()->sendAlertEmail($subject, $e);
        }
        return false;
    }

    public function getUsernameFromLoginId($gameId, $qid)
    {
        $result = false;
        try {
            $collection = strtolower($gameId) . '_authentication';
            $ret = $this->getDb()->{$collection}->findOne(['login_id' => (int)$qid], ['projection' => ['_id' => true]]);
            if ($ret) {
                $rett = $this->getCollection()->findOne(
                    ['_id' => (int)$ret['_id']],
                    ['projection' => ['username' => true]]
                );
                if ($rett) {
                    $result[$qid] = $rett['username'];
                }
            }
        } catch (\Exception $e) {
            $subject = "System Error: MongoDB Exception";
            $this->getMailService()->sendAlertEmail($subject, $e);
        }
        return $result;
    }

    public function getLoginIds($gameId, $usernameList, $agent = null)
    {
        $result = false;
        try {
            if ($agent && $agent != "game5") {
                $usernameList .=  '_' . $agent;
                $this->col = $this->getDb()->channeling_passport;
            }
            $collection = strtolower($gameId) . '_authentication';
            if (is_array($usernameList)) {
                $ret = $this->col->find(
                    ['username' => ['$in' => $usernameList]],
                    ['projection' => ['_id' => true, 'username' => true]]
                )->toArray();
                foreach ($ret as $id) {
                    $ids[] = (string)$id['_id'];
                    $tmp[$id['_id']] = $id['username'];
                }
                if (isset($ids)) {
                    $ret = $this->getDb()->{$collection}->find(
                        ['_id' => ['$in' => $ids]],
                        ['projection' => ['login_id' => true]]
                    )->toArray();
                    foreach ($ret as $r) {
                        if (isset($tmp[$r['_id']])) {
                            $result[$tmp[$r['_id']]] = $r['login_id'];
                        }
                    }
                }
            } else {
                $user = $this->login($usernameList);
                if ($user) {
                    try {
                        $this->getDb()->{$collection}->createIndexes([
                            [ 'key' => [ 'passport_id' => -1 ], 'unique' => true ],
                        ]);
                    } catch (\Exception $e) {
                        $subject = "System Error: MongoDB Exception";
                        $this->getMailService()->sendAlertEmail($subject, $e);
                    }

                    $msec = floor(microtime(true) * 1000);
                    // check username exists?
                    $filter = ['passport_id' => $user->getId()];
                    $ugameAuth = $this->getDb()->{$collection}->findOneAndUpdate(
                        $filter,
                        [ '$set' => [
                                'last_login' => new \MongoDB\BSON\UTCDateTime($msec),
                                'update_date' => new \MongoDB\BSON\UTCDateTime($msec)
                            ]
                        ],
                        [
                            'returnDocument' => \MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER,
                        ]
                    );
                    if (!$ugameAuth) {
                        $d = [
                            'id' => $this->getNextSequence(strtolower($gameId) . '_login_id'),
                        ];
                        $ugameAuth = new UserGameAuth($d);
                        $ugameAuth->setPassportId($user->getId());
                        $ugameAuth->setSource($agent);
                        $res = $this->getDb()->{$collection}->insertOne($ugameAuth);
                        if ($res->getInsertedId()) {
                            $ugameAuth->setId($res->getInsertedId());
                        }
                    }
                    if ($ugameAuth->getId()) {
                        $result[$user->getUsername()] = $ugameAuth->getId();
                        $d['source'] = $agent;
                        $d['login_id'] = $ugameAuth->getId();
                    }
                }
            }
        } catch (\Exception $e) {
            $subject = "System Error: MongoDB Exception";
            $this->getMailService()->sendAlertEmail($subject, $e);
        }
        return $result;
    }

    public function addGold($uid, array $balance)
    {
        $incData = [];
        foreach($balance as $k => $num) {
            $incData['balance.'.$k] = $num;
        }

        $conditions = [
            '_id' => new \MongoDB\BSON\ObjectID($uid),
        ];

        $msec = floor(microtime(true) * 1000);
        try {
            $balance = $this->getCollection()->findOneAndUpdate(
                $conditions,
                [
                    '$inc' => $incData,
                    '$set' => [
                        'update_date' => new \MongoDB\BSON\UTCDateTime($msec)
                    ]
                ],
                [
                    'projection' => [ 'balance' => 1 ],
                    'returnDocument' => \MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER
                ]
            );
            return $balance;
        } catch (\Exception $e) {
            $subject = "System Error: MongoDB Exception";
            $this->getMailService()->sendAlertEmail($subject, $e);
        }

        $balance = $this->getCollection()->findOne($conditions, ['projection' => [ 'balance' => 1 ]]);
        return $balance;
    }

    public function getBalance(array $conditions)
    {
        try {
            $ret = $this->getCollection()->findOne($conditions, ['projection' => [ 'balance' => 1 ]]);
            if ($ret) {
                return ['code' => 1, 'balance' => $ret];
            }
            return ['code' => -4015];
        } catch (\Exception $e) {
            $subject = "System Error: MongoDB Exception";
            $this->getMailService()->sendAlertEmail($subject, $e);
        }

        return ['code' => -3002, 'msg' => 'user_not_found'];
    }
}
