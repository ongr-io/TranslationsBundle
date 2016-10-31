Array.prototype.diff=function(a){return this.filter(function(t){return a.indexOf(t)<0})},$(document).ready(function(){function a(){var a=[{data:"domain"},{data:"key"}];return $.each(i,function(t,n){a.push({data:"messages."+n+".message"})}),a.push({}),a}function t(a,t,n){a.html(""),"input"==n?(t="[No message]"==t?"":t,a.append('<input class="translation-input" value="'+t+'">'),r=t,a.find("input").focus()):(t=""==t?"[No message]":r,a.append('<span class="translation-message">'+t+"</span>"))}function n(a){$("#tags-container .checkbox").html(""),l.forEach(function(t){$.inArray(t,a)>-1?e(t,!0):e(t,!1)})}function e(a,t){var n="";t&&(n='checked="checked"');var e='<label class="tag-choice"><input type="checkbox" '+n+' name="tags[]" value="'+a+'">'+a+"</label>";$("#tags-container .checkbox").append(e)}function s(a,t){var n="",e=null!=t.status&&"fresh"==t.status?"label-success":"label-danger",s="[No message]"==t.message?"":t.message;return n+='<label class="col-sm-2 control-label" for="translation_'+a+'">'+a+'</label><div class="col-sm-10"><input type="text" name="messages['+a+']" value="'+s+'" class="form-control"/></div>',n+='<label class="col-sm-2 control-label">status</label><div class="col-sm-10 translation-form-div"><span class="label '+e+' translation-message-status">'+t.status+"</span></div>"}var i,o,r,l;$.ajax({url:Routing.generate("ongr_translations_list_get_initial_data"),success:function(a){i=a.locales,l=a.tags;for(var t=[],n=2;n<a.locales.length+2;n++)t.push(n);o=t},async:!1});var c=$("#translations").DataTable({ajax:{url:Routing.generate("ongr_translations_list_get_translations"),data:function(){return{tags:$("#tag-select").val()}},dataSrc:""},stateSave:!0,columns:a(),columnDefs:[{targets:o,orderable:!1,render:function(a,t,n,e){return a=null==a?"[No message]":a,'<span class="translation-message">'+a+"</span>"}},{targets:-1,render:function(a,t,n){return'<a class="edit btn btn-primary btn-xs" data-toggle="modal" data-target="#setting-edit">Edit</a>&nbsp;<a class="delete delete-setting btn btn-danger btn-xs" data-name="'+n.name+'">Delete</a>'}}]}),d=$('<select multiple name="tags[]"/>').attr({id:"tag-select"}),u="";$.each(l,function(a,t){u+='<option value="'+t+'">'+t+"</option>>"}),d.append(u),$("#translations tbody").on("click","span.translation-message",function(){t($(this).parent(),$(this).text(),"input")}),$("#translations tbody").on("keyup","input.translation-input",function(a){if([13,38,40].indexOf(a.keyCode)!=-1){var n=c.row($(this).parents("tr")).data(),e=c.column($(this).parents("td")).index(),s=$(c.column($(this).parents("td")).header()).html(),i=$(this).val(),o=this;r!=i&&($.ajax({url:Routing.generate("ongr_translations_api_edit_message"),data:JSON.stringify({message:i,id:n.id,locale:s}),method:"post"}),r=i);var l;switch(a.keyCode){case 13:t($(o).parent(),i,"span");break;case 38:l=$(o).parents("tr").prev().find("td")[e],t($(o).parent(),i,"span"),t($(l),$(l).find("span").text(),"input");break;case 40:l=$(o).parents("tr").next().find("td")[e],t($(o).parent(),i,"span"),t($(l),$(l).find("span").text(),"input")}}else 27==a.keyCode&&t($(this).parent(),$(this).val(),"span")}),$("#translations_filter").append('<label class="tags-label">Tags: </label>'),$("#translations_filter").append(d.prop("outerHTML")),$("#tag-select").multiselect(),$("#translations tbody").on("blur","input.translation-input",function(a){t($(this).parent(),$(this).val(),"span")}),$("#translations tbody").on("click","a.edit",function(){var a=c.row($(this).parents("tr")).data();$("#translation-id").val(a.id),$("#translation-name-input").val(a.key),$("#translation-domain-input").val(a.domain),$("#translation-created-at-input").val(a.createdAt),$("#translation-updated-at-input").val(a.updatedAt),$("#messages-container").html(""),n(a.tags);var t="",e=[];$.each(a.messages,function(a,n){e.push(a),t+=s(a,n)});var o=i.diff(e);o.length>0&&$.each(o,function(a,n){t+=s(n,{message:"",status:"fresh"})}),$("#messages-container").append(t),$("#translation-form-modal").modal()}),$("#add-new-tag-show-form").on("click",function(){$(this).hide(),$("#add-new-tag-container").show(),$("#add-new-tag-input").focus()}),$("#select-all-tags").on("click",function(){$('#tags-container .checkbox input[type="checkbox"]').prop("checked",!0)}),$("#add-new-tag").on("click",function(){var a=$("#add-new-tag-input"),t=a.val();e(t),l.push(t),a.val("")}),$("#tag-select").change(function(){c.ajax.reload()}),$("#translation-form-submit").on("click",function(a){a.preventDefault();var t=$("#translation-id").val(),n=$.deparam($("#translation-form").serialize());n=JSON.stringify(n),$.ajax({url:Routing.generate("ongr_translations_api_edit",{id:t}),method:"post",data:n,contentType:"application/json; charset=utf-8",dataType:"json",success:function(a){0==a.error?(c.ajax.reload(),$("#translation-form-modal").modal("hide")):($("#translation-form-error-message").html(a.message),$("#translation-form-error").show())}})})});