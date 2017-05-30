<?php
namespace App\Mapper\SqlServer;

use App\Mapper\AbstractGateway;
use App\Provider\UserProviderInterface;
use App\Entity\User;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Hydrator;

class Gateway extends AbstractGateway implements UserProviderInterface
{
	public function getDb()
	{
		try {
			return $this->getServiceManager()->get('passport_db');
		}catch(\Exception $e) {
			$subject = "Exception: SQL Server";
			$this->getMailService()->sendAlertEmail($subject, $e);
		}
		
		return false;
	}
	
	public function login($data)
	{
		$username = strtolower(trim($data['username']));
		$password = md5($data['password']);
		
		try {
			$stmt = $this->getDb()->createStatement();
			$stmt->prepare('EXECUTE dbo.usp_passport_authenticate :username, :password');
			$stmt->setParameterContainer(new ParameterContainer([
					':username' => $username, ':password' => $password
			]));
		
			$statement = $stmt->execute()->getResource();
			$result = $statement->fetch(\PDO::FETCH_ASSOC);
			if ($result['result'] == 1) {
				$stmt = $this->getDb()->createStatement();
				//$stmt->prepare('EXECUTE dbo.usp_ssg_login_update :passport_id');
				$stmt->prepare('EXECUTE dbo.usp_gamble_login_update :passport_id');
				$stmt->setParameterContainer(new ParameterContainer([':passport_id' => $result['id']]));
				$statement = $stmt->execute()->getResource();
				$statement->fetch(\PDO::FETCH_ASSOC);
                $result['password'] = $password;
                $user = new User();
                $hydrator = new Hydrator\ArraySerializable();
                $hydrator->hydrate($result, $user);
                return $this->generateJWT($user);
			}
		} catch (\Exception $e) {
			$subject = "SQL SERVER Error";
			$this->getMailService()->sendAlertEmail($subject, $e);
		}
		$ret = ['code' => -2007];
		return $ret;
	}
	
	public function register($data)
	{
		
	}
	
	public function loginOauth($data)
	{
		
	}
	
	public function update($data)
	{
		
	}
	
	public function getById($userId)
	{
		
	}
	
	public function findByEmail($username)
	{
		
	}
}