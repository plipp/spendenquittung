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
require_once(plugin_dir_path(__FILE__) . '../util/css-selector.inc');
require_once(plugin_dir_path(__FILE__) . '../platforms/amazon/AmazonProviderApi.php');

class PlatformRegistry
{
    const BOOKLOOKER = 'booklooker';
    const ZVAB = 'zvab';
    const EBAY = 'ebay';
    const BUCHFREUND = 'buchfreund';
    const AMAZON = 'amazon';

    private $_platforms;

    public function __construct($platforms)
    {
        foreach ($platforms as $platform) {
            $platform['protocol'] = 'http';

            switch ($platform['name']) {
                case self::ZVAB:
                    $this->_platforms[$platform['name']] = new ZVAB($platform);
                    break;
                case self::AMAZON:
                    $this->_platforms[$platform['name']] = new Amazon($platform);
                    break;
                case self::BOOKLOOKER:
                    $this->_platforms[$platform['name']] = new Booklooker($platform);
                    break;
                case self::EBAY:
                    $this->_platforms[$platform['name']] = new Ebay($platform);
                    break;
                case self::BUCHFREUND:
                    $this->_platforms[$platform['name']] = new Buchfreund($platform);
                    break;
                default:
                    $this->_platforms[$platform['name']] = new Platform($platform);
                    break;
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
        $url = null;

        $isbn = $this->clean($isbn);
        if ($isbn != null) {
            $url = str_replace("\${ISBN10}", Isbn::to10($isbn), $this->protocol . "://" . $this->host . $this->urlpath);
            $url = str_replace("\${ISBN13}", Isbn::to13($isbn), $url);
        }
        return $url;
    }

    protected function clean($isbn)
    {
        $isbn = Isbn::clean($isbn);
        if (!Isbn::validate($isbn)) {
            error_log("ISBN not valid:" . $isbn);
            return null;
        } else return $isbn;
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

    /**
     * extracts the lowest total prices (== price + porto) from the $content
     *
     * @param $content either the content of the HTML-response-page or from an REST-API
     * @return array th extract lowest total prices
     */
    public function totalPricesFrom($content)
    {
        return array();
    }

    public function titleFrom($content)
    {
        return null;
    }

    public function authorFrom($content)
    {
        throw new RuntimeException("$this->name : authorFrom()-method not supported");
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

class ZVAB extends Platform
{
    public function __construct($params)
    {
        parent::__construct($params);
    }

    public function totalPricesFrom($content)
    {
//        error_log("---CONTENT---" . $content ."---CONTENT---");
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

        if (empty($prices) || count($prices) != 2) {
            error_log("No prices found at:" . $this->name . "(parsing error?)");
            return array();
        }
        error_log("ZVAB prices:" . implode('-', $prices[1]));
        return array_map(array("Converters", "toFloat"), $prices[1]);
    }
}

class Buchfreund extends Platform
{
    public function __construct($params)
    {
        $params['protocol'] = 'https';
        parent::__construct($params);
    }

    public function totalPricesFrom($content)
    {
        $no_result = 'Partnerplattform www.buchhai.de';

        if (empty($content)) {
            error_log("No response content from:" . $this->name);
            return array();
        }
        if (strpos($content, $no_result)) {
            error_log("No result found at:" . $this->name);
            return array();
        }

        $elements = select_elements('div.resultPrice td.priceBorder', $content);

        if (empty($elements) || count($elements) < 2) {
            error_log("No prices found at:" . $this->name . "(parsing error?)");
            return array();
        }

        // filter out labels
        $totalPrices = array();
        foreach ($elements as $key => $value) if ($key & 1) $totalPrices[] = $value;

        $prices = array_map(array("Buchfreund", "toFloat"), $totalPrices);
        error_log("Buchfreund Preise:" . implode('-', $prices));
        return $prices;

    }

    private static function toFloat($element)
    {
        return Converters::toFloat(trim(str_replace('EUR', '', $element['text'])));
    }
}

class Booklooker extends Platform
{
    public function __construct($params)
    {
        parent::__construct($params);
    }

    public function totalPricesFrom($xml)
    {
        $booklist = $this->booklistFrom($xml);
        if (!$booklist) {
            return array();
        }

        $price = (float)$booklist->Book->Price + (float)$booklist->Book->ShippingPrice;

        return array($price);
    }

    public function titleFrom($xml)
    {
        $booklist = $this->booklistFrom($xml);
        if (!$booklist) return null;
        return $booklist->Book->Title;
    }

    public function authorFrom($xml)
    {
        $booklist = $this->booklistFrom($xml);
        if (!$booklist) return null;
        return $booklist->Book->Author;
    }

    private function booklistFrom($xml)
    {
        if (empty($xml)) {
            error_log("No response xml from: " . $this->name);
            return null;
        }

        $booklist = new SimpleXMLElement($xml);
        if ($booklist['RecordCount'] == '0') {
            error_log("Book not found at: " . $this->name);
            return null;
        } else return $booklist;
    }
}


class Ebay extends Platform
{
    public function __construct($params)
    {
        parent::__construct($params);
    }

    public function totalPricesFrom($xml)
    {
        $booklist = $this->booklistFrom($xml);
        if (!$booklist) return array();

        $price = (float)$booklist->item->shippingInfo->shippingServiceCost
            + (float)$booklist->item->sellingStatus->currentPrice;

        return array($price);
    }

    public function titleFrom($xml)
    {
        $booklist = $this->booklistFrom($xml);
        if (!$booklist) return null;

        return $booklist->item->title;
    }

    private function booklistFrom($xml)
    {
        if (empty($xml)) {
            error_log("No response xml from: " . $this->name);
            return null;
        }

        $findItemsByProductResponse = new SimpleXMLElement($xml);
        if ($findItemsByProductResponse->ack != 'Success') {
            error_log("Error searching at: " . $this->name . "(" . $xml . ")");
            return null;
        }

        if ($findItemsByProductResponse->searchResult['count'] == '0') {
            error_log("No book found at: " . $this->name);
            return null;
        }
        return $findItemsByProductResponse->searchResult;
    }
}

class Amazon extends Platform
{

    private $api;

    public function __construct($params)
    {
        parent::__construct($params);
        $this->api = new AmazonProviderApi($this->host, $this->urlpath);
    }

    public function urlBy($isbn)
    {
        return $this->api->urlBy($this->clean($isbn));
    }

    public function totalPricesFrom($xml)
    {
        $item = $this->itemFrom($xml);
        if (empty($item) || empty($item->OfferSummary)) {
            return array();
        }

        if (!empty($item->OfferSummary->LowestUsedPrice)) {
            $price = $item->OfferSummary->LowestUsedPrice->Amount;
        } else if (!empty($item->OfferSummary->LowestNewPrice)) {
            $price = $item->OfferSummary->LowestNewPrice->Amount;
        } else {
            return array();
        }

        return array((float)$price / 100 + $this->portoDeclBy(1));
    }

    public function titleFrom($xml)
    {
        $item = $this->itemFrom($xml);
        if (empty($item)) return null;
        return (string)$item->ItemAttributes->Title;
    }

    public function authorFrom($xml)
    {
        $item = $this->itemFrom($xml);
        if (empty($item)) return null;
        return (string)$item->ItemAttributes->Author;
    }

    private function itemFrom($xml)
    {
        if (empty($xml)) {
            error_log("No response xml from: " . $this->name);
            return null;
        }

        $itemLookupResponse = new SimpleXMLElement($xml);
        $items = $itemLookupResponse->Items;
        if (!empty($items->Request->Errors) || empty($items->Item)) {
            $msg = empty($items->Request->Errors) ? "" : "(" . $items->Request->Errors->Error->Message . ")";
            error_log("Book not found at: " . $this->name . $msg);
            return null;
        }

        return $items->Item;
    }
}