<?php
	
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	
	include_once("Option.php");
	
	class Put extends Option {
		
		public function value(float $S = null, float $s = null, float $t = null): float {
			
			/*
				returns option value
				
				parameters:
					S: strike price to use for this valuation (uses instance value if null)
					s: volatility to use for this valuation (uses instance value if null)
					t: time to expiration to use for this valuation (uses instance value if null)
			*/
	
			if (is_null($S)) $S = $this->S;
			if (is_null($s)) $s = $this->s;
			if (is_null($t)) $t = $this->t;
			
			$d1 = $this->d1($S,$s,$t);
			$d2 = $this->d2($S,$s,$t);

			$n1p = normal_cdf(-$d1);
			$n2p = normal_cdf(-$d2);
						
			return $this->K * exp(-$this->r * $t) * $n2p - $S * exp(-$this->q * $t) * $n1p;
	
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
	
			$v0 = -$this->dividend_discount_factor() * $this->S * phi($d1) * $this->s / (2 * sqrt($this->t));
			$v1 = $this->r * $this->K * $this->risk_free_rate_discount_factor() * normal_cdf(-$d2);
			$v2 = -$this->q * $this->S * $this->dividend_discount_factor() * normal_cdf(-$d1);
				
			return ($v0 + $v1 + $v2) / 365;
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
		
		public function epsilon(): float {
			
			/*
				returns epsilon, the change in option value with respect to a 1ppt change in dividend yield
			*/
	
			$d1 = $this->d1();
			
			return $this->S * $this->t * $this->dividend_discount_factor() * normal_cdf(-$d1) / 100;			
		}

		public function V_as_a_function_of_S($increment=0.1,$increments_plus_minus=40): array {
		
			/*
				numeric approximation of option value with respect to underlying asset price - assumes all inputs besides
					underlying asset price are held constant
					
				parameters:
					increment: the spacing between steps for each test
					increments_plus_minus: the number of increments in each direction to test
					(consumes instance parameters for S, s, and t)
				
				returns:
					sensitivities (array of floats, length increments_plus_minus * 2 + 1) representing value of option at
						each simulated underlying asset price, keeping all other variables constant
			*/
			
			$total = [];
	
			for ($i=$increments_plus_minus*-1; $i<$increments_plus_minus; $i++) {
				$instance_S = $this->S + $increment * $i;
				$v = $this->value($instance_S,$this->s,$this->t);
				array_push($total,['S'=>$instance_S,'V'=>$v]);
			}
	
			return $total;
		}

		public function V_as_a_function_of_volatility($increment=0.01,$increments_plus_minus=40): array {

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
				$v = $this->value($this->S,$instance_s,$this->t);
				array_push($total,['vol'=>$instance_s,'V'=>$v]);
			}

			return $total;
		}
		
		public function V_as_a_function_of_t(): array {

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
				$v = $this->value($this->S,$this->s,$instance_t);
				array_push($total,['t'=>round($instance_t*365),'V'=>$v]);
				$t_days -= 1;
			}
	
			return $total;
		}
		
		
		
		/*
		public function sensitivity(
			float $independent = $this->S, 
			float $dependent = $this->value, 
			float $increment=0.01,
			int $increments_plus_minus=40
		): array {
			
			$total = [];
	
			for ($i=$increments_plus_minus*-1; $i<$increments_plus_minus; $i++) {
				$instance = $independent + $increment * $i;
				$v = $dependent($instance,$this->s,$this->t);
				array_push($total,['S'=>$instance_S,'V'=>$v]);
			}
	
			return $total;
		}
		*/
		
		
	}
	
?>









