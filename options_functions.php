<?php

	//default type should be 'both'

	function black_scholes($S,$K,$r,$t,$s,$type='call') {

		if ($type == 'call') {
			return ['call' => black_scholes_call($S,$K,$r,$t,$s)];
		} else if ($type == 'put') {
			return ['put' => black_scholes_put($S,$K,$r,$t,$s)];
		} else if ($type == 'both') {
			return [
				'call' => black_scholes_call($S,$K,$r,$t,$s),
				'put' => black_scholes_put($S,$K,$r,$t,$s)
			];
		} else {
			trigger_error("parameter 'type' in function 'black_scholes' accepts only the values: 'call', 'put', or 'both'. '" . $type . "' was provided.");
		}
	}

	function erf($x) {

		/*$p = 0.47047;
		$a1 = 0.3480242;
		$a2 = -0.0958798;
		$a3 = 0.7478556;
		$t = 1/(1+$p*$x);
		$er =  1 - exp(-pow($x,2))*($a1*$t + $a2*pow($t,2) + $a3*pow($t,3));*/

		$p = 0.3275911;
		$a1 = 0.254829592;
		$a2 = -0.284496736;
		$a3 = 1.421413741;
		$a4 = -1.453152027;
		$a5 = 1.061405429;

		$t = 1/(1+$p*$x);

		$er = 1 -
			exp(-pow($x,2)) *
			(
				$a1*$t +
				$a2*pow($t,2) +
				$a3*pow($t,3) +
				$a4*pow($t,4) +
				$a5*pow($t,5)
			);

		return $er;
	}

	function phi($x) {
		return exp(-pow($x,2)/2) / sqrt(2 * pi());
	}

	function is_negative($x) {
		return $x < 0 ? true : false;
	}

	function normal_cdf($z) {

		$r = 0.5 + erf(abs($z) / sqrt(2)) / 2;

		return is_negative($z) ? 1 - $r : $r;
	}

	function black_scholes_d1($S,$K,$r,$t,$s) {
		return (log($S/$K,exp(1)) + ($r + pow($s,2)/2) * $t) / ($s * sqrt($t));
	}

	function black_scholes_d2($s,$t,$d1) {
		return $d1 - $s*sqrt($t);
	}

	function black_scholes_call($S,$K,$r,$t,$s) {

		$d1 = black_scholes_d1($S,$K,$r,$t,$s);
		$n1 = normal_cdf($d1);

		$v0 = option_value($S,$K,$r,$t,$s,'call');

		return [
			"value" => $v0,
			"delta" => option_delta($S,$K,$r,$t,$s,'call'),
			"gamma" => option_gamma($S,$t,$s,$d1),
			"theta" => option_theta($S,$K,$r,$t,$s,'call'),
			"vega"  => option_vega ($S,$K,$r,$t,$s),
			"rho"   => option_rho  ($S,$K,$r,$t,$s,'call')
		];
	}

	function black_scholes_put($S,$K,$r,$t,$s) {

		$d1 = black_scholes_d1($S,$K,$r,$t,$s);
		$n1 = normal_cdf($d1);

		$v0 = option_value($S,$K,$r,$t,$s,'put');

		return [
			"value" => $v0,
			"delta" => option_delta($S,$K,$r,$t,$s,'put'),
			"gamma" => option_gamma($S,$t,$s,$d1),
			"theta" => option_theta($S,$K,$r,$t,$s,'put'),
			"vega"  => option_vega ($S,$K,$r,$t,$s),
			"rho"   => option_rho  ($S,$K,$r,$t,$s,'put')
		];
	}

	function option_value($S,$K,$r,$t,$s,$type='call') {

		$d1 = black_scholes_d1($S,$K,$r,$t,$s);
		$d2 = black_scholes_d2($s,$t,$d1);

		if ($type == 'call') {
			$n1 = normal_cdf($d1);
			$n2 = normal_cdf($d2);
			$v = $S * $n1 - $K * exp(-$r * $t) * $n2;
		} else if ($type == 'put') {
			$n1p = normal_cdf(-$d1);
			$n2p = normal_cdf(-$d2);
			$v = $K * exp(-$r * $t) * $n2p - $S * $n1p;
		} else {
			trigger_error("option value calculation requires specification of 'call' or 'put' for parameter 'type'.");
		}

		return $v;
	}

	function option_delta($S,$K,$r,$t,$s,$type='call') {

		$d1 = black_scholes_d1($S,$K,$r,$t,$s);
		$n1 = normal_cdf($d1);

		if ($type == 'call') {
			$v = $n1;
		} else if ($type == 'put') {
			$v = $n1 - 1;
		} else {
			trigger_error("option value calculation requires specification of 'call' or 'put' for parameter 'type'.");
		}

		return $v;
	}

	function option_gamma($S,$t,$s,$d1,$type='call') {
		// identical for calls and puts but handles $type just in case
		return phi($d1)/($S*$s*sqrt($t));
	}

	function option_theta($S,$K,$r,$t,$s,$type='call') {

		$d1 = black_scholes_d1($S,$K,$r,$t,$s);
		$d2 = black_scholes_d2($s,$t,$d1);

		$v0 = -$S * phi($d1) * $s / (2 * sqrt($t));

		if ($type == 'call') {
			$v1 = -$r * $K * exp(-$r * $t) * normal_cdf($d2);
		} else if ($type == 'put') {
			$v1 = $r * $K * exp(-$r * $t) * normal_cdf(-$d2);
		} else {
			trigger_error("theta calculation requires specification of 'call' or 'put' for parameter 'type'.");
		}

		return ($v0 + $v1) / 365;
	}

	function option_vega($S,$K,$r,$t,$s,$type='call') {
		// identical for calls and puts but handles $type just in case

		$d1 = black_scholes_d1($S,$K,$r,$t,$s);
		return $S * phi($d1) * sqrt($t) / 100;
	}

	function option_rho($S,$K,$r,$t,$s,$type='call') {

		$d1 = black_scholes_d1($S,$K,$r,$t,$s);
		$d2 = black_scholes_d2($s,$t,$d1);

		if ($type == 'call') {
			$v = $K * $t * exp(-$r * $t) * normal_cdf($d2);
		} else if ($type == 'put') {
			$v = -$K * $t * exp(-$r * $t) * normal_cdf(-$d2);
		} else {
			trigger_error("theta calculation requires specification of 'call' or 'put' for parameter 'type'.");
		}

		return $v/100;
	}

	function sensitivity_V_wrt_S($S,$K,$r,$t,$s,$type='call',$increment=0.1,$increments_plus_minus=40) {

		$total = [];

		if ($type == 'call') {
			$call_total = [];
			for ($i=$increments_plus_minus*-1; $i<$increments_plus_minus; $i++) {
				$test_S = $S + $increment * $i;
				$v = option_value($test_S,$K,$r,$t,$s,'call');
				array_push($call_total,["S"=>$test_S,"V"=>$v]);
			}

			$total["call"] = $call_total;

		} else if ($type == 'put') {
			$put_total = [];
			for ($i=$increments_plus_minus*-1; $i<$increments_plus_minus; $i++) {
				$test_S = $S + $increment * $i;
				$v = option_value($test_S,$K,$r,$t,$s,'put');
				array_push($put_total,['S'=>$test_S,'V'=>$v]);
			}

			$total['put'] = $put_total;

		} else if ($type == 'both') {
			$total['call'] = sensitivity_V_wrt_S($S,$K,$r,$t,$s,'call',$increment,$increments_plus_minus)['call'];
			$total['put'] = sensitivity_V_wrt_S($S,$K,$r,$t,$s,'put',$increment,$increments_plus_minus)['put'];

		} else {
			trigger_error("sensitivity calculation requires specification of 'call' or 'put' for parameter 'type'.");
		}

		return $total;
	}

	function sensitivity_V_wrt_vol($S,$K,$r,$t,$s,$type='call',$increment=0.01,$increments_plus_minus=40) {

		$total = [];

		if ($type == 'call') {

			$call_total = [];
			for ($i=$increments_plus_minus*-1; $i<$increments_plus_minus; $i++) {
				$test_s = $s + $increment * $i;
				$v = option_value($S,$K,$r,$t,$test_s,'call');
				array_push($call_total,['vol'=>$test_s,'V'=>$v]);
			}

			$total['call'] = $call_total;

		} else if ($type == 'put') {
			$put_total = [];
			for ($i=$increments_plus_minus*-1; $i<$increments_plus_minus; $i++) {
				$test_s = $s + $increment * $i;
				$v = option_value($S,$K,$r,$t,$test_s,'put');
				array_push($put_total,['vol'=>$test_s,'V'=>$v]);
			}

			$total['put'] = $put_total;

		} else if ($type == 'both') {
			$total['call'] = sensitivity_V_wrt_vol($S,$K,$r,$t,$s,'call',$increment,$increments_plus_minus)['call'];
			$total['put'] = sensitivity_V_wrt_vol($S,$K,$r,$t,$s,'put',$increment,$increments_plus_minus)['put'];

		} else {
			trigger_error("sensitivity calculation requires specification of 'call' or 'put' for parameter 'type'.");
		}

		return $total;
	}

	function sensitivity_V_wrt_t($S,$K,$r,$t,$s,$type='call') {

		global $days_in_year;
		$t_days = round($t * $days_in_year);

		$total = [];

		if ($type == 'call') {

			$call_total = [];

			while ($t_days > 1) {
				$test_t = ($t_days - 1) / $days_in_year;
				$v = option_value($S,$K,$r,$test_t,$s,'call');
				array_push($call_total,['t'=>$test_t*$days_in_year,'V'=>$v]);
				$t_days -= 1;
			}

			$total['call'] = $call_total;

		} else if ($type == 'put') {

			$put_total = [];

			while ($t_days > 1) {
				$test_t = ($t_days - 1) / $days_in_year;
				$v = option_value($S,$K,$r,$test_t,$s,'put');
				array_push($put_total,['t'=>$test_t*$days_in_year,'V'=>$v]);
				$t_days -= 1;
			}

			$total['put'] = $put_total;

		} else if ($type == 'both') {
			$total['call'] = sensitivity_V_wrt_t($S,$K,$r,$t,$s,'call')['call'];
			$total['put'] = sensitivity_V_wrt_t($S,$K,$r,$t,$s,'put')['put'];

		} else {
			trigger_error("sensitivity calculation requires specification of 'call' or 'put' for parameter 'type'.");
		}

		return $total;
	}

	function implied_volatility($S,$K,$V,$r,$t,$type='both',$s_init=1.0,$precision=0.0001,$increment=0.1) {

		$s = $s_init;

		if ($type == 'call') {
			return ['call' => implied_volatility_call($S,$K,$V,$r,$t,$s,$precision,$increment)];
		} else if ($type == 'put') {
			return ['put' => implied_volatility_put($S,$K,$V,$r,$t,$s,$precision,$increment)];
		} else if ($type == 'both') {
			return [
				'call' => implied_volatility_call($S,$K,$V,$r,$t,$s,$precision,$increment),
				'put' => implied_volatility_put($S,$K,$V,$r,$t,$s,$precision,$increment)
			];
		} else {
			trigger_error("parameter 'type' in function 'implied_volatility' accepts only the values: 'call', 'put', or 'both'. '" . $type . "' was provided.");
		}
	}

	// these don't need to be two separate functions
	function implied_volatility_call($S,$K,$V,$r,$t,$s=1.0,$precision=0.0001,$increment=0.1,$iterations=0) {

		$d1 = black_scholes_d1($S,$K,$r,$t,$s);
		$d2 = black_scholes_d2($s,$t,$d1);

		$n1 = normal_cdf($d1);
		$n2 = normal_cdf($d2);
		$v = $S * $n1 - $K * exp(-$r * $t) * $n2;

		//echo abs($V-$v); echo "\r\n"; echo $iterations; echo "\r\n\r\n";

		if ((abs($V-$v) <= $precision) || $iterations == 10000) {

			return [
				'call_iv' => $s,
				'iterations' => $iterations
			];
		} else {
			$s = $s + $increment * ($V/$v - 1);

			return implied_volatility_call($S,$K,$V,$r,$t,$s,$precision,$increment,$iterations+1);
		}
	}

	function implied_volatility_put($S,$K,$V,$r,$t,$s=1.0,$precision=0.0001,$increment=0.1,$iterations=0) {

		$d1 = black_scholes_d1($S,$K,$r,$t,$s);
		$d2 = black_scholes_d2($s,$t,$d1);

		$n1p = normal_cdf(-$d1);
		$n2p = normal_cdf(-$d2);
		$v = $K * exp(-$r * $t) * $n2p - $S * $n1p;

		//echo abs($V-$v); echo "\r\n"; echo $iterations; echo "\r\n\r\n";

		if ((abs($V-$v) <= $precision) || $iterations == 10000) {

			return [
				'put_iv' => $s,
				'iterations' => $iterations
			];

		} else {

			$s = $s + $increment * ($V/$v - 1);

			return implied_volatility_put($S,$K,$V,$r,$t,$s,$precision,$increment,$iterations+1);
		}
	}

?>
