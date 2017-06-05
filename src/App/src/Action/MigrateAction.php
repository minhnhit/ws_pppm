<?php

namespace App\Action;

use App\BSON\Card;
use App\BSON\CardStore;
use App\BSON\Match;
use App\BSON\Promotion;
use App\BSON\Sms;
use App\BSON\User;
use App\BSON\UserLog;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

class MigrateAction implements ServerMiddlewareInterface
{
    private $passportSqlDb;
    private $paySqlDb;
    private $mongoPassportDb;
    private $mongoPayDb;

    public function __construct($passportSqlDb, $paySqlDb, $mongoPassportDb, $mongoPayDb)
    {
        $this->passportSqlDb = $passportSqlDb;
        $this->paySqlDb = $paySqlDb;
        $this->mongoPassportDb = $mongoPassportDb;
        $this->mongoPayDb = $mongoPayDb;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
//        $this->migrateUser();//done
        $this->migrateUserGold();
//        $this->migrateCard(Card::CASHOUT_COLLECTION_NAME);//done
//        $this->migrateCardStore();//done
//        $this->migrateCardBuy();//done
//        $this->migrateMatch();//done
//        $this->migratePromotion();//done
//        $this->migrateSms();//done
        return new JsonResponse(['ack' => time()]);
    }

    private function migrateUser()
    {
        /*
        $this->mongoPassportDb->getMapper()->getCollection()->createIndexes([
            [ 'key' => [ 'username' => -1 ], 'unique' => true ],
            [ 'key' => [ 'source' => -1 ]],
            [ 'key' => [ 'status' => 1 ] ],
            [ 'key' => ['email' => -1], 'unique' => true,
                'partialFilterExpression' => [
                    'email' => ['$type' => 2] // string (not null)
                ]
            ]
        ]);
        */
        $page = 47;//47
        $range = 5000;
        $offset = ($page - 1) * $range;
        $sql = "
            SELECT id,username,password,fullname,birthday,sex,address,city,pp.status,pp.create_date,pp.update_date,source, agent_id,email,oauth_id,channel_id FROM passport AS pp
            LEFT JOIN passport_source AS ps
            ON pp.id = ps.passport_id
            LEFT JOIN passport_email AS pe
            ON pp.id = pe.passport_id
            LEFT JOIN passport_channeling AS pc
            ON pp.id = pc.passport_id
            ORDER BY id ASC
            OFFSET  $offset ROWS 
            FETCH NEXT $range ROWS ONLY 
        ";
        $results = $this->passportSqlDb->query($sql)->execute();
        $ret = [];
        foreach($results as $row) {
            $createDate = strtotime($row['create_date']) * 1000;
            $updateDate = strtotime($row['update_date']) * 1000;
            $user = new User();
            $user->setId($row['id']);
            $user->setUsername($row['username']);
            $user->setPassword($row['password']);
            $user->setSource($row['source']);
            $user->setAgent($row['agent_id']);
            $user->setEmail($row['email']);
            if($row['channel_id'] && $row['oauth_id']) {
                $oauth = [
                    'facebook' => [],
                    'google' => [],
                    'twitter' => []
                ];
                $channel = explode($row['channel_id']."_", $row['oauth_id']);
                $oauth[$row['channel_id']] = [$channel[1]];
                $user->setOauth($oauth);
            }
            $user->setCreateDate($createDate);
            $user->setUpdateDate($updateDate);
            try {
                $result = $this->mongoPassportDb->getMapper()->getCollection()->insertOne($user);
                //var_dump($result);
            } catch (\Exception $e) {
                var_dump($e->getMessage());
                //return ['code' => -9999, 'msg' => _t('system_error')];
            }
//            var_dump($user);die;
        }
        die('success');
    }

