<?php
namespace App\Mapper\MongoDb;

use App\Mapper\AbstractGateway;
use App\BSON\Match;
use App\BSON\CardStore;
use App\BSON\Promotion;
use App\BSON\Card;
use App\BSON\Sms;
use App\BSON\Exchange;

class PaymentGateway extends AbstractGateway
{
    private $db;

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
     * @return \MongoDB\Database
     */
    protected function getDb()
    {
    	$conn = $this->getConnection();
    	if($conn) {
    		$this->db = $conn->selectDatabase(env('MONGO_DB_PAYMENT_SOURCE', 'payment'));
    	}

    	return $this->db;
    }

    protected function setDb($dbname)
    {
        $conn = $this->getConnection();
        if($conn) {
            $this->db = $conn->selectDatabase($dbname);
        }
    }

    /**
     * @param array $data
     */
    public function insertTransaction($data)
    {
    	$card = new Card();
    	$card->setUser($data['user']);
    	$card->setCardPin($data['cardNumber']);
    	$card->setCardSerial($data['cardSerial']);
    	$card->setCardType($data['cardType']);
    	$card->setProvider($data['provider']);

        $btype = isset($data['ctype']) ? $data['ctype'] : 'silver';
        if (strtolower($btype) === 'silver') {
            $collectionName = Card::COLLECTION_NAME;
        }else {
            $collectionName = Card::CASHOUT_COLLECTION_NAME;
        }

    	try {
    		$col = $this->getDb()->selectCollection($collectionName);
    		$res = $col->insertOne($card);
    		if ($res->getInsertedId()) {
    			$card->setId($res->getInsertedId());

    			return ['result' => 1, 'transaction_id' => $res->getInsertedId()->__toString()];
    		}
    	}catch(\Exception $e) {
    		$subject = "System Error: MongoDB Exception";
    		$this->getMailService()->sendAlertEmail($subject, $e);
    	}

    	return ['result' => -1];
    }

    /**
     * @param $data
     * @return array
     */
    public function updateTransaction($data)
    {
        $btype = isset($data['ctype']) ? $data['ctype'] : 'silver';
        if (strtolower($btype) === 'silver') {
            $collectionName = Card::COLLECTION_NAME;
        }else {
            $collectionName = Card::CASHOUT_COLLECTION_NAME;
        }

        $msec = floor(microtime(true) * 1000);
    	try {
    		$col = $this->getDb()->selectCollection($collectionName);
    		$trans = $col->findOneAndUpdate(
    			[
    				'_id' => new \MongoDB\BSON\ObjectID($data['transactionId']),
    				'status' => 0
    			],
    			[
   					'$set' => [
						'amount' => (int)$data['amount'],
    					'gold' => (int)$data['gold'],
   						'provider' => $data['provider'],
    					'status' => 1,
    					'update_date' => new \MongoDB\BSON\UTCDateTime($msec)
    				]
    			],
    			[
    				'returnDocument' => \MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER
    			]
    		);
    	
    		if($trans) {
    		    // + balance
                $this->getServiceManager()->get('PassportService')
                    ->addGold($data['user']['id'], ['gold' => (int)$data['gold'], 'point' => (int)$data['point']]);
    			return ['code' => 1, 'result' => (array)$trans];
	    	}
    	}catch(\Exception $e) {
    		$subject = "System Error: MongoDB Exception";
    		$this->getMailService()->sendAlertEmail($subject, $e);
    	}
    	return ['code' => -1];
    }

    /**
     * @param $data
     * @return array
     */
    public function cancelTransaction($data)
    {
        $btype = isset($data['ctype']) ? $data['ctype'] : 'silver';
        if (strtolower($btype) === 'silver') {
            $collectionName = Card::COLLECTION_NAME;
        }else {
            $collectionName = Card::CASHOUT_COLLECTION_NAME;
        }

        $msec = floor(microtime(true) * 1000);
    	try {
    		$col = $this->getDb()->selectCollection($collectionName);
    		$trans = $col->findOneAndUpdate(
                [
                    '_id' => new \MongoDB\BSON\ObjectID($data['transactionId']),
                    'status' => 0
                ],
                [
                    '$set' => [
                        'provider' => $data['provider'],
                        'status' => -1,
                        'update_date' => new \MongoDB\BSON\UTCDateTime($msec)
                    ]
                ],
                [
                    'returnDocument' => \MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER
                ]
            );
    		
    		if($trans) {
    			return ['code' => 1, 'result' => (array)$trans];
    		}
    	}catch(\Exception $e) {
    		$subject = "System Error: MongoDB Exception";
    		$this->getMailService()->sendAlertEmail($subject, $e);
    	}
    }
    
