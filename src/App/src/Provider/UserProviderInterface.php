<?php
namespace App\Provider;

interface UserProviderInterface
{
    /**
     * Persist an updated entity
     *
     * @param $entity
     *
     * @return mixed
     */
    public function update($entity);
    /**
     * Find a user by email
     *
     * @param $username
     *
     * @return UserInterface
     */
    public function findByEmail($username);
    /**
     * Get a user by ID
     *
     * @param $userId
     *
     * @return UserInterface
     */
    public function getById($userId);
}
