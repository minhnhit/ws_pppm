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
 * @ORM\Entity(repositoryClass="\App\Repository\SourceRepository")
 * @ORM\Table(name="sources")
 */
class Source
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="bigint", options={"unsigned":true})
     */
    private $id;

    /**
     * @ORM\Column(name="code", type="string", length=32, unique=true, nullable=false)
     */
    private $code;

    /**
     * @ORM\Column(name="name", type="string", length=32, unique=true, nullable=false)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Game", mappedBy="sources")
     */
    private $games;

    /**
     * @ORM\Column(type="integer", name="status", options={"unsigned":false, "default":-1})
     */
    private $status;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;
}