    public function insertExchangeTransaction($data)
    {
    	$exchange = new Exchange();
    	$exchange->setUser($data['user']);
    	$exchange->setAmount($data['amount']);
    	$exchange->setGold($data['gold']);
    	$exchange->setServerId($data['serverId']);
    	$exchange->setId($data['ip']);
    	
    	try {
    		$col = $this->getDb()->selectCollection(Exchange::COLLECTION_NAME);
    		$res = $col->insertOne($exchange);
            if ($res->getInsertedId()) {
                $exchange->setId($res->getInsertedId());
    		}
    	}catch(\Exception $e) {
    		$subject = "System Error: MongoDB Exception";
    		$this->getMailService()->sendAlertEmail($subject, $e);
    	}
    }

    /**
     * @param $data
     * @return array
     */
    public function updateExchangeTransaction($data)
    {
        $msec = floor(microtime(true) * 1000);
    	try {
    		$col = $this->getDb()->selectCollection(Exchange::COLLECTION_NAME);
    		$trans = $col->findOneAndUpdate(
    				[
    					'_id' => new \MongoDB\BSON\ObjectID($data['transactionId']),
    					'status' => 0
    				],
    				[
    					'$set' => [
    						'provider' => $data['provider'],
    						'status' => $data['status'],
    						'update_date' => new \MongoDB\BSON\UTCDateTime($msec)
    					]
    				],
    				[
    					'returnDocument' => \MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER
    				]
    				);
    		
    		if($trans) {
    			if($data['status'] == 1) {
    				// - balance
                    $this->getServiceManager()->get('PassportService')
                        ->addGold($data['user']['id'], ['gold' => -$data['gold']]);
    				$this->cloneExchangeTransaction($data['transactionId'], $data['appId']);
    			}
    			return ['code' => 1, 'result' => (array)$trans];
    		}
    	}catch(\Exception $e) {
    		$subject = "System Error: MongoDB Exception";
    		$this->getMailService()->sendAlertEmail($subject, $e);
    	}
    }

    /**
     * @param $transactionId
     * @param $appId
     */
    public function cloneExchangeTransaction($transactionId, $appId)
    {
    	try {
    		$col = $this->getDb()->selectCollection(Exchange::COLLECTION_NAME);
    		$trans = $col->findOne(
    				[
   						'_id' => new \MongoDB\BSON\ObjectID($transactionId),
    				]
    			);
    		if($trans) {
    			$coll = $this->getDb()->selectCollection(strtolower($appId) . '_' . Exchange::COLLECTION_NAME);
	    		$coll->insertOne($trans);
    		}
    	}catch(\Exception $e) {
    		$subject = "System Error: MongoDB Exception";
    		$this->getMailService()->sendAlertEmail($subject, $e);
    	}
    }
    
    public function insertSMSTransaction($data)
    {
    	$sms = new Sms();
    	$sms->setUser($data['user']);
    	$sms->setShortCode($data['shortCode']);
    	$sms->setCommand($data['command']);
    	$sms->setAmount($data['amount']);
    	$sms->setGold($data['gold']);
    	$sms->setPhoneNumber($data['phoneNumber']);
    	$sms->setProvider($data['provider']);
    	$sms->setAppId($data['appId']);
    	
    	try {
    		$col = $this->getDb()->selectCollection(Sms::COLLECTION_NAME);
    		$res = $col->insertOne($sms);
    		if($res) {
    			// + balance here
                $this->getServiceManager()->get('PassportService')
                     ->addGold($data['user']['id'], ['gold' => (int)$data['gold']]);
                return ['code' => 1, 'msg' => 'Ban da nap thanh cong ' . $data['amount'],
                        'transaction_id' => $res->getInsertedId()
                    ];
    		}
    	}catch(\Exception $e) {
    		$subject = "System Error: MongoDB Exception";
    		$this->getMailService()->sendAlertEmail($subject, $e);
    	}
    	return ['code' => -1, 'msg' => 'Giao dich khong thanh cong. Vui long thu lai!'];
    }

