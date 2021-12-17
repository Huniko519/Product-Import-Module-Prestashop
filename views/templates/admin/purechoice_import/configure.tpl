<div id="alert-message" class="alert alert-success">
    <a class="alert-close pull-right" href="javascript:void(0);"><i class="glyphicon glyphicon-remove"></i></a>
    <span class="alert-message"></span>
</div>
<div id="pc-import" class="panel">
    <h3><i class="icon icon-credit-card"></i> {l s='Gun Import' mod='purechoiceimport'}</h3>
    <div id="fileupload">
        <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
        <div class="row fileupload-buttonbar">
            <div class="col-lg-6">
                <!-- The fileinput-button span is used to style the file input field as button -->
                    <span class="btn btn-default fileinput-button">
                        <i class="glyphicon glyphicon-plus"></i>
                        <span>{l s='Add files' mod='tablerateshipping'}</span>
                        <input type="file" name="files[]" multiple>
                    </span>
                    <button type="submit" class="btn btn-default start">
                        <i class="glyphicon glyphicon-upload"></i>
                        <span>{l s='Start upload' mod='tablerateshipping'}</span>
                    </button>
                    <button type="reset" class="btn btn-default cancel">
                        <i class="glyphicon glyphicon-ban-circle"></i>
                        <span>{l s='Cancel upload' mod='tablerateshipping'}</span>
                    </button>
                    <button type="button" class="btn btn-danger delete">
                        <i class="glyphicon glyphicon-trash"></i>
                        <span>{l s='Delete' mod='tablerateshipping'}</span>
                    </button>
                &nbsp;<input type="checkbox" class="toggle">
                <!-- The global file processing state -->
                <span class="fileupload-process"></span>
            </div>
            <!-- The global progress state -->
            <div class="col-lg-6 fileupload-progress fade">
                <!-- The global progress bar -->
                <div class="progress progress-striped active" role="progressbar"
                     aria-valuemin="0"
                     aria-valuemax="100">
                    <div class="progress-bar progress-bar-success" style="width:0;"></div>
                </div>
                <!-- The extended global progress state -->
                <div class="progress-extended">&nbsp;</div>
            </div>
        </div>
        <!-- The table listing the files available for upload/download -->
        <table role="presentation" class="table table-striped">
            <thead>
            <tr>
                <th class="trs-uploaddownload-file-column">{l s='File' mod='tablerateshipping'}</th>
                <th class="trs-uploaddownload-size-column" width="20%">{l s='Size' mod='tablerateshipping'}</th>
                <th class="trs-uploaddownload-actions-column" width="50%">{l s='Actions' mod='tablerateshipping'}</th>
            </tr>
            </thead>
            <tbody class="files"></tbody>
        </table>
        <div id="options" class="form-horizontal">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="exchange" class="col-sm-4 control-label"><span><i></i></span> Exchange Rate</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="exchange" value="{if isset($options.exchange)}{$options.exchange}{else}1.26{/if}">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="gst" class="col-sm-4 control-label"><span><i></i></span> GST (%)</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="gst" value="{if isset($options.gst)}{$options.gst}{else}5{/if}">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="markup" class="col-sm-4 control-label"><span><i></i></span> Markup Cost</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="markup" value="{if isset($options.markup)}{$options.markup}{else}15{/if}">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="tax" class="col-sm-4 control-label"><span><i></i></span> Tax Rule</label>
                        <div class="col-sm-8">
                            <select id="tax" class="form-control">
                                <option value="0">No Tax</option>
                                {foreach from=$taxes item=tax}
                                    <option value="{$tax.id_tax_rules_group}"{if isset($options.tax) && $options.tax == $tax.id_tax_rules_group} selected="selected"{/if}>{$tax.name}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="velocity" class="col-sm-4 control-label"><span><i></i></span> Velocity <</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="velocity" value="{if isset($options.velocity)}{$options.velocity}{else}366{/if}">
                        </div>
                    </div>
                    <div class="form-group margin-bottom-none">
                        <label for="catvelgtcat" class="col-sm-4 control-label"><span><i></i></span> Category velocity ></label>
                        <div class="col-sm-8">
                            <div class="row">
                                <div class="col-sm-6">
                                    <select id="catvelgtcat" class="form-control">
                                        <option value="0">All Categories</option>
                                        {foreach from=$categories item=category}
                                            <option value="{$category.id_category}"{if isset($options.catvelgtcat) && $options.catvelgtcat == $category.id_category} selected="selected"{/if}>{$category.name}</option>
                                        {/foreach}
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="catvelgtvel" value="{if isset($options.catvelgtvel)}{$options.catvelgtvel}{else}366{/if}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-offset-4 col-sm-8">
                            <button id="deleted-excluded" type="button" class="btn btn-danger">
                                <i class="glyphicon glyphicon-trash"></i>
                                <span>{l s='Delete Products by Exclude Keywords' mod='tablerateshipping'}</span>
                            </button>
                            <button id="disabled-velocity" type="button" class="btn btn-danger">
                                <i class="glyphicon glyphicon-trash"></i>
                                <span>{l s='Enable/Disable Products by Velocity Range' mod='tablerateshipping'}</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="exclude" class="col-sm-4 control-label"><span><i></i></span> Exclude Keywords</label>
                        <div class="col-sm-8">
                            <textarea class="form-control" id="exclude" rows="3">{if isset($options.exclude)}{$options.exclude}{else}silencer, moderator, whisper, suppressor{/if}</textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="caliber_options" class="col-sm-4 control-label"><span><i></i></span>
                            Widget Caliber Options<br>(<a href="#" onclick="javascript:void(0)" data-toggle="modal" data-target="#caliber_all">Show all</a>)</label>
                        <div class="col-sm-8">
                            <textarea class="form-control" id="caliber_options" rows="3">{if isset($options.caliber_options)}{$options.caliber_options}{else}{/if}</textarea>
                        </div>
                        <div id="caliber_all" class="modal fade" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title">All Distinct Caliber Values</h4>
                                    </div>
                                    <div class="modal-body">
                                        <ul>
                                            {foreach from=$caliber_all item=caliber}
                                                <li>{$caliber}</li>
                                            {/foreach}
                                        </ul>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->
                    </div>
                    <div class="form-group margin-bottom-none">
                        <label for="velocity_options" class="col-sm-4 control-label"><span><i></i></span>
                            Widget Velocity Options<br>(<a href="#" onclick="javascript:void(0)" data-toggle="modal" data-target="#velocity_all">Show all</a>)</label>
                        <div class="col-sm-8">
                            <textarea class="form-control" id="velocity_options" rows="3">{if isset($options.velocity_options)}{$options.velocity_options}{else}{/if}</textarea>
                        </div>
                        <div id="velocity_all" class="modal fade" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title">All Distinct Velocity Values</h4>
                                    </div>
                                    <div class="modal-body">
                                        <ul>
                                            {foreach from=$velocity_all item=velocity}
                                                <li>{$velocity}</li>
                                            {/foreach}
                                        </ul>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="preview">
        <div class="panel">
            <h3><i class="icon-AdminCatalog"></i> Products Preview</h3>
            <table id="previewtable" class="table table-striped">
                <thead>
                <tr>
                    <th>SKU</th>
                    <th>Image</th>
                    <th>Category</th>
                    <th>Manufacturer</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Caliber</th>
                    <th>Velocity</th>
                    <th>WNet</th>
                    <th>Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
    <div id="statistics">
        <div class="panel">
            <h3><i class="icon-AdminParentStats"></i> Live Statistics 
                <span class="livestatistcs">
                    <i class="icon-refresh icon-spin icon-fw"></i>
                </span>
            </h3>
            <table id="statisticsdata" class="table">
                <tr class="stat-products-import stat-categories-import">
                    <td class="text-right" width="25%"><strong>Rows processed:</strong></td>
                    <td><span id="stat-rows-processed">0</span> / <span id="stat-total-rows">0</span></td>
                </tr>
                <tr class="stat-products-import">
                    <td class="text-right"><strong>Products created:</strong></td>
                    <td><span id="stat-products-created">0</span></td>
                </tr>
                <tr class="stat-products-import">
                    <td class="text-right"><strong>Products updated:</strong></td>
                    <td><span id="stat-products-updated">0</span></td>
                </tr>
                <tr class="stat-products-import">
                    <td class="text-right"><strong>Products excluded:</strong></td>
                    <td><span id="stat-products-excluded">0</span></td>
                </tr>
                <tr class="stat-products-import">
                    <td class="text-right"><strong>Products failed:</strong></td>
                    <td><span id="stat-products-failed">0</span></td>
                </tr>
                <tr class="stat-products-import stat-categories-import">
                    <td class="text-right"><strong>Categories created:</strong></td>
                    <td><span id="stat-categories-created">0</span></td>
                </tr>
                <tr class="stat-categories-import">
                    <td class="text-right"><strong>Categories failed:</strong></td>
                    <td><span id="stat-categories-failed">0</span></td>
                </tr>
                <tr class="stat-products-import">
                    <td class="text-right"><strong>Manufacturers created:</strong></td>
                    <td><span id="stat-manufacturers-created">0</span></td>
                </tr>
                <tr class="stat-products-import">
                    <td class="text-right" width="25%"><strong>Products excluded SKUs:</strong></td>
                    <td><span id="stat-products-excluded-skus"></span></td>
                </tr>
                <tr class="stat-products-import">
                    <td class="text-right"><strong>Products failed SKUs:</strong></td>
                    <td><span id="stat-products-failed-skus"></span></td>
                </tr>
                <tr class="stat-categories-import">
                    <td class="text-right"><strong>Categories failed names:</strong></td>
                    <td><span id="stat-categories-failed-names"></span></td>
                </tr>
            </table>
            <br><h3><i class="icon icon-credit-card"></i> New added items</h3>
            <table class="table table-striped table-products">
                <thead>
                    <tr>
                        <th><span>Category</></th>
                        <th><span>SKU</span></th>
                        <th><span>Description</span></th>
                        <th><span>Velocity</span></th>
                    </tr>
                </thead>
                <tbody id="newaddeditems"></tbody>
            </table>
        </div>
    </div>
