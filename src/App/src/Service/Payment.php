<?php
namespace App\Service;

use App\Mapper\Factory;

class Payment extends AbstractService
{
	private $passportService;

    /**
     * Payment constructor.
     * @param $container
     */
	public function __construct($container)
	{
		parent::__construct($container);
		
		$this->mapper = Factory::create(env('PAYMENT_GATEWAY', 'MongoDb_Payment'), $this->config)
										->setServiceManager($this->serviceManager);
		$this->passportService = $container->get('PassportService');
	}

    /**
     * @param $params
     * @param null $primaryPaygate
     * @return array
     */
    public function charge($params, $primaryPaygate = null)
    {
        // verify parameters
        $errCode = $this->validateParams($params, ['username', 'cardNumber', 'cardSerial', 'cardType']);
        if ($errCode !== 1) {
            return ['code' => $errCode];
        }

        $userInfo = $this->passportService->getProfileByUsername($params['username']);
        if(!$userInfo) {
            return ['code' => -3002];
        }

        $user = $userInfo->getUserBasicInfo();

        $params['user'] = $user;

        $gameInfo = $this->getGameInfo($params['client_id']);
        if ($gameInfo) {
            $params['applicationId'] = $gameInfo['code'];
            if(isset($params['amount'])) {
                $params['knb'] = $params['amount'] * $gameInfo['rate'];
            }
            $params['maxTotal'] = isset($gameInfo['gold_limited']) ? $gameInfo['gold_limited'] : env('GOLD_LIMITED', 100000);
            $params['maxTimes'] = isset($gameInfo['times_limited'])? $gameInfo['times_limited'] : env('TIMES_LIMITED', 20);
        }

        if (!$primaryPaygate) {
            $paygate = $this->getPaygateCardListByTime($params['cardType']);
            $primaryPaygate = isset($paygate[0]) ? $paygate[0] : null;
        }

        if (!$primaryPaygate) {
            return ['code' => -9999];
        }

        $rateGold = !is_null($primaryPaygate['rate_gold']) ? $primaryPaygate['rate_gold'] : 0.01;
        $ratePoint = !is_null($primaryPaygate['rate_point']) ? $primaryPaygate['rate_point'] : 0.001;

        $paygateConfigInfo = json_decode($primaryPaygate['partner_info'], true);
        $params['providerName'] = $primaryPaygate['code'];
        $params['transactionId'] = substr(
            strtoupper(uniqid($params['cardType'] . rand(1000, 9999)) . rand(1000, 9999) . rand(1000, 9999)),
            0,
            30
        );
        $params['provider'] = [
            'name' => $primaryPaygate['code'],
            'transaction_id' => null,
            'message' => null,
            'status' => null
        ];
        $transaction = $this->mapper->insertTransaction($params);
        $errCode = (int)$transaction['result'];
        $msg = _t("transaction_fail_and_retry_again");
        if ($errCode === 1) {
            // call gateway API charge
            $params['transactionId'] = $transaction['transaction_id'];
            try {
                $payTransaction = \PaymentApi\Payment::create(
                    ucwords(strtolower($primaryPaygate['code'])),
                    $paygateConfigInfo
                )->charge($params);

            }catch(\Exception $e) {
                $payTransaction = [
                    'code' => $e->getCode(), 'providerMessage' => $e->getMessage(),
                    'providerMessage' => $e->getCode(), 'providerTransactionId' => ''
                ];
            }
            $errCode = $payTransaction['code'];
            $params['provider']['message'] = $payTransaction['providerMessage'];
            $params['provider']['status'] = $payTransaction['providerStatus'];
            $params['provider']['transaction_id'] = $payTransaction['providerTransactionId'];
            if ((int)$errCode === 1) {
                $params = array_merge($params, $payTransaction['result']);
                $params['gold'] = (int)$payTransaction['amount'] * $rateGold;
                $point = (int)(intval($payTransaction['amount']) * $ratePoint);
                $params['point'] = $point;
                $bill = $this->mapper->updateTransaction($params);
                if ($bill['result'] === 1) {
                    $msg = _t("charge_success") . $payTransaction['amount'] . ' VND. ';
                    $return = ['code' => 1, 'result' => [
                            'transactionId' => $transaction['transaction_id'],
                            'amount' => $payTransaction['amount'], 'gold' => $params['gold'], 'msg' => $msg
                        ]
                    ];
                    if ($gameInfo && isset($params['serverId'])) {
                        $paramsToEx = $params;
                        $paramsToEx['amount'] = isset($params['knb'])? $params['knb'] : $params['gold'];
                        $res = $this->exchange($paramsToEx, $gameInfo);
                        $msg .= $res['msg'];
                        $return['result']['msg'] = $msg;
                        if ($res['code'] == 1) {
                            $return['result']['orderId'] = $res['result']['orderId'];
                        }
                    }
                    return $return;
                }else {
                    $errCode = -9999;
                }
            } else {
                $this->mapper->cancelTransaction($params);
                if ($errCode === -9998 && isset($paygate) && isset($paygate[1])) { // switch second paygate
                    return $this->charge($params, $paygate[1]);
                }
            }
        } elseif ($errCode == -2) {
            $errCode = -9999;
            $msg = _t("charge_card_limited");
        }else {
            $errCode = -9999;
        }
        return ['code' => $errCode, 'msg' => $msg];
    }

