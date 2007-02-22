var YubNubHistory = [];
var YubNubHistoryIdx = 0;
var wasctrl = false;
String.prototype.trim = function() { return this.replace(/^\s+|\s+$/g, ""); };

var prefs = Components.classes["@mozilla.org/preferences-service;1"].
                getService(Components.interfaces.nsIPrefBranch);
if(prefs.getPrefType("yubnub.history") == prefs.PREF_STRING)
   YubNubHistory = prefs.getCharPref("yubnub.history").split('{-}');

function isDigit(num) {
	if (num.length>1){return false;}
	var string="1234567890";
	if (string.indexOf(num)!=-1){return true;}
	return false;
	}
   
function isInteger(val){
	for(var i=0;i<val.length;i++){
		if(!isDigit(val.charAt(i))){return false;}
	}
	return true;
}
   
function YubNubBarICO() {
   var baricon = document.getElementById("page-proxy-favicon");
   baricon.src = "chrome://yubnub/skin/yubnub.png";
}//end function YubNubBarICO

function YubNubGOTO(url) {
   var locbar = document.getElementById("urlbar");
   locbar.value = url;
   window._content.document.location = url;
   YubNubBarICO();
}//end function YubNubGOTO

function YubNubdohistory(cmdstr) {
   YubNubHistory.push(cmdstr);
   if(YubNubHistory.length > 50)
      YubNubHistory.shift();
   YubNubHistoryIdx = YubNubHistory.length;
   prefs.setCharPref("yubnub.history",YubNubHistory.join('{-}'));
   var uri = Components.classes["@mozilla.org/network/io-service;1"].getService(Components.interfaces.nsIIOService).newURI('y:'+cmdstr, null, null);
   var history = Components.classes["@mozilla.org/browser/global-history;2"].getService(Components.interfaces.nsIBrowserHistory);
   history.addPageWithDetails(uri,cmdstr,new Date().getTime());
   history.markPageAsTyped(uri);
}//end function YubNubdohistory

function YubNubGo(param) {
   YubNubBarICO();
   var locbar = document.getElementById("urlbar");
   var cmdstr = locbar.value;
   var cmd = new String(locbar.value).split(' ');
   cmd = cmd[0];
   var hadkey = false;
   var bmURL = '';
   
   //get keywords
      var dataSource = Components.classes["@mozilla.org/rdf/datasource;1?name=bookmarks"]
                                 .getService(Components.interfaces.nsIRDFDataSource);
      var service = Components.classes["@mozilla.org/rdf/rdf-service;1"]
                              .getService(Components.interfaces.nsIRDFService);
      var resources = dataSource.GetAllResources();
	var shortcutURLPredicate = service.GetResource( "http://home.netscape.com/NC-rdf#ShortcutURL" ); 
      var namePredicate = service.GetResource( "http://home.netscape.com/NC-rdf#Name" );
      var urlPredicate = service.GetResource( "http://home.netscape.com/NC-rdf#URL" );
      
      while( resources.hasMoreElements( ) )
      {
        var bookmark = resources.getNext( );
      
        if( bookmark instanceof Components.interfaces.nsIRDFResource )
        {
          var name = dataSource.GetTarget( bookmark, namePredicate, true );
          var shortcutURL = dataSource.GetTarget( bookmark, shortcutURLPredicate, true );
          var URL = dataSource.GetTarget( bookmark, urlPredicate, true );
         
          if( shortcutURL instanceof Components.interfaces.nsIRDFLiteral &&
              name instanceof Components.interfaces.nsIRDFLiteral && 
              URL instanceof Components.interfaces.nsIRDFLiteral )
          {
            hadkey = hadkey || (shortcutURL.Value == cmd);
            if(hadkey) {bmURL = URL.Value; break;}
          }
        }
      }
   if(hadkey) {
      var value = {'%s':cmdstr};
      if(bmURL.match(/\${/)) {
         value = cmdstr.split(' -');
         for(var i in value) {
            if(i == 0) {value['%s'] = value[i];continue;}
            var tmp = value[i].split(' ');
            var key = tmp.shift();
            tmp = tmp.join(' ');
            value[key.trim()] = tmp;
         }//for...in value
      }//end if switches
      var tmp = value['%s'].split(' ');
      tmp.shift();
      value['%s'] = tmp.join(' ');
      tmp = bmURL;
      tmp = tmp.replace(/%s/,value['%s']);
      for(var i in value) {
         if(isInteger(i) || i == '%s') continue;
         tmp = tmp.replace(new RegExp('\\${'+i+'}','g'),value[i]);
      }//end for ... in value
      YubNubGOTO(tmp);
      YubNubdohistory(cmdstr);
      return true;
   }//end if hadkey
   var isyubnub = false;
   if(cmdstr.match(/^y:.*$/m)) {//if is y:
      cmdstr = cmdstr.match(/^y:(.*)$/m);
      cmdstr = cmdstr[1];
      isyubnub = true;
   }//end if match y:
   if(!isyubnub && (cmdstr.match(/^[^ ]+\.[a-z]{1,4}(\/[^ ]*)?$/m) || cmdstr.match(/^about:.*$/m) || cmdstr.match(/^javascript:.*$/m) || cmdstr.match(/^http:.*$/m) || cmdstr.match(/^ftp[:\.].*$/m) || cmdstr.match(/^file[:\.].*$/m) || cmdstr.match(/^\d\d?\d?.\d\d?\d?.\d\d?\d?.\d\d?\d?$/m)))
      return handleURLBarCommand(param);
   else
      YubNubGOTO('http://yubnub.org/parser/parse?sourceid=Mozilla-search&command='+encodeURIComponent(cmdstr));
   YubNubdohistory(cmdstr);
   return true;
}//end function yubnubgo

function YubNubKey(event) {
   if(event.keyCode == 17) {wasctrl = true;return false;}
   if(wasctrl && event.keyCode == 38) {
      var locbar = document.getElementById("urlbar");
      YubNubHistoryIdx--;
      if(YubNubHistoryIdx < 0)
         YubNubHistoryIdx = YubNubHistory.length-1;
      locbar.value = YubNubHistory[YubNubHistoryIdx];
      wasctrl = false;
      return false;
   }//end if up-arrow
   if(wasctrl && event.keyCode == 40) {
      var locbar = document.getElementById("urlbar");
      YubNubHistoryIdx++;
      if(YubNubHistoryIdx > YubNubHistory.length-1)
         YubNubHistoryIdx = 0;
      locbar.value = YubNubHistory[YubNubHistoryIdx];
      if(locbar.value == 'undefined')
         locbar.value = '';
      wasctrl = false;
      return false;
   }//end if down-arrow
   wasctrl = false;
   return true;
}//end YubNubKey


/* auto icon-changeing code */

function blank2yubico() {
   if(!window._content.document.location || new String(window._content.document.location).match(/^about:.*$/m))
      YubNubBarICO();
}//end functin blank2yubico
window.addEventListener("pageshow", blank2yubico, false);
window.addEventListener('focus', blank2yubico, false);
