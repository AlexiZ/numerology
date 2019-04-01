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
        $messages = $this->slack->getLastMessages();

        return new JsonResponse($this->renderView('@App/Messages/_unread_preview.html.twig', ['messages' => $messages]));
    }

    public function showMessagesAction()
    {
        return $this->render('@App/Messages/show.html.twig', ['conversations' => $this->slack->getAllMessages()]);
    }

    public function renderConversationAction($conversation)
    {
        $messages = [];
        foreach ($conversation['messages'] as $index => $message) {
            if ($index > 0 && $conversation['messages'][$index - 1]['user'] == $message['user']) {
                $texts = [
                    $conversation['messages'][$index - 1]['ts'] => $conversation['messages'][$index - 1]['text'],
                    $message['ts'] => $message['text'],
                ];
                unset($messages[$index - 1]);
            } else {
                $texts = [
                    $message['ts'] => $message['text']
                ];
            }

            $messages[$index] = [
                'user' => $message['user'],
                'texts' => $texts,
            ];
        }

        return $this->render('@App/Messages/_conversation.html.twig', ['messages' => $messages]);
    }
}
