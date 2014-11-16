/* global ajaxConfig:true */
var appBlacklistConfig = (function (ajaxConfig) {
    'use strict';

    return {
        ajaxUrl: ajaxConfig.ajaxUrl,
        actionForValuesFromPlatforms: "request_blacklisted_books",
        actionForDeletionOfBlacklistedBook: "delete_blacklisted_book",
        actionForAddingBlacklistedBook: "add_blacklisted_book",
        urlForBlacklistedBooks: function () {
            return this.ajaxUrl + "?action=" + this.actionForValuesFromPlatforms;
        },
        urlForDeletionOfBlacklistBook: function () {
            return this.ajaxUrl + "?action=" + this.actionForDeletionOfBlacklistedBook;
        },
        urlForAddingBlacklistedBook: function() {
            return this.ajaxUrl + "?action=" +  this.actionForAddingBlacklistedBook;
        }
    };
})(ajaxConfig); // ajaxConfig from: wp_localize_script( 'sq-app-config', 'ajaxConfig', array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ) ) );