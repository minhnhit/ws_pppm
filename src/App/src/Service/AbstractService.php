<?php
namespace App\Service;

abstract class AbstractService
{
	protected $mapper;
	
    protected $serviceManager;

    protected $config;

    protected $httpConfig = [
            'adapter' => 'Zend\Http\Client\Adapter\Curl',
            'sslverifypeer' => false,
            'curloptions' => [
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false,
            ],
            'keepalive' => true,
            'timeout'   => 60
    ];
    
    public function __construct($container)
    {
    	$this->serviceManager = $container;
    	$this->config = $container->get('config');
    }

    /**
     *
     * @param mixed $id_slug_code
     * @return mixed|NULL|unknown[]|NULL|NULL|mixed|unknown[]
     */
    public function getGameInfo($id_slug_code)
    {
    	$gHashkey = "ginf";
    	$hashkey = "gkmap";
    	$redis = $this->serviceManager->get('PredisCache');
    	$gid = $redis->hget($hashkey, $id_slug_code);
    	if ($gid) {
    		$gameInfo = json_decode($redis->hget($gHashkey, $gid), true);
    		if ($gameInfo) {
    			$gameInfo['servers'] = $this->getServerList($gameInfo['id']);
    			return $gameInfo;
    		}
    	}
    	
    	$adapter = $this->serviceManager->get(\Zend\Db\Adapter\Adapter::class);
    	
    	$qi = function ($name) use ($adapter) {
    		return $adapter->platform->quoteIdentifier($name);
    	};
    	
    	$fp = function ($name) use ($adapter) {
    		return $adapter->driver->formatParameterName($name);
    	};
    	$gameInfo = null;
    	try {
    		$statement = $adapter->query(
    				'SELECT * FROM '
    				. $qi('games') . ' AS g'
    				. ' WHERE g.status = 1 AND (g.id = '
    				. $fp('id') . ' OR g.code = ' . $fp('code')
    				. ' OR slug = ' . $fp('slug') . ')'
    				);
    		
    		$results = $statement->execute([
    				'id' => $id_slug_code,
    				'code' => strtoupper($id_slug_code),
    				'slug' => $id_slug_code
    		]);
    		
    		$gameInfo = $results->current();
    		if ($gameInfo) {
    			$redis->hset($hashkey, $id_slug_code, $gameInfo['id']);
    			$gameInfo['servers'] = $this->getServerList($gameInfo['id']);
    		}
    	} catch (\Exception $e) {
    		$subject = "System Error: SQL Exception";
    		$this->serviceManager->get('mailService')->sendAlertEmail($subject, $e);
    		return null;
    	}
    	
    	return $gameInfo;
    }
    
    /**
     *
     * @return mixed[]|unknown[]|NULL|unknown[]|mixed[]
     */
    public function getGameList()
    {
    	$games = [];
    	$hashkey = "ginf";
    	$redis = $this->serviceManager->get('PredisCache');
    	$glist = $redis->hgetall($hashkey);
    	if ($glist) {
    		foreach ($glist as $i => $g) {
    			$r = json_decode($g, true);
    			$servers = $this->getServerList($r['id']);
    			foreach ($servers as $sev) {
    				$r['servers'][$sev['code']] = $sev['name'];
    			}
    			$games[$i] = $r;
    		}
    	}
    	
    	if (count($games) > 0) {
    		return $games;
    	}
    	
    	$adapter = $this->serviceManager->get(\Zend\Db\Adapter\Adapter::class);
    	$qi = function ($name) use ($adapter) {
    		return $adapter->platform->quoteIdentifier($name);
    	};
    	
    	$fp = function ($name) use ($adapter) {
    		return $adapter->driver->formatParameterName($name);
    	};
    	
    	try {
    		$statement = $adapter->query(
    				'SELECT * FROM '
    				. $qi('games')
    				. ' WHERE status = 1 AND is_display = 1'
    				. ' ORDER BY priority'
    				);
    		
    		$results = $statement->execute();
    	} catch (\Exception $e) {
    		$subject = "System Error: SQL Exception";
    		$this->serviceManager->get('mailService')->sendAlertEmail($subject, $e);
    		return null;
    	}
    	foreach ($results as $k => $r) {
    		$redis->hset($hashkey, $r['id'], json_encode($r));
    		$servers = $this->getServerList($r['id']);
    		foreach ($servers as $sev) {
    			$r['servers'][$sev['code']] = $sev['name'];
    		}
    		$games[$k] = $r;
    	}
    	return $games;
    }
    
