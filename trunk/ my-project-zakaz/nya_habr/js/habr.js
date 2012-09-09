$(function() {
    $('.habr_plus_minus a').click(function(e){e.stopPropagation();show(this); return false});
    $('body').click(function(){hide();});
    $(".habr_message").hover(
        function () {
            $(this).stop().animate({opacity:'1.0'},600);
        },
        function () {
            $(this).stop().animate({opacity:'0.3'},600);
        }
    );

    $(".habr_message_white").hover(
        function () {
            $(this).stop().animate({opacity:'1.0'},600);
        },
        function () {
            $(this).stop().animate({opacity:'0'},600);
        }
    );

	var shown = false, cur_el = null;
	
	$("#brd-wrap").append('<table class="habr_popup"><tbody><tr><td id="topleft" class="corner"></td><td class="top"></td><td id="topright" class="corner"></td></tr><tr><td class="left"></td><td id="tooltip_content"></td><td class="right"></td></tr><tr><td class="corner" id="bottomleft"></td><td class="bottom"><span class="arrow"><!-- //--></span></td><td id="bottomright" class="corner"></td></tr></tbody></table>');
	var tip = $('.habr_popup').css('opacity', 0);

	function get(e, func){
		$.ajax({
			url: cur_el,
			type: "GET",
			cache: false,
			dataType: "json",
			timeout: 3000,
			
			success:function(data){
				$('#tooltip_content').html(data.message);
				func();
			},
			
			error: function(){
				$('#tooltip_content').html(data.message);
				func();
			}
		});		
		return false;
	}
	
	function show(e) {
		var h = $(e).attr('rel');
		if (shown) {
			if (h != cur_el)	{
				tip.css('display', 'none');
			}
			else {
				return hide();
			}
		}
		cur_el = h;
		
		var offset = $(e).offset();
		get(e, function(){
			tip.css( {
				top : offset.top - tip.height() + 10,
				left : offset.left - tip.width()/2 + $(e).width()/2,
				display : 'block'
			}).animate( {
				top : '-=10px',
				opacity : 1
			}, 250, 'swing', function() {
				shown = true;
			});
		});
		return false;
	}
	
	function hide(){
		if (!shown)
			return false;
		tip.animate( {
			top : '-=10px',
			opacity : 0
		}, 250, 'swing', function() {
			shown = false;
			tip.css('display', 'none');
		});
		return false;
	}
});


var habr = {
	response : {},
	url : '',

	updateTips: function(t) {
		$(".validateTips").html(t).addClass("ui-state-highlight");
		setTimeout(function() {
				$(".validateTips").removeClass("ui-state-highlight", 1500);
			},500
		);
	},
	
	send_data: function() {

		$.ajax({
			url: habr.url,
			type: "POST",
			cache: false,
			data: {csrf_token : habr.response.csrf_token, form_sent : 1, req_message : $("#habr_form_reason").val()},
			dataType: "json",
			timeout: 3000,
			
			success:function(data){
				if (data.error != undefined)
				{
					habr.updateTips(data.error);
					$("#habr_form_reason").addClass("ui-state-error");
					return;
				}
				
				if (data.message != undefined)
				{
					$("#habr_form").dialog("close");
					alert(data.message);
					return;
				}
				
				if (data.destination_url != undefined)
				{
					window.location = data.destination_url;
				}
				window.location.reload(true);
				return;
			},
			
			error: function(){
				alert('error!');
				window.location = habr.url;
			}
			
		});
	},
	show_form: function(data) {
		$("#habr_form_description").html(data.description);
		$("#habr_form").dialog({
			height: "auto",
			width: 350,
			title : data.title,
			show: "fade",
			hide: "fade",
			resizable: false,
			modal: true,
			buttons: [{
				text: data.submit,
				click: function() {
					habr.send_data();
				}
			}],
			close: function() {
				$(".validateTips").empty();
				$("#habr_form_reason").val("").removeClass("ui-state-error");
			}				
		});			
	},
	init:function(){
		$(document).ready(function(){
			$("#brd-wrap").append('<div id="habr_form" style="display:none;pading:0 0;"><p id="habr_form_description"></p><p class="validateTips"></p><textarea style="width:97%;height:118px" id="habr_form_reason" /></div><div id="habr_error" style="display:none;pading:0 0;"><p></p></div>');
			
			$(".habr_info_link").click(function(){
				habr.url = $(this).attr("href");
				$.ajax({
					url: habr.url,
					type: "GET",
					cache: false,
					dataType: "json",
					timeout: 3000,
					
					success:function(data){
						if (data.code = -1 && data.message != undefined)
						{
							$("#habr_error").dialog({resizable: false});
							$("#habr_error p").html(data.message);
							return;
						}
						habr.response = data;
						habr.show_form(data);
					},
					
					error: function(){
						window.location = habr.url;
					}
					
				});		
				return false;
			});
		});		
	}
}
habr.init();