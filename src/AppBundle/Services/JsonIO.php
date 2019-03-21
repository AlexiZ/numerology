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
     * @var string
     */
    private $dataFileFolder;

    /**
     * @var KernelInterface
     */
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->storageFileFolder = $this->kernel->getRootDir().'/Resources/storage/';
        $this->dataFileFolder = $this->kernel->getRootDir().'/Resources/data/';
    }

    public function readJson($filename = null, $json = true)
    {
        if (5 <= strlen($filename) || '.json' !== substr($filename, -5, 5)) {
            $filename .= '.json';
        }

        $content = file_get_contents($filename);

        return $json ? json_decode($content,TRUE) : $content;
    }

    public function writeJson($filename, $content)
    {
        if (!file_exists($filename)) {
            file_put_contents($filename, $content);
        }
    }

    public function writeNumerology(Numerologie $subject, array $data)
    {
        $this->writeJson($this->storageFileFolder.$subject->getFileName(true), json_encode($subject->serialize($data)));

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