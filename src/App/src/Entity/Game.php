<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="\App\Repository\GameRepository")
 * @ORM\Table(name="games")
 */
class Game
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
     * @ORM\Column(name="name", type="string", length=255, unique=true, nullable=false)
     */
	private $name;

    /**
     * @Gedmo\Slug(fields={"name", "code"})
     * @ORM\Column(name="slug", type="string", length=255, unique=true, nullable=false)
     */
    private $slug;

    /**
     * @ORM\Column(name="type", type="string",
     *     columnDefinition="ENUM('web', 'mobile', 'channeling', 'private', 'other')",
     *     options={"default":"web"}
     * )
     */
    private $type;

    /**
     * @ORM\Column(name="icon")
     */
    private $icon;

    /**
     * @ORM\Column(name="chapo", type="string")
     */
    private $chapo;

    /**
     * @ORM\Column(name="desc", type="text")
     */
    private $desc;

    /**
     * @ORM\Column(name="currency", type="string")
     */
    private $currency;

    /**
     * @ORM\Column(name="rate", type="float")
     */
    private $rate;

    /**
     * @ORM\Column(name="is_role", type="boolean")
     */
    private $role;

    /**
     * @ORM\ManyToMany(targetEntity="\App\Entity\Source", inversedBy="games")
     * @ORM\JoinTable(name="game_sources",
     *      joinColumns={@ORM\JoinColumn(name="game_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="source_id", referencedColumnName="id")}
     *      )
     */
	private $sources;

    /**
     * @ORM\ManyToMany(targetEntity="\App\Entity\Server", inversedBy="games")
     * @ORM\JoinTable(name="game_servers",
     *      joinColumns={@ORM\JoinColumn(name="game_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="server_id", referencedColumnName="id")}
     *      )
     */
    private $servers;

    /**
     * @ORM\OneToMany(targetEntity="\App\Entity\Version", mappedBy="game", cascade={"persist", "remove", "merge"})
     * @ORM\JoinColumn(name="id", referencedColumnName="game_id")
     */
    private $versions;

    /**
     * @ORM\OneToMany(targetEntity="\App\Entity\CardChannel", mappedBy="game", cascade={"persist", "remove", "merge"})
     * @ORM\JoinColumn(name="id", referencedColumnName="game_id")
     */
    private $card_channel;

    /**
     * @ORM\OneToOne(targetEntity="\App\Entity\GameKey", cascade={"persist", "remove", "merge"})
     * @ORM\JoinColumn(name="keys", referencedColumnName="id")
     */
    private $keys;

    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(name="priority")
     * @ORM\OrderBy({"priority" = "ASC"})
     */
    private $priority;

    /**
     * @ORM\Column(type="integer", name="status", length=4, options={"unsigned":false, "default":-1})
     * @ORM\OrderBy({"status" = "DESC"})
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