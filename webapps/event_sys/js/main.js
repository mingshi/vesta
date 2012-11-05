$(function(){
	//初始化宽度与高度
	$("#leftPanel").width('255px');
	h = $(window).height() - 37;
	$(".panel").height(h);
	$("#vsplitBar").css({"left":$("#leftPanel").width()+"px","height":$(window).height()});
	$("#rightPanel").css({"left":($("#leftPanel").width() + 3) + "px",
						  "height":$(window).height(),
						  "width":($(window).width() - ($("#leftPanel").width() + 3)) + "px"
						 });
	$("#mainFrame").height($(window).height());
	$("#tabsNav li ").click(function(){
		panel = $(this).children("span").attr("href");
		$(panel).show();
		$(panel).siblings("div").hide();
		$(this).addClass("selected").siblings("li").removeClass("selected");
	});
	$("#pageTabs li").click(function(){
		$(this).addClass("selected").siblings("li").removeClass("selected");
		href = $(this).children("span").attr("href");
		switch (href){
			case "#pageTabs-1":
				$.ajax({
						url:"page-1.html",
						type:'post',
						dataType:"html",
						success:function(html) {
							if(!$("#pageTabs-1").html()) 
								$("#pageTabs-1").html(html);
							$("#pageTabs-1").show();
							$("#pageTabs-1").siblings("div").hide();
						}
					});
					break;
			case "#pageTabs-2":
				$.ajax({
						url:"page-2.html",
						type:'post',
						
						dataType:"html",
						success:function(html) {
							if(!$("#pageTabs-2").html())
								$("#pageTabs-2").html(html);
							$("#pageTabs-2").show();
							$("#pageTabs-2").siblings("div").hide();
						}
					});
					break;
			case "#pageTabs-3":
				$.ajax({
						url:"page-3.html",
						dataType:"html",
						type:'post',
						success:function(html) {
							if(!$("#pageTabs-3").html())
								$("#pageTabs-3").html(html);
							$("#pageTabs-3").show();
							$("#pageTabs-3").siblings("div").hide();
						}
					});
					break;
			case "#pageTabs-4":
				$.ajax({
						url:"page-4.html",
						dataType:"html",
						type:'post',
						success:function(html) {
							if(!$("#pageTabs-4").html())
								$("#pageTabs-4").html(html);
							$("#pageTabs-4").show();
							$("#pageTabs-4").siblings("div").hide();
						}
					});
					break;
			case "#pageTabs-5":
				$.ajax({
						url:"page-5.html",
						dataType:"html",
						type:'post',
						success:function(html) {
							if(!$("#pageTabs-5").html())
								$("#pageTabs-5").html(html);
							$("#pageTabs-5").show();
							$("#pageTabs-5").siblings("div").hide();
						}
					});
					break;
		}
	});
	
});