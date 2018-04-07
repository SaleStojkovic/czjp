<?php
/**
 * Created by PhpStorm.
 * User: Arbor
 * Date: 3/11/18
 * Time: 18:14
 */

namespace CZJPScraping\Models;

include_once __DIR__ . '/AbstractModel.php';

/**
 * Class User_Rating
 * @package CZJPScraping\Models
 */
class User_Rating extends AbstractModel
{
    public $user_rating_id;

    public $user_id;

    public $rating_score;

    public $rating_comment;

    /**
     * User_Rating constructor.
     * @param $user_id
     * @param $rating_score
     * @param $rating_comment
     */
    public function __construct($user_id, $rating_score, $rating_comment)
    {
        $this->user_id = $user_id;
        $this->rating_score = $rating_score;
        $this->rating_comment = $rating_comment;
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
    public function getRatingScore()
    {
        return $this->rating_score;
    }

    /**
     * @return mixed
     */
    public function getRatingComment()
    {
        return $this->rating_comment;
    }

    /**
     * @return mixed
     */
    public function getUserRatingId()
    {
        return $this->user_rating_id;
    }
}