<?php

namespace OAuth2Server\Entity;

use Doctrine\ORM\Mapping as ORM;

trait EntityTrait
{
    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(name="id", type="string", length=100)
     */
    protected $identifier;

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }
}
