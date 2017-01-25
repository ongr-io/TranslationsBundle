Array.prototype.diff = function(e) {
    return this.filter(function(i) {return e.indexOf(i) < 0;});
};

$(document).ready(function() {
    var translationColumns = [];
    var currentMessageValue;

    for (var i = 2; i < (locales.length + 2); i++) {
        translationColumns.push(i);
    }

    var tagSelect = $('<select multiple name="tags[]"/>').attr({'id': 'tag-select'});
    var domainSelect = $('<select multiple name="domains[]"/>').attr({'id': 'domain-select'});

    var translationsTable = $('#translations').DataTable( {
        ajax: {
            url: Routing.generate('ongr_translations_list_get_translations'),
            data: function() {
                return {tags: $('#tag-select').val(), domains: $('#domain-select').val()}
            },
            dataSrc: ''
        },
        stateSave: true,
        scrollX: true,
        columns: getColumnData(),
        columnDefs: [
            {
                "targets": translationColumns,
                "orderable": true,
                "render": function ( data, type, row, meta ) {
                    data = data == null ? '[No message]' : data;
                    var emptyClass = getMessageClass(data);

                    return '<span class="'+emptyClass+' translation-message">'+data+'</span>';
                }
            },
            {
                "targets": -1,
                "orderable": false,
                "render":function ( data, type, row ) {
                    return '<div class="action-containr"><a class="edit btn btn-primary btn-xs" data-toggle="modal" data-target="#setting-edit">Edit</a>&nbsp;<a class="history btn btn-warning btn-xs" data-name="'+row['name']+'">History</a></div>'
                }
            }
        ]
    } );

    tagSelect = addSelectOptions(tagSelect, tags);
    domainSelect = addSelectOptions(domainSelect, domains);

    var tagLabel = $('<label class="tags-label">Tags: </label>');
    var domainLabel = $('<label class="domains-label">Domains: </label>');
    var translationsFilter = $('#translations_filter');

    appendMultiselect(translationsFilter, tagLabel, tagSelect);
    appendMultiselect(translationsFilter, domainLabel, domainSelect);

    translationsFilter.parent().removeClass('col-sm-6').addClass('col-sm-9');
    $('#translations_length').parent().removeClass('col-sm-6').addClass('col-sm-3');
    $('#translations_length').append('<i class="glyphicon glyphicon-question-sign" id="info" onclick="$(\'#info-modal\').modal();">');

    function addSelectOptions(select, items) {
        var options = '';
        $.each(items, function(key, item) {
            options += '<option value="'+item+'">'+item+'</option>>';
        });
        select.append(options);

        return select;
    }

    function appendMultiselect(element, label, select) {
        label.append(select.prop('outerHTML'));
        element.prepend(label.prop('outerHTML'));
        $('#'+select.attr('id')).multiselect();
    }

    function getColumnData() {
        var data = [
            { data: 'domain' },
            { data: 'key' }
        ];

        $.each(locales, function(key, locale) {
            data.push({data: 'messages.'+locale+'.message'});
        });
        data.push({});

        return data;
    }

    function toggleMessage(element, message, action) {
        element.html('');

        if (action == 'input') {
            message = message == '[No message]' ? '' : message;
            element.append('<input class="translation-input" value="'+message+'">');
            currentMessageValue = message;
            element.find('input').focus();
        } else {
            message = message == '' ? '[No message]' : currentMessageValue;
            var emptyClass = getMessageClass(message);
            element.append('<span class="'+emptyClass+' translation-message">'+message+'</span>');
        }
    }

    function reloadTags(select) {
        $('#tags-container .checkbox').html('');
        tags.forEach(function (element) {
            if ($.inArray(element, select) >  -1) {
                appendNewTag(element, true);
            } else {
                appendNewTag(element, false);
            }
        });
    }

    function appendNewTag(element, check) {
        var checked = '';
        if (check) {
            checked = 'checked="checked"';
        }
        var input = '<label class="tag-choice"><input type="checkbox" '+checked+' name="tags[]" value="'+element+'">'+element+'</label>';
        $('#tags-container .checkbox').append(input);
    }

    function addTranslationMessage (locale, message) {
        var result = '';
        var label = message.status != null && message.status == 'fresh' ? 'label-success' : 'label-danger';
        var messageText = message.message == '[No message]' ? '' : message.message;
        result += '<label class="col-sm-2 control-label" for="translation_'+locale+'">'+locale+'</label>'+
            '<div class="col-sm-10">'+
            '<input type="text" name="messages['+locale+']" value="'+messageText+'" class="form-control"/>' +
            '</div>';
        result += '<label class="col-sm-2 control-label"></label>' +
            '<div class="col-sm-10 translation-form-div">Status: ' +
            '<span class="label '+label+' translation-message-status">'+message.status+'</span>' +
            '</div>';

        return result
    }

    function getMessageClass(message) {
        var messageClass = '';

        if (message == '[No message]') {
            messageClass = 'empty-message';
        }
        return messageClass;
    }

    $('#translations tbody').on('click', 'span.translation-message', function() {
        toggleMessage($(this).parent(), $(this).text(), 'input')
    });

    $('#translations tbody').on('keyup', 'input.translation-input', function(e) {
        if ([13, 38, 40].indexOf(e.keyCode) != -1) {
            var data = translationsTable.row( $(this).parents('tr') ).data();
            var column = translationsTable.column( $(this).parents('td') ).index();
            var locale = $(translationsTable.column($(this).parents('td')).header()).html();
            var value = $(this).val();
            var context = this;

            if (currentMessageValue != value) {
                $.ajax({
                    url: Routing.generate('ongr_translations_api_update', {id: data.id}),
                    data: '{"messages": {"'+locale+'": "'+value+'"}}',
                    method: 'put',
                    contentType: "application/json; charset=utf-8",
                    dataType   : "json",
                });
                currentMessageValue = value;
            }

            var nextInput;
            switch (e.keyCode) {
                case 13:
                    toggleMessage($(context).parent(), value, 'span');
                    break;
                case 38:
                    nextInput = $(context).parents('tr').prev().find('td')[column];
                    toggleMessage($(context).parent(), value, 'span');
                    toggleMessage($(nextInput), $(nextInput).find('span').text(), 'input');
                    break;
                case 40:
                    nextInput = $(context).parents('tr').next().find('td')[column];
                    toggleMessage($(context).parent(), value, 'span');
                    toggleMessage($(nextInput), $(nextInput).find('span').text(), 'input');
                    break;
            }
        } else if(e.keyCode == 27) {
            toggleMessage($(this).parent(), $(this).val(), 'span');
        }
    });

    $('#translations tbody').on('blur', 'input.translation-input', function(e) {
        toggleMessage($(this).parent(), $(this).val(), 'span');
    });

    $('#translations tbody').on( 'click', 'a.edit', function () {
        var id = translationsTable.row( $(this).parents('tr') ).data().id;
        $('#translation-id').val(id);
        $.get(Routing.generate('ongr_translations_api_get', {id: id}), function(data) {
            $('#translation-name-input').text(data.key);
            $('#translation-domain-input').text(data.domain);
            $('#translation-description-input').val(data.description);
            $('#messages-container').html('');
            reloadTags(data.tags);
            var messages = '';
            var translationLocales = [];
            $.each(data.messages, function(locale, message){
                translationLocales.push(locale);
                messages += addTranslationMessage(locale, message);
            });

            var unsupportedLocales = locales.diff(translationLocales);

            if (unsupportedLocales.length > 0) {
                $.each(unsupportedLocales, function(i, locale) {
                    messages += addTranslationMessage(locale, {message: '', status: 'fresh'});
                });
            }

            $('#messages-container').append(messages);
        });

        $('#translation-form-modal').modal();
    } );

    $('#translations tbody').on( 'click', '.history', function () {
        var data = translationsTable.row( $(this).parents('tr') ).data();
        var container = $('#history-container');
        $('#history-key').text(data.key);
        container.html('');
        $.get(Routing.generate('ongr_translations_api_history', {id: data.id}), function(historyData) {
            if (historyData instanceof Array && historyData.length < 1) {
                container.append('<h4>No history</h4>');
                return;
            }

            $.each(historyData, function(locale, histories) {
                var localeSection = $('<div class="form-group"></div>');
                var tableDiv = $('<div class="col-sm-10"></div>');
                var table = $('<table class="table"></table>');
                table.append('<tr style="width: 50%"><th>Message</th><th style="width: 50%">Updated at</th></tr>');
                localeSection.append('<label class="col-sm-2 control-label">'+locale+'</label>');

                $.each(histories, function(i, history) {
                    table.append('<tr><td>'+history.message+'</td><td>'+history.updatedAt+'</td></tr>');
                });

                tableDiv.append(table);
                localeSection.append(tableDiv);
                container.append(localeSection);
            })
        });
        $('#history-modal').modal();
    } );

    $('#translation-export').on('click', function(e){
        e.preventDefault();
        $('#export-loading').show();
        $('.export-dialog').hide();
        var table = $('#export-table');
        var header = '<tr><th>Domain</th><th>Key</th><th>Locale</th><th>Message</th></tr>';
        var dirtyTranslations = header;
        var data;
        table.html('');
        $('#export-nothing-to-export-header').hide()
        $('#export-modal').modal();
        translationsTable.ajax.reload(function(){
            data = translationsTable.rows().data()

            $.each(data, function(i, translation) {
                $.each(locales, function(i, locale){
                    if (typeof translation.messages[locale] != 'undefined' && translation.messages[locale].status == 'dirty') {
                        dirtyTranslations += '<tr class="dirty-translaiton-row">' +
                            '<td>'+translation.domain+'</td>' +
                            '<td>'+translation.key+'</td>' +
                            '<td>'+locale+'</td>' +
                            '<td>'+translation.messages[locale].message+'</td></tr>';
                    }
                });
            });

            $('#export-loading').hide();

            if (dirtyTranslations != header) {
                table.append(dirtyTranslations);
            } else {
                $('#export-nothing-to-export-header').show();
            }
        });
    });

    $('#export-submit').on('click', function() {
        $.post(Routing.generate('ongr_translations_api_export'), function (result) {
            if (result.error == true) {
                $('#export-error').show();
            } else {
                $('#export-success').show();
                $('#export-table').html('');
                translationsTable.reload();
            }
        });
    });

    $('#add-new-tag-show-form').on('click', function () {
        $(this).hide();
        $('#add-new-tag-container').show();
        $('#add-new-tag-input').focus();
    });

    $('#select-all-tags').on('click', function(){
        $('#tags-container .checkbox input[type="checkbox"]').prop('checked',true);
    });

    $('#add-new-tag').on('click', function(){
        var input = $('#add-new-tag-input');
        var value = input.val();
        appendNewTag(value, true);
        $('#tag-select').append('<option>'+value+'</option>').multiselect("destroy").multiselect();
        tags.push(value);
        input.val('');
    });

    $('#tag-select').change(function() {
        translationsTable.ajax.reload();
    });

    $('#domain-select').change(function() {
        translationsTable.ajax.reload();
    });

    $('#translation-form-submit').on('click', function (e) {
        e.preventDefault();
        var id = $('#translation-id').val();
        var data = $.deparam($('#translation-form').serialize());
        data = JSON.stringify(data);
        $.ajax({
            url: Routing.generate('ongr_translations_api_update', {id: id}),
            method: 'put',
            data: data,
            contentType: "application/json; charset=utf-8",
            dataType   : "json",
            success: function (response) {
                if (response.error == false) {
                    translationsTable.ajax.reload();
                    $('#translation-form-modal').modal('hide')
                } else {
                    $('#translation-form-error-message').html(response.message);
                    $('#translation-form-error').show();
                }
            }
        });
    });
} );
