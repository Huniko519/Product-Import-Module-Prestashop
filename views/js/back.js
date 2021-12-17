$(function () {
    var url,
        $fileUpload,
        $options,
        $preview,
        $previewTable,
        $statistics,
        // $statisticsTable,
        $alert;

    var init = function () {
        url = 'index.php?controller=AdminPurechoiceImport&token=' + getUrlVar('token') + '&ajax=1';
        $fileUpload = $('#fileupload');
        $options = $('#options');
        $preview = $('#preview');
        $previewTable = $('#previewtable');
        $statistics = $('#statistics');
        // $statisticsTable = $('#statisticstable');
        $alert = $('#alert-message');
        setupFileUpload();
        setupPreviewTable();
        // setupStatisticsTable();
        $preview.hide();
        $statistics.hide();
        $alert.find('.alert-close').on('click blur', function () {
            $(this).closest('.alert').fadeOut('slow');
        });
        $('#exchange,#gst,#markup,#tax,#velocity,#exclude,#caliber_options,#velocity_options,#catvelgtcat,#catvelgtvel').on('change', setOptions);
        $('.cat-duty,.cat-shipping,.cat-width,.cat-height,.cat-depth,.cat-show_widget').on('change', setCategoryOption);
        $('#deleted-excluded').on('click', deleteExcludedProducts);
        $('#disabled-velocity').on('click', disableVelocityProducts);
    };

    var setupFileUpload = function () {
        $fileUpload
            .fileupload({
                url: url + '&method=processAttachment',
                acceptFileTypes: /(\.|\/)(txt)$/i
            })
            .bind('fileuploadadd', function () {
                showEmpty();
            })
            .bind('fileuploadstarted', function () {
                $fileUpload.addClass('fileupload-processing');
            })
            .bind('fileuploadfinished', function () {
                $(this).removeClass('fileupload-processing');
                showEmpty();
            })
            .bind('fileuploadfail', function () {
                showEmpty();
            })
            .bind('fileuploaddestroy', function () {
                $fileUpload.addClass('fileupload-processing');
            })
            .bind('fileuploaddestroyed', function () {
                $(this).removeClass('fileupload-processing');
                showEmpty();
            });
        loadFiles();
        $fileUpload.on('click', '.trs-product-preview', productPreview);
        $fileUpload.on('click', '.trs-product-import', productImport);
        $fileUpload.on('click', '.trs-category-import', categoryImport);
    };

    var setupPreviewTable = function () {
        $previewTable.dataTable({
            "ajax": {
                "url": url + '&method=getProducts',
                "data": function (data) {
                    data.file = $fileUpload.find('.trs-active-product-preview .trs-product-preview').attr('data-file');
                }
            },
            "dom": "<'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'pl>>",
            "processing": true,
            "serverSide": true,
            "bSort": false,
            "bFilter": false,
            "order": [[0, "ASC"]],
            "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
            "columns": [
                {"data": "SKU", "class": "column-sku"},
                {"data": "SmallImageURL", "class": "column-image"},
                {"data": "Category", "class": "column-category"},
                {"data": "Manufacturer", "class": "column-manufacturer"},
                {"data": "ProductName", "class": "column-name"},
                {"data": "ProductDescription1", "class": "column-description"},
                {"data": "Caliber", "class": "column-caliber"},
                {"data": "Velocity", "class": "column-velocity"},
                {"data": "WNet", "class": "column-price"},
                {"data": "SKU", "class": "column-actions"}
            ],
            "columnDefs": [{
                render: function (data) {
                    return '<img src="' + data + '" alt="" width="45">';
                },
                targets: 1
            }, {
                render: function (data) {
                    return data.substring(0, 50) + ((data.length > 50) ? '...' : '');
                },
                targets: 4
            }, {
                render: function (data) {
                    return data.substring(0, 50) + ((data.length > 50) ? '...' : '');
                },
                targets: 5
            }, {
                render: function (data) {
                    return '<button class="btn btn-default trs-import-sku" data-sku="' + data + '"><i class="glyphicon glyphicon-import"></i> Import</button>';
                },
                targets: 9
            }],
            "initComplete": function (settings, json) {
            },
            "rowCallback": function (row, data, index) {
            },
            "drawCallback": function (settings) {
            }
        });
        $previewTable.on('click', '.trs-import-sku', productImportBySKU);
    };

    var setupStatisticsTable = function () {
        // $statisticsTable.dataTable({
        //     "ajax": {
        //         "url": url + '&method=getProductsImported'
        //     },
        //     "dom": "<'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'pl>>",
        //     "processing": true,
        //     "serverSide": true,
        //     "bSort": false,
        //     "bFilter": false,
        //     "order": [[0, "ASC"]],
        //     "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        //     "columns": [
        //         {"data": "SKU", "class": "column-sku"},
        //         {"data": "MainImageURL", "class": "column-image"},
        //         {"data": "Category", "class": "column-category"},
        //         {"data": "Manufacturer", "class": "column-manufacturer"},
        //         {"data": "ProductName", "class": "column-name"},
        //         {"data": "ProductDescription", "class": "column-description"},
        //         {"data": "ShippingWeight", "class": "column-weight"},
        //         {"data": "InStockQuantity", "class": "column-quantity"},
        //         {"data": "UPC", "class": "column-upc"},
        //         {"data": "Caliber", "class": "column-caliber"},
        //         {"data": "Velocity", "class": "column-velocity"},
        //         {"data": "WNet", "class": "column-wnet"},
        //         {"data": "Shipping", "class": "column-shipping"},
        //         {"data": "Exchange", "class": "column-exchange"},
        //         {"data": "GST", "class": "column-gst"},
        //         {"data": "Duty", "class": "column-duty"},
        //         {"data": "Markup", "class": "column-markup"},
        //         {"data": "Tax", "class": "column-tax"},
        //         {"data": "Price", "class": "column-price"},
        //         {"data": "WholesalePrice", "class": "column-wholesale-price"},
        //         {"data": "Active", "class": "column-active"}
        //     ],
        //     "initComplete": function (settings, json) {
        //     },
        //     "rowCallback": function (row, data, index) {
        //         $('td', row).each(function () {
        //             var text = $(this).html(),
        //                 state = 'none',
        //                 html = $('<span/>');

        //             if (text.search(/^##add##/) !== -1) {
        //                 state = 'add';
        //                 text = text.replace(/^##add##/, '');
        //             } else if (text.search(/^##update##/) !== -1) {
        //                 state = 'update';
        //                 text = text.replace(/^##update##/, '');
        //             } else if (text.search(/^##exclude##/) !== -1) {
        //                 state = 'exclude';
        //                 text = text.replace(/^##exclude##/, '');
        //             } else if (text.search(/^##fail##/) !== -1) {
        //                 state = 'fail';
        //                 text = text.replace(/^##fail##/, '');
        //             }

        //             html.attr('title', text).addClass('state-' + state);
        //             $(this).html(html);

        //             if ($(this).hasClass('column-sku')) {
        //                 $(this).prepend('<span>' + text + '&nbsp;&nbsp;</span>');
        //             }
        //         });
        //     },
        //     "drawCallback": function (settings) {
        //     }
        // });
    };

    var loadFiles = function () {
        $.ajax({
            method: 'get',
            url: $fileUpload.fileupload('option', 'url'),
            dataType: 'json',
            context: $fileUpload[0],
            beforeSend: function () {
                $fileUpload.addClass('fileupload-processing');
            }
        }).always(function () {
            $(this).removeClass('fileupload-processing');
        }).done(function (result) {
            $(this).fileupload('option', 'done').call(this, $.Event('done'), {result: result});
        });
    };

    var clearFileUploadRows = function () {
        $fileUpload.find('tr')
            .removeClass('trs-active-product-preview')
            .removeClass('trs-active-product-import')
            .removeClass('trs-active-category-import');

        productPreviewByFile();
        productImportByFile();
        categoryImportByFile();
    };

    var productPreview = function () {
        clearFileUploadRows();
        $(this).closest('tr').addClass('trs-active-product-preview');

        productPreviewByFile();
    };

    var productImport = function () {
        clearFileUploadRows();
        $(this).closest('tr').addClass('trs-active-product-import');

        productImportByFile();
    };

    var categoryImport = function () {
        clearFileUploadRows();
        $(this).closest('tr').addClass('trs-active-category-import');

        categoryImportByFile();
    };

    var productPreviewByFile = function () {
        var activeRow = $fileUpload.find('.trs-active-product-preview');
        if (activeRow.length) {
            $preview.show();
            $previewTable.api().ajax.reload();
        } else {
            $preview.hide();
            $previewTable.api().clear();
        }
    };
    
    var productImportByFile = function () {
        var activeRow = $fileUpload.find('.trs-active-product-import');
        if (activeRow.length) {
            $statistics.show();
            $statistics.find('#statisticsdata tr.stat-products-import').show();
            // var interval = intervalImportByFileStatus();
            $('.livestatistcs').css('display', 'block');
            ajaxRequest({
                'method': 'productImportByFile',
                'file': $fileUpload.find('.trs-active-product-import .trs-product-import').attr('data-file'),
                'exchange': $('#exchange').val(),
                'gst': $('#gst').val(),
                'markup': $('#markup').val(),
                'tax': $('#tax').val(),
                'velocity': $('#velocity').val(),
                'exclude': $('#exclude').val(),
                'catvelgtcat': $('#catvelgtcat').val(),
                'catvelgtvel': $('#catvelgtvel').val()
            }, activeRow.find('.trs-product-import'))
                .error(function () {
                    console.clear();
                    u_Statistics();
                    setTimeout(productImportByFile, 5000);
                })
                .success(function (response) {
                    console.log(response);
                    if (response !== null && typeof response.statistics !== 'undefined') {
                        $('.livestatistcs').css('display', 'none');
                        u_Statistics();
                        // $statisticsTable.closest('.dataTables_wrapper').show();
                        // $statisticsTable.api().ajax.reload();
                    }
                });
        } else {
            $statistics.hide();
            resetImportByFileStatistics();
        }
    };
    var u_Statistics = function () {
        ajaxRequest({'method': 'getImportByFileStatus'}, $(this))
        .success(function (response) {
            console.log(response);
            if (response !== null) {
                    $.each(response, function (key, value) {
                        if( key == 'products-created'){
                            $('#stat-products-created').text(Number($('#stat-products-created').html())+value);
                        }else if( key == 'products-updated'){
                            $('#stat-products-updated').text(Number($('#stat-products-updated').html())+value);
                        }else if( key == 'products-created-data' ){
                            if( value != '' ){
                                var allresult = value.split(':||:').sort();
                                var html = "";
                                for( num in allresult ){
                                    html += "<tr><td>"+allresult[num].split(':|:')[0]+"</td><td>"+allresult[num].split(':|:')[1]+"</td><td>"+allresult[num].split(':|:')[2]+"</td><td>"+allresult[num].split(':|:')[3]+"</td></tr>";
                                }
                                $('#newaddeditems').append(html);
                            }
                        }else{
                             $('#stat-' + key).text(value);
                        }
                    });
                    var show_update_val = Number($('#stat-rows-processed').text())-Number($('#stat-products-failed').text())-Number($('#stat-products-created').text())-Number($('#stat-products-excluded').text());
                    $('#stat-products-updated').text(show_update_val);
                 
            }
        });
    };

    var categoryImportByFile = function () {
        var activeRow = $fileUpload.find('.trs-active-category-import');
        if (activeRow.length) {
            $statistics.show();
            $statistics.find('#statisticsdata tr.stat-categories-import').show();

            var interval = intervalImportByFileStatus();

            ajaxRequest({
                'method': 'categoryImportByFile',
                'file': $fileUpload.find('.trs-active-category-import .trs-category-import').attr('data-file')
            }, activeRow.find('.trs-category-import'))
                .complete(function () {
                    clearInterval(interval);
                })
                .success(function (response) {
                    if (response !== null && typeof response.statistics !== 'undefined') {
                        setImportByFileStatistics(response.statistics);
                    }
                });
        } else {
            $statistics.hide();
            resetImportByFileStatistics();
        }
    };

    var intervalImportByFileStatus = function () {
        var sendreq = true;

        return setInterval(function () {
            if (sendreq) {
                sendreq = false;
                ajaxRequest({'method': 'getImportByFileStatus'}, $(this))
                .success(function (response) {
                    if (response !== null) {
                        console.log(response);
                        setImportByFileStatistics(response);
                    }
                    sendreq = true;
                });
            }
        }, 10000);
    };

    var resetImportByFileStatistics = function () {
        $statistics.find('#statisticsdata tr').hide();
        // $statisticsTable.closest('.dataTables_wrapper').hide();

        $('#stat-rows-processed').text('0');
        $('#stat-total-rows').text('0');
        $('#stat-products-created').text('0');
        $('#stat-products-updated').text('0');
        $('#stat-products-excluded').text('0');
        $('#stat-products-failed').text('0');
        $('#stat-categories-created').text('0');
        $('#stat-categories-failed').text('0');
        $('#stat-manufacturers-created').text('0');
        $('#stat-products-excluded-skus').text('--');
        $('#stat-products-failed-skus').text('--');
        $('#stat-categories-failed-names').text('--');
    };

    var setImportByFileStatistics = function (statistics) {
        $.each(statistics, function (key, value) {
            if( key == 'products-created'){
                $('#stat-products-created').text(Number($('#stat-products-created').html())+value);
            }else if( key == 'products-updated'){
                $('#stat-products-updated').text(Number($('#stat-products-updated').html())+value);
            }else if( key == 'products-created-data' ){
                if( value != '' ){
                    var allresult = value.split(':||:').sort();
                    var html = "";
                    for( num in allresult ){
                        html += "<tr><td>"+allresult[num].split(':|:')[0]+"</td><td>"+allresult[num].split(':|:')[1]+"</td><td>"+allresult[num].split(':|:')[2]+"</td><td>"+allresult[num].split(':|:')[3]+"</td></tr>";
                    }
                    $('#newaddeditems').append(html);
                }
            }else{
                 $('#stat-' + key).text(value);
            }
        });
    };

    var productImportBySKU = function () {
        ajaxRequest({
            'method': 'productImportByFileAndSKU',
            'file': $fileUpload.find('.trs-active-product-preview .trs-product-preview').attr('data-file'),
            'sku': $(this).attr('data-sku'),
            'exchange': $('#exchange').val(),
            'gst': $('#gst').val(),
            'markup': $('#markup').val(),
            'tax': $('#tax').val(),
            'velocity': $('#velocity').val(),
            'exclude': $('#exclude').val(),
            'catvelgtcat': $('#catvelgtcat').val(),
            'catvelgtvel': $('#catvelgtvel').val()
        }, $(this));
    };

    var setOptions = function () {
        var field = $(this),
            exchange = $('#exchange'),
            gst = $('#gst'),
            markup = $('#markup'),
            tax = $('#tax'),
            velocity = $('#velocity'),
            exclude = $('#exclude'),
            caliber_options = $('#caliber_options'),
            velocity_options = $('#velocity_options'),
            catvelgtcat = $('#catvelgtcat'),
            catvelgtvel = $('#catvelgtvel');

        ajaxRequest({
            'method': 'setOptions',
            'exchange': exchange.val(),
            'gst': gst.val(),
            'markup': markup.val(),
            'tax': tax.val(),
            'velocity': velocity.val(),
            'exclude': exclude.val(),
            'caliber_options': caliber_options.val(),
            'velocity_options': velocity_options.val(),
            'catvelgtcat': catvelgtcat.val(),
            'catvelgtvel': catvelgtvel.val()
        }, field.closest('.form-group').find('span'))
            .success(function (response) {
                if (response !== null) {
                    exchange.val(response.exchange);
                    gst.val(response.gst);
                    markup.val(response.markup);
                    tax.val(response.tax);
                    velocity.val(response.velocity);
                    exclude.val(response.exclude);
                    caliber_options.val(response.caliber_options);
                    velocity_options.val(response.velocity_options);
                    catvelgtcat.val(response.catvelgtcat);
                    catvelgtvel.val(response.catvelgtvel);
                }
            });
    };

    var setCategoryOption = function () {
        var field = $(this),
            duty = field.closest('tr').find('.cat-duty'),
            shipping = field.closest('tr').find('.cat-shipping'),
            width = field.closest('tr').find('.cat-width'),
            height = field.closest('tr').find('.cat-height'),
            depth = field.closest('tr').find('.cat-depth'),
            showWidget = field.closest('tr').find('.cat-show_widget');

        ajaxRequest({
            'method': 'setCategoryOption',
            'id_category': field.attr('data-cat'),
            'duty': duty.val(),
            'shipping': shipping.val(),
            'width': width.val(),
            'height': height.val(),
            'depth': depth.val(),
            'show_widget': +showWidget.prop('checked')
        }, field.closest('td').prev().find('span'))
            .success(function (response) {
                if (response !== null) {
                    if (typeof response.duty !== 'undefined') {
                        duty.val(response.duty);
                    }
                    if (typeof response.shipping !== 'undefined') {
                        shipping.val(response.shipping);
                    }
                    if (typeof response.width !== 'undefined') {
                        width.val(response.width);
                    }
                    if (typeof response.height !== 'undefined') {
                        height.val(response.height);
                    }
                    if (typeof response.depth !== 'undefined') {
                        depth.val(response.depth);
                    }
                    if (typeof response.show_widget !== 'undefined') {
                        showWidget.prop('checked', !!response.show_widget);
                    }
                }
            });
    };

    var deleteExcludedProducts = function () {
        if (confirm('Are you sure?')) {
            ajaxRequest({'method': 'deleteExcludedProducts'}, $(this));
        }
    };

    var disableVelocityProducts = function () {
        if (confirm('Are you sure?')) {
            ajaxRequest({'method': 'disableVelocityProducts'}, $(this));
        }
    };

    var showEmpty = function () {
        if ($fileUpload.find('table tbody tr').length == 0) {
            $fileUpload.find('table tbody').html('<tr id="no-files"><td colspan="3" class="text-center">No files found.</td></tr>');
        } else {
            $fileUpload.find('#no-files').remove();
        }
        $previewTable.api().ajax.reload();
    };

    var getUrlVars = function () {
        var vars = [], hash;
        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        for (var i = 0; i < hashes.length; i++) {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }
        return vars;
    };

    var getUrlVar = function (name) {
        return getUrlVars()[name];
    };

    var ajaxRequest = function (data, button) {
        if (!button.find('i').hasClass('icon-spinner')) {
            button.find('i').data('iconclass', button.find('i').attr('class'));
        }

        return $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: data,
            beforeSend: function () {
                button.find('i').removeClass(button.find('i').data('iconclass')).addClass('icon-spinner');
            },
            complete: function () {
                button.find('i').removeClass('icon-spinner').addClass(button.find('i').data('iconclass'));
            },
            success: function (response) {
                if (response !== null && typeof response.status !== 'undefined' && typeof response.message !== 'undefined') {
                    showAjaxRequestMessage(response.status, response.message);
                }
            }
        });
    };

    var showAjaxRequestMessage = function (status, message) {
        $alert.removeClass('alert-success');
        $alert.removeClass('alert-danger');
        $alert.removeClass('alert-warning');
        $alert.find('.alert-message').text(message);
        $alert.addClass('alert-' + status).fadeIn('slow');
        $alert.find('.alert-close').focus();
    };

    init();
});
