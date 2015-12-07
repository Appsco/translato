<?php

namespace AppBundle\Model;

use JMS\TranslationBundle\Model\Message;

class Change
{
    /** @var string[] */
    private $modified = [];

    /** @var string[] */
    private $added = [];

    /** @var string[] */
    private $deleted = [];

    /** @var ChangeConflict[] */
    private $conflicts = [];

    public function addModified($id)
    {
        $this->modified[$id] = $id;
    }

    public function addAdded($id)
    {
        $this->added[$id] = $id;
    }

    public function addDeleted($id)
    {
        $this->deleted[$id] = $id;
    }

    public function addConflict($id, $localValue, $inputValue)
    {
        $this->conflicts[$id] = new ChangeConflict($id, $localValue, $inputValue);
    }

    /**
     * @return \string[]
     */
    public function getModified()
    {
        return array_values($this->modified);
    }

    /**
     * @return \string[]
     */
    public function getAdded()
    {
        return array_values($this->added);
    }

    /**
     * @return \string[]
     */
    public function getDeleted()
    {
        return array_values($this->deleted);
    }

    /**
     * @return ChangeConflict[]
     */
    public function getConflicts()
    {
        return array_values($this->conflicts);
    }
}
