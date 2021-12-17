{if count($options) > 0}
    <section class="page-product-box">
        <p class="page-product-heading"><strong>{l s='Technical Data'}</strong></p>
        <table class="table-data-sheet" style="width: 100%">
            {foreach from=$options key=k item=v}
                <tr class="{cycle values="odd,even"}">
                    <td style="border: 1px solid #d6d4d4; padding: 2px">{$k|escape:'html':'UTF-8'}</td>
                    <td style="border: 1px solid #d6d4d4; padding: 2px">{$v|escape:'html':'UTF-8'}</td>
                </tr>
            {/foreach}
        </table>
    </section>
{/if}