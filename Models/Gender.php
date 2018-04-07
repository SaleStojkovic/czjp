<?php

namespace CZJPScraping\Models;

include_once __DIR__ . '/AbstractModel.php';

/**
 * Class Gender
 * @package CZJPScraping\Models
 */
class Gender
{
    const MAN = '1';
    const WOMAN = '2';
    const UNKNOWN = '3';

    public static $displayName = [
        self::MAN => 'Muškarac',
        self::WOMAN => 'Žena',
        self::UNKNOWN => 'Nepoznat'
    ];

    /** @var  string */
    public $gender_id;

    /** @var  string */
    public $name;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    public static function getGenderName($genderId)
    {
        return self::$displayName[$genderId];
    }
}