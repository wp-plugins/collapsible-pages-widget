
(function($){
	$(function(){
		$(".widget_collapsible_pages_widget .icon-plus").click(function(evt){
			console.log("clicked");
			var target = $(evt.currentTarget);

			target.parent().siblings("ul.children").show(200);
			target.hide();
			target.siblings('.icon-minus').show();

		});

		$(".widget_collapsible_pages_widget .icon-minus").click(function(evt){
			console.log("clicked");
			var target = $(evt.currentTarget);

			target.parent().siblings("ul.children").hide(200);
			target.hide();
			target.siblings('.icon-plus').show();

		});
		$(document).trigger("collapsible_pages_ready");
    });
})(jQuery);


function expand_to_page(id){
	(function($){
		var li = $(".widget_collapsible_pages_widget [data-page-id='" + id + "']");
		var parent = li.parent("ul.children");
		while(parent.length > 0) {
			parent.siblings('.toggle-item').children('.icon-plus').click();
			parent = parent.parent().parent("ul.children");
		}

	})(jQuery);
}