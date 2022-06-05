<div class="grid grid-field">
    <table class="table grid-field__table">
        <thead>
            <tr class="shop-order__header">
                <th>Price Option</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            <% loop $ProcessedPriceOptions %>
                <tr class="$EvenOdd $FirstLast">
                    <td>{$Title}</td>
                    <td>
                        <% if $Top.isReadonly %>
                            <input name="{$Name}" value="{$Value}" class="field text" type="text" disabled="disabled">
                        <% else %>
                            <input name="{$Name}" value="{$Value}" class="field text" type="text">
                        <% end_if %>
                    </td>
                </tr>
            <% end_loop %>
        </tbody>
    </table>
</div>
