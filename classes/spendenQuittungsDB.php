<?php

/**
 * The Database with the parameters of the single platforms(amazon, ebay,...)
 *
 * @author patricia
 */

class SpendenQuittungsDB
{

    const SQDB_DB_VERSION_OPTION = "sqdb_db_version";

    const SQ_BOOKLOOKER_API_KEY_OPTION = 'sq_booklooker_api_key';

    const SQ_EBAY_API_KEY_OPTION = 'sq_ebay_api_key';

    private $_columnDescription = array(
        'id' => 'Nr.',
        'name' => 'Plattformname',
        'host' => 'Plattformhost',
        'urlpath' => 'Plattform-URL-Pfad',
        'fixcosts' => 'VerkaufspreisUNabhaengige Kosten (Einstellgebuehren, Betrag der vom Porto einbehalten wird etc.) in Euro.',
        'provision' => 'Anteil des Verkaufspreises, den der Vermittler bekommt.',
        'porto_wcl1' => 'Ausgewiesenes Porto/Verpackung Gewichtsklasse 1',
        'porto_wcl2' => 'Ausgewiesenes Porto/Verpackung Gewichtsklasse 2',
        'porto_wcl3' => 'Ausgewiesenes Porto/Verpackung Gewichtsklasse 3',
        'percent_of_sales' => 'Verkaufsanteil',
        'is_active' => 'Die Platform soll/soll nicht abgefragt werden'
    );

    public function install()
    {
        global $wpdb;
        $tableName = $wpdb->prefix . "sqdb";

        $sqdbVersion = 1;
        $installedSqdbVersion = get_option(self::SQDB_DB_VERSION_OPTION, 0);

        $sql = "CREATE TABLE IF NOT EXISTS " . $tableName . " (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `name` varchar(255) NOT NULL,
                  `host` varchar(255) NOT NULL,
                  `urlpath` text NOT NULL,
                  `fixcosts` float(10,2),
                  `provision` float(10,2),
                  `porto_wcl1` float(10,2),
                  `porto_wcl2` float(10,2),
                  `porto_wcl3` float(10,2),
                  `percent_of_sales` float(10,2),
                  `is_active` boolean,
                  PRIMARY KEY  (`id`),
                  KEY `name` (`name`)
                );";

        $this->init_options();

        if ($installedSqdbVersion != $sqdbVersion) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql); # FIXME automatic upgrade does not work
            update_option(self::SQDB_DB_VERSION_OPTION, $sqdbVersion);

            // TODO cleanup
//            $count = $wpdb->get_var("SELECT COUNT(id) FROM $tableName");
//            if ($count==0) {
                $this->initDatabaseData(get_option(self::SQ_BOOKLOOKER_API_KEY_OPTION, 'BOOKLOOKER_API_KEY' ),
                    get_option( self::SQ_EBAY_API_KEY_OPTION, 'EBAY_API_KEY' ));
