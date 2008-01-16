   <form id="login" method="get" action="/login/try_auth.php"><div>
		<h2>Login</h2>
     <input type="hidden" name="action" value="<?php echo $login_action ? $login_action : 'verify'; ?>" />
     <select class="openid" name="type" id="type" onchange="this.className=this.options[this.selectedIndex].className;">
           <option value="openid" class="openid">OpenID</option>
           <option value="aol" class="aol">AOL/AIM</option>
           <option value="livejournal" class="livejournal">LiveJournal</option>
           <option value="technorati" class="technorati">Technorati</option>
           <option value="wordpress" class="wordpress">WordPress.com</option>
        </select>
   	<input type="text" name="openid_identifier" value="<?php echo htmlentities($_REQUEST['openid_identifier']); ?>" />
   	<input style="display:none;" type="submit" value="Login" />
		or <a href="https://www.myopenid.com/affiliate_signup?affiliate_id=1735" rel="register">register</a>
   </div></form>
