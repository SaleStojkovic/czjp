<?php

namespace CZJPScraping\Models;

include_once __DIR__ . '/AbstractModel.php';
include_once __DIR__ . '/Mapper.php';


/**
 * Class Skill
 * @package CZJPScraping\Models
 */
class Skill extends AbstractModel
{
    /** @var  string */
    public $skill_id;

    /** @var  string */
    public $name;

    /** @var  string */
    public $platform_id;

    /**
     * Skill constructor.
     * @param string $name
     * @param string $platform_id
     */
    public function __construct(string $name, string $platform_id)
    {
        $this->name = $name;
        $this->platform_id = $platform_id;
    }

    /**
     * @return string
     */
    public function getSkillName(): String
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSkillId(): string
    {
        return $this->skill_id;
    }

    /**
     * @return string
     */
    public function getPlatformId(): string
    {
        return $this->platform_id;
    }
}