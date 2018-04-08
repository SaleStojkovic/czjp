<?php

namespace CZJPScraping\Controllers;

use CZJPScraping\Models\Nationalities;
use CZJPScraping\Models\User;
use CZJPScraping\Models\Mapper;
use CZJPScraping\Models\User_Earning;
use CZJPScraping\Models\User_Earning_Comment;
use CZJPScraping\Models\User_Skill;
use GuzzleHttp\Client;

include_once __DIR__ . '/AbstractScraper.php';

class PeoplePerHourScrape extends AbstractScraper
{
    //todo ovo najverovatnije sve moze da se obrise
    public  function run() : bool
    {
        $url = 'https://www.peopleperhour.com/freelance?location=RS';

        $maxPage = $this->findMaxPage($this->getHttpPage($url));

        $userLinks = [];
        $bidsArray = [];
        $j = 0;

        for ($i = 1; $i < $maxPage; $i++) {
            $contents = $this->getHttpPage($url . '&page=' . $i);
            $matches = $this->matchTagContent('h5', '', $contents);

            foreach ($matches[1] as $match) {
                $userLinks[] = $this->extractHrefFromATag($match);
            }

            $bidsRaw = $this->matchTagContent('div', 'class="medium price-tag quiet"', $contents)[1];

            foreach ($bidsRaw as $bidRaw) {
                $bidsArray[$j] = str_replace(['<small>PER HOUR</small>', '<span title="">', '</span>', '$'],'',$bidRaw);

                $j++;
            }

            unset($matches, $contents, $response, $bidsRaw);
        }

        foreach ($userLinks as $link) {
            $contents = $this->getHttpPage($link);

            $userNameDiv = $this->matchTagContent('div', 'class="seller-name light"', $contents)[1][0];
            $userName = $this->matchTagContent('h2', '', $userNameDiv)[1][0];
            $userName = ltrim(strip_tags($userName));

            $biographyDiv = $this->matchTagContent('div', 'class="about-container js-about-container"', $contents)[1][0];
            $biography = $this->matchTagContent('span', 'class="js-about-full-text"', $biographyDiv)[1][0];
            $biography = strip_tags($biography);

//            $newUser = new User(
//                $userName,
//                $this->genderHelper->determineGender($userName),
//                $biography,
//                (int)$bidsArray[$j],
//                $this->genderHelper::US,
//                $this->scrape_log
//            );
//            $newUser->save();


//            $skillsRaw = $this->matchTagContent('a', 'class="tag-item small"', $contents)[1];
//
//            foreach ($skillsRaw as $skill) {
//                $skillId = $this->skillHelper->getSkillName($skill);
//
//                $newUserSkill = new User_Skill(
//                    $newUser->getUserId(),
//                    $skillId
//                );
//
//                $newUserSkill->save();
//            }

            $offerDivs = $this->matchTagContent('div', 'class="hourlie-wrapper clearfix"', $contents)[1];
            $offerUrls = [];
            $soldArray = [];

            foreach ($offerDivs as $offerDiv) {
                $offerUrls[] = $this->extractHrefFromATag($offerDiv);
                $soldRaw = $this->matchTagContent('div', 'class="sales__info"', $offerDiv)[1][0];

                $soldArray[] = $this->matchTagContent('span', 'class="value"', $soldRaw)[1][0];
            }

//            $commentUrl = href="/hourlie/view?Feedback_page=2&ajax=reviews-list&id=262675"

            $counter = 0;

            foreach ($offerUrls as $offerUrl) {
                $offerPage = $this->getHttpPage($offerUrl);
                $jobName = $this->matchTagContent('h1', 'class="clearfix"', $offerPage)[1][0];

                $jobPriceRaw = $this->matchTagContent('span', 'class="js-hourlie-discounted-price discounted-price"', $offerPage)[1][0];
                $jobPrice = substr(ltrim($jobPriceRaw), 1);

                $sold = $soldArray[$counter];
                $counter++;

                //todo insertHourlie

                $comments[] = $this->matchTagContent('li', 'class="item participant feedback clearfix "', $offerPage)[1][0];
                $otherCommentUrls = $this->findAllAjaxUrlForComments($offerPage);

                //todo getting and saving all comments
                foreach ($otherCommentUrls as $commentUrl) {
                    $newCommentPage = $this->getHttpPage($offerUrl . $commentUrl);
                    $comments[] = $this->matchTagContent('li', 'class="item participant feedback clearfix "', $newCommentPage)[1][0];
                }

//                var_dump($comments);
            }

            unset($contents);
        }

        unset($userLinks, $bidsArray);

        return true;
    }