    /**
     * @param $params
     * @param $userInfo
     * @param null $gameInfo
     * @return array
     */
    public function chargeApple($params, $userInfo, $gameInfo = null)
    {
        $paygate = $this->getPaygateByType('gate', 'apple');
        $paygateConfigInfo = json_decode($paygate['partner_info'], true);

        $params['passportId'] = $userInfo->getId();
        $params['username'] = $userInfo->getUsername();
        $params['transactionIdentifier'] = $params['transactionIdentifier'];
        $params['receipt'] = $params['receipt'];
        $params['applicationId'] = $gameInfo['code'];
        $params['providerName'] = $paygate['code'];

        $transaction = $this->mapper->insertAppleTransaction($params);
        if ($transaction['result'] === 1) {
            $params['transactionId'] = $transaction['transaction_id'];
            $result = \PaymentApi\Payment::create(
                ucwords(strtolower($paygate['code'])),
                $paygateConfigInfo
            )->verifyReceipt($params, $gameInfo);

            if ($result['status'] === 1) {
                // success
                $params['providerTransactionId'] = $result['providerTranId'];
                $params['providerMessage'] = $result['message'];
                $params['providerStatus'] = $result['providerStatus'];
                $params['gold'] = $result['knb'];
                $params['amount'] = $result['amount'];
                $bill = $this->mapper->updateAppleTransaction($params);
                if ($bill['result'] === 1) {
                    return ['code' => 1, 'gold' => $params['gold'],
                            'amount' => $params['amount'], 'transactionId' => $params['transactionId']
                    ];
                }
                return ['code' => $bill['result'], 'msg' => _("transaction_not_found")];
            } else {
                $params['providerTransactionId'] = $params['transactionId'];
                $params['providerMessage'] = $result['message'];
                $params['providerStatus'] = $result['providerStatus'];
                $this->mapper->cancelAppleTransaction($params);
            }
        }
        return ['code' => -9999];
    }

    /**
     * @param $params
     * @param $userInfo
     * @param null $gameInfo
     * @return array
     */
    public function chargeGoogle($params, $userInfo, $gameInfo = null)
    {
        $paygate = $this->getPaygateByType('gate', 'google');
        $paygateConfigInfo = json_decode($paygate['partner_info'], true);

        $params['passportId'] = $userInfo->getId();
        $params['username'] = $userInfo->getUsername();
        $params['applicationId'] = $gameInfo['code'];
        $params['providerName'] = $paygate['code'];

        $result = \PaymentApi\Payment::create(
            ucwords(strtolower($paygate['code'])),
            $paygateConfigInfo
        )->verifyReceipt($params, $gameInfo);

        $params['transactionIdentifier'] = isset($result['providerTranId'])? $result['providerTranId'] :"";
        $params['amount'] = isset($result['amount']) ? $result['amount'] : 0;
        $params['gold'] = isset($result['knb'])? $result['knb'] : 0;
        $params['provider_transaction_id'] = isset($result['providerTranId'])? $result['providerTranId'] :"";
        $params['provider_message'] = isset($result['message'])? $result['message'] : "";
        $params['provider_status'] = isset($result['providerStatus'])? $result['providerStatus'] : "";
        $params['status'] = $result['status'];

        $transaction = $this->mapper->insertGoogleTransaction($params);
        if ($transaction['result'] === 1) {
            return ['code' => 1, 'amount' => $params['amount'],
                    'gold' => $params['gold'], 'transactionId' => $transaction['transaction_id']
            ];
        }
        return ['code' => -9999];
    }

