<?php
namespace App\Entity;

class User
{
	private $id;
	
	private $username;
	
	private $password;
	
	private $source;
	
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