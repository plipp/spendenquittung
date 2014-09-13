<?php

require_once(plugin_dir_path(__FILE__) . '../classes/spendenQuittungsDB.php');
class BlacklistedBooksAction
{
    private $_db;

    public function BlacklistedBooksAction($db)
    {
        $this->_db = $db;

        add_action('wp_ajax_request_blacklisted_books', array($this, 'request_blacklisted_books'));
        add_action('wp_ajax_nopriv_request_blacklisted_books', array($this, 'request_blacklisted_books'));

        add_action('wp_ajax_delete_blacklisted_book', array($this, 'delete_blacklisted_book'));
        add_action('wp_ajax_nopriv_delete_blacklisted_book', array($this, 'delete_blacklisted_book'));
    }

    public function delete_blacklisted_book()
    {
        $isbn = sanitize_text_field($_POST['ISBN']);

        $response = $this->_db->deleteBlacklistedBook($isbn);

        if ($response) {
            wp_send_json_success();
        } else {
            wp_send_json_error("book data couldn't be deleted");
        }
        exit; // !!! REQUIRED !!!
    }

    public function request_blacklisted_books()
    {
        $response = $this->_db->getBlacklistedBooks();

        if ($response) {
            wp_send_json_success($response);
        } else {
            wp_send_json_error("book data couldn't be retrieved: Check your internet connection!");
        }
        exit; // !!! REQUIRED !!!
    }

}