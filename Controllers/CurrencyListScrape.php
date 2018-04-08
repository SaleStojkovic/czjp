<?php

namespace CZJPScraping\Controllers;

use CZJPScraping\Models\Currency_Value;
use CZJPScraping\Models\GenderHelper;
use CZJPScraping\Models\Mapper;
use CZJPScraping\Models\Scrape_Log;

include_once __DIR__ . '/AbstractScraper.php';

class CurrencyListScrape extends AbstractScraper
{
    const URL = 'https://www.x-rates.com/historical/?from=USD&amount=1&date=';

    /** @var Scrape_Log */
    public $scrapeLog;

    /**
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function saveCurrencyValues()
    {
        $mapper = new Mapper('scrape_logs');
        $this->scrapeLog = $mapper->getCollection('WHERE scrape_log_id = ' . $this->scrape_log)[0];
        $referenceDate = $this->scrapeLog->getDate();

        $valuesArray = $this->getValuesArray($referenceDate);

        foreach ($valuesArray as $currencyId => $valueToDollar) {
            $currencyValue = new Currency_Value(
                $valueToDollar,
                $currencyId,
                $referenceDate,
                $this->scrapeLog->getScrapeLogId()
            );

            $currencyValue->save();
        }

        echo 'Done!';
    }

    /**
     * @param string $referenceDate
     * @return array
     * @throws \Throwable
     */
    public function getValuesArray(string $referenceDate) : array
    {
        $content = $this->getHttpPage(self::URL . $referenceDate);
        $allRates = $this->matchTagContent('tBody', '', $content)[1][0];
        $ratesArray = $this->matchTagContent('td', "class='rtRates'", $allRates)[1];
        $euroValue = $this->matchTagContent('a', '', $ratesArray[0])[1][0];
        $poundValue = $this->matchTagContent('a', '', $ratesArray[2])[1][0];

        $valuesArray[GenderHelper::EURO] = $euroValue;
        $valuesArray[GenderHelper::POUND] = $poundValue;

        return $valuesArray;
    }
}

try {
    $scraper = new CurrencyListScrape('3', '1');
    $scraper->saveCurrencyValues();
} catch (\Exception $exception) {
    throw new $exception;
}
