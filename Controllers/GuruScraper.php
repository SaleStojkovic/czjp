<?php

namespace CZJPScraping\Controllers;
use CZJPScraping\Models\GenderHelper;
use CZJPScraping\Models\Guru_Anual_Earning;
use CZJPScraping\Models\Mapper;
use CZJPScraping\Models\Nationalities;
use CZJPScraping\Models\Scrape_Log;
use CZJPScraping\Models\Skill;
use CZJPScraping\Models\Skill_Url_Scrape_Log;
use CZJPScraping\Models\User;
use CZJPScraping\Models\User_Rating;
use CZJPScraping\Models\User_Skill;
use function simplehtmldom_1_5\file_get_html;
use simplehtmldom_1_5\simple_html_dom;

include_once __DIR__ . '/../vendor/sunra/php-simple-html-dom-parser/Src/Sunra/PhpSimple/simplehtmldom_1_5/simple_html_dom.php';

include_once __DIR__ . '/AbstractScraper.php';

/**
 * Class GuruScraper
 * @package CZJPScraping\Controllers
 */
class GuruScraper extends AbstractScraper implements ScrapeInterface
{
    public $cookie;

    //todo ovo izbrisi
    public $allUserNames;

    /**
     * GuruScraper constructor.
     * @param $scrapeLogId
     * @param $platformId
     * @param $cookie
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function __construct($scrapeLogId, $platformId, $cookie)
    {
        $this->cookie = $cookie;
        parent::__construct($scrapeLogId, $platformId);

        $mapper = new Mapper('users');

        $allUsers = $mapper->getCollection('WHERE scrape_log_id =' . $scrapeLogId);

        $allUserNames = [];

        /** @var User $user */
        foreach ($allUsers as $user) {
            $allUserNames[] = $user->getUsername();
        }

