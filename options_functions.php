<?php

	function black_scholes($S,$K,$r,$t,$s,$type='both') {
		/*
			api wrapper function for black scholes call and put valuation
			
			parameters:
				S: asset price
				K: option exercise / strike price
				r: prevailing risk-free interest rate
				t: years to expiration (days to expiration / 365 in the API)
				s: volatility of the underlying asset
				type: ['call','put','both'] - what type of valuation to perform
				
			returns:
				option value and first- and some second-order greeks (black scholes valuation object)
		*/

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
		
		/*
			error function (for use calculating normal cdf)
			
			parameters: 
				x: z-score
				
			returns: 
				approximate error function output value for x (float)
		*/
		
		if (is_negative($x)) {
			return tau($x) - 1;
		} else {
			return 1 - tau($x);
		}
	}
	
	function t($x) {
		
		/*
			helper for error function approximation (for use calculating normal cdf)
			parameters: 
				x: z-score
				
			returns: 
				multiplier for normal cdf approximator (float)
		*/
		
		return 1 / (1 + 0.5*abs($x));
	}
	
	function tau($x) {
		
		/*
			numeric approximation for error function (for use calculating normal cdf)
			parameters:
				x: z-score
				
			returns:
				raw error function (this is an odd function so it is passed to erf()) (float)
		*/
		
		$t = t($x);
		$a1 = -1.26551223;
		$a2 = 1.00002368;
		$a3 = 0.37409196;
		$a4 = 0.09678418;
		$a5 = -0.18628806;
		$a6 = 0.27886807;
		$a7 = -1.13520398;
		$a8 = 1.48851587;
		$a9 = -0.82215223;
		$a10 = 0.17087277;
		
		return $t * exp(
			-pow($x,2) +
			$a1 +
			$a2 * pow($t,1) + 
			$a3 * pow($t,2) + 
			$a4 * pow($t,3) + 
			$a5 * pow($t,4) + 
			$a6 * pow($t,5) + 
			$a7 * pow($t,6) + 
			$a8 * pow($t,7) + 
			$a9 * pow($t,8) + 
			$a10 * pow($t,9)
		);
	}
	
	function phi($x) {
		
		/*
			phi(x) = e^(-x^2/2 - dt)
			current implementation ignores dividends so dxt is 0
			
			parameters:
				x: d1 from black scholes model
				
			returns:
				phi(x) (float)
		*/
		
		return exp(-pow($x,2)/2) / sqrt(2 * pi());
	}

	function is_negative($x) {
		/*
			helper function wrapping negativity test
			
			parameters:
				x: any number
				
			returns:
				true if x < 0, false otherwise (bool)
		*/
		return $x < 0 ? true : false;
	}

	function normal_cdf($z) {
		
		/*
			
			numeric approximation of normal cdf
			
			parameters: 
				z: a z-score
				
			returns: 
				approximate normal cdf value for the given z-score (float)
		*/
		
		return (1+erf($z/sqrt(2)))/2;
	}

	function black_scholes_d1($S,$K,$r,$t,$s) {
		
		/*
			calculate black scholes d1, the z-score for the stock's future value iff S > K at expiration
				normal_cdf(d1) gives the stock's future value iff S > K at expiration
			
			parameters:
				S: current asset price
				K: option strike / exercise price
				r: prevailing risk-free interest rate
				t: years to expiration (days / 365)
				s: volatility of the underlying asset
				
			returns:
				d1 (float)
		*/
		
		return (log($S/$K,exp(1)) + ($r + pow($s,2)/2) * $t) / ($s * sqrt($t));
	}

	function black_scholes_d2($s,$t,$d1) {
		
		/*
			calculate black scholes d2, the z-score of the probability the option will be exercised
				normal_cdf(d2) gives the probability of exercise
				
			parameters:
				s: volatility of the underlying asset
				t: years to expiration (days / 365)
				d1: z-score for future value if S > K at expiration
				
			returns:
				d2 (float)
		*/
		
		return $d1 - $s*sqrt($t);
	}

	function black_scholes_call($S,$K,$r,$t,$s) {
		
		/*
			internal function for calculating black scholes call
			
			parameters:
				S: asset price
				K: option exercise / strike price
				r: prevailing risk-free interest rate
				t: years to expiration (days to expiration / 365 in the API)
				s: volatility of the underlying asset
			
			returns:
				option value and first- and some second-order greeks (black scholes call object)
			
		*/

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
		
		/*
			internal function for calculating black scholes put
			
			parameters:
				S: asset price
				K: option exercise / strike price
				r: prevailing risk-free interest rate
				t: years to expiration (days to expiration / 365 in the API)
				s: volatility of the underlying asset
			
			returns:
				option value and first- and some second-order greeks (black scholes put object)
			
		*/

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
		
		/*
			internal function for calculating just the value of an option
				used for calculating sensitivities; more efficient than the black scholes object calculation
				because greeks are not included, only the actual value
			
			parameters:
				S: asset price
				K: option exercise / strike price
				r: prevailing risk-free interest rate
				t: years to expiration (days to expiration / 365 in the API)
				s: volatility of the underlying asset
				type: ['call','put','both'] - what type of valuation to perform
			
			returns:
				option value and first- and some second-order greeks (black scholes call object)
			
		*/

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
		
		/*
			calculates delta, the change in option value with respect to an increase of $1 in the underlying asset price
			
			parameters:
				S: asset price
				K: option exercise / strike price
				r: prevailing risk-free interest rate
				t: years to expiration (days to expiration / 365 in the API)
				s: volatility of the underlying asset
				type: ['call','put','both'] - what type of valuation to perform
			
			returns:
				delta (float) representing the $ change in option price for a $1 increase in underlying asset value
		*/

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
		
		// identical for calls and puts but handles $type anyway for conceptual consistency among functions
		
		/*
			calculates gamma, the change in option delta with respect to an increase of $1 in the underlying asset price
				this is a measure of convexity, the second-order derivative of option value w.r.t. asset value
				
			parameters:
				S: asset price
				t: years to expiration (days to expiration / 365 in the API)
				s: volatility of the underlying asset
				d1: z-score for future value if S > K at expiration
				type: ['call','put','both'] - what type of valuation to perform
			
			returns:
				gamma (float) representing the change in option delta for a $1 increase in underlying asset value
		*/
		
		return phi($d1)/($S*$s*sqrt($t));
	}
	
	function option_color() {
		// not yet implemented
	}

	function option_theta($S,$K,$r,$t,$s,$type='call') {
		
		/*
			calculates theta (time decay), the change in option value with respect to the passage of 1 day
			
			parameters:
				S: asset price
				K: option exercise / strike price
				r: prevailing risk-free interest rate
				t: years to expiration (days to expiration / 365 in the API)
				s: volatility of the underlying asset
				type: ['call','put','both'] - what type of valuation to perform
			
			returns:
				theta (float) representing the $ change in option price for a 1-day passage of time
		*/

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
		
		// identical for calls and puts but handles $type anyway for conceptual consistency among functions
		
		/*
			calculates vega, the change in option value with respect to a 1% change in implied volatility
			
			parameters:
				S: asset price
				K: option exercise / strike price
				r: prevailing risk-free interest rate
				t: years to expiration (days to expiration / 365 in the API)
				s: volatility of the underlying asset
				type: ['call','put','both'] - what type of valuation to perform
			
			returns:
				vega (float) representing the $ change in option price for a 1% change in implied volatility
		*/

		$d1 = black_scholes_d1($S,$K,$r,$t,$s);
		return $S * phi($d1) * sqrt($t) / 100;
	}

	function option_rho($S,$K,$r,$t,$s,$type='call') {
		
		/*
			calculates rho, the change in option value with respect to a 1% change in risk-free interest rate
			
			parameters:
				S: asset price
				K: option exercise / strike price
				r: prevailing risk-free interest rate
				t: years to expiration (days to expiration / 365 in the API)
				s: volatility of the underlying asset
				type: ['call','put','both'] - what type of valuation to perform
			
			returns:
				rho (float) representing the $ change in option price for a 1% change in risk-free interest rate
		*/

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
		
		/*
			numeric approximation of option value with respect to underlying asset price - assumes all inputs besides
				underlying asset price are held constant
			
			parameters:
				S: current asset price
				K: option exercise / strike price
				r: prevailing risk-free interest rate
				t: years to expiration (days to expiration / 365 in the API)
				s: volatility of the underlying asset
				type: ['call','put','both'] - what type of valuation to perform
				increment: the spacing of tested points
				increments_plus_minus: how many points (spaced $increment apart) to test above and below current price
			
			returns:
				sensitivities (array of floats, length increments_plus_minus * 2 + 1) representing value of option at
					each possible underlying asset price
		*/
		
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

	
	function implied_volatility(
		$S,$K,$V,$r,$t,
		$type='both',
		$s=1.0,
		$precision=1e-4,$increment=0.1,$max_iterations=1e4
	) {

		if ($type == 'call') {
			return ['call' => implied_volatility_calc($S,$K,$V,$r,$t,'call',$s,$precision,$increment,$max_iterations)];
		} else if ($type == 'put') {
			return ['put' => implied_volatility_calc($S,$K,$V,$r,$t,'put',$s,$precision,$increment,$max_iterations)];
		} else if ($type == 'both') {
			return [
				'call' => implied_volatility_calc($S,$K,$V,$r,$t,'call',$s,$precision,$increment,$max_iterations),
				'put' => implied_volatility_calc($S,$K,$V,$r,$t,'put',$s,$precision,$increment,$max_iterations)
			];
		} else {
			trigger_error(
				"parameter 'type' in function 'implied_volatility' ".
				"accepts only the values: 'call', 'put', or 'both'. '" . $type . "' was provided."
			);
		}
	}

	function implied_volatility_calc(
		$S,$K,$V,$r,$t,
		$type,
		$s,
		$precision,$increment,$max_iterations,
		$iterations=0
	) {
		
		$v = option_value($S,$K,$r,$t,$s,$type);
		
		if ((abs($V-$v) <= $precision) || $iterations == $max_iterations) {
			return [
				's' => $s,
				'iterations' => $iterations
			];
		} else {
			$s = $s + $increment * ($V/$v - 1);
			return implied_volatility_calc($S,$K,$V,$r,$t,$type,$s,$precision,$increment,$max_iterations,$iterations+1);
		}
	}

?>
