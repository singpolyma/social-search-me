   <div style="position:absolute;top:1em;right:2em;text-align:right;">
		<?php
		require_once dirname(__FILE__).'/server.php';
		if($LOGIN_DATA['user_id']) {
			echo '<span id="statistics">';
			require_once dirname(__FILE__).'/../statistics.php';
			if($_REQUEST['server_id'] && !$server) $server = new server($_REQUEST['server_id']);
			echo '</span>';
			echo ' | <a href="'.($_REQUEST['server_id']?'/server/'.$_REQUEST['server_id']:'').'/user/'.$LOGIN_DATA['user_id'].'"><img src="/images/user.png" alt="Your Profile" title="Your Profile" /></a> ';
			echo ' &nbsp;  <a href="/login/out.php"><img src="/images/door_out.png" alt="Logout" title="Logout" /></a> ';
		}//end if logged in
		?>
      &nbsp; <a href="http://pibb.com/go/theproject"><img src="/images/comment.png" alt="Discuss" title="Discuss" /></a>
      &nbsp; <a href="/faq/"><img src="/images/information.png" alt="FAQ" title="FAQ" /></a>
		<br />
		<?php
			if($server) {
				if(!$server->getWeekLength() || $server->getPreviousWeek() < time()) {
					$day = $server->getPreviousDay();
					$time_left = round(abs((time()-$server->getDayLength()) - $day)/60,2);
					echo '<span id="day-left">'.$time_left.'</span> minutes left in the day';
					?>
      			<script type="text/javascript" src="/include/prototype.js"></script>
			      <script type="text/javascript" src="/include/ajax.js"></script>
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
	
		      	   var statistics_id = '';
   			      function update_statistics() {
	   		         new Ajax.Updater('statistics', '/statistics.php?server_id=<?php echo $server->getID(); ?>');
	   	   	      new Ajax.Updater('server-message', '/server-message.php?server_id=<?php echo $server->getID(); ?>');
      	   		   statistics_id = setTimeout("update_statistics()", 1000*8);
		   	      }//end function update_day_left
			         statistics_id = setTimeout("update_statistics()", 1000*8)
	
					//]]>
					</script>
					<?php
					$week = $server->getPreviousWeek();
					$time_left = ceil((abs((time()-$server->getWeekLength()) - $week)/$server->getDayLength()));
					if($server->getWeekLength()) echo ' | '.$time_left.' days left in week';
				}//end if has started
			}//end if server
		?>
	</div>
	<?php
	if($LOGIN_DATA['user_id'] && $_REQUEST['server_id']) {
	echo '<div style="font-weight:bold;font-size:0.8em;position:absolute;top:1.6em;left:250px;width:24em;" id="server-message">';
	require_once dirname(__FILE__).'/../server-message.php';
	echo '</div> ';
	}
	?>
	<div style="position:absolute;top:5em;left:250px;">
		<object style="border-width:0px;width:490px;height:65px;overflow:hidden;" data="/ads.html" type="text/html"></object>
	</div>
	<?php if($server) : ?>
	<div style="position:absolute;top:10em;left:240px;">
		<a href="/">&lt;Switch Server&gt;</a>
	</div>
	<?php endif; ?>
	<div>
   <h1 style="display:inline;"><a href="/<?php if($_REQUEST['server_id']) echo 'server/'.$_REQUEST['server_id'];  ?>" rel="home"><img style="width:200px;" src="/images/theprojectlogotext.jpg" alt="The Project" /></a></h1>
	<?php if($_REQUEST['server_id']) echo '<h2 style="display:inline;">'.htmlentities(ucwords($server->getName())).' Server</h2> [<a href="/server/'.$_REQUEST['server_id'].'/rules">rules</a>]'; ?>
	</div>
<?php
	if($server && $server->getWeekLength() && $server->getPreviousWeek() > time()) {
		die('<div style="text-align:center;margin-top:2em;">Server restarted, come back in '.round(($server->getPreviousWeek()-time())/(60*60),2).' hours.</div></body></html>');
	}//end if
?>
