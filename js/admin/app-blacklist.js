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
                    { className: "dt-body-left" },
                    { className: "dt-body-left" },
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
            this.$setBookBtn = this.$app.find('#add-book');
            this.$isbn = this.$app.find('#ISBN');
            this.$comment = this.$app.find('#comment');
            this.$bookTableBody = this.$app.find('#book-table tbody');
        },
        bindEvents: function () {
            this.$setBookBtn.on('submit', this.onSetBook.bind(this));
            this.$bookTableBody.on('click', 'button.delete-row', this.onRemoveBook.bind(this));
        },
        afterBooksFetched: function (response) {
            if (response.success) {
                if (response.data) {
                    var lTable = this.table;
                    var bookDatas = response.data;
                    $.each(bookDatas, function (index, bookData) {
                        lTable.row.add([
                            bookData.isbn, bookData.title, bookData.author, bookData.comment, '<button class="delete-row">X</button>'
                        ]);
                    });
                    lTable.draw();
                }
            } else {
                alert("Die schwarze Liste konnten nicht ermittelt werden. " +
                    "Bitte checken Sie Ihre Internet-Verbindung.");
            }
        },
        fetchBooks: function () {
            document.body.style.cursor = 'wait';
            $.ajax({
                type: "POST",
                url: config.urlForBlacklistedBooks()
            }).done(this.afterBooksFetched.bind(this)).always(function () {
                document.body.style.cursor = 'default';
            });
        },
        afterBookSet: function (response) {
            var $isbn = this.$isbn;
            if (response.success) {
                if (response.data) {
                    var lTable = this.table;
                    var bookData = JSON.parse(response.data);
                    lTable.row.add([
                        bookData.isbn, bookData.title, bookData.author, bookData.comment, '<button class="delete-row">X</button>'
                    ]).draw();
                    $isbn.val("");
                }
            } else {
                alert ("Das Buch mit der ISBN '" + $isbn.val() + "' konnte nicht hinzugefügt werden. Ist es schon in der Liste?");
            }
        },
        setBook: function (isbn, comment) {
            document.body.style.cursor = 'wait';
            $.ajax({
                type: "POST",
                url: config.urlForAddingBlacklistedBook(),
                data: { ISBN: isbn, COMMENT: comment }
            }).done(this.afterBookSet.bind(this)).
                always(function () {
                    document.body.style.cursor = 'default';
                });
        },
        onSetBook: function (event) {
            var isbn = this.$isbn.val().trim(),
                comment = this.$comment.val().trim();
            this.setBook(isbn, comment);
            event.preventDefault();
        },
        onRemoveBook: function (event) {
            var $row = $(event.target).parents('tr'), isbn=$row.find('td:first').text();
            $.ajax({
                type: "POST",
                url: config.urlForDeletionOfBlacklistBook(),
                data: { ISBN: isbn }
            });
            this.table.row($row)
                .remove()
                .draw();

            event.preventDefault();
        }
    };
    return {
        init: function() {return App.init();},
        fetchBooks: function() { return App.fetchBooks();}
    };
}(jQuery, appBlacklistConfig));

jQuery(function () {
    'use strict';

    if (appBlacklist.init()) {
        appBlacklist.fetchBooks();
    } else {
        if (window.console) {window.console.log("Page is not the Blacklist (#sq-blacklist-app)");}
    }

});
