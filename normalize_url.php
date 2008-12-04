<?php

	function normalize_url( $url )
	{
		 $url = trim( $url );
		 
		 $parts = parse_url( $url );
		 $scheme = isset( $parts['scheme'] ) ? $parts['scheme'] : null;

		 if( !$scheme )
		 {
			  $url = 'http://' . $url;
			  $parts = parse_url( $url );
		 }

		 $path = isset( $parts['path'] ) ? $parts['path'] : null;
		 
		 if( !$path )
			  $url .= '/';
		 
		 return $url;
	}

?>
