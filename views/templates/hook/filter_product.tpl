<section id="filter_products" class="block filter_products">
    <h3 class="title_block">
        <span class="title_block_inner">
            Advanced Search
        </span>
    </h3>
    <div class="block_content list-block">
        <form action="" method="get">
            {foreach from=$get_params key=param_name item=param_value}
                <input type="hidden" name="{$param_name}" value="{$param_value}">
            {/foreach}
            <div class="row" style="margin-bottom:10px;">
                <div class="col-lg-3" style="margin-top: 7px; font-size: 14px;">&nbspCaliber:</div>
                <div class="col-lg-9">
                    <select class="form-control" name="caliber">
                        <option value="">-- select caliber --</option>
                        {foreach from=$caliber_options item=caliber_option}
                            <option value="{if isset($caliber_option[1])}{$caliber_option[1]}{/if}"
                                    {if strcasecmp($caliber, $caliber_option[1]) === 0}selected="selected"{/if}>
                                {$caliber_option[0]}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="row" style="margin-bottom:10px;">
                <div class="col-lg-3" style="margin-top: 7px; font-size: 14px;">&nbspVelocity:</div>
                <div class="col-lg-9">
                    <select class="form-control" name="velocity">
                        <option value="">-- select velocity --</option>
                        {foreach from=$velocity_options item=velocity_option}
                            <option value="{if isset($velocity_option[1])}{$velocity_option[1]}{/if}"
                                    {if isset($velocity_option[1]) && $velocity == $velocity_option[1]}selected="selected"{/if}>
                                {$velocity_option[0]}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4"></div>
                <div class="col-lg-8 text-right">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="icon-filter"></i>Submit</button>
                </div>
            </div>
        </form>
    </div>
</section>