        $this->allUserNames = $allUserNames;
    }

    public function getHttpPage($url)
    {
        $agent= 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.162 Safari/537.36';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_ACCEPT_ENCODING, 'gzip, deflate, br');
        curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_URL, $url);

        $result = curl_exec($ch);

        curl_close($ch);

        return $result;
    }

    public function run() : bool
    {
        return true;
    }

    public function getAllSkills()
    {
        $url = 'https://www.guru.com/m/hire/freelancers/all-skills/';

        $page = $this->getHttpPage($url);

        $skillsSet = $this->matchTagContent('ul', 'class="c-allSkills__links u-clearfix o-list3x"', $page)[1];

        foreach ($skillsSet as $value) {
            $skills = $this->matchTagContent('a', '', $value)[1];

            foreach ($skills as $skill) {
                $newSkill = new Skill(
                    $skill,
                    '3'
                );

                $newSkill->save();
            }
        }
    }

    public function saveUsers($nationality)
    {
        $skillUrls = [
            'Programming & Dev' => '/programming-dev/',
            'Design & Art' => '/design-art/',
            'Writing & Translation' => '/writing-translation/',
            'Administrative & Secretarial' => '/administrative-secretarial/',
            'Business & Finance' => '/business-finance/',
            'Sales & Marketing' => '/sales-marketing/',
            'Engineering & Architecture' => '/engineering-architecture/',
            'Other' => '/other/',
            'Legal' => '/legal/'
        ];

        foreach ($skillUrls as $skillString => $skillUrl) {
            echo "\r\n" . 'Now saving ' . $skillString;
            $this->saveUsersBySkill($nationality, $skillUrl, $skillString);
        }
    }

    private function saveUsersBySkill($nationality, $skillUrl, $skillString)
    {
        $url = 'https://www.guru.com/d/freelancers/';

        if ($nationality === Nationalities::AMERICAN) {
            $url .= 'l/united-states/c';
        } else {
            $url .= 'l/serbia/c';
        }

        $url .= $skillUrl . '/pg/';

        $mapper = new Mapper('skill_url_scrape_logs');

        /** @var Skill_Url_Scrape_Log $scrapeLog */
        $scrapeLog = $mapper->getCollection(" WHERE scrape_log_id = '" . $this->scrape_log . "' AND skill_url = '" . $skillUrl . "'");

        if (!$scrapeLog) {
            $scrapeLog = new Skill_Url_Scrape_Log(
                $this->scrape_log,
                $skillUrl
            );

            $scrapeLog->save();
        } else {
            $scrapeLog = $scrapeLog[0];
        }

        $i = (int)$scrapeLog->getLastFetchedPage();
        $i++;

        $page = $this->getHttpPage($url . $i .'/');
        $users = $this->matchTagContent('li', 'class="serviceItem clearfix"', $page)[1];

        if (!$users) {
            echo "\r\n" . 'Cookie has expired, mate! Or there are no more users! Check browser!';
            return;
        }
        $this->saveUsersFromDivs($users, $nationality, $skillString);
        echo "\r\n" . 'Page ' . $i . ' - Done! Waiting for another set of users...';

        sleep(1);

        while ($users) {
            $i++;

            $page = $this->getHttpPage($url . $i .'/');
            sleep(1);

            $users = $this->matchTagContent('li', 'class="serviceItem clearfix"', $page)[1];

            if (!$users) {
                echo "\r\n" . 'Cookie has expired or the scrape is finished. Page number ' . $i . ' Go check it out using browser!';
                break;
            }
            $scrapeLog->updateLastPageFetched($i);
            $this->saveUsersFromDivs($users, $nationality, $skillString);

            echo "\r\n" . 'Page ' . $i . ' - Done! Waiting for another set of users...';
        }
    }

    public function saveUsersFromDivs($usersDivs, $nationalityId, $skillString)
    {
        $userCount = count($usersDivs);
        $i = 0;
        $skillId = $this->skillHelper->getSkillId($skillString);

        foreach ($usersDivs as $userDiv) {
            $userNameRaw = $this->matchTagContent('h3', 'class="identityName"', $userDiv)[1][0];
            $userName = trim($this->matchTagContent('a', '', $userNameRaw)[1][0]);

            if (in_array($userName, $this->allUserNames, true)) {
                echo "\r\n" . 'User already exists';
                continue;
            }

            $userUrl = $this->extractHrefFromATag($userNameRaw);

            $biography = trim($this->matchTagContent('p','class="desc"', $userDiv)[1][0]);
            $earning = $this->matchTagContent('p','class="subtext"', $userDiv)[1][0];

            $dollarPosition = strpos($earning, '$');
            $earning = substr($earning, $dollarPosition);
            $earning = str_replace(['Earned', '$', ','], '', $earning);
            $earning = trim($earning);

            $newUser = new User(
                $userName,
                $this->genderHelper->determineGender($userName),
                $biography,
                null,
                GenderHelper::US,
                $this->scrape_log,
                null,
                $nationalityId,
                'https://www.guru.com' . $userUrl
            );

            $newUser->save();

            $newGuruAnualEarning = new Guru_Anual_Earning(
                $newUser->getUserId(),
                $earning,
                $this->scrape_log
            );

            $newGuruAnualEarning->save();

            $newUserSkill = new User_Skill(
                $newUser->getUserId(),
                $skillId
            );

            $newUserSkill->save();

            $i++;
            echo "\r\n" . 'Saved ' . $i . ' out of ' . $userCount;
        }

    }

    public function saveUserReviewsAndQualifications($nationalityId)
    {
        $mapper = new Mapper('users');
        $whereCause = 'WHERE nationality_id = ' . $nationalityId . ' AND scrape_log_id = ' . $this->scrape_log .
            ' AND imported_reviews = 0';
        $allUsers = $mapper->getCollection($whereCause);

        if (!$allUsers) {
            echo "\r\n" . 'All users reviews fetched!';
            return;
        }

        $userCount = count($allUsers);

        $userCounter = 1;
        /** @var User $user */
        foreach ($allUsers as $user) {
            $url = $user->getUrlPage() . '/reviews';
            $content = $this->getHttpPage($url);
            $actualReviews = $this->matchTagContent('div', 'id="hdnProfileReviewsContainer"', $content);

            $user->changeImportedReviews();

            if (!$actualReviews) {
                continue;
            }

            $actualReviews = htmlspecialchars_decode($actualReviews[1][0]);
            $valuePosition = strpos($actualReviews, 'value');
            $actualReviews = substr($actualReviews, $valuePosition);
            $actualReviews = str_replace(['value="', '" />'], '', $actualReviews);
            $actualReviews = json_decode($actualReviews);

            $reviewsCount = count($actualReviews);
            $reviewsCounter = 1;

            foreach ($actualReviews as $actualReview) {
                $newUserReview = new User_Rating(
                    $user->getUserId(),
                    $actualReview->FeedbackScore,
                    $actualReview->ReviewComment
                );

                $newUserReview->save();

                echo "\r\n" . 'Saved review ' . $reviewsCounter++ . ' out of ' . $reviewsCount;
            }
            echo "\r\n" . 'Saved user ' . $userCounter++ . ' out of ' . $userCount;
        }
        echo "\r\n" . 'All users reviews fetched!';
    }
}

