<?php

namespace CZJPScraping\Models;

include_once __DIR__ . '/Mapper.php';
include_once __DIR__ . '/Skill.php';
/**
 * Class SkillHelper
 * @package CZJPScraping\Models
 */
class SkillHelper
{
    /** @var  array */
    public $skills;

    /** @var  string */
    public $platform_id;

    /**
     * SkillHelper constructor.
     * @param string $platform_id
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function __construct(string $platform_id)
    {
        $mapper = new Mapper(Mapper::SKILL);
        $this->platform_id = $platform_id;
        $this->skills = [];

        /** @var Skill $skill */
        foreach ($mapper->getCollection('') as $skill) {
            if ($skill->getPlatformId() !== $platform_id) {
                continue;
            }
            $this->skills[$skill->getSkillId()] = $skill->getSkillName();
        }
    }

    /**
     * @return array
     */
    public function getSkills(): array
    {
        return $this->skills;
    }

    /**
     * @param string $skillName
     * @return bool
     */
    public function checkExistance(string $skillName) : bool
    {
        return in_array($skillName, $this->skills, true);
    }

    /**
     * @param $skillName
     * @return string
     * @throws \ReflectionException
     */
    public function getSkillId($skillName) : string
    {
        if ($this->checkExistance($skillName)) {
            return array_search ($skillName, $this->skills, false) . '';
        }

        $newSkill = new \CZJPScraping\Models\Skill(
            $skillName,
            $this->platform_id
        );

        $newSkill->save();
        $this->skills[$newSkill->getSkillId()] = $skillName;
        return $newSkill->getSkillId();
    }

    public function getSkillName($skillId)
    {
        return $this->skills[$skillId];
    }
}