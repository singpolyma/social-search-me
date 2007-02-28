/* 
   To My Timezone - Convert dates into local user's timezone
   Stephen Paul Weber a.k.a. Singpolyma
   http://singpolyma-tech.blogspot.com/
   cc-by-nc-sa : http://creativecommons.org/licenses/by-nc-sa/2.5/
*/

if( document.all && !document.getElementsByTagName )
  document.getElementsByTagName = function( nodeName )
  {
    if( nodeName == '*' ) return document.all;
    var result = [], rightName = new RegExp( nodeName, 'i' ), i;
    for( i=0; i<document.all.length; i++ )
      if( rightName.test( document.all[i].nodeName ) )
 result.push( document.all[i] );
    return result;
  };
document.getElementsByClassName = function( className, nodeName )
{
  var result = [], tag = nodeName||'*', node, seek, i;
  var rightClass = new RegExp( '(^| )'+ className +'( |$)' );
  seek = document.getElementsByTagName( tag );
  for( i=0; i<seek.length; i++ )
    if( rightClass.test( (node = seek[i]).className ) )
      result.push( seek[i] );
  return result;
};
//adds an onload event to the current page
function addLoadEvent(func) {
         var oldonload = window.onload;
         if (typeof(window.onload) != 'function') {
            window.onload = func;
         } else {
            window.onload = function() {
               oldonload();
               func();
            }
         }//end if
}//end function addLoadEvent
function zeropad(n) { return n>9 ? n : '0'+n; }
function is_number(str) { return (new String(str)).match(/^[0-9]*$/) ? true : false; }

function to_my_timezone(format,tclass) {
 try {
   if(!format) format = 'Y-m-d H:i';
   if(!tclass) tclass = 'time';
   var times = document.getElementsByClassName(tclass);
   var stamp = '', tmp = '', ampm = '', twelvehour = '';
   for(var i in times) {//loop through elements with class=time
      if(!times[i].firstChild) continue;
      stamp = times[i].title ? times[i].title : times[i].firstChild.nodeValue;//extract timestamp from TITLE attribute or node value
	  if(!stamp) continue;//if there is no timestamp, skip
	  if(is_number(stamp)) {//javascript uses milliseconds and creates date objects in local time
	     stamp = stamp * 1000;
		 stamp = stamp + ((new Date()).getTimezoneOffset()*60*1000);
      }//end if is_number stamp
	  if(!is_number(stamp)) {//if we are not yet in unix time, try default parse
	     tmp = Date.parse(stamp);
		 if(tmp && is_number(tmp) && tmp > 0) stamp = tmp;
	  }//end if ! is_number stamp
      if(!is_number(stamp)) {//if we are not yet in unix time, try ATOM-style parse
	     tmp = stamp.match(/^(\d+)-(\d+)-(\d+)T(\d+):(\d+):(\d+)([+-]\d+):(\d+)$/);
         if(!tmp) tmp = stamp.match(/^(\d+)-(\d+)-(\d+)T(\d+):(\d+):(\d+)(Z)$/);
         if(tmp) {
            stamp = new Date(parseInt(tmp[1],10), parseInt(tmp[2],10)-1 /*since January==0 in Date lingo*/, parseInt(tmp[3],10), parseInt(tmp[4],10), parseInt(tmp[5],10), 0, parseInt(tmp[6],10)).getTime();
            if(tmp[7] == 'Z') tmp[8] = 0;
            if(!tmp[8]) tmp[9] = 0;
            stamp = stamp + -(60*60*1000*parseInt(tmp[7],10)) + -(60*1000*parseInt(tmp[8],10));//adjust to GMT
         }//end if tmp
	  }//end if ! is_number stamp
	  if(!stamp || !is_number(stamp) || stamp <= 0) continue;//if we cannot parse timestamp, skip
	  stamp = stamp - ((new Date()).getTimezoneOffset()*60*1000);
	  stamp = new Date(stamp);
	  ampm = 'AM';
	  twelvehour = stamp.getHours();
	  if(stamp.getHours() > 12) {
	     ampm = 'PM';
	     twelvehour = stamp.getHours() - 12;
      }//end if hours > 12
	  tmp = format.replace(/Y/,stamp.getFullYear()).replace(/m/,zeropad(stamp.getMonth()+1)).replace(/d/,zeropad(stamp.getDate())).replace(/H/,zeropad(stamp.getHours())).replace(/i/,zeropad(stamp.getMinutes())).replace(/h/,twelvehour).replace(/A/,ampm);
          if(times[i].firstChild)
             times[i].firstChild.nodeValue = tmp;
   }//end for i in times
 } finally {}
}//end function to_my_timezone