
(function($){
	$(function(){
		$(".widget_collapsible_pages_widget .toggle").click(function(evt){
			console.log("clicked");
			var target = $(evt.currentTarget);

			target.siblings("ul.hidden").toggle(200);
			var oldSrc = target.attr('src');
			var newSrc = oldSrc.indexOf('icon-plus') > -1 ? oldSrc.replace('icon-plus', 'icon-minus') :
														oldSrc.replace('icon-minus', 'icon-plus') ;
			target.attr('src', newSrc);

			target.toggleClass("icon-plus");
			target.toggleClass("icon-minus");
		});
		$(document).trigger("collapsible_pages_ready");
    });
})(jQuery);


function expand_to_page(id){
	(function($){
		var li = $(".widget_collapsible_pages_widget [data-page-id='" + id + "']");
		var parent = li.parent("ul.hidden");
		while(parent.length > 0) {
			parent.siblings(".toggle").click()
			parent = parent.parent().parent("ul.hidden");
		}

	})(jQuery);
}