document.getElementsByRel = function( className, nodeName )
{
  var result = [], tag = nodeName||'*', node, seek, i,j,rightClass;
	var rightClasses = [];
	for(i = 0; i < className.length; i++) {
		rightClasses.push( new RegExp( '(^| )'+ className[i] +'( |$)' ) );
	}
  seek = document.getElementsByTagName( tag );
  for( i=0; i<seek.length; i++ ) {
		for(j = 0; j < rightClasses.length; j++) {
			rightClass = rightClasses[j];
		    if( rightClass.test( (node = seek[i]).rel ) )
      		result.push( seek[i] );
		}
	}
  return result;
};

function contact_transform(node, data) {
	if(!data.fn) return;
	var block = document.createElement('div');
	block.style.clear = 'left';
	block.style.border = '2px dotted #aaa';
	block.style.padding = '0.5em';
	block.style.margin = '0.5em';
	block.style.display = 'block';
	block.className = 'vcard';
	block.style.maxWidth = '40em';
	block.style.minHeight = '6em';
	block.style.overflow = 'hidden';
	node.parentNode.insertBefore(block, node);
	if(node.parentNode.nodeName == 'LI') {
		node.parentNode.style.listStyleType = 'none';
		node.parentNode.style.paddingLeft = '0px';
		node.parentNode.style.marginLeft = '-1em';
	}
	node.parentNode.removeChild(node);
	if(data.photo && data.photo[0]) {
		var photo = document.createElement('img');
		photo.src = data.photo[0];
		photo.style.cssFloat = 'left';
		photo.style.maxWidth = '6em';
		photo.style.maxHeight = '6em';
		photo.style.marginRight = '0.5em';
		photo.className = 'photo';
		block.appendChild(photo);
	}
	var fnline = document.createElement('div');
	var fn = document.createElement('span');
	fn.innerHTML = data.fn;
	fn.className = 'fn';
	fnline.appendChild(fn);
	if(data.email && data.email[0]) {
		fnline.appendChild(document.createTextNode(' ( '));
		var email = document.createElement('a');
		email.href = 'mailto:' + data.email[0];
		email.innerHTML = data.email[0];
		fnline.appendChild(email);
		fnline.appendChild(document.createTextNode(' ) '));
	}//end if email
	block.appendChild(fnline);
	var url;
	var urls = document.createElement('div');
	for(var i in data.url) {
		if(data.url[i].logo) {
			url = document.createElement('a');
			url.style.cssFloat = 'left';
			url.style.marginRight = '0.8em';
			url.style.marginBottom = '0.3em';
			url.style.whiteSpace = 'nowrap';
			url.className = 'url';
			url.href = data.url[i].url;
			url.innerHTML = ' <img src="'+data.url[i].logo+'" alt="'+data.url[i].org+':" />&nbsp;'+data.url[i].fn;
			urls.appendChild(url);
		} else {
			if(i < 1) {//first URL
				url = document.createElement('a');
				url.className = 'url';
				url.style.marginBottom = '0.5em';
				url.href = data.url[i].url;
				url.innerHTML = data.url[i].url;
				block.appendChild(url);
			}//end if i < 1
		}//end if-else logo
		url.style.display = 'block';
	}//end for data.urls
	block.appendChild(urls);
}


var contacts = document.getElementsByRel(['contact','friend','acquaintance','met','co-worker','colleague','co-resident','neighbor','child','parent','sibling','spouse','kin','muse','crush','date','sweetheart']);

for(var i in contacts) {

	var script = document.createElement('script');
	script.type = 'text/javascript';
	script.src = 'http://scrape.singpolyma.net/profile/person.js.php?callback=(function(data) {contact_transform(contacts['+i+'],data);})&url=' + encodeURIComponent(contacts[i].href);
	document.body.appendChild(script);

}//end for contacts
