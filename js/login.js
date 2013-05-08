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
            if (response.authResponse && !isNaN(response.authResponse.userID)) {
                document.cookie = "roskildeapp=" + response.authResponse.userID;
            }
        } else {
            loggedOut();
        }
    });

};


function loggedIn() {
    FB.api('/me', function(response) {
        if (!isNaN(response.id)) {
            fbUser = response;

            $(document.getElementById('content')).html(mustache(templates.statusLoggedIn, response));

            checkUser();
            $(document.getElementById('user-avatar')).html(mustache(templates.userAvatarImg, response)).removeClass('none');
            document.cookie = "roskildeapp=" + response.id;

            if (localStorage.getItem('danish') === null) {
                if (response.locale === 'da_DK') {
                    console.log('Seeing Danish from FB!');
                    danish = true;
                }
            } else {
                console.log('User toggled Danish');
                danish = localStorage.getItem('danish');
            }
        }
    });
}


function loggedOut() {
    $(document.getElementById('content')).html(mustache(templates.statusLoggedOut));
    $(document.getElementById('user-avatar')).addClass('none');
    finishLoading();
}



function mainMenu() {
    if (user && user.id) {
        $(document.getElementById('content')).html(mustache(templates.statusLoggedIn, user));
        finishLoading();
    } else {
        loggedIn();
    }
    if (xhr && xhr.abort) {
        console.log(xhr);
        xhr.abort();
    }
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
        }

        finishLoading();

    }).fail(function(error) { ajaxFail(error); });
}