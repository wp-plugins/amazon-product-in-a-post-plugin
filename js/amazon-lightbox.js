jQuery(document).ready(function() {
  appiplightbox();
});

function appiplightbox() {
	var newLinks 		= [];
	var overlay 		= jQuery(jQuery('<div id="overlay" style="display: none"></div>'));
	var container 		= jQuery(jQuery('<div id="appiplightbox" style="display: none"></div>'));
	var close 			= jQuery(jQuery('<a href="#close" class="close">&times; Close</a>'));
	var target 			= jQuery(jQuery('<div class="target"></div>'));
	var prev 			= jQuery(jQuery('<a href="#prev" class="prev">&laquo;</a>'));
	var next 			= jQuery(jQuery('<a href="#next" class="next">&raquo;</a>'));
	jQuery('a[rel^=appiplightbox]').each(function (i) {
		if(jQuery.inArray(jQuery(this).attr('rel'), newLinks)== -1){
			newLinks.push(jQuery(this).attr('rel'));
		}
	});
	for(var i=newLinks.length;i--;){
		var itemtext = 'a[rel='+newLinks[i]+']';
		jQuery(itemtext).each( function(index) {
			var itemapp = jQuery(this);
			itemapp.click(function(c) {
				c.preventDefault();
				open(jQuery(this).attr('href'));
				jQuery(itemtext).filter('.selected').removeClass('selected');
				jQuery(this).addClass('selected');
			});
			itemapp.attr({'lb-position': index});
		});
	}
	jQuery('body').append(overlay).append(container);
	container.append(close).append(target).append(prev).append(next);
	container.show().css({'top': Math.round(((jQuery(window).height() > window.innerHeight ? window.innerHeight : jQuery(window).height()) - container.outerHeight()) / 2) + 'px', 'left': Math.round((jQuery(window).width() - container.outerWidth()) / 2) + 'px', 'margin-top': 0, 'margin-left': 0}).hide();
	prev.add(next).click(function(c) {
		c.preventDefault();
		var tempo 		= jQuery('.amazon-image-wrapper a.selected');
		var itemgr 		= jQuery('.amazon-image-wrapper a.selected').attr('rel');
		var itemgb 		= jQuery('.amazon-image-wrapper a.selected').attr('lb-position');
		var mgsize 		= jQuery('a[rel='+itemgr+']').size();
		jQuery('.amazon-image-wrapper a').attr('class','');
		if(jQuery(this).is('.prev')){
			if(itemgb == 0){
				jQuery('[rel='+itemgr+'][lb-position="'+(mgsize - 1)+'"]').addClass('selected');
			}else{
				jQuery('[rel='+itemgr+'][lb-position="'+(itemgb + 1)+'"]').addClass('selected');
			}
		}else{
			if(itemgb == mgsize-1 ){
				jQuery('[rel='+itemgr+'][lb-position="0"]').addClass('selected');
			}else{
				jQuery('[rel='+itemgr+'][lb-position="'+(itemgb + 1)+'"]').addClass('selected');
			}
		}
		var maingroup 	= jQuery(itemgr);
		var current 	= parseInt(itemgb,10);
		if(jQuery(this).is('.prev')){
			if(current == 0 && mgsize == 1){
				var to = jQuery('[rel='+itemgr+'][lb-position="0"]');
			}else if(current == 0){
				var to = jQuery('[rel='+itemgr+'][lb-position="'+(mgsize-1)+'"]');
			}else{
				var to = jQuery('[rel='+itemgr+'][lb-position="'+(current - 1)+'"]');
			}
		}
		if(jQuery(this).is('.next')){
			if(current == 0 && mgsize == 1){
				var to = jQuery('[rel='+itemgr+'][lb-position="0"]');
			}else if(current == (mgsize - 1)){
				var to = jQuery('[rel='+itemgr+'][lb-position="0"]');
			}else{
				var to = jQuery('[rel='+itemgr+'][lb-position="'+(current + 1)+'"]');
			}
		}
		if(!to.size()) {
		  to = jQuery(this).is('.prev') ? jQuery('[rel='+itemgr+'][lb-position='+(mgsize-1)+']') : jQuery('[rel='+itemgr+'][lb-position="0"]');
		}
		if(to.size()) {
		  to.click();
		}
	});
	close.add(overlay).click(function(c) {
		c.preventDefault();
		overlay.add(container).fadeOut('normal');
	});

  var open = function(url) {
    if(container.is(':visible')) {
      target.children().fadeOut('normal', function() {
        target.children().remove();
        loadImage(url);
      });
    } else {
      target.children().remove();
      overlay.add(container).fadeIn('normal',function(){
        loadImage(url);
      });
    }
  }
  
  var loadImage = function(url) {
    if(container.is('.loading')) { return; }
    container.addClass('loading');
    var img = new Image();
    img.onload = function() {
      img.style.display = 'none';
      
      var maxWidth = (jQuery(window).width() - parseInt(container.css('padding-left'),10) - parseInt(container.css('padding-right'), 10)) - 100;
      var maxHeight = ((jQuery(window).height() > window.innerHeight ? window.innerHeight : jQuery(window).height()) - parseInt(container.css('padding-top'),10) - parseInt(container.css('padding-bottom'), 10)) - 100;
      
      if(img.width > maxWidth || img.height > maxHeight) { // One of these is larger than the window
        var ratio = img.width / img.height;
        if(img.height >= maxHeight) {
          img.height = maxHeight;
          img.width = maxHeight * ratio;
        } else {
          img.width = maxWidth;
          img.height = maxWidth * ratio;
        }
      }
      
      container.animate({'width': img.width,'height': img.height, 'top': Math.round(((jQuery(window).height() > window.innerHeight ? window.innerHeight : jQuery(window).height()) - img.height - parseInt(container.css('padding-top'),10) - parseInt(container.css('padding-bottom'),10)) / 2) + 'px', 'left': Math.round((jQuery(window).width() - img.width - parseInt(container.css('padding-left'),10) - parseInt(container.css('padding-right'),10)) / 2) + 'px'},'normal', function(){
        target.append(img);
        jQuery(img).fadeIn('normal', function() {
          container.removeClass('loading');
        });
      })
    }
    img.src = url;
  }
}