$cookie = 'visid_incap_1227176=lSM67Z2mQBiTWfTdTkqQ5EeAs1oAAAAAQUIPAAAAAACJyh+PM1qzErSqVC4B4p3O; _ga=GA1.2.101285913.1521713227; ASP.NET_SessionId=ziw45ub4aqquajn4qnu04cks; nlbi_1227176=31xbTM9PjnhYmDHVNw2eVgAAAABJcpBT//gL44e/M9OPUroX; incap_ses_534_1227176=imbXIu5ei1Rv6Wl2oiZpB9OwyFoAAAAAdGhbkVTHx20kpoJ95xOiMQ==; ___utmvc=qPq5ybzGAl8m+fmr1cH0OoZFOd3KrBUVMO7jKynojrHTs9+9oP2AHx+X+CK1m+t5ElaxpWJxisRBXDCkKqQenbJXUF7rR6Ub1FBwjoOlh/6oWj0HaNLBES44L9u+Gta3p+0Vdeqoc5JzdrGwxvpXAh7FtxQfnrHc3YKbrZknPr+GWtZ7NhKEhr5XQxxqtZD8zwGLu2TYiztDdOc0oLUCfJ76BdM0yRNrgUfQWWdqtNtveY/tKcPLCHYmnFRAwFevdrQZypLqSb1lkQ7Yj5+bPpp8gjz8Wf1vVF3Qlbwfry2TWXNCvRIZK1mMOfHCXWdC2kEHFeOCxxrk26sB4fl8axKM6JHpUq9hxCPligulj0/8bE3TBpPfhoza2PU7XRINeu3CreRfwK1GtIH2B36jaMhYK+Q7d9GsaiOz2msp5uO06aCq1Cc2U5NRkj3wnswcLu274hIUfx2L73TE85+yuw9YZIc6B0Iau7g4Rpf9rkYL+8I9xQdpwdw7KSOlQg36M5AXYvilcd1fgI33Df5K/KNacBor+PR0Rjpts0w6JV5bg8Xii6ncjvLZFZUoXyofX/QfsWSrTjr+hKFI+Sr2n0CSlFfEehzNEwYS2EXe7Ppsp97xgKaFUd56d3HpJvwyl10dHXqpx/Mv8d1TG9snTecqN0QTezkffihXkUvjzpYk6CNv0HZY+QKOGcOjoO6wXv9Mx/a4pDujFf3q7URSjzLVbxMKSWXv7/GBsME/LLKZU3VpeS2MDVmwEcf+UB3wgrLY6LhfeZPcMjEB1vFf8hAtoDr9DgmXuIosw3ckHEnoN53yl21siif33x8WAranvPCzz0iGYfa7zIjQyCCc/lgNMD6HKcd6Y6k8yOFbmRQrhVhrBXE2Uce9gAPg9cBHKQ5WvXQ+hR4RA7+VOQrWc4p7ZvK5mn/SNkhmtSMu60VsiGsP2LmIQkv0HiphQu4a5rH9ZVAxcj00W80p2vF+1Po3eqKDx364ECC8jZKcm5nRC712HMFulwJoiW2I95n+qZO/q4Gf1OPyUy3v6Wk+wQVqeHTgH2GHCJVXmsU00opDgg9QXEQapqbwyhxBWwj2hSOUyfMsZGlnZXN0PTgxMTE1LHM9Njg3YThiNjI3YjljODU5ZTdkODc2MzYyNzQ5ZjdiOGI3ZDkzNWNhZTY5ODhhNzllOWM3Y2FhNzc3NGFiODNhNGFiODA4Y2FhN2M4MjZlNzI=';
$scraper = new GuruScraper('7', '3', $cookie);
$scraper->saveUserReviewsAndQualifications(Nationalities::SERBIAN);

echo 'Done!';