    /**
     * @param $params
     * @param null $primaryPaygate
     * @return array
     */
    public function chargeCardByAgent($params, $primaryPaygate = null)
    {
        // verify parameters
        $errCode = $this->validateParams($params, ['agentTransactionId', 'cardNumber', 'cardSerial', 'cardType']);
        if ($errCode !== 1) {
            return ['code' => $errCode];
        }

        $gameInfo = $this->getGameInfo($params['client_id']);
        $gameId = $gameInfo['id'];

        if (!$primaryPaygate) {
            $paygate = $this->getCardServiceInfo($gameId, $params['cardType']);
            $primaryPaygate = isset($paygate[0]) ? $paygate[0] : null;
        }

        if (!$primaryPaygate) {
            return ['code' => -9999];
        }

        $paygateConfigInfo = json_decode($primaryPaygate['partner_info'], true);
        $params['providerName'] = $primaryPaygate['channel'];
        $params['transactionId'] = substr(
            strtoupper(uniqid($params['cardType'] . rand(1000, 9999)) . rand(1000, 9999) . rand(1000, 9999)),
            0,
            30
        );
        $params['agentId'] = strtolower($params['client_id']);

        $transaction = $this->mapper->insertTransactionServiceCard($params);
        $errCode = $transaction['result'];
        $msg = _t("transaction_fail_and_retry_again");
        $amount = null;
        if ($errCode === 1) {
            // call gateway API charge
            $params['transactionId'] = $transaction['transaction_id'];
            $payTransaction = \PaymentApi\Payment::create(
                ucwords(strtolower($primaryPaygate['channel'])),
                $paygateConfigInfo
            )->charge($params);

            $errCode = $payTransaction['code'];
            $params = array_merge($params, $payTransaction);
            $params['providerMessage'] = $payTransaction['msg'];
            if ($errCode === 1) {
                $bill = $this->mapper->updateTransactionServiceCard($params);
                if ($bill['result'] === 1) {
                    $amount = $params['amount'];
                    $errCode = 1;
                    $msg = _t("charge_success") . ' ' . $payTransaction['amount'] . ' VND.';
                } else {
                    $errCode = -3;
                }
            } else {
                $msg = $payTransaction['msg'];
                $this->mapper->cancelTransactionServiceCard($params);
                if ($errCode === -9998 && isset($paygate) && isset($paygate[1])) { // switch second paygate
                    return $this->chargeCardByAgent($params, $paygate[1]);
                }
            }
        } elseif ($errCode == -2) {
            $msg = _t("charge_card_limited");
        } elseif ($errCode == -3) {
            $msg = _t("duplicated_agent_transaction_id");
        }

        if ($errCode === 1) {
            return ['code' => $errCode, 'msg' => $msg, 'result' => ['transaction_id' => $params['transactionId'],
                'amount' => $amount, 'agentTransactionId' => $params['agentTransactionId']]];
        } else {
            return ['code' => $errCode, 'result' => null, 'msg' => $msg];
        }
    }

