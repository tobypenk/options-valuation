<?php
	
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	
	include_once("../libraries/math.php");
	
	//note - should add caching to improve compute speed, particularly for sensitivities
	
	class CallOption {
		
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
	    
	    private function d1(float $S = null, float $s = null, float $t = null): float {
		
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
	    
	    private function d2(float $S = null, float $s = null, float $t = null): float {
			
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
		
		
		public function valuation(float $S = null, float $s = null, float $t = null): float {
		
			/*
				returns option value
			*/
			
			if (is_null($S)) $S = $this->S;
			if (is_null($s)) $s = $this->s;
			if (is_null($t)) $t = $this->t;
			
			$d1 = $this->d1($S,$s,$t);
			$d2 = $this->d2($S,$s,$t);
	
			$n1 = normal_cdf($d1);
			$n2 = normal_cdf($d2);
			
			$v = $S * $n1 - $this->K * exp(-$this->r * $t) * $n2;
			
			return $v;
		}
		
		public function delta(): float {
		
			/*
				returns delta, the change in option value with respect to an increase of $1 in the underlying asset price
			*/
	
			$d1 = $this->d1();
			$n1 = normal_cdf($d1);

			return $n1;
		}
		
		public function gamma(): float {
					
			/*
				returns gamma, the change in option delta with respect to an increase of $1 in the underlying asset price
					this is a measure of convexity, the second-order derivative of option value w.r.t. asset value
			*/
			
			return phi($this->d1())/($this->S*$this->s*sqrt($this->t));
		}
	
		
		public function theta(): float {
		
			/*
				returns theta (time decay), the change in option value with respect to the passage of 1 day
			*/
	
			$d1 = $this->d1();
			$d2 = $this->d2();
	
			$v0 = -$this->S * phi($d1) * $this->s / (2 * sqrt($this->t));
			$v1 = -$this->r * $this->K * exp(-$this->r * $this->t) * normal_cdf($d2);

			return ($v0 + $v1) / 365;
		}
		
		public function vega(): float {
		
			/*
				returns vega, the change in option value with respect to a 1ppt change in implied volatility
			*/
	
			$d1 = $this->d1();
			return $this->S * phi($d1) * sqrt($this->t) / 100;
		}
		
		public function rho(): float {
			
			/*
				returns rho, the change in option value with respect to a 1ppt change in risk-free interest rate
			*/
	
			$d2 = $this->d2();

			return  $this->K * $this->t * exp(-$this->r * $this->t) * normal_cdf($d2) / 100;
			
		}
		
		
		
		
		
		
		public function sensitivity_V_wrt_S(float $increment=0.1, int $increments_plus_minus=40): array {
		
			/*
				numeric approximation of option value with respect to underlying asset price - assumes all inputs besides
					underlying asset price are held constant
				
				returns:
					sensitivities (array of floats, length increments_plus_minus * 2 + 1) representing value of option at
						each simulated underlying asset price, keeping all other variables constant
			*/
			
			$total = [];

			for ($i=$increments_plus_minus*-1; $i<$increments_plus_minus; $i++) {
				
				$instance_S = $this->S + $increment * $i;
				$v = $this->valuation($instance_S,$this->s,$this-t);
				array_push($total,["S"=>$instance_S,"V"=>$v]);
			}
						
			return $total;
		}


		public function sensitivity_V_wrt_vol(float $increment=0.01, int $increments_plus_minus=40): array {

			/*
				numeric approximation of option value with respect to underlying asset volatility - assumes all inputs besides
					underlying asset volatility are held constant
				
				returns:
					sensitivities (array of floats, length increments_plus_minus * 2 + 1) representing value of option at
						each simulated underlying asset volatility, keeping all other variables constant
			*/
	
			$total = [];
	
			for ($i=$increments_plus_minus*-1; $i<$increments_plus_minus; $i++) {
				$instance_s = $this->s + $increment * $i;
				$v = $this->valuation($this->S,$instance_s,$this->t);
				array_push($total,['vol'=>$instance_s,'V'=>$v]);
			}
	
			return $total;
		}

		
		public function sensitivity_V_wrt_t() {

			/*
				numeric approximation of option value with respect to passage of time - assumes all inputs besides
					time are held constant
				
				returns:
					sensitivities (array of floats, length increments_plus_minus * 2 + 1) representing value of option at
						each time, keeping all other variables constant
			*/
	
			$t_days = round($this->t * 365);
	
			$total = [];
	
			while ($t_days > 0) {
				$instance_t = ($t_days) / 365;
				$v = $this->valuation($this->S,$this->s,$instance_t);
				array_push($total,['t'=>round($instance_t*365),'V'=>$v]);
				$t_days -= 1;
			}

			return $total;
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
						
			$v = $this->valuation(null,$s,null);
			
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
		
		
		
		
		public function echotest(): void {
			echo 0.52918078313221;
			echo json_encode($this->implied_volatility());
		}
		
	}
	
	
	$x = new CallOption(10.0,10.0,0.01,10./365,null,0.52918078313221);
	$x->echotest();
	
?>