    private function migrateUserGold()
    {
        $page = 15;
        $range = 5000;
        $offset = ($page - 1) * $range;
        $sql = "
            SELECT * FROM gold AS cc 
            ORDER BY passport_id ASC
            OFFSET  $offset ROWS 
            FETCH NEXT $range ROWS ONLY 
        ";
        $results = $this->paySqlDb->query($sql)->execute();
        $ret = [];
        foreach($results as $row) {
            try {
                $updateDate = new \MongoDB\BSON\UTCDateTime(strtotime($row['update_date']) * 1000);
                $balance = $this->mongoPassportDb->getMapper()->getCollection()->findOneAndUpdate(
                    ['_id' => (int)$row['passport_id']],
                    [
                        '$set' => [
                            'balance.gold' => (int)$row['balance'],
                            'update_date' => $updateDate
                        ]
                    ],
                    [
                        'projection' => [ 'balance' => 1 ],
                        'returnDocument' => \MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER
                    ]
                );
                //$result = $this->mongoPassportDb->getMapper()->addGold((int)$row['passport_id'], ['gold' => (int)$row['gold']]);
                //var_dump($result);
            } catch (\Exception $e) {
                var_dump($e->getMessage());
                //return ['code' => -9999, 'msg' => _t('system_error')];
            }
//            var_dump($user);die;
        }
        die('success');
    }

    private function migrateCard($collectionName = Card::COLLECTION_NAME)
    {
        // card_charge & card_cashout_charge
        $page = 1;
        $range = 5000;
        $offset = ($page - 1) * $range;
        $sqlCount = "SELECT COUNT(*) AS total FROM card_charge";
        $rets = $this->paySqlDb->query($sqlCount)->execute()->current();
        $total = $rets['total'];
        $sql = "
            SELECT * FROM ".$collectionName." AS cc 
            ORDER BY id ASC
            OFFSET  $offset ROWS 
            FETCH NEXT $range ROWS ONLY 
        ";
        $results = $this->paySqlDb->query($sql)->execute();
        $ret = [];
        foreach($results as $row) {
            $card = new Card();
            $card->setTransactionId($row['transaction_id']);
            $card->setUser([
                'id' => (int)$row['passport_id'], 'username' => $row['username']
            ]);
            $card->setCardPin($row['card_pin']);
            $card->setCardSerial($row['card_serial']);
            $card->setCardType($row['card_type']);
            $card->setAmount((int)$row['amount']);
            $provider = [
                'name' => $row['provider_name'],
                'transaction_id' => $row['provider_transaction_id'],
                'message' => $row['provider_message'],
                'status' => $row['provider_status']
            ];
            $card->setProvider($provider);
            $card->setStatus((int)$row['status']);

            $createDate = new \MongoDB\BSON\UTCDateTime(strtotime($row['create_date']) * 1000);
            $updateDate = new \MongoDB\BSON\UTCDateTime(strtotime($row['update_date']) * 1000);
            $card->setCreateDate($createDate);
            $card->setUpdateDate($updateDate);

            try {
                $col = $this->mongoPayDb->getMapper()->getDb()->selectCollection($collectionName);
                $result = $col->insertOne($card);
            } catch (\Exception $e) {
                var_dump($e->getMessage());die;
            }
        }
        die("done");
    }

    private function migrateCardStore()
    {
        $page = 1;
        $range = 5000;
        $offset = ($page - 1) * $range;
//        $sqlCount = "SELECT COUNT(*) AS total FROM card_store";
//        $rets = $this->paySqlDb->query($sqlCount)->execute()->current();
//        $total = $rets['total'];

        $sql = "
            SELECT * FROM card_store AS cc 
            ORDER BY id ASC
            OFFSET  $offset ROWS 
            FETCH NEXT $range ROWS ONLY 
        ";
        $results = $this->paySqlDb->query($sql)->execute();
        $ret = [];
        foreach($results as $row) {
            $card = new CardStore();
            //$card->setTransactionId($row['transaction_id']);
//            $card->setUser([
//                'id' => $row['passport_id'], 'username' => $row['username']
//            ]);
            $card->setCardPin($row['card_pin']);
            $card->setCardSerial($row['card_serial']);
            $card->setCardType($row['card_type']);
            $card->setCardValue((int)$row['card_value']);
            $card->setExpiredDate($row['expired_date']);
            $card->setStatus((int)$row['status']);

            $createDate = new \MongoDB\BSON\UTCDateTime(strtotime($row['create_date']) * 1000);
            $updateDate = new \MongoDB\BSON\UTCDateTime(strtotime($row['update_date']) * 1000);
            $card->setCreatedDate($createDate);
            $card->setUpdatedDate($updateDate);

            try {
                $col = $this->mongoPayDb->getMapper()->getDb()->selectCollection(CardStore::COLLECTION_NAME);
                $result = $col->insertOne($card);
            } catch (\Exception $e) {
                var_dump($e->getMessage());die;
            }
        }
        die("donee");
    }

