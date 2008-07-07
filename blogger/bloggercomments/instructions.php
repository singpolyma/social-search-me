<xn:head>
<style type="text/css">
li {margin-bottom:1em;}
</style>
</xn:head>
<h1>Instructions on Setting up your Blogger Blog</h1>

<ol>

<li>Add this code to your blog template directly after the opening &lt;body&gt; tag:<br />

<pre>
&lt;!-- COMMENTS FEED --&gt;
&lt;ItemPage&gt;
&lt;ul class="xoxo posts" style="display:none;"&gt;
&lt;Blogger&gt;
   &lt;li&gt;
      &lt;a href="&lt;$BlogItemPermalinkUrl$&gt;"&gt;&lt;BlogItemTitle&gt;&lt;$BlogItemTitle$&gt;&lt;/BlogItemTitle&gt;&lt;/a&gt;
&lt;BlogItemCommentsEnabled&gt;
      &lt;ul&gt; 
&lt;BlogItemComments&gt;
         &lt;li&gt;&lt;a href="#c&lt;$BlogCommentNumber$&gt;" title="&lt;$BlogCommentNumber$&gt;0"&gt;comment&lt;/a&gt;
            &lt;$BlogCommentAuthor$&gt;
            &lt;dl&gt;
               &lt;dt&gt;body&lt;/dt&gt;
                  &lt;dd&gt;&lt;$BlogCommentBody$&gt;&lt;/dd&gt;
            &lt;/dl&gt;
         &lt;/li&gt;
&lt;/BlogItemComments&gt;
      &lt;/ul&gt;
&lt;/BlogItemCommentsEnabled&gt;
&lt;/li&gt;
&lt;/Blogger&gt;
&lt;/ul&gt;
&lt;/ItemPage&gt;
&lt;MainPage&gt;
&lt;ul class="xoxo posts" style="display:none;"&gt;
&lt;Blogger&gt;
   &lt;li&gt;
      &lt;a href="&lt;$BlogItemPermalinkUrl$&gt;"&gt;&lt;BlogItemTitle&gt;&lt;$BlogItemTitle$&gt;&lt;/BlogItemTitle&gt;&lt;/a&gt;
&lt;BlogItemCommentsEnabled&gt;
      &lt;ul&gt; 
&lt;BlogItemComments&gt;
         &lt;li&gt;&lt;a href="#c&lt;$BlogCommentNumber$&gt;" title="&lt;$BlogCommentNumber$&gt;0"&gt;comment&lt;/a&gt;
            &lt;$BlogCommentAuthor$&gt;
            &lt;dl&gt;
               &lt;dt&gt;body&lt;/dt&gt;
                  &lt;dd&gt;&lt;$BlogCommentBody$&gt;&lt;/dd&gt;
            &lt;/dl&gt;
         &lt;/li&gt;
&lt;/BlogItemComments&gt;
      &lt;/ul&gt;
&lt;/BlogItemCommentsEnabled&gt;
&lt;/li&gt;
&lt;/Blogger&gt;
&lt;/ul&gt;
&lt;/MainPage&gt;
&lt;!-- /COMMENTS FEED --&gt;
</pre>

<i>NOTE: If your blog uses an <a href="http://blogxoxo.blogspot.com/2006/01/xoxo-blog-format.html">XOXO Blog Format</a> comatible template (or hAtom with XOXO Blog Format comment extensions), this step is unnecessary.</i>

</li>

<li>Republish your blog</li>

<li>Set your comment notification address to bloggerecent|At|gmail.com, or add a forwarding filter to your existing notification address that forwards all incoming comment notifications there
<br />
<i>NOTE: if you skip this step the service will still work, but comments on older posts may not be indexed.</i>
</li>
</ol>

Once you have completed these steps the service will be able to generate feeds from your blog main page, and post pages.  Queries to the main page will also include new comments on old posts and all comments will be archived by the service.