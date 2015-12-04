<?php

namespace AppBundle\Model;

use JMS\Serializer\Annotation as JMS;
use JMS\TranslationBundle\Util\FileUtils;

class Project 
{
    /**
     * @var string
     * @JMS\Type(name="string")
     */
    protected $id;

    /**
     * @var string
     * @JMS\Type(name="string")
     */
    protected $name;

    /**
     * @var string
     * @JMS\Type(name="string")
     */
    protected $path;

    /**
     * @var \DateTime
     * @JMS\Type(name="DateTime")
     */
    protected $modifiedAt;

    /**
     * domain => lang => [0 => format, 1 => \SplFileInfo]
     *
     * @var array
     * @JMS\Exclude()
     */
    private $_files;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return Project
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Project
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     *
     * @return Project
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * @param \DateTime $modifiedAt
     *
     * @return Project
     */
    public function setModifiedAt(\DateTime $modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }

    public function getDomains()
    {
        $files = $this->getFiles();
        $domains = array_keys($files);

        return $domains;
    }

    /**+
     * @param $preferedDomain
     *
     * @return string|null
     */
    public function pickDomain($preferedDomain)
    {
        $files = $this->getFiles();
        $domains = $this->getDomains();
        if ((!$domain = $preferedDomain) || !isset($files[$domain])) {
            $domain = reset($domains);
        }

        return $domain ? $domain : null;
    }

    public function getLocales($domain)
    {
        $files = $this->getFiles();
        $locales = array_keys($files[$domain]);

        return $locales;
    }

    /**
     * @param string $domain
     * @param string $preferedLocale
     *
     * @return string
     */
    public function pickLocale($domain, $preferedLocale)
    {
        $files = $this->getFiles();
        $locales = $this->getLocales($domain);
        if ((!$locale = $preferedLocale) || !isset($files[$domain][$locale])) {
            $locale = reset($locales);
        }

        return $locale;
    }

    /**
     * @param string $domain
     * @param string $locale
     *
     * @return string
     */
    public function getFilePathName($domain, $locale)
    {
        $files = $this->getFiles();

        return $files[$domain][$locale][1]->getPathName();
    }

    /**
     * @param string $domain
     * @param string $locale
     *
     * @return string
     */
    public function getFileFormat($domain, $locale)
    {
        $files = $this->getFiles();

        return $files[$domain][$locale][0];
    }

    /**
     * @return array
     */
    private function getFiles()
    {
        if (empty($this->_files)) {
            $this->_files = FileUtils::findTranslationFiles($this->path);
        }

        return $this->_files;
    }
}
