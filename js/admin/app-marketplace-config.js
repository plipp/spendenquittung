/* global ajaxConfig:true */
var appMarketplaceConfig = (function (ajaxConfig) {
    'use strict';

    return {
        ajaxUrl: ajaxConfig.ajaxUrl,
        actionForBlacklistedBooks: "request_value_from_platforms",
        actionForPdfPrint: "as_pdf",
        urlForBookDataFromPlatforms: function () {
            return this.ajaxUrl + "?action=" + this.actionForBlacklistedBooks;
        },
        urlForPdfPrint: function() {
            return this.ajaxUrl  + "?action=" + this.actionForPdfPrint;
        }
    };
})(ajaxConfig); // ajaxConfig from: wp_localize_script( 'sq-app-config', 'ajaxConfig', array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ) ) );