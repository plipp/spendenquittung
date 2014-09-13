<div id="sq-blacklist-app" class="sq">
    <div class="sq-container">
        <div id="sq-books">
            <div>
                <div class="sq-with-tb-padding">
                    <h1>Schwarze Liste</h1>

                    <form id="add-book">
                        <label>Gültige ISBN:
                            <input type="text" id="ISBN" required placeholder="978-3570303283"
                                   pattern="(\d{3}-)*\d{10}">
                        </label>
                        <label>Kommentar:
                            <input type="text" id="comment" size="80">
                        </label>

                        <input class="sq-button" type="submit" value="Hinzufügen">
                    </form>
                </div>
                <table id="book-table" class="display" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>ISBN</th>
                        <th>Titel</th>
                        <th>AutorIn</th>
                        <th>Kommentar</th>
                    </tr>
                    </thead>
                    <tfoot>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>