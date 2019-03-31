<?php

namespace AppBundle\Controller;

use AppBundle\Services\SendBird\SendBirdManager;
use AppBundle\Services\Slack\SlackManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class MessagesController extends Controller
{
    /**
     * @var SendBirdManager
     */
    private $slack;

    public function __construct(SlackManager $slack)
    {
        $this->slack = $slack;
    }

    public function getMessagesNumberAction()
    {
        $unread = $this->slack->getMessagesNumber();

        return new JsonResponse($unread);
    }

    public function getMessagesAction()
    {
        $messages = $this->slack->getMessages();

        return new JsonResponse($this->renderView('@App/Messages/_unread_preview.html.twig', ['messages' => $messages]));
    }
}
