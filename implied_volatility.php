<?php
	
	include "options_functions.php";
	
	$days_in_year = isset($_GET["days_in_year"]) ? $_GET["days_in_year"] : 365;

	$S = isset($_GET["S"]) ? $_GET["S"] : 10;			//current asset price
	$K = isset($_GET["K"]) ? $_GET["K"] : 10;			//option exercise price
	$r = isset($_GET["r"]) ? $_GET["r"]/100 : 1/100;	//short-term risk-free interest rate
	$t = isset($_GET["t"]) ? $_GET["t"] : 10;			//days to expiration
	$t = $t / $days_in_year;
	$V = isset($_GET["V"]) ? $_GET["V"] : 10;			//option premium

	$vol = implied_volatility($S,$K,$V,$r,$t,'both');
	
	echo json_encode($vol);
	
?>