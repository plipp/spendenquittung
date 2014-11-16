/* global ajaxConfig:true */
var appConfig = (function (ajaxConfig) {
    'use strict';

    return {
        ajaxUrl: ajaxConfig.ajaxUrl,
        actionForValuesFromPlatforms: "request_value_from_platforms",
        actionForPdfPrint: "as_pdf",
        urlForBookDataFromPlatforms: function () {
            return this.ajaxUrl + "?action=" + this.actionForValuesFromPlatforms;
        },
        urlForPdfPrint: function() {
            return this.ajaxUrl  + "?action=" + this.actionForPdfPrint;
        }
    };
})(ajaxConfig); // ajaxConfig from: wp_localize_script( 'sq-app-config', 'ajaxConfig', array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ) ) );