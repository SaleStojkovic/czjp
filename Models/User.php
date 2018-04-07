<?php

namespace CZJPScraping\Models;

include_once __DIR__ . '/AbstractModel.php';

/**
 * Class User
 * @package CZJPScraping\Models
 */
class User extends AbstractModel
{
    /** @var  string */
    public $user_id;

    /** @var  string */
    public $username;

    /** @var  string */
    public $gender_id;

    /** @var  string */
    public $biography;

    /** @var  string */
    public $bid;

    /** @var  string */
    public $scrape_log_id;

    /** @var  string */
    public $currency_id;

    /** @var string */
    public $platform_user_id;

    /** @var  string */
    public $nationality_id;

    /** @var string */
    public $url_page;

    /** @var array */
    private $skills;

    /** @var array */
    private $reviews;

    /** @var array */
    private $scores;

    /** @var int */
    private $totalMoneyEarned;

    /**
     * User constructor.
     * @param string $username
     * @param string $gender_id
     * @param string $biography
     * @param string $bid
     * @param string $currency_id
     * @param string $scrape_log_id
     * @param string|null $platform_user_id
     * @param string|null $nationality_id
     * @param string|null $url_page
     */
    public function __construct(
        string $username,
        string $gender_id,
        string $biography,
        string $bid = null,
        string $currency_id,
        string $scrape_log_id,
        string $platform_user_id = null,
        string $nationality_id = null,
        string $url_page = null
    )
    {
        $this->username = $username;
        $this->gender_id = $gender_id;
        $this->biography = $biography;
        $this->bid = $bid;
        $this->currency_id = $currency_id;
        $this->scrape_log_id = $scrape_log_id;
        $this->nationality_id = $nationality_id;
        $this->platform_user_id = $platform_user_id;
        if (!$nationality_id) {
            $this->nationality_id = Nationalities::SERBIAN;
        }
        $this->url_page = $url_page;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->user_id;
    }

    /**
     * @return string
     */
    public function getBid(): string
    {
        return $this->bid;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getGenderId(): string
    {
        return $this->gender_id;
    }

    /**
     * @param string $gender_id
     */
    public function setGenderId(string $gender_id)
    {
        $this->gender_id = $gender_id;
    }

    /**
     * @return string
     */
    public function getBiography(): string
    {
        return $this->biography;
    }

    /**
     * @param string $biography
     */
    public function setBiography(string $biography)
    {
        $this->biography = $biography;
    }

    /**
     * @return string
     */
    public function getScrapeLogId(): string
    {
        return $this->scrape_log_id;
    }

    /**
     * @return string
     */
    public function getCurrencyId(): string
    {
        return $this->currency_id;
    }

    /**
     * @return string
     */
    public function getNationalityId(): string
    {
        return $this->nationality_id;
    }

    /**
     * @return string
     */
    public function getUrlPage(): string
    {
        return $this->url_page;
    }

    /**
     * @return string
     */
    public function getPlatformUserId(): string
    {
        return $this->platform_user_id;
    }

    public function changeImportedJobs() : bool
    {
        return $this->updateColumn('imported_jobs', 1);
    }

    public function changeImportedSkills() : bool
    {
        return $this->updateColumn('imported_skills', 1);
    }

    public function changeImportedReviews() : bool
    {
        return $this->updateColumn('imported_reviews', 1);
    }

    /**
     * @return string
     */
    public function getSkillString() : string
    {
        $skillString = "";

        if (!$this->skills) {
            return $skillString;
        }

        return '"' . implode(', ', $this->skills) . '"';
    }

    /**
     * @return string
     */
    public function getReviewString() : string
    {
        $reviewString = "";

        if (!$this->reviews) {
            return $reviewString;
        }

        return '"' . implode(', ', $this->reviews) . '"';
    }

    public function getAverageScore()
    {
        $avgScore = 0;
        $count = count($this->scores);

        if (!$count) {
            return $avgScore;
        }

        foreach ($this->scores as $score) {
            $avgScore += (int) $score;
        }

        $avgScore /= $count;

        return round($avgScore, 2);
    }

    /**
     * @return int
     */
    public function getMoneyEarned() : int
    {
        return $this->totalMoneyEarned;
    }

    /**
     * @return array
     */
    public function getScores(): array
    {
        return $this->scores;
    }

    /**
     * @param array $scores
     */
    public function setScores(array $scores)
    {
        $this->scores = $scores;
    }

    /**
     * @return int
     */
    public function getTotalMoneyEarned(): int
    {
        return $this->totalMoneyEarned;
    }

    /**
     * @param int $totalMoneyEarned
     */
    public function setTotalMoneyEarned(int $totalMoneyEarned)
    {
        $this->totalMoneyEarned = $totalMoneyEarned;
    }

    /**
     * @return array
     */
    public function getSkills(): array
    {
        return $this->skills;
    }

    /**
     * @param array $skills
     */
    public function setSkills(array $skills)
    {
        $this->skills = $skills;
    }

    /**
     * @return array
     */
    public function getReviews(): array
    {
        return $this->reviews;
    }

    /**
     * @param array $reviews
     */
    public function setReviews(array $reviews)
    {
        $this->reviews = $reviews;
    }
}