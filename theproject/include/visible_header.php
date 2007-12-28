   <div style="position:absolute;top:1em;right:2em;text-align:right;">
		<?php
		require_once dirname(__FILE__).'/server.php';
		if($LOGIN_DATA['user_id']) {
			if($_REQUEST['server_id']) {
				$server = new server($_REQUEST['server_id']);
				require_once dirname(__FILE__).'/user.php';
				$current_user = new user($LOGIN_DATA['user_id'],$server);
   		   echo ' | '.round($current_user->calculateScore(),2)." Overall Score";
   		   echo ' | '.round($current_user->calculateDailyGold(),2)." Income";
      		echo ' | '.$current_user->getValue('gold')." Gold";
	      	echo ' | <a href="/server/'.$_REQUEST['server_id'].'">'.$current_user->getValue('city_count')." Cities</a>";
			}//end if server_id
			echo ' | <a href="'.($_REQUEST['server_id']?'/server/'.$_REQUEST['server_id']:'').'/user/'.$LOGIN_DATA['user_id'].'">Your Profile</a>';
			echo ' | <a href="/login/out.php">Logout</a>';
		}//end if logged in
		?>
      | <a href="https://pibb.com/go/theproject">Discuss</a>
      | <a href="/faq/">FAQ</a>
		|
		<br />
		<?php
			if($server) {
				$day = $server->getPreviousDay();
				$time_left = round(abs((time()-$server->getDayLength()) - $day)/60,2);
				echo '<span id="day-left">'.$time_left.'</span> minutes left in the day';
				?>
				<script type="text/javascript">
				//<![CDATA[
					var day_left_id = '';
					function update_day_left() {
						var block = document.getElementById('day-left');
						block.innerHTML = Math.round((block.innerHTML - (1/60))*100)/100;
						if(block.innerHTML < 0) block.innerHTML = 0;
						day_left_id = setTimeout("update_day_left()", 1100);
					}//end function update_day_left
					day_left_id = setTimeout("update_day_left()", 1100);
				//]]>
				</script>
				<?php
			}//end if server
		?>
	</div>
	<div>
   <h1 style="display:inline;"><a href="/<?php if($_REQUEST['server_id']) echo 'server/'.$_REQUEST['server_id'];  ?>" rel="home"><img style="width:200px;" src="/images/theprojectlogotext.jpg" alt="The Project" /></a></h1>
	<?php if($_REQUEST['server_id']) echo '<h2 style="display:inline;">'.htmlentities(ucwords($server->getName())).' Server</h2>'; ?>
	</div>
