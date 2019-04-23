<?php

namespace ExtranetBundle\Entity;

/**
 * Numerologie
 */
class Numerologie
{
    /**
     * @var string
     */
    private $birthName;

    /**
     * @var string
     */
    private $useName;

    /**
     * @var string
     */
    private $firstname;

    /**
     * @var array
     */
    private $otherFirstnames;

    /**
     * @var array
     */
    private $pseudos = [];

    /**
     * @var \Datetime
     */
    private $birthDate;

    /**
     * @var string
     */
    private $birthPlace;

    private $data = [];

    public function __toString()
    {
        return $this->getFirstname() . ' ' . ($this->getOtherFirstnames() ? implode(' ', $this->getOtherFirstnames()) . ' ' : '') . (!empty($this->getUseName()) ? $this->getUseName() : $this->getBirthName());
    }

    public function serialize($data)
    {
        return [
            'birthName' => $this->cleanString($this->getBirthName()),
            'useName' => $this->cleanString($this->getUseName()),
            'firstname' => $this->cleanString($this->getFirstname()),
            'otherFirstnames' => $this->cleanString(implode(',', $this->getOtherFirstnames())),
            'pseudos' => $this->cleanString(implode(',', $this->getPseudos())),
            'birthDate' => $this->getBirthDate() ? $this->getBirthDate()->format('d-m-Y H:i') : '',
            'birthPlace' => $this->cleanString($this->getBirthPlace()),
            'data' => $data,
        ];
    }

    public function unserialize(array $data)
    {
        $this->setBirthName($data['birthName'] ?? null);
        $this->setUseName($data['useName'] ?? null);
        $this->setFirstname($data['firstname']);
        if (isset($data['otherFirstnames'])) {
            foreach (explode(',', $data['otherFirstnames']) as $other) if ($other) {
                $this->addOtherFirstname($other);
            }
        }
        if (isset($data['pseudos'])) {
            foreach (explode(',', $data['pseudos']) as $pseudo) if ($pseudo) {
                $this->addPseudo($pseudo);
            }
        }

        $this->setBirthDate(new \DateTime($data['birthDate'] ?? time()));
        $this->setBirthPlace($data['birthPlace']);
        $this->setData($data['data']);

        return $this;
    }

    protected function cleanString($text) {
        $utf8 = array(
            '/[áàâãªä]/u'   =>   'a',
            '/[ÁÀÂÃÄ]/u'    =>   'A',
            '/[ÍÌÎÏ]/u'     =>   'I',
            '/[íìîï]/u'     =>   'i',
            '/[éèêë]/u'     =>   'e',
            '/[ÉÈÊË]/u'     =>   'E',
            '/[óòôõºö]/u'   =>   'o',
            '/[ÓÒÔÕÖ]/u'    =>   'O',
            '/[úùûü]/u'     =>   'u',
            '/[ÚÙÛÜ]/u'     =>   'U',
            '/ç/'           =>   'c',
            '/Ç/'           =>   'C',
            '/ñ/'           =>   'n',
            '/Ñ/'           =>   'N',
            '/–/'           =>   '',
            '/-/'           =>   '',
            '/[’‘‹›‚]/u'    =>   '',
            '/[“”«»„]/u'    =>   '',
            '/  /'          =>   '',
        );

        return strtoupper(preg_replace(array_keys($utf8), array_values($utf8), $text));
    }

    public function getFileName($extension = false)
    {
        $filename = implode('_', array_merge(
            [($this->cleanString($this->getUseName()) ?? $this->cleanString($this->getBirthName()))],
            [$this->cleanString($this->getFirstname())]
        ));

        return md5($filename) . ($extension ? '.json' : '');
    }

    public function getPublicName()
    {
        return !empty($this->getPseudos()) && !empty($this->getPseudos()[0]) ? implode(',', $this->getPseudos()) : ((!empty($this->getUseName()) ? $this->getUseName() : $this->getBirthName()) . $this->getFirstname());
    }

    public function getFullNames()
    {
        return (!empty($this->getUseName()) ? $this->getUseName() : '') . $this->getBirthName() . $this->getFirstname() . ($this->getOtherFirstnames() ? implode('', $this->getOtherFirstnames()) : '') . ($this->getPseudos() ? str_replace(' ', '', implode('', $this->getPseudos())) : '');
    }

    /**
     * @return string
     */
    public function getBirthName()
    {
        return $this->birthName;
    }

    /**
     * @param string $birthName
     *
     * @return Numerologie
     */
    public function setBirthName($birthName)
    {
        $this->birthName = $birthName;

        return $this;
    }

    /**
     * @return string
     */
    public function getUseName()
    {
        return $this->useName;
    }

    /**
     * @param string $useName
     *
     * @return Numerologie
     */
    public function setUseName($useName)
    {
        $this->useName = $useName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     *
     * @return Numerologie
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return array
     */
    public function getOtherFirstnames()
    {
        return $this->otherFirstnames;
    }

    /**
     * @param array $otherFirstnames
     *
     * @return Numerologie
     */
    public function setOtherFirstnames($otherFirstnames)
    {
        $this->otherFirstnames = $otherFirstnames;

        return $this;
    }

    /**
     * @param string $otherFirstname
     *
     * @return Numerologie
     */
    public function addOtherFirstname($otherFirstname)
    {
        $this->otherFirstnames[] = $otherFirstname;

        return $this;
    }

    /**
     * @return array
     */
    public function getPseudos()
    {
        return $this->pseudos;
    }

    /**
     * @param array $pseudos
     *
     * @return Numerologie
     */
    public function setPseudos($pseudos)
    {
        $this->pseudos = $pseudos;

        return $this;
    }

    /**
     * @param string $pseudo
     *
     * @return Numerologie
     */
    public function addPseudo($pseudo)
    {
        $this->pseudos[] = $pseudo;

        return $this;
    }

    /**
     * @return \Datetime
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * @param \Datetime $birthDate
     *
     * @return Numerologie
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getBirthPlace()
    {
        return $this->birthPlace;
    }

    /**
     * @param string $birthPlace
     *
     * @return Numerologie
     */
    public function setBirthPlace($birthPlace)
    {
        $this->birthPlace = $birthPlace;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return Numerologie
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
