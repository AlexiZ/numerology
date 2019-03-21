<?php

namespace AppBundle\Twig;

use AppBundle\Services\Numerologie;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class NumerologieExtension extends AbstractExtension
{
    /**
     * @var Numerologie
     */
    private $numerologie;

    public function __construct(Numerologie $numerologie)
    {
        $this->numerologie = $numerologie;
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('lettre', array($this, 'letterNumber')),
            new TwigFilter('totalLettre', array($this, 'totalLetterNumber')),
            new TwigFilter('totalReduitLettre', array($this, 'reducedTotalLetterNumber')),

            new TwigFilter('voyelle', array($this, 'vowelNumber')),
            new TwigFilter('totalVoyelle', array($this, 'totalVowelNumber')),
            new TwigFilter('totalReduitVoyelle', array($this, 'reducedTotalVowelNumber')),

            new TwigFilter('consonne', array($this, 'consonantNumber')),
            new TwigFilter('totalConsonne', array($this, 'totalConsonantNumber')),
            new TwigFilter('totalReduitConsonne', array($this, 'reducedTotalConsonantNumber')),

            new TwigFilter('lettrePhysique', array($this, 'physicalNumber')),
            new TwigFilter('totalLettrePhysique', array($this, 'totalPhysicalNumber')),

            new TwigFilter('lettreEmotive', array($this, 'emotionalNumber')),
            new TwigFilter('totalLettreEmotive', array($this, 'totalEmotionalNumber')),

            new TwigFilter('lettreCerebrale', array($this, 'brainNumber')),
            new TwigFilter('totalLettreCerebrale', array($this, 'totalBrainNumber')),

            new TwigFilter('lettreIntuitive', array($this, 'intuitiveNumber')),
            new TwigFilter('totalLettreIntuitive', array($this, 'totalIntuitiveNumber')),

            new TwigFilter('getSubject', array($this, 'getSubject')),

            new TwigFilter('addNumberDigits', array($this, 'addNumberDigits')),

            new TwigFilter('getAnalysis', array($this, 'getAnalysis')),
        );
    }

    /**
     * LETTER
     */

    public function letterNumber($letter)
    {
        return $this->numerologie->number('letter', $letter);
    }

    public function totalLetterNumber($word)
    {
        return $this->numerologie->totalNumber('letter', $word);
    }

    public function reducedTotalLetterNumber($word)
    {
        return $this->numerologie->reducedTotalNumber('letter', $word);
    }

    public function addNumberDigits($number)
    {
        return $this->numerologie->addNumberDigits($number);
    }


    /**
     * VOWEL
     */

    public function vowelNumber($letter)
    {
        return $this->numerologie->number('vowel', $letter);
    }

    public function totalVowelNumber($word)
    {
        return $this->numerologie->totalNumber('vowel', $word);
    }

    public function reducedTotalVowelNumber($word)
    {
        return $this->numerologie->reducedTotalNumber('vowel', $word);
    }


    /**
     * CONSONANT
     */

    public function consonantNumber($letter)
    {
        return $this->numerologie->number('consonant', $letter);
    }

    public function totalConsonantNumber($word)
    {
        return $this->numerologie->totalNumber('consonant', $word);
    }

    public function reducedTotalConsonantNumber($word)
    {
        return $this->numerologie->reducedTotalNumber('consonant', $word);
    }


    /**
     * TRANSCRIPTION
     */

    public function physicalNumber($letter)
    {
        return $this->numerologie->is('physical', $letter);
    }

    public function totalPhysicalNumber($word)
    {
        return $this->numerologie->count('physical', $word);
    }

    public function emotionalNumber($letter)
    {
        return $this->numerologie->is('emotional', $letter);
    }

    public function totalEmotionalNumber($word)
    {
        return $this->numerologie->count('emotional', $word);
    }

    public function brainNumber($letter)
    {
        return $this->numerologie->is('brain', $letter);
    }

    public function totalBrainNumber($word)
    {
        return $this->numerologie->count('brain', $word);
    }

    public function intuitiveNumber($letter)
    {
        return $this->numerologie->is('intuitive', $letter);
    }

    public function totalIntuitiveNumber($word)
    {
        return $this->numerologie->count('intuitive', $word);
    }

    /**
     * SUBJECT
     */

    public function getSubject($md5)
    {
        return $this->numerologie->getSubject($md5);
    }

    public function getAnalysis($number, $context)
    {
        return $this->numerologie->getAnalysis($number, $context);
    }
}