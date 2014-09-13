/* global ajaxConfig:true */
var appBlacklistConfig = (function (ajaxConfig) {
    'use strict';

    return {
        ajaxUrl: ajaxConfig.ajaxUrl,
        actionForBookDataFromPlatforms: "request_blacklisted_books",
        urlForBlacklistDataFromPlatforms: function () {
            return this.ajaxUrl + "?action=" + this.actionForBookDataFromPlatforms;
        }
    };
})(ajaxConfig); // ajaxConfig from: wp_localize_script( 'sq-app-config', 'ajaxConfig', array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ) ) );