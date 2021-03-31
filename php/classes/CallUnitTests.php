<?php
	
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	
	include_once "Call.php";
	include_once "TestResult.php";
	
	class CallUnitTest extends Call {
		
		public function epsilon_test(float $tolerance = 1e-6): TestResult {
			
			/*
				implicit test of epsilon accuracy
			*/
			
			$tmp_q = $this->q;
			
			foreach (range(-0.10,0.20,0.01) as $i) {
				
				$this->q = $i;
				
				$test_p = new Call($this->S,$this->K,$this->r,$this->t,$this->s,$this->V,$this->q + 0.0001);
				$compare_p = $this->value() + $this->epsilon()/100 - $test_p->value();
				
				if (abs($compare_p) >= $tolerance) {
					return new TestResult(false, "epsilon test failed",$this,$test_p);
				}
				
				$test_m = new Call($this->S,$this->K,$this->r,$this->t,$this->s,$this->V,$this->q - 0.0001);
				$compare_m = $this->value() - $this->epsilon()/100 - $test_m->value();
				
				if (abs($compare_m) >= $tolerance) {
					return new TestResult(false, "epsilon test failed",$this,$test_m);
				}
			}
			
			$this->q = $tmp_q;
			
			return new TestResult(true);
		}
		
		
/*
	
		$C_delta_test = new Call(100.01,100,.05,30.0/365,.25,null,0.01);
		//echo $C->value() + $C->delta()/100 - $C_delta_test->value();
		
		$C_theta_test = new Call(100,100,.05,30.01/365,.25,null,0.01);
		//echo $C->value() - $C->theta()/100 - $C_theta_test->value();
		
		$C_vega_test = new Call(100,100,.05,30.0/365,.2501,null,0.01);
		//echo $C->value() + $C->vega()/100 - $C_vega_test->value();
		
		$C_rho_test = new Call(100,100,.0501,30.0/365,.25,null,0.01);
		//echo $C->value() + $C->rho()/100 - $C_rho_test->value();
		
		$C_gamma_test = new Call(100.01,100,.05,30.0/365,.25,null,0.01);
		//echo $C->delta() + $C->gamma()/100 - $C_gamma_test->delta();
		
		//dvegadspot
		$C_vanna_test = new Call(100.01,100,.05,30.0/365,.25,null,0.01);
		echo $C->vega() * (1 + $C->vanna()/100) - $C_vanna_test->vega();
		
		//ddeltadvol
		$C_vanna_test_2 = new Call(100,100,.05,30.0/365,.2501,null,0.01);
		//echo $C->delta() + $C->vanna()/100 - $C_vanna_test_2->delta();
	
	
		
		$P = new Put(100,100,.05,30.0/365,.25,null,0.01);
		
*/
		
	}
	
	
	$CT = new CallUnitTest(10,8,.05,30.0/365,.25,null,0.01);
	echo json_encode($CT->epsilon_test());
	
?>






