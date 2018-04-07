<?php

/**
 * Class ScrapeFactory
 */
class ScrapeFactory
{
    const FREELANCER = 'freelancer';
    const PEOPLE_PER_HOUR = 'peoplePerHour';

    public static function createScraper(string $scraperConst) : ScrapeInterface
    {
        switch ($scraperConst) {
            case self::FREELANCER:
                return new FreelanceScrape();
                break;
            case self::PEOPLE_PER_HOUR:
                return new PeoplePerHourScrape();
                break;
            default:
                throw \GuzzleHttp\Promise\exception_for('Class not found');
        }
    }
}