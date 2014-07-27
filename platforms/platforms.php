<?php
/**
 * Created by IntelliJ IDEA.
 * User: patricia
 * Date: 12.07.14
 * Time: 21:20
 */

require_once("costs.php");
require_once(plugin_dir_path(__FILE__) . '../util/isbn.php');
require_once(plugin_dir_path(__FILE__) . '../util/converters.php');

class PlatformRegistry
{
    private $_platforms;

    public function __construct($platforms)
    {
        foreach ($platforms as $platform) {
            switch ($platform['name']) {
                case 'zvab': $this->_platforms[$platform['name']] = new ZVAB($platform); break;
                case 'booklooker': $this->_platforms[$platform['name']] = new Booklooker($platform); break;
                case 'ebay': $this->_platforms[$platform['name']] = new Ebay($platform); break;
                default: $this->_platforms[$platform['name']] = new Platform($platform); break;
            }
        }
    }

    public function by($name)
    {
        return $this->_platforms[$name];
    }

    public function all()
    {
        return array_values($this->_platforms);
    }
}

class Platform
{
    private $params;

    public function __construct($params)
    {
        $this->params = $params;
    }


    public function __get($name)
    {
        if (array_key_exists($name, $this->params)) {
            return $this->params[$name];
        }
        return null;
    }

    public function urlBy($isbn)
    {
        $isbn = Isbn::clean($isbn);
        if (!Isbn::validate($isbn)) {
            error_log("ISBN not valid:" . $isbn);
            return null;
        }

        $url = str_replace("\${ISBN10}", Isbn::to10($isbn), "http://" . $this->host . $this->urlpath);
        $url = str_replace("\${ISBN13}", Isbn::to13($isbn), $url);
        return $url;
    }

    public function portoDeclBy($weightClass)
    {
        switch ($weightClass) {
            case Weight::WEIGHT_CLASS_450:
                return $this->porto_wcl1;
            case Weight::WEIGHT_CLASS_950:
                return $this->porto_wcl2;
            case Weight::WEIGHT_CLASS_MAX:
                return $this->porto_wcl3;
        }
        trigger_error("portoDeclBy, unknown weightclass:" . $weightClass);
        return 0;
    }

    public function totalPricesFrom($content) {
        return array();
    }


    public function profitByWeightClasses($price)
    {
        $weightClasses = Weight::classes();
        foreach ($weightClasses as $weightClass) {
            $profit[$weightClass] = ($price - $this->portoDeclBy($weightClass)) * (1 - MWST) #Vom ausgewiesenen Preis: MwSt abziehen
                * (1 - $this->provision) #Provision abziehen
                - $this->fixcosts #Fixkosten abziehen
                - (Porto::by($weightClass) + ADDITIONAL_COSTS) #Tatsächlich anfallendes Porto und Zusatzkosten abziehen
                + $this->portoDeclBy($weightClass); #Das ausgewiesene Porto wieder addieren
        }
        return $profit;
    }

    public function __toString()
    {
        return json_encode($this->params);
    }
}

class ZVAB extends Platform {
    public function __construct($params) {
        parent::__construct($params);
    }

    public function totalPricesFrom($content) {
        $no_result = 'Es konnten momentan leider';
        $price_regexp = '|<span class="total">Gesamt:&nbsp;EUR&nbsp;(\d+,\d+)</span>|';

        if (empty($content)) {
            error_log("No response content from:" . $this->name);
            return array();
        }
        if (strpos($content, $no_result)) {
            error_log("No result found at:" . $this->name);
            return array();
        }

        preg_match_all($price_regexp, $content, $prices);

        if (empty($prices) || count($prices)!=2){
            error_log("No prices found at:" . $this->name . "(parsing error?)");
            return array();
        }
        return array_map(array("Converters","toFloat"), $prices[1]);
    }
}

class Booklooker extends Platform {
    public function __construct($params) {
        parent::__construct($params);
    }

    public function totalPricesFrom($xml) {
        if (empty($xml)) {
            error_log("No response xml from: " . $this->name);
            return array();
        }

        $booklist = new SimpleXMLElement($xml);
        if ($booklist['RecordCount']=='0') {
            error_log("Book not found at: " . $this->name);
            return array();
        }

        $price = (float)$booklist->Book->Price + (float)$booklist->Book->ShippingPrice;

        return array($price);
    }
}


class Ebay extends Platform {
    public function __construct($params) {
        parent::__construct($params);
    }

    public function totalPricesFrom($xml) {
        if (empty($xml)) {
            error_log("No response xml from: " . $this->name);
            return array();
        }

        $findItemsByProductResponse = new SimpleXMLElement($xml);
        if ($findItemsByProductResponse->ack != 'Success') {
            error_log("Error searching at: " . $this->name . "(" . $xml . ")");
            return array();
        }

        if ($findItemsByProductResponse->searchResult['count'] == '0') {
            error_log("No book found at: " . $this->name);
            return array();
        }

        $price = (float)$findItemsByProductResponse->searchResult->item->shippingInfo->shippingServiceCost
            + (float)$findItemsByProductResponse->searchResult->item->sellingStatus->currentPrice;

        return array($price);
    }
}