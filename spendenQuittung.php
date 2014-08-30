<?php

/*
  Plugin Name: Spendenquittungs-Parameter-Datenbank
  Description: Spendenquittungs-Parameter-Datenbank
  Version: 1.1
  Author: Patricia Lipp
  License: OpenSource
 */

require_once ("classes/spendenQuittungsDB.php");
require_once ("platforms/valueFromPlatformsAction.php");
require_once ("platforms/platforms.php");
require_once ("pdf/pdfPrintAction.php");

class Bootstrap {

    public function createUserCss() {
        wp_enqueue_style( 'datatables-css', '//cdn.datatables.net/1.10.2/css/jquery.dataTables.css', array(), '1.10.2' );
        wp_register_style('sq-style', plugins_url('spendenQuittung.css', __FILE__));
        wp_enqueue_style('sq-style');
    }

    function addUserScripts () {
        wp_enqueue_script("datatables", "//cdn.datatables.net/1.10.2/js/jquery.dataTables.js", array( 'jquery' ), '1.10.2');
        wp_enqueue_script( 'sq-app-config', plugin_dir_url( __FILE__ ) . 'js/quittung/app-config.js');
        wp_enqueue_script( 'sq-app', plugin_dir_url( __FILE__ ) . 'js/quittung/app.js', array( 'jquery', 'sq-app-config' ) );
        wp_enqueue_script( 'base64', plugin_dir_url( __FILE__ ) . 'js/util/base64.js');

        // declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
        wp_localize_script( 'sq-app-config', 'ajaxConfig', array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ) ) );
    }

    public function createQuittungsWidget() {
        $content = file_get_contents(dirname(dirname(__FILE__)) . "/spendenquittung/templates/quittung.html");
        return $content;
    }
}

global $wpdb;
$sqdb = new SpendenQuittungsDB();

//add_action('admin_menu', array($sqdb, 'createAdminPluginMenu'));
//add_action('admin_print_styles', array($sqdb, 'createAdminCss'));
//add_action('admin_enqueue_scripts', array($sqdb, 'addAdminScripts'));

register_activation_hook(__FILE__, array($sqdb, 'install'));
register_uninstall_hook(__FILE__, array('SpendenQuittungsDB', 'uninstall'));

$platformRegistry = new PlatformRegistry($sqdb->getAllPlatforms());
$valueFromPlatforms = new ValueFromPlatformsAction($platformRegistry);
$pdfPrint = new PdfPrintAction();

$sq_bootstrap = new Bootstrap();
add_action('wp_print_styles', array($sq_bootstrap, 'createUserCss'));
add_action('wp_enqueue_scripts',  array($sq_bootstrap,'addUserScripts'));

add_shortcode('sq', array($sq_bootstrap, 'createQuittungsWidget'));

