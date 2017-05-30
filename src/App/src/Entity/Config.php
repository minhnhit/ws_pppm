<?php
/**
 * Created by PhpStorm.
 * User: jamesn
 * Date: 5/12/17
 * Time: 1:54 PM
 */
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ConfigRepository")
 * @ORM\Table(name="configs")
 */
class Config
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, unique=true, nullable=false)
     */
    private $key;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $value;
}