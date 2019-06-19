<?php

namespace ExtranetBundle\Services\Auth0;

use Auth0\SDK\API\Authentication;
use Auth0\SDK\API\Header\Authorization\AuthorizationBearer;
use Auth0\SDK\API\Header\ContentType;
use Auth0\SDK\API\Helpers\ApiClient;
use Auth0\SDK\API\Management;
use Gedmo\Sluggable\Util\Urlizer;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use ExtranetBundle\Exception\ExistingUserException;
use ExtranetBundle\Exception\UnkownUserException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;

class Auth0Manager
{
    protected $domain;
    protected $clientId;
    protected $clientSecret;
    protected $token;
    protected $managementApi;
    protected $authenticationApi;
    protected $router;
    protected $loginClientId;

    /**
     * @var RoleHierarchyInterface
     */
    private $roleHierarchy;
    /**
     * @var int
     */
    private $auth0EmailTtl;
    /**
     * @var string
     */
    private $baseEmail;

    public function __construct(UrlGeneratorInterface $router, RoleHierarchyInterface $roleHierarchy, array $auth0Data, $baseEmail)
    {
        $this->domain = trim($auth0Data['domain'], 'https://');
        $this->clientId = $auth0Data['clientId'];
        $this->clientSecret = $auth0Data['clientSecret'];
        $this->router = $router;
        $this->loginClientId = $auth0Data['loginClientId'];
        $this->roleHierarchy = $roleHierarchy;
        $this->auth0EmailTtl = $auth0Data['auth0EmailTtl'];
        $this->baseEmail = $baseEmail;
    }

    public function getAccessToken()
    {
        if (null !== $this->token) {
            return $this->token['access_token'];
        }

        return $this->refreshToken();
    }

