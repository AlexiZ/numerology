<?php

namespace ExtranetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Number
 *
 * @ORM\Table(name="number")
 * @ORM\Entity(repositoryClass="ExtranetBundle\Repository\NumberRepository")
 */
class Number
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255, unique=true)
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(name="inherited", type="text", nullable=true)
     */
    private $inherited;

    /**
     * @var string
     *
     * @ORM\Column(name="duty", type="text", nullable=true)
     */
    private $duty;

    /**
     * @var string
     *
     * @ORM\Column(name="social", type="text", nullable=true)
     */
    private $social;

    /**
     * @var string
     *
     * @ORM\Column(name="structure", type="text", nullable=true)
     */
    private $structure;

    /**
     * @var string
     *
     * @ORM\Column(name="global", type="text", nullable=true)
     */
    private $global;

    /**
     * @var string
     *
     * @ORM\Column(name="lifePath", type="text", nullable=true)
     */
    private $lifePath;

    /**
     * @var string
     *
     * @ORM\Column(name="ideal", type="text", nullable=true)
     */
    private $ideal;

    /**
     * @var string
     *
     * @ORM\Column(name="personalYearNow", type="text", nullable=true)
     */
    private $personalYearNow;

    /**
     * @var string
     *
     * @ORM\Column(name="secret", type="text", nullable=true)
     */
    private $secret;

    /**
     * @var string
     *
     * @ORM\Column(name="astrological", type="text", nullable=true)
     */
    private $astrological;

    /**
     * @var string
     *
     * @ORM\Column(name="veryShortTerm", type="text", nullable=true)
     */
    private $veryShortTerm;

    /**
     * @var string
     *
     * @ORM\Column(name="shortTerm", type="text", nullable=true)
     */
    private $shortTerm;

    /**
     * @var string
     *
     * @ORM\Column(name="meanTerm", type="text", nullable=true)
     */
    private $meanTerm;

    /**
     * @var string
     *
     * @ORM\Column(name="longTerm", type="text", nullable=true)
     */
    private $longTerm;

    /**
     * @var string
     *
     * @ORM\Column(name="missing", type="text", nullable=true)
     */
    private $missing;

    /**
     * @var string
     *
     * @ORM\Column(name="weak", type="text", nullable=true)
     */
    private $weak;

    /**
     * @var string
     *
     * @ORM\Column(name="strong", type="text", nullable=true)
     */
    private $strong;

    /**
     * @var string
     *
     * @ORM\Column(name="average", type="text", nullable=true)
     */
    private $average;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set value.
     *
     * @param string $value
     *
     * @return Number
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set inherited.
     *
     * @param string $inherited
     *
     * @return Number
     */
    public function setInherited($inherited)
    {
        $this->inherited = $inherited;

        return $this;
    }

    /**
     * Get inherited.
     *
     * @return string
     */
    public function getInherited()
    {
        return $this->inherited;
    }

    /**
     * Set duty.
     *
     * @param string $duty
     *
     * @return Number
     */
    public function setDuty($duty)
    {
        $this->duty = $duty;

        return $this;
    }

    /**
     * Get duty.
     *
     * @return string
     */
    public function getDuty()
    {
        return $this->duty;
    }

    /**
     * Set social.
     *
     * @param string $social
     *
     * @return Number
     */
    public function setSocial($social)
    {
        $this->social = $social;

        return $this;
    }

    /**
     * Get social.
     *
     * @return string
     */
    public function getSocial()
    {
        return $this->social;
    }

    /**
     * Set structure.
     *
     * @param string $structure
     *
     * @return Number
     */
    public function setStructure($structure)
    {
        $this->structure = $structure;

        return $this;
    }

    /**
     * Get structure.
     *
     * @return string
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * Set global.
     *
     * @param string $global
     *
     * @return Number
     */
    public function setGlobal($global)
    {
        $this->global = $global;

        return $this;
    }

    /**
     * Get global.
     *
     * @return string
     */
    public function getGlobal()
    {
        return $this->global;
    }

    /**
     * Set lifePath.
     *
     * @param string $lifePath
     *
     * @return Number
     */
    public function setLifePath($lifePath)
    {
        $this->lifePath = $lifePath;

        return $this;
    }

    /**
     * Get lifePath.
     *
     * @return string
     */
    public function getLifePath()
    {
        return $this->lifePath;
    }

    /**
     * Set ideal.
     *
     * @param string $ideal
     *
     * @return Number
     */
    public function setIdeal($ideal)
    {
        $this->ideal = $ideal;

        return $this;
    }

    /**
     * Get ideal.
     *
     * @return string
     */
    public function getIdeal()
    {
        return $this->ideal;
    }

    /**
     * Set personalYearNow.
     *
     * @param string $personalYearNow
     *
     * @return Number
     */
    public function setPersonalYearNow($personalYearNow)
    {
        $this->personalYearNow = $personalYearNow;

        return $this;
    }

    /**
     * Get personalYearNow.
     *
     * @return string
     */
    public function getPersonalYearNow()
    {
        return $this->personalYearNow;
    }

    /**
     * Set secret.
     *
     * @param string $secret
     *
     * @return Number
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * Get secret.
     *
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Set astrological.
     *
     * @param string $astrological
     *
     * @return Number
     */
    public function setAstrological($astrological)
    {
        $this->astrological = $astrological;

        return $this;
    }

    /**
     * Get astrological.
     *
     * @return string
     */
    public function getAstrological()
    {
        return $this->astrological;
    }

    /**
     * Set veryShortTerm.
     *
     * @param string $veryShortTerm
     *
     * @return Number
     */
    public function setVeryShortTerm($veryShortTerm)
    {
        $this->veryShortTerm = $veryShortTerm;

        return $this;
    }

    /**
     * Get veryShortTerm.
     *
     * @return string
     */
    public function getVeryShortTerm()
    {
        return $this->veryShortTerm;
    }

    /**
     * Set shortTerm.
     *
     * @param string $shortTerm
     *
     * @return Number
     */
    public function setShortTerm($shortTerm)
    {
        $this->shortTerm = $shortTerm;

        return $this;
    }

    /**
     * Get shortTerm.
     *
     * @return string
     */
    public function getShortTerm()
    {
        return $this->shortTerm;
    }

    /**
     * Set meanTerm.
     *
     * @param string $meanTerm
     *
     * @return Number
     */
    public function setMeanTerm($meanTerm)
    {
        $this->meanTerm = $meanTerm;

        return $this;
    }

    /**
     * Get meanTerm.
     *
     * @return string
     */
    public function getMeanTerm()
    {
        return $this->meanTerm;
    }

    /**
     * Set longTerm.
     *
     * @param string $longTerm
     *
     * @return Number
     */
    public function setLongTerm($longTerm)
    {
        $this->longTerm = $longTerm;

        return $this;
    }

    /**
     * Get longTerm.
     *
     * @return string
     */
    public function getLongTerm()
    {
        return $this->longTerm;
    }

    /**
     * Set missing.
     *
     * @param string $missing
     *
     * @return Number
     */
    public function setMissing($missing)
    {
        $this->missing = $missing;

        return $this;
    }

    /**
     * Get missing.
     *
     * @return string
     */
    public function getMissing()
    {
        return $this->missing;
    }

    /**
     * Set weak.
     *
     * @param string $weak
     *
     * @return Number
     */
    public function setWeak($weak)
    {
        $this->weak = $weak;

        return $this;
    }

    /**
     * Get weak.
     *
     * @return string
     */
    public function getWeak()
    {
        return $this->weak;
    }

    /**
     * Set strong.
     *
     * @param string $strong
     *
     * @return Number
     */
    public function setStrong($strong)
    {
        $this->strong = $strong;

        return $this;
    }

    /**
     * Get strong.
     *
     * @return string
     */
    public function getStrong()
    {
        return $this->strong;
    }

    /**
     * Set average.
     *
     * @param string $average
     *
     * @return Number
     */
    public function setAverage($average)
    {
        $this->average = $average;

        return $this;
    }

    /**
     * Get average.
     *
     * @return string
     */
    public function getAverage()
    {
        return $this->average;
    }
}
