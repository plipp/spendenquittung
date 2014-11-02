<?php

/*
  Plugin Name: Spendenquittungs-Parameter-Datenbank
  Description: Spendenquittungs-Parameter-Datenbank
  Version: 1.1
  Author: Patricia Lipp
  License: OpenSource
 */

require_once ("db/spendenQuittungsDB.php");
require_once ("platforms/valueFromPlatformsAction.php");
require_once ("admin/blacklistedBooksAction.php");
require_once ("platforms/platforms.php");
require_once ("pdf/pdfPrintAction.php");

class Bootstrap {

    public function createAuthorPluginMenu() {
        add_menu_page('Spendenquittung', 'Spendenquittung', 'author', 'sq-overview', array($this, 'createAuthorPageOverview'));
        # add_submenu_page('sq-overview', 'Marktplätze bearbeiten', 'Marktplätze berbeiten', 'author', 'sq-marketplaces', array($this, 'createAdminPageMarketplaces'));
        add_submenu_page('sq-overview', 'Schwarze Liste bearbeiten', 'Schwarze Liste bearbeiten', 'author', 'sq-blacklist', array($this, 'createAuthorPageBlacklist'));

    }

    public function createAuthorPageOverview()
    {
        include('templates/author_overview.tpl.php');
    }

    public function createAdminPageMarketplaces()
    {
        include('templates/admin_marketplaces.tpl.php');
    }

    public function createAuthorPageBlacklist()
    {
        include('templates/author_blacklist.tpl.php');
    }

    public function createAdminAndAuthorCss() {
        if (is_admin() || is_author()) {
            wp_enqueue_style('datatables-css', '//cdn.datatables.net/1.10.2/css/jquery.dataTables.css', array(), '1.10.2');
            wp_register_style('sq-admin-style', plugins_url('spendenQuittungAdmin.css', __FILE__));
            wp_enqueue_style('sq-admin-style');
        }
    }

    public function addAuthorScripts() {
        if (is_admin() || is_author()) {
            wp_enqueue_script("datatables", "//cdn.datatables.net/1.10.2/js/jquery.dataTables.js", array('jquery'), '1.10.2');
            wp_enqueue_script('sq-admin-blacklist-app-config', plugin_dir_url(__FILE__) . 'js/admin/app-blacklist-config.js');
            wp_enqueue_script('sq-admin-blacklist-app', plugin_dir_url(__FILE__) . 'js/admin/app-blacklist.js', array('jquery', 'sq-admin-blacklist-app-config'));
            wp_localize_script('sq-admin-blacklist-app-config', 'ajaxConfig', array('ajaxUrl' => admin_url('admin-ajax.php')));
        }
    }

    public function createUserCss() {
        if (!is_admin()) {
            wp_enqueue_style('datatables-css', '//cdn.datatables.net/1.10.2/css/jquery.dataTables.css', array(), '1.10.2');
            wp_register_style('sq-style', plugins_url('spendenQuittung.css', __FILE__));
            wp_enqueue_style('sq-style');
        }
    }

    function addUserScripts () {
        if (!is_admin()) {
            wp_enqueue_script("datatables", "//cdn.datatables.net/1.10.2/js/jquery.dataTables.js", array('jquery'), '1.10.2');
            wp_enqueue_script('sq-app-config', plugin_dir_url(__FILE__) . 'js/quittung/app-config.js');
            wp_enqueue_script('sq-app', plugin_dir_url(__FILE__) . 'js/quittung/app.js', array('jquery', 'sq-app-config'));
            wp_enqueue_script('base64', plugin_dir_url(__FILE__) . 'js/util/base64.js');

            // declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
            wp_localize_script('sq-app-config', 'ajaxConfig', array('ajaxUrl' => admin_url('admin-ajax.php')));
        }
    }

    public function createQuittungsWidget() {
        $content = is_admin() ? "":file_get_contents(dirname(dirname(__FILE__)) . "/spendenquittung/templates/quittung.html");
        return $content;
    }
}

// Initialize hooks, registries and actions
global $wpdb;
$sqdb = new SpendenQuittungsDB();

register_activation_hook(__FILE__, array($sqdb, 'install'));

// TODO remove deactivation hook and use uninstall-hook as soon as development is ready
register_deactivation_hook(__FILE__, array('SpendenQuittungsDB', 'uninstall'));
//register_uninstall_hook(__FILE__, array('SpendenQuittungsDB', 'uninstall'));

$platformRegistry = new PlatformRegistry($sqdb->getAllPlatforms());
$valueFromPlatformsAction = new ValueFromPlatformsAction($platformRegistry, $sqdb->getBlacklistedBooks());
$blacklistAction = new BlacklistedBooksAction($sqdb, $platformRegistry->by(PlatformRegistry::BOOKLOOKER));

$pdfPrint = new PdfPrintAction();

// Bootstrapping
$sq_bootstrap = new Bootstrap();

add_action('admin_menu', array($sq_bootstrap, 'createAuthorPluginMenu'));
add_action('admin_print_styles', array($sq_bootstrap, 'createAdminAndAuthorCss'));
add_action('admin_enqueue_scripts', array($sq_bootstrap, 'addAuthorScripts'));

add_action('wp_print_styles', array($sq_bootstrap, 'createUserCss'));
add_action('wp_enqueue_scripts',  array($sq_bootstrap,'addUserScripts'));

add_shortcode('sq', array($sq_bootstrap, 'createQuittungsWidget'));

