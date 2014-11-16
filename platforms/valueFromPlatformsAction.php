<?php

require_once(plugin_dir_path(__FILE__) . '../util/isbn.php');
require_once(plugin_dir_path(__FILE__) . '../util/parallelcurl.php');

class Profits
{
    private $_values;

    public function __construct($platform, $content)
    {
        $prices = $platform->totalPricesFrom($content);

        if (!empty($prices)) {
            $profit = min($prices);
            error_log("Profits: " . $platform->name . ": " . implode('-',$prices) . ", profit=" . $profit);

            $this->_values['profit'] = $profit;
            $this->_values['profitsByWeightClasses'] = empty($prices) ? array() : $platform->profitByWeightClasses($profit);
        } else {
            $this->_values['profit'] = -1;
        }
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->_values)) {
            return $this->_values[$name];
        }
        return null;
    }

    public function as_json()
    {
        return json_encode($this->_values);
    }

    public static function is_valid($profit) {
        return $profit>=0;
    }
}

class ValueFromPlatformsAction
{
    const INTERNAL_REQUEST_HEADER = 'x-internal-request';

    const STATUS_OK = 'OK';
    const STATUS_BLACKLISTED = 'BL';

    const TITLE_RESULT = 'title';
    const PROFIT_RESULT = 'profits';
    const PROFITS_BY_WEIGHT_RESULT = 'profitsByWeightClasses';

    private $_platformRegistry;
    private $_results;
    private $_blacklistedBooksByIsbn;

    private $_isInternalRequest;

    public function ValueFromPlatformsAction($platformRegistry, $blacklistedBooks, $doRegister=true)
    {
        $this->_platformRegistry = $platformRegistry;

        $blacklistedBookIsbns = array_map(function ($book) {return $book['isbn'];},$blacklistedBooks);
        $this->_blacklistedBooksByIsbn = array_combine($blacklistedBookIsbns, $blacklistedBooks);
        // error_log("ISBN of blacklisted books: ".implode('+',$this->_blacklistedBooksByIsbn));

        if ($doRegister) {
            add_action('wp_ajax_request_value_from_platforms', array($this, 'request_value_from_platforms'));
            add_action('wp_ajax_nopriv_request_value_from_platforms', array($this, 'request_value_from_platforms'));
        }
    }

    public function request_value_from_platforms()
    {
        $this->_isInternalRequest = self::check_if_internal_request($_SERVER);

        $isbn = Isbn::clean($_POST['ISBN']);
        if (!Isbn::validate($isbn)) {
            wp_send_json_error("Invalid ISBN $isbn");
            exit;
        }

        $isbn13 = Isbn::to13($isbn);
        if (array_key_exists($isbn13, $this->_blacklistedBooksByIsbn)) {
            $response = $this->json_encoded_response($isbn, self::STATUS_BLACKLISTED, $this->_blacklistedBooksByIsbn[$isbn13]['title']);
        } else {
            $response = $this->_parallel_requests($isbn);
        }

        if ($response) {
            wp_send_json_success($response);
        } else {
            wp_send_json_error("book data couldn't be retrieved: Check your internet connection!");
        }
        exit; // !!! REQUIRED !!!
    }

    public function on_request_done($content, $url, $ch, $platform_name)
    {
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpcode !== 200) {
            error_log("Fetch error $httpcode for '$url'");
            return;
        }

        $platform = $this->_platformRegistry->by($platform_name);
        $profits = new Profits($platform, $content);

