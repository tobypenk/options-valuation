<?php

?>

<html>
	<head>
		<script type="text/javascript" src="js/jquerylib.js"></script>
		<script type="text/javascript" src="js/js_helpers.js"></script>
		<script src="https://d3js.org/d3.v5.min.js"></script>
		
		<link rel='stylesheet' type='text/css' href='css/main.css' />
		
		<link href="https://fonts.googleapis.com/css?family=Inconsolata&display=swap" rel="stylesheet">
	</head>
	<body>
		
		<?php include "templates/header.html"; ?>
		
		<div class='content'>
			<h1>no-frills option valuation</h1>
			<p class='header-paragraph'>use this package to conduct option analysis given fundamental inputs. create a
				pull request or contact me directly with bugs or feature requests.
			</p>
			<h2>option value</h2>
		
			<div class='numeric-panel valuation'>
				<div class='input-panel valuation'>
					<h3>inputs</h3>
					<div class='input-wrapper'>
						<p class='input-label'>asset price</p>
						<input class='valuation-input S' placeholder='S' value=10 />
					</div>
					<div class='input-wrapper'>
						<p class='input-label'>exercise price</p>
						<input class='valuation-input K' placeholder='K' value=10 />
					</div>
					<div class='input-wrapper'>
						<p class='input-label'>interest rate %</p>
						<input class='valuation-input r' placeholder='r' value=1 />
					</div>
					<div class='input-wrapper'>
						<p class='input-label'>days to exp.</p>
						<input class='valuation-input t' placeholder='t' value=10 />
					</div>
					<div class='input-wrapper'>
						<p class='input-label'>volatility %</p>
						<input class='valuation-input vol' placeholder='s' value=80 />
					</div>
					
					
					
					
					<div class='submit valuation'>
						<p>calculate</p>
					</div>
				</div>
				
				<div class='output-panels valuation'>
					
					<div class='output-panel call'>
						<h3>call</h3>
						<div class='valuation-output value'>
							<p>value:</p><p class='val'></p>
						</div>
						<div class='valuation-output delta'>
							<p>delta:</p><p class='val'></p>
						</div>
						<div class='valuation-output gamma'>
							<p>gamma:</p><p class='val'></p>
						</div>
						<div class='valuation-output theta'>
							<p>theta:</p><p class='val'></p>
						</div>
						<div class='valuation-output vega'>
							<p>vega:</p><p class='val'></p>
						</div>
						<div class='valuation-output rho'>
							<p>rho:</p><p class='val'></p>
						</div>
						
						<div class='svg-container call-valuation'>
							<h3>call sensitivities</h3>
							<svg class='sensitivity-v-wrt-s'></svg>
							<svg class='sensitivity-v-wrt-vol'></svg>
							<svg class='sensitivity-v-wrt-t'></svg>
						</div>
						
						
						
					</div>
					
					<div class='output-panel put'>
						<h3>put</h3>
						<div class='valuation-output value'>
							<p>value:</p><p class='val'></p>
						</div>
						<div class='valuation-output delta'>
							<p>delta:</p><p class='val'></p>
						</div>
						<div class='valuation-output gamma'>
							<p>gamma:</p><p class='val'></p>
						</div>
						<div class='valuation-output theta'>
							<p>theta:</p><p class='val'></p>
						</div>
						<div class='valuation-output vega'>
							<p>vega:</p><p class='val'></p>
						</div>
						<div class='valuation-output rho'>
							<p>rho:</p><p class='val'></p>
						</div>
						
						<div class='svg-container put-valuation'>
							<h3>put sensitivities</h3>
							<svg class='sensitivity-v-wrt-s'></svg>
							<svg class='sensitivity-v-wrt-vol'></svg>
							<svg class='sensitivity-v-wrt-t'></svg>
						</div>
					</div>
				</div>
			</div>
			
			<h2>implied volatility</h2>
			
			<div class='numeric-panel implied-volatility'>
				<div class='input-panel implied-volatility'>
					<h3>inputs</h3>
					<div class='input-wrapper'>
						<p class='input-label'>asset price</p>
						<input class='implied-volatility-input S' placeholder='S' value=10 />
					</div>
					<div class='input-wrapper'>
						<p class='input-label'>exercise price</p>
						<input class='implied-volatility-input K' placeholder='K' value=10 />
					</div>
					<div class='input-wrapper'>
						<p class='input-label'>interest rate %</p>
						<input class='implied-volatility-input r' placeholder='r' value=1 />
					</div>
					<div class='input-wrapper'>
						<p class='input-label'>days to exp.</p>
						<input class='implied-volatility-input t' placeholder='t' value=10 />
					</div>
					<div class='input-wrapper'>
						<p class='input-label'>option premium</p>
						<input class='implied-volatility-input V' placeholder='V' value=0.56 />
					</div>
					
					<div class='submit implied-volatility'>
						<p>calculate</p>
					</div>
				</div>
				
				<div class='output-panels implied-volatility'>
					<div class='output-panel call'>
						<h3>call</h3>
						<div class='implied-volatility-output s'>
							<p>s %:</p><p class='val'></p>
						</div>
					</div>
					
					<div class='output-panel put'>
						<h3>put</h3>
						<div class='implied-volatility-output s'>
							<p>s %:</p><p class='val'></p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php include "templates/footer.html"; ?>
	</body>
