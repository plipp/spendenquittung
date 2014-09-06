<div id="sq-app" class="sq">
    <div class="sq-container">
        <div id="sq-marketplaces">
            <div>
                <div class="sq-with-tb-padding">
                    <h1>Marktplätze</h1>

                    <form id="add-marketplace">
                        <label>Name:
                            <input type="text" id="marketplace-name" required placeholder="Amazon">
                        </label>

                        <input class="sq-button" type="submit" value="Hinzufügen">
                    </form>
                </div>
                <table id="marketplace-table" class="display" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Domain</th>
                        <th>Url-Pfad</th>
                        <th>Fixkosten/€</th>
                        <th>Provision/%</th>
                        <th>Porto <= 450g/€</th>
                        <th>Porto <= 950g/€</th>
                        <th>Porto  > 950g/€</th>
                        <th>Verkaufsanteil/%</th>
                        <th>wird zur Berechnung herangezogen</th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th colspan="2" style="text-align:right">Summe:</th>
                        <th style="text-align: right">0.00</th>
                        <th></th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
 </div>