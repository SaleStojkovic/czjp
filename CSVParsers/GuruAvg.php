<?php

namespace CZJPScraping\CSVParsers;

use CZJPScraping\Models\Guru_Anual_Earning;
use CZJPScraping\Models\Mapper;
use CZJPScraping\Models\User;

include_once __DIR__ . '/AmericanAvgAbstract.php';

class GuruAvg extends AmericanAvgAbstract
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

        foreach ($userIdsArray as $counter => $userIdString) {
            $usersIds = substr($userIdString, 0, -2);
            $whereCause = 'WHERE user_id IN (' . $usersIds . ')';
            $mapper = new Mapper('guru_anual_earnings');
            $earnings = $mapper->getCollection($whereCause);

            /** @var Guru_Anual_Earning $earning */
            foreach ($earnings as $earning) {
                $moneyEarned = (int) $earning->getAmount();

                $jobsPerUser[$counter][$earning->getUserId()] += $moneyEarned;
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

try {
    $parser = new GuruAvg(8, 'guruAvg');
    $parser->makeCsv();
} catch (\Exception $exception) {
    throw new $exception;
}