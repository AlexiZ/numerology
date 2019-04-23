<?php

namespace ExtranetBundle\Security;

class User extends Auth0User
{
    const AUTH0_NAMESPACE = 'https://www.numerology.com/';
    const ROLES_CLAIM = 'roles';
    const USER_ID = 'user_id';
    const SLACK_ID = 'slack_id';
    const FIRSTNAME = 'firstname';
    const LASTNAME = 'lastname';

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->data[self::AUTH0_NAMESPACE.self::ROLES_CLAIM] ?? [];
    }

    public function getUserId()
    {
        return $this->data[self::USER_ID] ?? null;
    }

    public function getSlackId()
    {
        return $this->data[self::AUTH0_NAMESPACE.self::SLACK_ID] ?? null;
    }
}
