// FreshTags0.5-Singpolyma2
// Tag-Driven Blog Navigation
// www.greg-hill.id.au 2006
// modifications by Stephen Paul Weber a.k.a. Singpolyma (singpolyma-tech.blogspot.com)
// This work is licensed under a Creative Commons Attribution-ShareAlike 2.1 Australia License.
//     (http://creativecommons.org/licenses/by-sa/2.1/au/)
// URL: http://singpolyma-tech.blogspot.com/2006/02/freshtags-singpolyma.html
// Original URL: http://ghill.customer.netspace.net.au/freshtags/

//add the asynch load event
addLoadEvent(freshtags_load);

//make sure the WidgetData object and freshtags section are defined
if(typeof(WidgetData) != 'object') WidgetData = {};
if(typeof(WidgetData['freshtags']) != 'object') WidgetData['freshtags'] = {};

//draw in backwards-compatible settings data from global variables
if(typeof(del_user) != 'undefined') {
   if(typeof(freshtags_tags_id) == 'undefined')
      var freshtags_tags_id = 'freshtags_tags';
   if(typeof(WidgetData['freshtags'][freshtags_tags_id]) == 'undefined')
      WidgetData['freshtags'][freshtags_tags_id] = {'type':'tags','del_user':del_user};
   if(typeof(curr_tags) != 'undefined')
      WidgetData['freshtags'][freshtags_tags_id]['curr_tags'] = curr_tags;
   if(typeof(anchor) != 'undefined')
      WidgetData['freshtags'][freshtags_tags_id]['anchor'] = anchor;
   if(typeof(defs) != 'undefined')
      WidgetData['freshtags'][freshtags_tags_id]['defs'] = defs;
   if(typeof(no_autocapture) != 'undefined')
      WidgetData['freshtags'][freshtags_tags_id]['no_autocapture'] = no_autocapture;
   if(typeof(freshtags_tag_format) != 'undefined')
      WidgetData['freshtags'][freshtags_tags_id]['format'] = freshtags_tag_format;
   if(typeof(freshtags_tag_url) != 'undefined')
      WidgetData['freshtags'][freshtags_tags_id]['tag_url'] = freshtags_tag_url;

   if(typeof(freshtags_posts_id) == 'undefined')
      var freshtags_posts_id = 'freshtags_posts';
   if(typeof(WidgetData['freshtags'][freshtags_posts_id]) == 'undefined')
      WidgetData['freshtags'][freshtags_posts_id] = {'type':'posts','format':'list','tag_list':freshtags_tags_id};
   if(typeof(freshtags_postpage_id) != 'undefined' && typeof(WidgetData['freshtags'][freshtags_postpage_id]) == 'undefined')
      WidgetData['freshtags'][freshtags_postpage_id] = {'type':'posts','format':'list-extended','tag_list':freshtags_tags_id};
   if(typeof(maxposts) != 'undefined') {
      WidgetData['freshtags'][freshtags_posts_id]['rows'] = maxposts;
      if(typeof(WidgetData['freshtags'][freshtags_postpage_id]) == 'object')
         WidgetData['freshtags'][freshtags_postpage_id]['rows'] = maxposts;
   }//end if maxposts != undefined
}//end if del_user ! undefined

//set defaults
for(id in WidgetData['freshtags']) {
   if(WidgetData['freshtags'][id]['type'] == 'tags') {
      if(!WidgetData['freshtags'][id]['format'])
         WidgetData['freshtags'][id]['format'] = 'drop-add';
      if(!WidgetData['freshtags'][id]['rows'])
         WidgetData['freshtags'][id]['rows'] = 0;
      if(!WidgetData['freshtags'][id]['prompt'])
         WidgetData['freshtags'][id]['prompt'] = '- Tags -';
      if(!WidgetData['freshtags'][id]['tag_url'])
         WidgetData['freshtags'][id]['tag_url'] = '';
      if(!WidgetData['freshtags'][id]['source'])
         WidgetData['freshtags'][id]['source'] = 'del.icio.us';
      if(WidgetData['freshtags'][id]['del_user'])
         WidgetData['freshtags'][id]['username'] = WidgetData['freshtags'][id]['del_user'];
      //spaces are + for del.icio.us
      if(!WidgetData['freshtags'][id]['anchor']) WidgetData['freshtags'][id]['anchor'] = '';
      if(!WidgetData['freshtags'][id]['defs']) WidgetData['freshtags'][id]['defs'] = '';
      WidgetData['freshtags'][id]['anchor'] = WidgetData['freshtags'][id]['anchor'].replace(/ +/g,'+');
      WidgetData['freshtags'][id]['defs'] = WidgetData['freshtags'][id]['defs'].replace(/ +/g,'+');
      //set callback handlers
      WidgetData['freshtags'][id]['main_tags_loaded'] = eval('function(delicious_data){main_tags_loaded(delicious_data,"'+id+'");}');
      WidgetData['freshtags'][id]['tags_loaded'] = eval('function(delicious_data){tags_loaded(delicious_data,"'+id+'");}');
   }//end if type == tags
   if(WidgetData['freshtags'][id]['type'] == 'posts' || WidgetData['freshtags'][id]['type'] == 'external') {
      if(!WidgetData['freshtags'][id]['format'])
         WidgetData['freshtags'][id]['format'] = 'list';
      if(!WidgetData['freshtags'][id]['rows'])
         WidgetData['freshtags'][id]['rows'] = 0;
      //set callback handlers
      WidgetData['freshtags'][id]['posts_loaded'] = eval('function(delicious_data,onnull){posts_loaded(delicious_data,"'+id+'",onnull);}');
   }//end if type == tags
}//end for id in WidgetData['freshtags']

