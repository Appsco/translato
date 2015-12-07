<?php

namespace AppBundle\Model;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;

class LoadedProject
{
    /** @var Project */
    private $project;

    /** @var string */
    private $domain;

    /** @var string */
    private $locale;

    /** @var MessageCatalogue */
    private $catalogue;

    /**
     * id => Message
     * @var Message[]
     */
    private $newMessages;

    /**
     * id => Message
     * @var Message[]
     */
    private $existingMessages;

    /**
     * id => locale => Message
     * @var array
     */
    private $alternativeMessages;

    /**
     * @param Project          $project
     * @param string           $domain
     * @param string           $locale
     * @param MessageCatalogue $catalogue
     * @param Message[]        $newMessages
     * @param Message[]        $existingMessages
     * @param array            $alternativeMessages
     */
    public function __construct(
        Project $project,
        $domain,
        $locale,
        MessageCatalogue $catalogue,
        array $newMessages,
        array $existingMessages,
        array $alternativeMessages
    ) {
        $this->project = $project;
        $this->domain = $domain;
        $this->locale = $locale;
        $this->catalogue = $catalogue;
        $this->newMessages = $newMessages;
        $this->existingMessages = $existingMessages;
        $this->alternativeMessages = $alternativeMessages;
    }

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return MessageCatalogue
     */
    public function getCatalogue()
    {
        return $this->catalogue;
    }

    /**
     * id => Message
     *
     * @return Message[]
     */
    public function getNewMessages()
    {
        return $this->newMessages;
    }

    /**
     * id => Message
     *
     * @return Message[]
     */
    public function getExistingMessages()
    {
        return $this->existingMessages;
    }

    /**
     * id => locale => Message
     *
     * @return array
     */
    public function getAlternativeMessages()
    {
        return $this->alternativeMessages;
    }
}
