<?php

require_once(plugin_dir_path(__FILE__) . '../db/spendenQuittungsDB.php');
class PlatformsAction
{
    private $_db;

    public function PlatformsAction($db)
    {
        $this->_db = $db;

        add_action('wp_ajax_request_platforms', array($this, 'request_platforms'));
//        add_action('wp_ajax_nopriv_request_platforms', array($this, 'request_platforms'));
    }


    public function request_platforms()
    {
        $response = $this->_db->getAllPlatforms();

        wp_send_json_success($response);
        exit; // !!! REQUIRED !!!
    }
    
}