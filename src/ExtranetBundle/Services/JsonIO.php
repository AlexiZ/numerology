<?php

namespace ExtranetBundle\Services;

use ExtranetBundle\Entity\Analysis;
use ExtranetBundle\Security\User;
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
     * @var string
     */
    private $archivesFolder;

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
        $this->archivesFolder = $this->kernel->getRootDir().'/Resources/archives/';

        if ($this->tokenStorage->getToken() && $this->tokenStorage->getToken()->getUser() instanceof User) {
            $this->storageFileFolder .= $this->tokenStorage->getToken()->getUser()->getId() . '/';
        } else {
            $this->storageFileFolder .= 'other/';
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
        file_put_contents($folder.$filename, $content);
    }

    public function deleteJson($filename)
    {
        $folder = $this->storageFileFolder.($this->authorizationChecker->isGranted('ROLE_ADMIN') ? '../*/' : '').'*.json';
        foreach (glob($folder) as $fullname) {
            $explodedFilename = explode('/', $fullname);
            $shortFilename = $explodedFilename[count($explodedFilename) - 1];

            if ($shortFilename === $filename) {
                $destFolder = $this->archivesFolder . $explodedFilename[count($explodedFilename) - 2] . '/';

                if (!is_dir($destFolder)) {
                    mkdir($destFolder);
                }
                rename($fullname, $destFolder . $filename);

                return true;
            }
        }

        return false;
    }

    public function writeNumerology(Analysis $subject, array $data)
    {
        $this->writeJson($this->storageFileFolder, $subject->getFileName(true), json_encode($subject->serialize($data)));

        return $subject->getFileName(true);
    }

    public function readHistoryFolder($full = false)
    {
        $history = [];
        $historyFolder = $this->storageFileFolder;
        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $explodedFilename = explode('/', $historyFolder);
            $explodedFilename[count($explodedFilename) - 2] = '*';
            $historyFolder = implode('/', $explodedFilename);
        }

        foreach (glob($historyFolder.'*.json') as $filename) {
            $explodedFilename = explode('/', $filename);
            $shortFilename = $explodedFilename[count($explodedFilename) - 1];
            if ($full) {
                $history[$shortFilename] = file_get_contents($filename);
            } else {
                $history[$shortFilename] = filemtime($filename);
            }
        }

        uasort($history, function ($a, $b) {
            return $b <=> $a;
        });
        return $history;
    }

    public function readAnalysis($number, $context)
    {
        return $this->readJson($this->dataFileFolder.'numbers/'.$number)[$context];
    }

    public function readDefinition($context)
    {
        return $this->readJson($this->dataFileFolder.'definitions/'.$context)['definition'];
    }
}