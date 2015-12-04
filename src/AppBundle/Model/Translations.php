<?php

namespace AppBundle\Model;

use JMS\Serializer\Annotation as JMS;

class Translations
{
    /**
     * @var Project[]
     * @JMS\Type(name="array<AppBundle\Model\Project>")
     */
    protected $projects = [];

    /**
     * @return int
     */
    public function getProjectCount()
    {
        return count($this->projects);
    }

    /**
     * @return Project[]
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * @param string $projectId
     *
     * @return Project|null
     */
    public function getProject($projectId)
    {
        foreach ($this->projects as $project) {
            if ($project->getId() == $projectId) {
                return $project;
            }
        }

        return null;
    }

    /**
     * @return Project|null
     */
    public function getFirstProject()
    {
        $arr = $this->projects;

        return array_shift($arr);
    }

    /**
     * @param Project $project
     *
     * @return Translations
     */
    public function addProject(Project $project)
    {
        if ($this->getProject($project->getId())) {
            throw new \InvalidArgumentException(sprintf('Project "%s" already added', $project->getId()));
        }

        $this->projects[] = $project;

        return $this;
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function removeProject($id)
    {
        if (null == $this->getProject($id)) {
            return false;
        }

        $arr = $this->projects;
        $this->projects = [];
        foreach ($arr as $project) {
            if ($project->getId() != $id) {
                $this->projects[] = $project;
            }
        }

        return true;
    }
}
