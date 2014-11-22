/* global ajaxConfig:true */
var appAssamConfig = (function (ajaxConfig) {
    'use strict';

    return {
        ajaxUrl: ajaxConfig.ajaxUrl,
        actionForValuesFromPlatforms: "request_value_from_platforms",
        urlForBookDataFromPlatforms: function () {
            return this.ajaxUrl + "?action=" + this.actionForValuesFromPlatforms;
        }                                                      ,
        actionForPlatforms: "request_platforms",
        urlForPlatforms: function () {
            return this.ajaxUrl + "?action=" + this.actionForPlatforms;
        }
    };
})(ajaxConfig);