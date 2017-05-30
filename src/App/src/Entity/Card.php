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
 * @ORM\Entity(repositoryClass="App\Repository\CardRepository")
 * @ORM\Table(name="cards")
 */
class Card
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, unique=false, nullable=false)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=32, unique=true, nullable=false)
     */
    private $code;

    /**
     * @ORM\Column(name="type", type="string",
     *     columnDefinition="ENUM('mobile', 'game','atm','credit')",
     *     options={"default":"mobile"}
     * )
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $icon;

    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    private $priority;

    /**
     * @ORM\ManyToMany(targetEntity="\App\Entity\Paygate", mappedBy="cards")
     */
    private $paygates;

    /**
     * @ORM\OneToMany(targetEntity="\App\Entity\CardChannel", mappedBy="card", cascade={"persist", "remove", "merge"})
     * @ORM\JoinColumn(name="id", referencedColumnName="card_id")
     */
    private $card_channel;

    /**
     * @ORM\Column(type="boolean", options={"default":1})
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