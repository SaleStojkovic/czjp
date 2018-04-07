<?php
/**
 * Created by PhpStorm.
 * User: Arbor
 * Date: 3/19/18
 * Time: 19:51
 */

namespace CZJPScraping\Models;

include_once __DIR__ . '/AbstractModel.php';


class Guru_Anual_Earning extends AbstractModel
{
    public $guru_anual_earning_id;

    public $user_id;

    public $amount;

    public $scrape_log_id;

    /**
     * Guru_Anual_Earning constructor.
     * @param $user_id
     * @param $amount
     * @param $scrape_log_id
     */
    public function __construct($user_id, $amount, $scrape_log_id)
    {
        $this->user_id = $user_id;
        $this->amount = $amount;
        $this->scrape_log_id = $scrape_log_id;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return mixed
     */
    public function getGuruAnualEarningId()
    {
        return $this->guru_anual_earning_id;
    }

    /**
     * @return mixed
     */
    public function getScrapeLogId()
    {
        return $this->scrape_log_id;
    }
}