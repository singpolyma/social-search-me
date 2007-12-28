<?php

require_once dirname(__FILE__).'/xhtmlSite.php';
$xhtmlSite = new xhtmlSite();
$xhtmlSite->startDocument();

require_once dirname(__FILE__).'/processCookie.php';

require_once dirname(__FILE__).'/cron.php';

?>
