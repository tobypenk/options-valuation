<?php
	
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	
	include_once "Call.php";
	include_once "TestResult.php";
	
	class PutUnitTest extends Put {
		
		public function battery(): array {
			
			return [
				$this->value_test_explicit(2.690706, new Put(100,100,.05,30.0/365,.25,null,0.01)),
				$this->delta_test_explicit(-0.46744, new Put(100,100,.05,30.0/365,.25,null,0.01)),
				$this->delta_test_implicit(),
				$this->theta_test_explicit(-0.0430, new Put(100,100,.05,30.0/365,.25,null,0.01)),
				$this->theta_test_implicit(),
				$this->epsilon_test_implicit(),
				$this->vega_test_explicit(),
				//$this->vega_test_implicit(),
				//$this->rho_test_explicit(),
				//$this->rho_test_implicit(),
			];
		}
		
		
		
		public function value_test_explicit(float $known_value, Option $base_option, float $tolerance = 1e-5): TestResult {
			
			$predicted = $base_option->value();
			$error = $predicted - $known_value;
			
			if (abs($error) < $tolerance) {
				return new TestResult(true);
			} else {
				return new TestResult(
					false,
					"explicit value test failed",
					["base_option"=>$base_option,"predicted_value"=>$predicted,"actual_value"=>$actual,"error"=>$error]
				);
			}
		}
		
		public function delta_test_explicit(float $known_value, Option $base_option, float $tolerance = 1e-6): TestResult {
			
			$predicted = $base_option->delta();
			$error = $predicted - $known_value;
			
			if (abs($error) < $tolerance) {
				return new TestResult(true);
			} else {
				return new TestResult(
					false,
					"explicit delta test failed",
					["base_option"=>$base_option,"predicted_value"=>$predicted,"actual_value"=>$actual,"error"=>$error]
				);
			}
		}
		
		public function delta_test_implicit(float $tolerance = 1e-6): TestResult {
			
			$tmp_S = $this->S;
			$tmp_K = $this->K;
			
			foreach (range(1,200,1) as $i) {
				
				$this->S = $i;
				
				foreach (range(max(0.1,$i*0.8),$i*1.2,$i*0.1) as $j) {
					
					$this->K = $j;
					$delta = $this->delta();
					$value = $this->value();
					$factor = 1e-4;
					
					$test_p = new Put($this->S+$factor,$this->K,$this->r,$this->t,$this->s,$this->V,$this->q);
					$compare_p = $value + $delta * $factor - $test_p->value();
					
					if (abs($compare_p) >= $tolerance) {
						return new TestResult(false, "delta test failed +",["base_option"=>$this,"test_option"=>$test_p,"error"=>$compare_p]);
					}
					
					$test_m = new Put($this->S-$factor,$this->K,$this->r,$this->t,$this->s,$this->V,$this->q);
					$compare_m = $value - $delta * $factor - $test_m->value();
					
					if (abs($compare_m) >= $tolerance) {
						return new TestResult(false, "delta test failed -",["base_option"=>$this,"test_option"=>$test_m,"error"=>$compare_m]);
					}
				}
			}
			
			$this->S = $tmp_S;
			$this->K = $tmp_K;
			
			return new TestResult(true);
		}
		
		public function theta_test_explicit(float $known_value, Option $base_option, float $tolerance = 2e-3): TestResult {
			
			$base_option = new Put(100,100,.05,30.0/365,.25,null,0.01);
			$predicted = $base_option->theta();
			$error = $predicted - $known_value;
			
			if (abs($error) < $tolerance) {
				return new TestResult(true);
			} else {
				return new TestResult(
					false,
					"explicit theta test failed",
					["base_option"=>$base_option,"predicted_value"=>$predicted,"actual_value"=>$actual,"error"=>$error]
				);
			}
		}
		
		public function theta_test_implicit(float $tolerance = 1e-5): TestResult {
			
			/*
				performs an implicit test of accuracy of theta, the change in option value with respect to 
					the passage of one day.
					
				implicit testing assumes the value method for the class (or parent class) is accurate.
				models the change in option value for a given dtheta using:
					
					prediction: v + dv/dtheta
					actual: valuation given t = t + dtheta
					
				parameters:
					tolerance: the maximum allowable error between predicted and actual values to return success
				
				returns:
					TestResult object representing the success or failure of the test. returns metadata about the 
						specific configuration of parameters that failed if unsuccessful.
			*/
			
			$tmp_t = $this->t;

			foreach (range(1,40,1) as $j) {
				
				$this->t = $j/365;
				$theta = $this->theta();
				$value = $this->value();
				$factor = 1e-2;
				
				$test_p = new Put($this->S,$this->K,$this->r,($j+$factor)/365,$this->s,$this->V,$this->q);
				$compare_p = $value - $theta * $factor - $test_p->value();
				
				if (abs($compare_p) >= $tolerance) {
					return new TestResult(false, "theta test failed +",["base_option"=>$this,"test_option"=>$test_p,"error"=>$compare_p]);
				}
				
				$test_m = new Put($this->S,$this->K,$this->r,($j-$factor)/365,$this->s,$this->V,$this->q);
				$compare_m = $value + $theta * $factor - $test_m->value();
				
				if (abs($compare_m) >= $tolerance) {
					return new TestResult(false, "theta test failed -",["base_option"=>$this,"test_option"=>$test_m,"error"=>$compare_m]);
				}
			}
			
			$this->t <- $tmp_t;
			
			return new TestResult(true);
		}
		
		public function epsilon_test_implicit(float $tolerance = 1e-6): TestResult {
			
			/*
				not yet done
			*/
			
			$tmp_q = $this->q;
			
			foreach (range(0.,0.20,0.01) as $i) {
				
				$this->q = $i;
				$epsilon = $this->epsilon();
				$value = $this->value();
				$factor = 1e-2;
				
				$test_p = new Put($this->S,$this->K,$this->r,$this->t,$this->s,$this->V,$this->q + 0.01 * $factor);
				$compare_p = $value + $epsilon * $factor - $test_p->value();
				
				if (abs($compare_p) >= $tolerance) {
					return new TestResult(false, "epsilon test failed +",["base_option"=>$this,"test_option"=>$test_p,"error"=>$compare_p]);
				}
				
				$test_m = new Put($this->S,$this->K,$this->r,$this->t,$this->s,$this->V,$this->q - 0.01 * $factor);
				$compare_m = $value - $epsilon * $factor - $test_m->value();
				
				if (abs($compare_m) >= $tolerance) {
					return new TestResult(false, "epsilon test failed -",["base_option"=>$this,"test_option"=>$test_m,"error"=>$compare_m]);
				}
			}
			
			$this->q = $tmp_q;
			
			return new TestResult(true);
		}
		
		
		
		public function vega_test_explicit(float $tolerance = 1e-4): TestResult {
			
			$base_option = new Put(100,100,.05,30.0/365,.25,null,0.01);
			$predicted = $base_option->vega();
			$actual = 0.113992;
			$error = $predicted - $actual;
			
			if (abs($error) < $tolerance) {
				return new TestResult(true);
			} else {
				return new TestResult(
					false,
					"explicit vega test failed",
					["base_option"=>$base_option,"predicted_value"=>$predicted,"actual_value"=>$actual,"error"=>$error]
				);
			}
		}
		
		
		
		
		
		
	}
	
	$P = new PutUnitTest(100,100,0.05,30/360,0.25,null,0.01);
	echo json_encode($P->battery());
	
?>









