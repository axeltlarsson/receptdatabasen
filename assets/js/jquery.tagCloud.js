$(document).ready(function(){
	/**---------------------------------------
		Taggmoln
	----------------------------------------*/
	// Ladda in listan på taggar
	$.ajax({
		type 		: 'GET',
		url 		: 'http://192.168.0.199:8082/assets/getTagList',
		dataType 	: 'json',
		cache 		: false,

		success : function(response) {
			$.each(response, function(index, value){

				if (index != "0") { // för att få bort den första konstiga "0"
					$("#tagList").append('<li class="tag" data-weight="' + value + '"><a href="recipes?searchQuery='+ index + '">' + index + '</a></li>');

				}

			});

			// Gå igenom varje tagg
			$(".tag").each(function() {
				
				// Väg dem mot varandra
				var weight = $(this).data("weight");
				$(this).css("font-size", weight + "em");
			});
		}
	});



});