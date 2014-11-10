/* global base64:true, appConfig:true, window: true */
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
                return value.toLocaleString("de-DE", { minimumFractionDigits: 2, maximumFractionDigits: 2});
            } else {
                return value;
            }
        }
    };

    var App = {
        init: function () {
            if ($('#sq-app').size()<1) {
                return false;
            }

            this.table = $('#book-table').DataTable({
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
                },
                "footerCallback": function () {
                    var api = this.api();
                    var sum = api.column(2).data().reduce(function (a, b) {
                        return util.toFloat(a) + util.toFloat(b);
                    }, 0);
                    $(api.column(2).footer()).html(util.toString(sum));
                }
            });
            this.cacheElements();
            this.bindEvents();

            return true;
        },
        cacheElements: function () {
            this.$app = $('#sq-app');
            this.$addBookBtn = this.$app.find('#add-isbn');
            this.$userData = this.$app.find('#user-data');
            this.$isbn = this.$app.find('#ISBN');
            this.$bookTableBody = this.$app.find('#book-table tbody');
            this.$hiddenBookList = this.$app.find('#book-list');
            this.$hiddenSum = this.$app.find('#sum');
        },
        bindEvents: function () {
            this.$addBookBtn.on('submit', this.onAddBook.bind(this));
            this.$userData.on('submit', this.beforePrintPdf.bind(this));
            this.$bookTableBody.on('click', 'button.delete-row', this.onRemoveBook.bind(this));
        },
        bookDataNotAvailable: function (isbn) {
            alert("Titel und Preis des Buches konnten nicht ermittelt werden. " +
                "Bitte checken Sie noch einmal die ISBN '" + isbn + "' bzw. Ihre Internet-Verbindung.");
        },
        afterBookAdded: function (response) {
            var $isbn = this.$isbn;
            if (response.success && response.data) {
                var lTable = this.table;
                var bookData = JSON.parse(response.data);
                lTable.row.add([
                    bookData.isbn, bookData.title, bookData.profit, '<button class="delete-row">X</button>'
                ]).draw();
                $isbn.val("");
            } else {
                this.bookDataNotAvailable($isbn.val());
            }
        },
        addBook: function (isbn) {
            document.body.style.cursor = 'wait';
            $.ajax({
                type: "POST",
                url: config.urlForBookDataFromPlatforms(),
                data: { ISBN: isbn }
            }).done(this.afterBookAdded.bind(this)).always(function () {
                document.body.style.cursor = 'default';
            });
        },
        onAddBook: function (event) {
            var isbn = this.$isbn.val().trim();
            this.addBook(isbn);
            event.preventDefault();
        },
        onRemoveBook: function (event) {
            this.table.row($(event.target).parents('tr'))
                .remove()
                .draw();
            event.preventDefault();
        },
        beforePrintPdf: function () {
            function toJson(tableRows) {
                return JSON.stringify($.map(tableRows, function (value) {
                    return {isbn: value[0], title: value[1], profit: value[2]};
                }));
            }
            function totalAmountFrom(table) {
                return $(table.column(2).footer()).html();
            }

            var booksAsJson = toJson(this.table.rows().data());
            this.$hiddenBookList.val(base64.encode(booksAsJson));
            this.$hiddenSum.val(totalAmountFrom(this.table));

            this.$userData.attr("action", config.urlForPdfPrint());

            // ... regular submit ...
        },
        dummyBooks: function() {
            this.addBook("0385351399");
            this.addBook("0307385906");
            this.addBook("978-1557787903");
        }
    };
    return {
        init: function() {return App.init();},
        dummyBooks: function() {App.dummyBooks();}
    };
}(jQuery, appConfig));

jQuery(function () {
    'use strict';

    if (app.init()) {
        // app.dummyBooks();
        if (window.console) {window.console.log("Initialization of quittungs-site finished");}
    } else {
        if (window.console) {window.console.log("Page is not the Spendenquittung (#sq-app)");}
    }

});
