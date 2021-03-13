<?php
	
	include "options_functions.php";
	
	// default year length is 365 days; can be set to 360 (or any other number)
	$days_in_year = isset($_GET["days_in_year"]) ? $_GET["days_in_year"] : 365;
	
	$S = isset($_GET["S"]) ? $_GET["S"] : 100;			//current asset price
	$K = isset($_GET["K"]) ? $_GET["K"] : 100;			//option exercise price
	$r = isset($_GET["r"]) ? $_GET["r"]/100 : 1/100;	//short-term risk-free interest rate
	$t = isset($_GET["t"]) ? $_GET["t"] : 9;			//days to expiration
	$t = $t / $days_in_year;
	$s = isset($_GET["s"]) ? $_GET["s"]/100 : 80/100;	//volatility
	
	$val = black_scholes($S,$K,$r,$t,$s,'both');
	
	$sen_S = sensitivity_V_wrt_S($S,$K,$r,$t,$s,'both');
	$sen_vol = sensitivity_V_wrt_vol($S,$K,$r,$t,$s,'both');
	$sen_t = sensitivity_V_wrt_t($S,$K,$r,$t,$s,'both');
	
	$val["sensitivity_V_wrt_S"] = $sen_S;
	$val["sensitivity_V_wrt_vol"] = $sen_vol;
	$val["sensitivity_V_wrt_t"] = $sen_t;
	$val["args"] = [
		"S"=>$S,
		"K"=>$K,
		"r"=>$r,
		"t"=>$t,
		"s"=>$s
	];
	
	echo json_encode($val);
	
?>