/* Script by Stephen Paul Weber a.k.a. Singpolyma (http://singpolyma-tech.blogspot.com/)
   Based on work by Johan Sundstrom (http://ecmanaut.blogspot.com/2005/10/blogger-hack-inline-comment-faces.html)
   Updated by PurpleMoggy (http://purplemoggy.blogspot.com/)
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
function writeScript(src) {
  var thescript = document.createElement("script");
  thescript.type = "text/javascript";
  thescript.src = src;
  document.body.appendChild(thescript);
}//end function writeScript
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
BloggerProfiles = {};
BloggerProfiles.callbacks = {};
BloggerProfiles.noimage = 'http://img139.imageshack.us/img139/1011/defaultavatarad7.png';
BloggerProfiles.imageWidth = 0;//60
BloggerProfiles.imageHeight = 0;//75
BloggerProfiles.callbacks.profile = function(data) {

          if(data.photo && data.photo.url) {
             node = document.createElement('img');
             node.alt = 'Photo';
             if(!data.photo.url)
                data.photo.url = BloggerProfiles.noimage;
             node.src = data.photo.url;
             if(data.photo.width)
                node.width = data.photo.width;
             if(data.photo.height)
                node.height = data.photo.height;
             if (BloggerProfiles.imageWidth && node.width > BloggerProfiles.imageWidth) {
                node.height = (BloggerProfiles.imageWidth * node.height) / node.width;
                node.width = BloggerProfiles.imageWidth;
             }
             if (BloggerProfiles.imageHeight && node.height > BloggerProfiles.imageHeight) {
                node.width = (BloggerProfiles.imageHeight * node.width) / node.height;
                node.height = BloggerProfiles.imageHeight;
             }
             thecomments = document.getElementsByClassName('commentphoto-'+escape(data.url));
             for(var i=0; i<thecomments.length; i++)
                thecomments[i].appendChild(node.cloneNode(false));
          } else {
             node = document.createElement('img');
             node.alt = 'Photo';
             node.src = BloggerProfiles.noimage;
             if (BloggerProfiles.imageWidth && node.width > BloggerProfiles.imageWidth) {
                node.height = (BloggerProfiles.imageWidth * node.height) / node.width;
                node.width = BloggerProfiles.imageWidth;
             }
             if (BloggerProfiles.imageHeight && node.height > BloggerProfiles.imageHeight) {
                node.width = (BloggerProfiles.imageHeight * node.width) / node.height;
                node.height = BloggerProfiles.imageHeight;
             }
             thecomments = document.getElementsByClassName('commentphoto-'+escape(data.url));
             for(var i=0; i<thecomments.length; i++)
                thecomments[i].appendChild(node.cloneNode(false));
          }//end if-else data.photo

          if(data.blogs) {
             for(var i=0; i<data.blogs.length; i++) {
                if(data.blogs[i].url == BloggerProfiles.blogurl) {
                   thecomments = document.getElementsByClassName('commentblock-'+escape(data.url));
                   for(var i2=0; i2<thecomments.length; i2++) {
                      thecomments[i2].style.color = BloggerProfiles.color;
                      thecomments[i2].style.backgroundColor = BloggerProfiles.bgcolor;
                   }//end for thecomments
                }//end if blogs.url == blogurl
             }//end for blogs
          }//end if data.blogs

}//end callback

function showCommentPhotos(photoclass,linkclass,linknum,fullclass,blogurl,color,bgcolor) {
  var comments, comlinks, i, node, re, by, blocks;
  if(!blogurl) blogurl = '';
  if(blogurl.match(/.*?index.html/)) blogurl = blogurl.match(/(.*)?index.html/)[1];
  BloggerProfiles.blogurl = blogurl;
  BloggerProfiles.color = color;
  BloggerProfiles.bgcolor = bgcolor;
  re = new RegExp( '^http://www.blogger.com/profile/\\d+', 'i' );
  comments = document.getElementsByClassName(photoclass);
  comlinks = document.getElementsByClassName(linkclass);
  if(fullclass)
     blocks = document.getElementsByClassName(fullclass);
  else
     blocks = [];
  var profilesobj = {};
  var profileurls = [];
  for(i=0; i<comments.length; i++) {
    by = comlinks[i].getElementsByTagName('a').item(linknum);
    if(by && re.test(by.href)) {
       comments[i].className += ' commentphoto-'+escape(by.href);
       if(blocks && blocks[i])
          blocks[i].className += ' commentblock-'+escape(by.href);
    } else {
       comments[i].className += ' commentphoto-nophoto'+i;
    }//end if-else
    if( !by || !re.test( by.href ) ) {BloggerProfiles.callbacks.profile({'url':'nophoto'+i,'photo':{'url':BloggerProfiles.noimage}});continue;}
    if(!profilesobj[by.href])
       profileurls.push(by.href);
    profilesobj[by.href] = true;
  }//end for
  for(i=0; i<profileurls.length; i++)
     writeScript('http://singpolymaplay.ning.com/bloggerProfile.php?xn_auth=no&url='+encodeURIComponent(profileurls[i]));
}//end function showCommentPhotos