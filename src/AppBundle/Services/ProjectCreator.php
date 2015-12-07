<?php

namespace AppBundle\Services;

use AppBundle\Model\Account;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProjectCreator
{
    /** @var Store */
    private $store;

    /**
     * @param Store $store
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    /**
     * @param Account        $account
     * @param string         $projectName
     * @param UploadedFile[] $files
     *
     * @return \AppBundle\Model\Project
     */
    public function create(Account $account, $projectName, array $files)
    {
        $project = $this->store->addProject($account, $projectName);

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $this->store->addFile($account, $project, $file);
            } else {
                throw new \InvalidArgumentException('Expected UploadedFile');
            }
        }

        return $project;
    }
}
