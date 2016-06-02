<?php

namespace AppBundle\Services;

use Symfony\Component\Finder\Finder;

class UserStore
{
    /** @var string */
    private $baseDir;

    /**
     * @param string $baseDir
     */
    public function __construct($baseDir)
    {
        $this->baseDir = $baseDir;
    }

    /**
     * @return string[]
     */
    public function listUsers()
    {
        $result = [];

        $finder = new Finder();
        $finder->in($this->baseDir)->directories()->depth(0);
        /** @var \SplFileInfo $file */
        foreach ($finder as $file) {
            $result[] = $file->getFilename();
        }

        return $result;
    }
}
