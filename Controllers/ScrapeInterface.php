<?php

namespace CZJPScraping\Controllers;

/**
 * Interface ScrapeInterface
 * @package CZJPScraping\Controllers
 */
interface ScrapeInterface
{
    /**
     * @return bool
     */
    public function run() : bool;
}