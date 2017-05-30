<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="\App\Repository\GameKeyRepository")
 * @ORM\Table(name="game_keys")
 */
class GameKey
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="bigint", options={"unsigned":true})
     */
	private $id;

    /**
     * @ORM\Column(type="bigint", unique=true, nullable=false, options={"unsigned":true})
     */
    private $game_id;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
	private $pub_key;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    private $priv_key;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    private $cpub_key;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $cpriv_key;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $pay_url;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $verify_url;

	public function getId()
	{
		return $this->id;
	}
	
	public function setId($id)
	{
		$this->id = $id;
	}
	
	public function getUsername()
	{
		return $this->username;
	}
	
	public function setUsername($username)
	{
		$this->username = $username;
	}
	
	public function getPassword()
	{
		return $this->password;
	}
	
	public function setPassword($password)
	{
		$this->password = $password;
	}
	
	public function getSource()
	{
		return $this->source;
	}
	
	public function setSource($source) 
	{
		$this->source = $source;
	}
	
	public function populate($data)
	{
		$this->id = $data['id'];
		$this->username = $data['username'];
		$this->password = $data['password'];
		$this->source = $data['source'];
	}
}