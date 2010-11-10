function showTooltip(x, y, contents, css, position) {
	css = jQuery.extend({
            position: 'absolute',
            display: 'none',
            top: y + "px",
            left: x + "px",
            border: '1px solid #fdd',
            padding: '2px',
            'background-color': '#fee',
            opacity: 0.80,
            'font-size': '9px',
        }, css);
    c = "";
    for(i in css){
        c+= i + ": " + css[i] + "\n";
    }
	var elem = jQuery('<div id="tooltip">' + contents + '</div>').css(css);
	elem.appendTo("body");
	var css = {};
	jQuery(position.split(/ /)).each(function(i, p){
		switch(p) {
			case 'center':
				css.left = (x - elem.outerWidth() / 2) + "px";
			break;
			case 'top':
				css.top = (y - elem.outerHeight()) + "px";
			break;
		}
	});
	elem.css(css).fadeIn(200);
}
function strtr(str, from, to) {
    if(typeof from == 'object'){
	    for(f in from){
		    str = strtr(str, f, from[f]);
	    }
    }else{
	    str = str.replace(new RegExp(from), to);
    }
    return str;
}