//            }
        }
    }

    public function init_options() {
        add_option( self::SQ_BOOKLOOKER_API_KEY_OPTION, 'BOOKLOOKER_API_KEY' );
        add_option( self::SQ_EBAY_API_KEY_OPTION, 'EBAY_API_KEY' );
    }

    public function initDatabaseData($booklooker_api_key, $ebay_api_key)
    {
        global $wpdb;
        $tableName = $wpdb->prefix . "sqdb";

        $format = array('%s', '%s', '%s', '%f', '%f', '%f', '%f', '%f', '%d', '%d');

        $data = array(
            'name' => 'amazon',
            'host' => 'www.amazon.de',
            'urlpath' => '/o/ASIN/${ISBN10}',
            'fixcosts' => 1.14,
            'provision' => 0.15,
            'porto_wcl1' => 3.0,
            'porto_wcl2' => 3.0,
            'porto_wcl3' => 3.0,
            'percent_of_sales' => 50,
            'is_active' => 0
        );
        $wpdb->insert($tableName, $data, $format);

        $booklooker_urlpath_template='/interface/search.php?pid=${BOOKLOOKER_API_KEY}&medium=book&limit=1&sortOrder=pricePlusShipping&isbn=${ISBN13}';
        $data = array(
            'name' => 'booklooker',
            'host' => 'www.booklooker.de',
            'urlpath' => str_replace("\${BOOKLOOKER_API_KEY}",$booklooker_api_key,$booklooker_urlpath_template),
            'fixcosts' => 0.0,
            'provision' => 0.082,
            'porto_wcl1' => 3.0,
            'porto_wcl2' => 3.0,
            'porto_wcl3' => 3.0,
            'percent_of_sales' => 10,
            'is_active' => 1
        );
        $wpdb->insert($tableName, $data, $format);

        $data = array(
            'name' => 'buchfreund',
            'host' => 'www.buchfreund.de',
            'urlpath' => '/results.php?q=${ISBN13}&sO=7',
            'fixcosts' => 0.0,
            'provision' => 0.1,
            'porto_wcl1' => 3.0,
            'porto_wcl2' => 3.0,
            'porto_wcl3' => 3.0,
            'percent_of_sales' => 0,
            'is_active' => 0
        );
        $wpdb->insert($tableName, $data, $format);

        $data = array(
            'name' => 'zvab',
            'host' => 'www.zvab.com',
            'urlpath' => '/advancedSearch.do?isbn=${ISBN13}&displayCurrency=EUR&itemsPerPage=10&sortBy=6',
            'fixcosts' => 0,
            'provision' => 0.15,
            'porto_wcl1' => 3.0,
            'porto_wcl2' => 3.0,
            'porto_wcl3' => 3.0,
            'percent_of_sales' => 20,
            'is_active' => 1
        );
        $wpdb->insert($tableName, $data, $format);

        $ebay_urlpath_template = '/services/search/FindingService/v1?SECURITY-APPNAME=${EBAY_API_KEY}&OPERATION-NAME=findItemsByProduct&SERVICE-VERSION=1.0.0&RESPONSE-DATA-FORMAT=XML&REST-PAYLOAD=&productId.@type=ISBN&productId=${ISBN13}&sortOrder=PricePlusShippingLowest&GLOBAL-ID=EBAY-DE&itemFilter(0).name=country&itemFilter(0).value=DE&itemFilter(0).paramName=Currency&itemFilter(0).paramValue=EUR&itemFilter(1).name=ListingType&itemFilter(1).value(0)=FixedPrice&paginationInput.entriesPerPage=1';
        $data = array(
            'name' => 'ebay',
            'host' => 'svcs.ebay.com',
            'urlpath' => str_replace("\${EBAY_API_KEY}",$ebay_api_key,$ebay_urlpath_template),
            'fixcosts' => 0.2,
            'provision' => 0.11,
            'porto_wcl1' => 3.0,
            'porto_wcl2' => 3.0,
            'porto_wcl3' => 3.0,
            'percent_of_sales' => 20,
            'is_active' => 1
        );
        $wpdb->insert($tableName, $data, $format);

        $data = array(
            'name' => 'Easyankauf',
            'host' => 'www.easy-ankauf.de',
            'urlpath' => '/ajax/angebote',
            'fixcosts' => 0,
            'provision' => 0,
            'porto_wcl1' => 0,
            'porto_wcl2' => 0,
            'porto_wcl3' => 0,
            'percent_of_sales' => 0,
            'is_active' => 0
        );
        $wpdb->insert($tableName, $data, $format);
    }

    public static function uninstall()
    {
        global $wpdb;
        $tableName = $wpdb->prefix . "sqdb";

        $wpdb->query("DROP TABLE IF EXISTS " . $tableName);
        delete_option(self::SQDB_DB_VERSION_OPTION);
        delete_option(self::SQ_BOOKLOOKER_API_KEY_OPTION);
        delete_option(self::SQ_EBAY_API_KEY_OPTION);
    }

    public function getAllPlatforms() {
        global $wpdb;
        $tableName = $wpdb->prefix . "sqdb";

        return $wpdb->get_results('SELECT * FROM ' . $tableName, ARRAY_A);
    }
}

