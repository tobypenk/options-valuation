<?php
	
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	
	include_once "Call.php";
	include_once "TestResult.php";
	
	class PutUnitTest extends Put {
		
		public function value_test_explicit(float $tolerance = 1e-5): TestResult {
			
			$base_option = new Put(100,100,.05,30.0/365,.25,null,0.01);
			$predicted = $base_option->value();
			$actual = 2.690706;
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
		
		
		
		
		
		
		
		public function delta_test_explicit(float $tolerance = 1e-6): TestResult {
			
			$base_option = new Put(100,100,.05,30.0/365,.25,null,0.01);
			$predicted = $base_option->delta();
			$actual = -0.46744;
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
		
		
		
		
		
		
		
	}
	
	$P = new PutUnitTest(100,100,0.05,30/365,0.25,null,0.01);
	echo json_encode($P->delta_test_explicit());
	
?>









