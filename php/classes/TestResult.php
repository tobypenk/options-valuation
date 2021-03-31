<?php
	
	class TestResult {
		
		public bool $passed;
		public ?string $message;
		public ?Call $base;
		public ?Call $test;
		
		public function __construct(bool $passed, ?string $message = null, ?Call $base = null, ?Call $test = null) {
		    
		    $this->passed = $passed;
		    $this->message = $message;
		    $this->base = $base;
		    $this->test = $test;
	    }
	}
	
	
	
?>