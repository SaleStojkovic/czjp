<?php
namespace CZJPScraping\CSVParsers;

include_once __DIR__ . '/../Controllers/AbstractScraper.php';
include_once __DIR__ . '/../Models/AbstractModel.php';

use CZJPScraping\Models\Currency_Value;
use CZJPScraping\Models\Mapper;
use CZJPScraping\Models\Scrape_Log;
use CZJPScraping\Models\SkillHelper;
use CZJPScraping\Models\User;
use CZJPScraping\Models\User_Skill;
use CZJPScraping\Models\Gender;
use CZJPScraping\Models\GenderHelper;

/**
 * Class CSVParserAbstract
 * @package CZJPScraping
 */
abstract class AbstractParser
{
    /** @var Scrape_Log */
    public $scrapeLog;

    /** @var string  */
    public $fileName;

    /** @var array|User[] */
    public $users;

    /** @var SkillHelper */
    public $skillHelper;

    /** @var array */
    public $currencyValues;

    /**
     * AbstractParser constructor.
     * @param int $scrapeLogId
     * @param string $fileName
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function __construct(int $scrapeLogId, string $fileName)
    {
        try {
            $mapper = new Mapper('scrape_logs');
            $whereCause = 'WHERE scrape_log_id = ' . $scrapeLogId;

            $this->scrapeLog = $mapper->getCollection($whereCause)[0];
            $this->fileName = $this->getDesktopPath() . $fileName . '.csv';
            $this->skillHelper = new SkillHelper($this->scrapeLog->getPlatformId());
            $this->users = $this->getUsers();
            $this->loadAllSkills();
            $this->loadAllReviews();
            $this->loadMoneyEarned();
            $this->loadCurrencyValues();

        } catch (\ReflectionException $exception) {
            throw new \ReflectionException($exception);
        }
    }

    public function makeCsvRows() : array
    {
        $users = $this->users;
        $rows = [];

        /** @var User $user */
        foreach ($users as $user) {
            $row = [
                'Username' => $user->getUsername(),
                'Gender' => Gender::getGenderName($user->getGenderId()),
                'Biography' => $user->getBiography(),
                'Bid' => $user->getBid(),
                'Currency' => GenderHelper::getMonetaryString($user->getCurrencyId()),
                'Skills' => $user->getSkillString(),
                'Reviews' => $user->getReviewString(),
                'Avg. Score' => $user->getAverageScore(),
                'Money Earned' => $user->getMoneyEarned()
            ];

            $rows[] = $row;
        }

        return $rows;
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
     * @throws \Exception
     * @throws \ReflectionException
     */
    private function getUsers()
    {
        $mapper = new Mapper('users');
        $whereCause = 'WHERE scrape_log_id = ' . $this->scrapeLog->getScrapeLogId();

        return $mapper->getCollection($whereCause);
    }

    public function makeCsv()
    {
        $rows = $this->makeCsvRows();

        $file = fopen($this->fileName,'w');

        fputcsv($file, array_keys($rows[0]));

        foreach ($rows as $row) {
            fputcsv($file, $row);
        }
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function loadAllSkills()
    {
        $usersIds = '';
        $skillsByUser = [];

        /** @var User $user */
        foreach ($this->users as $user) {
            $usersIds .= $user->getUserId() .  ', ';
            $skillsByUser[$user->getUserId()] = [];
        }

        $usersIds = substr($usersIds, 0, -2);
        $whereCause = 'WHERE user_id IN (' . $usersIds . ')';
        $mapper = new Mapper('user_skills');
        $userSkills = $mapper->getCollection($whereCause);

        /** @var User_Skill $userSkill */
        foreach ($userSkills as $userSkill) {
            $skillName = $this->skillHelper->getSkillName($userSkill->getSkillId());
            if (in_array($skillName, $skillsByUser[$userSkill->getUserId()], true)) {
                continue;
            }

            $skillsByUser[$userSkill->getUserId()][] = $skillName;
        }

        foreach ($this->users as $user) {
            $user->setSkills($skillsByUser[$user->getUserId()]);
        }

        echo 'Loaded all skillArray' . "\r\n";
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     */
    private function loadCurrencyValues()
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

    public abstract function loadAllReviews();

    public abstract function loadMoneyEarned();
}
