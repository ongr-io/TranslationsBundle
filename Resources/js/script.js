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
                    return getColumnDefinitions(data);
                }
            }
        ]

        //     {
        //         "targets": 1,
        //         "render": function ( data, type, row ) {
        //             if (row['type'] == 'bool') {
        //                 var label = $('<label/>').addClass('boolean-property btn btn-default')
        //                     .addClass('boolean-property-' + row['id']).attr('data-name', row['name']);
        //                 var on = label.clone().html('ON').attr('data-element', 'boolean-property-' + row['id'])
        //                     .attr('data-value', 1);
        //                 var off = label.clone().html('OFF').attr('data-element', 'boolean-property-' + row['id'])
        //                     .attr('data-value', 0);
        //
        //                 if (row['value'] == true) {
        //                     on.addClass('btn-primary');
        //                 } else {
        //                     off.addClass('btn-primary');
        //                 }
        //
        //                 var cell = $('<div/>').addClass('btn-group btn-group-sm').append(on, off);
        //                 return cell.prop('outerHTML');
        //
        //             } else {
        //                 return data;
        //             }
        //         }
        //     },
        //     {
        //         "targets": 3,
        //         "orderable": false,
        //     },
        //     {
        //         "targets": 4,
        //         "data": null,
        //         "orderable": false,
        //         "render": function ( data, type, row ) {
        //             return '<a class="edit btn btn-primary btn-xs" data-toggle="modal" data-target="#setting-edit">Edit</a>&nbsp;<a class="delete delete-setting btn btn-danger btn-xs" data-name="'+row['name']+'">Delete</a>'
        //         }
        //     } ]
    } );

    function getColumnData() {
        var data = [
            { data: 'domain' },
            { data: 'key' }
        ];

        $.each(locales, function(key, locale) {
            data.push({data: 'messages.'+locale+'.message'});
        });

        return data;
    }

    function getColumnDefinitions(translation) {
        var message = '<span class="translation-message" onclick="toggleMessage(\''+translation+'\')">'+translation+'</span>';

        return message;
    }
} );

function toggleMessage(message) {
    alert(message);
}
