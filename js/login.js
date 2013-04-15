function loggedIn() {
    FB.api('/me', function(response) {
        fbUser = response;
        $(document.getElementById('content')).html(mustache(templates.statusLoggedIn, response));
		                    
        checkUser();
    });
}

function loggedOut() {
    $(document.getElementById('content')).html(mustache(templates.statusLoggedOut));
	finishLoading();
}

function checkUser() {
    var data    = fbUser;
    data.fb_id  = data.id;
    data.action = 'auth';
	
    
    $.ajax({
        type: "POST",
        url: "/php/api.php",
        data: data
    }).done(function(data) {
		if (data.result && !isNaN(data.result.id)) {
			user = data.result;
			FB.api('/me/friends', function(response) {
				postFriends(response);
			});
		}
		
		finishLoading();

	});
}