    /**
     * @param $params
     * @param $provider
     * @param $config
     * @return array
     */
  	public function chargeSMS($params, $provider)
  	{
        $config = $this->config;
  		if($provider == '1pay') {
  		    $smsType = $config['partner'][$provider]['type'];
  			$secret = $config['partner'][$provider][$smsType]['secret'];
  			//"access_key":"c4gbr23cbvh824l66w03","command":"kh","mo_message":"KH nguyenanbadaogmailcom","msisdn":"84902639325","request_id":"8x98|523424|84902639325","request_time":"2016-01-30T09:28:35Z","short_code":"8298","signature":"72a70708d294f9462127ba9fc4244ffe93c223da235335e86b3a2aa297955000"
  			// SMSPlus
            if($smsType == 'smsplus') {
                $arParams['access_key'] = isset($params['access_key']) ? $params['access_key'] : 'no_access_key';
                $arParams['command_code'] = isset($params['command_code']) ? $params['command_code'] : 'no_command';
                $arParams['mo_message'] = isset($params['mo_message']) ? $params['mo_message'] : 'no_mo_message';
                $arParams['msisdn'] = isset($params['msisdn']) ? $params['msisdn'] : 'no_msisdn';
                $arParams['request_id'] = isset($params['request_id']) ? $params['request_id'] : 'no_request_id';
                $arParams['request_time'] = isset($params['request_time']) ? $params['request_time'] : 'no_request_time';
                $arParams['amount'] = isset($params['amount']) ? $params['amount'] : 0;
                $arParams['signature'] = isset($params['signature']) ? $params['signature'] : 'no_signature';
                $arParams['error_code'] = isset($params['error_code']) ? $params['error_code'] : 'no_error_code';
                $arParams['error_message'] = isset($params['error_message']) ? $params['error_message'] : 'no_error_message';

                if (isset($params['telco']) && $params['telco'] == 'vtm') {
                    //access_key=$access_key&amount=$amount&command_code=$command_code&mo_message=$mo_message&msisdn=$msisdn&telco=$telco
                    $dataSign = "access_key=" . $arParams['access_key'] . "&amount=" . $arParams['amount'] . "&command_code="
                        . $arParams['command_code'] . "&mo_message=" . $arParams['mo_message'] . "&msisdn="
                        . $arParams['msisdn'] . "&telco=" . $arParams['telco'];
                    $signature = hash_hmac("sha256", $dataSign, $secret);
                    if ($signature != $arParams['signature']) {
                        return ['status' => 0, 'sms' => '', 'type' => 'text'];
                    }
                    return ['status' => 1, 'sms' => 'Chu ky khong hop le!', 'type' => 'text'];
                }

                $dataSign = "access_key=" . $arParams['access_key'] . "&amount=" . $arParams['amount'] . "&command_code="
                    . $arParams['command_code'] . "&error_access_keycode=" . $arParams['error_code'] . "&error_message="
                    . $arParams['error_message'] . "&mo_message=" . $arParams['mo_message'] . "&msisdn="
                    . $arParams['msisdn'] . "&request_id=" . $arParams['request_id'] . "&request_time=" . $arParams['request_time'];
                $signature = hash_hmac("sha256", $dataSign, $secret);
                if ($signature != $arParams['signature']) {
                    return ['status' => 0, 'sms' => 'Chu ky khong hop le!', 'type' => 'text'];
                }

                $mo = explode(" ", $arParams['mo_message']);
                end($mo);
                $username = strtolower($mo[key($mo)]);
                $phone = "0" . substr($arParams['msisdn'], 2);
                $shortCode = 9029;
            }else {
                return ['status' => 0, 'sms' => 'Partner not found', 'type' => 'text'];
            }
  		}else {
            return ['status' => 0, 'sms' => 'Partner not found', 'type' => 'text'];
        }

  		$transactionId = substr(
  				strtoupper(uniqid($shortCode . rand(1000, 9999)) . rand(1000, 9999) . rand(1000, 9999)),
  				0,
  				30
  		);

  		$data = [
  			'transactionId' => $transactionId,
  			'username' => $username,
  			'passportId' => '',
  			'phoneNumber' => $phone,
  			'shortCode' => $shortCode,//$arParams['short_code'],
  			'command' => $arParams['command_code'],
  			'time' => $arParams['request_time'],
  			'amount' => $arParams['amount'],
  			'providerTransactionId' => $arParams['request_id'],
  			'providerName' => $provider	,
  			'providerMessage' => $arParams['error_message'],
  			'providerStatus' => 1,
  			'appId' => null
  		];

        $userInfo = $this->passportService->getProfileByUsername($username);
        if(!$userInfo) {
            return ['status' => 0, 'msg' => 'Giao dich khong thanh cong. Vui long thu lai!'];
        }
        $data['user'] = $userInfo->getUserBasicInfo();
        $data['provider'] = [
            'name' => $provider, 'status' => $data['providerStatus'],
            'message' => $data['providerMessage'], 'transaction_id' => $data['providerTransactionId']
        ];

  		$data['gold'] = (int)$arParams['amount'] * $config['smsRate'][$shortCode]['rateGold'];
  		$result = $this->mapper->insertSMSTransaction($data);
  		if($result['code'] == 1) {
  		    $result['status'] = 1;
            // call Game API to exchange gold here
            if($data['appId']) {
                $partnerConfig = $this->config['partner'][strtolower($data['appId'])];
                $gParams = [
                    'appId' => strtoupper($data['appId']),
                    'transaction_id' => $result['transaction_id'],
                    'username' => $username,
                    'amount' => $data['amount'],
                ];
                $gParams['sign'] = md5($gParams['appId'].$gParams['transaction_id'].$username.$gParams['amount'].$partnerConfig['secret_key']);
                try {
                    $httpClient = new \Zend\Http\Client($partnerConfig['_GAME_URL'], $this->httpConfig);
                    $httpClient->setMethod('POST');
                    $httpClient->setParameterPost($gParams);
                    $res = $httpClient->send();
                    $body = $res->getBody();
                    $ret = json_decode($body, true);
                }catch (\Exception $e) {
                    $subject = "Game Server Error";
                    $this->serviceManager->get('mailService')->sendAlertEmail($subject, $e);
                }
            }
        }
        $result['type'] = 'text';
        return $result;
  	}

