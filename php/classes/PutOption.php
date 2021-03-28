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

		
		
	
		public function echotest(): void {
			echo json_encode($this->delta());
		}
		
	}
	
	
	$x = new Put(10.0,9.0,0.01,10./365,1.8,null);
	$x->echotest();
	
?>