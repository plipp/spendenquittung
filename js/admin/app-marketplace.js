/* global base64:true, appConfig:true, window: true */
// Structure: see https://github.com/tastejs/todomvc/blob/gh-pages/architecture-examples/jquery/js/app.js
var appMarketplace = (function ($, appConfig) {
    'use strict';

    var config = appConfig;

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
            this.bindEvents();

            return true;
        },
        cacheElements: function () {
            this.$app = $('#sq-marketplace-app');
            this.$addMarketplaceBtn = this.$app.find('#add-Marketplace');
            this.$marketplaceName = this.$app.find('#marketplace-name');
            this.$marketplaceTableBody = this.$app.find('#marketplace-table tbody');
        },
        bindEvents: function () {
            this.$addMarketplaceBtn.on('submit', this.onAddMarketplace.bind(this));
            this.$marketplaceTableBody.on('click', 'button.delete-row', this.onRemoveMarketplace.bind(this));
        },
        afterMarketplaceAdded: function (response) {
            var $marketplaceName = this.$marketplaceName;
            if (response.success && response.data) {
                var lTable = this.table;
                var marketplaceData = JSON.parse(response.data);
                lTable.row.add([
                    marketplaceData.marketplace, marketplaceData.title, marketplaceData.profit, '<button class="delete-row">X</button>'
                ]).draw();
                $marketplaceName.val("");
            } else {
                // TODO
            }
        },
        addMarketplace: function (marketplace) {
            document.body.style.cursor = 'wait';
            $.ajax({
                type: "POST",
                url: config.urlForMarketplaceDataFromPlatforms(),
                data: { marketplace: marketplace }
            }).done(this.afterMarketplaceAdded.bind(this)).always(function () {
                document.body.style.cursor = 'default';
            });
        },
        onAddMarketplace: function (event) {
            var marketplace = this.$marketplaceName.val().trim();
            this.addMarketplace(marketplace);
            event.preventDefault();
        },
        onRemoveMarketplace: function (event) {
            this.table.row($(event.target).parents('tr'))
                .remove()
                .draw();
            event.preventDefault();
        }
    };
    return {
        init: function() {return App.init();}
    };
}(jQuery, appMarketplaceConfig));

jQuery(function () {
    'use strict';

    if (!appMarketplace.init()) {
        if (window.console) {window.console.log("Page is not the Marketplace (#sq-marketplace-app)");}
    }

});
