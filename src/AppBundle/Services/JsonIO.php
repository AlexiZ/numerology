<?php

namespace AppBundle\Services;

use AppBundle\Entity\Numerologie;
use AppBundle\Security\User;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

class JsonIO
{
    /**
     * @var string
     */
    private $storageFileFolder;

    /**
     * @var string
     */
    private $dataFileFolder;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var AuthorizationChecker
     */
    private $authorizationChecker;
    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    public function __construct(KernelInterface $kernel, AuthorizationChecker $authorizationChecker, TokenStorage $tokenStorage)
    {
        $this->kernel = $kernel;
        $this->dataFileFolder = $this->kernel->getRootDir().'/Resources/data/';
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->storageFileFolder = $this->kernel->getRootDir().'/Resources/storage/';

        if ($this->tokenStorage->getToken() && $this->tokenStorage->getToken()->getUser() instanceof User) {
            if (!$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
                $this->storageFileFolder .= $this->tokenStorage->getToken()->getUser()->getId() . '/';
            } else {
                $this->storageFileFolder .= '*/';
            }
        }
    }

    public function readJson($filename = null, $json = true)
    {
        if (5 <= strlen($filename) || '.json' !== substr($filename, -5, 5)) {
            $filename .= '.json';
        }

        $content = file_get_contents($filename);

        return $json ? json_decode($content,TRUE) : $content;
    }

    public function writeJson($folder, $filename, $content)
    {
        if (!is_dir($folder)) {
            mkdir($folder);
        }
        if (!file_exists($folder.$filename)) {
            file_put_contents($folder.$filename, $content);
        }
    }

    public function writeNumerology(Numerologie $subject, array $data)
    {
        $this->writeJson($this->storageFileFolder, $subject->getFileName(true), json_encode($subject->serialize($data)));

        return $subject->getFileName(true);
    }

    public function readHistoryFolder($full = false)
    {
        $history = [];
        foreach (glob($this->storageFileFolder.'*.json') as $filename) {
            $explodedFilename = explode('/', $filename);
            $shortFilename = $explodedFilename[count($explodedFilename) - 1];
            if ($full) {
                $history[$shortFilename] = file_get_contents($filename);
            } else {
                $history[$shortFilename] = strftime("Le %d/%m/%Y à %H:%m", filemtime($filename));
            }
        }

        return $history;
    }

    public function readAnalysis($number, $context)
    {
        return $this->readJson($this->dataFileFolder.$number)[$context];
    }
}