<?php
/**
 * Created by PhpStorm.
 * User: Arbor
 * Date: 3/11/18
 * Time: 18:03
 */

namespace CZJPScraping\Models;

include_once __DIR__ . '/AbstractModel.php';

/**
 * Class User_Earning
 * @package CZJPScraping\Models
 */
class User_Earning extends AbstractModel
{
    public $user_earning_id;

    public $job_name;

    public $user_id;

    public $price;

    public $sold;

    /**
     * User_Earning constructor.
     * @param $job_name
     * @param $user_id
     * @param $price
     * @param $sold
     */
    public function __construct($job_name, $user_id, $price, $sold)
    {
        $this->job_name = $job_name;
        $this->user_id = $user_id;
        $this->price = $price;
        $this->sold = $sold;
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
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return mixed
     */
    public function getUserEarningId()
    {
        return $this->user_earning_id;
    }

    /**
     * @return mixed
     */
    public function getSold()
    {
        return $this->sold;
    }

    /**
     * @return mixed
     */
    public function getJobName()
    {
        return $this->job_name;
    }
}