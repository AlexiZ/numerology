<?php

namespace ExtranetBundle\Services;

use Doctrine\Common\Persistence\ManagerRegistry;
use ExtranetBundle\Entity\Analysis as Analysis;
use ExtranetBundle\Entity\Definition;
use ExtranetBundle\Entity\Number;
use Symfony\Component\Translation\TranslatorInterface;

class Numerologie
{
    const LETTERS_LIMIT_VALUE = 8;

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

    private static $numberExceptions = [
        1,2,3,4,5,6,7,8,9,11,22
    ];

    /**
     * @var Geocoding
     */
    private $geocoding;

    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    private static $averageLettersValues = [
        'physical' => 25,
        'emotional' => 30,
        'brain' => 27,
        'intuitive' => 13
    ];

    public function __construct(Geocoding $geocoding, ManagerRegistry $registry, TranslatorInterface $translator)
    {
        $this->geocoding = $geocoding;
        $this->registry = $registry;
        $this->translator = $translator;
    }

    public function exportData(Analysis $subject)
    {
        return [
            'identity' => [
                'inheritedNumber' => $this->getInheritedNumber($subject),
                'dutyNumber' => $this->getDutyNumber($subject),
                'socialNumber' => $this->getSocialNumber($subject),
                'structureNumber' => $this->getStructureNumber($subject),
                'globalNumber' => $this->getGlobalNumber($subject),
            ],
            'lifePath' => [
                'lifePathNumber' => $this->getLifePathNumber($subject),
                'idealNumber' => $this->getIdealNumber($subject),
                'majorTurnNumber' => $this->getMajorTurnNumber($subject),
                'personalYearNowNumber' => $this->getPersonnalYearNowNumber($subject),
                'astrologicalNumber' => $this->getAstrologicalNumber($subject),
                'veryShortTermNumber' => $this->getVeryShortTermNumber($subject),
                'shortTermNumber' => $this->getShortTermNumber($subject),
                'meanTermNumber' => $this->getMeanTermNumber($subject),
                'longTermNumber' => $this->getLongTermNumber($subject),
                'secretNumber' => $this->getSecretNumber($subject),
            ],
            'geolocation' => $this->geocoding->getGeolocation($subject->getBirthPlace(), true),
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
        $total = $this->totalNumber($type, $word);

        return $total > 0 ? $this->reduceNumber((string) $total) : '-';
    }

    protected function reduceNumber($stringN, $exceptions = [])
    {
        if (empty($exceptions)) {
            $exceptions = self::$numberExceptions;
        }

        if (in_array($stringN, $exceptions)) {
            return (int) $stringN;
        }

        $reduction = 0;
        for ($i = 0; $i < strlen($stringN); $i++) {
            $reduction += (int) $stringN[$i];
        }

        if (in_array($reduction, $exceptions)) {
            return (int) $reduction;
        }

        return $this->reduceNumber((string) $reduction, $exceptions);
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

    public function getInheritedNumber(Analysis $subject)
    {
        return $this->reducedTotalNumber('letter', $subject->getUseName().$subject->getBirthName().implode('', $subject->getPseudos()));
    }

    public function getDutyNumber(Analysis $subject)
    {
        return $this->reducedTotalNumber('letter', $subject->getFirstName().implode('', $subject->getOtherFirstnames()));
    }

    public function getSocialNumber(Analysis $subject)
    {
        return $this->reducedTotalNumber('vowel', $subject->getUseName().$subject->getBirthName().implode('', $subject->getPseudos()).$subject->getFirstName().implode('', $subject->getOtherFirstnames()));
    }

    public function getStructureNumber(Analysis $subject)
    {
        return $this->reducedTotalNumber('consonant', $subject->getUseName().$subject->getBirthName().implode('', $subject->getPseudos()).$subject->getFirstName().implode('', $subject->getOtherFirstnames()));
    }

    public function getGlobalNumber(Analysis $subject)
    {
        return $this->addNumberDigits(
            (int) $this->reducedTotalNumber('letter', $subject->getUseName().$subject->getBirthName().implode('', $subject->getPseudos()))
            + $this->reducedTotalNumber('letter', $subject->getFirstName().implode('', $subject->getOtherFirstnames()))
        );
    }

    public function getDefinition($context)
    {
        /** @var Definition $definition */
        $definition = $this->registry->getRepository(Definition::class)->findOneByName($context);
        if ($definition) {
            return $definition->getContent();
        }

        return null;
    }

    public function getAnalysis($value, $context)
    {
        /** @var Number $number */
        $number = $this->registry->getRepository(Number::class)->findOneByValue($value);
        if ($number) {
            return $number->get($context);
        }

        return null;
    }

    public function getLettersNumberOccurrences($word)
    {
        $letters = array_fill(1, 9, 0);

        for ($i = 0; $i < strlen($word); $i++) {
            $letters[$this->getLetter($word[$i])]++;
        }

        return array_map(function($a) {
            return $a == 0 ? '-' : $a;
        }, $letters);
    }

    public function getAverageLetterNumber($word)
    {
        return strlen($word) / 9;
    }

    public function getMissingLettersNumbers($word)
    {
        $occurrences = $this->getLettersNumberOccurrences($word);

        return array_filter(array_map(function($a, $b) {
            return $a == '-' ? $b : null;
        }, $occurrences, array_keys($occurrences)));
    }

    public function getWeakLettersNumbers($word)
    {
        $occurrences = array_map(function($a) {
            return $a == '-' ? 10 : $a;
        }, $this->getLettersNumberOccurrences($word));

        return array_filter(array_map(function($a, $b) use ($word) {
            return $a < round($this->getAverageLetterNumber($word), 0, PHP_ROUND_HALF_DOWN) ? $b : null;
        }, $occurrences, array_keys($occurrences)));
    }

    public function getStrongLettersNumbers($word)
    {
        $occurrences = $this->getLettersNumberOccurrences($word);

        return array_filter(array_map(function($a, $b) use ($word) {
            return $a > round($this->getAverageLetterNumber($word), 0, PHP_ROUND_HALF_UP) ? $b : null;
        }, $occurrences, array_keys($occurrences)));
    }

    public function getAverageLettersNumbers($word)
    {
        $occurrences = $this->getLettersNumberOccurrences($word);

        return array_filter(array_map(function($a, $b) use ($word) {
            return $a == round($this->getAverageLetterNumber($word), 0, PHP_ROUND_HALF_DOWN) || $a == round($this->getAverageLetterNumber($word), 0, PHP_ROUND_HALF_UP) ? $b : null;
        }, $occurrences, array_keys($occurrences)));
    }

    public function getLifePathNumber(Analysis $subject)
    {
        return $this->reduceNumber($subject->getUTCBirthDate()->format('dmY'));
    }

    public function getIdealNumber(Analysis $subject)
    {
        return $this->reduceNumber($subject->getUTCBirthDate()->format('dmYHi'));
    }

    public function getMajorTurnNumber(Analysis $subject)
    {
        return (int) $subject->getUTCBirthDate()->format('d') + (int) $subject->getUTCBirthDate()->format('m');
    }

    public function getPersonnalYearNowNumber(Analysis $subject)
    {
        return $this->reduceNumber($subject->getUTCBirthDate()->format('dm').date('Y'), [1, 2, 3, 4, 5, 6, 7, 8, 9]);
    }

    public function getAstrologicalNumber(Analysis $subject)
    {
        $cityCoordinates = str_replace([',', '.'], '', $this->geocoding->getGeolocation($subject->getBirthPlace()));

        return $this->reduceNumber($subject->getUTCBirthDate()->format('dmYHi').$cityCoordinates, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]);
    }

    public function getVeryShortTermNumber(Analysis $subject)
    {
        return $this->reduceNumber($subject->getUTCBirthDate()->format('Hi'));
    }

    public function getShortTermNumber(Analysis $subject)
    {
        return $this->reduceNumber($subject->getUTCBirthDate()->format('d'));
    }

    public function getMeanTermNumber(Analysis $subject)
    {
        return $this->reduceNumber($subject->getUTCBirthDate()->format('m'));
    }

    public function getLongTermNumber(Analysis $subject)
    {
        return $this->reduceNumber($subject->getUTCBirthDate()->format('Y'));
    }

    public function getSecretNumber(Analysis $subject)
    {
        return $this->reduceNumber((string) ($this->getGlobalNumber($subject) + $this->getIdealNumber($subject)));
    }

    public function getIdentityDetails(Analysis $subject)
    {
        $a = [];
        $b = [
            $this->getGlobalNumber($subject) => 'global',
            $this->getInheritedNumber($subject) => 'inherited',
            $this->getDutyNumber($subject) => 'duty',
            $this->getSocialNumber($subject) => 'social',
            $this->getStructureNumber($subject) => 'structure',
        ];
        $missing = array_values($this->getMissingLettersNumbers(str_replace(' ', '', $subject->getFullNames())));
        $c = [
            'strong' => array_values($this->getStrongLettersNumbers(str_replace(' ', '', $subject->getFullNames()))),
            'missingIn' => array_values(array_intersect(array_keys($b), $missing)),
            'weak' => array_values($this->getWeakLettersNumbers(str_replace(' ', '', $subject->getFullNames()))),
        ];

        foreach ($c as $i => $j) {
            if (!isset($a[$i])) {
                $a[$i] = [];
            }

            foreach ($j as $k) {
                if (isset($b[$k])) {
                    $a[$i][$k] = $b[$k];
                }
            }
        }

        $d = [];
        foreach ($a as $l => $m) {
            foreach ($m as $n => $o) {
                $d[$l][$n] = $this->translator->trans('analysis.show.vibrations.identity.' . $o);
            }
        }

        return array_merge($d, ['missingOut' => array_values(array_diff($missing, array_keys($b)))]);
    }

    public function getLettersChartValues(Analysis $subject)
    {
        $values = [
            'public' => [
                'physical' => (int) round($this->count('physical', $subject->getPublicName()) / strlen($subject->getPublicName()) * 100, 0),
                'emotional' => (int) round($this->count('emotional', $subject->getPublicName()) / strlen($subject->getPublicName()) * 100, 0),
                'brain' => (int) round($this->count('brain', $subject->getPublicName()) / strlen($subject->getPublicName()) * 100, 0),
                'intuitive' => (int) round($this->count('intuitive', $subject->getPublicName()) / strlen($subject->getPublicName()) * 100, 0),
            ],
            'private' => [
                'physical' => (int) round($this->count('physical', $subject->getFullNames()) / strlen($subject->getFullNames()) * 100, 0),
                'emotional' => (int) round($this->count('emotional', $subject->getFullNames()) / strlen($subject->getFullNames()) * 100, 0),
                'brain' => (int) round($this->count('brain', $subject->getFullNames()) / strlen($subject->getFullNames()) * 100, 0),
                'intuitive' => (int) round($this->count('intuitive', $subject->getFullNames()) / strlen($subject->getFullNames()) * 100, 0),
            ],
        ];

        $return = [];
        foreach ($values as $type => $data) {
            foreach ($data as $name => $value) {
                if (!isset($return[$type])) {
                    $return[$type] = [];
                }

                $return[$type][] = $value - self::$averageLettersValues[$name];
            }
        }

        return array_merge($return);
    }

    public function getLettersDifferences(Analysis $subject)
    {
        $differences = [];

        foreach (['public', 'private'] as $property) {
            $lettersChartValues = $this->getLettersChartValues($subject);

            foreach ([0 => 'physical', 1 => 'emotional', 2 => 'brain', 3 => 'intuitive'] as $index => $type) {
                if (abs($lettersChartValues[$property][$index]) >= self::LETTERS_LIMIT_VALUE) {
                    $t = $lettersChartValues[$property][$index] > 0 ? self::LETTERS_LIMIT_VALUE : (- self::LETTERS_LIMIT_VALUE);
                    $differences[$property][$type] = $lettersChartValues[$property][$index] - $t;

                    if ($t < 0 && 0 === $differences[$property][$type]) {
                        $differences[$property][$type]--;
                    }
                }
            }
        }

        return $differences;
    }

    public function getLettersSynthesis(Analysis $subject)
    {
        $input = $this->getLettersDifferences($subject);
        $output = [];

        foreach ($input as $property => $data) {
            $output[$property] = array_flip(array_filter($data, function ($a) {
                return $a >= 0;
            }));
            krsort($output[$property]);
            $output[$property] = array_values($output[$property]);
        }

        return $output;
    }
}