<div id="sq-assam-app" class="sq">
    <div class="sq-container">
        <div id="sq-books">
            <div>
                <div class="sq-with-tb-padding">
                    <h1>Assam II</h1>

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
                        <th>Platform</th>
                        <th>Preis incl. Porto/€</th>
                        <th>Gewinn 1 /€</th>
                        <th>Gewinn 2 /€</th>
                        <th>Gewinn 3 /€</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="2" style="text-align:right">Berechneter Spendenquittungspreis:</th>
                            <th style="text-align: right"></th>
                            <th colspan="2" style="text-align:right">Formel: 1/(&sum;(anteil(p)) * &sum;(anteil(p)*preis(p))</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>