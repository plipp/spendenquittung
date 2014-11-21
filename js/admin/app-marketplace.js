/* global base64:true, appMarketplaceConfig:true, window: true */
// Structure: see https://github.com/tastejs/todomvc/blob/gh-pages/architecture-examples/jquery/js/app.js
var appMarketplace = (function ($, appMarketplaceConfig) {
    'use strict';

    var config = appMarketplaceConfig;

    var App = {
        init: function () {
            if ($('#sq-marketplace-app').size()<1) {
                return false;
            }

            this.table = $('#marketplace-table').DataTable({
                "paging": false,
                "info": false,
                "columns": [
                    { className: "dt-body-left" },
                    { className: "dt-body-left" },
                    { className: "dt-body-right" },
                    { className: "dt-body-right" },
                    { className: "dt-body-right" },
                    { className: "dt-body-right" },
                    { className: "dt-body-right" },
                    { className: "dt-body-right" },
                    { className: "dt-body-center" }
                ],
                "language": {
                    "sEmptyTable": "Keine Daten in der Tabelle vorhanden",
                    "sInfo": "_START_ bis _END_ von _TOTAL_ Einträgen",
                    "sInfoEmpty": "0 bis 0 von 0 Einträgen",
                    "sInfoFiltered": "(gefiltert von _MAX_ Einträgen)",
                    "sInfoPostFix": "",
                    "sInfoThousands": ".",
                    "sLengthMenu": "_MENU_ Einträge anzeigen",
                    "sLoadingRecords": "Wird geladen...",
                    "sProcessing": "Bitte warten...",
                    "sSearch": "Suchen",
                    "sZeroRecords": "Keine Einträge vorhanden.",
                    "oPaginate": {
                        "sFirst": "Erste",
                        "sPrevious": "Zurück",
                        "sNext": "Nächste",
                        "sLast": "Letzte"
                    },
                    "oAria": {
                        "sSortAscending": ": aktivieren, um Spalte aufsteigend zu sortieren",
                        "sSortDescending": ": aktivieren, um Spalte absteigend zu sortieren"
                    }
                }
            });
            this.cacheElements();

            return true;
        },
        cacheElements: function () {
            this.$app = $('#sq-marketplace-app');
        },
        afterMarketplacesFetched: function (response) {
            if (response.success) {
                if (response.data) {
                    var lTable = this.table;
                    var marketplaceDatas = response.data;
                    $.each(marketplaceDatas, function (index, marketplaceData) {
                        lTable.row.add([
                            marketplaceData.name,
                            marketplaceData.host,
                            marketplaceData.fixcosts,
                            marketplaceData.provision,
                            marketplaceData.porto_wcl1,
                            marketplaceData.porto_wcl2,
                            marketplaceData.porto_wcl3,
                            marketplaceData.percent_of_sales,
                            marketplaceData.is_active>0 ? 'Ja':'Nein'
                        ]);
                    });
                    lTable.draw();
                }
            } else {
                alert("Die Plattformen konnten nicht ermittelt werden. " +
                "Bitte checken Sie Ihre Internet-Verbindung.");
            }
        },
        fetchMarketplaces: function () {
            document.body.style.cursor = 'wait';
            $.ajax({
                type: "GET",
                url: config.urlForPlatforms()
            }).done(this.afterMarketplacesFetched.bind(this)).always(function () {
                document.body.style.cursor = 'default';
            });
        }
    };
    return {
        init: function() {return App.init();},
        fetchMarketplaces: function() {return App.fetchMarketplaces();}

    };
}(jQuery, appMarketplaceConfig));

jQuery(function () {
    'use strict';

    if (appMarketplace.init()) {
        appMarketplace.fetchMarketplaces();
    } else {
        if (window.console) {window.console.log("Page is not the Marketplace (#sq-marketplace-app)");}
    }

});