        $this->_results[self::PROFIT_RESULT][$platform_name] = $profits->profit;
        $this->_results[self::PROFITS_BY_WEIGHT_RESULT][$platform_name] = $profits->profitsByWeightClasses;
        $this->_results[self::TITLE_RESULT][$platform_name] = $platform->titleFrom($content);
    }


    private function _parallel_requests($isbn)
    {
        $this->_results = array(
            self::TITLE_RESULT => array(),
            self::PROFIT_RESULT => array()
        );

        $platforms = $this->_platformRegistry->all();
        $max_requests = count($platforms);

        $curl_options = array(
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_USERAGENT, 'Parallel Curl test script',
        );

        $parallel_curl = new ParallelCurl($max_requests, $curl_options);

        foreach ($platforms as $platform) {
            if ($platform->is_active && ($platform->percent_of_sales>0 || $this->_isInternalRequest)) {
                $search_url = $platform->urlBy($isbn);
                error_log("SEARCH-URL:" . $search_url);
                $parallel_curl->startRequest($search_url, array($this, 'on_request_done'), $platform->name);
            }
        }

        // synchronize requests
        $parallel_curl->finishAllRequests();

        $title = $this->bestTitleFrom($this->_results[self::TITLE_RESULT]);
        $profit = $this->averageFrom($this->_results[self::PROFIT_RESULT]);

        $encoded_value = null;
        if ($title != null && Profits::is_valid($profit)) {
            $profits = $this->_isInternalRequest ? $this->_results[self::PROFIT_RESULT]:array();
            $profitsByWeightClasses = $this->_isInternalRequest ? $this->_results[self::PROFITS_BY_WEIGHT_RESULT]:array();
            $encoded_value = $this->json_encoded_response($isbn, self::STATUS_OK, strval($title),
                Converters::toCurrencyString($profit), $profits, $profitsByWeightClasses);
        }
        error_log("Booksearch Result:" . $encoded_value);
        return $encoded_value;
    }

    private function json_encoded_response($isbn, $status=self::STATUS_OK, $title='?', $profit="0.00", $profits=array(), $profitsByWeightClasses=array())
    {
        return json_encode(array(
            "isbn" => $isbn,
            "status" => $status,
            "title" => $title,
            "profit" => $profit,
            "profits" => $profits,
            "profitsByWeightClasses" => $profitsByWeightClasses));
    }

    static function bestTitleFrom($titleResults)
    {
        // error_log("Title-Results:" . implode('-', $titleResults));
        if (isset($titleResults[PlatformRegistry::BOOKLOOKER])) return $titleResults[PlatformRegistry::BOOKLOOKER];
        foreach ($titleResults as $title) {
            if ($title) return $title;
        }
        return null;
    }

    function averageFrom($profitByPlatform)
    {
        $platformsWithValidProfit = $this->platformsWithValidProfit($profitByPlatform);
        $sumOfAllRelevantPercentages = $this->sumOfAllRelevantPercentages($platformsWithValidProfit);

        if ($sumOfAllRelevantPercentages==0 || count($platformsWithValidProfit) == 0) {
            return -1;
        }

        $average = 0.0;
        foreach ($platformsWithValidProfit as $platform => $profit) {
//            error_log("averageFrom: profit (" . $platform . ") = " . $profit);
            $platform = $this->_platformRegistry->by($platform);
            $average += $profit * ($platform->percent_of_sales / $sumOfAllRelevantPercentages);
        }
        return $average;
    }

    private function platformsWithValidProfit($profitByPlatform)
    {
        $platformsWithValidProfit = array_filter($profitByPlatform, function ($profit) {
            return Profits::is_valid($profit);
        });
        error_log("remaining platforms:" . implode('-', array_keys($platformsWithValidProfit)));
        return $platformsWithValidProfit;
    }

    private function sumOfAllRelevantPercentages($validProfitsByPlatform)
    {
        $complete_percentage = 0;
        $registry = $this->_platformRegistry;
        foreach (array_keys($validProfitsByPlatform) as $platform) {
            $complete_percentage += $registry->by($platform)->percent_of_sales;
        }
        return $complete_percentage;
    }

    private static function check_if_internal_request($http_headers)
    {
        $header_key='HTTP_' . str_replace('-','_', strtoupper(self::INTERNAL_REQUEST_HEADER));
//        error_log("HEADER_KEY=" . $header_key . ", server-headers:" . implode('-', $http_headers));
        return array_key_exists($header_key, $http_headers);
    }
}