    /**
     * @param $params
     * @return array
     */
    public function exchange($params)
    {
        $gameInfo = $this->getGameInfo($params['client_id']);
        $params['applicationId'] = $gameInfo['code'];
        $params['knb'] = $params['amount'];
        $params['amount'] = intval($params['knb']/$gameInfo['rate']);
        $params['maxTotal'] = isset($gameInfo['gold_limited']) ? intval($gameInfo['gold_limited']) : 100000;
        $params['maxTimes'] = isset($gameInfo['times_limited'])? intval($gameInfo['times_limited']) : 20;
        $transactionId = substr(
            strtoupper(uniqid('EX' . $params['applicationId'] . rand(1000, 9999)) . rand(1000, 9999)),
            0,
            20
        );
        $params['transactionId'] = $transactionId;

        if ($params['amount'] > $this->mapper->getBalance($params['passportId'])) {
            return ['code' => -4015, 'msg' => _t('not_enough_exu')];// amount > balance
        }
        $msg = _t("transaction_fail_and_retry_again");

        $isRole = $this->verifyRole($params, $gameInfo);

        if ((int)$isRole !== 1) {
            return ['code' => -4016, 'msg' => _t('invalid_rolename')];
        } // role name not found
        $result = $this->mapper->insertExchangeTransaction($params);
        $status = $result['result'];
        if ((int)$status === 1) {
            $result = $this->addGold($params, $gameInfo);
            $status = $result['status'];
            $params['detail'] = $result['detail'];
            $params['providerMessage'] = $result['message'];
            $params['providerStatus'] = $result['status'];
            $msg = $result['status'];
            $ret = $this->mapper->updateExchangeTransaction($params);
            if ((int)$ret['result'] === 1) {
                $this->updateLog($params['passportId'], $params['applicationId']);
                return ['code' => 1, 'result' => [
                        'orderId' => $ret['transaction_id'],
                        'uid' => $params['passportId'], 'username' => $params['username'],
                        'amount' => $params['amount'],
                        'msg' => _t('exchange_success') . $params['knb']. ' ' . $gameInfo['currency']
                    ]
                ];
            }else {
                $status = '';
            }
        } elseif ($status == -2) {
            $status = -4017;
            $msg = _t("transaction_exists");
        } elseif ($status == -3) {
            $status = -4015;
            $msg = _t('not_enough_exu');
        } elseif ($status == -4) {
            $msg = sprintf(_t('limit_exu_per_day'), $params['maxTotal']);
        } elseif ($status == -5) {
            $msg = sprintf(_t('limit_times_per_day'), $params['maxTimes']);
        } else {
            $status = -9999;
        }
        return ['code' => $status, 'msg' => $msg];
    }

