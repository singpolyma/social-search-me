
		<div id="header" <?php if(!$_GET['q']) echo 'style="text-align:right;"';?>>
		<?php
		if($_GET['q'])
			echo '<h1>Results for "'.htmlspecialchars($_GET['q']).'"</h1>';
		else
			echo '<a href="/profile/">&laquo; Back to search</a>';
		$count = mysql_fetch_assoc(mysql_query("SELECT count(*) AS count FROM people WHERE `given-name`!='' OR fn!=''"));
		echo ' <span>('.$count['count'].' profiles indexed)</span>';
		?>

		</div>

