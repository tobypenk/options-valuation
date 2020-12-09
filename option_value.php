<?php
	
	include "options_functions.php";
	
	$days_in_year = isset($_GET["days_in_year"]) ? $_GET["days_in_year"] : 365;

	$S = isset($_GET["S"]) ? $_GET["S"] : 100;		//current asset price
	$K = isset($_GET["K"]) ? $_GET["K"] : 100;		//option exercise price
	$r = isset($_GET["r"]) ? $_GET["r"] : 1/100;	//short-term risk-free interest rate
	$t = isset($_GET["t"]) ? $_GET["t"] : 9;		//days to expiration
	$s = isset($_GET["s"]) ? $_GET["s"] : 80/100;	//volatility
	
	$t = $t / $days_in_year;
	
	$val = black_scholes($S,$K,$r,$t,$s,'both');
	
	$sen_S = sensitivity_V_wrt_S($S,$K,$r,$t,$s,'both');
	$sen_vol = sensitivity_V_wrt_vol($S,$K,$r,$t,$s,'both');
	$sen_t = sensitivity_V_wrt_t($S,$K,$r,$t,$s,'both');
	
	$val["sensitivity_V_wrt_S"] = $sen_S;
	$val["sensitivity_V_wrt_vol"] = $sen_vol;
	$val["sensitivity_V_wrt_t"] = $sen_t;
	
	echo json_encode($val);
?>