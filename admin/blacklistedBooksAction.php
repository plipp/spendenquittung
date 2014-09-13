<?php

require_once(plugin_dir_path(__FILE__) . '../classes/spendenQuittungsDB.php');
class BlacklistedBooksAction
{
    private $_db;

    public function BlacklistedBooksAction($db)
    {
        $this->_db = $db;
        add_action('wp_ajax_request_value_from_platforms', array($this, 'request_blacklisted_books'));
        add_action('wp_ajax_nopriv_request_value_from_platforms', array($this, 'request_blacklisted_books'));
    }

    public function request_blacklisted_books()
    {
        $response = $this->_db.getBlacklistedBooks();

        if ($response) {
            wp_send_json_success($response);
        } else {
            wp_send_json_error("book data couldn't be retrieved: Check your internet connection!");
        }
        exit; // !!! REQUIRED !!!
    }

}