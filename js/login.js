window.fbAsyncInit = function() {
    // init the FB JS SDK
    FB.init({
        appId      : '357860537664045', // App ID from the App Dashboard
        channelUrl : 'http://r.oskil.de/php/channel.php', // Channel File for x-domain communication
        status     : true, // check the login status upon init?
        cookie     : true, // set sessions cookies to allow your server to access the session?
        xfbml      : false  // parse XFBML tags on this page?
    });

    // Additional initialization code such as adding Event Listeners goes here
    FB.getLoginStatus(function(response) {
        if (response.status === 'connected') {
            loggedIn();
        } else {
            loggedOut();
        }
    });
    
};


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
