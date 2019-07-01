<?php

namespace ExtranetBundle\Controller;

use ExtranetBundle\Services\Slack\SlackManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class MessagesController extends Controller
{
    /**
     * @var SlackManager
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

        return new JsonResponse($this->renderView('@Extranet/Messages/_unread_preview.html.twig', ['messages' => $messages]));
    }

    public function showMessagesAction()
    {
        return $this->render('@Extranet/Messages/show.html.twig', ['conversations' => $this->slack->getAllMessages()]);
    }

    public function renderConversationUserAction($conversation)
    {
        $messages = [];
        foreach ($conversation['messages'] as $message) {
            $messages[] = [
                'sender' => $message['user'] ?? $message['bot_id'],
                'user' => isset($message['user']) ? $message['user'] : null,
                'bot' => isset($message['bot_id']) ? $message['username'] : null,
                'text' => [
                    'ts' => $message['ts'],
                    'value' => $message['text'],
                ],
            ];
        }

        return $this->render('@Extranet/Messages/_conversation_user.html.twig', ['messages' => array_reverse($messages, true)]);
    }

    public function renderConversationAdminAction($conversation)
    {
        $messages = [];
        foreach ($conversation['messages'] as $message) {
            $messages[] = [
                'sender' => $message['user'] ?? $message['bot_id'],
                'user' => isset($message['user']) ? $message['user'] : null,
                'bot' => isset($message['bot_id']) ? $message['username'] : null,
                'text' => [
                    'ts' => $message['ts'],
                    'value' => $message['text'],
                ],
            ];
        }

        return $this->render('@Extranet/Messages/_conversation_admin.html.twig', ['messages' => array_reverse($messages, true)]);
    }

    public function sendMessageAction(Request $request, SlackManager $slackManager)
    {
        if (!$request->request->has('message') || !$request->request->get('message')) {
            return new JsonResponse('Message vide. Vous devez entrer un texte.', 400);
        }

        if ($slackManager->sendMessage($request->request->get('message'))) {
            return new JsonResponse('ok');
        }

        return new JsonResponse('Une erreur inconnue est survenue', 500);
    }
}
