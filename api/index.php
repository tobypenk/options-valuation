<?php

?>

<html>
	<head>
		<script type="text/javascript" src="../js/jquerylib.js"></script>
		<script type="text/javascript" src="../js/js_helpers.js"></script>
		<script src="https://d3js.org/d3.v5.min.js"></script>
		
		<link rel='stylesheet' type='text/css' href='../css/main.css' />
		
		<link href="https://fonts.googleapis.com/css?family=Inconsolata&display=swap" rel="stylesheet">
	</head>
	<body>
		
		<?php include "templates/header.html"; ?>
		
		<div class='content'>
			<h2>api</h2>
			<h3>option pricing</h3>
			<p>calculate the intrinsic value of options given the:<br>
				- current asset price<br>
				- exercise price of the option<br>
				- risk-free short-term interest rate<br>
				- days to option expiration<br>
				- volatility of the underlying asset<br>
				<br><br>
				optionally specify:<br>
				- number of days in the financial year (default 365 but commonly 360)
			</p>
			<p>the returned object</p>
			<h4>example api call:</h4>
			<div class='example call'>
				<div class='api-languages-trough'>
					</div class='api-language javascript'>javascript</div>
				</div>
				<pre class='api-example'>
var S = 10, //current asset price
	K = 10, //option exercise price
	i = 1,  //current risk-free short-term interest rate in % (will be divided by 100 by the API)
	t = 5,  //days to option expiration
	v = 80, //current asset volatility in % (will be divided by 100 by the API)
	days_in_year = 365 //optionally set the length of the year (default 365)
	
	$.ajax({
		method: "GET",
		url: "optoprice.com/api/fetch_value",
		
	})
</pre>
			</div>
		</div>
			
			
		<?php include "../templates/footer.html"; ?>
			
			
		
	</body>
</html>



<script>
	
    
    
    
</script>














