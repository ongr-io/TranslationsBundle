$(document).ready(function(){function t(){var t=[{data:"domain"},{data:"key"}];return $.each(n,function(a,n){t.push({data:"messages."+n+".message"})}),t.push({}),t}function a(t,a,n){"input"==n?(t.html(""),a="[No message]"==a?"":a,t.append('<input class="translation-input" value="'+a+'">'),t.find("input").focus()):(t.html(""),a=""==a?"[No message]":a,t.append('<span class="translation-message">'+a+"</span>"))}var n,s;$.ajax({url:Routing.generate("ongr_translations_list_get_locales"),success:function(t){n=t;for(var a=[],e=2;e<t.length+2;e++)a.push(e);s=a},async:!1});var e=$("#translations").DataTable({ajax:{url:Routing.generate("ongr_translations_list_get_translations"),dataSrc:""},stateSave:!0,columns:t(),columnDefs:[{targets:s,orderable:!1,render:function(t,a,n,s){return'<span class="translation-message">'+t+"</span>"}},{targets:-1,render:function(t,a,n){return'<a class="edit btn btn-primary btn-xs" data-toggle="modal" data-target="#setting-edit">Edit</a>&nbsp;<a class="delete delete-setting btn btn-danger btn-xs" data-name="'+n.name+'">Delete</a>'}}]});$("#translations tbody").on("click","span.translation-message",function(){a($(this).parent(),$(this).text(),"input")}),$("#translations tbody").on("keyup","input.translation-input",function(t){if(13==t.keyCode){var n=e.row($(this).parents("tr")).data(),s=$(e.column($(this).parents("td")).header()).html(),i=$(this).val(),r=this;$.ajax({url:Routing.generate("ongr_translations_api_edit_message"),data:JSON.stringify({message:i,id:n.id,locale:s}),method:"post",success:function(){a($(r).parent(),i,"span")}})}else 27==t.keyCode&&a($(this).parent(),$(this).val(),"span")}),$("#translations tbody").on("blur","input.translation-input",function(t){a($(this).parent(),$(this).val(),"span")})});