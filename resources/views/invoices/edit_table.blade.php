<thead  {!! $isTasks ? 'style="display:none;" data-bind="visible: $root.hasTasks"' : '' !!}>
    @if ($isTasks)
        <tr><td style="20px" colspan="20"></td></tr>
    @endif
    <tr>
        <th style="min-width:32px;" class="hide-border"></th>
        <th style="min-width:120px;width:25%">{{ $invoiceLabels[$isTasks ? 'service' : 'item'] }}</th>
        <th style="width:100%">{{ $invoiceLabels['description'] }}</th>
        @if ($account->showCustomField('custom_invoice_item_label1'))
            <th style="min-width:120px">{{ $account->custom_invoice_item_label1 }}</th>
        @endif
        @if ($account->showCustomField('custom_invoice_item_label2'))
            <th style="min-width:120px">{{ $account->custom_invoice_item_label2 }}</th>
        @endif
        <th style="min-width:120px">{{ $invoiceLabels[$isTasks ? 'rate' : 'unit_cost'] }}</th>
        <th style="{{ $account->hide_quantity ? 'display:none' : 'min-width:120px' }}">{{ $invoiceLabels[$isTasks ? 'hours' : 'quantity'] }}</th>
        <th style="min-width:{{ $account->enable_second_tax_rate ? 180 : 120 }}px;display:none;" data-bind="visible: $root.invoice_item_taxes.show">{{ trans('texts.tax') }}</th>
        <th style="min-width:120px;">{{ trans('texts.line_total') }}</th>
        <th style="min-width:32px;" class="hide-border"></th>
    </tr>
</thead>
<tbody data-bind="sortable: { data: invoice_items_{{ $isTasks ? 'with_tasks' : 'without_tasks' }}, allowDrop: false, afterMove: onDragged} {{ $isTasks ? ', visible: $root.hasTasks' : '' }}"
    {!! $isTasks ? 'style="display:none;border-spacing: 100px"' : '' !!}>
    <tr data-bind="event: { mouseover: showActions, mouseout: hideActions }" class="sortable-row">
        <td class="hide-border td-icon">
            <i style="display:none" data-bind="visible: actionsVisible() &amp;&amp;
                $parent.invoice_items_{{ $isTasks ? 'with_tasks' : 'without_tasks' }}().length > 1" class="fa fa-sort"></i>
        </td>
        <td>
            <div id="scrollable-dropdown-menu">
                <input id="product_key" type="text" data-bind="productTypeahead: product_key, items: $root.products, key: 'product_key', valueUpdate: 'afterkeydown', attr: {name: 'invoice_items[{{ $isTasks ? 'T' : '' }}' + $index() + '][product_key]'}" class="form-control invoice-item handled"/>
            </div>
        </td>
        <td>
            <textarea data-bind="value: notes, valueUpdate: 'afterkeydown', attr: {name: 'invoice_items[{{ $isTasks ? 'T' : '' }}' + $index() + '][notes]'}"
                rows="1" cols="60" style="resize: vertical;height:42px" class="form-control word-wrap"></textarea>
                <input type="text" data-bind="value: task_public_id, attr: {name: 'invoice_items[{{ $isTasks ? 'T' : '' }}' + $index() + '][task_public_id]'}" style="display: none"/>
                <input type="text" data-bind="value: expense_public_id, attr: {name: 'invoice_items[{{ $isTasks ? 'T' : '' }}' + $index() + '][expense_public_id]'}" style="display: none"/>
                <input type="text" data-bind="value: invoice_item_type_id, attr: {name: 'invoice_items[{{ $isTasks ? 'T' : '' }}' + $index() + '][invoice_item_type_id]'}" style="display: none"/>
        </td>
        @if ($account->showCustomField('custom_invoice_item_label1'))
            <td>
                <input data-bind="value: custom_value1, valueUpdate: 'afterkeydown', attr: {name: 'invoice_items[{{ $isTasks ? 'T' : '' }}' + $index() + '][custom_value1]'}" class="form-control invoice-item"/>
            </td>
        @endif
        @if ($account->showCustomField('custom_invoice_item_label2'))
            <td>
                <input data-bind="value: custom_value2, valueUpdate: 'afterkeydown', attr: {name: 'invoice_items[{{ $isTasks ? 'T' : '' }}' + $index() + '][custom_value2]'}" class="form-control invoice-item"/>
            </td>
        @endif
        <td>
            <input data-bind="value: prettyCost, valueUpdate: 'afterkeydown', attr: {name: 'invoice_items[{{ $isTasks ? 'T' : '' }}' + $index() + '][cost]'}"
                style="text-align: right" class="form-control invoice-item"/>
        </td>
        <td style="{{ $account->hide_quantity ? 'display:none' : '' }}">
            <input data-bind="value: prettyQty, valueUpdate: 'afterkeydown', attr: {name: 'invoice_items[{{ $isTasks ? 'T' : '' }}' + $index() + '][qty]'}"
                style="text-align: right" class="form-control invoice-item" name="quantity"/>
        </td>
        <td style="display:none;" data-bind="visible: $root.invoice_item_taxes.show">
                {!! Former::select('')
                        ->addOption('', '')
                        ->options($taxRateOptions)
                        ->data_bind('value: tax1, event:{change:onTax1Change}')
                        ->addClass($account->enable_second_tax_rate ? 'tax-select' : '')
                        ->raw() !!}
            <input type="text" data-bind="value: tax_name1, attr: {name: 'invoice_items[{{ $isTasks ? 'T' : '' }}' + $index() + '][tax_name1]'}" style="display:none">
            <input type="text" data-bind="value: tax_rate1, attr: {name: 'invoice_items[{{ $isTasks ? 'T' : '' }}' + $index() + '][tax_rate1]'}" style="display:none">
            <div data-bind="visible: $root.invoice().account.enable_second_tax_rate == '1'">
                {!! Former::select('')
                        ->addOption('', '')
                        ->options($taxRateOptions)
                        ->data_bind('value: tax2, event:{change:onTax2Change}')
                        ->addClass('tax-select')
                        ->raw() !!}
            </div>
            <input type="text" data-bind="value: tax_name2, attr: {name: 'invoice_items[{{ $isTasks ? 'T' : '' }}' + $index() + '][tax_name2]'}" style="display:none">
            <input type="text" data-bind="value: tax_rate2, attr: {name: 'invoice_items[{{ $isTasks ? 'T' : '' }}' + $index() + '][tax_rate2]'}" style="display:none">
        </td>
        <td style="text-align:right;padding-top:9px !important" nowrap>
            <div class="line-total" data-bind="text: totals.total"></div>
        </td>
        <td style="cursor:pointer" class="hide-border td-icon">
            <i style="padding-left:2px" data-bind="click: $parent.removeItem, visible: actionsVisible() &amp;&amp; !isEmpty()"
            class="fa fa-minus-circle redlink" title="Remove item"/>
        </td>
    </tr>
</tbody>