</html>



<script>
	
	
	var d3_is_init = true;
	
	// what it should actually do is allow the input of any 5 of 6 variables and solve for the missing one.
	//	if all 6 are given, the model should assert that the option is over- or under-priced.
	
	valuation_submit();
	implied_volatility_submit();
	
	
	$(".input-panel.valuation > .submit").click(function() {
		valuation_submit();
	});
	
	$(".valuation-input").blur(function(){
		valuation_submit();
	})
	
	function valuation_submit() {
		var S = $(".valuation-input.S").val().trim(),
			K = $(".valuation-input.K").val().trim(),
			r = $(".valuation-input.r").val().trim(),
			t = $(".valuation-input.t").val().trim(),
			vol = $(".valuation-input.vol").val().trim();
			
		if (S == "") {
			throw_error("asset price must be provided.");
		} else if(K == "") {
			throw_error("exercise price must be provided.");
		} else if(r == "") {
			throw_error("interest rate must be provided.");
		} else if(t == "") {
			throw_error("period must be provided.");
		} else if(vol == "") {
			throw_error("volatility must be provided.");
		} else {
			
			var data = {S:S,K:K,r:r,t:t,s:vol};
			$.ajax({
				url: "option_value.php",
				method: "GET",
				data: data,
				success: function(d) {
					
					console.log(d);
					d = JSON.parse(d);
					console.log(d);
					
					var val, type, field;
					
					for (type of ["call","put"]) {
						for (field of ["value","delta","gamma","theta","vega","rho"]) {
							val = (Math.round(d[type][field]*1e4)/1e4).toFixed(4);
							$(".output-panel."+type+" .valuation-output."+field+" p.val").html(val);
						}
					}
					
					d3_draw_line(
						".svg-container.call-valuation > svg.sensitivity-v-wrt-s",
						svg_1,g_1,
						d.sensitivity_V_wrt_S.call,
						"S","aqua",
						"V","tomato",
						null,null,
						d3_is_init,
						false
					);
					
					d3_draw_line(
						".svg-container.call-valuation > svg.sensitivity-v-wrt-vol",
						svg_3,g_3,
						d.sensitivity_V_wrt_vol.call,
						"vol","aqua",
						"V","tomato",
						null,null,
						d3_is_init,
						false
					);
					
					d3_draw_line(
						".svg-container.call-valuation > svg.sensitivity-v-wrt-t",
						svg_5,g_5,
						d.sensitivity_V_wrt_t.call,
						"t","aqua",
						"V","tomato",
						null,null,
						d3_is_init,
						true
					);
					
					
					
					
					
					d3_draw_line(
						".svg-container.put-valuation > svg.sensitivity-v-wrt-s",
						svg_2,g_2,
						d.sensitivity_V_wrt_S.put,
						"S","aqua",
						"V","tomato",
						null,null,
						d3_is_init,
						false
					);
					
					d3_draw_line(
						".svg-container.put-valuation > svg.sensitivity-v-wrt-vol",
						svg_4,g_4,
						d.sensitivity_V_wrt_vol.put,
						"vol","aqua",
						"V","tomato",
						null,null,
						d3_is_init,
						false
					);
					
					d3_draw_line(
						".svg-container.call-valuation > svg.sensitivity-v-wrt-t",
						svg_6,g_6,
						d.sensitivity_V_wrt_t.put,
						"t","aqua",
						"V","tomato",
						null,null,
						d3_is_init,
						true
					);
					
					d3_is_init = false;
				}
			});
		}
	}
	
	$(".input-panel.implied-volatility > .submit").click(function() {
		implied_volatility_submit();
	});
	
	$(".implied-volatility-input").blur(function(){
		implied_volatility_submit();
	})
	
	function implied_volatility_submit() {
		var S = $(".implied-volatility-input.S").val().trim(),
			K = $(".implied-volatility-input.K").val().trim(),
			r = $(".implied-volatility-input.r").val().trim(),
			t = $(".implied-volatility-input.t").val().trim(),
			V = $(".implied-volatility-input.V").val().trim();
			
		if (S == "") {
			throw_error("asset price must be provided.");
		} else if(K == "") {
			throw_error("exercise price must be provided.");
		} else if(r == "") {
			throw_error("interest rate must be provided.");
		} else if(t == "") {
			throw_error("period must be provided.");
		} else if(V == "") {
			throw_error("option premium must be provided.");
		} else {
			
			var data = {S:S,K:K,r:r,t:t,V:V};
			$.ajax({
				url: "implied_volatility.php",
				method: "GET",
				data: data,
				success: function(d) {
					
					console.log(d);
					d = JSON.parse(d);
					console.log(d);
					
					var val, type, field;
					
					for (type of ["call","put"]) {
						for (field of ["s"]) {
							val = (Math.round(d[type][field]*1e6)/1e4).toFixed(4);
							$(".output-panel."+type+" .implied-volatility-output."+field+" p.val").html(val);
						}
					}
				}
			});
		}

	}
	
	function throw_error(message) {
		console.log(message);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	var margin = {top: 10, right: 10, bottom: 40, left: 50}
		, width = $(".output-panels.valuation").width()*.5 - margin.left - margin.right
		, height = 200 - margin.top - margin.bottom;

	var t = d3.transition()
      .duration(750);
      
    var svg_1 = d3.select(".svg-container.call-valuation > svg.sensitivity-v-wrt-s")
	    .attr("width", width + margin.left + margin.right)
	    .attr("height", height + margin.top + margin.bottom);
      
    var g_1 = svg_1.append("g")
    	.attr("transform","translate(" + margin.left + "," + margin.top + ")")
    	.attr("class","wrapper");
    	
    var svg_3 = d3.select(".svg-container.call-valuation > svg.sensitivity-v-wrt-vol")
	    .attr("width", width + margin.left + margin.right)
	    .attr("height", height + margin.top + margin.bottom);
      
    var g_3 = svg_3.append("g")
    	.attr("transform","translate(" + margin.left + "," + margin.top + ")")
    	.attr("class","wrapper");
    	
    var svg_5 = d3.select(".svg-container.call-valuation > svg.sensitivity-v-wrt-t")
	    .attr("width", width + margin.left + margin.right)
	    .attr("height", height + margin.top + margin.bottom);
      
    var g_5 = svg_5.append("g")
    	.attr("transform","translate(" + margin.left + "," + margin.top + ")")
    	.attr("class","wrapper");
    	
    	
    	
    	
    var svg_2 = d3.select(".svg-container.put-valuation > svg.sensitivity-v-wrt-s")
	    .attr("width", width + margin.left + margin.right)
	    .attr("height", height + margin.top + margin.bottom);
      
    var g_2 = svg_2.append("g")
    	.attr("transform","translate(" + margin.left + "," + margin.top + ")")
    	.attr("class","wrapper");
    	
    var svg_4 = d3.select(".svg-container.put-valuation > svg.sensitivity-v-wrt-vol")
	    .attr("width", width + margin.left + margin.right)
	    .attr("height", height + margin.top + margin.bottom);
      
    var g_4 = svg_4.append("g")
    	.attr("transform","translate(" + margin.left + "," + margin.top + ")")
    	.attr("class","wrapper");
    	
    var svg_6 = d3.select(".svg-container.put-valuation > svg.sensitivity-v-wrt-t")
	    .attr("width", width + margin.left + margin.right)
	    .attr("height", height + margin.top + margin.bottom);
      
    var g_6 = svg_6.append("g")
    	.attr("transform","translate(" + margin.left + "," + margin.top + ")")
    	.attr("class","wrapper");
    	
    function init_axes(
    	g,
    	x_axis_call,x_name,x_color,
    	y_l_axis_call,y_l_axis_name,y_l_color,
    	y_r_axis_call=null,y_r_axis_name=null,y_r_color=null) 
    {
        g.append("g")
		    .attr("class", "x axis")
		    .attr("transform", "translate(0," + height + ")")
		    .call(x_axis_call)
		    .selectAll("text")
		    .attr("y", "14")
		    .attr("x", "-10")
		    .attr("font-size", "75%")
    		.attr("transform", "rotate(-30)");
    		
    	g.append("text")
		    .attr("stroke", x_color)
			.attr("x", width / 2)
			.attr("y", height + margin.bottom / 2)
			.attr("dy", "1em")
			.text(x_name);

        g.append("g")
		    .attr("class", "y axis left")
		    .attr("transform", "translate(0,0)")
		    .call(y_l_axis_call)
		    .selectAll("text")
		    .attr("font-size", "75%");

		g.append("text")
	      	.attr("transform", "rotate(-90)")
		    .attr("stroke", y_l_color)
	      	.attr("y", -margin.left)
	      	.attr("x", -height / 2)
	      	.attr("dy", "1em")
	      	.style("text-anchor", "middle")
	      	.text(y_l_axis_name);     

		if (y_r_axis_call != undefined) {
			g.append("g")
			    .attr("class", "y axis right")
			    .attr("transform", "translate("+width+",0)")
			    .call(y_r_axis_call);

			g.append("text")
		      	.attr("transform", "rotate(90)")
		      	.attr("stroke", y_r_color)
		      	.attr("y", -width - margin.left )
		      	.attr("x", height / 2)
		      	.attr("dy", "1em")
		      	.style("text-anchor", "middle")
		      	.text(y_r_axis_name);   
		}
    }
    
    function update_axes(svg,x_axis_call,y_l_axis_call,y_r_axis_call=null){

        svg.select(".x.axis")
            .transition(t)
            .call(x_axis_call)
		    .selectAll("text")
		    .attr("y", "14")
		    .attr("x", "-10")
		    .attr("font-size", "75%")
    		.attr("transform", "rotate(-30)");
        
        svg.select(".y.axis.left")
            .transition(t)
            .call(y_l_axis_call)
            .selectAll("text")
		    .attr("font-size", "75%");
        
        if (y_r_axis_call != undefined) {
			svg.select(".y.axis.right")
	            .transition(t)
	            .call(y_r_axis_call);
		}
    }

    function d3_set_axis_scales(
    	parent,
    	x_axis_call,x_scale,x_domain,
    	y_l_axis_call,y_l_scale,y_l_domain,
    	y_r_axis_call=null,y_r_scale=null,y_r_domain=null
    ){
        x_scale
        	.domain(x_domain)
        	.range([0, width])
        	//.padding(0.1);
        x_axis_call.scale(x_scale);

        y_l_scale
        	.range([height, 0])
			.domain(y_l_domain);
        y_l_axis_call.scale(y_l_scale);

        if (y_r_axis_call != undefined) {
			y_r_scale
	        	.range([height, 0])
				.domain(y_r_domain);
	        y_r_axis_call.scale(y_r_scale);
		}
    }

	function d3_draw_line(
		parent,
		svg,g,
		data,
		x_name,x_color,
		y_l_name,y_l_color,
		y_r_name=null,y_r_color=null,
		is_init,
    	reverse_x_axis=false
	) 
	{	

		if (reverse_x_axis) {
			var x_max = d3.min(data, function(d) { return d[x_name]; }),
				x_min = d3.max(data, function(d) { return d[x_name]; });
		} else {
			var x_min = d3.min(data, function(d) { return d[x_name]; }),
				x_max = d3.max(data, function(d) { return d[x_name]; });
		}
		
			
		var x_scale = d3.scaleLinear(),
	    	x_domain = [x_min,x_max],
	    	x_axis_call = d3.axisBottom(),

			y_min = d3.min(data, function(d) { return d[y_l_name]; }),
			y_max = d3.max(data, function(d) { return d[y_l_name]; }),
			y_pad = Math.abs(y_min-y_max) * 0.1,
			
	    	y_l_scale = d3.scaleLinear(),
	    	y_l_domain = [y_min - y_pad,y_max + y_pad],
	    	y_l_axis_call = d3.axisLeft();

	    if (y_r_name != undefined) {
	    	var y_r_scale = d3.scaleLinear(),
		    	y_r_domain = [0,d3.max(data, function(d) { return d[y_r_name]; })],
		    	y_r_axis_call = d3.axisRight();
	    } else {
	    	var y_r_scale = null,
	    		y_r_domain = null,
	    		y_r_axis_call = null;
	    }

		var x = x_scale
			.range([0, width])
			//.padding(0.1)
			.domain(x_domain);

		var y_l = y_l_scale
			.range([height, 0])
			.domain(y_l_domain);

		var line_l = d3.line()
		    .x(function(d) { return x(d[x_name]); })
		    .y(function(d) { return y_l(d[y_l_name]); })
		    .curve(d3.curveMonotoneX);

		if (y_r_name != undefined) {

			var y_r = y_r_scale
				.range([height, 0])
				.domain(y_r_domain);

			var line_r = d3.line()
			    .x(function(d) { return x(d[x_name]); })
			    .y(function(d) { return y_r(d[y_r_name]); })
			    .curve(d3.curveMonotoneX);
		}

		d3_set_axis_scales(
			parent,

			x_axis_call,
			x_scale,
			x_domain,

			y_l_axis_call,
			y_l_scale,
			y_l_domain,

			y_r_axis_call,
			y_r_scale,
			y_r_domain
		);

		if (is_init) {
			init_axes(
				g,

				x_axis_call,
				x_name,
				x_color,

				y_l_axis_call,
				y_l_name,
				y_l_color,

				y_r_axis_call,
				y_r_name,
				y_r_color
			);

		} else {
			
			update_axes(
				svg,
				x_axis_call,
				y_l_axis_call,
				y_r_axis_call
			);
		}




		var path_l = g.selectAll("."+y_l_name+"-line")
			.data([data]);
		path_l.transition(t).attr("d",line_l)
			.attr("class", "line "+y_l_name+"-line")
			.attr("stroke", y_l_color)
		path_l.enter().append("path")
		  .transition(t)
			.attr("d",line_l)
			.attr("class", "line "+y_l_name+"-line")
			.attr("stroke", y_l_color)
		path_l.exit().remove();

		var dots_l = g.selectAll("."+y_l_name+"-dot")
		    .data(data);

		dots_l.attr("class", "dot "+y_l_name+"-dot")
			.attr("fill", y_l_color)
		  .transition(t)
		    .attr("cx", function(d) { return x(d[x_name]); })
		    .attr("cy", function(d) { return y_l(d[y_l_name]); })
		    .attr("r", 2);

		dots_l.enter().append("circle")
			.attr("fill", y_l_color)
		    .attr("class", "dot "+y_l_name+"-dot")
		  .transition(t)
		    .attr("cx", function(d) { return x(d[x_name]); })
		    .attr("cy", function(d) { return y_l(d[y_l_name]); })
		    .attr("r", 2);

		dots_l.exit().remove();


		if (y_r_name != undefined) {
			var path_r = g.selectAll("."+y_r_name+"-line")
				.data([data]);
			path_r.transition(t).attr("d",line_r)
				.attr("stroke", y_r_color)
				.attr("class", "line "+y_r_name+"-line")
			path_r.enter().append("path")
			  .transition(t)
				.attr("d",line_r)
				.attr("stroke", y_r_color)
				.attr("class", "line "+y_r_name+"-line")
			path_r.exit().remove();

			var dots_r = g.selectAll("."+y_r_name+"-dot")
			    .data(data);

			dots_r.attr("class", "dot "+y_r_name+"-dot")
				.attr("fill", y_r_color)
			  .transition(t)
			    .attr("cx", function(d) { return x(d[x_name]); })
			    .attr("cy", function(d) { return y_r(d[y_r_name]); })
			    .attr("r", 2);

			dots_r.enter().append("circle")
			    .attr("class", "dot "+y_r_name+"-dot")
				.attr("fill", y_r_color)
			  .transition(t)
			    .attr("cx", function(d) { return x(d[x_name]); })
			    .attr("cy", function(d) { return y_r(d[y_r_name]); })
			    .attr("r", 2);

			dots_r.exit().remove();
		}
	}
    
    
    
    
    
    
</script>