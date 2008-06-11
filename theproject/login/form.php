   <!-- BEGIN ID SELECTOR --><script type="text/javascript" id="__openidselector" src="https://www.idselector.com/selector/3c30cd115d23cb4381b59010b7429854b8d30c80" charset="utf-8"></script><!-- END ID SELECTOR -->
   <form id="login" method="get" action="/login/try_auth.php"><div>
		<h2>Login</h2>
     <input type="hidden" name="action" value="<?php echo $login_action ? $login_action : 'verify'; ?>" />
   	<input type="text" name="openid_identifier" value="<?php echo htmlentities($_REQUEST['openid_identifier']); ?>" />
   	<input style="display:none;" type="submit" value="Login" />
		or <a href="https://www.myopenid.com/affiliate_signup?affiliate_id=1735" rel="register">register</a>
   </div></form>
