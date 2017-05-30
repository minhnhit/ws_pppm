<?php
/**
 * @author      Haydar KULEKCI <haydarkulekci@gmail.com>
 * @copyright   Copyright (c) Haydar KULEKCI
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/biberlabs/zend-expressive-oauth2-server
 */

namespace OAuth2Server\Entity;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="OAuth2Server\Entity\Repository\RefreshTokenRepository")
 * @ORM\Table(name="oauth_refresh_tokens")
 */
class RefreshToken implements RefreshTokenEntityInterface
{
    use RefreshTokenTrait, EntityTrait;

    /**
     * @var ScopeEntityInterface[]
     * @ORM\ManyToMany(targetEntity="OAuth2Server\Entity\Scope", inversedBy="authCodes")
     * @ORM\JoinTable(name="oauth_refresh_token_scopes",
     *     joinColumns={@ORM\JoinColumn(name="scope_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="refresh_token_scope_id", referencedColumnName="id")}
     *     )
     */
    protected $scopes = [];

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated_at;
}