//function called when FreshTags widgets are loaded (usually on page load) to set them up
function freshtags_load(aid) {
   if(!aid) {
      for(id in WidgetData['freshtags'])
         freshtags_load_sub(id);
   } else {
      freshtags_load_sub(aid,true);
   }//end if-else ! aid
}//end function freshtags_load

//function for use in freshtags_load processing
function freshtags_load_sub(id,allowexternal) {
   if(typeof(WidgetData['freshtags'][id]) != 'object') return;
   if(WidgetData['freshtags'][id]['type'] == 'tags') {
      if(WidgetData['freshtags'][id]['source'] == 'del.icio.us') writeScript('http://del.icio.us/feeds/json/tags/'+WidgetData['freshtags'][id]['username']+'/'+WidgetData['freshtags'][id]['anchor']+'?sort=freq&count=100&callback=WidgetData[\'freshtags\'][\''+id+'\'][\'main_tags_loaded\']');
      if(WidgetData['freshtags'][id]['source'] == 'mediawiki') writeScript('http://singpolymaplay.ning.com/MediaWiki-categories.php?xn_auth=no&mainpage='+encodeURIComponent(WidgetData['freshtags'][id]['mainpage'])+'&callback=WidgetData[\'freshtags\'][\''+id+'\'][\'main_tags_loaded\']');
   } else if ((WidgetData['freshtags'][id]['type'] == 'posts' && (!WidgetData['freshtags'][id]['tag_list'] || allowexternal)) || (allowexternal && WidgetData['freshtags'][id]['type'] == 'external')) {
      //TODO : need to get all tags for this user (for filter)
      WidgetData['freshtags'][id]['curr_tags'] = get_current_tags(WidgetData['freshtags'][id]['defs'],'',WidgetData['freshtags'][id]['no_autocapture']);
      process_source(id);
   }//end if-else-if type==tags, etc
}//end function freshtags_load_sub

//Pick up current tags (if exists) from URL or referrer or search engine
function get_current_tags(defs,all_tags,no_autocapture) {
   var return_tags = fetchTags('','');//string of + separated tags
   if(!return_tags && !no_autocapture) {//if there is no tag specified and none in the referrer or search query string, try to grab from page
      var reltag = xget('//*[contains(@rel, "tag")]');
      if(reltag)
         return_tags = reltag.href.replace(/(.*)\/$/i, '$1').split('/').pop().split('?').reverse().pop().split('#').reverse().pop();
   }//end if !no_autocapture

   if(defs && !return_tags)//if no tags grabbed, use defaults
	   return_tags = defs;
        
   return_tags = return_tags.replace(/(?:\%20)+/gi,"+");
   return_tags = return_tags.replace(/ +/gi,"+");
   return_tags = return_tags + '+' + return_tags.replace(/_/,' ');
   
   return_tags = filterTags(return_tags,all_tags);
   
   return return_tags;
}//end function get_current_tags

