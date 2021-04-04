<?php
	
	class TestResult {
		
		public bool $passed;
		public ?string $message;
		public ?array $parameters;
		
		public function __construct(bool $passed, ?string $message = null, ?array $parameters = null) {
		    
		    $this->passed = $passed;
		    $this->message = $message;
		    $this->parameters = $parameters;
	    }
	}
	
	
	
?>