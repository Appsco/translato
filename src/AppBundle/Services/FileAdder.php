<?php

namespace AppBundle\Services;

use AppBundle\Model\Account;
use AppBundle\Model\Change;
use AppBundle\Model\Project;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\FileWriter;
use JMS\TranslationBundle\Translation\LoaderManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileAdder
{
    /** @var Store */
    private $store;

    /** @var Loader */
    private $projectLoader;

    /** @var LoaderManager */
    private $loaderManager;

    /** @var FileWriter */
    private $writer;

    /**
     * @param Store         $store
     * @param Loader        $projectLoader
     * @param LoaderManager $loaderManager
     * @param FileWriter    $writer
     */
    public function __construct(Store $store, Loader $projectLoader, LoaderManager $loaderManager, FileWriter $writer)
    {
        $this->store = $store;
        $this->projectLoader = $projectLoader;
        $this->loaderManager = $loaderManager;
        $this->writer = $writer;
    }

    /**
     * @param Account        $account
     * @param Project        $project
     * @param UploadedFile[] $files
     * @param bool           $save
     *
     * @return Change[]
     */
    public function addFiles(Account $account, Project $project, array $files, $save)
    {
        $result = [];
        foreach ($files as $file) {
            $result[$file->getClientOriginalName()] = $this->addFile($account, $project, $file, $save);
        }

        return $result;
    }

    /**
     * @param Account      $account
     * @param Project      $project
     * @param UploadedFile $file
     * @param bool         $save
     *
     * @return Change
     */
    private function addFile(Account $account, Project $project, UploadedFile $file, $save)
    {
        $result = new Change();

        list($domain, $locale, $format) = $this->getDomainLocaleFormatFromFilename($file->getClientOriginalName());

        /** @var MessageCatalogue $inputCatalogue */
        $inputCatalogue = $this->loaderManager->loadFile(
            $file->getPathname(),
            $format,
            $locale,
            $domain
        );
        $inputDomain = $inputCatalogue->getDomain($domain);

        $loadedProject = $this->projectLoader->load($project, $domain, $locale);

        if ($loadedProject->getDomain() == $domain && $loadedProject->getLocale() == $locale) {
            // existing file

            /**
             * 1) remove from local all keys that do not exist in input
             * 2) add to local all keys that exist in input and do not exist in local
             * 3) update all empty app keys with values from input
             * 4) report conflicts where app values are different then input
             */

            $path = $project->getFilePathName($domain, $locale);

            $localCatalogue = $loadedProject->getCatalogue();
            $localDomain = $localCatalogue->getDomain($domain);

            /** @var Message $localMessage */
            /** @var Message $inputMessage */

            // find deleted
            foreach ($localDomain->all() as $localMessage) {
                $id = $localMessage->getId();
                if (false == $inputDomain->has($id)) {
                    $result->addDeleted($id);
                }
            }

            // find added
            foreach ($inputDomain->all() as $inputMessage) {
                $id = $inputMessage->getId();
                if (false == $localDomain->has($id)) {
                    $result->addAdded($id);
                }
            }

            // find updated empty local values & find conflicts and set values to local values
            foreach ($inputDomain->all() as $inputMessage) {
                $id = $inputMessage->getId();
                if ($localDomain->has($id)) {
                    $localMessage = $localDomain->get($id);

                    $valueLocal = $localMessage->getLocaleString();
                    $valueInput = $inputMessage->getLocaleString();

                    if ($valueInput == $valueLocal) {
                        continue;
                    }

                    if ($valueLocal == '' && $valueInput != '') {
                        $result->addModified($id);
                    } elseif ($valueLocal != $valueInput && $valueInput != '') {
                        $result->addConflict($id, $localMessage->getLocaleString(), $inputMessage->getLocaleString());
                        $inputMessage->setLocaleString($localMessage->getLocaleString());
                    } else {
                        $inputMessage->setLocaleString($valueLocal);
                    }
                }
            }

            if ($save) {
                $this->writer->write($inputCatalogue, $domain, $path, $format);
            }
        } else {
            // new file

            /** @var Message $message */
            foreach ($inputDomain->all() as $message) {
                $result->addAdded($message->getId());
            }

            if ($save) {
                $this->store->addFile($account, $project, $file);
            }
        }

        return $result;
    }

    /**
     * @param string $file
     *
     * @return array|null
     */
    private function getDomainLocaleFormatFromFilename($file)
    {
        if (preg_match('/^([^\.]+)\.([^\.]+)\.([^\.]+)$/', basename($file), $match)) {
            return [
                $match[1], // domain
                $match[2], // locale
                $match[3], // format
            ];
        }

        return null;
    }
}