</div>
<div class="panel">
    <h3><i class="icon icon-credit-card"></i> {l s='Category Duty' mod='purechoiceimport'}</h3>
    <table class="table">
        <thead>
        <tr>
            <th class="text-right" width="25%">Category</th>
            <th>
                <div class="row">
                    <div class="col-md-2">Duty</div>
                    <div class="col-md-2">Shipping</div>
                    <div class="col-md-2">Product Width</div>
                    <div class="col-md-2">Product Height</div>
                    <div class="col-md-2">Product Depth</div>
                    <div class="col-md-2">Show Widget</div>
                </div>
            </th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$categories item=category}
            <tr>
                <td class="text-right" width="25%"><span><i></i></span> {$category.name}</td>
                <td>
                    <div class="row">
                        <div class="col-md-2"><input class="cat-duty" type="text" value="{$category.duty}" data-cat="{$category.id_category}"></div>
                        <div class="col-md-2"><input class="cat-shipping" type="text" value="{$category.shipping}" data-cat="{$category.id_category}"></div>
                        <div class="col-md-2"><input class="cat-width" type="text" value="{$category.width}" data-cat="{$category.id_category}"></div>
                        <div class="col-md-2"><input class="cat-height" type="text" value="{$category.height}" data-cat="{$category.id_category}"></div>
                        <div class="col-md-2"><input class="cat-depth" type="text" value="{$category.depth}" data-cat="{$category.id_category}"></div>
                        <div class="col-md-2"><input class="cat-show_widget" type="checkbox" value="1" style="margin-top:8px;"
                                                     data-cat="{$category.id_category}"{if $category.show_widget} checked="checked"{/if}></div>
                    </div>
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
</div>
{include file=$module_views_dir|cat:"templates/admin/purechoice_import/fileupload.tpl"}
