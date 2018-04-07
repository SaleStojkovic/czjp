<?php

namespace CZJPScraping\Models;

include_once __DIR__ . '/Nationalities.php';
include_once __DIR__ . '/User.php';
include_once __DIR__ . '/GenderHelper.php';
include_once __DIR__ . '/SkillHelper.php';
include_once __DIR__ . '/Skill.php';
include_once __DIR__ . '/User_Skill.php';
include_once __DIR__ . '/NameHelper.php';
include_once __DIR__ . '/User_Job.php';
include_once __DIR__ . '/User_Rating.php';
include_once __DIR__ . '/Qualification.php';
include_once __DIR__ . '/User_Earning.php';
include_once __DIR__ . '/User_Earning_Comment.php';
include_once __DIR__ . '/Mapper.php';
include_once __DIR__ . '/Guru_Anual_Earning.php';
include_once __DIR__ . '/Skill_Url_Scrape_Log.php';

/**
 * Class AbstractModel
 * @package CZJPScraping\Models
 */
abstract class AbstractModel
{
    const SERVER_NAME = 'p:mis.arbor.local';
    const USERNAME = 'root';
    const PASSWORD = 'burek';
    const DB_NAME = 'CZJP';

    public static function connectToDb() : \mysqli
    {
        // Create connection
        $conn = new \mysqli(
            self::SERVER_NAME,
            self::USERNAME,
            self::PASSWORD,
            self::DB_NAME
        );

        if ($conn->connect_error) {
            die('Connection failed: ' . $conn->connect_error);
        }

        return $conn;
    }

    /**
     * @throws \ReflectionException
     */
    public function save()
    {
        $conn = self::connectToDb();

        /** @var  $values */
        $values = get_object_vars($this);

        $query = $this->createSqlQuery($conn, $values);

        $primaryKeyName = rtrim($this->getTableName(),'s') . '_id';

        try {
            $conn->query($query);
            $this->$primaryKeyName = $conn->insert_id;
            if ($conn->error) {
                echo $conn->error;
            }
        } catch (\Exception $exception) {
            throw new \mysqli_sql_exception($exception);
        }
    }

    /**
     * @param \mysqli $conn
     * @param array $values
     * @return string
     * @throws \ReflectionException
     */
    private function createSqlQuery(\mysqli $conn, array $values) : string
    {
        $query = "INSERT INTO " . $this->getTableName() . " (";

        $primaryKeyName = rtrim($this->getTableName(),'s') . '_id';

        $columns = array_keys($values);

        foreach ($columns as $columnName) {
            if ($columnName !== $primaryKeyName) {
                $query .= $columnName . ", ";
            }
        }

        $query = rtrim($query,", ") . ') VALUES (';

        foreach ($values as $columnName => $value) {
            if ($columnName !== $primaryKeyName) {
                $query .= '\'' . mysqli_real_escape_string($conn, $value) . '\'' . ", ";
            }
        }

        return rtrim($query,", ") . ");";
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    private function getTableName() : string
    {
        try {
            $reflector = new \ReflectionClass(get_class($this));
            $fileName = $reflector->getShortName();
            return strtolower($fileName) . 's';
        } catch (\ReflectionException $exception) {
            throw new \ReflectionException($exception);
        }
    }

    /**
     * @param string $columnName
     * @param $value
     * @return bool
     * @throws \ReflectionException
     */
    protected function updateColumn(string $columnName, $value)  : bool
    {
        $tableName = $this->getTableName();
        $primaryKeyName = rtrim($tableName,'s') . '_id';

        $query = 'UPDATE ' . $tableName . ' SET ' . $columnName . ' = ' . $value .
            ' WHERE ' . $primaryKeyName . ' = ' . $this->$primaryKeyName;

        $conn = self::connectToDb();
        try {
            $conn->query($query);
            if ($conn->error) {
                echo $conn->error;
                return false;
            }
            return true;
        } catch (\Exception $exception) {
            throw new \mysqli_sql_exception($exception);
        }
    }
}