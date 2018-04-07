<?php

namespace CZJPScraping\Models;

include_once __DIR__ . '/Name.php';
include_once __DIR__ . '/User.php';
include_once __DIR__ . '/Gender.php';
include_once __DIR__ . '/Skill.php';
include_once __DIR__ . '/Scrape_Log.php';

use CZJPScraping\Models\Gender as Gender;
use CZJPScraping\Models\Name as Name;
use CZJPScraping\Models\User as User;
use CZJPScraping\Models\Skill as Skill;

/**
 * Class Mapper
 * @package CZJPScraping\Models
 */
class Mapper
{
    /** @var  string */
    private $tableName;

    const GENDER = 'genders';
    const NAME = 'names';
    const SKILL = 'skills';
    const USER = 'users';

    public function __construct(string $tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * @param $whereCause
     * @return array
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function getCollection($whereCause = '') : array
    {
        $this->includeClasses();

        $query = 'SELECT * FROM ' . $this->tableName;
        if ($whereCause !== '') {
            $query .= ' ' . $whereCause;
        }
        $query .= ';';
        $conn = AbstractModel::connectToDb();

        try {
            $result = $conn->query($query);
        } catch (\mysqli_sql_exception $exception) {
            throw new \mysqli_sql_exception($exception);
        }

        $resultArray = [];

        if (!$result) {
            return [];
        }

        $className = $this->returnEmptyName($this->tableName);
        try {
            $reflectionObject = new \ReflectionClass('CZJPScraping\Models\\' . $className);
        } catch (\ReflectionException $exception) {
            throw new \ReflectionException($exception);
        }

        while ($row = $result->fetch_assoc()) {
            $newModel = $reflectionObject->newInstanceWithoutConstructor();

            foreach ($row as $key => $value) {
                $newModel->$key = $value;
            }

            $resultArray[] = $newModel;
        }

        return $resultArray;
    }

    /**
     * @param string $modelName
     * @return string
     */
    private function returnEmptyName(string $modelName) : string
    {
        $modelName = rtrim($modelName,'s');
        $modelName = ucfirst($modelName);

        return $modelName;
    }

    public function getModelInstance()
    {

    }

    private function includeClasses() {

    }
}