<form class="form-horizontal" method="get" action="{$action_url}" target="_blank">
    <input type="hidden" name="controller" value="{$controller}">
    <input type="hidden" name="token" value="{$token}">
    <input type="hidden" name="ajax" value="1">
    <input type="hidden" name="method" value="taxReport">
    <input type="hidden" name="header" value="text/plain">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon icon-download"></i> {l s='Report' mod='purechoiceimport'}
        </div>
        <div class="form-wrapper">
            <div class="form-group">
                <div>
                    <label for="report_date_from" class="control-label col-lg-3">Date From:</label>
                    <div class="col-lg-9">
                        <input id="report_date_from" class="form-control fixed-width-xxl datepicker" type="text" size="5" name="date_from" value="{$date_from}" placeholder="dd-mm-yyyy">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div>
                    <label for="report_date_to" class="control-label col-lg-3">Date To:</label>
                    <div class="col-lg-9">
                        <input id="report_date_to" class="form-control fixed-width-xxl datepicker" type="text" size="5" name="date_to" value="{$date_to}" placeholder="dd-mm-yyyy">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div>
                    <label for="report_order_states" class="control-label col-lg-3">Order States:</label>
                    <div class="col-lg-9">
                        <select id="report_order_states" class="form-control fixed-width-xxl" name="order_states[]" multiple style="height:25em;">
                            {foreach from=$order_states item=order_state}
                                <option value="{$order_state.id_order_state}"{if in_array($order_state.id_order_state,$order_states_selected)} selected{/if}>{$order_state.name}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <button type="submit" class="btn btn-default pull-right" name="report_submit"><i class="process-icon-save"></i> Submit</button>
        </div>
    </div>
    <script type="text/javascript">
        $(function () {
            $(".datepicker").datepicker({
                prevText: '',
                nextText: '',
                dateFormat: 'dd-mm-yy',
                altFormat: 'dd-mm-yy'
            });
        });
    </script>
</form>