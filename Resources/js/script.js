$(document).ready(function() {
    var locales;
    var translationColumns;
    var currentMessageValue;
    var tags;

    $.ajax({
        url: Routing.generate('ongr_translations_list_get_initial_data'),
        success: function(data) {
            locales = data.locales;
            tags = data.tags;
            var columnNumbers = [];

            for (var i = 2; i < (data.locales.length + 2); i++) {
                columnNumbers.push(i);
            }

            translationColumns = columnNumbers;
        },
        async: false
    });

    var translationsTable = $('#translations').DataTable( {
        ajax: {
            url: Routing.generate('ongr_translations_list_get_translations'),
            dataSrc: ''
        },
        stateSave: true,
        columns: getColumnData(),
        columnDefs: [
            {
                "targets": translationColumns,
                "orderable": false,
                "render": function ( data, type, full, meta ) {
                    return '<span class="translation-message">'+data+'</span>';
                }
            },
            {
                "targets": -1,
                "render":function ( data, type, row ) {
                    return '<a class="edit btn btn-primary btn-xs" data-toggle="modal" data-target="#setting-edit">Edit</a>&nbsp;<a class="delete delete-setting btn btn-danger btn-xs" data-name="'+row['name']+'">Delete</a>'
                }
            }
        ]
    } );

    var tagSelect = $('<select multiple name="tags[]"/>').attr(
        {
            'id': 'tag-select',
        }
    );

    var tagOptions = '';
    $.each(tags, function(key, tag) {
        tagOptions += '<option value="'+tag+'">'+tag+'</option>>';
    });
    tagSelect.append(tagOptions);

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
            element.append('<span class="translation-message">'+message+'</span>');
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
        var input = '<label class="tag-choice"><input type="checkbox" '+checked+' name="translation[tags][]" value="'+element+'">'+element+'</label>';
        $('#tags-container .checkbox').append(input);
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
                    url: Routing.generate('ongr_translations_api_edit_message'),
                    data: JSON.stringify({message: value, id: data.id, locale: locale}),
                    method: 'post'
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

    $('#translations_filter').append('<label class="tags-label">Tags: </label>');
    $('#translations_filter').append(tagSelect.prop('outerHTML'));
    $('#tag-select').multiselect();

    $('#translations tbody').on('blur', 'input.translation-input', function(e) {
        toggleMessage($(this).parent(), $(this).val(), 'span');
    });

    $('#translations tbody').on( 'click', 'a.edit', function () {
        var data = translationsTable.row( $(this).parents('tr') ).data();
        $('#translation-id').val(data.id);
        $('#translation-name-input').val(data.key);
        $('#translation-domain-input').val(data.domain);
        $('#translation-created-at-input').val(data.createdAt);
        $('#translation-updated-at-input').val(data.updatedAt);
        $('#messages-container').html('');
        reloadTags(data.tags);
        var messages = '';
        $.each(data.messages, function(locale, message){
            var label = message.status == 'fresh' ? 'label-success' : 'label-danger';
            var messageText = message.message == '[No message]' ? '' : message.message;
            messages += '<label class="col-sm-2 control-label" for="translation_'+locale+'">'+locale+'</label>'+
                '<div class="col-sm-10">'+
                    '<input type="text" name="translation[messages]['+locale+']" value="'+messageText+'" class="form-control"/>' +
                '</div>';
            messages += '<label class="col-sm-2 control-label">status</label>' +
                '<div class="col-sm-10 translation-form-div">' +
                    '<span class="label '+label+' translation-message-status">'+message.status+'</span>' +
                '</div>';
        });
        $('#messages-container').append(messages);

        $('#translation-form-modal').modal();
    } );

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
        appendNewTag(value);
        tags.push(value);
        input.val('');
    });
} );
