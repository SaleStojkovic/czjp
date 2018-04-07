<?php
/**
 * Created by PhpStorm.
 * User: Arbor
 * Date: 3/11/18
 * Time: 17:57
 */

namespace CZJPScraping\Models;

include_once __DIR__ . '/AbstractModel.php';

/**
 * Class Qualification
 * @package CZJPScraping\Models
 */
class Qualification extends AbstractModel
{
    public $user_id;

    public $name;

    /**
     * Qualification constructor.
     * @param $user_id
     * @param $name
     */
    public function __construct($user_id, $name)
    {
        $this->user_id = $user_id;
        $this->name = $name;
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
    public function getName()
    {
        return $this->name;
    }
}