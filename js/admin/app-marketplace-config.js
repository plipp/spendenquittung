/* global ajaxConfig:true */
var appMarketplaceConfig = (function (ajaxConfig) {
    'use strict';

    return {
        ajaxUrl: ajaxConfig.ajaxUrl,
        actionForPlatforms: "request_platforms",
        urlForPlatforms: function () {
            return this.ajaxUrl + "?action=" + this.actionForPlatforms;
        }
    };
})(ajaxConfig); // ajaxConfig from: wp_localize_script( 'sq-app-config', 'ajaxConfig', array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ) ) );