    /**
     *
     * @param unknown $gameId
     * @return NULL|mixed|unknown[]
     */
    public function getServerList($gameId)
    {
    	$hashkey = "srv";
    	$redis = $this->serviceManager->get('PredisCache');
    	$servers = json_decode($redis->hget($hashkey, $gameId), true);
    	if (!$servers) {
    		$adapter = $this->serviceManager->get(\Zend\Db\Adapter\Adapter::class);
    		$qi = function ($name) use ($adapter) {
    			return $adapter->platform->quoteIdentifier($name);
    		};
    		
    		$fp = function ($name) use ($adapter) {
    			return $adapter->driver->formatParameterName($name);
    		};
    		$servers = [];
    		try {
    			$statement = $adapter->query(
    					'SELECT code, name FROM '
    					. $qi('servers')
    					. ' WHERE status = 1 AND game_id = ' . $fp('gameId')
    					. ' ORDER BY id DESC'
    					);
    			
    			$results = $statement->execute(['gameId' => $gameId]);
    		} catch (\Exception $e) {
    			$subject = "System Error: SQL Exception";
    			$this->serviceManager->get('mailService')->sendAlertEmail($subject, $e);
    			return null;
    		}
    		foreach ($results as $r) {
    			$servers[] = $r;
    		}
    		$redis->hset($hashkey, $gameId, json_encode($servers));
    	}
    	return $servers;
    }
    
    /**
     *
     * @param unknown $gameId
     * @param unknown $id_code
     * @return mixed|unknown
     */
    public function getServerInfo($gameId, $id_code)
    {
    	$hkey = "srvinf:".$gameId;
    	$redis = $this->serviceManager->get('PredisCache');
    	$server = json_decode($redis->hget($hkey, $id_code), true);
    	if ($server) {
    		return $server;
    	}
    	
    	$adapter = $this->serviceManager->get(\Zend\Db\Adapter\Adapter::class);
    	$qi = function ($name) use ($adapter) {
    		return $adapter->platform->quoteIdentifier($name);
    	};
    	$fp = function ($name) use ($adapter) {
    		return $adapter->driver->formatParameterName($name);
    	};
    	
    	$statement = $adapter->query(
    			'SELECT * FROM '
    			. $qi('servers')
    			. ' WHERE status = 1 AND game_id = ' . $fp('gameId') . ' AND (id = ' . $fp('id')
    			. ' OR code = ' . $fp('code') . ')'
    			);
    	
    	$results = $statement->execute(['gameId' => $gameId, 'id' => $id_code,
    			'code' => $id_code
    	]);
    	
    	$row = $results->current();
    	if ($row) {
    		$redis->hset($hkey, $id_code, json_encode($row));
    	}
    	return $row;
    }
    
    public function getPaygateByType($type, $id_slug_code = null, $isSingle = false)
    {
    	$hkey = "pbtype";
    	$rkey = $type;
    	if ($isSingle) {
    		$hkey .= ":" . $type;
    		$rkey = "sil";
    	}
    	if ($id_slug_code) {
    		$hkey .= ":" . $type;
    		$rkey = $id_slug_code;
    	}
    	
    	$redis = $this->serviceManager->get('PredisCache');
    	$rows = json_decode($redis->hget($hkey, $rkey), true);
    	if ($rows) {
    		return $rows;
    	}
    	
    	$adapter = $this->serviceManager->get(\Zend\Db\Adapter\Adapter::class);
    	
    	$qi = function ($name) use ($adapter) {
    		return $adapter->platform->quoteIdentifier($name);
    	};
    	
    	$fp = function ($name) use ($adapter) {
    		return $adapter->driver->formatParameterName($name);
    	};
    	
    	if ($id_slug_code) {
    		$statement = $adapter->query(
    				'SELECT * FROM '
    				. $qi('paygates') . ' AS g'
    				. ' WHERE g.status = 1 AND (g.id = '
    				. $fp('id') . ' OR g.code = ' . $fp('code')
    				. ' OR slug = ' . $fp('slug') . ')'
    				);
    		
    		$results = $statement->execute([
    				'id' => $id_slug_code,
    				'code' => strtoupper($id_slug_code),
    				'slug' => $id_slug_code
    		]);
    	} else {
    		$statement = $adapter->query(
    				'SELECT * FROM '
    				. $qi('paygates')
    				. ' WHERE status = 1 AND type = ' . $fp('type')
    				);
    		$results = $statement->execute(['type' => $type]);
    	}
    	
    	$rows = null;
    	if ($isSingle || $id_slug_code) {
    		$rows = $results->current();
    	} else {
    		foreach ($results as $r) {
    			$rows[] = $r;
    		}
    	}
    	
    	if ($rows) {
    		$redis->hset($hkey, $rkey, json_encode($rows));
    	}
    	
    	return $rows;
    }
    
