<?php
/**
 * Created by PhpStorm.
 * User: Arbor
 * Date: 3/21/18
 * Time: 19:19
 */

namespace CZJPScraping\Models;
include_once __DIR__ . '/AbstractModel.php';

class Skill_Url_Scrape_Log extends AbstractModel
{
    /** @var string */
    public $skill_url_scrape_log_id;

    /** @var string */
    public $scrape_log_id;

    /** @var string */
    public $skill_url;

    /** @var string */
    public $last_fetched_page;

    /**
     * Skill_Url_Scrape_Log constructor.
     * @param string $scrape_log_id
     * @param string $skill_url
     * @param string $last_fetched_page
     */
    public function __construct(string $scrape_log_id, string $skill_url, string $last_fetched_page = '0')
    {
        $this->scrape_log_id = $scrape_log_id;
        $this->skill_url = $skill_url;
        $this->last_fetched_page = $last_fetched_page;
    }

    /**
     * @return string
     */
    public function getSkillUrlScrapeLogId(): string
    {
        return $this->skill_url_scrape_log_id;
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
    public function getSkillUrl(): string
    {
        return $this->skill_url;
    }

    /**
     * @return string
     */
    public function getLastFetchedPage(): string
    {
        return $this->last_fetched_page;
    }

    public function updateLastPageFetched($lastPageFetched) : bool
    {
        return $this->updateColumn('last_fetched_page', $lastPageFetched);
    }
}