    /**
     * @param $data
     * @return array
     */
    public function insertMatch($data)
    {
        $fee = $data['gold'] * $this->config['partner'][$data['client_id']]["rateMatch"];
    	$match = new Match();
    	$match->setMatchId($data['matchId']);
    	$match->setWinner($data['winner']);
    	$match->setLoser($data['loser']);
    	$match->setGold((int)$data['gold']);
    	$match->setFee((int)$fee);
    	$match->setStatus(1);

        $data['status'] = isset($data['status'])? : 1;
    	
    	try {
	    	$col = $this->getDb()->selectCollection(Match::COLLECTION_NAME);
            $gold = $data['gold'];
	    	if($data['status'] == 1) {

            }else { //$data['status'] == 2: deuce
	    	    $gold = $fee / 2;
                // check winner enough gold
                $r1 = $this->getServiceManager()->get('PassportService')
                    ->getMapper()->getBalance([
                        'username' => $data['winner']['username'],
                        'balance.gold' => ['$gte' => (int)$gold]
                    ]);
                if($r1['code'] != 1) {
                    return ['code' => -4015];
                }
            }
            // check loser enough gold
            $r2 = $this->getServiceManager()->get('PassportService')
                        ->getMapper()->getBalance([
                            'username' => $data['loser']['username'],
                            'balance.gold' => ['$gte' => (int)$gold]
                        ]);

            if($r2['code'] != 1) {
                return ['code' => -4015];
            }
	    	$ret = $col->findOne(['match_id' => $data['matchId']]);
	    	if($ret) {
	    	    return ['code' => -4018];
            }

	    	$res = $col->insertOne($match);
	    	if($res) {
                if($data['status'] == 1) {
                    // + gold (gold - fee)
                    $incGold = $gold - $fee;
                    $winnerBalance = $this->getServiceManager()->get('PassportService')
                        ->addGold($data['winner']['id'], ['gold' => (int)$incGold]);
                }else {
                    // - gold
                    $winnerBalance = $this->getServiceManager()->get('PassportService')
                        ->addGold($data['winner']['id'], ['gold' => (int)-$gold]);
                }
                // - gold
                $loserBalance = $this->getServiceManager()->get('PassportService')
                    ->addGold($data['loser']['id'], ['gold' => (int)-$gold]);

	    	    return ['code' => 1, 'winner_username' => $data['winner']['username'],
                        'loser_username' => $data['loser']['username'],
                        'winner_balance' => $winnerBalance,
                        'loser_balance' => $loserBalance
                    ];
            }
    	}catch(\Exception $e) {
    		$subject = "System Error: MongoDB Exception";
    		$this->getMailService()->sendAlertEmail($subject, $e);
    	}
    	return ['code' => -9999];
    }
    
    public function insertPromotion($data)
    {
        $userInfo = $this->getServiceManager()->get('PassportService')
                    ->getProfileByUsername($data['username']);

        if(!$userInfo) {
            return ['code' => -3002];
        }
        $data['user'] = $userInfo->getUserBasicInfo();

    	$promotion = new Promotion();
    	$promotion->setUser($data['user']);
    	$promotion->setPromotionId($data['promotionId']);
    	$promotion->setCode($data['promotionCode']);
    	$promotion->setGold($data['gold']);

    	try {
            // check duplicate promotion_id
	    	$col = $this->getDb()->selectCollection(Promotion::COLLECTION_NAME);
	    	$r2 = $col->findOne(['promotion_id' => $data['promotionId']]);
	    	if($r2) {
	    	    return ['code' => -4017];
            }

	    	$res = $col->insertOne($promotion);
            if ($res->getInsertedId()) {
                // increment gold
                $userBlanace = $this->getServiceManager()->get('PassportService')
                    ->addGold($data['user']['id'], ['gold' => (int)$data['gold']]);
            }
	    	return ['code' => 1, 'result' => ['balance' => $userBlanace]];
    	}catch(\Exception $e) {
    		$subject = "System Error: MongoDB Exception";
    		$this->getMailService()->sendAlertEmail($subject, $e);
    	}
        return ['code' => -9999];
    }

