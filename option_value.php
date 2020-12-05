<?php
	
	
	//ini_set('display_errors', 1);
	//ini_set('display_startup_errors', 1);
	//error_reporting(E_ALL);
	
	include "options_functions.php";
	
	$days_in_year = 360;

	$S = isset($_GET["S"]) ? $_GET["S"] : 57.63;	//current stock price
	$K = isset($_GET["K"]) ? $_GET["K"] : 40;		//strike price
	$r = isset($_GET["r"]) ? $_GET["r"] : .0094;	//short-term risk-free interest rate
	$t = isset($_GET["t"]) ? $_GET["t"] : 9;		//time to expiration
	$s = isset($_GET["s"]) ? $_GET["s"] : 1.942;	//volatility (sd of S)
	
	$t = $t / $days_in_year;
	
	$val = black_scholes($S,$K,$r,$t,$s,'both');
	
	$sen_S = sensitivity_V_wrt_S($S,$K,$r,$t,$s,'both');
	$sen_vol = sensitivity_V_wrt_vol($S,$K,$r,$t,$s,'both');
	$sen_t = sensitivity_V_wrt_t($S,$K,$r,$t,$s,'both');
	
	$val["sensitivity_V_wrt_S"] = $sen_S;
	$val["sensitivity_V_wrt_vol"] = $sen_vol;
	$val["sensitivity_V_wrt_t"] = $sen_t;
	
	echo json_encode($val);
	//echo erf(1);
?>