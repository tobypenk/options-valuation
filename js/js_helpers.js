function contains(arr,el) {

	for (var i=0; i<arr.length; i++) {
		if (arr[i] == el) return true;
	}
	return false;
}

function parse_get_parameters() {

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