    private function migrateCardBuy()
    {
        $page = 1;
        $range = 5000;
        $offset = ($page - 1) * $range;

        $sql = "
            SELECT * FROM card_buy AS cc 
            ORDER BY id ASC
            OFFSET  $offset ROWS 
            FETCH NEXT $range ROWS ONLY 
        ";
        $results = $this->paySqlDb->query($sql)->execute();
        $ret = [];
        foreach($results as $row) {
            $updateDate = new \MongoDB\BSON\UTCDateTime(strtotime($row['update_date']) * 1000);
            try {
                $col = $this->mongoPayDb->getMapper()->getDb()->selectCollection(CardStore::COLLECTION_NAME);
                $cardInfo = $col->findOneAndUpdate(
                    [
                        'card_type' => $row['card_type'],
                        'card_value' => (int)$row['card_value'],
                        'card_pin' => $row['card_pin'],
                        'card_serial' => $row['card_serial'],
                        'status' => -1
                    ],
                    [
                        '$set' => [
                            'transaction_id' => $row['transaction_id'],
                            'user' => ['id' => (int)$row['passport_id'], 'username' => $row['username']],
                            'gold' => (int)$row['gold'],
                            'status' => -1,
                            'update_date' => $updateDate
                        ]
                    ],
                    [
                        'projection' => [ 'card_pin' => 1, 'card_serial' => 1,
                            'card_type' => 1,'expired_date' => 1
                        ],
                        'returnDocument' => \MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER
                    ]
                );
                //var_dump($cardInfo);
            } catch (\Exception $e) {
                var_dump($e->getMessage());die;
            }
        }
        die("donee");
    }

    private function migrateMatch()
    {
        $page = 10;
        $range = 5000;
        $offset = ($page - 1) * $range;

        $sql = "
            SELECT * FROM chess_matches AS cc 
            ORDER BY id ASC
            OFFSET  $offset ROWS 
            FETCH NEXT $range ROWS ONLY 
        ";
        $results = $this->paySqlDb->query($sql)->execute();
        $ret = [];
        foreach($results as $row) {
            $card = new Match();
            $card->setMatchId($row['match_id']);
            $card->setWinner([
                'id' => (int)$row['winner_id'], 'username' => $row['winner_username']
            ]);
            $card->setLoser([
                'id' => (int)$row['loser_id'], 'username' => $row['loser_username']
            ]);

            $card->setGold((int)$row['gold']);
            $card->setFee((int)$row['fee']);
            $card->setStatus((int)$row['status']);

            $createDate = new \MongoDB\BSON\UTCDateTime(strtotime($row['create_date']) * 1000);
            $updateDate = new \MongoDB\BSON\UTCDateTime(strtotime($row['update_date']) * 1000);
            $card->setCreateDate($createDate);
            $card->setUpdateDate($updateDate);

            try {
                $col = $this->mongoPayDb->getMapper()->getDb()->selectCollection(Match::COLLECTION_NAME);
                $result = $col->insertOne($card);
            } catch (\Exception $e) {
                var_dump($e->getMessage());die;
            }
        }
        die('doneee');
    }

