<?php
	
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
				
			based on Numerical Recipes in Fortran 77: The Art of Scientific Computing 
				(ISBN 0-521-43064-X), 1992, page 214, Cambridge University Press.
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
			current implementation ignores dividends so dt is 0
			
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
	
	function normal_cdf_v0($x) {
		
		$b1 =  0.319381530;
		$b2 = -0.356563782;
		$b3 =  1.781477937;
		$b4 = -1.821255978;
		$b5 =  1.330274429;
		$p  =  0.2316419;
		$c  =  0.39894228;
		
		if($x >= 0.0) {
		    $t = 1.0 / ( 1.0 + $p * $x );
		    return (1.0 - $c * exp( -$x * $x / 2.0 ) * $t *
		    ( $t *( $t * ( $t * ( $t * $b5 + $b4 ) + $b3 ) + $b2 ) + $b1 ));
		}
		else {
		    $t = 1.0 / ( 1.0 - $p * $x );
		    return ( $c * exp( -$x * $x / 2.0 ) * $t *
		    ( $t *( $t * ( $t * ( $t * $b5 + $b4 ) + $b3 ) + $b2 ) + $b1 ));
	    }
	}
	
?>