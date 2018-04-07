<?php
/**
 * Created by PhpStorm.
 * User: Arbor
 * Date: 3/11/18
 * Time: 18:08
 */

namespace CZJPScraping\Models;

include_once __DIR__ . '/AbstractModel.php';

class User_Job extends AbstractModel
{
    public $user_id;

    public $user_rating_id;

    public $earned;

    public $currency_id;

    public $date;

    public $bid;

    /**
     * User_Job constructor.
     * @param $user_id
     * @param $user_rating_id
     * @param $earned
     * @param $currency_id
     * @param $date
     * @param $bid
     */
    public function __construct($user_id, $user_rating_id, $earned, $currency_id, $date = null, $bid)
    {
        $this->user_id = $user_id;
        $this->user_rating_id = $user_rating_id;
        $this->earned = $earned;
        $this->currency_id = $currency_id;
        $this->date = $date;
        $this->bid = $bid;
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
    public function getUserRatingId()
    {
        return $this->user_rating_id;
    }

    /**
     * @return mixed
     */
    public function getEarned()
    {
        return $this->earned;
    }

    /**
     * @return mixed
     */
    public function getCurrencyId()
    {
        return $this->currency_id;
    }

    /**
     * @return null
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return mixed
     */
    public function getBid()
    {
        return $this->bid;
    }
}