    private function migratePromotion()
    {
        $page = 75;
        $range = 1000;
        $offset = ($page - 1) * $range;

        $sql = "
            SELECT * FROM promotion AS cc 
            ORDER BY id ASC
            OFFSET  $offset ROWS 
            FETCH NEXT $range ROWS ONLY 
        ";

        $results = $this->paySqlDb->query($sql)->execute();
        $ret = [];
        foreach($results as $row) {
            $card = new Promotion();
            $username = strtolower($row['username']);
            $user = $this->mongoPassportDb->getMapper()->getCollection()->findOne(['username' => $username]);
            $card->setPromotionId($row['promotion_id']);
            $card->setUser([
                'id' => (int)$user['_id'], 'username' => $row['username']
            ]);
            $card->setCode($row['promotion_code']);
            $card->setGold((int)$row['gold']);
            $card->setStatus((int)$row['status']);

            $createDate = new \MongoDB\BSON\UTCDateTime(strtotime($row['create_date']) * 1000);
            $updateDate = new \MongoDB\BSON\UTCDateTime(strtotime($row['update_date']) * 1000);
            $card->setCreateDate($createDate);
            $card->setUpdateDate($updateDate);

            try {
                $col = $this->mongoPayDb->getMapper()->getDb()->selectCollection(Promotion::COLLECTION_NAME);
                $result = $col->insertOne($card);
            } catch (\Exception $e) {
                //var_dump($e->getMessage());die;
            }
        }
        die('done');
    }

    private function migrateSms()
    {
        $page = 2;
        $range = 5000;
        $offset = ($page - 1) * $range;

        $sql = "
            SELECT * FROM sms_charge AS cc 
            ORDER BY id ASC
            OFFSET  $offset ROWS 
            FETCH NEXT $range ROWS ONLY 
        ";
        $results = $this->paySqlDb->query($sql)->execute();
        $ret = [];
        foreach($results as $row) {
            $card = new Sms();
            $card->setTransactionId($row['transaction_id']);
            $card->setUser([
                'id' => (int)$row['passport_id'], 'username' => $row['username']
            ]);
            $card->setPhoneNumber($row['phone_number']);
            $card->setShortCode($row['short_code']);
            $card->setCommand($row['command']);
            $card->setAmount((int)$row['amount']);
            $card->setGold((int)$row['gold']);
            $provider = [
                'name' => $row['provider_name'],
                'transaction_id' => $row['provider_transaction_id'],
                'message' => $row['provider_message'],
                'status' => $row['provider_status']
            ];
            $card->setProvider($provider);
            $card->setStatus((int)$row['status']);

            $createDate = new \MongoDB\BSON\UTCDateTime(strtotime($row['create_date']) * 1000);
            $updateDate = new \MongoDB\BSON\UTCDateTime(strtotime($row['update_date']) * 1000);
            $card->setCreateDate($createDate);
            $card->setUpdateDate($updateDate);

            try {
                $col = $this->mongoPayDb->getMapper()->getDb()->selectCollection(Sms::COLLECTION_NAME);
                $result = $col->insertOne($card);
            } catch (\Exception $e) {
                var_dump($e->getMessage());die;
            }
        }
        die('done');
    }

    private function migrateUserLog()
    {
        $page = 1;
        $range = 5000;
        $offset = ($page - 1) * $range;
        $sql = "
            SELECT * FROM ssg_login AS cc 
            ORDER BY passport_id ASC
            OFFSET  $offset ROWS 
            FETCH NEXT $range ROWS ONLY 
        ";
        $results = $this->passportSqlDb->query($sql)->execute();
        $ret = [];
        foreach($results as $row) {
            try {
                $firstLogin = new \MongoDB\BSON\UTCDateTime(strtotime($row['first_login']) * 1000);
                $lastLogin = new \MongoDB\BSON\UTCDateTime(strtotime($row['last_login']) * 1000);
                $uLog = new UserLog();
                $uLog->setId((int)$row['passport_id']);
                $uLog->setFirstLogin($firstLogin);
                $uLog->setLastLogin($lastLogin);
                //var_dump($result);
            } catch (\Exception $e) {
                var_dump($e->getMessage());
                //return ['code' => -9999, 'msg' => _t('system_error')];
            }
//            var_dump($user);die;
        }
        die('success');
    }
}
