<?php

namespace AppBundle\Security;

class User extends Auth0User
{
    const AUTH0_NAMESPACE = 'https://www.numerology.com/';
    const ROLES_CLAIM = 'roles';
    const USER_ID = 'user_id';
    const LAST_LOGIN_DATE_CLAIM = 'last_login_date';

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

    public function getLastLoginDate()
    {
        return $this->data[self::AUTH0_NAMESPACE.self::LAST_LOGIN_DATE_CLAIM] ?? null;
    }
}
