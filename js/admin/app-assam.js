/* global base64:true, appAssamConfig:true, window: true */
// Structure: see https://github.com/tastejs/todomvc/blob/gh-pages/architecture-examples/jquery/js/app.js
var app = (function ($, appConfig) {
    'use strict';

    var config = appConfig;

    var util = {
        toFloat: function (value) {
            if (typeof value === "string") {
                return parseFloat(value.replace(',', '.'));
            } else {
                return value;
            }
        },
        toString: function (value) {
            if (typeof value === 'number') {
                return value.toLocaleString("de-DE", {minimumFractionDigits: 2, maximumFractionDigits: 2});
            } else {
                return value;
            }
        }
    };

    var App = {
        init: function () {
            if ($('#sq-assam-app').size() < 1) {
                return false;
            }

            this.table = $('#book-table').DataTable({
                "paging": false,
                "info": false,
                "columns": [
                    {className: "dt-body-left"},
                    {className: "dt-body-right"},
                    {className: "dt-body-right"},
                    {className: "dt-body-right"},
                    {className: "dt-body-right"},
                    {className: "dt-body-right"},
                    {className: "dt-body-center"},
                    {className: "dt-body-center"}
                ],
                "language": {
                    "sEmptyTable": "Noch kein Buch ausgewählt oder keine Plattform, die das Buch anbietet, gefunden",
                    "sInfo": "_START_ bis _END_ von _TOTAL_ Einträgen",
                    "sInfoEmpty": "0 bis 0 von 0 Einträgen",
                    "sInfoFiltered": "(gefiltert von _MAX_ Einträgen)",
                    "sInfoPostFix": "",
                    "sInfoThousands": ".",
                    "sLengthMenu": "_MENU_ Einträge anzeigen",
                    "sLoadingRecords": "Wird geladen...",
                    "sProcessing": "Bitte warten...",
                    "sSearch": "Suchen",
                    "oAria": {
                        "sSortAscending": ": aktivieren, um Spalte aufsteigend zu sortieren",
                        "sSortDescending": ": aktivieren, um Spalte absteigend zu sortieren"
                    }
                }
            });
            this.fetchMarketplaces();

            this.cacheElements();
            this.bindEvents();

            return true;
        },
        cacheElements: function () {
            this.$app = $('#sq-assam-app');
            this.$setBookBtn = this.$app.find('#add-isbn');
            this.$isbn = this.$app.find('#ISBN');
            this.$title = this.$app.find('#title');
            this.$bookTableBody = this.$app.find('#book-table tbody');
        },
        bindEvents: function () {
            this.$setBookBtn.on('submit', this.onSetBook.bind(this));
        },
        afterBookSet: function (response) {
            function addTableRow(lTable, platform, httpStatus, comment, percentOfSales, profit, profitsByWeightClasses) {
                comment = comment || "-";
                percentOfSales = percentOfSales || "-";
                httpStatus = httpStatus || 200;
                profit = profit ? util.toString(profit) : "-";

                var profitsByWeightClasses1 = profitsByWeightClasses ? util.toString(profitsByWeightClasses[1]) : '-';
                var profitsByWeightClasses2 = profitsByWeightClasses ? util.toString(profitsByWeightClasses[2]) : '-';
                var profitsByWeightClasses3 = profitsByWeightClasses ? util.toString(profitsByWeightClasses[3]) : '-';
                return lTable.row.add([
                    platform,
                    percentOfSales,
                    profit,
                    profitsByWeightClasses1,
                    profitsByWeightClasses2,
                    profitsByWeightClasses3,
                    comment,
                    (httpStatus >= 200 && httpStatus <=300) ? 'OK':httpStatus
                ]).node();
            }

            function highlightInRed($row) {
                $($row).css({'color': 'red', 'font-weight': 'bold'});
            }

            function lowlight($row) {
                $($row).css({'color': 'grey'});
            }

            function setTitle(title, isbn) {
                if (title && title.trim() !== "" && title.trim() !== "?") {
                    $title.text(title + " (ISBN: " + isbn + ")");
                } else {
                    $title.text("");
                }
            }

            var lTable = this.table;

            lTable.clear();
            var $isbn = this.$isbn;
            var $title = this.$title;

            var percentOfSalesByMarketplace = this.percentOfSalesByMarketplace || {}

            if (response.success && response.data) {
                var bookData = JSON.parse(response.data);

                if (bookData.status === 'BL') {
                    highlightInRed(addTableRow(lTable, "Buch ist auf der Schwarzen Liste", '-' ,'nehmen wir nicht'));
                } else {
                    $.each(bookData.profitsByWeightClasses, function (platform, profitsByWeightClasses) {
                        var profit = bookData.profits[platform];
                        var percentOfSales = (platform in percentOfSalesByMarketplace) ? percentOfSalesByMarketplace[platform]:"?";

                        if (profit < 0) {
                            lowlight(addTableRow(lTable, platform, bookData.httpStatus[platform], "keine Daten", percentOfSales));
                        } else {
                            addTableRow(lTable, platform, bookData.httpStatus[platform], "", percentOfSales, profit, profitsByWeightClasses);
                        }
                    });
                }
                setTitle(bookData.title, bookData.isbn);

                $(lTable.column(2).footer()).html(util.toString(bookData.profit));

                $isbn.val("");
            } else {
                highlightInRed(addTableRow(lTable, "Platformen nicht erreichbar", '?', 'keine Daten'));
            }
            lTable.draw();
        },
        setBook: function (isbn) {
            document.body.style.cursor = 'wait';
            $.ajax({
                type: "POST",
                url: config.urlForBookDataFromPlatforms(),
                headers: {'x-internal-request': 'true'},
                data: {ISBN: isbn}
            }).done(this.afterBookSet.bind(this)).always(function () {
                document.body.style.cursor = 'default';
            });
        },
        onSetBook: function (event) {
            var isbn = this.$isbn.val().trim();
            this.setBook(isbn);
            event.preventDefault();
        },
        afterMarketplacesFetched: function (response) {
            var percentOfSalesByMarketplace=this.percentOfSalesByMarketplace;

            if (response.success && response.data) {
                var marketplaceDatas = response.data;
                $.each(marketplaceDatas, function (index, marketplaceData) {
                    percentOfSalesByMarketplace[marketplaceData.name] = marketplaceData.percent_of_sales
                });
            } else {
                window.console && window.console.log ("Die Plattformdaten konnten nicht ermittelt werden!");
            }
        },
        fetchMarketplaces: function () {
            this.percentOfSalesByMarketplace = {};

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
        init: function () {
            return App.init();
        }
    };
}(jQuery, appAssamConfig));

jQuery(function () {
    'use strict';

    if (app.init()) {
        if (window.console) {
            window.console.log("Initialization of assam-site finished");
        }
    } else {
        if (window.console) {
            window.console.log("Page is not the assam-site (#sq-assam-app)");
        }
    }

});
