<?php

namespace CZJPScraping\Models;

include_once __DIR__ . '/AbstractModel.php';

/**
 * Class Name
 * @package CZJPScraping\Models
 */
class Name extends AbstractModel
{
    /** @var  string */
    public $name_id;

    /** @var  string */
    public $name;

    /** @var  string */
    public $gender_id;

    public function __construct(string $name, string $gender_id)
    {
        $this->name = $name;
        $this->gender_id = $gender_id;
    }
    
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getNameId(): string
    {
        return $this->name_id;
    }

    /**
     * @param string $name_id
     */
    public function setNameId(string $name_id)
    {
        $this->name_id = $name_id;
    }

    /**
     * @return string
     */
    public function getGenderId(): string
    {
        return $this->gender_id;
    }

    /**
     * @param string $gender_id
     */
    public function setGenderId(string $gender_id)
    {
        $this->gender_id = $gender_id;
    }


}