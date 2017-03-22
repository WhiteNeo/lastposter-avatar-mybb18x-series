$(document).on("ready", function(){
	var NavaT = 0;						
	var myTimer;
	$('a[id^="'+lpaname+'"]').on('click', function (e) {
		e.preventDefault();	
		return false;
	});
	$('a[id^="'+lpaname+'"]').on('mouseover', function(){
	var Nava = $(this).attr('id');
	Nava = Nava.substr(8);
	var ID_href = $(this).attr("href");
	var Data = "id=" + ID_href;
	var lpamyid = Nava;
	console.log(NavaT);
	if(Nava != NavaT)
	{
		myTimer = setTimeout( function()
		{			
			$.ajax({
				url:ID_href,
				data:Data,
				type:"post",
				dataType:"json",
				beforeSend:function()
				{
					$("div#"+lpaname+"mod"+lpamyid).css({
						"display": "block",
						"margin-top": "0px",
						"margin-left": "0px",
						"position": "absolute",
						"width": 320														
					});						
					$("div#"+lpaname+"mod"+lpamyid).fadeIn("fast");										
					$("div#"+lpaname+"mod"+lpamyid).html("<center><img src='images/spinner_big.gif' alt='Retrieving Data'><br>Loading...<br></center>");
				},									
				success:function(res){	
					NavaT = lpamyid;
					$("div#"+lpaname+"mod"+lpamyid+" div.modal_avatar").css("display","inline-block");
					$("div#"+lpaname+"mod"+lpamyid).html(res);
				}
			});	
		return false;
		}, lpatimer);
	}
	else
	{
		$("div#"+lpaname+"mod"+lpamyid).fadeIn("slow");
	}						
	});
	$('a[id^="mention_"]').on("mouseout", function(){
		var Nava = $(this).attr('id');
		Nava = Nava.substr(8);
		var lpamyid = Nava;		
		if(myTimer)
		clearTimeout(myTimer);				
		$("div#"+lpaname+"mod"+lpamyid).fadeOut("fast");
		$(this).stop();
	});
});