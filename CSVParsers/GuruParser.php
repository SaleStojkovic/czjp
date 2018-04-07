<?php

namespace CZJPScraping\CSVParsers;

include_once __DIR__ . '/AbstractParser.php';

use CZJPScraping\Models\Guru_Anual_Earning;
use CZJPScraping\Models\User;
use CZJPScraping\Models\Mapper;
use CZJPScraping\Models\User_Rating;

/**
 * Class GuruParser
 * @package CZJPScraping\CSVParsers
 */
class GuruParser extends AbstractParser
{
    /**
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function loadAllReviews()
    {
        $usersIds = '';
        $reviewsByUser = [];
        $reviewScoreByUsers = [];

        /** @var User $user */
        foreach ($this->users as $user) {
            $usersIds .= $user->getUserId() .  ', ';
            $reviewsByUser[$user->getUserId()] = [];
            $reviewScoreByUsers[$user->getUserId()] = [];
        }

        $usersIds = substr($usersIds, 0, -2);
        $whereCause = 'WHERE user_id IN (' . $usersIds . ')';
        $mapper = new Mapper('user_ratings');
        $userRatings = $mapper->getCollection($whereCause);

        /** @var User_Rating $userRating */
        foreach ($userRatings as $userRating) {
            $rating = $userRating->getRatingComment();
            if (in_array($rating, $reviewsByUser[$userRating->getUserId()], true)) {
                continue;
            }

            $reviewsByUser[$userRating->getUserId()][] = $rating;
            $reviewScoreByUsers[$userRating->getUserId()][] = $userRating->getRatingScore();
        }

        foreach ($this->users as $user) {
            $user->setReviews($reviewsByUser[$user->getUserId()]);
            $user->setScores($reviewScoreByUsers[$user->getUserId()]);
        }

        echo 'Loaded all reviews' . "\r\n";
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function loadMoneyEarned()
    {
        $usersIds = '';
        $jobsPerUser = [];

        /** @var User $user */
        foreach ($this->users as $user) {
            $usersIds .= $user->getUserId() .  ', ';
            $jobsPerUser[$user->getUserId()] = 0;
        }

        $usersIds = substr($usersIds, 0, -2);
        $whereCause = 'WHERE user_id IN (' . $usersIds . ')';
        $mapper = new Mapper('guru_anual_earnings');
        $earnings = $mapper->getCollection($whereCause);

        /** @var Guru_Anual_Earning $earning */
        foreach ($earnings as $earning) {
            $moneyEarned = (int) $earning->getAmount();

            $jobsPerUser[$earning->getUserId()] += $moneyEarned;
        }

        foreach ($this->users as $user) {
            $user->setTotalMoneyEarned($jobsPerUser[$user->getUserId()]);
        }

        echo 'Loaded all earnings' . "\r\n";
    }
}
$parser = new GuruParser('7', 'guruV1');

$parser->makeCsv();