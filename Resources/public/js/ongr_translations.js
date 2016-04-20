/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$(document).ready(function(){
    $('.translation-message').on('click', function(event){
        event.preventDefault();
        var locale = $(this).attr('locale');
        var message = $(this).text().trim();
        var key = $(this).attr('key');
        messageEdit(event, $(this), key, message, locale);
    });

    $('.remove-button').on('click', function(event){
        event.preventDefault();
        var $li = $(this).parent();
        var $form = $('#translationAddTagForm');
        var tagName = $(this).siblings('span').text();
        var requestData = {
            id : $form.find('#translationId').val(),
            name : $form.find('#translationActionName').val(),
            properties : {name : tagName},
            findBy : {name : tagName}
        };
        requestData = JSON.stringify(requestData);
        $.ajax({
            url : $(this).attr('url'),
            type : 'POST',
            data : requestData,
            success : function() {
                $li.remove();
                var $successElement = $('.alert-success');
                $successElement.removeClass('hidden');
                $successElement.text('Tag successfully removed');
            },
            error : function() {
                var $successElement = $('.alert-danger');
                $successElement.removeClass('hidden');
                $successElement.text('Could not remove the Tag');
            }
        });
    });
});

function messageUpdate(el, message, locale)
{
    var url = $('.urls').attr('edit-url');
    var id = jQuery(el).closest('.pointer').attr('id');
    var requestData = {
        id: id,
        name: 'messages',
        properties: {
            message: $(el).parent().siblings('input').val(),
            locale: locale,
            status: 'dirty'
        },
        findBy: {'locale': locale}
    };
    requestData = JSON.stringify(requestData);
    $.ajax({
        url : url,
        type : 'POST',
        data : requestData,
        success : function(data) {
            jQuery(el).addClass('btn-success');
        }
    });
}

function messageEdit(event, element, key, message, locale)
{
    event.preventDefault();
    element = jQuery(element);
    var display = message;
    if (message == 'Empty field') {
        display = '';
    }
    var domain = element.parent().siblings('.domain-holder').text().trim();
    var content = '<div class="ng-isolate-scope inline-edit active" locale="'+locale+'">'+
        '<div class="input-group input-group-sm">'+
        '<span class="input-group-btn">'+
        '<button class="btn btn-default update" onclick="messageUpdate(this, \''+message+'\', \''+locale+'\')">'+
        '<i class="glyphicon glyphicon-ok"></i>'+
        '</button>'+
        '<button class="btn btn-default" onclick="messageCancel(this, \''+key+'\',\''+message+'\', \''+locale+'\')">'+
        '<i class="glyphicon glyphicon-remove" ></i>'+
        '</button>'+
        '<button class="btn btn-default" onclick="messageHistory(\''+key+'\',\''+locale+'\',\''+domain+'\')" >'+
        '<i class="glyphicon glyphicon-header"></i>'+
        '</button>'+
        '</span>'+
        '<input class="form-control" type="text" value="'+display+'">'+
        '</div>'+
        '</div>';
    element.parent().html(content);
}

function messageCancel(element, key, message, locale)
{
    var $el = jQuery(element);
    if ($el.siblings('.update').hasClass('btn-success')) {
        message = $el.parent().siblings('input').val();
        $el.closest('td').removeClass('bg-danger');
    }
    var content = '<a class="translation-message" onclick="messageEdit(event, this, \''+key+'\', \''+message+'\',\''+locale+'\');" ' +
        'locale="'+locale+'">'+message+'</a>';
    $el.closest('td').html(content);
}

function messageHistory(key, locale, domain)
{
    var requestData = JSON.stringify({key: key, domain: domain, locale: locale});
    var url = $('.urls').attr('history-url');
    //alert(JSON.stringify(requestData));
    $.ajax({
        url: url,
        type: "POST",
        data: requestData,
        success: function (data) {
            var content = '<tr><td>Message</td><td>Created at</td></tr>';
            for (var i in data) {
                var date = data[i].created_at.substring(0, data[i].created_at.length-5);
                date = date.replace('T', ' ');
                content = content + '<tr><td>' + data[i].message + '</td><td>'+date + '</td></tr>';
            }
            $('#history-modal-content').html(content);
            $('#historyModal').modal();
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {

        }
    });
}
