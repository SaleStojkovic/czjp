<?php

namespace CZJPScraping\Models;

include_once __DIR__ . '/AbstractModel.php';

/**
 * Class Scrape_Log
 * @package CZJPScraping\Models
 */
class Scrape_Log extends AbstractModel
{
    /** @var string */
    public $scrape_log_id;

    /** @var  string */
    public $platform_id;

    /** @var  string */
    public $date;

    /** @var  string */
    public $total_rows;

    /** @var  string */
    public $scrape_rows;

    /** @var string */
    public $nationality_id;

    /** @var string */
    public $last_page_fetched;

    /** @var string */
    public $skill_url;

    /**
     * Scrape_Logs constructor.
     * @param string $platform_id
     * @param string $total_rows
     * @param string $scrape_rows
     * @param string $nationality_id
     * @param string $last_page_fetched
     * @param string $skill_url
     */
    public function __construct(
        string $platform_id,
        string $total_rows,
        string $scrape_rows,
        string $nationality_id,
        string $last_page_fetched = null,
        string $skill_url = null
    )
    {
        $this->platform_id = $platform_id;
        $this->total_rows = $total_rows;
        $this->scrape_rows = $scrape_rows;
        $this->nationality_id = $nationality_id;
        $this->last_page_fetched = $last_page_fetched;
        $this->skill_url = $skill_url;
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
    public function getPlatformId(): string
    {
        return $this->platform_id;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getTotalRows(): string
    {
        return $this->total_rows;
    }

    /**
     * @return string
     */
    public function getScrapeRows(): string
    {
        return $this->scrape_rows;
    }

    /**
     * @return string
     */
    public function getNationalityId(): string
    {
        return $this->nationality_id;
    }

    /**
     * @return string
     */
    public function getLastPageFetched(): string
    {
        return $this->last_page_fetched;
    }

    /**
     * @return string
     */
    public function getSkillUrl(): string
    {
        return $this->skill_url;
    }
}