    /**
     *
     * @param unknown $type
     * @param string $single
     * @param string $tbl
     * @return mixed|unknown|unknown[]
     */
    public function getCardList($type = null, $single = false, $tbl = 'card_types')
    {
    	$rkey = 'clist:cct';
    	if ($tbl == 'channel_mobile_card_types') {
    		$rkey = 'clist:cmct';
    	}
    	
    	$redis = $this->serviceManager->get('PredisCache');
    	$clist = json_decode($redis->get($rkey), true);
    	
    	if ($clist) {
    		return $clist;
    	}
    	$adapter = $this->serviceManager->get(\Zend\Db\Adapter\Adapter::class);
    	
    	$qi = function ($name) use ($adapter) {
    		return $adapter->platform->quoteIdentifier($name);
    	};
    	
    	$fp = function ($name) use ($adapter) {
    		return $adapter->driver->formatParameterName($name);
    	};
    	if (!$type) {
    		$statement = $adapter->query(
    				'SELECT * FROM '
    				. $qi($tbl) . ' AS cct'
    				. ' LEFT JOIN ' . $qi('cards') . ' AS c'
    				. ' ON c.id = cct.cid'
    				. ' WHERE c.status = 1 AND cct.status = 1'
    				. ' GROUP BY cct.cid, cct.gid'
    				. ' ORDER BY c.priority'
    				);
    		$results = $statement->execute();
    	} else {
    		if (is_array($type)) {
    			$statement = $adapter->query(
    					'SELECT * FROM '
    					. $qi($tbl) . ' AS cct'
    					. ' LEFT JOIN ' . $qi('cards') . ' AS c'
    					. ' ON c.id = cct.cid'
    					. ' WHERE c.status = 1 AND cct.status = 1 AND c.type IN ("' . implode('","', $type) . '")'
    					. ' GROUP BY cct.cid, cct.gid'
    					. ' ORDER BY c.priority'
    					);
    			$results = $statement->execute();
    		} else {
    			$statement = $adapter->query(
    					'SELECT * FROM '
    					. $qi($tbl) . ' AS cct'
    					. ' LEFT JOIN ' . $qi('cards') . ' AS c'
    					. ' ON c.id = cct.cid'
    					. ' WHERE c.status = 1 AND cct.status = 1 AND c.type = ' . $fp('type')
    					. ' GROUP BY cct.cid, cct.gid'
    					. ' ORDER BY c.priority'
    					);
    			$results = $statement->execute(['type' => $type]);
    		}
    	}
    	if ($single) {
    		return $results->current();
    	}
    	$rows = [];
    	foreach ($results as $r) {
    		$rows[] = $r;
    	}
    	$redis->set($rkey, json_encode($rows));
    	return $rows;
    }
    
    /**
     *
     * @param int $cardId
     * @param string $tbl
     * @return mixed|unknown[]
     */
    public function getPaygateCardListByTime($cardType, $tbl = 'card_types')
    {
    	$hkey = "pm:pgate";
    	if ($tbl == 'channel_mobile_card_types') {
    		$hkey .= ':m';
    	}
    	
    	$predis = $this->serviceManager->get('PredisCache');
    	
    	$hour = date('H', time());
    	$nextHour = date('H', strtotime('+1 hour'));
    	
    	$hashData = $predis->hget($hkey, $hour);
    	if ($hashData) {
    		$rows = json_decode($hashData, true);
    		if (isset($rows[strtolower($cardType)])) {
    			return $rows[strtolower($cardType)];
    		}
    	}
    	
    	$adapter = $this->serviceManager->get(\Zend\Db\Adapter\Adapter::class);
    	
    	$qi = function ($name) use ($adapter) {
    		return $adapter->platform->quoteIdentifier($name);
    	};
    	
    	$fp = function ($name) use ($adapter) {
    		return $adapter->driver->formatParameterName($name);
    	};
    	
    	$rows = [];
    	$statement = $adapter->query(
    			'SELECT pg.code, pg.rate_gold, pg.rate_point, pg.discount, pg.partner_info, cct.rate AS card_rate FROM '
    			. $qi($tbl) . ' AS cct'
    			. ' LEFT JOIN ' . $qi('paygates') . ' AS pg'
    			. ' ON cct.gid = pg.id'
    			. ' INNER JOIN ' . $qi('cards') . ' AS c'
    			. ' ON c.id = cct.cid'
    			. ' WHERE cct.status = 1 AND (cct.start_time < ' . $nextHour
    			. ' OR cct.start_time IS NULL) AND c.code = ' . $fp('code')
    			. ' ORDER BY cct.start_time DESC, cct.priority'
    			. ' LIMIT 2'
    			);
    	$results = $statement->execute(['code' => $cardType]);
    	foreach ($results as $r) {
    		$rows[] = $r;
    	}
    	if ($results->count() > 0) {
    		// cache here
    		$d = [strtolower($cardType) => $rows];
    		$predis->hset($hkey, $hour, json_encode($d));
    	}
    	
    	return $rows;
    }
    
