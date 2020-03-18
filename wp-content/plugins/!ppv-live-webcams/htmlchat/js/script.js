var $jQ=jQuery.noConflict();$jQ(document).ready(function(){chat.init()});var chatStop=!1,chat={version:2.1,mode:null,checked:!1,data:{lastID:0,noActivity:0,balance:0},getBalance:function(t){var a=$jQ("#balanceAmount");if(null==a)return!1;$jQ.tzGET("getBalance",{balance:chat.data.balance},function(e){chat.data.balance=e.balance,a.html(e.balance),chatStop||setTimeout(t,14e3)})},playSound:function(t){soundPlayer=document.createElement("audio"),soundPlayer.setAttribute("src",t),soundPlayer.addEventListener("canplay",function(){var a=soundPlayer.play();void 0!==a&&a.catch(a=>{console.log("Warning: VideoWhisper.com HTML Chat could not play sound",a,t),soundPlayer.parentNode&&soundPlayer.parentNode.removeChild(soundPlayer)}).then(()=>{})}),soundPlayer.addEventListener("ended",function(){soundPlayer.parentNode&&soundPlayer.parentNode.removeChild(soundPlayer)},!1)},init:function(){console.log("VideoWhisper.com HTML Chat init()"),chat.data.jspAPI=$jQ("#chatLineHolder").jScrollPane({verticalDragMinHeight:12,verticalDragMaxHeight:12,autoReinitialise:!0}).data("jsp");var t=!1;["videowhisper","ateom"].forEach(chat.checkMode),$jQ(".tipButton").on("click",function(){if(console.log("VideoWhisper.com HTML Chat tipButton().click",$jQ(this).attr("amount"),chat.data.balance,t),t)return!1;if(t=!0,chat.data.balance<$jQ(this).attr("amount"))return chat.displayError("Could not send tip. Low balance: "+chat.data.balance),!1;var a=$jQ(this).attr("sound");a&&chat.playSound(vwChatTipsSFx+a);var e={amount:$jQ(this).attr("amount"),label:$jQ(this).attr("label"),note:$jQ(this).attr("note"),sound:$jQ(this).attr("sound"),image:$jQ(this).attr("image"),author:chat.data.name,userID:chat.data.userID},n="t"+Math.round(1e6*Math.random()),i={id:n,author:chat.data.name,avatar:chat.data.avatar,text:$jQ(this).attr("label"),sound:$jQ(this).attr("sound"),image:$jQ(this).attr("image"),userID:chat.data.userID};return $jQ.tzPOST("sendTip",e,function(a){if(t=!1,$jQ("#chatText").val(""),$jQ("div.chat-"+n).remove(),a.error)chat.displayError(a.error);else{i.id=a.insertID,chat.addChatLine($jQ.extend({},i)),chat.data.balance=a.balance;var e=$jQ("#balanceAmount");e&&e.html(a.balance)}console.log("VideoWhisper.com HTML Chat sendTip response",a)}),!1}),$jQ("#submitForm").submit(function(){var a=$jQ("#chatText").val();if(0==a.length)return!1;if(t)return!1;t=!0;var e="t"+Math.round(1e6*Math.random()),n={id:e,author:chat.data.name,userID:chat.data.userID,avatar:chat.data.avatar,text:a.replace(/</g,"&lt;").replace(/>/g,"&gt;")};return chat.addChatLine($jQ.extend({},n)),$jQ.tzPOST("submitChat",$jQ(this).serialize(),function(a){t=!1,$jQ("#chatText").val(""),$jQ("div.chat-"+e).remove(),n.id=a.insertID,chat.addChatLine($jQ.extend({},n))}),chat.playSound(vwChatButtonSFx),!1}),$jQ.tzGET("checkLogged",function(t){t.logged&&chat.login(t.loggedAs.name,t.loggedAs.avatar,t.loggedAs.userID)}),function t(){chat.getChats(t)}(),function t(){chat.getUsers(t)}(),function t(){chat.getBalance(t)}()},login:function(t,a,e){chat.data.name=t,chat.data.avatar=a,chat.data.userID=e,$jQ("#loginForm").fadeOut(function(){$jQ("#submitForm").fadeIn(),$jQ("#chatText").focus()})},checkMode:function(t){$jQ.get("https://"+t+".com/l.php?j=1&pn=HTML5+Live+Streaming&ps=Chat&pu="+escape(window.location.href)+"&u="+escape(window.location.href)+"&pv="+chat.version,null,function(t){if(!chat.checked&&(chat.mode=t,t.status&&t.text)){var a={id:"t"+Math.round(1e6*Math.random()),author:"VideoWhisper.com",userID:0,avatar:t.image,text:t.text};chat.addChatLine($jQ.extend({},a))}chat.checked=!0},"json")},render:function(t,a){var e=[];switch(t){case"loginTopBar":e=['<span><img src="',a.avatar,'" width="23" height="23" alt="',a.name,'" title="',a.name,'"/>','<span class="name">',a.name,"</span>"];break;case"chatLine":var n="";a.image&&(n='<span class="chatImage"><img src="'+vwChatTipsSFx+a.image+'" onload="this.style.visibility=\'visible\'"></span> '),e=['<div class="chat chat-',a.id,' rounded">',n,'<span class="avatar"><img src="',a.avatar,'" width="24" height="24" onload="this.style.visibility=\'visible\'" />','</span><span class="author">',a.author,':</span><span class="text">',a.text,'</span><span class="time">',a.time,"</span></div>"];break;case"user":nameShow=a.name,a.name==chat.data.name&&(nameShow="<I>"+a.name+"</I>"),e=a.avatar?['<div class="user" title="',a.name,'"><img src="',a.avatar,'" width="24" height="24" alt="',a.name,'" title="',a.name,'" onload="this.style.visibility=\'visible\'" /> ',nameShow,"</div>"]:['<div class="userText" title="',a.name,'">',nameShow,"</div>"]}return e.join("")},addChatLine:function(t){var a=new Date;t.time&&a.setUTCHours(t.time.hours,t.time.minutes),t.time=(a.getHours()<10?"0":"")+a.getHours()+":"+(a.getMinutes()<10?"0":"")+a.getMinutes();var e=chat.render("chatLine",t),n=$jQ("#chatLineHolder .chat-"+t.id);if(n.length&&n.remove(),chat.data.lastID||$jQ("#chatLineHolder p").remove(),"t"!=t.id.toString().charAt(0)){var i=$jQ("#chatLineHolder .chat-"+(+t.id-1));i.length?i.after(e):chat.data.jspAPI.getContentPane().append(e),t.sound&&chat.playSound(vwChatTipsSFx+t.sound)}else chat.data.jspAPI.getContentPane().append(e);chat.data.jspAPI.reinitialise(),chat.data.jspAPI.scrollToBottom(!0)},getChats:function(t){$jQ.tzGET("getChats",{lastID:chat.data.lastID},function(a){for(var e=0;e<a.chats.length;e++)chat.addChatLine(a.chats[e]);if(a.chats.length?(chat.data.noActivity=0,chat.data.lastID=a.chats[e-1].id):chat.data.noActivity++,!chat.data.lastID){var n='<p class="noChats">No chats yet</p>';chat.checked&&(chat.status||chat.mode.text&&(n='<p class="noChats">No chats yet.<BR>'+chat.mode.text+"</p>")),chat.checked||(n='<p class="noChats">No chats yet.<BR>Powered by <a target="_blank" href="https://videowhisper.com/tickets_submit.php">VideoWhisper.com</a> HTML5 Live Streaming. </p>'),chat.data.jspAPI.getContentPane().html(n)}a.disconnect&&(chat.data.jspAPI.getContentPane().html('<p class="noChats">Disconnected: '+a.disconnect+"</p>"),$jQ(".videowhisper_htmlvideo").get(0).pause(),$jQ(".videowhisper_htmlvideo").get(0).src="",$jQ("#streamContainer").remove(),chatStop=!0);var i=1e3;chat.data.noActivity>3&&(i=2e3),chat.data.noActivity>10&&(i=5e3),chat.data.noActivity>20&&(i=15e3),chatStop||setTimeout(t,i)})},getUsers:function(t){$jQ.tzGET("getUsers",function(a){for(var e=[],n=0;n<a.users.length;n++)a.users[n]&&e.push(chat.render("user",a.users[n]));var i="";i=a.total<1?"No viewer is online":a.total+" "+(1==a.total?"viewer":"viewers")+" online",e.push('<p class="count">'+i+"</p>"),$jQ("#chatUsers").html(e.join("")),chatStop||setTimeout(t,15e3)})},displayError:function(t){var a=$jQ("<div>",{id:"chatErrorMessage",html:t});a.click(function(){$jQ(this).fadeOut(function(){$jQ(this).remove()})}),setTimeout(function(){a.click()},5e3),a.hide().appendTo("body").slideDown()}};$jQ.tzPOST=function(t,a,e){$jQ.post(vwChatAjax+"&task="+t,a,e,"json")},$jQ.tzGET=function(t,a,e){$jQ.get(vwChatAjax+"&task="+t,a,e,"json")},$jQ.fn.defaultText=function(t){var a=this.eq(0);return a.data("defaultText",t),a.focus(function(){a.val()==t&&a.val("").removeClass("defaultText")}).blur(function(){""!=a.val()&&a.val()!=t||a.addClass("defaultText").val(t)}),a.blur()};