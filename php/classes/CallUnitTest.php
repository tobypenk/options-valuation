<?php
	
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	
	include_once "Call.php";
	include_once "TestResult.php";
	
	class CallUnitTest extends Call {
		
		public function battery(): array {
			
			return [

				$this->value_test_explicit(),
				$this->delta_test_explicit(),
				$this->delta_test_implicit(),
				$this->theta_test_explicit(),
				$this->theta_test_implicit(),
				$this->epsilon_test_implicit(),
				$this->vega_test_explicit(),
				$this->vega_test_implicit(),
				$this->rho_test_explicit(),
				$this->rho_test_implicit(),

				$this->vanna_test_dvegadspot_implicit(),
				$this->vanna_test_ddeltadvol_implicit()
			];
		}
		
		private function value_test_explicit(float $tolerance = 1e-5): TestResult {
			
			$base_option = new Call(100,100,.05,30.0/365,.25,null,0.01);
			$predicted = $base_option->value();
			$actual = 3.018663;
			$error = $predicted - $actual;
			
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
		
		private function delta_test_explicit(float $tolerance = 1e-3): TestResult {
			
			$base_option = new Call(100,100,.05,30.0/365,.25,null,0.01);
			$predicted = $base_option->delta();
			$actual = 0.53256;
			$error = $predicted - $actual;
			
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
		
		private function delta_test_implicit(float $tolerance = 1e-6): TestResult {
			
			$tmp_S = $this->S;
			$tmp_K = $this->K;
			
			foreach (range(1,200,1) as $i) {
				
				$this->S = $i;
				
				foreach (range(max(0.1,$i*0.8),$i*1.2,$i*0.1) as $j) {
					
					$this->K = $j;
					$delta = $this->delta();
					$value = $this->value();
					$factor = 1e-4;
					
					$test_p = new Call($this->S+$factor,$this->K,$this->r,$this->t,$this->s,$this->V,$this->q);
					$compare_p = $value + $delta * $factor - $test_p->value();
					
					if (abs($compare_p) >= $tolerance) {
						return new TestResult(false, "delta test failed +",["base_option"=>$this,"test_option"=>$test_p,"error"=>$compare_p]);
					}
					
					$test_m = new Call($this->S-$factor,$this->K,$this->r,$this->t,$this->s,$this->V,$this->q);
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
		
		private function theta_test_explicit(float $tolerance = 1e-4): TestResult {
			
			$base_option = new Call(100,100,.05,30.0/365,.25,null,0.01);
			$predicted = $base_option->theta();
			$actual = -0.0529;
			$error = $predicted - $actual;
			
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
		
		private function theta_test_implicit(float $tolerance = 1e-5): TestResult {
			
			$tmp_t = $this->t;

			foreach (range(1,40,1) as $j) {
					
				$this->t = $j/365;
				$theta = $this->theta();
				$value = $this->value();
				$factor = 1e-2;
				
				$test_p = new Call($this->S,$this->K,$this->r,($j+$factor)/365,$this->s,$this->V,$this->q);
				$compare_p = $value - $theta * $factor - $test_p->value();
				
				if (abs($compare_p) >= $tolerance) {
					return new TestResult(false, "theta test failed +",["base_option"=>$this,"test_option"=>$test_p,"error"=>$compare_p]);
				}
				
				$test_m = new Call($this->S,$this->K,$this->r,($j-$factor)/365,$this->s,$this->V,$this->q);
				$compare_m = $value + $theta * $factor - $test_m->value();
				
				if (abs($compare_m) >= $tolerance) {
					return new TestResult(false, "theta test failed -",["base_option"=>$this,"test_option"=>$test_m,"error"=>$compare_m]);
				}
			}
			
			$this->t = $tmp_t;
			
			return new TestResult(true);
		}
		
		private function epsilon_test_implicit(float $tolerance = 1e-6): TestResult {
			
			/*
				implicit test of epsilon accuracy
			*/
			
			$tmp_q = $this->q;
			
			foreach (range(0.0,0.20,0.01) as $i) {
				
				$this->q = $i;
				$epsilon = $this->epsilon();
				$value = $this->value();
				
				$test_p = new Call($this->S,$this->K,$this->r,$this->t,$this->s,$this->V,$this->q + 0.0001);
				$compare_p = $value + $epsilon / 100 - $test_p->value();
				
				if (abs($compare_p) >= $tolerance) {
					return new TestResult(false, "epsilon test failed",["base_option"=>$this,"test_option"=>$test_p]);
				}
				
				$test_m = new Call($this->S,$this->K,$this->r,$this->t,$this->s,$this->V,$this->q - 0.0001);
				$compare_m = $value - $epsilon / 100 - $test_m->value();
				
				if (abs($compare_m) >= $tolerance) {
					return new TestResult(false, "epsilon test failed",["base_option"=>$this,"test_option"=>$test_m]);
				}
			}
			
			$this->q = $tmp_q;
			
			return new TestResult(true);
		}
		
		private function vega_test_explicit(float $tolerance = 1e-4): TestResult {
			
			$base_option = new Call(100,100,.05,30.0/365,.25,null,0.01);
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
		
		private function vega_test_implicit(float $tolerance = 1e-5): TestResult {
			
			$tmp_s = $this->s;
			
			foreach (range(0.01,3.0,0.01) as $i) {
				
				$this->s = $i;
				
				$vega = $this->vega();
				$value = $this->value();
				$factor = 1e-2;
				
				$test_p = new Call($this->S,$this->K,$this->r,$this->t,$this->s+$factor/100,$this->V,$this->q);
				$compare_p = $value + $vega * $factor - $test_p->value();
				
				if (abs($compare_p) >= $tolerance) {
					return new TestResult(false, "vega test failed +",["base_option"=>$this,"test_option"=>$test_p,"error"=>$compare_p]);
				}
				
				$test_m = new Call($this->S,$this->K,$this->r,$this->t,$this->s-$factor/100,$this->V,$this->q);
				$compare_m = $value - $vega * $factor - $test_m->value();
				
				if (abs($compare_m) >= $tolerance) {
					return new TestResult(false, "vega test failed -",["base_option"=>$this,"test_option"=>$test_m,"error"=>$compare_m]);
				}
			}
			
			$this->s = $tmp_s;
			
			return new TestResult(true);
		}
		
		private function rho_test_explicit(float $tolerance = 1e-6): TestResult {
			
			$base_option = new Call(100,100,.05,30.0/365,.25,null,0.01);
			$predicted = $base_option->rho();
			$actual = 0.041255;
			$error = $predicted - $actual;
			
			if (abs($error) < $tolerance) {
				return new TestResult(true);
			} else {
				return new TestResult(
					false,
					"explicit rho test failed",
					["base_option"=>$base_option,"predicted_value"=>$predicted,"actual_value"=>$actual,"error"=>$error]
				);
			}
		}
		
		private function rho_test_implicit(float $tolerance = 1e-6): TestResult {
			
			$tmp_r = $this->r;
			
			foreach (range(0.01,0.12,0.01) as $i) {
				
				$this->r = $i;
				$rho = $this->rho();
				$value = $this->value();
				$factor = 1e-2;
				
				$test_p = new Call($this->S,$this->K,$this->r + $factor / 100,$this->t,$this->s,$this->V,$this->q);
				$compare_p = $value + $rho / 100 - $test_p->value();
				
				if (abs($compare_p) >= $tolerance) {
					return new TestResult(false, "rho test failed",["base_option"=>$this,"test_option"=>$test_p]);
				}
				
				$test_m = new Call($this->S,$this->K,$this->r - $factor / 100,$this->t,$this->s,$this->V,$this->q);
				$compare_m = $value - $rho / 100 - $test_m->value();
				
				if (abs($compare_m) >= $tolerance) {
					return new TestResult(false, "rho test failed",["base_option"=>$this,"test_option"=>$test_m]);
				}
			}
			
			$this->r = $tmp_r;
			
			return new TestResult(true);
		}
		
		private function vanna_test_dvegadspot_implicit(float $tolerance = 1e-4): TestResult {
			
			$tmp_S = $this->S;
			$tmp_K = $this->K;
			
			foreach (range(1,200,1) as $i) {
				
				$this->S = $i;
				$this->K = $i;
				$vanna = $this->vanna();
				$vega = $this->vega();
				$factor = 1e-2;
				
				$test_p = new Call($this->S+$factor,$this->S,$this->r,$this->t,$this->s,$this->V,$this->q);
				$compare_p = $vega * (1 + $vanna * $factor) - $test_p->vega();
				
				if (abs($compare_p) >= $tolerance) {
					return new TestResult(false, "vanna test failed",["base_option"=>$this,"test_option"=>$test_p,"error"=>$compare_p]);
				}
				
				$test_m = new Call($this->S-$factor,$this->S,$this->r,$this->t,$this->s,$this->V,$this->q);
				$compare_m = $vega * (1 - $vanna * $factor) - $test_p->vega();
				
				if (abs($compare_m) >= $tolerance) {
					return new TestResult(false, "vanna test failed",["base_option"=>$this,"test_option"=>$test_m,"error"=>$compare_m]);
				}
			}
			
			$this->S = $tmp_S;
			$this->K = $tmp_K;

			return new TestResult(true);	
		}
		
		private function vanna_test_ddeltadvol_implicit(float $tolerance = 1e-3): TestResult {
			
			$tmp_s = $this->s;
			
			foreach (range(0.25,2.5,0.01) as $i) {
				
				$this->s = $i;
				$delta = $this->delta();
				$vanna = $this->vanna();
				$factor = 1e-2;
				
				$test_p = new Call($this->S,$this->K,$this->r,$this->t,$this->s + $factor / 100,$this->V,$this->q);
				$compare_p = $delta + $vanna * $factor - $test_p->delta();
				
				if (abs($compare_p) >= $tolerance) {
					return new TestResult(
						false, 
						"vanna test failed +",
						["base_option"=>$this,"test_option"=>$test_p,"error"=>$compare_p]
					);
				}
				
				$test_m = new Call($this->S,$this->K,$this->r,$this->t,$this->s - $factor / 100,$this->V,$this->q);
				$compare_m = $delta - $vanna * $factor - $test_m->delta();
				
				if (abs($compare_m) >= $tolerance) {
					return new TestResult(false, "vanna test failed -",["base_option"=>$this,"test_option"=>$test_m,"error"=>$compare_m]);
				}
			}
			
			$this->s = $tmp_s;
			
			return new TestResult(true);	
		}
	}
	


?>
















