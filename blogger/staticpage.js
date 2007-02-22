/* 
   StaticPage - Static pages for Blogger
   Stephen Paul Weber a.k.a. Singpolyma
   http://singpolyma-tech.blogspot.com/
   cc-by-nc-sa : http://creativecommons.org/licenses/by-nc-sa/2.5/
*/

//load the staticpage widgets, if any are applicable
function staticpage_load() {
   if(!statipage_query.page) return;
   var thepage = WidgetData['staticpage'][statipage_query.page];
   if(!thepage) return;
   if(thepage.type == 'iframe') {
      staticpage_callback({'html':'<iframe style="border-width:0px;width:100%;height:100%;" frameborder="0" src="' + thepage.url + '"></iframe>'});
   } else if(thepage.type == 'redirect') {
      window.location = thepage.url;
   } else {
     var thescript = document.createElement("script");
     thescript.type = "text/javascript";
     thescript.src = "http://singpolymaplay.ning.com/blogger/page_get.php?xn_auth=no&callback=staticpage_callback&url=" + encodeURIComponent(thepage.url);
     document.body.appendChild(thescript);
   }//end if-else thepage.type
}//end function staticpage_load

function staticpage_callback(data) {
   if(!statipage_query.page) return;
   var block = document.getElementById(statipage_query.page);
   if(block) {
      block.innerHTML = data.html;
      block.style.display = 'block';
   } else {
      document.close();
      document.open();
      document.write(data.html);
      document.close();
   }//end if-else block
}//end function staticpage_callback

//turn a query string (blah=blah&dude=dude with optional preceding ?) into JSON
function queryString2JSON(str) {
   var rtrn = {};
   var tmp = '';
   if(str.substr(0,1) == '?')
      str = str.substr(1,str.length);
   str = str.split('&');
   for(var i in str) {
      if(typeof(str[i]) != 'string') continue;
      tmp = str[i].split('=');
      rtrn[tmp[0]] = tmp[1];
   }//end for i in strs
   return rtrn;
}//end function queryString2JSON

//adds an onload event to the current page
function addLoadEvent(func) {
         var oldonload = window.onload;
         if (typeof window.onload != 'function') {
            window.onload = func;
         } else {
            window.onload = function() {
               oldonload();
               func();
            }
         }//end if
}//end function addLoadEvent

//make sure the WidgetData object and staticpage section are defined
if(typeof(WidgetData) != 'object') WidgetData = {};
if(typeof(WidgetData['staticpage']) != 'object') WidgetData['staticpage'] = {};

var statipage_query = queryString2JSON(window.location.search);
if(WidgetData['staticpage'][statipage_query.page] && WidgetData['staticpage'][statipage_query.page]['type'] == 'redirect')
   staticpage_load();
else
   addLoadEvent(staticpage_load);