    /**
     *
     * @return mixed|unknown
     */
    public function getBankList()
    {
    	$rkey = "blist";
    	$predis = $this->serviceManager->get('PredisCache');
    	
    	$rows = json_decode($predis->get($rkey), true);
    	if ($rows) {
    		return $rows;
    	}
    	
    	$adapter = $this->serviceManager->get(\Zend\Db\Adapter\Adapter::class);
    	
    	$qi = function ($name) use ($adapter) {
    		return $adapter->platform->quoteIdentifier($name);
    	};
    	
    	$fp = function ($name) use ($adapter) {
    		return $adapter->driver->formatParameterName($name);
    	};
    	
    	$statement = $adapter->query(
    			'SELECT * FROM '
    			. $qi('banks') . ' AS ctw'
    			. ' WHERE ctw.status = 1'
    			. ' ORDER BY ctw.priority'
    			);
    	$rows = [];
    	$results = $statement->execute();
    	foreach ($results as $r) {
    		$rows[] = $r;
    	}
    	
    	if (count($rows) > 0) {
    		$predis->set($rkey, json_encode($rows));
    	}
    	return $rows;
    }
    
    /**
     *
     * @param unknown $gameId
     * @param unknown $cardType
     * @return mixed|unknown|NULL
     */
    public function getCardServiceInfo($gameId, $cardType)
    {
    	$hkey = "clist:sev:".$gameId;
    	$rkey = strtolower($cardType);
    	$predis = $this->serviceManager->get('PredisCache');
    	$rows = json_decode($predis->hget($hkey, $rkey), true);
    	if ($rows) {
    		return $rows;
    	}
    	$adapter = $this->serviceManager->get(\Zend\Db\Adapter\Adapter::class);
    	$qi = function ($name) use ($adapter) {
    		return $adapter->platform->quoteIdentifier($name);
    	};
    	
    	$fp = function ($name) use ($adapter) {
    		return $adapter->driver->formatParameterName($name);
    	};
    	
    	$statement = $adapter->query(
    			'SELECT *, pg.code AS channel FROM '
    			. $qi('card_services') . ' AS cs'
    			. ' LEFT JOIN ' . $qi('paygates') . ' AS pg'
    			. ' ON cs.gid = pg.id'
    			. ' LEFT JOIN ' . $qi('cards') . ' AS c'
    			. ' ON cs.cid = c.id'
    			. ' WHERE cs.status = 1 AND cs.game_id = ' . $fp('gameId')
    			. ' AND c.code = ' . $fp('cardType')
    			. ' ORDER BY cs.priority'
    			. ' LIMIT 2'
    			);
    	$results = $statement->execute(['gameId' => $gameId, 'cardType' => $cardType]);
    	$rows = [];
    	foreach ($results as $r) {
    		$rows[] = $r;
    	}
    	if (count($rows) > 0) {
    		$predis->hset($hkey, $rkey, json_encode($rows));
    		return $rows;
    	}
    	return null;
    }
    
    public function getRoleInfo($userInfo, $gameInfo, $serverId)
    {
    	$params = [
    			'passportId' => $userInfo->getId(),
    			'username' => $userInfo->getUsername(),
    			'serverId' => $serverId,
    			'methodName' => __FUNCTION__
    	];
    	return $this->verifyRole($params, $gameInfo);
    }
    
    private function getValidations()
    {
        $rkey = "vld";
        $predis = $this->serviceManager->get('PredisCache');
        $rows = json_decode($predis->get($rkey), true);
        if ($rows) {
            return $rows;
        }
        $adapter = $this->serviceManager->get(\Zend\Db\Adapter\Adapter::class);
        $qi = function ($name) use ($adapter) {
            return $adapter->platform->quoteIdentifier($name);
        };

        $fp = function ($name) use ($adapter) {
            return $adapter->driver->formatParameterName($name);
        };

        $statement = $adapter->query(
            'SELECT parameter, expression, error_code'
                . ' FROM ' . $qi('validations')
                . ' ORDER BY error_code'
        );
        $results = $statement->execute();
        $rows = [];
        foreach ($results as $r) {
            $key = $r['parameter'];
            unset($r['parameter']);
            $rows[$key] = $r;
        }
        if (count($rows) > 0) {
            $predis->set($rkey, json_encode($rows));
            return $rows;
        }
        return null;
    }
    
    public function validateParams($params, $requiredParams = [], $exclusive = [])
    {
    	if (count($requiredParams) > 0) {
    		$validations = $this->getValidations();
    		foreach ($requiredParams as $key) {
    			if (!isset($exclusive[$key]) && isset($validations[$key])) {
    				if (!isset($params[$key]) || !preg_match($validations[$key]['expression'], $params[$key])) {
    					return $validations[$key]['error_code'];
    				}
    			}
    		}
    	}
    
    	return 1;
    }
}
