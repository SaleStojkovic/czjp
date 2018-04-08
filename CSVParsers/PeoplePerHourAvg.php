<?php

namespace CZJPScraping\CSVParsers;

use CZJPScraping\Models\Mapper;
use CZJPScraping\Models\User;
use CZJPScraping\Models\User_Earning;

include_once __DIR__ . '/AmericanAvgAbstract.php';

class PeoplePerHourAvg extends AmericanAvgAbstract
{
    /**
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function loadAllEarnings()
    {
        $userIdsArray = [];
        $moneyUserEarned = [];

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
                $moneyUserEarned[$counter][$user->getUserId()] = 0;
            }
        }

        foreach ($userIdsArray as $counter => $usersIds) {
            $usersIds = substr($usersIds, 0, -2);
            $whereCause = 'WHERE user_id IN (' . $usersIds . ')';
            $mapper = new Mapper('user_earnings');
            $userEarnings = $mapper->getCollection($whereCause);

            /** @var User_Earning $userEarning */
            foreach ($userEarnings as $userEarning) {
                $moneyUserEarned[$counter][$userEarning->getUserId()] += ((int) $userEarning->getPrice()) * ((int) $userEarning->getSold());
            }
        }

        foreach ($userArray as $counter => $users) {
            foreach ($users as $user) {
                $user->setTotalMoneyEarned($moneyUserEarned[$counter][$user->getUserId()]);
            }
        }

        echo 'Loaded all comments and earnings' . "\r\n";
    }
}
try {
    $parser = new PeoplePerHourAvg(6, 'peoplePerHourAvg');
    $parser->makeCsv();
} catch (\Exception $exception) {
    throw new $exception;
}