//main handler for when the full tag list has been loaded for a tag list widget
//handles loading more tag data based on curr_tags or using all tags as the list
function main_tags_loaded(delicious_data,id) {
   if(delicious_data) {
      WidgetData['freshtags'][id]['all_tag_data'] = delicious_data;
      if(!WidgetData['freshtags'][id]['curr_tags'])
         WidgetData['freshtags'][id]['curr_tags'] = get_current_tags(WidgetData['freshtags'][id]['defs'],WidgetData['freshtags'][id]['all_tag_data'],WidgetData['freshtags'][id]['no_autocapture']);
   }//end if delicious_data
   if(WidgetData['freshtags'][id]['curr_tags']) {
      if(WidgetData['freshtags'][id]['source'] == 'del.icio.us') writeScript('http://del.icio.us/feeds/json/tags/'+WidgetData['freshtags'][id]['username']+'/'+WidgetData['freshtags'][id]['anchor']+'+'+WidgetData['freshtags'][id]['curr_tags']+'?count=100&sort=freq&callback=WidgetData[\'freshtags\'][\''+id+'\'][\'tags_loaded\']');
      if(WidgetData['freshtags'][id]['source'] == 'mediawiki') writeScript('http://singpolymaplay.ning.com/MediaWiki-categories.php?xn_auth=no&mainpage='+encodeURIComponent(WidgetData['freshtags'][id]['mainpage'])+'&tags='+encodeURIComponent(WidgetData['freshtags'][id]['curr_tags'].replace(/\+/,' '))+'&callback=WidgetData[\'freshtags\'][\''+id+'\'][\'tags_loaded\']');
   } else
      WidgetData['freshtags'][id]['tags_loaded'](delicious_data);
}//end function main_tags_loaded

//main handler for what the tag list for posts matching curr_tags has been loaded on a tag widget
//also wraps rendering a tag widget based on this data and loading actual post data
function tags_loaded(delicious_data,id) {
   if(!WidgetData['freshtags'][id]['curr_tags']) WidgetData['freshtags'][id]['curr_tags'] = '';
   if(delicious_data)
      WidgetData['freshtags'][id]['tag_data'] = delicious_data;
   tagarea = document.getElementById(id);
   tagarea.innerHTML = listTags(id);
   for(i in WidgetData['freshtags']) {
      if((WidgetData['freshtags'][i]['type'] == 'posts' || (WidgetData['freshtags'][i]['type'] == 'external' && WidgetData['freshtags'][i]['loaded'])) && WidgetData['freshtags'][i]['tag_list'] == id) {
         if(WidgetData['freshtags'][id]['curr_tags']) {//if there are tags selected/detected
            postarea = document.getElementById(i);
            if(postarea)
               postarea.innerHTML = '<i>Loading posts data...<\/i>';
            process_source(i);
         } else {//otherwise blank out post fields
            WidgetData['freshtags'][i]['posts_loaded']({});
         }//end if-else curr_tags
      }//if posts widget going with this tag widget
   }//end for id in WidgetData['freshtags']
   
   //rewrite URLs on rel=bookmark
   var links = document.getElementsByTagName('a');
   for(var i = 0; i < links.length; i++) {
      if(!links[i].hasAttribute('rel')) continue;
      var rel = links[i].getAttribute('rel').split(' ');
      var hasrel = false;
      for(var i2 = 0; i2 < rel.length; i2++)
         hasrel = hasrel || rel[i2] == 'bookmark';
      if(!hasrel) continue;
      links[i].href = makeURL(WidgetData['freshtags'][id]['curr_tags'],links[i].href);
   }//end for var i < links.length
   
}//end function tags_loaded

//main handler for when the post data has been loaded for a post widget
//also wraps rendering of the post widget with this data
function posts_loaded(delicious_data,id,onnull) {
   WidgetData['freshtags'][id]['posts_data'] = delicious_data;
   var out = listTitles(id);
   if(!out && onnull) {
      if(onnull(id))
         return;
      else
         out = listTitles(id,'show');
   }//end if ! out
   postarea = document.getElementById(id);
   if(postarea)
      postarea.innerHTML = out;
}//end function posts_loaded

//converts the JSON returned by feed2json to JSON of the Delicious.posts type
function feedjson2deljson(json_data) {
   var rtrn = [];
   for(var i=0; i<json_data.items.length; i++) {
      obj = {};
      obj.d = json_data.items[i].title;
      obj.u = json_data.items[i].link;
      if(obj.u.href)
         ojb.u = obj.u.href;
      obj.n = '';
      if(json_data.items[i].description)
         obj.n = json_data.items[i].description;
      if(json_data.items[i].content)
         obj.n = json_data.items[i].content;
      rtrn.push(obj);
   }//end for
   return rtrn;
}//end feedjson2deljson

