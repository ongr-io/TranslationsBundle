$(document).ready(function() {
    var locales;
    var translationColumns;

    $.ajax({
        url: Routing.generate('ongr_translations_list_get_locales'),
        success: function(data) {
            locales = data;
            var columnNumbers = [];

            for (var i = 2; i < (data.length + 2); i++) {
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
                    // alert(JSON.stringify(type));
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
        if (action == 'input') {
            element.html('');
            element.append('<input class="translation-input" value="'+message+'">');
            element.find('input').focus();
        } else {
            element.html('');
            element.append('<span class="translation-message">'+message+'</span>');
        }
    }

    $('#translations tbody').on('click', 'span.translation-message', function() {
        toggleMessage($(this).parent(), $(this).text(), 'input')
    });

    $('#translations tbody').on('keyup', 'input.translation-input', function(e) {
        if (e.keyCode == 13) {
            var data = translationsTable.row( $(this).parents('tr') ).data();
            var locale = $(translationsTable.column($(this).parents('td')).header()).html();
            var value = $(this).val();
            var context = this;
            $.ajax({
                url: Routing.generate('ongr_translations_api_edit_message'),
                data: JSON.stringify({message: value, id: data.id, locale: locale}),
                method: 'post',
                async: false,
                success: function() {
                    toggleMessage($(context).parent(), value, 'span');
                }
            });
        } else if(e.keyCode == 27) {
            toggleMessage($(this).parent(), $(this).val(), 'span');
        }
    });

    $('#translations tbody').on('blur', 'input.translation-input', function(e) {
        toggleMessage($(this).parent(), $(this).val(), 'span');
    });
} );
