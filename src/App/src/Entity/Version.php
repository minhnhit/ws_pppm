<?php
/**
 * Created by PhpStorm.
 * User: jamesn
 * Date: 5/12/17
 * Time: 1:54 PM
 */
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VersionRepository")
 * @ORM\Table(name="game_versions",
 *  uniqueConstraints={@ORM\UniqueConstraint(name="game_version_unique", columns={"game_id", "version"})}
 * )
 */
class Version
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="bigint", options={"unsigned":true})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Game", inversedBy="versions")
     * @ORM\JoinColumn(name="game_id", referencedColumnName="id")
     */
    private $game;

    /**
     * @ORM\Column(name="version", type="string", length=10, unique=false, nullable=false)
     */
    private $version;

    /**
     * @ORM\Column(name="client", type="string", length=32, unique=true, nullable=false)
     */
    private $client;

    /**
     * @ORM\Column(type="integer", name="status", options={"unsigned":false, "default":-1})
     */
    private $status;

}