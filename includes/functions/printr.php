<?php
function printr ( $object , $name = '' ) {
	print "<div style='margin: 10px; padding: 5px; border: 1px dashed #888888;'>
		<strong style='display: block; padding: 5px; background: #FFFF88'>$name</strong>
	";
	if ( is_array ( $object ) ) {
		print ( '<pre>' );
		print_r ( $object ) ; 
		print ( '</pre>' ) ;
	} else {
		var_dump ( $object ) ;
	}
	print "</div>";
}
?>