//process non-local post source
function process_source(id,feedoverride) {
   var username, anchor;
   if(WidgetData['freshtags'][id]['tag_list']) {
      var curr_tags = WidgetData['freshtags'][WidgetData['freshtags'][id]['tag_list']]['curr_tags'];
      username = WidgetData['freshtags'][WidgetData['freshtags'][id]['tag_list']]['username'];
      anchor = WidgetData['freshtags'][WidgetData['freshtags'][id]['tag_list']]['anchor'];
      if(!WidgetData['freshtags'][id]['mainpage'])
         WidgetData['freshtags'][id]['mainpage'] = WidgetData['freshtags'][WidgetData['freshtags'][id]['tag_list']]['mainpage'];
      if(!WidgetData['freshtags'][id]['source'])
         WidgetData['freshtags'][id]['source'] = WidgetData['freshtags'][WidgetData['freshtags'][id]['tag_list']]['source'];
   } else
      var curr_tags = WidgetData['freshtags'][id]['curr_tags'];
   if(WidgetData['freshtags'][id]['username']) {
      username = WidgetData['freshtags'][id]['username'];
      anchor = WidgetData['freshtags'][id]['anchor'];
   }//end if username
   if(!WidgetData['freshtags'][id]['source']) WidgetData['freshtags'][id]['source'] = 'del.icio.us';
   if(!feedoverride && (WidgetData['freshtags'][id]['source'] == 'del.icio.us' || WidgetData['freshtags'][id]['source'] == 'delicious' || WidgetData['freshtags'][id]['source'] == 'wordpress')) {
      WidgetData['freshtags'][id]['posts_loaded'] = eval('function(delicious_data,onnull){posts_loaded(delicious_data,"'+id+'",function(id){if(WidgetData[\'freshtags\'][id][\'feedurl\']) {process_source(id,true);return true;}return false;});}');
      if(curr_tags) {
         if(WidgetData['freshtags'][id]['source'] == 'wordpress') {
            if(WidgetData['freshtags'][id]['url'].substr(-1,1) != '/')
               WidgetData['freshtags'][id]['url'] += '/';
            writeScript(WidgetData['freshtags'][id]['url']+'wp-content/plugins/freshtags.php?json&tags='+curr_tags+'&count='+(WidgetData['freshtags'][id]['rows'] ? WidgetData['freshtags'][id]['rows'] : 100)+'&callback=WidgetData[\'freshtags\'][\''+id+'\'][\'posts_loaded\']');
         } else
            writeScript('http://del.icio.us/feeds/json/'+username+'/'+anchor+'+'+curr_tags+'?count='+(WidgetData['freshtags'][id]['rows'] ? WidgetData['freshtags'][id]['rows'] : 100)+'&callback=WidgetData[\'freshtags\'][\''+id+'\'][\'posts_loaded\']');
      } else {
         if(WidgetData['freshtags'][id]['feedurl'])
            process_source(id,true);
         else
            WidgetData['freshtags'][id]['posts_loaded']({},function(id){return false;});
      }//end if-else curr_tags
   }//end if service = del.icio.us
   if(!feedoverride && WidgetData['freshtags'][id]['source'] == 'mediawiki') {
      var prefix = WidgetData['freshtags'][id]['mainpage'].replace(/Main_Page[\/]?/,'') + '/Category:';
      curr_tags = curr_tags.split('+');
      curr_tags = curr_tags[0];
      WidgetData['freshtags'][id]['curr_tags'] = curr_tags;
      if(WidgetData['freshtags'][id]['tag_list'] && WidgetData['freshtags'][WidgetData['freshtags'][id]['tag_list']]['source'] == 'mediawiki' && !WidgetData['freshtags'][WidgetData['freshtags'][id]['tag_list']]['tags_modded']) {WidgetData['freshtags'][WidgetData['freshtags'][id]['tag_list']]['curr_tags'] = curr_tags; WidgetData['freshtags'][WidgetData['freshtags'][id]['tag_list']]['tags_modded'] = true; freshtags_load(WidgetData['freshtags'][id]['tag_list']);}
      writeScript('http://singpolymaplay.ning.com/MediaWiki-items.php?xn_auth=no&url='+encodeURIComponent(prefix + encodeURIComponent(curr_tags.replace(/\+/,'_').replace(/ +/,'_')))+'&callback=WidgetData[\'freshtags\'][\''+id+'\'][\'posts_loaded\']');
   }//end if source == mediawiki
   if(feedoverride || WidgetData['freshtags'][id]['source'] == 'feed') {
      WidgetData['freshtags'][id]['posts_loaded'] = eval('function(feed_data,onnull){posts_loaded(feedjson2deljson(feed_data),"'+id+'",onnull);}');
      var feedurl = WidgetData['freshtags'][id]['feedurl'];
      if(!curr_tags) curr_tags = '';
      feedurl = feedurl.replace(/\%tags\%/,curr_tags);
      writeScript('http://xoxotools.ning.com/outlineconvert.php?xn_auth=no&output=json&classes=items&url='+encodeURIComponent(feedurl)+'&callback=WidgetData[\'freshtags\'][\''+id+'\'][\'posts_loaded\']');
   }//end if service == feed
   WidgetData['freshtags'][id]['loaded'] = true;
}//end function process_source

//gets a DOM item based on an XPATH selector (borrowed from Johan Sundstr?m)
function xget( xpathSelector ) {
  if(typeof(document.evaluate) == 'undefined') {return '';}
  var it = document.evaluate( xpathSelector, document, null,
			      XPathResult.UNORDERED_NODE_SNAPSHOT_TYPE, null );
  if( it.snapshotLength )
    return it.snapshotItem( 0 );
  return '';
}//end function xget

