<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Website
 *
 * @ORM\Table(name="numerologie")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NumerologieRepository")
 */
class Numerologie
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
     * @var array
     *
     * @ORM\Column(name="noms", type="array", nullable=false)
     */
    private $noms;

    /**
     * @var array
     *
     * @ORM\Column(name="prenoms", type="array", nullable=false)
     */
    private $prenoms;

    /**
     * @var string
     *
     * @ORM\Column(name="pseudo", type="string", length=255, nullable=true)
     */
    private $pseudo = null;

    /**
     * @var \Datetime
     *
     * @ORM\Column(name="date_naissance", type="datetime", nullable=false)
     */
    private $dateNaissance;

    /**
     * @var string
     *
     * @ORM\Column(name="lieu_naissance", type="string", length=255, nullable=false)
     */
    private $lieuNaissance;

    public function __toString()
    {
        return implode(' ', $this->getPrenoms()) . ' ' . implode(' ', $this->getNoms());
    }

    public function serialize()
    {
        return [
            'noms' => $this->getNoms(),
            'prenoms' => $this->getPrenoms(),
            'pseudo' => $this->getPseudo(),
            'dateNaissance' => $this->getDateNaissance(),
            'lieuNaissance' => $this->getLieuNaissance(),
        ];
    }

    public function unserialize(array $data)
    {
        foreach ($data as $name => $value) {
            $function = 'set' . ucfirst($name);
            $this->$function($value);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getNoms()
    {
        return $this->noms;
    }

    /**
     * @param array $noms
     * @return Numerologie
     */
    public function setNoms($noms)
    {
        $this->noms = $noms;
        return $this;
    }

    /**
     * @return array
     */
    public function getPrenoms()
    {
        return $this->prenoms;
    }

    /**
     * @param array $prenoms
     * @return Numerologie
     */
    public function setPrenoms($prenoms)
    {
        $this->prenoms = $prenoms;
        return $this;
    }

    /**
     * @return string
     */
    public function getPseudo()
    {
        return $this->pseudo;
    }

    /**
     * @param string|null $pseudo
     * @return Numerologie
     */
    public function setPseudo($pseudo = null)
    {
        $this->pseudo = $pseudo;
        return $this;
    }

    /**
     * @return \Datetime
     */
    public function getDateNaissance()
    {
        return $this->dateNaissance;
    }

    /**
     * @param \Datetime $dateNaissance
     * @return Numerologie
     */
    public function setDateNaissance($dateNaissance)
    {
        $this->dateNaissance = $dateNaissance;
        return $this;
    }

    /**
     * @return string
     */
    public function getLieuNaissance()
    {
        return $this->lieuNaissance;
    }

    /**
     * @param string $lieuNaissance
     * @return Numerologie
     */
    public function setLieuNaissance($lieuNaissance)
    {
        $this->lieuNaissance = $lieuNaissance;
        return $this;
    }
}

