<?php

namespace AppBundle\Services;

use AppBundle\Entity\Numerologie as NumerologieEntity;

class Numerologie
{
    private static $vowels = [
        'A' => 1,
        'E' => 5,
        'I' => 9,
        'O' => 6,
        'U' => 3,
        'Y' => 7,
    ];
    private static $consonants = [
        'B' => 2,
        'C' => 3,
        'D' => 4,
        'F' => 6,
        'G' => 7,
        'H' => 8,
        'J' => 1,
        'K' => 2,
        'L' => 3,
        'M' => 4,
        'N' => 5,
        'P' => 7,
        'Q' => 8,
        'R' => 9,
        'S' => 1,
        'T' => 2,
        'V' => 4,
        'W' => 5,
        'X' => 6,
        'Z' => 8,
    ];

    private static $physical = [
        'D', 'E', 'M', 'W',
    ];
    private static $emotional = [
        'B', 'I', 'O', 'R', 'S', 'T', 'X', 'Z',
    ];
    private static $brain = [
        'A', 'G', 'H', 'J', 'L', 'N', 'P',
    ];
    private static $intuitive = [
        'C', 'F', 'K', 'Q', 'U', 'V', 'Y',
    ];

    /**
     * @var JsonIO
     */
    private $jsonIO;

    public function __construct(JsonIO $jsonIO)
    {
        $this->jsonIO = $jsonIO;
    }

    public function exportData(NumerologieEntity $subject)
    {
        return [
            'identity' => [
                'inheritedNumber' => $this->reducedTotalNumber('letter', $subject->getUseName().$subject->getBirthName()),
                'dutyNumber' => $this->reducedTotalNumber('letter', $subject->getFirstName().implode('', $subject->getOtherFirstnames())),
                'socialNumber' => $this->reducedTotalNumber('vowel', $subject->getUseName().$subject->getBirthName().$subject->getFirstName().implode('', $subject->getOtherFirstnames())),
                'structureNumber' => $this->reducedTotalNumber('consonant', $subject->getUseName().$subject->getBirthName().$subject->getFirstName().implode('', $subject->getOtherFirstnames())),
                'globalNumber' => $this->addNumberDigits(
                    (int) $this->reducedTotalNumber('letter', $subject->getUseName().$subject->getBirthName().$subject->getFirstName())
                    + (int) $this->reducedTotalNumber('vowel', $subject->getUseName().$subject->getBirthName().$subject->getFirstName())
                    + (int) $this->reducedTotalNumber('consonant', $subject->getUseName().$subject->getBirthName().$subject->getFirstName())
                ),
            ],
        ];
    }

    protected function getVowel($letter)
    {
        return self::$vowels[strtoupper($letter)] ?? null;
    }

    protected function getConsonant($letter)
    {
        return self::$consonants[strtoupper($letter)] ?? null;
    }

    protected function getLetter($letter)
    {
        $letters = array_merge(self::$vowels, self::$consonants);

        return $letters[strtoupper($letter)] ?? null;
    }

    protected function isPhysical($letter)
    {
        return in_array($letter, self::$physical);
    }

    protected function isEmotional($letter)
    {
        return in_array($letter, self::$emotional);
    }

    protected function isBrain($letter)
    {
        return in_array($letter, self::$brain);
    }

    protected function isIntuitive($letter)
    {
        return in_array($letter, self::$intuitive);
    }

    public function number($type, $letter)
    {
        $function = 'get' . ucfirst($type);

        return $this->$function($letter);
    }

    public function totalNumber($type, $word)
    {
        $function = 'get' . ucfirst($type);
        $total = 0;
        for ($i = 0; $i < strlen($word); $i++) {
            $total += $this->$function($word[$i]);
        }

        return $total;
    }

    public function reducedTotalNumber($type, $word)
    {
        $total = (string) $this->totalNumber($type, $word);
        $reduction = 0;
        for ($i = 0; $i < strlen($total); $i++) {
            $reduction += $total[$i];
        }
        $total = (string) $reduction;
        if (strlen($total) > 1) {
            $reduction = 0;
            for ($i = 0; $i < strlen($total); $i++) {
                $reduction += $total[$i];
            }
        }

        return $reduction > 0 ? $reduction : '-';
    }

    public function is($type, $letter)
    {
        $function = 'is' . ucfirst($type);

        return $this->$function($letter);
    }

    public function count($type, $word)
    {
        $function = 'is' . ucfirst($type);
        $total = 0;
        for ($i = 0; $i < strlen($word); $i++) {
            $total += $this->$function($word[$i]);
        }

        return $total;
    }

    public function getSubject($md5)
    {
        $subject = null;
        $files = $this->jsonIO->readHistoryFolder(true);
        if (false !== preg_match('/\.*/', $md5)) {
            $md5 = explode('.', $md5)[0];
        }

        foreach ($files as $name => $file) {
            if (false !== preg_match('/\.*/', $name)) {
                $name = explode('.', $name)[0];
            }
            if ($name == $md5) {
                $data = json_decode($file, true);
                $subject = new NumerologieEntity();
                $subject->unserialize($data);
            }
        }

        return $subject;
    }

    public function addNumberDigits($number)
    {
        $digits = (string)$number;
        $total = 0;
        for ($i = 0; $i < strlen($digits); $i++) {
            $total += (int)$digits[$i];
        }

        if (strlen($total) > 1) {
            return $this->addNumberDigits($total);
        }

        return $total;
    }
}