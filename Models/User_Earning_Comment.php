<?php
/**
 * Created by PhpStorm.
 * User: Arbor
 * Date: 3/12/18
 * Time: 22:22
 */

namespace CZJPScraping\Models;

/**
 * Class User_Earning_Comment
 * @package CZJPScraping\Models
 */
class User_Earning_Comment extends AbstractModel
{
    public $user_earning_comment_id;

    public $user_earning_id;

    public $comment;

    /**
     * User_Earning_Comment constructor.
     * @param $user_earning_id
     * @param $comment
     */
    public function __construct($user_earning_id, $comment)
    {
        $this->user_earning_id = $user_earning_id;
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getUserEarningCommentId()
    {
        return $this->user_earning_comment_id;
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
    public function getComment()
    {
        return $this->comment;
    }
}