    /**
     * @param $data
     * @return array
     */
    public function buyCard($data)
    {
        $userInfo = $this->getServiceManager()->get('PassportService')
            ->getProfileByUsername($data['username']);

        if(!$userInfo) {
            return ['code' => -3002];
        }
        $user = $userInfo->getUserBasicInfo();

        $gold = $data['cardValue'] + $data['cardValue'] * $this->config['partner'][$data['client_id']]["rateBuyCard"];
        $msec = floor(microtime(true) * 1000);
    	$col = $this->getDb()->selectCollection(CardStore::COLLECTION_NAME);
    	try {
            $r1 = $this->getServiceManager()->get('PassportService')
                    ->getMapper()->getBalance([
                        'username' => $data['username'],
                        'balance.gold' => ['$gte' => (int)$gold]
                    ]);
            if($r1['code'] != 1) {
                return ['code' => -4015];
            }

    		$cardInfo = $col->findOneAndUpdate(
    				[
    					'card_type' => $data['cardType'],
    					'card_value' => (int)$data['cardValue'],
    					'status' => 1
    				],
    				[
    					'$set' => [
    						'user' => $user,
    						'gold' => (int)$gold,
    						'status' => -1,
    						'update_date' => new \MongoDB\BSON\UTCDateTime($msec)
    					]
    				],
    				[
    					'projection' => [ 'card_pin' => 1, 'card_serial' => 1, 
    							'card_type' => 1,'expired_date' => 1
    					],
    					'returnDocument' => \MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER
    				]
    		);
    		
    		if($cardInfo) {
    			// minus user balance here
                $userBalance = $this->getServiceManager()->get('PassportService')
                    ->addGold($user['id'], ['gold' => (int)-$gold]);
                $cardInfo['balance'] = $userBalance;
                $cardInfo['username'] = $data['username'];
    			return ['code' => 1, 'result' => (array)$cardInfo];
    		}
    		return ['code' => -4003];//
    	} catch (\Exception $e) {
    		$subject = "System Error: MongoDB Exception";
    		$this->getMailService()->sendAlertEmail($subject, $e);
    	}
    	
    	return ['code' => -9999];
    }

    public function recheckTransaction($data)
    {
        $bal = [
            'gold' => 0, 'silver' => 0, 'point' => 0
        ];

        $btype = isset($data['ctype']) ? $data['ctype'] : 'silver';
        if (strtolower($btype) === 'silver') {
            $collectionName = Card::COLLECTION_NAME;
        }else {
            $collectionName = Card::CASHOUT_COLLECTION_NAME;
        }
        $col = $this->getDb()->selectCollection($collectionName);
        $transactionInfo = $col->findOne(['_id' => new \MongoDB\BSON\ObjectID(strtolower($data['transactionId']))]);
        if($transactionInfo) {
            if($transactionInfo['status'] == 0) {
                $paygate = $this->getServiceManager()->get('PaymentService')
                    ->getPaygateByType($transactionInfo['card_type'], $transactionInfo['provider']['name']);
                $paygateConfigInfo = json_decode($paygate['partner_info'], true);
                $params = ['payload' => $data['transactionId']];
                $rest = \PaymentApi\Payment::create(
                    ucwords(strtolower($paygate['code'])),
                    $paygateConfigInfo
                )->recheck($params);

                if ($rest['code'] == 1) {
                    $rateGold = isset($paygate['rate_gold']) ? $paygate['rate_gold'] : 0.01;
                    $gold = (int)$rest['amount'] * $rateGold;
                    $data['gold'] = $gold;
                    $data['amount'] = $rest['amount'];
                    $data['provider'] = [
                        'transaction_id' => $rest['providerTransactionId'],
                        'name' => $transactionInfo['provider']['name'],
                        'message' => $rest['providerMessage'],
                        'status' => $rest['providerStatus']
                    ];
                    $trans = $this->updateTransaction($data);

                    if ($trans['code'] == 1) {
                        $balanceInfo = $this->getServiceManager()->get('PassportService')
                            ->getBalance(['username' => $transactionInfo['user']['username']]);
                        
                        if ($balanceInfo['code'] == 1) {
                            $bal = $balanceInfo['result']['balance'];
                        }
                        $ret = ['code' => 1, 'result' => [
                            'amount' => $rest['amount'],
                            'balance' => $bal,
                            'transactionId' => $data['transactionId']
                        ]];
                        return $ret;
                    }
                }
            }elseif($transactionInfo['status'] == 1) {
                $balanceInfo = $this->getServiceManager()->get('PassportService')
                    ->getBalance(['username' => $transactionInfo['user']['username']]);

                if ($balanceInfo['code'] == 1) {
                    $bal = $balanceInfo['result']['balance'];
                }
                $ret = ['code' => 1, 'result' => [
                    'amount' => $transactionInfo['amount'],
                    'balance' => $bal,
                    'transactionId' => $data['transactionId']
                ]];
                return $ret;
            }else {
                return ['code' => -4020];
            }
        }else {
            return ['code' => -4019];
        }
        return ['code' => -9999];
    }
}
