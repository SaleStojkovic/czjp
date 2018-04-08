<?php

namespace CZJPScraping\Controllers;
//todo takodje da se skloni
use CZJPScraping\Models\Freelancer_Link;
use CZJPScraping\Models\Mapper;
use CZJPScraping\Models\Nationalities;
use CZJPScraping\Models\Qualification;
use CZJPScraping\Models\User;
use CZJPScraping\Models\User_Job;
use CZJPScraping\Models\User_Rating;
use CZJPScraping\Models\User_Skill;

//todo ovo da se skloni odavde
include_once __DIR__ . '/FreelanceThread.php';
include_once __DIR__ . '/AbstractScraper.php';
include_once __DIR__ . '/../Models/Freelancer_Link.php';

//todo ako bude jedna skripta onda bi mogao da se iskoristi Scrape Factory
class FreelanceScrape extends AbstractScraper implements ScrapeInterface
{
    const POST = 'POST';
    const PUT = 'PUT';
    const GET = 'GET';

    private $runForSAD;

    //todo da se instancira novi scrape log
    private $scrapeLog;

    public function __construct($scrape_log, $platform_id, $runForSAD = false)
    {
        parent::__construct($scrape_log, $platform_id);

        $this->runForSAD = $runForSAD;
    }

    //todo ne znam da li da se doda neki UI u smislu odaberi sta hoces itd.
    public function run() : bool
    {
        $serbians = $this->runForSerbians();
        $americans = true;

        if ($this->runForSAD) {
            $americans = $this->runForAmericans();
        }

        return $serbians && $americans;
    }

    //todo ovo da se iskoristi za novi scrape log
    public function getAllUsersCount()
    {
        $url = 'https://www.freelancer.com/api/users/0.1/users/directory/';

        $users = $this->callAPI(self::GET, $url);

        return $users->result->total_count;
    }

