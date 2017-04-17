/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$.ajax({
    url: '/ajax/example.html',             // указываем URL и
    dataType : "json",                     // тип загружаемых данных
    success: function (data, textStatus) { // вешаем свой обработчик на функцию success
        $.each(data, function(i, val) {    // обрабатываем полученные данные
            /* ... */
        });
    } 
});

function ajaxLoad(obj,url,defMessage,post,callback){
  var ajaxObj;
  if (defMessage) document.getElementById(obj).innerHTML=defMessage;
  if(window.XMLHttpRequest){
      ajaxObj = new XMLHttpRequest();
  } else if(window.ActiveXObject){
      ajaxObj = new ActiveXObject("Microsoft.XMLHTTP");
  } else {
      return;
  }
  ajaxObj.open ((post?'POST':'GET'), url);
  if (post&&ajaxObj.setRequestHeader)
      ajaxObj.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=windows-1251;");
  ajaxObj.setRequestHeader("Referer", location.href); // нужен в Donate

  ajaxObj.onreadystatechange = ajaxCallBack(obj,ajaxObj,(callback?callback:null));
  ajaxObj.send(post);
  return false;
  }
function updateObj(obj, data, bold, blink){
   if(bold)data=data.bold();
   if(blink)data=data.blink();
   ajaxEval(document.getElementById(obj), data);
}

function ajaxEval(obj, data){
   if(obj.tagName=='INPUT'||obj.tagName=='TEXTAREA') obj.value=data;
   else if(obj.tagName=='SELECT'){
       for(i=0;i<obj.options.length;i++)
        if(obj.options[i].value==data){obj.options[i].selected=true;break;}
   }else obj.innerHTML = data;
}

function ajaxCallBack(obj, ajaxObj, callback){
return function(){
    if(ajaxObj.readyState == 4){
       if(callback) if(!callback(obj,ajaxObj))return;
       if (ajaxObj.status==200){
            if(ajaxObj.getResponseHeader("Content-Type").indexOf("application/x-javascript")>=0)
              eval(ajaxObj.responseText);
            else if(ajaxObj.getResponseHeader("Content-Type").indexOf('json')>=0){
        ajaxObj=eval("(" + ajaxObj.responseText + ")");
        for(key in ajaxObj){
            o=eval('obj.'+key);
            if(typeof(o)!='undefined')ajaxEval(o, ajaxObj[key]);
        }
        obj.style.display='block'; // до окончания загрузки информации форма скрыта
        }else updateObj(obj, ajaxObj.responseText);
        }
       else updateObj(obj, ajaxObj.status+' '+ajaxObj.statusText,1,1);
    }
}}

// функция, осуществляющая JSON-запрос
function edit(id){
document.frm.id.value=id;
document.frm.action='#'+id;
ajaxLoad(document.frm,'json.php?id=383&tbl=user&id='+id);
document.frm.name.focus();
return false;
}