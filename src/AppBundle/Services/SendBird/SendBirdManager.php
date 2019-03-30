<?php

namespace AppBundle\Services\SendBird;

use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class SendBirdManager
{
    const GROUP_CHANNELS = 'group_channels';
    const USERS = 'users';
    const CUSTOM_TYPE = 'custom_type';
    const CUSTOM_TYPE_SUPPORT = 'support';

    /**
     * @var array
     */
    private $apiUrl;
    /**
     * @var string
     */
    private $apiToken;
    /**
     * @var string
     */
    private $privateChannelUrl;
    /**
     * @var string
     */
    private $userId;

    public function __construct(TokenStorage $tokenStorage, array $sendbirdParams)
    {
        $this->apiUrl = $sendbirdParams['apiUrl'];
        $this->apiToken = $sendbirdParams['apiToken'];
        $this->userId = $tokenStorage->getToken()->getUser()->getId();
        $this->privateChannelUrl = Urlizer::urlize($this->userId);
    }

    public function getUnreadMessagesNumber()
    {
        if (!$this->privateChannelExist()) {
            $this->createPrivateChannel();
        }

        $url = $this->apiUrl
            .self::GROUP_CHANNELS.'/'
            .$this->privateChannelUrl
            .'/unread_count'
        ;

        $response = $this->callGetApi($url, [
            'user_ids' => [$this->userId],
        ]);

        if (is_array($response) && array_key_exists('name', $response)) {
            return $response['unread'][$this->userId];
        }

        return 0;
    }

    public function getMessages($limit = 5)
    {
        if (!$this->privateChannelExist()) {
            return [];
        }

        $url = $this->apiUrl
            .self::GROUP_CHANNELS.'/'
            .$this->privateChannelUrl
            .'/messages'
        ;

        $response = $this->callGetApi($url, [
            'message_ts' => time(),
            'prev_limit' => $limit,
            self::CUSTOM_TYPE => self::CUSTOM_TYPE_SUPPORT,
        ]);

        if (is_array($response) && array_key_exists('error', $response) && true === $response['error']) {
            return [];
        }

        return $response;
    }

    public function sendMessage($message)
    {
        if (!$this->privateChannelExist()) {
            $this->createPrivateChannel();
        }

        $url = $this->apiUrl
            .self::GROUP_CHANNELS.'/'
            .$this->privateChannelUrl
            .'/messages'
        ;

        $response = $this->callPostApi($url, [
            'message' => $message,
            self::CUSTOM_TYPE => self::CUSTOM_TYPE_SUPPORT,
            'mark_as_read' => true,
            'message_type' => 'MESG',
            'user_id' => $this->userId,
        ]);

        if (is_array($response) && array_key_exists('name', $response)) {
            return true;
        }

        return false;
    }

    private function privateChannelExist()
    {
        $url = $this->apiUrl
            .self::GROUP_CHANNELS.'/'
            .$this->privateChannelUrl
        ;

        $response = $this->callGetApi($url);

        if (is_array($response) && array_key_exists('error', $response) && true === $response['error']) {
            return false;
        }

        return true;
    }

    private function createPrivateChannel()
    {
        if (!$this->createUser()) {
            return false;
        }

        $url = $this->apiUrl
            .self::GROUP_CHANNELS.'/'
        ;

        $response = $this->callPostApi($url, [
            'name' => $this->userId,
            'channel_url' => $this->privateChannelUrl,
            self::CUSTOM_TYPE => self::CUSTOM_TYPE_SUPPORT,
            'is_public' => true,
            'user_ids' => [$this->userId],
        ]);

        if (is_array($response) && array_key_exists('name', $response)) {
            return true;
        }

        return false;
    }

    private function createUser()
    {
        $url = $this->apiUrl
            .self::USERS.'/'
        ;

        $response = $this->callPostApi($url, [
            'user_id' => $this->userId,
            'nickname' => $this->userId,
            'profile_url' => $this->privateChannelUrl,
        ]);

        if (is_array($response) && array_key_exists('user_id', $response)) {
            return true;
        }

        return false;
    }

    private function callPostApi($url, array $data)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json, charset=utf8',
            'Api-Token: ' . $this->apiToken
        ));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
        $output = curl_exec($ch);

        // close curl resource to free up system resources
        curl_close($ch);

        return json_decode($output, true);
    }

    private function callGetApi($url, array $parameters = [])
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url . (!empty($parameters) ? '?' . http_build_query($parameters) : ''));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json, charset=utf8',
            'Api-Token: ' . $this->apiToken
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