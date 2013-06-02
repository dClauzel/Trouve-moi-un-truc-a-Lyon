	 $(function() {
	   var el, newPoint, newPlace, offset;
	   $("input[type='range']").change(function() {
	     el = $(this);
	     width = el.width();
	     newPoint = (el.val() - el.attr("min")) / (el.attr("max") - el.attr("min"));
	     offset = -1.3;
	     if (newPoint < 0) { newPlace = 0;  }
	     else if (newPoint > 1) { newPlace = width; }
	     else { newPlace = width * newPoint + offset; offset -= newPoint;}
	     el
	       .next("output")
	       .css({
	         left: newPlace,
	         marginLeft: offset + "%"
	       })
	       .text(el.val());
	   })
	   .trigger('change');
	 });
