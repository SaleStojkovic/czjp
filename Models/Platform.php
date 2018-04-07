<?php

namespace CZJPScraping\Models;

/**
 * Class Platform
 * @package CZJPScraping\Models
 */
class Platform
{
    /** @var  string */
    public $platform_id;

    /** @var string  */
    public $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPlatformId(): string
    {
        return $this->platform_id;
    }
}