//adds an onload event to the current page
function addLoadEvent(func) {
         var oldonload = window.onload;
         if (typeof window.onload != 'function') {
            window.onload = function() {func();};
         } else {
            window.onload = function() {
               oldonload();
               func();
            }
         }//end if
}//end function addLoadEvent

function listTags(id)
{
// outputs formatted list of tags
// will not work in generic mode

// load up blogger navbar if present
if(typeof(navbar = document.forms["b-search"]) != 'undefined')
	navbar.as_q.value = WidgetData['freshtags'][id]['curr_tags'];

//allow format to be a function
if(typeof(WidgetData['freshtags'][id]['format']) == 'function')
   return WidgetData['freshtags'][id]['format'](id);

var outputHTML; 								// string containing returned HTML

//if (!del_user) return '';						// no user account, no tags

//get data from id
var type = WidgetData['freshtags'][id]['format'];
var rows = WidgetData['freshtags'][id]['rows'];
var prompt = WidgetData['freshtags'][id]['prompt'];

if(!type)
   type = 'drop-add';

// delicious branding
if(WidgetData['freshtags'][id]['source'] == 'del.icio.us')
   del_plug = '<a class="del_plug" href="http://del.icio.us/'+WidgetData['freshtags'][id]['del_user']+'/'+WidgetData['freshtags'][id]['anchor']+'+'+WidgetData['freshtags'][id]['curr_tags']+'"><img src="http://del.icio.us/static/img/delicious.small.gif" alt="delicious"></a><br/>'; 
else
   del_plug = '';

delete WidgetData['freshtags'][id]['tag_data'][WidgetData['freshtags'][id]['anchor']];//delete anchor and default tags from list
delete WidgetData['freshtags'][id]['all_tag_data'][WidgetData['freshtags'][id]['anchor']];
//delete WidgetData['freshtags'][id]['tag_data'][WidgetData['freshtags'][id]['defs']];
//delete WidgetData['freshtags'][id]['all_tag_data'][WidgetData['freshtags'][id]['defs']];

// based on type, get the template for the html to be written out [start, item, end, selected item
// !url! -> new url !murl! -> multi url !tag! -> tag name  !count! -> tag count
var raw_html;

switch(type)
{

	case "flat": raw_html=['', '<a class="showtag" href="!url!">!tag! (!count!)</a> ', '  '+del_plug, '<a class="showtag" href="!murl!"><strong>!tag! (!count!)</strong></a> '];
	break;

	case "flat-multi": raw_html=['', '<a class="showtag" href="!murl!">!tag! (!count!)</a> ', '  '+del_plug, '<a class="showtag" href="!murl!"><strong>!tag! (!count!)</strong></a> '];
	break;

	case "list": raw_html=['<ul class="showtag">', '<li class="showtag"><a class="showtag" href="!url!">!tag! (!count!)</a></li>',del_plug+'</ul>', '<li class="showtag"><a href="!murl!"><strong>!tag! (!count!)</strong></a></li>'];
	break;

	case "list-multi": raw_html=['<ul class="showtag">', '<li class="showtag">[<a class="showtag" href="!murl!">+</a>]&nbsp;<a class="showtag" href="!url!">!tag! (!count!)</a></li>',del_plug+'</ul>', '<li class="showtag">[<a class="showtag" href="!murl!">--</a>]&nbsp;<a href="!url!"><strong>!tag! (!count!)</strong></a></li>'];
	break;

	case "scroll": raw_html=['<select class="showtag" multiple name="multimenu">', '<option class="showtag" value="!tag!">!tag! (!count!)</option>', '</select><br/><input class="showtag" type="button" value="Go" onClick="clickScroll();">&nbsp;&nbsp;'+del_plug, '<option class="showtag" selected value="!tag!">&gt;&gt;!tag! (!count!)</option>'];
	break;

	case "drop": raw_html=['<select class="showtag" name="dropmenu" size="1" onChange="document.location.href=makeURL(this.options[this.selectedIndex].value,WidgetData[\'freshtags\'][\''+id+'\'][\'tag_url\'],WidgetData[\'freshtags\'][\''+id+'\'][\'join_char\']);">', '<option class="showtag" value="!tag!">!tag! (!count!)</option>', '</select>&nbsp;'+del_plug, '<option class="showtag" selected value="!tag!">&gt;&gt;!tag! (!count!)</option>'];
	break;
        
   case "drop-add": raw_html=['<select class="showtag" name="dropmenu" size="1" onChange="document.location.href=makeURL(WidgetData[\'freshtags\'][\''+id+'\'][\'curr_tags\']+\'+\'+this.options[this.selectedIndex].value,WidgetData[\'freshtags\'][\''+id+'\'][\'tag_url\'],WidgetData[\'freshtags\'][\''+id+'\'][\'join_char\']);">', '<option class="showtag" value="!tag!">!tag! (!count!)</option>', '</select>&nbsp;<a href="javascript:WidgetData[\'freshtags\'][\''+id+'\'][\'curr_tags\'] = \'\';WidgetData[\'freshtags\'][\''+id+'\'][\'main_tags_loaded\'](false);">reset</a>', '<option class="showtag" selected value="!tag!">&gt;&gt;!tag! (!count!)</option>'];
	break;
   
   case "drop-add-async": raw_html=['<select class="showtag" name="dropmenu" size="1" onChange="javascript:WidgetData[\'freshtags\'][\''+id+'\'][\'curr_tags\'] += \'+\'+this.options[this.selectedIndex].value;WidgetData[\'freshtags\'][\''+id+'\'][\'main_tags_loaded\'](false);">', '<option class="showtag" value="!tag!">!tag! (!count!)</option>', '</select>&nbsp;<a href="javascript:WidgetData[\'freshtags\'][\''+id+'\'][\'curr_tags\'] = \'\';WidgetData[\'freshtags\'][\''+id+'\'][\'main_tags_loaded\'](false);">reset</a>', '<option class="showtag" selected value="!tag!">&gt;&gt;!tag! (!count!)</option>'];
	break;

	case "sub": raw_html=['[ ', '!tag! (!count!) ', ']', '<strong>!tag! !count!</strong> '];
	break;

	default:
	   if(typeof(type) == 'string')
   	   raw_html = type.split('@');
   	else
   	   raw_html = type;
}

outputHTML = raw_html[0];						// write start code

if (prompt && (type=="drop" || type=="drop-add" || "drop-add-async"))//insert prompt
	outputHTML += '<option selected value="">'+prompt+'</option>';

var loop=0;
var taglist=WidgetData['freshtags'][id]['curr_tags'].split('+');

for(var i in WidgetData['freshtags'][id]['all_tag_data'])
{
	loop++;

	template = raw_html[1];						// item code template

	for(var j=0; j<taglist.length; j++)
		if (taglist[j].toLowerCase()==i.toLowerCase())	// if we're up to a currently selected tag ...
		{	
			template = raw_html[3]; 				// use "selected" item code template instead
			break;
		}
		
	template=template.replace(/!tag!/gi, i);				// insert tag

	template=template.replace(/!url!/gi, makeURL(i,WidgetData['freshtags'][id]['tag_url']) );		// insert new URL

	template=template.replace(/!murl!/gi, makeURL(taggle(i,WidgetData['freshtags'][id]['curr_tags']),WidgetData['freshtags'][id]['tag_url']) );//insert multiple URL

	c=WidgetData['freshtags'][id]['tag_data'][i];						// test if we need sub-counts
	a=WidgetData['freshtags'][id]['all_tag_data'][i];

	if (!a || a==0)
	{
		if (!c || c==0 || !WidgetData['freshtags'][id]['curr_tags'] || c==a)
			cLabel = "";
		else
			cLabel = c;
	}
	else
	{	
		if (!c || c==0 || !WidgetData['freshtags'][id]['curr_tags'] || c==a)
			cLabel = a;
		else
			cLabel = c+'/'+a;
	}

	template=template.replace(/!count!/gi, cLabel);			// insert count
	template=template.replace(/\(\)/gi,'');				// remove any empty brackets

	outputHTML+=template;

	if(loop>=rows && rows!=0)					// enforce row maximum
		break;
}

outputHTML+=raw_html[2];						// end code

return outputHTML;
}


