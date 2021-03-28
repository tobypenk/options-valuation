<?php
	
	include_once("../libraries/math.php");
	
	class Option {
		
		public float $S;
		public float $K;
		public float $r;
		public float $t;
		public ?float $s;
		public ?float $V;
	
	    public function __construct(float $S, float $K, float $r, float $t, ?float $s = null, ?float $V = null) {
		    
		    if (is_null($s) & is_null($V)) {
			    trigger_error(
			    	"exactly one of s (implied volatility) and V (option value) must be null.".
			    	" both were provided as null."
			    );
		    } else if (!is_null($s) & !is_null($V)) {
			    trigger_error(
			    	"exactly one of s (implied volatility) and V (option value) must be null.".
			    	" $s was provided for volatility and $V was provided for value."
			    );
		    } else {
			    $this->S = $S;
		        $this->K = $K;
		        $this->r = $r;
		        $this->t = $t;
		        $this->s = $s;
		        $this->V = $V;
		    }
	    }
	    
	    public function summary($sensitivities = false) {
			$total = [
				"value" => $this->value(),
				"delta" => $this->delta(),
				"gamma" => $this->gamma(),
				"theta" => $this->theta(),
				"rho" => $this->rho(),
				"vega" => $this->vega()
			];
			
			if ($sensitivities) {
				$total["sensitivities"] = [
					"V_as_a_function_of_S" => $this->V_as_a_function_of_S(),
					"V_as_a_function_of_volatility" => $this->V_as_a_function_of_volatility(),
					"V_as_a_function_of_t" => $this->V_as_a_function_of_t()
				];
			}
			
			return $total;
		}
	    
	    protected function d1(float $S = null, float $s = null, float $t = null): float {
		
			/*
				returns black scholes d1, the z-score for the stock's future value iff S > K at expiration
					normal_cdf(d1) gives the stock's future value iff S > K at expiration
			*/
			
			if (is_null($S)) $S = $this->S;
			if (is_null($s)) $s = $this->s;
			if (is_null($t)) $t = $this->t;
			
			return (
				log($S/$this->K,exp(1)) + 
				($this->r + pow($s,2)/2) * $t) / 
				($s * sqrt($t)
			);
	    }
	    
	    protected function d2(float $S = null, float $s = null, float $t = null): float {
			
			/*
				returns black scholes d2, the z-score of the probability the option will be exercised
					normal_cdf(d2) gives the probability of exercise
			*/
			
			if (is_null($S)) $S = $this->S;
			if (is_null($s)) $s = $this->s;
			if (is_null($t)) $t = $this->t;
			
			return $this->d1($S,$s,$t) - 
				$s * sqrt($t);
		}
		
	    
	    public function gamma(): float {
					
			/*
				returns gamma, the change in option delta with respect to an increase of $1 in the underlying asset price
					measures convexity, the second-order derivative of option value w.r.t. asset value
			*/
			
			return phi($this->d1())/($this->S*$this->s*sqrt($this->t));
		}
	    
	    public function vega(): float {
		
			/*
				returns vega, the change in option value with respect to a 1ppt change in implied volatility
			*/
	
			$d1 = $this->d1();
			return $this->S * phi($d1) * sqrt($this->t) / 100;
		}
	    
		public function implied_volatility($s=1.0,$precision=1e-5,$increment=1e-1,$max_iterations=1e4,$iterations=0) {
			
			/*
				
				iterative method for finding implied volatility
				
				parameters:
					s: initial guess for volatility of the underlying asset
					precision: the threshold of accuracy below which the function will return instead of iterating
					increment: how much to increment s on each iteration (weighted by magnitude of inaccuracy)
					max_iterations: how many iterations to try before returning even if precision is not reached
				
				returns:
					implied volatility object (s => implied volatility (float), iterations => iterations to completion (int)
					
					recurs if precision not reached and iteration ceiling not reached
				
			*/
						
			$v = $this->value(null,$s,null);
			
			if ((abs($this->V-$v) <= $precision) || $iterations == $max_iterations) {
				return [
					's' => $s,
					'iterations' => $iterations
				];
			} else {
				$s = $s + $increment * ($this->V/$v - 1);
				return $this->implied_volatility($s,$precision,$increment,$max_iterations,$iterations+1);
			}
		}
	}
	
	
?>