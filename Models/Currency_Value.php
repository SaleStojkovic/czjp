<?php

namespace CZJPScraping\Models;

include_once __DIR__ . '/AbstractModel.php';

class Currency_Value extends AbstractModel
{
    /** @var string */
    public $currency_value_id;

    /** @var string */
    public $value_to_dollar;

    /** @var string */
    public $currency_id;

    /** @var string */
    public $reference_date;

    /** @var string */
    public $scrape_log_id;

    /**
     * Currency_Value constructor.
     * @param string $value_to_dollar
     * @param string $currency_id
     * @param string $reference_date
     * @param string $scrape_log_id
     */
    public function __construct(string $value_to_dollar, string $currency_id, string $reference_date, string $scrape_log_id)
    {
        $this->value_to_dollar = $value_to_dollar;
        $this->currency_id = $currency_id;
        $this->reference_date = $reference_date;
        $this->scrape_log_id = $scrape_log_id;
    }

    /**
     * @return string
     */
    public function getCurrencyValueId(): string
    {
        return $this->currency_value_id;
    }

    /**
     * @return string
     */
    public function getValueToDollar(): string
    {
        return $this->value_to_dollar;
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
    public function getReferenceDate(): string
    {
        return $this->reference_date;
    }

    /**
     * @return string
     */
    public function getScrapeLogId(): string
    {
        return $this->scrape_log_id;
    }
}