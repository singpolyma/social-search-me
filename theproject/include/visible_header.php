   <div style="position:absolute;top:1em;right:2em;text-align:right;">
		<?php
		require_once dirname(__FILE__).'/server.php';
		if($LOGIN_DATA['user_id']) {
			echo '<span id="statistics">';
			require_once dirname(__FILE__).'/../statistics.php';
			if($_REQUEST['server_id'] && !$server) $server = new server($_REQUEST['server_id']);
			echo '</span>';
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
      	   	   statistics_id = setTimeout("update_statistics()", 1000*10);
	   	      }//end function update_day_left
		         statistics_id = setTimeout("update_statistics()", 1000*10)

				//]]>
				</script>
				<?php
				$week = $server->getPreviousWeek();
				$time_left = ceil((abs((time()-$server->getWeekLength()) - $week)/$server->getDayLength()));
				if($server->getWeekLength()) echo ' | '.$time_left.' days left in week';
			}//end if server
		?>
	</div>
	<div style="position:absolute;top:5em;left:250px;">
		<object style="border-width:0px;width:490px;height:70px;overflow:hidden;" data="/ads.html" type="text/html"></object>
	</div>
	<div style="position:absolute;top:10em;left:240px;">
		<a href="/">&lt;Switch Server&gt;</a>
	</div>
	<div>
   <h1 style="display:inline;"><a href="/<?php if($_REQUEST['server_id']) echo 'server/'.$_REQUEST['server_id'];  ?>" rel="home"><img style="width:200px;" src="/images/theprojectlogotext.jpg" alt="The Project" /></a></h1>
	<?php if($_REQUEST['server_id']) echo '<h2 style="display:inline;">'.htmlentities(ucwords($server->getName())).' Server</h2>'; ?>
	</div>
