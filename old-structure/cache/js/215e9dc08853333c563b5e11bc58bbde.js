
jQuery(function($){$('#sCompleted').change(function(){$.post('/checklists/store-session/',{_nonce:$('#_store_session').val(),keys:['checklists','completed'],value:$(this).val()},function(response){if(response.success)
$('.dt:first').dataTable().fnDraw();});})});var sparrow=function(context){$('input[tmpval],textarea[tmpval]',context).each(function(){$(this).focus(function(){if($(this).val()==$(this).attr('tmpval'))
$(this).val('').removeClass('tmpval');}).blur(function(){var value=$(this).val(),tmpValue=$(this).attr('tmpval');if(0==value.length||value==tmpValue)
$(this).val(tmpValue).addClass('tmpval');});if(!$(this).val().length)
$(this).val($(this).attr('tmpval')).addClass('tmpval');});var tables=$('table[ajax],table.dt',context);if(tables.length)
head.js('/resources/js_single/?f=jquery.datatables',function(){tables.addClass('dt').each(function(){var aPerPage=$(this).attr('perPage').split(','),opts='',ths=$(this).find('th').append('<img src="/images/trans.gif" width="9" height="8" />'),sorting=new Array(),columns=new Array(),s='',c='',a=$(this).attr('ajax');for(var i in aPerPage){opts+='<option value="'+aPerPage[i]+'">'+aPerPage[i]+'</option>';}
if(ths.length){for(var i=0;i<ths.length;i++){if(s=$(ths[i]).attr('sort')){var direction=(-1==s.search('desc'))?'asc':'desc';sorting[s.replace(' '+direction,'')-1]=[i,direction];}
if(c=$(ths[i]).attr('column')){columns.push({'sType':c});}else{columns.push(null);}}}else{sorting=[[0,'asc']];}
var settings={bAutoWidth:false,iDisplayLength:parseInt(aPerPage[0]),oLanguage:{sLengthMenu:'<select>'+opts+'</select>',sInfo:"_START_ - _END_ of _TOTAL_"},aaSorting:sorting,aoColumns:columns,fnDrawCallback:function(){sparrow($(this).find('tr:last').addClass('last').end());},sDom:'<"top"lfr>t<"bottom"pi>'};if(a)
settings.bProcessing=1,settings.bServerSide=1,settings.sAjaxSource=a;$(this).dataTable(settings);});});var dialogs=$('a[rel=dialog]',context);if(dialogs.length)
head.js('/resources/js_single/?f=jquery.boxy',function(){dialogs.click(function(e){e.preventDefault();var dialogData=$(this).attr('href').split('#'),content=$('#'+dialogData[1]),settings={title:$(this).attr('title'),behaviours:sparrow};if(content.length&&'0'!=$(this).attr('cache')){new Boxy(content,settings);}else{if(content.length)
content.remove();$('body').append('<div id="'+dialogData[1]+'" class="dialog" />');content=$('#'+dialogData[1]);content.load(dialogData[0],function(){new Boxy(content,settings);});}});});$('a[ajax]',context).click(function(e){e.preventDefault();var confirmQuestion=$(this).attr('confirm');if(confirmQuestion&&!confirm(confirmQuestion))
return
$.get($(this).attr('href'),ajaxResponse,'json');}).removeAttr('ajax');var RTEs=$('textarea[rte]',context);if(RTEs.length)
head.js('/ckeditor/ckeditor.js','/ckeditor/adapters/jquery.js',function(){RTEs.ckeditor({autoGrow_minHeight:100,resize_minHeight:100,height:100,toolbar:[['Bold','Italic','Underline'],['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],['NumberedList','BulletedList','Table'],['Format'],['Link','Unlink'],['Source']]});});var ajaxForms=$('form[ajax]',context);if(ajaxForms.length)
head.js('/resources/js_single/?f=jquery.form',function(){ajaxForms.ajaxForm({dataType:'json',success:ajaxResponse});});}
$.fn.sparrow=function(context){sparrow($(this));}
head.ready(function(){sparrow($('body'));});function ajaxResponse(response){if(response['success']){if(response['refresh']){window.location=window.location;}else if('object'==typeof(response['jquery'])){head.js('/resources/js_single/?f=jquery.php',function(){php(response['jquery']);});}}else{if(response['error'])
alert(response['error']);}}(function($){$.fn.extend({notify:function(options){var settings=$.extend({type:'sticky',fade:5000,speed:500,onDemandButtonHeight:35},options);return this.each(function(){var wrapper=$(this);if(0!=settings.fade)
setTimeout(function(){wrapper.fadeOut(500,function(){$(this).remove();});},settings.fade);var ondemandBtn=$('.ondemand-button');var dh=-35;var w=wrapper.outerWidth()-ondemandBtn.outerWidth();ondemandBtn.css('left',w).css('margin-top',dh+"px");var h=-wrapper.outerHeight();wrapper.addClass(settings.type).css('margin-top',h).show();if(settings.type!='ondemand'){wrapper.stop(true,false).animate({marginTop:0},settings.speed);}
else{ondemandBtn.stop(true,false).animate({marginTop:0},settings.speed);}
var closeBtn=$('.close',wrapper);closeBtn.click(function(){if(settings.type=='ondemand'){wrapper.stop(true,false).animate({marginTop:h},settings.speed,function(){wrapper.hide();ondemandBtn.stop(true,false).animate({marginTop:0},settings.speed);});}
else{wrapper.stop(true,false).animate({marginTop:h},settings.speed,function(){wrapper.hide();});}});if(settings.type=='floated'){$(document).scroll(function(e){wrapper.stop(true,false).animate({top:$(document).scrollTop()},settings.speed);}).resize(function(e){c
wrapper.stop(true,false).animate({top:$(document).scrollTop()},settings.speed);});}
else if(settings.type=='ondemand'){ondemandBtn.click(function(){$(this).animate({marginTop:dh},settings.speed,function(){wrapper.show().animate({marginTop:0},settings.speed,function(){});})});}});}});})(jQuery);jQuery(function($){$('.notification').notify();$('body').on('click','a[href^=#]',function(e){e.preventDefault();});$('#aTicket').click(function(){var a=$(this);if(a.hasClass('loaded')){new Boxy($('#dTicketPopup'),{title:a.attr('title')});return;}
head.js('/resources/js_single/?f=jquery.boxy','/resources/js_single/?f=jquery.form',function(){a.addClass('loaded');new Boxy($('#dTicketPopup'),{title:a.attr('title')});$('#fCreateTicket').addClass('ajax').ajaxForm({dataType:'json',beforeSubmit:function(){var tTicketSummary=$('#tTicketSummary'),summary=tTicketSummary.val(),taTicket=$('#taTicket'),message=taTicket.val();if(!summary.length||summary==tTicketSummary.attr('tmpval')){alert(tTicketSummary.attr('error'));return false;}
if(!message.length||message==taTicket.attr('tmpval')){alert(taTicket.attr('error'));return false;}
return true;},success:ajaxResponse});});});$('#bCreateTicket').click(function(){$('#fCreateTicket').submit();});$('#dTicketPopup .expander').click(function(){if($(this).hasClass('open')){$(this).removeClass('open').addClass('close');$('#'+$(this).attr('rel')).show();}else{$(this).removeClass('close').addClass('open');$('#'+$(this).attr('rel')).hide();}});$('body').on('click','.boxy-footer .button[rel]',function(){$('#'+$(this).attr('rel')).submit();});});