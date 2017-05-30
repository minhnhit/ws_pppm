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
 * @ORM\Entity(repositoryClass="App\Repository\CardChannelRepository")
 * @ORM\Table(name="card_channels",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="game_paygate_card_unique",
 *          columns={"game_id", "paygate_id", "card_id"})
 *      }
 * )
 */
class CardChannel
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Entity\Game", inversedBy="card_channel")
     * @ORM\JoinColumn(name="game_id", referencedColumnName="id")
     */
    private $game;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Entity\Paygate", inversedBy="card_channel")
     * @ORM\JoinColumn(name="paygate_id", referencedColumnName="id")
     */
    private $paygate;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Entity\Card", inversedBy="card_channel")
     * @ORM\JoinColumn(name="card_id", referencedColumnName="id")
     */
    private $card;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $rate;

    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    private $priority;

    /**
     * @ORM\Column(type="integer")
     */
    private $start_time;

    /**
     * @ORM\Column(type="boolean", options={"default":-1})
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