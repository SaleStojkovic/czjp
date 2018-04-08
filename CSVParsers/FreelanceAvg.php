<?php

namespace CZJPScraping\CSVParsers;

use CZJPScraping\Models\Mapper;
use CZJPScraping\Models\User;
use CZJPScraping\Models\User_Job;

include_once __DIR__ . '/AmericanAvgAbstract.php';

class FreelanceAvg extends AmericanAvgAbstract
{
    /**
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function loadAllEarnings()
    {
        $userIdsArray = [];
        $jobsPerUser = [];

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
                $jobsPerUser[$counter][$user->getUserId()] = 0;
            }
        }

        /**
         * @var int $counter
         * @var string $userIds
         */
        foreach ($userIdsArray as $counter => $userIds) {
            $usersIds = substr($userIds, 0, -2);
            $whereCause = 'WHERE user_id IN (' . $usersIds . ')';
            $mapper = new Mapper('user_jobs');
            $userJobs = $mapper->getCollection($whereCause);

            /** @var User_Job $userJob */
            foreach ($userJobs as $userJob) {
                $moneyEarned = (int) $userJob->getEarned();
                $currencyId = (int) $userJob->getCurrencyId();
                $currencyValue = $this->currencyValues[$currencyId] ?? 1;
                $jobsPerUser[$counter][$userJob->getUserId()] += $moneyEarned * (float) $currencyValue;
            }
        }

        foreach ($userArray as $counter => $users) {
            foreach ($users as $user) {
                $user->setTotalMoneyEarned($jobsPerUser[$counter][$user->getUserId()]);
            }
        }

        echo 'Loaded all earnings' . "\r\n";
    }
}

$parser = new FreelanceAvg(4, 'freelanceAvg');

$parser->makeCsv();