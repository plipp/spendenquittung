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

global $wpdb;
$sqdb = new SpendenQuittungsDB($wpdb);

//add_action('admin_menu', array($sqdb, 'createAdminPluginMenu'));
//add_action('admin_print_styles', array($sqdb, 'createAdminCss'));
//add_action('admin_enqueue_scripts', array($sqdb, 'addAdminScripts'));

register_activation_hook(__FILE__, array($sqdb, 'install'));
register_deactivation_hook(__FILE__, array($sqdb, 'uninstall'));

$platformRegistry = new PlatformRegistry($sqdb->getAllPlatforms());
$valueFromPlatforms = new ValueFromPlatformsAction($platformRegistry);

