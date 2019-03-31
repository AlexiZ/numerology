<?php

namespace AppBundle\Services\Slack;

use AppBundle\Security\User;
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
    private $defaultChannel;
    /**
     * @var User
     */
    private $user;

    public function __construct(TokenStorage $tokenStorage, array $slackParams)
    {
        $this->apiUrl = $slackParams['apiUrl'];
        $this->apiToken = $slackParams['apiToken'];
        $this->defaultChannel = $slackParams['defaultChannel'];
        $this->user = $tokenStorage->getToken() ? $tokenStorage->getToken()->getUser() : null;
        $this->inviteEmail = $slackParams['inviterGmailId'] . '+' . (method_exists($this->user, 'getId') ? $this->user->getId() : uniqid()) . '@gmail.com';
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

            if (is_array($mpimDetails) && 'true' == $mpimDetails['ok'] && isset($mpimDetails['messages'])) {
                $count++;
            }
        }

        return $count;
    }

    public function getMessages($cursor = null)
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
                $channels[] = end($mpimDetails['messages']);
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

        $url = $this->apiUrl
            . 'chat.postMessage'
        ;

        $params = [
            'token' => $this->apiToken,
            'channel' => $this->defaultChannel,
            'text' => $message,
            'username' => $this->user->getNickname(),
        ];

        $response = $this->callPostApi($url, $params);

        if (is_array($response) && 'true' == $response['ok']) {
            return true;
        }

        return false;
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
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
        $output = curl_exec($ch);

        dump($ch);
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