function listTitles(id,onnull)
{
// outputs formatted list of titles

if(typeof(WidgetData['freshtags'][id]['format']) == 'function')
   return WidgetData['freshtags'][id]['format'](id);

var outputHTML=""; 							// string containing returned HTML
var css_label='\"showtitle\"';

var type = WidgetData['freshtags'][id]['format'];
var prompt = WidgetData['freshtags'][id]['prompt'];
var rows = WidgetData['freshtags'][id]['rows'];
if(!onnull)
   onnull = WidgetData['freshtags'][id]['onnull'];

if(!type)
   type = 'list';

if(WidgetData['freshtags'][id]['tag_list'])
   var curr_tags = WidgetData['freshtags'][WidgetData['freshtags'][id]['tag_list']]['curr_tags'];
else
   var curr_tags = WidgetData['freshtags'][id]['curr_tags'];

var entry = WidgetData['freshtags'][id]['posts_data'];
var isnull = false;
if(!entry || entry.length==0) isnull=true;//return if nothing to display
if(isnull && !onnull) return '';

if(rows==0) rows=entry.length;

rows=Math.min(rows, entry.length);

// based on type, get the template for the html to be written out [start, item, end]
// !url! -> new url  !title! -> post title 

var del_plug = '';

if(WidgetData['freshtags'][id]['source'] == 'delicious' || WidgetData['freshtags'][id]['source'] == 'del.icio.us') {
   if(WidgetData['freshtags'][id]['tag_list'])
      del_plug = '<a class="del_plug" href="http://del.icio.us/'+WidgetData['freshtags'][WidgetData['freshtags'][id]['tag_list']]['username']+'/'+WidgetData['freshtags'][WidgetData['freshtags'][id]['tag_list']]['anchor']+'+'+curr_tags+'"><img src="http://del.icio.us/static/img/delicious.small.gif" alt="delicious" /></a><br/>';
   else
      del_plug = '<a class="del_plug" href="http://del.icio.us/'+WidgetData['freshtags'][id]['username']+'/'+WidgetData['freshtags'][id]['anchor']+'+'+curr_tags+'"><img src="http://del.icio.us/static/img/delicious.small.gif" alt="delicious" /></a><br/>';
}//end if source==delicious

var raw_html;
switch(type)
{

	case "flat": raw_html=['', '<a class='+css_label+' href="!url!">!title!</a> ', ''];
	break;

	case "list": raw_html=['<ul class='+css_label+'>', '<li class='+css_label+'><a class='+css_label+' href="!url!" title="!extended-stripped!">!title!</a></li>','</ul>'];
	break;
        
   case "list-extended": raw_html=['<h2>Posts in "'+curr_tags+'"&nbsp;'+del_plug+'</h2> <ul class='+css_label+'>', '<li class='+css_label+'><a class='+css_label+' href="!url!">!title!</a> <br /> !extended!</li>','</ul>'];
	break;

	case "scroll": raw_html=['<select class='+css_label+' name="multilist" size="'+rows+'" onChange="document.location.href=this.options[this.selectedIndex].value">', '<option class='+css_label+' value="!url!">!title!</option>', '</select>'];
	break;

	case "drop": raw_html=['<select class='+css_label+' name="droplist" size="1" onchange="document.location.href=this.options[this.selectedIndex].value">', '<option class='+css_label+' value="!url!">!title!</option>', '</select>'];
	break;

	case "sub": raw_html=['[ ', '!title!, ', ']'];
	break;

	default:
	   if(typeof(type) == 'string')
	      raw_html = type.split('@');
	   else
	      raw_html = type;
}

outputHTML+=raw_html[0];						// start code

if (prompt && type=="drop")						// insert prompt
	outputHTML+='<option selected value="">'+prompt+'</option>';

for(var i=0; i<entry.length; i++)
{
	descr = tidy(entry[i].d);

	url = entry[i].u;
	if(curr_tags)//pass tags
		url=makeURL(curr_tags, url);

	template = raw_html[1];						// item code template
	template=template.replace(/!title!/i, descr);			// insert title
	template=template.replace(/!url!/i, url);				// insert URL
        template=template.replace(/!extended!/i, entry[i].n);			// insert extended
        template=template.replace(/!extended-stripped!/i, striphtml(entry[i].n));  // insert extended-stripped
	
	outputHTML+=template;							// write out item code

	if(i>=rows-1 && rows!=0)					// enforce row maximum
		break;
}

if(isnull && onnull=='show') {
   template = raw_html[1];						// item code template
   template=template.replace(/!title!/i, 'No Posts Found');			// insert title
   template=template.replace(/!url!/i, '');				// remove URL
   template=template.replace(/href=\"\"/i, '');				// remove href=""
   template=template.replace(/!extended!/i, '');			// remove extended
   template=template.replace(/!extended-stripped!/i, '');  // remove extended-stripped
   outputHTML+=template;							// write out item code
}//end if isnull && onnull==show

outputHTML+=raw_html[2];						// end code

return outputHTML;
}

function striphtml(str) {
   if(!str)
      return '';
   return str.replace(/(<([^>]+)>)/ig,"");
}//end striphtml

function tidy(descr)
{
// eat title ie everything before first :

pos = descr.indexOf(':');

if(pos==-1) return descr;

return descr.substr(pos+1);
}

function fetchTags( source, names )
{
// try matching tag in query string; 
// failing that try looking in referrer query string;
// if that fails, try looking in referrer pathname.

var ref=document.referrer.indexOf('?');					// separate out referrer query string
var ref_path=document.referrer;
var ref_query='';

if (ref>0)
{
	ref_path=document.referrer.substring(0,ref+1);
	ref_query=unescape(document.referrer.substr(ref));
}

if (!source && names)
	return '';

if (!source)
	return fetchTags( location.search, ['tags', 'tag', 'cat', 'label'] ) || fetchTags( location.href, ['label', 'tag', 'tags', 'cat', 'category', 'wiki', 'w', 'search', 'topics', 'topic'] ) || fetchTags( ref_query, ['tags', 'q', 'p', 'tag', 'cat', 'query', 'search', 'topics', 'topic', 'label'] ) || fetchTags( ref_path, ['label', 'tag', 'tags', 'cat', 'category', 'wiki', 'search', 'topics', 'topic'] );

var peeker, i, tag;
tag = [];
 for( i=0; i<names.length; i++ )
 {	if (source.indexOf('http:')==0)						// process path
		peeker = new RegExp( '[/]'+ names[i] +'[/:]([^&/?]*)', 'i' );
	else											// process query string
		peeker = new RegExp( '[?&]'+ names[i] +'[=]([^&]*)', 'i' );
        tag = peeker.exec(source);
	if(tag)
	   return unescape(tag[1]);
 }
return '';
}

//either tacks (asynch) <script> tag to body or writes (synch) it where it is
function writeScript(src,synch) {
  if(!synch) {
     var thescript = document.createElement("script");
     thescript.type = "text/javascript";
     thescript.src = src;
     document.body.appendChild(thescript);
  } else
     document.write('<script type="text/javascript" src="'+src+'"><\/script>');
}//end function writeScript

function taggle(new_tag,curr_tags)
{
// toggles presence (absence) of a new_tag in the currently selected tags list
// returns taggled string


var present=false, tagstring='', old_tags = curr_tags.split('+');

for(var i=0; i<old_tags.length; i++)
	if(old_tags[i].toLowerCase()!= new_tag.toLowerCase() )
		tagstring+=old_tags[i]+'+';
	else
		present=true;

if (!present) tagstring+=new_tag;

tagstring=tagstring.replace(/\++/gi, '+');
tagstring=tagstring.replace(/^\+/gi, '');				// tidy up
tagstring=tagstring.replace(/\+$/gi, '');

return tagstring;
}

function makeURL(tags, url, join_char)
{
// creates a new URL based on passed in tags

var pos;

if(!url) url = '';

var thetags = tags;
if(join_char) {
   thetags = thetags.split('+');
   thetags = thetags.join(join_char);
}//end if join_char

url = url.replace(/\%tags\%/,thetags);

if (url.indexOf('?')==-1) url+="?";

pos = url.search(/tags=/i);						// find start of "tags=..." parameter
if (pos==-1) 
{
	url+="&";
	pos=url.length;
}

endpos=url.indexOf('&',pos);						// find end of "tags=..." parameter
if (endpos==-1) endpos=url.length;

url=url.substr(0,pos)+"tags="+tags+url.substr(endpos);

return url;
}

function clickScroll()
{
// Processes multiple select scroll box 

var menus=document.getElementsByName('multimenu');
var len = menus.length;

var tagstring="";

for (var i=0; i<len; i++)						// For each menu ...
	for (var j=0; j<menus[i].options.length; j++)		// go through each option
		if (menus[i].options[j].selected)
			tagstring+=menus[i].options[j].value+"+";	// building currently selected tags

tagstring=tagstring.replace(/\+$/gi, '');

document.location.href=makeURL(tagstring);			// Submit selection

return;
}

function filterTags(tags,all_tags)
{
// returns filtered tagstring, dropping non-tags (or unlikely tags, for generic mode)

tags=tags.replace(/[+]/g, ' ');
var black = 'also another could every find from have here into I just many more most much next only really same should show still such that their them then there these they thing this those very well were what when where which while will with without would'; 

var white = 'art bbc css diy irc job fun law log mac map net osx pda php rdf rss tag tax tv web win xml';
var match = [], i, rexp, stop;

tags = tags.replace(/[!?\"#]/g,'');					// knock out punctuation

if (all_tags)
{
	for(i in all_tags )
		if( (new RegExp('\\b('+ i +')\\b', 'i')).test(tags) )
			match.push(i);
	return match.join('+');
}

// stop list is: known black words + (lowercase words shorter than 4) - known white words

rexp = new RegExp('\\b([a-z]{1,3})\\b','g'); 				// lowercase words <4

if (tags.match(rexp))
	black += ' '+tags.match(rexp).join(' ');

rexp = new RegExp('\\b('+white.replace(/ +/g,'|')+')\\b','ig');	// | separted white list
black = black.replace(rexp,'');

stop = new RegExp('\\b('+black.replace(/ +/g,'|')+')\\b', 'ig');
tags = tags.replace(stop, '').replace(/ +/g, '+');
return tags.replace(/^\+|\+$/g, '');
}
