<?php

namespace ExtranetBundle\Services\Slack;

use ExtranetBundle\Security\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class SlackManager
{
    const CONTENT_TYPE_FORM = 'x-www-form-urlencoded';

    /**
     * @var string
     */
    private $apiUrl;
    /**
     * @var string
     */
    private $apiToken;
    /**
     * @var string
     */
    private $admins;
    /**
     * @var User
     */
    private $user;

    public function __construct(TokenStorage $tokenStorage, array $slackParams)
    {
        $this->apiUrl = $slackParams['apiUrl'];
        $this->apiToken = $slackParams['apiToken'];
        $this->admins = $slackParams['admins'];
        $this->user = $tokenStorage->getToken() ? $tokenStorage->getToken()->getUser() : null;
        $this->slackUser = isset($this->user) && method_exists($this->user, 'getSlackId') ? $this->getUserInfo($this->user->getSlackId()) : null;
    }

    public function getMessagesNumber($cursor = null)
    {
        if (!$this->validatePrerequisites()) {
            return 0;
        }

        $response = $this->getUserMessages($cursor);
        $count = 0;
        foreach ($response['channels'] as $channel) {
            $url = $this->apiUrl
                . 'mpim.history'
            ;
            $params = [
                'token' => $this->apiToken,
                'channel' => $channel['id'],
                'unreads' => true,
            ];
            $mpimDetails = $this->callGetApi($url, $params, self::CONTENT_TYPE_FORM);

            if (is_array($mpimDetails) && 'true' == $mpimDetails['ok'] && isset($mpimDetails['messages']) && isset($mpimDetails['messages'][0]['user'])) {
                $count++;
            }
        }

        return $count;
    }

    public function getLastMessages($cursor = null)
    {
        if (!$this->validatePrerequisites()) {
            return [];
        }

        $response = $this->getUserMessages($cursor);
        $channels = [];
        foreach ($response['channels'] as $channel) {
            $url = $this->apiUrl
                . 'mpim.history'
            ;
            $params = [
                'token' => $this->apiToken,
                'channel' => $channel['id'],
                'unreads' => true,
            ];
            $mpimDetails = $this->callGetApi($url, $params, self::CONTENT_TYPE_FORM);

            if (is_array($mpimDetails) && 'true' == $mpimDetails['ok'] && isset($mpimDetails['messages'])) {
                $channels[] = $mpimDetails['messages'][0];
            }
        }

        return $channels;
    }

    public function getAllMessages($cursor = null)
    {
        if (!$this->validatePrerequisites()) {
            return [];
        }

        $response = $this->getUserMessages($cursor);

        $channels = [];
        foreach ($response['channels'] as $channel) {
            $url = $this->apiUrl
                . 'mpim.history'
            ;
            $params = [
                'token' => $this->apiToken,
                'channel' => $channel['id'],
                'unreads' => true,
            ];
            $mpimDetails = $this->callGetApi($url, $params, self::CONTENT_TYPE_FORM);

            if (is_array($mpimDetails) && 'true' == $mpimDetails['ok'] && isset($mpimDetails['messages'])) {
                $channels[$channel['id']] = $mpimDetails;
            }
        }

        return $channels;
    }

    public function getUserMessages($cursor = null)
    {
        if (!$this->validatePrerequisites()) {
            return [];
        }

        $url = $this->apiUrl
            . 'users.conversations'
        ;

        $params = [
            'token' => $this->apiToken,
            'user' => $this->user->getSlackId(),
            'types' => 'mpim',
            'cursor' => $cursor,
        ];

        $response = $this->callGetApi($url, $params, self::CONTENT_TYPE_FORM);

        if (is_array($response) && 'true' == $response['ok']) {
            return $response;
        }

        return [];
    }

    public function sendMessage($message)
    {
        if (!$this->validatePrerequisites()) {
            return false;
        }

        $mpim = $this->openMpim();

        if (!isset($mpim['id'])) {
            return false;
        }

        $url = $this->apiUrl
            . 'chat.postMessage'
        ;

        $params = [
            'token' => $this->apiToken,
            'channel' => $mpim['id'],
            'text' => $message,
            'as_user' => false,
            'username' => $this->slackUser['display_name'],
        ];

        $response = $this->callPostApi($url, $params, self::CONTENT_TYPE_FORM);

        if (is_array($response) && 'true' == $response['ok']) {
            return true;
        }

        return false;
    }

    private function openMpim()
    {
        $url = $this->apiUrl
            . 'mpim.open'
        ;

        $params = [
            'token' => $this->apiToken,
            'users' => implode(',', [$this->user->getSlackId(), $this->admins]),
        ];

        $response = $this->callPostApi($url, $params, self::CONTENT_TYPE_FORM);

        if (is_array($response) && 'true' == $response['ok']) {
            return $response['group'];
        }

        return [];
    }

    public function getUserInfo($userId)
    {
        $url = $this->apiUrl
            . 'users.info'
        ;

        $params = [
            'token' => $this->apiToken,
            'user' => $userId,
        ];

        $response = $this->callGetApi($url, $params, self::CONTENT_TYPE_FORM);

        if (is_array($response) && 'true' == $response['ok']) {
            return $response['user']['profile'];
        }

        return [];
    }

    private function validatePrerequisites()
    {
        if (!$this->doesUserExist()) {
            return false;
        }

        return true;
    }

    private function doesUserExist()
    {
        $url = $this->apiUrl
            . 'users.info'
        ;

        $params = [
            'token' => $this->apiToken,
            'user' => $this->user->getSlackId(),
        ];

        $response = $this->callGetApi($url, $params, self::CONTENT_TYPE_FORM);

        if (is_array($response) && 'true' == $response['ok']) {
            return true;
        }

        return false;
    }

    private function callPostApi($url, array $data, $contentType = 'json')
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/' . $contentType . ', charset=utf8'
        ));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
        $output = curl_exec($ch);

        // close curl resource to free up system resources
        curl_close($ch);

        return json_decode($output, true);
    }

    private function callGetApi($url, array $parameters = [], $contentType = 'json')
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url . (!empty($parameters) ? '?' . http_build_query($parameters) : ''));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/' . $contentType . ', charset=utf8'
        ]);

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
        $output = curl_exec($ch);

        // close curl resource to free up system resources
        curl_close($ch);

        return json_decode($output, true);
    }
}