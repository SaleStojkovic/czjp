<?php

namespace CZJPScraping\Controllers;

use CZJPScraping\Models\Name;
use CZJPScraping\Models\Gender;
use CZJPScraping\Models\NameHelper;

include_once __DIR__ . '/AbstractScraper.php';
include_once __DIR__ . '/../Models/Name.php';
include_once __DIR__ . '/../Models/Gender.php';
include_once __DIR__ . '/../Models/NameHelper.php';

/**
 * Class SerbianNamesScrape
 * @package CZJPScraping\Controllers
 */
class SerbianNamesScrape extends AbstractScraper
{
    const SITE_DESTINATION_1 = 'https://www.znacenje-imena.com/imena/srpska/';
    const WIKIPEDIA_PAGE = 'https://sr.wikipedia.org/sr-el/%D0%A1%D0%BF%D0%B8%D1%81%D0%B0%D0%BA_%D1%81%D1%80%D0%BF%D1%81%D0%BA%D0%B8%D1%85_%D0%B8%D0%BC%D0%B5%D0%BD%D0%B0';

    const MAN_NAMES = 'muska?page=';
    const WOMAN_NAMES = 'zenska?page=';

    /** @var  array */
    private $wikipediaManNames;

    /** @var  array */
    private $wikipediaWomanNames;

    public function run()
    {
        $allManNames = $this->getManNamesFromSite1();
        $allWomanNames = $this->getWomanNamesFromSite1();

        $this->getAllNamesFromWikipedia();

        foreach ($this->wikipediaManNames as $manName) {
            if (in_array($manName, $allManNames, true)) {
                continue;
            }

            $allManNames[] = $manName;
        }

        foreach ($this->wikipediaWomanNames as $womanName) {
            if (in_array($womanName, $allWomanNames, true)) {
                continue;
            }

            $allWomanNames[] = $womanName;
        }

        $this->saveMenNames($allManNames);
        $this->saveWomenNames($allWomanNames);
    }

    private function getManNamesFromSite1() : array
    {
        $manNames = [];
        $rawNames = [];

        for ($i = 1; $i <= 49; $i++) {
            $page = $this->getHttpPage(self::SITE_DESTINATION_1 . self::MAN_NAMES . $i);
            $matches = $this->matchTagContent('div', 'class="col-sm-3 col-xs-6"', $page);

            foreach ($matches[1] as $match) {
                $newMatches = $this->matchTagContent('a', '', $match);
                $rawNames[] = $newMatches[1];
            }
        }

        foreach ($rawNames as $name) {
            $manNames[] = NameHelper::flushSpecialChars($name[0]);
        }

        return $manNames;
    }

    private function getAllNamesFromWikipedia()
    {
        $page = $this->getHttpPage(self::WIKIPEDIA_PAGE);
        $matches = $this->matchTagContent('table', 'style="border: 1px black solid; border-collapse: collapse; text-align: left; width:80%;"', $page);

        $manNames = [];
        $womanNames = [];

        $manNamesByLetter = [];
        $womanNamesByLetter = [];

        foreach ($matches[1] as $match) {
            $manNamesPosition = strpos($match, 'Muška imena');
            $womanNamesPostiion = strpos($match, 'Ženska imena');

            $manNamesRaw = substr($match, $manNamesPosition, $womanNamesPostiion);
            $womanNamesRaw = substr($match, $womanNamesPostiion, strlen($match));

            $rawManNamesMatches = $this->matchTagContent('a', '', $manNamesRaw);
            $rawWomanNamesMatches = $this->matchTagContent('a', '', $womanNamesRaw);

            $manNamesByLetter[] = $rawManNamesMatches[1];
            $womanNamesByLetter[] = $rawWomanNamesMatches[1];
        }

        foreach ($manNamesByLetter as $names) {
            foreach ($names as $name) {
                $manNames[] = NameHelper::flushSpecialChars($name);
            }
        }

        foreach ($womanNamesByLetter as $names) {
            foreach ($names as $name) {
                $womanNames[] = NameHelper::flushSpecialChars($name);
            }
        }

        $this->wikipediaManNames = $manNames;
        $this->wikipediaWomanNames = $womanNames;
    }

    private function getWomanNamesFromSite1() : array
    {
        $womanNames = [];
        $rawNames = [];

        for ($i = 1; $i <= 35; $i++) {
            $page = $this->getHttpPage(self::SITE_DESTINATION_1 . self::WOMAN_NAMES . $i);
            $matches = $this->matchTagContent('div', 'class="col-sm-3 col-xs-6"', $page);

            foreach ($matches[1] as $match) {
                $newMatches = $this->matchTagContent('a', '', $match);
                $rawNames[] = $newMatches[1];
            }
        }

        foreach ($rawNames as $name) {
            $womanNames[] = NameHelper::flushSpecialChars($name[0]);
        }

        return $womanNames;
    }

    private function saveMenNames(array $menNamesArray)
    {
        foreach ($menNamesArray as $manName) {
            $this->saveName($manName, Gender::MAN);
        }
    }

    private function saveWomenNames(array $womenNamesArray)
    {
        foreach ($womenNamesArray as $womenName) {
            $this->saveName($womenName, Gender::WOMAN);
        }
    }

    private function saveName(string $name, int $gender_id)
    {
        if (!ctype_alpha($name)) {
            return;
        }

        if (strpos($name, ',')) {
            $newNames = explode(', ', $name);

            foreach ($newNames as $newName) {
                $this->saveName($newName, $gender_id);
            }

            return;
        }

        if (ctype_lower($name)) {
            return;
        }

        $modelName = new Name($name, $gender_id);
        $modelName->save();


    }
}

$object = new SerbianNamesScrape();
$object->run();
