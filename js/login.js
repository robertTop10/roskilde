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

    console.log('onLine', navigator.onLine);
    if (navigator.onLine === false) {
        console.log('offline');
        offlineAccess();
    }

};


function loggedIn() {
    FB.api('/me', function(response) {
        if (!isNaN(response.id)) {
            fbUser = response;

            if (localStorage.getItem('danish') === null) {
                if (response.locale === 'da_DK') {
                    console.log('Seeing Danish from FB!');
                    danish = true;
                }
            } else {
                console.log('User toggled Danish');
                danish = (localStorage.getItem('danish') === "true");
            }

            $content.html(mustache(templates.statusLoggedIn, response));

            changeTitle();
            checkUser();

            $(document.getElementById('user-avatar')).html(mustache(templates.userAvatarImg, response)).removeClass('none');

            var lng = (danish) ? 'dk' : 'en';
            $(document.getElementById('user-avatar')).addClass(lng);

            document.cookie = "roskildedanish=" + danish;
            document.cookie = "roskildeapp=" + response.id;
        } else {
            loggedOut();
        }
    });
}


function loggedOut() {
    $content.html(mustache(templates.statusLoggedOut));
    $(document.getElementById('user-avatar')).addClass('none');
    finishLoading();
}



function mainMenu() {
    changeTitle();

    if (user && user.id) {
        $content.html(mustache(templates.statusLoggedIn, user));
        finishLoading();
    } else {
        loggedIn();
    }
    if (xhr && xhr.abort) {
        console.log(xhr);
        xhr.abort();
    }

    map = null;
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
            setLocalStorage('user', JSON.stringify(data.result), true);
        }

        finishLoading();

    }).fail(function(error) { ajaxFail(error); });
}


function offlineAccess() {
    console.log('offlineAccess');

    var u = localStorage.getItem('user');
    var t = templates.statusLoggedOut;
    if (u) {
        user    = JSON.parse(u);
        t       = templates.statusLoggedIn;
    }

    $content.html(mustache(t, user));
    finishLoading();
}