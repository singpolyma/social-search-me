
		<div id="header">
		<?php
		echo '<div style="float:right;">';
		if($_GET['q'] || $_GET['id'] || $_GET['url'])
			echo '<a href="/profile/">&laquo; Back to search</a>';
		$count = mysql_fetch_assoc(mysql_query("SELECT count(*) AS count FROM people WHERE `given-name`!='' OR fn!=''"));
		echo ' <span>('.$count['count'].' profiles indexed)</span></div>';
		if($_GET['q'])
			echo '<h1>Social web results for "'.htmlspecialchars($_GET['q']).'"</h1>';
		else
			echo '<h1>Social Search Me</h1>';
		?>

		</div>

