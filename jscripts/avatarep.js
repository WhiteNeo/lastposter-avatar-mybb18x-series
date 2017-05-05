/**
*@ Autor: Dark Neo
*@ Fecha: 2013-12-12
*@ Version: 2.9.3
*@ Contacto: neogeoman@gmail.com
*/
$(document).on("ready", function(){
	var NavaT = 0;						
	var myTimer;
	$('a[class^="'+lpaname+'"]').on('click', function (e) {
		e.preventDefault();	
		return false;
	});
	$('a[class^="'+lpaname+'"]').on('mouseover', function(){
	var Nava = $(this).attr('id');
	var Navan = lpaname.length;
	Nava = Nava.substr(Navan);
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
						"display": "inline-block",
						"width": 320														
					});						
					$("div#"+lpaname+"mod"+lpamyid).fadeIn("fast");										
					$("div#"+lpaname+"mod"+lpamyid).html("<center><img src='images/spinner_big.gif' alt='Retrieving Data'><br>Loading...<br></center>");
				},									
				success:function(res){	
					NavaT = lpamyid;
					$("div#"+lpaname+"mod"+lpamyid).html(res);
				}
			});	
		return false;
		}, lpatimer);
	}
	else
	{
		$(this).stop();
		$("div#"+lpaname+"mod"+lpamyid).css("display","inline-block");		
		$("div#"+lpaname+"mod"+lpamyid).fadeIn("slow");
	}						
	});
	$('a[class^="'+lpaname+'"]').on("mouseout", function(){
		var Nava = $(this).attr('id');
		var Navan = lpaname.length;
		Nava = Nava.substr(Navan);
		var lpamyid = Nava;		
		if(myTimer)
		clearTimeout(myTimer);				
		$("div#"+lpaname+"mod"+lpamyid).fadeOut("fast");
		$(this).stop();
	});
});