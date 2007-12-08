<?php

/*
    test_harness.php - test harness for ABM Locator

    Copyright (C) 2007  Denver Gingerich

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

$banks = array('bmo', 'cibc', 'citizensbank', 'pc', 'rbc', /*'scotiabank',*/ 'td');

$city = $_REQUEST["city"];
$province = $_REQUEST["province"];

$list = "			<ol>\n";
foreach($banks as $bank) {
	require_once $bank.'.php';
	$func = 'bank_'.$bank;
	$results = $func($province, $city);

	foreach ($results as $result) {
		$gmapjs .= "geocoder.getLatLng('".$result["address"]."', function(point) { plotPoint(point, '".$result["name"].' ['.strtoupper($bank).']<br />'.$result["address"]."', '".$bank."'); } );\n";

		//TODO?  Would be teh awesome to server-side geocode and include the uf for that (/ XML scrAPI?, meh)
		//TODO: Style this list more better
		$list .= '				<li class="vcard">'."\n";
		$list .= '					<dl>'."\n";
		$list .= '						<dt>Name</dt>'."\n";
		$list .= '							<dd class="fn">'.htmlentities($result['name']).'</dd>'."\n";
		$list .= '						<dt>Address</dt>'."\n";
		$list .= '							<dd class="adr">'.htmlentities($result['address']).'</dd>'."\n";//invalid adr, need to mark up the parts of the address -- better scraping?
		if($result['telephone']) {
			$list .= '						<dt>Telephone</dt>'."\n";
			$list .= '							<dd class="tel">'.htmlentities($result['telephone']).'</dd>'."\n";
		}//end if telephone
		if($result['transit_no']) {
			$list .= '						<dt>Transit Number</dt>'."\n";
			$list .= '							<dd>'.htmlentities($result['transit_no']).'</dd>'."\n";
		}//end if transit_no
		$list .= '					</dl>'."\n";
		$list .= '				</li>'."\n";
	}//end foreach results
}//end foreach banks
$list .= "			</ol>\n";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>ABM Locator results</title>
    <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAt9BzDNuc_4OvBtEYNdUYFRSWPNx79sozGDdpidotaqyvbgZJbhTv3tM7JWzEJRDrKZcejCs9gD9-Ww"
      type="text/javascript"></script>
    <script type="text/javascript">
	//<![CDATA[

    var map = null;
    var geocoder = null;

    function plotPoint(point, text, bank) {
      if (!point) {
        //try { console.log(text + " not found"); } catch(ex) {}
      } else {
			var myIcon = new GIcon(G_DEFAULT_ICON);
			myIcon.image = 'img/'+bank+'.png';//using colorized famfamfam silk
			var marker = new GMarker(point,{icon:myIcon}); 
         map.addOverlay(marker);
			GEvent.addListener(marker, "click", function() {
			marker.openInfoWindowHtml(text);
        });
	      map.setCenter(point, 11);
      }
    }//end function plotPoint

    function load() {
      if (GBrowserIsCompatible()) {
      	map = new GMap2(document.getElementById("map"));
	      map.setCenter(new GLatLng(37.4419, -122.1419), 13);
			map.addControl(new GLargeMapControl());
			map.addControl(new GMapTypeControl());
    		geocoder = new GClientGeocoder();

			<?php echo $gmapjs; ?>

      }//end if GBrowserIsCompatible
    }//end function load

	//]]>
    </script>
  </head>
	<body onload="load()" onunload="GUnload()">
		<div id="map" style="width: 800px; height: 500px"></div>
		<?php echo $list; ?>
	</body>
</html>
