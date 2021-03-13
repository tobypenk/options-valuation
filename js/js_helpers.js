function contains(arr,el) {
	
	/*
		
		detects whether el is in arr
		
		parameters:
			arr (array): the haystack
			el (flexible type): the needle
			
		returns:
			bool: el in arr ? true : false
	*/

	for (var i=0; i<arr.length; i++) {
		if (arr[i] == el) return true;
	}
	return false;
}

function parse_get_parameters() {
	
	/*
		
		parses the search field of the window's url
		
		parameters:
			none (consumes window global variable)
			
		returns:
			array of arrays, with arr[i][0] = key and arr[i][1] = value
		
	*/

	var params = params = window.location.search
			.slice(1)
			.split("&")
			.map(function(x){return x.split("=");}),
		limit = params.filter(function(x){return x[0] == "limit"}),
		offset = params.filter(function(x){return x[0] == "offset"});

	if (limit.length == 0) params.push(["limit",10])
	if (offset.length == 0) params.push(["offset",0]);

	return dedupe_parameters(params).filter(function(x){return x.length == 2;});
}

function dedupe_parameters(params) {
	
	/*
		
		handles the case where a certain key is present more than once in the window search string
			retains only the last instance
			
		parameters:
			params (array of arrays) representing the parsed window search field
			
		returns:
			array of arrays de-duped on arr[i][0] (the key)
		
	*/
		
	var p = [], n = [], i

	for (i=params.length-1; i>=0; i--) {
		if (contains(n,params[i][0])) {
			continue;
		} else {
			p.push(params[i]);
			n.push(params[i][0]);
		}
	}
	return p;
}

function stringify_parameter_array(params) {
	return params.map(function(x){return x.join("=")})
		.join("&");
}


