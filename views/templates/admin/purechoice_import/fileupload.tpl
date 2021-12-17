{literal}
    <!-- The template to display files available for upload -->
    <script id="template-upload" type="text/x-tmpl">
    {% for (var i=0, file; file=o.files[i]; i++) { %}
        <tr class="template-upload fade">
            <td>
                <div class="name">{%=file.name%}</div>
                <strong class="error text-danger"></strong>
            </td>
            <td>
                <div class="size">Processing...</div>
                <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
            </td>
            <td>
                <div class="btn-group">
                    {% if (!i && !o.options.autoUpload) { %}
                    <button class="btn btn-default start" disabled>
                        <i class="glyphicon glyphicon-upload"></i>
                        <span>Start</span>
                    </button>
                    {% } %}
                    {% if (!i) { %}
                    <button class="btn btn-default cancel">
                        <i class="glyphicon glyphicon-ban-circle"></i>
                        <span>Cancel</span>
                    </button>
                    {% } %}
                </div>
            </td>
        </tr>
    {% } %}
    </script>
    <!-- The template to display files available for download -->
    <script id="template-download" type="text/x-tmpl">
    {% for (var i=0, file; file=o.files[i]; i++) { %}
        <tr class="template-download fade">
            <td>
                <div class="name">
                    {% if (file.url) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
                    {% } else { %}
                    <span>{%=file.name%}</span>
                    {% } %}
                </div>
                {% if (file.error) { %}
                <div><span class="label label-danger">Error</span> {%=file.error%}</div>
                {% } %}
            </td>
            <td>
                <div class="size">{%=o.formatFileSize(file.size)%}</div>
            </td>
            <td>
                <div class="btn-group">
                    {% if (!file.error) { %}
                    <button type="button" class="btn btn-default trs-product-preview" data-file="{%=file.name%}">
                        <i class="glyphicon glyphicon-eye-open"></i>
                        <span>Products Preview</span>
                    </button>
                    {% } %}
                    {% if (!file.error) { %}
                    <button type="button" class="btn btn-default trs-product-import" data-file="{%=file.name%}">
                        <i class="glyphicon glyphicon-import"></i>
                        <span>Products Import</span>
                    </button>
                    {% } %}
                    {% if (!file.error) { %}
                    <button type="button" class="btn btn-default trs-category-import" data-file="{%=file.name%}">
                        <i class="glyphicon glyphicon-import"></i>
                        <span>Categories Import</span>
                    </button>
                    {% } %}
                    {% if (file.deleteUrl) { %}
                    <button class="btn btn-danger delete" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
                        <i class="glyphicon glyphicon-trash"></i>
                        <span>Delete</span>
                    </button>
                </div>
                    &nbsp;<input type="checkbox" name="delete" value="1" class="toggle">
                    {% } else { %}
                    <button class="btn btn-warning cancel">
                        <i class="glyphicon glyphicon-ban-circle"></i>
                        <span>Cancel</span>
                    </button>
                </div>
                    {% } %}
            </td>
        </tr>
    {% } %}
    </script>
{/literal}
