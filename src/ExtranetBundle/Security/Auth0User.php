<?php

namespace ExtranetBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class Auth0User implements UserInterface, \Serializable
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var string
     */
    protected $accessToken;

    public function __construct(array $data, $accessToken)
    {
        $this->data = $data;
        $this->accessToken = $accessToken;
    }

    public function __toString()
    {
        return (string) $this->data['email'];
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function getId()
    {
        return $this->data['sub'];
    }

    public function getEmail()
    {
        return $this->data['email'];
    }

    public function isEmailVerified()
    {
        return $this->data['email_verified'];
    }

    public function getName()
    {
        return $this->data['name'];
    }

    public function getNickname()
    {
        return $this->data['nickname'];
    }

    public function getPicture()
    {
        return $this->data['picture'];
    }

    public function getUpdatedAt()
    {
        return $this->data['updated_at'];
    }

    public function getFirstname()
    {
        return $this->data[User::FIRSTNAME] ?? $this->data['given_name'] ?? null;
    }

    public function getLastname()
    {
        return $this->data[User::LASTNAME] ?? $this->data['family_name'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->data['email'];
    }

    public function getRoles()
    {
        return [];
    }

    public function replaceUserMetadata(array $userMetaData)
    {
        $this->data = array_merge($this->data, $userMetaData);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        return;
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize([
            $this->data,
            $this->accessToken,
        ]);
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list($this->data, $this->accessToken) = unserialize($serialized);
    }
}
