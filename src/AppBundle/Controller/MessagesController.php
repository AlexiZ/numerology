<?php

namespace AppBundle\Controller;

use AppBundle\Services\SendBird\SendBirdManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class MessagesController extends Controller
{
    /**
     * @var SendBirdManager
     */
    private $sendBird;

    public function __construct(SendBirdManager $sendBird)
    {
        $this->sendBird = $sendBird;
    }

    public function getUnreadNumberAction()
    {
        $unread = $this->sendBird->getUnreadMessagesNumber();

        return new JsonResponse($unread);
    }

    public function getMessagesAction()
    {
        $messages = $this->sendBird->getMessages();

        return new JsonResponse($this->renderView('@App/Messages/_unread_preview.html.twig', ['messages' => $messages]));
    }
}
