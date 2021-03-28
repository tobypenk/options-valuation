<?php
	
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	
	include_once("Option.php");
	
	class Put extends Option {
		
		public function value(float $S = null, float $s = null, float $t = null): float {
			
			/*
				returns option value
			*/
	
			if (is_null($S)) $S = $this->S;
			if (is_null($s)) $s = $this->s;
			if (is_null($t)) $t = $this->t;
			
			$d1 = $this->d1($S,$s,$t);
			$d2 = $this->d2($S,$s,$t);

			$n1p = normal_cdf(-$d1);
			$n2p = normal_cdf(-$d2);
			return $this->K * exp(-$this->r * $t) * $n2p - $S * $n1p;
	
		}
		
		public function delta(): float {
		
			/*
				calculates delta, the change in option value with respect to an increase of $1 in the underlying asset price
			*/
	
			$d1 = $this->d1();
			$n1 = normal_cdf($d1);
			
			return $n1 - 1;
		}
		
		public function theta(): float {
		
			/*
				calculates theta (time decay), the change in option value with respect to the passage of 1 day
				
				returns:
					theta (float) representing the $ change in option price for a 1-day passage of time
			*/
	
			$d1 = $this->d1();
			$d2 = $this->d2();
	
			$v0 = -$this->S * phi($d1) * $this->s / (2 * sqrt($this->t));
			$v1 = $this->r * $this->K * exp(-$this->r * $this->t) * normal_cdf(-$d2);
				
			return ($v0 + $v1) / 365;
		}

		public function rho(): float {
		
			/*
				calculates rho, the change in option value with respect to a 1ppt change in risk-free interest rate
				
				returns:
					rho (float) representing the $ change in option price for a 1ppt change in risk-free interest rate
			*/
	
			$d1 = $this->d1();
			$d2 = $this->d2();

			$v = -$this->K * $this->t * exp(-$this->r * $this->t) * normal_cdf(-$d2);
	
			return $v/100;
		}

		
	
		public function echotest(): void {
			echo json_encode($this->rho());
		}
		
	}
	
	
	$x = new Put(10.0,9.0,0.01,10./365,1.8,null);
	$x->echotest();
	
?>