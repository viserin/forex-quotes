jQuery(document).ready(function($) {
  if ($("#ticker-forex").length) {
  	var speed_scroll = 22;
    var fxData = $("#ticker-system").html();
		var width = 0;
    var k = 0;

    $("#ticker-forex #ticker-system:first-child table").each(function () {
        width += $(this).outerWidth();
    });
    $("#ticker-forex").width(width * 2);
    $(".ticker-forex-wrap").height($("#ticker-forex table").outerHeight(true));

    function qAnim() {
				$.fx.interval = 50;
				$("#ticker-forex").animate({
					"left": "-" + width + "px"
					}, {
						duration: width * speed_scroll,
						easing: "linear",
						complete: function () {
							$("#ticker-forex").css("left", "0px");
							qAnim();
							$("#ticker-system").html(fxData + fxData);
						}
				});
    }

    qAnim();
		$("#ticker-system").html(fxData + fxData);

		var dataSendTicker = {
			action: "refreshQuotesTicker"
		};

		setInterval(function() {
			$.get(ajax_action.ajaxurl, dataSendTicker, function(response) {
				if (response != "" && response != -1 && response != 0) {
			  	fxData = response;
					$("#ticker-system").html(fxData + fxData);
				}
			});
		}, php_data.expiration * 1000);
  }

  if ($("#table-forex-wrap").length) {
			var dataSendTable = {
				action: "refreshQuotesTable"
			};

			setInterval(function() {
				$.get(ajax_action.ajaxurl, dataSendTable, function(response) {
					if (response != "" && response != -1 && response != 0) {
						$("#table-forex-wrap").html(response);
					}
				});
			}, php_data.expiration * 1000);
  }

  $(".ticker-forex-wrap").mouseenter(function() {
  	$("#ticker-forex").stop();
  });

  $(".ticker-forex-wrap").mouseleave(function() {
  	qAnim();
  });
});