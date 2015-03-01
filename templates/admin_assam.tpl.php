<div id="sq-assam-app" class="sq">
    <div class="sq-container">
        <div id="sq-books">
            <div>
                <div class="sq-with-tb-padding">
                    <h1>Buchwertberechnung - Assam II</h1>

                    <form id="add-isbn">
                        <label>Gültige ISBN:
                            <input type="text" id="ISBN" required placeholder="978-3570303283"
                                   pattern="(\d{3}-?)*\d{9}(\d|X)"></label>

                        <input class="sq-button" type="submit" value="Setzen">
                    </form>
                </div>
                <div>
                    <h1 id="title"></h1>
                </div>
                <table id="book-table" class="display" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th style="text-align:left">Platform</th>
                        <th>Verkaufsanteile in % *</th>
                        <th>Preis incl. Porto/€</th>
                        <th>Gewinn 1 /€</th>
                        <th>Gewinn 2 /€</th>
                        <th>Gewinn 3 /€</th>
                        <th>Kommentar</th>
                        <th>Status**</th>
                    </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="2" style="text-align:left">Berechneter Spendenquittungspreis:</th>
                            <th style="text-align: right"></th>
                            <th style="text-align: right"></th>
                            <th style="text-align: right"></th>
                            <th style="text-align: right"></th>
                            <th colspan="2" style="text-align:right">Formel: 1/(&sum;(verkaufsanteil(p)) * &sum;(verkaufsanteil(p)*preis(p))</th>
                        </tr>
                    </tfoot>
                </table>
                <div class="sq-with-table-padding">
                    *Weitere Parameter finden sich auf der <a href="admin.php?page=sq-marketplaces">Liste der Plattformen</a><br>
                    **Eine Zahl bedeutet, dass ein Fehler aufgetreten ist. Zur Analyse kann
                      <a href="http://de.wikipedia.org/wiki/HTTP-Statuscode">die Liste der Status-Codes</a> herangezogen werden.
                </div>
            </div>
        </div>
    </div>
</div>