    /**
     * @param $nationality
     * @return array
     */
    private function getUsersLinksAndBids($nationality) : array
    {
        $url = 'https://www.peopleperhour.com/freelance?location=RS';

        if ($nationality === Nationalities::AMERICAN) {
            $url = 'https://www.peopleperhour.com/freelance?location=US';
        }

        $maxPage = $this->findMaxPage($this->getHttpPage($url));

        $returnArray['userLinks'] = [];
        $returnArray['bids'] = [];

        $j = 0;

        for ($i = 1; $i <= $maxPage; $i++) {
            $contents = $this->getHttpPage($url . '&page=' . $i);
            $matches = $this->matchTagContent('h5', '', $contents);

            foreach ($matches[1] as $match) {
                $returnArray['userLinks'][] = $this->extractHrefFromATag($match);
            }

            $bidsRaw = $this->matchTagContent('div', 'class="medium price-tag quiet"', $contents)[1];

            foreach ($bidsRaw as $bidRaw) {
                $returnArray['bids'][$j] = str_replace(['<small>PER HOUR</small>', '<span title="">', '</span>', '$'],'',$bidRaw);

                $j++;
            }

            unset($matches, $contents, $response, $bidsRaw);
        }

        return $returnArray;
    }

    public function saveUsers($nationality)
    {
        $linksAndBidsArray = $this->getUsersLinksAndBids($nationality);

        echo "\r\n" . 'Fetched links! Now I will save users...';

        $countTotal = count($linksAndBidsArray['userLinks']);

        $counter = 1;
        $j = 0;

        foreach ($linksAndBidsArray['userLinks'] as $link) {
            $contents = $this->getHttpPage($link);

            $userNameDiv = $this->matchTagContent('div', 'class="seller-name light"', $contents)[1][0];
            $userName = $this->matchTagContent('h2', '', $userNameDiv)[1][0];
            $userName = ltrim(strip_tags($userName));

            $biographyDiv = $this->matchTagContent('div', 'class="about-container js-about-container"', $contents)[1][0];
            $biography = $this->matchTagContent('span', 'class="js-about-full-text"', $biographyDiv)[1][0];
            $biography = strip_tags($biography);

            $userBid = (int)$linksAndBidsArray['bids'][$j];

            $newUser = new User(
                $userName,
                $this->genderHelper->determineGender($userName),
                $biography,
                $userBid,
                $this->genderHelper::US,
                $this->scrape_log,
                null,
                $nationality,
                $link

            );

            $newUser->save();

            echo "\r\n" . 'Saved user ' . $counter . ' out of ' . $countTotal;
            $counter++;
            $j++;
        }
    }

    public function saveUsersAndTheirSkills($scrapeLogId, $nationality)
    {
        $linksAndBidsArray = $this->getUsersLinksAndBids($nationality);

        echo "\r\n" . 'Fetched links! Now I will save users...';

        $countTotal = count($linksAndBidsArray['userLinks']);

        $counter = 1;
        $j = 0;

        foreach ($linksAndBidsArray['userLinks'] as $link) {
            $contents = $this->getHttpPage($link);

            $userNameDiv = $this->matchTagContent('div', 'class="seller-name light"', $contents)[1][0];
            $userName = $this->matchTagContent('h2', '', $userNameDiv)[1][0];
            $userName = ltrim(strip_tags($userName));

            $biographyDiv = $this->matchTagContent('div', 'class="about-container js-about-container"', $contents)[1][0];
            $biography = $this->matchTagContent('span', 'class="js-about-full-text"', $biographyDiv)[1][0];
            $biography = strip_tags($biography);

            $userBid = (int)$linksAndBidsArray['bids'][$j];

            $newUser = new User(
                $userName,
                $this->genderHelper->determineGender($userName),
                $biography,
                $userBid,
                $this->genderHelper::US,
                $scrapeLogId,
                null,
                $nationality,
                $link

            );

            $newUser->save();

            echo "\r\n" . 'Saved user ' . $counter . ' out of ' . $countTotal;

            $skillsRaw = $this->matchTagContent('a', 'class="tag-item small"', $contents)[1];

            $skillCount = count($skillsRaw);
            $skillCounter = 1;

            if ($skillsRaw) {
                foreach ($skillsRaw as $skill) {
                    $skillId = $this->skillHelper->getSkillId($skill);

                    $newUserSkill = new User_Skill(
                        $newUser->getUserId(),
                        $skillId
                    );

                    $newUserSkill->save();

                    echo "\r\n" . 'Saved skill ' . $skillCounter . ' out of ' . $skillCount;
                    $skillCounter++;
                }
            }

            echo "\r\n" . 'Saved user ' . $counter . ' out of ' . $countTotal;
            $newUser->changeImportedSkills();
            $counter++;
        }
    }

