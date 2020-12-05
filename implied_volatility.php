<?php
	
	include "options_functions.php";

	$S = isset($_GET["S"]) ? $_GET["S"] : 50;		//current stock price
	$K = isset($_GET["K"]) ? $_GET["K"] : 40;		//strike price
	$V = isset($_GET["V"]) ? $_GET["V"] : 10;		//option price
	$r = isset($_GET["r"]) ? $_GET["r"] : .0094;	//short-term risk-free interest rate
	$t = isset($_GET["t"]) ? $_GET["t"] : 10/365;	//time to expiration

	$vol = implied_volatility($S,$K,$V,$r,$t,'both');
	
	echo json_encode($vol);
	
?>