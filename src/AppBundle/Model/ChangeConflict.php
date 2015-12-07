<?php

namespace AppBundle\Model;

class ChangeConflict 
{
    /** @var string */
    private $id;

    /** @var string */
    private $localValue;

    /** @var string */
    private $inputValue;

    /**
     * @param string $id
     * @param string $localValue
     * @param string $inputValue
     */
    public function __construct($id, $localValue, $inputValue)
    {
        $this->id = $id;
        $this->localValue = $localValue;
        $this->inputValue = $inputValue;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLocalValue()
    {
        return $this->localValue;
    }

    /**
     * @return string
     */
    public function getInputValue()
    {
        return $this->inputValue;
    }
}
