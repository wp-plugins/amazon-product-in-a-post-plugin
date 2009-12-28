jQuery(document).ready(function() {
  appiplightbox();
});

function appiplightbox() {
  var links = jQuery('a[rel^=appiplightbox]');
  //alert(links);
  var overlay = jQuery(jQuery('<div id="overlay" style="display: none"></div>'));
  var container = jQuery(jQuery('<div id="appiplightbox" style="display: none"></div>'));
  var close = jQuery(jQuery('<a href="#close" class="close">&times; Close</a>'));
  var target = jQuery(jQuery('<div class="target"></div>'));
  var prev = jQuery(jQuery('<!--<a href="#prev" class="prev">&laquo; Previous</a>-->'));
  var next = jQuery(jQuery('<!--<a href="#next" class="next">Next &raquo;</a>-->'));

  jQuery('body').append(overlay).append(container);
  container.append(close).append(target).append(prev).append(next);
  container.show().css({'top': Math.round(((jQuery(window).height() > window.innerHeight ? window.innerHeight : jQuery(window).height()) - container.outerHeight()) / 2) + 'px', 'left': Math.round((jQuery(window).width() - container.outerWidth()) / 2) + 'px', 'margin-top': 0, 'margin-left': 0}).hide();
  close.click(function(c) {
    c.preventDefault();
    overlay.add(container).fadeOut('normal');
  });

  prev.add(next).click(function(c) {
    c.preventDefault();
    var current = parseInt(links.filter('.selected').attr('lb-position'),10);
    var to = jQuery(this).is('.prev') ? links.eq(current - 1) : links.eq(current + 1);
    if(!to.size()) {
      to = jQuery(this).is('.prev') ? links.eq(links.size() - 1) : links.eq(0);
    }
    if(to.size()) {
      to.click();
    }
  });

  links.each(function(index) {
    var link = jQuery(this);
    link.click(function(c) {
      c.preventDefault();
      open(link.attr('href'));
      links.filter('.selected').removeClass('selected');
      link.addClass('selected');
    });
    link.attr({'lb-position': index});
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