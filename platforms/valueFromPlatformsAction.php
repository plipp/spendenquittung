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
    const TITLE_RESULT = 'title';
    const PROFIT_RESULT = 'profits';

    private $_platformRegistry;
    private $_results;
    private $_blacklistedBookIsbns;

    public function ValueFromPlatformsAction($platformRegistry, $blacklistedBooks, $doRegister=true)
    {
        $this->_platformRegistry = $platformRegistry;
        $this->_blacklistedBookIsbns = array_map(function ($book) {return $book['isbn'];},$blacklistedBooks);
        // error_log("ISBN of blacklisted books: ".implode('+',$this->_blacklistedBookIsbns));

        if ($doRegister) {
            add_action('wp_ajax_request_value_from_platforms', array($this, 'request_value_from_platforms'));
            add_action('wp_ajax_nopriv_request_value_from_platforms', array($this, 'request_value_from_platforms'));
        }
    }

    public function request_value_from_platforms()
    {
        $isbn = Isbn::clean($_POST['ISBN']);
        if (!Isbn::validate($isbn)) {
            wp_send_json_error("Invalid ISBN $isbn");
            exit;
        }

        $isbn13 = Isbn::to13($isbn);
        if (in_array($isbn13, $this->_blacklistedBookIsbns)) {
            $response = json_encode(array(
                    "isbn" => $isbn,
                    "title" => '?',
                    "profit" => "0.00")
            );
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
            if ($platform->is_active && $platform->percent_of_sales>0) {
                $search_url = $platform->urlBy($isbn);
                error_log($search_url);
                $parallel_curl->startRequest($search_url, array($this, 'on_request_done'), $platform->name);
            }
        }

        // synchronize requests
        $parallel_curl->finishAllRequests();

        $title = $this->bestTitleFrom($this->_results[self::TITLE_RESULT]);
        $profit = $this->averageFrom($this->_results[self::PROFIT_RESULT]);

        $encoded_value = ($title == null || !Profits::is_valid($profit)) ? null :
            json_encode(array(
                    "isbn" => $isbn,
                    "title" => strval($title),
                    "profit" => Converters::toCurrencyString($profit))
            );

        // error_log("Booksearch Result:" . $encoded_value);
        return $encoded_value;
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
            error_log("averageFrom: profit (" . $platform . ") = " . $profit);
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
}