<?php

namespace CZJPScraping\Models;

include_once __DIR__ . '/Mapper.php';
include_once __DIR__ . '/NameHelper.php';

/**
 * Class GenderHelper
 * @package CZJPScraping\Models
 */
class GenderHelper
{
    const US = '1';
    const EURO = '2';
    const POUNDS = '3';

    public static $displayNames = [
        self::US => 'Dolar',
        self::EURO => 'Euro',
        self::POUNDS => 'Funta'
    ];

    /** @var  array  */
    private $allNames;

    /**
     * @return array
     */
    public function getAllNames() : array
    {
        return $this->allNames;
    }

    public function __construct()
    {
        $this->allNames = $this->getCollection();
    }
    
    /**
     * @return array
     */
    public function getAllManNames() : array
    {
        $manNames = [];
        /** @var Name $name */
        foreach ($this->allNames as $name) {
            if ($name->getGenderId() === Gender::MAN) {
                $manNames[] = $name;
            }
        }

        return $manNames;
    }

    /**
     * @return array
     */
    public function getAllWomanNames() : array
    {
        $womanNames = [];
        /** @var Name $name */
        foreach ($this->allNames  as $name) {
            if ($name->getGenderId() === Gender::WOMAN) {
                $womanNames[] = $name;
            }
        }

        return $womanNames;
    }

    private function getCollection() : array
    {
        $mapper = new Mapper(Mapper::NAME);
        return $mapper->getCollection('');
    }

    public function determineGender(string $targetName) : string
    {
        $targetName = NameHelper::flushSpecialChars($targetName);
        $targetNames = explode(' ', $targetName);

        $mostSimilarNames = [];
        $determinedGender = Gender::UNKNOWN;

        foreach ($targetNames as $testName) {
            $maxSimilarity = 0;

            /** @var Name $name */
            foreach ($this->allNames as $name) {
                $percent = 0;
                similar_text($name->getName(), $testName, $percent);

                if ($percent === $maxSimilarity || $percent > $maxSimilarity) {

                    $maxSimilarity = $percent;

                    if ($maxSimilarity < 90) {
                        continue;
                    }

                    $mostSimilarNames[$name->getGenderId()] = $maxSimilarity;
                }
            }
        }

        if (count($mostSimilarNames) === 0) {
            return $determinedGender;
        }

        if (count($mostSimilarNames) === 1) {
            return array_keys($mostSimilarNames)[0];
        }

        if ($mostSimilarNames[Gender::MAN] === $mostSimilarNames[Gender::WOMAN]) {
            return Gender::UNKNOWN;
        }

        return $mostSimilarNames[Gender::MAN] > $mostSimilarNames[Gender::WOMAN] ? Gender::MAN : Gender::WOMAN;
    }

    public static function getMonetaryString($constantId)
    {
        return self::$displayNames[$constantId];
    }
}