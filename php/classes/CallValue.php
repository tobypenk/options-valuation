<?php
	
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	
	include_once("../libraries/math.php");
	
	//note - should add caching to improve compute speed, particularly for sensitivities
	
	class CallValue {
		
		public float $S;
		public float $K;
		public float $r;
		public float $t;
		public float $s;
	
	    public function __construct(float $S, float $K, float $r, float $t, float $s) {
		    		    
	        $this->S = $S;
	        $this->K = $K;
	        $this->r = $r;
	        $this->t = $t;
	        $this->s = $s;
	    }
	    
	    private function d1(): float {
		
			/*
				calculate black scholes d1, the z-score for the stock's future value iff S > K at expiration
					normal_cdf(d1) gives the stock's future value iff S > K at expiration
				
				parameters:
					none (uses instance parameters)
					
				returns:
					d1 (float)
			*/
			
			return (
				log($this->S/$this->K,exp(1)) + 
				($this->r + pow($this->s,2)/2) * $this->t) / 
				($this->s * sqrt($this->t)
			);
	    }
	    
	    private function d2(): float {
			
			/*
				calculate black scholes d2, the z-score of the probability the option will be exercised
					normal_cdf(d2) gives the probability of exercise
					
				parameters:
					none (uses instance parameters)
					
				returns:
					d2 (float)
			*/
			
			return $this->d1() - $this->s * sqrt($this->t);
		}
		
		public function valuation(): float {
		
			/*
				calculates option value
				
				parameters:
					none (uses instance parameters)
				
				returns:
					option value (float)
			*/
	
			$d1 = $this->d1();
			$d2 = $this->d2();
	
			$n1 = normal_cdf($d1);
			$n2 = normal_cdf($d2);
			$v = $this->S * $n1 - $this->K * exp(-$this->r * $this->t) * $n2;
			
			return $v;
		}
		
		public function delta(): float {
		
			/*
				calculates delta, the change in option value with respect to an increase of $1 in the underlying asset price
				
				parameters:
					none (uses instance parameters)
				
				returns:
					delta (float) representing the $ change in option price for a $1 increase in underlying asset value
			*/
	
			$d1 = $this->d1();
			$n1 = normal_cdf($d1);

			return $n1;
		}
		
		public function gamma(): float {
		
			// identical for calls and puts but handles $type anyway for conceptual consistency among functions
			
			/*
				calculates gamma, the change in option delta with respect to an increase of $1 in the underlying asset price
					this is a measure of convexity, the second-order derivative of option value w.r.t. asset value
					
				parameters:
					none (uses instance parameters)
				
				returns:
					gamma (float) representing the change in option delta for a $1 increase in underlying asset value
			*/
			
			return phi($this->d1())/($this->S*$this->s*sqrt($this->t));
		}
	
		
		public function theta(): float {
		
			/*
				calculates theta (time decay), the change in option value with respect to the passage of 1 day
				
				parameters:
					none (uses instance parameters)
				
				returns:
					theta (float) representing the $ change in option price for a 1-day passage of time
			*/
	
			$d1 = $this->d1();
			$d2 = $this->d2();
	
			$v0 = -$this->S * phi($d1) * $this->s / (2 * sqrt($this->t));
			$v1 = -$this->r * $this->K * exp(-$this->r * $this->t) * normal_cdf($d2);

			return ($v0 + $v1) / 365;
		}
		
		
		
		
		public function echotest(): void {
			echo $this->theta();
		}
		
	}
	
	
	$x = new CallValue(10.0,10.0,0.01,10./365,0.8);
	$x->echotest();
	
?>








