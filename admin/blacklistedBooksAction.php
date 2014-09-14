<?php

require_once(plugin_dir_path(__FILE__) . '../classes/spendenQuittungsDB.php');
class BlacklistedBooksAction
{
    private $_db;
    private $_booklookerApi;

    public function BlacklistedBooksAction($db, $booklookerApi)
    {
        $this->_db = $db;
        $this->_booklookerApi = $booklookerApi;

        add_action('wp_ajax_request_blacklisted_books', array($this, 'request_blacklisted_books'));
        add_action('wp_ajax_nopriv_request_blacklisted_books', array($this, 'request_blacklisted_books'));

        add_action('wp_ajax_delete_blacklisted_book', array($this, 'delete_blacklisted_book'));
        add_action('wp_ajax_nopriv_delete_blacklisted_book', array($this, 'delete_blacklisted_book'));

        add_action('wp_ajax_add_blacklisted_book', array($this, 'add_blacklisted_book'));
        add_action('wp_ajax_nopriv_add_blacklisted_book', array($this, 'add_blacklisted_book'));
    }

    public function add_blacklisted_book()
    {
        if (empty($_POST['ISBN'])) {
            wp_send_json_error("ISBN missing");
            exit;
        }

        $isbn = sanitize_text_field($_POST['ISBN']); // TODO: remove '-' to avoid duplicates
        $comment = sanitize_text_field($_POST['COMMENT']);

        $xml = $this->_fetchFrom($this->_booklookerApi->urlBy($isbn)); // TODO error handling

        $newEntry =array(
                "isbn" => $isbn,
                "title" => $this->_booklookerApi->titleFrom($xml),
                "author" => $this->_booklookerApi->authorFrom($xml),
                "comment" => $comment);

        $this->_db->addBlacklistedBook($newEntry);

        wp_send_json_success( json_encode($newEntry)); // TODO author/title???
        exit; // !!! REQUIRED !!!
    }

    public function delete_blacklisted_book()
    {
        $isbn = sanitize_text_field($_POST['ISBN']);

        $this->_db->deleteBlacklistedBook($isbn);

        wp_send_json_success();
        exit; // !!! REQUIRED !!!
    }

    public function request_blacklisted_books()
    {
        // TODO: $this->_db->populateBlacklistTable();
        $response = $this->_db->getBlacklistedBooks();

        wp_send_json_success($response);
        exit; // !!! REQUIRED !!!
    }

    function _fetchFrom($url)
    {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

}