    public function findMaxPage($content) : int
    {
        $pageCountArray = $this->matchTagContent('li', 'class="hidden-xs"', $content)[1];

        $max = 1;

        foreach ($pageCountArray as $pageCount) {
            $rawCount = $this->matchTagContent('a', '', $pageCount);
            $count = (int)$rawCount[1][0];

            $max = $count > $max ? $count : $max;
        }

        return $max;
    }

    public function findAllAjaxUrlForComments($offerPage)
    {
        $allCommentsUrls = [];
        $pageCountArray = $this->matchTagContent('li', 'class="hidden-xs"', $offerPage);

        if ($pageCountArray === false) {
            return $allCommentsUrls;
        }

        foreach ($pageCountArray[1] as $pageCount) {
            $urlRaw = $this->matchTagContent('a', '', $pageCount);
            if (!$urlRaw) {
                continue;
            }
            $allCommentsUrls[] = $this->extractHrefFromATag($urlRaw[0][0]);
        }

        return $allCommentsUrls;
    }

    public function saveHourlies($scrapeLogId)
    {
        $mapper = new Mapper('users');

        $users = $mapper->getCollection('WHERE scrape_log_id = ' . $scrapeLogId . ' AND imported_jobs = 0');

        $userCount = count($users);

        $counter = 1;
        /** @var User $user */
        foreach ($users as $user) {
            $userId = $user->getUserId();

            $contents = $this->getHttpPage($user->getUrlPage());
            $offerDivs = $this->matchTagContent('div', 'class="hourlie-wrapper clearfix"', $contents)[1];

            $offerUrls = [];
            $soldArray = [];
            $i = 0;

            if ($offerDivs) {
                foreach ($offerDivs as $offerDiv) {
                    $offerUrls[] = $this->extractHrefFromATag($offerDiv);

                    $soldRaw = $this->matchTagContent('div', 'class="sales__info"', $offerDiv)[1][0];

                    $soldArray[$i] = $this->matchTagContent('span', 'class="value"', $soldRaw)[1][0];
                    $i++;
                }

                $j = 0;
                $hourlieCount = count($offerUrls);

                foreach ($offerUrls as $offerUrl) {
                    $offerPage = $this->getHttpPage($offerUrl);

                    $jobName = ltrim($this->matchTagContent('h1', 'class="clearfix"', $offerPage)[1][0]);

                    $jobPriceRaw = $this->matchTagContent('span', 'class="js-hourlie-discounted-price discounted-price"', $offerPage)[1][0];
                    $jobPrice = substr(ltrim($jobPriceRaw), 1);

                    $sold = $soldArray[$j];

                    $newHourlie = new User_Earning(
                        $jobName,
                        $userId,
                        $jobPrice,
                        $sold
                    );

                    $newHourlie->save();

                    $comments = $this->getAllHourlieComments($offerPage, $offerUrl);

                    if ($comments) {
                        $commentCounter = 0;
                        foreach ($comments as $comment) {
                            $comment = $this->matchTagContent('p', '', $comment)[1][0];
                            $comment = htmlspecialchars_decode($comment);
                            $comment = html_entity_decode($comment);
                            $comment = strip_tags($comment);

                            $newHourlieComment = new User_Earning_Comment(
                                $newHourlie->getUserEarningId(),
                                ltrim($comment)
                            );

                            $newHourlieComment->save();
                            echo "\r\n" . 'Saved ' . $commentCounter . ' out of ' . count($comments) . ' comments';
                            $commentCounter++;
                        }
                    }

                    $j++;

                    echo "\r\n" . 'Saved ' . $j . ' hourlie out of ' . $hourlieCount;
                }
            }

            $counter++;
            echo "\r\n" . 'Finished ' . $counter . ' user out of ' . $userCount;
            $user->changeImportedJobs();
            $user->changeImportedReviews();
        }
    }

    private function getAllHourlieComments($offerPage, $offerUrl)
    {
        $comments = $this->matchTagContent('li', 'class="item participant feedback clearfix "', $offerPage)[1];

        $otherCommentUrls = $this->findAllAjaxUrlForComments($offerPage);
        //getting all comments
        $counter = 2;
        if ($otherCommentUrls !== []) {
            foreach ($otherCommentUrls as $commentUrl) {
                $newCommentPage = $this->getHttpPage($offerUrl . '&Feedback_page=' . $counter);
                $newComments = $this->matchTagContent('li', 'class="item participant feedback clearfix "', $newCommentPage)[1];

                if ($newComments) {
                    foreach ($newComments as $newComment) {
                        $comments[] = $newComment;
                    }
                }

                $counter++;
            }
        }

        return $comments;
    }
}

$scraper = new PeoplePerHourScrape('6', '2');
//
//$scraper->saveUsers(Nationalities::AMERICAN);

echo 'Saving user hourlies' . "\r\n";

$scraper->saveHourlies('6');

echo "\r\n" . 'Done!' . "\r\n";




