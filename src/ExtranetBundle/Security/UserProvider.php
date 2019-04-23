<?php

namespace ExtranetBundle\Security;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use ExtranetBundle\Services\Auth0\Auth0Manager;
use ExtranetBundle\Exception\Auth0Exception;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface, OAuthAwareUserProviderInterface
{
    /**
     * @var ObjectManager
     */
    protected $em;
    protected $auth0Manager;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, Auth0Manager $auth0Manager)
    {
        $this->em = $em;
        $this->auth0Manager = $auth0Manager;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        if (empty($response->getEmail())) {
            throw new Auth0Exception('The received token does not contain the email key. Check your scope settings in auth0 login.');
        }

        return new User($response->getData(), $response->getAccessToken());
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        throw new UsernameNotFoundException();
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        $token = $this->auth0Manager->getAccessToken();
        try {
            $userData = $this->auth0Manager->getUserinfo($token);

            if (!isset($userData['email'])) {
                throw new Auth0Exception('The received token does not contain the email key. Check your scope settings in auth0 login.');
            }
        } catch (\Exception $e) {
            return $user;
        }

        if (null === $userData) {
            throw new UsernameNotFoundException();
        }

        return new User($userData, $token);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return User::class;
    }
}