    /**
     * @param $data
     * @param $gameInfo
     * @return array
     */
    private function addGold($data, $gameInfo)
    {
        $metaData = json_decode($gameInfo['meta_data'], true);
        $payLink = $metaData['pay_link'];
        $key = $metaData['provider_key'];
        $time = time();
        $method = 'GET';
        $requestLink = null;
        $infoArr = [
                'time' => $time,
                'orderId' => $data['transactionId'],
                'money' => $data['knb'],
                'ext' => isset($data['extension']) ? $data['extension'] :"",
                'userId' => $data['passportId'],
                'username' => $data['username']
        ];

        switch ($gameInfo['code']) {
            case 'SM0':
                $type = 'SSR';
                $signature = md5(
                    $data['username'] . $time . $type . $data['transactionId'] . $data['amount'] . $data['knb'] . $key
                );
                $requestLink = sprintf(
                    $payLink,
                    $data['username'],
                    $time,
                    $type,
                    $data['transactionId'],
                    $data['serverId'],
                    $data['amount'],
                    $data['knb'],
                    $signature
                );
                break;
        }

        $return  = [
                'status' => 0,
                'message' => _t("transaction_fail_and_retry_again"),
                'detail' => $requestLink
        ];

        if ($requestLink) {
            try {
                $client = new \Zend\Http\Client($requestLink, $this->httpConfig);
                $client->setMethod($method);
                if ($method == 'POST') {
                    $client->setParameterPost($infoArr);
                }
                $response = $client->send();
                $result = $response->getBody();

                if (is_string($result) && is_array(json_decode($result, true))) {
                    $res = json_decode($result, true);
                    if (isset($res['status']) && $res['status'] == 200) {
                        $return['status'] = 1;
                        $return['message'] = 'success';
                    } else {
                        $return['status'] = $res['code'];
                        $return['message'] = $res['code'] . " [" . json_encode($res['data']) . "]";
                    }
                } else {
                    $return['status'] = trim($result);
                    $return['message'] = trim($result);
                }
            } catch (\Exception $e) {
                $subject = $e->getMessage();
                $this->serviceManager->get('mailService')->sendAlertEmail($subject, $e);
            }
        }
        return $return;
    }

    /**
     * @param $params
     * @return array
     */
    public function getBalance($params)
    {
        $errCode = $this->validateParams($params, ['username']);
        if ($errCode !== 1) {
            return ['code' => $errCode];
        }
        $user = $this->passportService->getProfileByUsername($params['username']);
        $balance = $user->getBalance();
        return ['code' => 1, 'result' => ['balance' => $balance]];
    }

    /**
     * @param $params
     * @return mixed
     */
    public function promotion($params)
    {
		$result = $this->mapper->insertPromotion($params);
		return $result;
    }

    /**
     * @param $params
     * @return mixed
     */
    public function updateMatch($params)
    {
    	$winUser = $this->passportService->getProfileByUsername($params['winner_username']);
    	$loserUser = $this->passportService->getProfileByUsername($params['loser_username']);
    	if(!$winUser || !$loserUser) {
    	    return ['code' => -3002];
        }
    	$params['winner'] = $winUser->getUserBasicInfo();
    	$params['loser'] = $loserUser->getUserBasicInfo();
		$result = $this->mapper->insertMatch($params);
		return $result;
    }

    /**
     * @param $params
     * @return mixed
     */
    public function buyCard($params)
    {
        $errCode = $this->validateParams($params, ['username', 'cardValue', 'cardType']);
        if ($errCode !== 1) {
            return ['code' => $errCode];
        }
    	$result = $this->mapper->buyCard($params);
    	return $result;
    }

    /**
     * @param $params
     * @return mixed
     */
    public function recheck($params)
    {
        $result = $this->mapper->recheckTransaction($params);
        return $result;
    }
}
