<?php

require_once(plugin_dir_path(__FILE__) . '../util/isbn.php');
require_once(plugin_dir_path(__FILE__) . '../util/parallelcurl.php');

class Profits {
    private $_values;

    public function __construct($platform, $content)
    {
        $prices = $platform->totalPricesFrom($content);

        if (!empty($prices)) {
            $profit = min($prices);

            $this->_values['profit'] = $profit;
            $this->_values['profitsByWeightClasses'] = empty($prices) ? array() : $platform->profitByWeightClasses($profit);
        } else {
            $this->_values['profit'] = 0;
        }
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->_values)) {
            return $this->_values[$name];
        }
        return null;
    }

    public function as_json() {
        return json_encode($this->_values);
    }
}

class ValueFromPlatformsAction
{
    private $_platformRegistry;
    private $_results;

    public function ValueFromPlatformsAction($platformRegistry)
    {
        $this->_platformRegistry = $platformRegistry;
        add_action('wp_ajax_request_value_from_platforms', array($this, 'request_value_from_platforms'));
        add_action('wp_ajax_nopriv_request_value_from_platforms', array($this, 'request_value_from_platforms'));
    }

    public function request_value_from_platforms()
    {
        $isbn = $_POST['ISBN'];

        header("Content-Type: application/json");
        echo $this->_parallel_requests ($isbn);

        exit;
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
        $this->_results[$platform_name] = $profits->as_json();
    }


    private function _parallel_requests($isbn)
    {
        $this->_results = array();

        $platforms = $this->_platformRegistry->all();
        $max_requests = count($platforms);

        $curl_options = array(
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_USERAGENT, 'Parallel Curl test script',
        );

        $parallel_curl = new ParallelCurl($max_requests, $curl_options);

        foreach ($platforms as $platform) {
            if ($platform->is_active == 1) {
                $search_url = $platform->urlBy($isbn);
                error_log($search_url);
                $parallel_curl->startRequest($search_url, array($this,'on_request_done'), $platform->name);
            }
        }

        // synchronize requests
        $parallel_curl->finishAllRequests();
        return json_encode($this->_results);
    }
}