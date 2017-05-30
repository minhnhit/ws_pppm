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
 * @ORM\Entity(repositoryClass="App\Repository\PaygateRepository")
 * @ORM\Table(name="paygates")
 */
class Paygate
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
     * @ORM\Column(type="string", length=50, unique=true, nullable=false)
     */
    private $slug;

    /**
     * @ORM\Column(type="float", length=4, precision=2, nullable=true)
     */
    private $discount;

    /**
     * @ORM\Column(type="float", length=4, precision=2, nullable=true)
     */
    private $rate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $desc;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $config;

    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    private $priority;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Card", inversedBy="paygates")
     * @ORM\JoinTable(name="paygate_cards",
     *      joinColumns={@ORM\JoinColumn(name="paygate_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="card_id", referencedColumnName="id")}
     *      )
     */
    private $cards;

    /**
     * @ORM\OneToMany(targetEntity="\App\Entity\CardChannel", mappedBy="paygate", cascade={"persist", "remove", "merge"})
     * @ORM\JoinColumn(name="id", referencedColumnName="paygate_id")
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