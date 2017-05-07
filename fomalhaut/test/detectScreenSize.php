<?php

// get the l for length, and w for width
$l = $_REQUEST["l"];
$w = $_REQUEST["w"];

$hint = $l.$w;

// Output "no suggestion" if no hint was found or output correct values 
echo $hint === "" ? "no suggestion" : $hint;
?>