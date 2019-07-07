<?php

namespace ExtranetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="ExtranetBundle\Repository\AnalysisRepository")
 * @ORM\Table(name="analysis")
 */
class Analysis
{
    const STATUS_ACTIVE = 'active';
    const STATUS_PENDING = 'pending';
    const STATUS_DELETED = 'deleted';
    const STATUS_CANCELED = 'canceled';
    const LEVEL_FREE = 'free';
    const LEVEL_PREMIUM = 'premium';

    /**
     * @var integer
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $birthName;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $useName;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $firstname;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    private $otherFirstnames;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    private $pseudos = [];

    /**
     * @var \Datetime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $birthDate;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $birthPlace;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    private $data = [];

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $hash;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $userId;

    /**
     * @var \Datetime
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \Datetime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $level;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default" : false})
     */
    private $example;

    public static $statusValues = [
        self::STATUS_ACTIVE,
        self::STATUS_PENDING,
        self::STATUS_CANCELED,
        self::STATUS_DELETED,
    ];

    public static $levelValues = [
        self::LEVEL_FREE,
        self::LEVEL_PREMIUM,
    ];


    public function __construct()
    {
        $this->status = self::STATUS_ACTIVE;
        $this->hash = uniqid();
        $this->setCreatedAt(new \DateTime());
        $this->example = false;
    }

    public function __toString()
    {
        return $this->getFirstname() . ' ' . ($this->getOtherFirstnames() ? implode(' ', $this->getOtherFirstnames()) . ' ' : '') . (!empty($this->getUseName()) ? $this->getUseName() . ' né(e) ' . $this->getBirthName() : $this->getBirthName());
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
     * @return integer
     */
    public function getId()
    {
        return $this->id;
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
     * @return Analysis
     */
    public function setBirthName($birthName)
    {
        $this->birthName = $this->cleanString($birthName);

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
     * @return Analysis
     */
    public function setUseName($useName)
    {
        $this->useName = $this->cleanString($useName);

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
     * @return Analysis
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $this->cleanString($firstname);

        return $this;
    }

    /**
     * @return array
     */
    public function getOtherFirstnames()
    {
        return $this->otherFirstnames ?? [];
    }

    /**
     * @param array $otherFirstnames
     *
     * @return Analysis
     */
    public function setOtherFirstnames($otherFirstnames)
    {
        foreach ($otherFirstnames as $otherFirstname) {
            $this->addOtherFirstname($otherFirstname);
        }

        return $this;
    }

    /**
     * @param string $otherFirstname
     *
     * @return Analysis
     */
    public function addOtherFirstname($otherFirstname)
    {
        $this->otherFirstnames[] = $this->cleanString($otherFirstname);

        return $this;
    }

    /**
     * @return array
     */
    public function getPseudos()
    {
        return $this->pseudos ?? [];
    }

    /**
     * @param array $pseudos
     *
     * @return Analysis
     */
    public function setPseudos($pseudos)
    {
        foreach ($pseudos as $pseudo) {
            $this->addPseudo($pseudo);
        }

        return $this;
    }

    /**
     * @param string $pseudo
     *
     * @return Analysis
     */
    public function addPseudo($pseudo)
    {
        $this->pseudos[] = $this->cleanString($pseudo);

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
     * @return \Datetime
     */
    public function getUTCBirthDate()
    {
        $utcBirthDate = clone $this->birthDate;
        return $utcBirthDate->setTimezone(new \DateTimeZone('UTC'));
    }

    /**
     * @param \Datetime $birthDate
     *
     * @return Analysis
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
     * @return Analysis
     */
    public function setBirthPlace($birthPlace)
    {
        $this->birthPlace = $this->cleanString($birthPlace);

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
     * @return Analysis
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return Analysis
     */
    public function setStatus($status)
    {
        if (in_array($status, self::$statusValues)) {
            $this->status = $status;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     *
     * @return Analysis
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return \Datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \Datetime $createdAt
     *
     * @return Analysis
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \Datetime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \Datetime $updatedAt
     *
     * @return Analysis
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param string $level
     *
     * @return Analysis
     */
    public function setLevel($level)
    {
        if (in_array($level, self::$levelValues)) {
            $this->level = $level;
        }

        return $this;
    }

    public function isPaymentAvailable()
    {
        if ((self::LEVEL_PREMIUM === $this->getLevel() && self::STATUS_PENDING === $this->getStatus()) || (self::LEVEL_FREE === $this->getLevel() && self::STATUS_ACTIVE === $this->getStatus())) {
            return true;
        }

        return false;
    }

    /**
     * Get example.
     *
     * @return bool
     */
    public function isExample()
    {
        return $this->example;
    }

    /**
     * Set example.
     *
     * @param bool $example
     *
     * @return Analysis
     */
    public function setExample($example)
    {
        $this->example = $example;

        return $this;
    }

    public function invertExample()
    {
        if ('0' == $this->example) {
            $this->example = '1';
        } else {
            $this->example = '0';
        }

        return $this;
    }
}
