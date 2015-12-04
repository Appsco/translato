<?php

namespace AppBundle\Services;

use AppBundle\Model\Account;
use AppBundle\Model\Project;
use AppBundle\Model\Translations;
use JMS\Serializer\Serializer;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Store
{
    /** @var Serializer */
    private $serializer;

    /** @var string */
    private $baseDir;

    /**
     * @param Serializer $serializer
     * @param string     $baseDir
     */
    public function __construct(Serializer $serializer, $baseDir)
    {
        $this->serializer = $serializer;
        $this->baseDir = $baseDir;
    }

    /**
     * @param string $username
     *
     * @return Translations
     */
    public function load($username)
    {
        $result = $this->loadFromStorage($username);
        if (null == $result) {
            $result = new Translations();
            $this->save($username, $result);
        }

        return $result;
    }

    /**
     * @param string       $username
     * @param Translations $translations
     */
    public function save($username, Translations $translations)
    {
        $json = $this->serializer->serialize($translations, 'json');
        $this->ensureUserDir($username);
        file_put_contents($this->getInfoFilename($username), $json);
    }

    /**
     * @param Account $account
     * @param string  $name
     *
     * @return Store
     */
    public function addProject(Account $account, $name)
    {
        $project = new Project();
        $project
            ->setId($id = uniqid())
            ->setName($name)
            ->setPath($this->getUserDirPath($account->getUsername()).'/'.$id)
            ->setModifiedAt(new \DateTime())
        ;
        mkdir($project->getPath());
        $account->getTranslations()->addProject($project);
        $this->save($account->getUsername(), $account->getTranslations());

        return $this;
    }

//    /**
//     * @param Account      $account
//     * @param UploadedFile $uploadedFile
//     * @param string       $name
//     *
//     * @return File
//     */
//    public function addFile(Account $account, UploadedFile $uploadedFile, $name)
//    {
//        $dir = $this->getUserDirPath($account->getUsername());
//        $this->ensureUserDir($account->getUsername());
//        $uploadedFile->move($dir);
//
//        $file = new File($uploadedFile->getClientOriginalName(), $name, $this->getUserDirPath($account->getUsername()).'/'.$uploadedFile->getFilename());
//        $account->getUserFiles()->addFile($file);
//        $this->save($account->getUsername(), $account->getUserFiles());
//
//        return $file;
//    }

    /**
     * @param string $username
     *
     * @return Translations|null
     */
    private function loadFromStorage($username)
    {
        $filename = $this->getInfoFilename($username);
        if (false == file_exists($filename)) {
            return null;
        }
        $content = file_get_contents($filename);
        $catalogue = $this->serializer->deserialize($content, Translations::class, 'json');

        return $catalogue;
    }

    /**
     * @param string $username
     */
    private function ensureUserDir($username)
    {
        $path = $this->getUserDirPath($username);
        if (false == is_dir($path)) {
            mkdir($path);
        }
    }

    /**
     * @param string $username
     *
     * @return string
     */
    private function getUserDirPath($username)
    {
        return sprintf('%s/%s', $this->baseDir, $username);
    }

    /**
     * @param string $username
     *
     * @return string
     */
    private function getInfoFilename($username)
    {
        return sprintf('%s/%s/_info.json', $this->baseDir, $username);
    }
}
