=== spendenquittung ===
Contributors: Patricia Lipp
Tags: spendenquittung, buechertisch
Requires at least: 3.9.1
Tested up to: 3.9.1
Stable tag:
License: GPLv2 or later

Spendenquittung lets the donators of the Buechertisch (http://buechertisch.org/) of Berlin print out a
contribution receipt for their donated books.

== Description ==

Spendenquittung determines the current prices of donated books by checking various platforms like Ebay, Amazon, Booklookers...
and prints out a contribution receipt.

This receipt can be sent together with the donated books to the postal address of the Buechertisch and will be
send back after approval to the donator.

== Installation ==

Upload the Spendenquittung plugin to your blog, activate it, then go to the http://<wordpress>/wp-admin/options.php
page and enter
  - the next sqdb_db_version version,
  - your API keys/secrets for the Ebay-, Amazon and the Booklooker APIs (sq_ebay_api_key, sq_booklooker_api_key, sq_amazon*) and
  - deactivate (TODO: uninstall) + activate the Plugin again.

... You're done!

== Usage ==

Use the shortcode [sq] to integrate the Spendenquittungs from into your pages.

== Changelog ==

= 0.0.1 =

Initial Version

== TODOs ==

- APIs
  -ZVAB must check both ISBN10 + 13

--
- see TODOs
- remove: error_logs

- minify

== SNI ==
- replace Assam completely