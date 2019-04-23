<?php

namespace ExtranetBundle\Twig;

use ExtranetBundle\Services\Slack\SlackManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MessagesExtension extends AbstractExtension
{
    /**
     * @var SlackManager
     */
    private $slackManager;

    public function __construct(SlackManager $slackManager)
    {
        $this->slackManager = $slackManager;
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('howLongAgo', array($this, 'howLongAgo')),
            new TwigFilter('getSlackUser', array($this, 'getSlackUser')),
        );
    }

    public function howLongAgo($date)
    {
        $message = new \DateTime(date('Y-m-d H:i:s.u', $date));
        $now = new \DateTime(date('Y-m-d H:i:s.u', time()));

        $interval = $now->diff($message);

        $doPlural = function($nb,$str){return $nb>1?$str.'s':$str;}; // adds plurals

        $format = array();
        if ($interval->y !== 0) {
            $format[] = "%y " . $doPlural($interval->y, "an");
        }
        if ($interval->m !== 0) {
            $format[] = "%m mois";
        }
        if ($interval->d !== 0) {
            $format[] = "%d " . $doPlural($interval->d, "jour");
        }
        if ($interval->h !== 0) {
            $format[] = "%h " . $doPlural($interval->h, "heure");
        }
        if ($interval->i !== 0) {
            $format[] = "%i " . $doPlural($interval->i, "minute");
        }
        if ($interval->s !== 0) {
            if (!count($format)) {
                return "Il y a moins d'une minute";
            } else {
                $format[] = "%s " . $doPlural($interval->s, "seconde");
            }
        }

        // We use the two biggest parts
        if (count($format) > 1) {
            $format = array_shift($format) . " et " . array_shift($format);
        } else {
            $format = array_pop($format);
        }

        // Prepend 'since ' or whatever you like
        return $interval->format('Il y a ' . $format);
    }

    public function getSlackUser($userId)
    {
        return $this->slackManager->getUserInfo($userId);
    }
}