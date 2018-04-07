<?php

/**
 * Class AbstractScrape
 */
class ScraperList
{
    const FREELANCER = 'freelancer';
    const PEOPLE_PER_HOUR = 'peoplePerHour';

    private function returnScraperList() : array
    {
        return [
            'freelancer' => self::FREELANCER,
            'peoplePerHour' => self::PEOPLE_PER_HOUR
        ];
    }
}