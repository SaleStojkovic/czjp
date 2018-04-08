<?php

namespace CZJPScraping\CSVParsers;

use CZJPScraping\Models\Currency_Value;
use CZJPScraping\Models\Mapper;
use CZJPScraping\Models\Scrape_Log;
use CZJPScraping\Models\Skill;
use CZJPScraping\Models\Skill_Avg;
use CZJPScraping\Models\User;
use CZJPScraping\Models\User_Skill;

include_once __DIR__ . '/../Models/AbstractModel.php';

abstract class AmericanAvgAbstract
{
    /** @var string */
    public $fileName;

    /** @var Scrape_Log */
    public $scrapeLog;

    /** @var array|Skill_Avg[] */
    public $skillAvgs;

    /** @var array */
    public $skillArray;

    /** @var array|User[] */
    public $users;

    /** @var array */
    public $currencyValues;

    /**
     * AmericanAvgAbstract constructor.
     * @param $scrapeLogId
     * @param string $fileName
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function __construct($scrapeLogId, string $fileName)
    {
        $mapper = new Mapper('scrape_logs');
        $this->scrapeLog = $mapper->getCollection('WHERE scrape_log_id = ' . $scrapeLogId)[0];
        $this->skillArray = $this->getSkillArray();
        $this->loadCurrencyValues();
        $this->users = $this->getUsers();
        $this->fileName = $this->getDesktopPath() . $fileName . '.csv';
        $this->loadAllSkills();
        $this->loadAllEarnings();
    }

    private function makeCsvRows() : array
    {
        $skillAvgs = $this->skillAvgs;
        $rows = [];

        /** @var Skill_Avg $skillAvg */
        foreach ($skillAvgs as $skillAvg) {
            $row = [
                'Skill name' => $skillAvg->getSkillName(),
                'Average Sum' => $skillAvg->getAverage(),
            ];

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @throws \ReflectionException
     */
    public function makeCsv()
    {
        $this->skillAvgs = $this->saveAllAvgs();

        $rows = $this->makeCsvRows();

        $file = fopen($this->fileName,'w');

        fputcsv($file, array_keys($rows[0]));

        foreach ($rows as $row) {
            fputcsv($file, $row);
        }
    }

    /**
     * @return string
     */
    private function getDesktopPath()
    {
        return '/Users/Arbor/Desktop/CZJPcsv/';
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function saveAllAvgs() : array
    {
        $skillsAvg = [];
        $userCountBySkill = [];
        $usersWithEarnings = [];

        foreach ($this->users as $user) {
            if (!$user->getTotalMoneyEarned()) {
                continue;
            }

            $usersWithEarnings[] = $user;
        }

        $usersEarningsBySkill = [];

        foreach ($this->skillArray as $skillName) {
            $usersEarningsBySkill[$skillName] = 0;
            $userCountBySkill[$skillName] = 0;
        }

        /** @var User $user */
        foreach ($usersWithEarnings as $user) {
            foreach ($user->getSkills() as $skillName) {
                $usersEarningsBySkill[$skillName] += $user->getTotalMoneyEarned();
                $userCountBySkill[$skillName]++;
            }
        }

        foreach ($userCountBySkill as $skillName => $count) {
            $average = 0;
            if ($count) {
                $average = $usersEarningsBySkill[$skillName] / $count;
            }

            $skillAvg = new Skill_Avg(
                $average . '',
                $skillName,
                $this->scrapeLog->getScrapeLogId(),
                date('Y-m-d H:i:s')
            );

            $skillAvg->save();

            $skillsAvg[] = $skillAvg;
        }

        return $skillsAvg;
    }

    public abstract function loadAllEarnings();

    /**
     * @return array
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function getSkillArray() : array
    {
        $mapper = new Mapper('skills');
        $allSkills = $mapper->getCollection('WHERE platform_id = ' . $this->scrapeLog->getPlatformId());
        $skillsArray = [];

        /** @var Skill $skill */
        foreach ($allSkills as $skill) {
            $skillsArray[$skill->getSkillId()] = $skill->getSkillName();
        }

        return $skillsArray;
    }


    /**
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function loadAllSkills()
    {
        $skillsByUser = [];
        $userIdsArray = [];
        $userArray = array_chunk($this->users, 5000);

        /**
         * @var int $counter
         * @var array $users
         */
        foreach ($userArray as $counter => $users) {
            $userIdsArray[$counter] = '';

            /** @var User $user */
            foreach ($users as $user) {
                $userIdsArray[$counter] .= $user->getUserId() .  ', ';
                $skillsByUser[$counter][$user->getUserId()] = [];
            }
        }

        foreach ($userIdsArray as $counter => $userIdString) {
            $usersIds = substr($userIdString, 0, -2);
            $whereCause = 'WHERE user_id IN (' . $usersIds . ')';
            $mapper = new Mapper('user_skills');
            $userSkills = $mapper->getCollection($whereCause);

            /** @var User_Skill $userSkill */
            foreach ($userSkills as $userSkill) {
                $skillName = $this->skillArray[$userSkill->getSkillId()];
                if (in_array($skillName, $skillsByUser[$counter][$userSkill->getUserId()], true)) {
                    continue;
                }

                $skillsByUser[$counter][$userSkill->getUserId()][] = $skillName;
            }
        }

        foreach ($userArray as $counter => $users) {
            foreach ($users as $user) {
                $user->setSkills($skillsByUser[$counter][$user->getUserId()]);
            }
        }

        echo 'Loaded all skillArray' . "\r\n";
    }

    /**
     * @return array
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function getUsers() : array
    {
        $mapper = new Mapper('users');
        return $mapper->getCollection('WHERE scrape_log_id = ' . $this->scrapeLog->getScrapeLogId());
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function loadCurrencyValues()
    {
        $mapper = new Mapper('currency_values');

        $currencyValues = $mapper->getCollection('WHERE scrape_log_id = ' . $this->scrapeLog->getScrapeLogId());
        $currencyArray = [];

        /** @var Currency_Value $currencyValue */
        foreach ($currencyValues as $currencyValue) {
            $currencyArray[$currencyValue->getCurrencyId()] = $currencyValue->getValueToDollar();
        }

        $this->currencyValues = $currencyArray;
    }
}