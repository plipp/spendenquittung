<?php

require_once(plugin_dir_path(__FILE__) . '../db/spendenQuittungsDB.php');
class PlatformsAction
{
    private $_db;

    public function PlatformsAction($db)
    {
        $this->_db = $db;

        add_action('wp_ajax_request_platforms', array($this, 'request_platforms'));
    }


    public function request_platforms()
    {
        $projected_fields = array("name", "host", "fixcosts", "provision", "porto_wcl1", "porto_wcl2", "porto_wcl3", "percent_of_sales", "is_active");
        $response = $this->_db->getAllPlatforms($projected_fields);

        wp_send_json_success($response);
        exit; // !!! REQUIRED !!!
    }
    
}