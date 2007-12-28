<?php
	class xhtmlSite {
		var $accepts;
		function xhtmlSite() {
			if (stristr($_SERVER['HTTP_ACCEPT'],'application/xhtml+xml') )
				$this->accepts = true;
			else
				$this->accepts = false;
		}
		function startDocument($override=false) {
			if ($this->accepts && !$override) {
				header('Content-type: application/xhtml+xml');
				echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
			} else {
				header('Content-type: text/html; charset=utf-8');
			}
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">'."\n";
			echo '<html xmlns="http://www.w3.org/1999/xhtml"';
			echo '>'."\n";
		}
		function metaType() {
			if (stristr($_SERVER['HTTP_ACCEPT'],'application/xhtml+xml'))
				echo '<meta http-equiv="Content-Type" content="application/xhtml+xml" />'."\n";
			else
				echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'."\n";
		}
	}
?>
