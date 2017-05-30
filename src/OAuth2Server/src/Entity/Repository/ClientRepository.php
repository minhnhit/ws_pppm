<?php

namespace OAuth2Server\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use OAuth2Server\Entity\Client as ClientEntity;

class ClientRepository extends EntityRepository implements ClientRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getClientEntity($clientIdentifier, $grantType, $clientSecret = null, $mustValidateSecret = true)
    {
        $clients = [
            'myawesomeapp' => [
                'secret'          => password_hash('admin', PASSWORD_BCRYPT),
                'name'            => 'test',
                'redirect_uri'    => 'http://foo/bar',
                'is_confidential' => true,
            ],
        ];

        $qb = $this->_em->createQueryBuilder();
        $qb->select('C');
        $qb->from(ClientEntity::class, 'C')
            ->andWhere('C.identifier = :identifier')
            ->setParameter('identifier', $clientIdentifier);
        $query = $qb->getQuery();
        
        /**
         * @var ClientEntity $client
         */
        $client = $query->getOneOrNullResult();
        if ($mustValidateSecret === true
            && $client->isConfidential() === true
            && password_verify($clientSecret, $client->getSecret()) === false
        ) {
            return;
        }
        return $client;
    }
}
