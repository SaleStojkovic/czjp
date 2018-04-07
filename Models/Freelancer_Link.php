<?php
/**
 * Created by PhpStorm.
 * User: Arbor
 * Date: 3/15/18
 * Time: 00:06
 */

namespace CZJPScraping\Models;

include_once __DIR__ . '/AbstractModel.php';

class Freelancer_Link extends AbstractModel
{
    public $freelancer_link_id;

    /** @var string */
    public $link_text;

    /** @var string */
    public $scrape_log_id;

    /** @var string */
    public $nationality_id;

    /** @var string */
    public $executed;

    /**
     * Freelancer_Link constructor.
     * @param string $link_text
     * @param string $scrape_log_id
     * @param string $nationality_id
     * @param string $executed
     */
    public function __construct(string $link_text, string $scrape_log_id, $nationality_id = null, $executed = null)
    {
        $this->link_text = $link_text;
        $this->scrape_log_id = $scrape_log_id;
        $this->nationality_id = $nationality_id;
        $this->executed = $executed;
    }

    /**
     * @return mixed
     */
    public function getFreelancerLinkId()
    {
        return $this->freelancer_link_id;
    }

    /**
     * @return string
     */
    public function getLinkText(): string
    {
        return $this->link_text;
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
    public function getNationalityId(): string
    {
        return $this->nationality_id;
    }

    /**
     * @return string
     */
    public function getExecuted(): string
    {
        return $this->executed;
    }

    public function changeExecuted() : bool
    {
        return $this->updateColumn('executed', 1);
    }
}