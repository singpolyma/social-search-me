		<ol class="hfeed">
		<?php
			require dirname(__FILE__).'/event.php';
			$events = Event::get_events();
			$c = 1;
			foreach($events as $event) {
				echo '<li class="hentry vevent">';
				echo $event;
				echo '</li>';
				$c++;
				if($_GET['num'] && $c > $_GET['num']) break;
			}//end foreach events
		?>
		</ol>
