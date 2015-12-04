<?php

namespace AppBundle\Services;

use AppBundle\Model\LoadedProject;
use AppBundle\Model\Project;
use JMS\TranslationBundle\Translation\LoaderManager;

class Loader
{
    /** @var LoaderManager */
    private $loader;

    /**
     * @param LoaderManager $loader
     */
    public function __construct(LoaderManager $loader)
    {
        $this->loader = $loader;
    }

    /**
     * @param Project $project
     *
     * @return LoadedProject|null
     */
    public function load(Project $project, $preferedDomain, $preferedLocale)
    {
        $domain = $project->pickDomain($preferedDomain);
        if (null == $domain) {
            return null;
        }

        $locale = $project->pickLocale($domain, $preferedLocale);

        $catalogue = $this->loader->loadFile(
            $project->getFilePathName($domain, $locale),
            $project->getFileFormat($domain, $locale),
            $locale,
            $domain
        );

        $alternativeMessages = [];
        foreach ($project->getLocales($domain) as $otherLocale) {
            if ($locale === $otherLocale) {
                continue;
            }

            $altCatalogue = $this->loader->loadFile(
                $project->getFilePathName($domain, $otherLocale),
                $project->getFileFormat($domain, $otherLocale),
                $otherLocale,
                $domain
            );
            foreach ($altCatalogue->getDomain($domain)->all() as $id => $message) {
                $alternativeMessages[$id][$otherLocale] = $message;
            }
        }

        $newMessages = $existingMessages = array();
        foreach ($catalogue->getDomain($domain)->all() as $id => $message) {
            if ($message->isNew()) {
                $newMessages[$id] = $message;
                continue;
            }

            $existingMessages[$id] = $message;
        }

        return new LoadedProject(
            $project,
            $domain,
            $locale,
            $newMessages,
            $existingMessages,
            $alternativeMessages
        );
    }
}
