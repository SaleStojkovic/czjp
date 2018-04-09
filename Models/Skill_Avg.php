<?php

namespace CZJPScraping\Models;

include_once __DIR__ . '/AbstractModel.php';

class Skill_Avg extends AbstractModel
{
    /** @var string */
    public $skill_avg_id;

    /** @var string */
    public $average;

    /** @var string */
    public $skill_name;

    /** @var string */
    public $scrape_log_id;

    /** @var string */
    public $date;

    /**
     * Skill_Avg constructor.
     * @param string $average
     * @param string $skill_name
     * @param string $scrape_log_id
     * @param string $date
     */
    public function __construct(string $average, string $skill_name, string $scrape_log_id, string $date)
    {
        $this->average = $average;
        $this->skill_name = $skill_name;
        $this->scrape_log_id = $scrape_log_id;
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getSkillAvgId(): string
    {
        return $this->skill_avg_id;
    }

    /**
     * @return string
     */
    public function getAverage() : string
    {
        return $this->average;
    }

    /**
     * @return string
     */
    public function getSkillName(): string
    {
        return $this->skill_name;
    }

    /**
     * @return string
     */
    public function getScrapeLogId(): string
    {
        return $this->scrape_log_id;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }
}