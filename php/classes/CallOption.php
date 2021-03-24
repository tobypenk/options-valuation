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
	    
	    private function d1(float $S_override = null, float $s_override = null): float {
		
			/*
				returns black scholes d1, the z-score for the stock's future value iff S > K at expiration
					normal_cdf(d1) gives the stock's future value iff S > K at expiration
			*/
			
			if (is_null($s_override)) {
				$instance_s = $this->s;
			} else {
				$instance_s = $s_override;
			}
			
			if (is_null($S_override)) {
				$instance_S = $this->S;
			} else {
				$instance_S = $S_override;
			}
			
			return (
				log($instance_S/$this->K,exp(1)) + 
				($this->r + pow($instance_s,2)/2) * $this->t) / 
				($instance_s * sqrt($this->t)
			);
	    }
	    
	    private function d2(float $S_override = null, float $s_override = null): float {
			
			/*
				returns black scholes d2, the z-score of the probability the option will be exercised
					normal_cdf(d2) gives the probability of exercise
			*/
			
			if (is_null($s_override)) {
				$instance_s = $this->s;
			} else {
				$instance_s = $s_override;
			}
			
			return $this->d1($S_override, $s_override) - $instance_s * sqrt($this->t);
		}
		
		public function valuation(float $S_override = null, float $s_override = null): float {
		
			/*
				returns option value
			*/
			
			$d1 = $this->d1($s_override);
			$d2 = $this->d2($s_override);
	
			$n1 = normal_cdf($d1);
			$n2 = normal_cdf($d2);
			$v = $this->S * $n1 - $this->K * exp(-$this->r * $this->t) * $n2;
			
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
		
		
		
		
		
		
		public function sensitivity_V_wrt_S(float $increment=0.1,int $increments_plus_minus=40): array {
		
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
				$v = (new CallOption($instance_S,$this->K,$this->r,$this->t,$this->s))->valuation();
				array_push($total,["S"=>$instance_S,"V"=>$v]);
			}
						
			return $total;
		}

		
		
		
		
		
		
		
		public function echotest(): void {
			echo json_encode($this->sensitivity_V_wrt_S());
		}
		
	}
	
	
	$x = new CallOption(10.0,10.0,0.01,10./365,0.8,null);
	$x->echotest();
	
?>








