/**
 * Indeterminate Checkboxes
 *
 * http://css-tricks.com/13467-indeterminate-checkboxes/
 *
 * Many thanks to Chris Coyier for this jQuery and general inspiration
 *
 */
(function($) {
	// Apparently click is better chan change? Cuz IE?
	var propFunc = $.prop ? 'prop' : 'attr';
	$('div.nestedcheckboxset input[type="checkbox"]').live('change',function(e) {
		var checked = $(this)[propFunc]("checked"),
			container = $(this).parent(),
			siblings = container.siblings();

		container.find('input[type="checkbox"]')[propFunc]({
			indeterminate: false,
			checked: checked
		});

		function checkSiblings(el) {
			var parent = el.parent().parent(),
				all = true;
			el.siblings().each(function() {
				return all = ($(this).children('input[type="checkbox"]')[propFunc]("checked") === checked);
			});
			if (all && checked) {
				parent.children('input[type="checkbox"]')[propFunc]({
					indeterminate: false,
					checked: checked
				});
				checkSiblings(parent);
			} else if (all && !checked) {
				parent.children('input[type="checkbox"]')[propFunc]("checked", checked);
				parent.children('input[type="checkbox"]')[propFunc]("indeterminate", (parent.find('input[type="checkbox"]:checked').length > 0));
				checkSiblings(parent);
			} else {
				el.parents("li").children('input[type="checkbox"]')[propFunc]({
					indeterminate: true,
					checked: $(document.body).hasClass('LeftAndMain')
				});
			}
		}
		checkSiblings(container);
	});
})(jQuery);
