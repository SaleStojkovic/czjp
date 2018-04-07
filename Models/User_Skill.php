<?php

namespace CZJPScraping\Models;

include_once __DIR__ . '/AbstractModel.php';
/**
 * Class User_Skill
 * @package CZJPScraping\Models
 */
class User_Skill extends AbstractModel
{
    /** @var  string */
    public $user_skill_id;

    /** @var  string */
    public $user_id;

    /** @var  string */
    public $skill_id;

    public function __construct(string $user_id, string $skill_id)
    {
        $this->user_id = $user_id;
        $this->skill_id = $skill_id;
    }

    /**
     * @return string
     */
    public function getUserSkillId(): string
    {
        return $this->user_skill_id;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->user_id;
    }

    /**
     * @return string
     */
    public function getSkillId(): string
    {
        return $this->skill_id;
    }
}