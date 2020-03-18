/* global jQuery */

jQuery(function($) {
	var $inputs = $(".zaddon-type-container input:not([type=hidden]), .zaddon-type-container select");

	var isVariations = $(".variations").length > 0;
	var $cart = $(".variations_form.cart");
	var variation = null;

	$cart.on("found_variation", function(event, current_variation) {
		variation = current_variation;
		window.setTimeout(function() {
			update_info();
		}, 40);
	});
	$cart.on("hide_variation", function(event) {
		variation = null;
		window.setTimeout(function() {
			update_info();
		}, 40);
	});

	function update_info() {
		var additional = 0;
		$(".zaddon_data").hide();
		if (isVariations && variation === null) {
			return;
		}

		$inputs.each(function() {
			switch ($(this).data("type")) {
				case "select": {
						additional += $(this).find("option:selected").data("price") ? $(this).find("option:selected").data("price") : 0;
					break;
				}

				case "radio":
				case "checkbox": {
					if ($(this).is(":checked")) additional += $(this).data("price");
					break;
				}

				case "text":
				default: {
					if ($(this).val().length > 0) additional += $(this).data("price");
					break;
				}
			}
		});

		const $additional = $(".zaddon_additional span");

		$additional.html(formatPrice(additional));

		const $total = $(".zaddon_total span");

		var price = isVariations ? variation.display_price : +$("#zaddon_base_price").val();

		var $subtotal = $(".zaddon_subtotal span");
		$subtotal.html(formatPrice(price));

		price += additional;
		$total.html(formatPrice(price));
		$(".woocommerce-variation-price").hide();

		$(".zaddon_data").show();
	}

	$inputs.on("change", update_info);


	$(".variations select").on("change", function() {
		window.setTimeout(function() {
			update_info();
		}, 40);
	});
	update_info();


	$(".zaddon-type-container").on("click", ".zaddon-open", function(e) {
		e.preventDefault();
		var $parent = $(this).parents(".zaddon-type-container");
		$parent.toggleClass("zaddon_closed");
		var $description = $parent.find(".zaddon_hide_on_toggle");
		$description.toggleClass("zaddon_hide");

		var hidden = $parent.hasClass("zaddon_closed");

		var text = hidden ? $(this).data("open") : $(this).data("close");
		$(this).html(text);
	});

	function formatPrice(price) {
		if(!Intl || !$('#zaddon_locale').val()) return price.toFixed(2);
		var formatter = new Intl.NumberFormat($('#zaddon_locale').val().replace('_', '-'), {
			style: 'currency',
			currency: $('#zaddon_currency').val(),
			minimumFractionDigits: 2,
		});
		return formatter.format(price);
	}
	window.formatPrice = formatPrice;
});
