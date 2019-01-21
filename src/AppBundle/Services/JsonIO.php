<?php

namespace AppBundle\Services;

use AppBundle\Entity\Numerologie;
use Symfony\Component\HttpKernel\KernelInterface;

class JsonIO
{
    /**
     * @var string
     */
    private $storageFileFolder;

    /**
     * @var KernelInterface
     */
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->storageFileFolder = $this->kernel->getRootDir().'/Resources/storage/';
    }

    public function readJson($filename = null)
    {
        return json_decode(file_get_contents($this->storageFileFolder.$filename),TRUE);
    }

    public function writeJson(Numerologie $subject)
    {
        $filename = $this->storageFileFolder.$subject->getFileName(true);
        if (!file_exists($filename)) {
            file_put_contents($filename, json_encode($subject->serialize()));
        }

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
}