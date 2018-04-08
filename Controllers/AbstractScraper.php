<?php

namespace CZJPScraping\Controllers;

use GuzzleHttp\Client;
use CZJPScraping\Models\GenderHelper;
use CZJPScraping\Models\NameHelper;
use CZJPScraping\Models\SkillHelper;

include_once __DIR__ . '/AbstractScraper.php';
include_once __DIR__ . '/../Models/AbstractModel.php';

require_once __DIR__  . '/../vendor/autoload.php';

/**
 * Class AbstractScraper
 * @package CZJPScraping\Controllers
 */
abstract class AbstractScraper
{
    private $client;

    public $scrape_log;

    /** @var  GenderHelper */
    public $genderHelper;

    /** @var  NameHelper */
    public $nameHelper;

    /** @var  SkillHelper */
    public $skillHelper;

    /**
     * AbstractScraper constructor.
     * @param $scrape_log
     * @param $platform_id
     * @param array $clientConfig
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function __construct($scrape_log, $platform_id, array $clientConfig = [])
    {
        $this->scrape_log = $scrape_log;
        $this->client = new Client();
        $this->genderHelper = new GenderHelper();
        $this->nameHelper = new NameHelper();
        $this->skillHelper = new SkillHelper($platform_id);
    }

    /**
     * @param $url
     * @return string
     * @throws \Throwable
     */
    protected function getHttpPage($url)
    {
        try {
            $body = $this->client->request('GET', $url)->getBody();
            return $body->getContents();
        } catch (\Exception $ex) {
            throw \GuzzleHttp\Promise\exception_for($ex->getMessage());
        }
    }

    /**
     * @param string $tag
     * @param string $regex
     * @param string $page
     * @return bool|array
     */
    protected function matchTagContent($tag, $regex, $page)
    {
        $pattern = '@<' . $tag . '[^>]*' . $regex . '[^>]*>((?:(?:(?!<' . $tag . '[^>]*>|</' . $tag . '>).)++|<'
            . $tag . '[^>]*>(?1)</' . $tag . '>)*)</' . $tag . '>@si';
        preg_match_all($pattern, $page, $matches);
        if (is_array($matches) && isset($matches[0]) && count($matches[0]) > 0) {
            return $matches;
        }
        return false;
    }

    public function recordScraping()
    {

    }

    public function extractHrefFromATag(string $aTag) : string
    {
        preg_match_all('/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $aTag, $result);
        return $result['href'][0];
    }
}