    /**
     * @param $method
     * @param $url
     * @param bool $data
     * @return mixed
     */
    public function callAPI($method, $url, $data = false)
    {
        $curl = curl_init();

        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data) {
                    $url = sprintf("%s?%s", $url, http_build_query($data));
                }
        }

        // Optional Authentication:
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($curl, CURLOPT_USERPWD, "username:password");
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $result = json_decode(curl_exec($curl));

        curl_close($curl);

        if ($result->status === 'error') {
            echo 'Too many calls, mate! :( Waiting...' . "\r\n";
            sleep(3610);
            $result = $this->callAPI($method, $url, $data);
        }

        return $result;
    }


    public function saveUsers($scrapeLogId)
    {
        $mapper = new Mapper('freelancer_links');
        $whereCause = 'WHERE scrape_log_id = ' . $scrapeLogId . ' AND executed = 0';
        $allLinks = $mapper->getCollection($whereCause);
        $linkCount = count($allLinks);

        $counter = 0;
        /** @var Freelancer_Link $allLink */
        foreach ($allLinks as $allLink) {
            $users = $this->callAPI('GET', $allLink->getLinkText())->result->users;

            $i = 0;

            foreach ($users as $userObject) {
                $newUser = new User(
                    $userObject->display_name ?? '',
                    $this->genderHelper->determineGender($userObject->display_name ?? ''),
                    $userObject->profile_description ?? '',
                    $userObject->hourly_rate . '',
                    $userObject->primary_currency->code === 'EUR' ? $this->genderHelper::EURO : $this->genderHelper::US,
                    $this->scrape_log,
                    $userObject->id,
                    '2'
                );

                $newUser->save();

                echo "\r\n" . 'Saved ' . $i++ . ' out of 100';
            }

            echo "\r\n" . 'Saved link ' . $counter++ . '/' . $linkCount;
        }
    }

    /**
     * @param string $nationalityId
     * @param string $scrapeLogId
     * @return bool
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function saveUsersJobsAndSkills(string $nationalityId, string $scrapeLogId) : bool
    {
        $mapper = new Mapper('users');
        $whereCause = 'WHERE nationality_id = ' . $nationalityId . ' AND scrape_log_id = ' . $scrapeLogId .
        ' AND imported_jobs IS NULL AND imported_skills IS NULL';
        $allUsers = $mapper->getCollection($whereCause);

        $count = count($allUsers);

        $url = 'https://www.freelancer.com/api/users/0.1/users/?qualification_details&profile_description&portfolio_details&preferred_details&jobs&reputation_extra';

        $userArrayByPlatformId = [];

        $links = [];

        $slicedArray = array_chunk($allUsers, 100);

        $j = 0;
        foreach ($slicedArray as $array) {
            $links[$j] = $url;
            /** @var User $user */
            foreach ($array as $user) {
                $links[$j] .= '&users[]=' . $user->getPlatformUserId();
                $userArrayByPlatformId[$user->getPlatformUserId()] = $user;
            }
            $j++;
        }

        $i = 1;

        foreach ($links as $link) {
            $userObjects = $this->callAPI('GET', $link)->result->users;

            foreach ($userObjects as $userObject) {
                /** @var User $userModel */
                $userModel = $userArrayByPlatformId[$userObject->id];

                if ($userObject->jobs) {
                    foreach ($userObject->jobs as $job) {
                        $skillName = $job->category->name;

                        $skillId = $this->skillHelper->getSkillId($skillName);

                        $newUserSkill = new User_Skill(
                            $userModel->getUserId(),
                            $skillId
                        );

                        $newUserSkill->save();
                    }
                }

            if ($userObject->qualifications) {
                foreach ($userObject->qualifications as $qualification) {
                    $newQualification = new Qualification(
                        $userModel->getUserId(),
                        $qualification->description
                    );

                    $newQualification->save();
                }
            }

            $userModel->changeImportedJobs();
            $userModel->changeImportedSkills();
            echo "\r\n" . 'Saved ' . $i . ' out of ' . $count;
            $i++;
            }
        }
    }

    public function saveUsersReviews(string $nationalityId, string $scrapeLogId)
    {
        $mapper = new Mapper('users');

        $whereCause = 'WHERE nationality_id = ' . $nationalityId . ' AND scrape_log_id = ' . $scrapeLogId .
            ' AND imported_reviews IS NULL';

        $allUsers = $mapper->getCollection($whereCause);

        $reviewsUrl = 'https://www.freelancer.com/api/projects/0.1/reviews/?ratings&qualification_details';

        $slicedArray = array_chunk($allUsers, 100);

        $usersByLink = [];

        $j = 0;
        foreach ($slicedArray as $array) {
            $links[$j] = $reviewsUrl;
            /** @var User $user */
            foreach ($array as $user) {
                $links[$j] .= '&to_users[]=' . $user->getPlatformUserId();
                $userArrayByPlatformId[$user->getPlatformUserId()] = $user;
                $usersByLink[$j][] = $user;
            }
            $j++;
        }

        $allLinkCount = count($links);

        foreach ($links as $counter => $link) {
            $i = 1;

            $reviews = $this->callAPI('GET', $link . '&limit=100');

            echo "\r\n" . 'Loading reviews: press ctrl + C to stop' . ' Link ' . $counter . '/' . $allLinkCount;

            if (empty($reviews)) {
                continue;
            }

            if (empty($reviews->result->reviews)) {
                /** @var User $user */
                foreach ($usersByLink[$counter] as $user) {
                    $user->changeImportedReviews();
                }
                continue;
            }

            $allReviews = $reviews->result->reviews;

            $offset = 1;
            $count = count($allReviews);

            if ($count === 100) {
                do {
                    $offset++;
                    $moreReviews = $this->callAPI('GET', $link . '&limit=100' . '&offset=' . $offset * 100);

                    if (empty($moreReviews)) {
                        break;
                    }

                    if (empty($moreReviews->result->reviews)) {
                        break;
                    }

                    $moreReviews = $moreReviews->result->reviews;

                    $allReviews = array_merge($allReviews, $moreReviews);

                    $count = count($allReviews);

                } while ($count >= $offset * 100);
            }

            foreach ($allReviews as $review) {
                /** @var User $userModel */
                $userModel = $userArrayByPlatformId[$review->to_user_id];

                $newReview = new User_Rating(
                    $userModel->getUserId(),
                    $review->rating,
                    $review->description
                );

                $newReview->save();

                $userJob = new User_Job(
                    $userModel->getUserId(),
                    $newReview->getUserRatingId(),
                    $review->paid_amount . '',
                    $review->currency->code === 'EUR' ? $this->genderHelper::EURO : $this->genderHelper::US,
                    date('Y-m-d', $review->time_submitted)
                );

                $userJob->save();

                unset($review);

                $userModel->changeImportedReviews();

                echo "\r\n" . 'Link number: ' . $counter . ' out of ' . $allLinkCount . ' links. Saved review ' . $i . ' out of ' . count($allReviews);
                $i++;
            }
        }
    }

    //todo Ovde bi mogao da ubacis da se pamti scrape log
    public function saveFreelancerLinks(string $nationality) : bool
    {
        $url = 'https://www.freelancer.com/api/users/0.1/users/directory/?countries[]=United%20States&limit=100';

        $users = $this->callAPI(self::GET, $url);

        $usersArray = $users->result->users;

        $userUrl = 'https://www.freelancer.com/api/users/0.1/users/?qualification_details&profile_description&portfolio_details&preferred_details&jobs&reputation_extra';

        foreach ($usersArray as $userObject) {
            $userUrl .= '&users[]=' . $userObject->id;
            unset($userObject);
        }

        $platformLink = new Freelancer_Link(
            $userUrl,
            '4'
        );

        $totalCount = $users->result->total_count;
        unset($users);
        $start = $totalCount;

        $platformLink->save();

        unset($userList);

        while ($start > 100) {
            $start -= 100;
            $value = $totalCount - $start;
            $users = $this->callAPI(self::GET, $url . '&offset=' . $value);

            $usersArray = $users->result->users;

            $userUrl = 'https://www.freelancer.com/api/users/0.1/users/?qualification_details&profile_description&portfolio_details&preferred_details&jobs&reputation_extra';

            foreach ($usersArray as $userObject) {
                $userUrl .= '&users[]=' . $userObject->id;
                unset($userObject);
            }
            unset($userList);

            $platformLink = new Freelancer_Link(
                $userUrl,
                '4'
            );

            $platformLink->save();

            unset($users);
            echo '';
        }

        echo 'Still HERE!' . "\r\n";

        $users = $this->callAPI(self::GET, $url . '&offset=' . $start);
        $usersArray = $users->result->users;
        $userUrl = 'https://www.freelancer.com/api/users/0.1/users/?qualification_details&profile_description&portfolio_details&preferred_details&jobs&reputation_extra';

        foreach ($usersArray as $userObject) {
            $userUrl .= '&users[]=' . $userObject->id;
            unset($userObject);
        }

        $platformLink = new Freelancer_Link(
            $userUrl,
            '4'
        );

        $platformLink->save();

        unset($userList);

        unset($users);

        unset($usersArray);

        echo 'Saved all links!';
        return true;
    }

    //todo mislim da je bolje imati jednu metodu nego 2, treba da se to prepravi
    public function runForSerbians() : bool
    {
        $usersArray = [];

        $url = 'https://www.freelancer.com/api/users/0.1/users/directory/?countries[]=Serbia&limit=100';

        $users = $this->callAPI(self::GET, $url);
        $usersArray[] = $users->result->users;
        $totalCount = $users->result->total_count;

        unset($users);

        $start = $totalCount;

        while ($start > 100) {
            $start -= 100;
            $value = $totalCount - $start;
            $users = $this->callAPI(self::GET, $url . '&offset=' . $value);
            $usersArray[] = $users->result->users;

            unset($users);
        }

        $users = $this->callAPI(self::GET, $url . '&offset=' . $start);

        $usersArray[] = $users->result->users;

        unset($users);

        foreach ($usersArray as $userList) {
            $userUrl = 'https://www.freelancer.com/api/users/0.1/users/?qualification_details&profile_description&portfolio_details&preferred_details&jobs&reputation_extra';

            foreach ($userList as $userObject) {
                $userUrl .= '&users[]=' . $userObject->id;

                unset($userObject);
            }
            unset($userList);

            //todo mislim da ovo vise nije potrebno
            $this->extractAndSaveUsers($this->callAPI(self::GET, $userUrl)->result->users);

            printf('It is still working! Do not worry!');
        }
        unset($usersArray);
        echo 'Serbians - DONE!';
        return true;
    }

    //todo sta cemo sa treadovima???
    public function extractAndSaveUsers($userArray, $nationality = null)
    {
        $thread = new FreelanceThread($userArray, $this->scrape_log, Nationalities::AMERICAN);

        return $thread;
    }

}
//todo mozda bi bilo najbolje da se napravi jedna skripta gde bi se odabralo sta da se pokrene koji scraper
$quickScript = new FreelanceScrape('4', '1');

//echo 'First jobs and skillArray' . "\r\n";
//
//$quickScript->saveUsersJobsAndSkills('2', '4');
//
//echo 'DONE!';

echo 'And now for reviews' . "\r\n";

$quickScript->saveUsersReviews('2', '4');
echo "\r\n" . 'DONE!' . "\r\n";