<?php
function uniqueArray($array) { 
 // Get unique elts as keys in assoc. array 
 for ($i=0,$n=count($array, 1);$i<$n;$i++) 
     $u_array[$array[$i]] = 1; 

 // Copy keys only into another array 
 reset($u_array, 1); 
 for ($i=0,$n=count($u_array, 1);$i<$n;$i++) { 
     $unduplicated_array[] = key($u_array, 1); 
     next($u_array, 1); 
 } 
 return $unduplicated_array; 
} 
?>