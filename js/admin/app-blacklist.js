/* global base64:true, appConfig:true, window: true */
// Structure: see https://github.com/tastejs/todomvc/blob/gh-pages/architecture-examples/jquery/js/app.js
var appBlacklist = (function ($, appConfig) {
    'use strict';

    var config = appConfig;

    var App = {
        init: function () {
            if ($('#sq-blacklist-app').size()<1) {
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
                }
            });
            this.cacheElements();
            this.bindEvents();

            return true;
        },
        cacheElements: function () {
            this.$app = $('#sq-blacklist-app');
            this.$addISBNBtn = this.$app.find('#add-isbn');
            this.$isbn = this.$app.find('#ISBN');
            this.$bookTableBody = this.$app.find('#book-table tbody');
        },
        bindEvents: function () {
            this.$addISBNBtn.on('submit', this.onAddBook.bind(this));
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
        }
    };
    return {
        init: function() {return App.init();}
    };
}(jQuery, appBlacklistConfig));

jQuery(function () {
    'use strict';

    if (!appBlacklist.init()) {
        if (window.console) {window.console.log("Page is not the Blacklist (#sq-blacklist-app)");}
    }

});