    public function refreshToken()
    {
        $curl = curl_init();

        $data = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'audience' => "https://$this->domain/api/v2/",
        ];

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://$this->domain/oauth/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'content-type: application/json',
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new \RuntimeException('Error during auth0 API call: '.$err);
        } else {
            $this->token = json_decode($response, true);
        }

        return $this->token['access_token'] ?? null;
    }

    private function getManagementApi()
    {
        if (null === $this->managementApi) {
            $this->managementApi = new Management($this->getAccessToken(), $this->domain, ['connect_timeout' => 1]);
        }

        return $this->managementApi;
    }

    private function getAuthenticationApi()
    {
        if (null === $this->authenticationApi) {
            $this->authenticationApi = new Authentication($this->domain);
        }

        return $this->authenticationApi;
    }

    public function getUser($email)
    {
        $api = $this->getManagementApi();
        $user = [];

        try {
            $users = $api->usersByEmail->get(['email' => urlencode($email)]);
            $user = !empty($users) ? $users[0] : [];
        } catch (ClientException $e) {
            if (404 === $e->getCode() || 429 === $e->getCode()) {
                $user = [];
            } else {
                throw $e;
            }
        } catch (RequestException $e) {
            if (429 === $e->getCode()) {
                $user = [];
            }
        } catch (\Exception $e) {
            $user = [];
        }

        return $user;
    }

    public function getUsers($page = 1, $limit = 10, $fields = '')
    {
        $api = $this->getManagementApi();
        $users = [];

        try {
            $users = $api->users->getAll(['page' => $page - 1, 'per_page' => $limit], $fields);
        } catch (\Exception $e) {
        }

        return $users;
    }

    public function getUserById($id)
    {
        $api = $this->getManagementApi();
        $user = null;

        try {
            $user = $api->users->get($id);
        } catch (ClientException $e) {
            if (404 === $e->getCode() || 429 === $e->getCode()) {
                $user = null;
            } else {
                throw $e;
            }
        } catch (RequestException $e) {
            if (429 === $e->getCode()) {
                $user = null;
            }
        } catch (\Exception $e) {
            $user = null;
        }

        return $user;
    }

    public function getUserinfo($accessToken)
    {
        $api = $this->getAuthenticationApi();

        try {
            $userinfo = $api->userinfo($accessToken);
        } catch (ClientException $e) {
            if (404 === $e->getCode()) {
                $userinfo = null;
            } else {
                throw $e;
            }
        }

        return $userinfo;
    }

    public function isGranted($userId, $role)
    {
        $api = $this->getManagementApi();
        $user = $api->users->get($userId);

        return isset($user['app_metadata']) && in_array($role, $user['app_metadata']['roles']);
    }

    public function updateUserRoles($email, array $roles)
    {
        $user = $this->getUser($email);

        if (empty($user)) {
            throw new UnkownUserException(sprintf('Unkown user in auth0: %s', $email()));
        }

        $api = $this->getManagementApi();

        $metadata = array_merge($user['app_metadata'], [
            'roles' => $roles,
        ]);

        $api->users->update($user['user_id'], [
            'app_metadata' => $metadata,
        ]);
    }

    /**
     * @param string $userId auth0 userId
     * @param array $roles new roles
     *
     * @return array
     */
    public function addUserRolesByUserId($userId, array $roles)
    {
        $api = $this->getManagementApi();

        $user = $api->users->get($userId);

        if (null === $user) {
            throw new UnkownUserException(sprintf('Unkown user in auth0: %s', $userId()));
        }
        if (!array_key_exists('app_metadata', $user)) {
            $user['app_metadata'] = ['roles' => []];
        }

        $appMetadataRoles = array_unique(array_merge($user['app_metadata']['roles'], $roles));

        $appMetadata = array_merge($user['app_metadata'], [
            'roles' => $appMetadataRoles,
        ]);

        return $api->users->update($user['user_id'], [
            'app_metadata' => $appMetadata,
        ]);
    }

    public function removeUserRoleByUserId($userId, $role)
    {
        $api = $this->getManagementApi();

        $user = $api->users->get($userId);

        if (null === $user) {
            throw new UnkownUserException(sprintf('Unkown user in auth0: %s', $userId()));
        }

        $childrenRoles = array_slice($this->getInheritedRoles($role), 1);
        $appMetadataRoles = $childrenRoles;

        $appMetadata = array_merge($user['app_metadata'], [
            'roles' => $appMetadataRoles,
        ]);

        return $api->users->update($user['user_id'], [
            'app_metadata' => $appMetadata,
        ]);
    }

    public function updateUserMetadata($email, array $data)
    {
        $user = $this->getUser($email);

        if (empty($user)) {
            throw new UnkownUserException(sprintf('Unkown user in auth0: %s', $email));
        }

        $data = array_merge($user['user_metadata'] ?? [], $data);
        $api = $this->getManagementApi();

        $api->users->update($user['user_id'], [
            'user_metadata' => $data,
        ]);

        $this->refreshToken();
    }

    public function updateAppMetadata($email, array $data)
    {
        $user = $this->getUser($email);

        if (empty($user)) {
            throw new UnkownUserException(sprintf('Unkown user in auth0: %s', $email));
        }

        $api = $this->getManagementApi();

        $api->users->update($user['user_id'], [
            'app_metadata' => $data,
        ]);
    }

    public function changeEmail($oldEmail, $newEmail)
    {
        if ($oldEmail === $newEmail) {
            return false;
        }
        $user = $this->getUser($oldEmail);

        if (empty($user)) {
            throw new UnkownUserException(sprintf('Unkown user in auth0: %s', $oldEmail));
        }

        $api = $this->getManagementApi();

        $api->users->update($user['user_id'], [
            'email' => $newEmail,
            'verify_email' => true,
        ]);

        return true;
    }

    public function createPasswordResetTicket($userId, $locale)
    {
        $params = [
            'client_id' => $this->loginClientId,
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'redirect_uri' => $this->router->generate('auth0_callback', ['language' => $locale], UrlGeneratorInterface::ABSOLUTE_URL), // removed param cuz invalid callback '_destination' => $this->router->generate('site_customer_area')
            'locale' => $locale,
        ];

        $returnUrl = 'https://'.$this->domain.'/authorize?'.http_build_query($params);

        $ticket = $this->createPasswordChangeTicket($userId, null, null, $returnUrl, null, $this->auth0EmailTtl);

        return "{$ticket['ticket']}&language={$locale}";
    }

    /**
     * Method taken from auth api bundle, intially called "createPasswordChangeTicketRaw"
     *
     * @param string|null $user_id
     * @param string|null $email
     * @param string|null $new_password
     * @param string|null $result_url
     * @param string|null $connection_id
     *
     * @return mixed
     */
    public function createPasswordChangeTicket($user_id = null, $email = null, $new_password = null, $result_url = null, $connection_id = null, $ttl_sec = null)
    {
        $body = [];

        if ($user_id) {
            $body['user_id'] = $user_id;
        }
        if ($email) {
            $body['email'] = $email;
        }
        if ($new_password) {
            $body['new_password'] = $new_password;
        }
        if ($result_url) {
            $body['result_url'] = $result_url;
        }
        if ($connection_id) {
            $body['connection_id'] = $connection_id;
        }
        if ($ttl_sec) {
            $body['ttl_sec'] = $ttl_sec;
        }

        $client = new ApiClient([
            'domain' => 'https://'.$this->domain,
            'basePath' => '/api/v2/',
            'headers' => [
                new AuthorizationBearer($this->getAccessToken()),
            ],
        ]);

        return $client->post()
            ->tickets()
            ->addPath('password-change')
            ->withHeader(new ContentType('application/json'))
            ->withBody(json_encode($body))
            ->call();
    }

    /**
     * @param array|null $attributes
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function createUser(array $attributes = null)
    {
        if (!$attributes['email']) {
            throw new \Exception('Missing input data');
        }

        $api = $this->getManagementApi();
        $existingUser = $this->getUser($attributes['email']);

        if (!empty($existingUser)) {
            throw new ExistingUserException('A user with this email already exists');
        }

        return $api->users->create([
            'connection' => 'Username-Password-Authentication',
            'email' => $attributes['email'],
            'password' => uniqid('', true).'!A',
            'email_verified' => true,
            'user_metadata' => [
                'slack_id' => $attributes['slackId'],
            ],
            'app_metadata' => [
                'roles' => ['ROLE_USER'],
            ],
        ]);
    }

    /**
     * @param string $userId
     *
     * @return mixed
     */
    public function blockUser($userId)
    {
        return $this->getManagementApi()->users->update($userId, [
            'blocked' => true,
        ]);
    }

    /**
     * @param string $userId
     *
     * @return mixed auth0 response
     */
    public function unblockUser($userId)
    {
        return $this->getManagementApi()->users->update($userId, [
            'blocked' => false,
        ]);
    }

    public function grantRole($userId, $role)
    {
        $inheritedRoles = $this->getInheritedRoles($role);

        return $this->addUserRolesByUserId($userId, $inheritedRoles);
    }

    public function removeRole($userId, $role)
    {
        return $this->removeUserRoleByUserId($userId, $role);
    }

    private function getInheritedRoles($role)
    {
        $inheritedRoles = $this->roleHierarchy->getReachableRoles([new Role($role)]);

        return array_map(function (RoleInterface $role) {
            return $role->getRole();
        }, $inheritedRoles);
    }

    public function sendVerificationEmail($userId)
    {
        return  $this->getManagementApi()->jobs->sendVerificationEmail($userId);
    }

    public function isEmailAlreadyTaken($email)
    {
        return [] !== $this->getUser($email);
    }
}
