$(document).ready(function(){function a(){var a=[{data:"domain"},{data:"key"}];return $.each(n,function(t,n){a.push({data:"messages."+n+".message"})}),a.push({}),a}function t(a,t,n){a.html(""),"input"==n?(t="[No message]"==t?"":t,a.append('<input class="translation-input" value="'+t+'">'),e=t,a.find("input").focus()):(t=""==t?"[No message]":e,a.append('<span class="translation-message">'+t+"</span>"))}var n,s,e;$.ajax({url:Routing.generate("ongr_translations_list_get_locales"),success:function(a){n=a;for(var t=[],e=2;e<a.length+2;e++)t.push(e);s=t},async:!1});var i=$("#translations").DataTable({ajax:{url:Routing.generate("ongr_translations_list_get_translations"),dataSrc:""},stateSave:!0,columns:a(),columnDefs:[{targets:s,orderable:!1,render:function(a,t,n,s){return'<span class="translation-message">'+a+"</span>"}},{targets:-1,render:function(a,t,n){return'<a class="edit btn btn-primary btn-xs" data-toggle="modal" data-target="#setting-edit">Edit</a>&nbsp;<a class="delete delete-setting btn btn-danger btn-xs" data-name="'+n.name+'">Delete</a>'}}]});$("#translations tbody").on("click","span.translation-message",function(){t($(this).parent(),$(this).text(),"input")}),$("#translations tbody").on("keyup","input.translation-input",function(a){if([13,38,40].indexOf(a.keyCode)!=-1){var n=i.row($(this).parents("tr")).data(),s=i.column($(this).parents("td")).index(),r=$(i.column($(this).parents("td")).header()).html(),l=$(this).val(),o=this;e!=l&&($.ajax({url:Routing.generate("ongr_translations_api_edit_message"),data:JSON.stringify({message:l,id:n.id,locale:r}),method:"post"}),e=l);var d;switch(a.keyCode){case 13:t($(o).parent(),l,"span");break;case 38:d=$(o).parents("tr").prev().find("td")[s],t($(o).parent(),l,"span"),t($(d),$(d).find("span").text(),"input");break;case 40:d=$(o).parents("tr").next().find("td")[s],t($(o).parent(),l,"span"),t($(d),$(d).find("span").text(),"input")}}else 27==a.keyCode&&t($(this).parent(),$(this).val(),"span")}),$("#translations tbody").on("blur","input.translation-input",function(a){t($(this).parent(),$(this).val(),"span")}),$("#translations tbody").on("click","a.edit",function(){var a=i.row($(this).parents("tr")).data();$("#translation-name-input").val(a.key),$("#translation-domain-input").val(a.domain),$("#translation-created-at-input").val(a.createdAt),$("#translation-updated-at-input").val(a.updatedAt),$("#messages-container").html("");var t="";$.each(a.messages,function(a,n){var s="fresh"==n.status?"label-success":"label-danger",e="[No message]"==n.message?"":n.message;t+='<label class="col-sm-2 control-label" for="translation_'+a+'">'+a+'</label><div class="col-sm-10"><input type="text" name="translation[messages]['+a+']" value="'+e+'" class="form-control"/></div>',t+='<label class="col-sm-2 control-label">status</label><div class="col-sm-10 translation-form-div"><span class="label '+s+' translation-message-status">'+n.status+"</span></div>"}),$("#messages-container").append(t),$("#translation-form-modal").modal()})});