require(['jquery'],function ($) {
	$(function () {
		$("[title='magedelight_base']").hide();

		$(document).on('click', ".item-md-base-root [role='menu-item'] a:not([target=_blank])", function (e) {
			var self = this;
			$(".item-md-base-root a[data-role='close-submenu']").trigger('click');

			setTimeout(function () {
				var li_id = $(self).parents("li[role='menu-item']").attr('data-ui-id');
				li_id = li_id.replace('-commonlyvisible', '');
				$("li[data-ui-id='"+li_id+"'] > a").trigger('click');
			});
		});

		$(document).on('click', ".level-0[id*='menu-magedelight-']:not([id='menu-magedelight-base-md-base-root']) a[data-role='close-submenu']", function (e) {
			$('.item-md-base-root.level-0 > a').trigger('click');
		});
	});
});