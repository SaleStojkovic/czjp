<?php
namespace CZJPScraping\CSVParsers;

include_once __DIR__ . '/AbstractParser.php';

use CZJPScraping\Models\User;
use CZJPScraping\Models\Mapper;
use CZJPScraping\Models\User_Earning_Comment;
use CZJPScraping\Models\User_Earning;

/**
 * Class PeoplePerHourParser
 * @package CZJPScraping\CSVParsers
 */
class PeoplePerHourParser extends AbstractParser
{
    /**
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function loadAllReviews()
    {
        $usersIds = '';
        $userEarningsArray = [];
        $userCommentsArray = [];
        //TODO ovo treba da se cuva
        $moneyUserEarned = [];

        /** @var User $user */
        foreach ($this->users as $user) {
            $usersIds .= $user->getUserId() .  ', ';
            $userEarningsArray[$user->getUserId()] = [];
            $userCommentsArray[$user->getUserId()] = [];
            $moneyUserEarned[$user->getUserId()] = 0;
        }

        $usersIds = substr($usersIds, 0, -2);
        $whereCause = 'WHERE user_id IN (' . $usersIds . ')';
        $mapper = new Mapper('user_earnings');
        $userEarnings = $mapper->getCollection($whereCause);

        /** @var User_Earning $userEarning */
        foreach ($userEarnings as $userEarning) {
            $userEarningsArray[$userEarning->getUserId()][] = $userEarning->getUserEarningId();
            $moneyUserEarned[$userEarning->getUserId()] += ((int) $userEarning->getPrice()) * ((int) $userEarning->getSold());
        }

        $mapper2 = new Mapper('user_earning_comments');
        $whereCause2 = 'WHERE user_earning_id IN (';

        foreach ($userEarningsArray as $userId => $ratingIds) {
            $ratingsIds = implode (', ',  $ratingIds);

            $userComments = $mapper2->getCollection($whereCause2 . $ratingsIds . ')');

            /** @var User_Earning_Comment $comment */
            foreach ($userComments as $comment) {
                $userCommentsArray[$userId][] = $comment->getComment();
            }
        }

        foreach ($this->users as $user) {
            $user->setReviews($userCommentsArray[$user->getUserId()]);
            $user->setTotalMoneyEarned($moneyUserEarned[$user->getUserId()]);
        }

        echo 'Loaded all comments and earnings' . "\r\n";
    }

    public function loadMoneyEarned()
    {
        
    }
}
$parser = new PeoplePerHourParser('5', 'peoplePerHour');

$parser->makeCsv();