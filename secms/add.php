<?php

require dirname(__FILE__).'/password.php';
require dirname(__FILE__).'/event.php';

if(count($_POST)) {
	$event = new Event($_POST);
	$event->save();
	$message = 'Event saved!';
}//end if GET

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" 
   "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Add Event - Simple Event CMS</title>
		<style type="text/css">
			label {
				display: block;
				float: left;
				width: 100px;
				clear: both;
			}
			input, textarea {
				display: block;
				margin-left: 150px;
			}
		</style>
	</head>
	<body>
		<h1>Simple Event CMS</h1>
		<b><?php echo $message; ?></b>
		<form method="post" action=""><div>
			<h2>Add Event</h2>
			<label for="summary">Title:</label>
				<input type="text" name="summary" id="summary" />
			<label for="location">Location:</label>
				<input type="text" name="location" id="location" />
			<label for="url">Website:</label>
				<input type="text" name="url" id="url" />
			<label for="dtstart">Date and Time:</label>
				<input type="text" name="dtstart" id="dtstart" />
			<label for="timezone">Timezone:</label>
				<input type="text" name="timezone" id="timezone" />
			<label for="duration">Duration:</label>
				<input type="text" name="duration" id="duration" />
			<label for="rrule" title="seconds|daily|weekly">Repeat:</label>
				<input type="text" name="rrule" id="rrule" />
			<label for="description">Description:</label>
				<textarea name="description" id="description" rows="4" cols="21"></textarea>
			<input type="submit" value="Save &raquo;" />
		</div></form>
		<script type="text/javascript">
			document.getElementById('timezone').value = (new Date().getTimezoneOffset()/60)*-1;
		</script>
	</body>
</html>
