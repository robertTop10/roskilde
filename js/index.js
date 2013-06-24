            function postFriends(friends) {
                var data        = {};
                data.action     = 'friends';
                data.id         = user.id;
                data.fb_id      = user.fb_id;
                data.friends    = friends.data;

                xhr = $.ajax({
                    type: "POST",
                    url: "/php/api.php",
                    data: data
                }).done(function(data) {
                    console.log('Friends Posted');
                }).fail(function(error) { ajaxFail(error); });

            }

            function getPosition(position) {
                // TODO - This top parsing of co-ords needs be used on all geo-location objects
                if (position && position.coords.latitude && position.coords.longitude) {
                    var data        = {};
                    var headings    = ['accuracy', 'latitude','longitude'];
                    $.each(headings, function(i,v) {
                        if (!isNaN(position.coords[v])) { data[v] = position.coords[v]; }
                    });

                    createCheckIn(data.latitude, data.longitude, data.accuracy);
                }
            }


            function initCreateEventsMap(data) {
                initMap(data, false, function(data, coords, map, markers) {
                    //var iframe    = document.getElementById('map-iframe');
                    $(iframe).after(templates.createEventOptions);

                    //iframe        = iframe.contentDocument || iframe.contentWindow.document;

                    //var m     = iframeDoc.getElementById("map-canvas");
                    var $m      = $(m);

                    $m.data({
                        'my-location-latitude': coords.coords.latitude,
                        'my-location-longitude': coords.coords.longitude,
                        'my-location-accuracy': coords.coords.accuracy,
                        'form': data
                    });

                    var html =  mustache(templates.marker, {src: '/new-images/logo.png'});

                    google.maps.event.addListener(map, 'click', function(e) {
                        if (createEventMarker) { createEventMarker.setMap(null); }
                        createEventMarker = iconPin(e.latLng.lat(), e.latLng.lng(), map, {
                            html:       html,
                            icon:       '/new-images/logo.png',
                            timestamp:  new Date().getTime(),
                            title:      'Now',
                            zIndex:     null
                        });

                        $m.data({
                            'my-marker-latitude': e.latLng.lat(),
                            'my-marker-longitude': e.latLng.lng()
                        });
                        
                        $(document.getElementById('createEventMarker')).show();
                    });
                });
            }


            function createCheckIn(latitude, longitude, accuracy, data) {
                var obj = {
                    user_id:        user.id,
                    fb_id:          user.fb_id,
                    user:           user.name,

                    latitude:       latitude,
                    longitude:      longitude,
                    accuracy:       accuracy
                };

                xhr = $.ajax({
                    type: "POST",
                    url: "/php/api.php",
                    data: {action: 'createCheckIn', event: obj}
                }).done(function(data) {
                    finishLoading(true);
                    loggedIn();
                }).fail(function(error) { ajaxFail(error); });
            }


            function createEvent(latitude, longitude, accuracy, data) {
                loading();

                var obj = {
                    user_id:        user.id,
                    fb_id:          user.fb_id,
                    user:           user.name,

                    latitude:       latitude,
                    longitude:      longitude,
                    accuracy:       accuracy,

                    name:           data.name,
                    description:    data.description,
                    start:          data.start,
                    end:            data.end
                };

                xhr = $.ajax({
                    type: "POST",
                    url: "/php/api.php",
                    data: {action: 'createEvent', event: obj}
                }).done(function(data) {
                    finishLoading(true);
                    mainMenu();
                }).fail(function(error) { ajaxFail(error); });

            }


            function createLocation(latitude, longitude, accuracy, data) {
                loading();

                var obj = {
                    user_id:        user.id,
                    fb_id:          user.fb_id,
                    user:           user.name,

                    latitude:       latitude,
                    longitude:      longitude,
                    accuracy:       accuracy,

                    title:          data.title,
                    message:        data.msg
                };

                xhr = $.ajax({
                    type: "POST",
                    url: "/php/api.php",
                    data: {action: 'createLocation', event: obj}
                }).done(function(data) {
                    finishLoading(true);
                    loggedIn();
                }).fail(function(error) { ajaxFail(error); });
            }


            function findFriends() {
                if (navigator.onLine === true) {
                    xhr = $.ajax({
                        type: "POST",
                        url: "/php/api.php",
                        data: {action: 'findFriends', id: user.id, fb_id: user.fb_id}
                    }).done(function(data) {
                        if (data.result.length === 0) {
                            alert('None of your friends have checked in.');
                        }
                        initMap(data, true, function(data, coords, map, markers) {
                            populateMarker(data, coords, map, markers, function(d, markers, z) {
                                return iconFriend(d.latitude, d.longitude, map, {
                                    icon:       d.fb_id,
                                    timestamp:  d.timestamp,
                                    title:      d.user,
                                    zIndex:     z
                                });
                            });
                        });
                        
                        setLocalStorage('friends', JSON.stringify({time: new Date().getTime(), friends: data}), true);
                        changeTitle('findFriends');

                    }).fail(function(error) { ajaxFail(error); });
                } else {
                    var data = JSON.parse(localStorage.getItem('friends'));

                    if (data !== null) {
                        alert('Your phone is offline. This data is cached from:\n\n' + timeDifference(data.time));

                        data = data.friends;

                        initMap(data, true, function(data, coords, map, markers) {
                            populateMarker(data, coords, map, markers, function(d, markers, z) {
                                return iconFriend(d.latitude, d.longitude, map, {
                                    icon:       d.fb_id,
                                    timestamp:  d.timestamp,
                                    title:      d.user,
                                    zIndex:     z
                                });
                            });
                        });

                        changeTitle('findFriends');
                    } else {
                        alert('Sorry, your phone is offline and there is no cached data.');
                        mainMenu();
                    }
                }
            }


            function getLocation() {
                if (navigator.onLine === true) {
                    xhr = $.ajax({
                        type: "POST",
                        url: "/php/api.php",
                        data: {action: 'getLocations', user_id: user.id, fb_id: user.fb_id, name: user.name}
                    }).done(function(data) {
                        initMap(data, true, function(data, coords, map, markers) {

                            var html    =  mustache(templates.marker, {src: '/new-images/logo.png'});
                            var cluster = (data.result.length > 20);
                            var newMap  = (cluster === true) ? null : map; 

                            populateMarker(data, coords, newMap, markers, function(d, markers, z) {
                                return iconPin(d.latitude, d.longitude, newMap, {
                                    html:       html,
                                    icon:       '/new-images/logo.png',
                                    img:        '/new-images/logo.png',
                                    message:    d.message,
                                    title:      d.title,
                                    tooltip:    true                        
                                });
                            });

                            assignTooltips();

                            if (cluster === true) {
                                initCluster();
                            }
                        });

                        setLocalStorage('locations', JSON.stringify({time: new Date().getTime(), locations: data}), true);
                        changeTitle('getLocation');

                    }).fail(function(error) { ajaxFail(error); });
                } else {
                    var data = JSON.parse(localStorage.getItem('locations'));

                    if (data !== null) {
                        alert('Your phone is offline. This data is cached from:\n\n' + timeDifference(data.time));

                        data = data.locations;

                        initMap(data, true, function(data, coords, map, markers) {

                            var html =  mustache(templates.marker, {src: '/new-images/logo.png'});
                            var cluster = (data.result.length > 20);
                            var newMap  = (cluster === true) ? null : map; 

                            populateMarker(data, coords, newMap, markers, function(d, markers, z) {
                                return iconPin(d.latitude, d.longitude, newMap, {
                                    html:       html,
                                    icon:       '/new-images/logo.png',
                                    img:        '/new-images/logo.png',
                                    message:    d.message,
                                    title:      d.title,
                                    tooltip:    true
                                });
                            });

                            assignTooltips();

                            if (cluster === true) {
                                initCluster();
                            }
                        });

                        changeTitle('getLocation');
                    } else {
                        alert('Sorry, your phone is offline and there is no cached data.');
                        mainMenu();
                    }
                }
            }


            function getEvents() {
                xhr = $.ajax({
                    type: "POST",
                    url: "/php/api.php",
                    data: {action: 'getEvents'}
                }).done(function(data) {
                    initMap(data, true, function(data, coords, map, markers) {
                        schedule = JSON.parse(localStorage.getItem('mySchedule'));

                        //var html =  mustache(templates.marker, {src: '/new-images/logo.png'});
                        var cluster = (data.result.length > 20);
                        var newMap  = (cluster === true) ? null : map;

                        populateMarker(data, coords, newMap, markers, function(d, markers, z) {
                            return iconPin(d.latitude, d.longitude, newMap, {
                                html:       mustache(templates.marker, {src: (d.fb_id) ? 'http://graph.facebook.com/' + d.fb_id + '/picture?width=50&height=50' : '/new-images/logo.png'}),
                                icon:       (d.fb_id) ? 'http://graph.facebook.com/' + d.fb_id + '/picture' : '/new-images/logo.png?width=50&height=50',
                                img:        (d.fb_id) ? 'http://graph.facebook.com/' + d.fb_id + '/picture' : '/new-images/logo.png?width=50&height=50',
                                message:    d.description,
                                sTime:      formatTime(d.start),
                                eTime:      formatTime(d.end),
                                ftime:      formatTime(d.fstart) + ' - ' + formatTime(d.fend),
                                timestamp:  d.start,
                                title:      d.name,
                                tooltip:    true,
                                schedule:   {
                                    button:     true,
                                    start:      d.start,
                                    end:        d.end,
                                    fstart:     d.fstart,
                                    fend:       d.fend,
                                    id:         d.id,
                                    latitude:   d.latitude,
                                    longitude:  d.longitude,
                                    fb_id:      d.fb_id
                                },
                                zIndex:     z
                            });
                        });

                        assignTooltips();

                        if (cluster === true) {
                            initCluster();
                        }
                    });

                    changeTitle('getEvents');

                }).fail(function(error) { ajaxFail(error); });
            }


            function addToMySchedule(e) {
                e.preventDefault();

                var text = (danish) ? "Vil du tilføje dette til dit skema?" : "Do you want to add to your schedule?";
                var r = confirm(text);
                if (r === true) {
                    var schedule    = JSON.parse(localStorage.getItem('mySchedule'));
                    var data        = $(e.target).data();
                    var result;

                    if (schedule === null) {
                        result = JSON.stringify([data]);
                    } else {
                        schedule.push(data);
                        result = JSON.stringify(schedule);
                    }

                    var set = setLocalStorage('mySchedule', result);

                    if (set === true) {
                        $(e.target).removeClass('add-to-schedule');
                        $(e.target).addClass('remove-from-schedule');
                        var str = (danish) ?  'Fjern fra mit skema' : 'Remove from My Schedule';
                        $(e.target).text(str);

                        finishLoading(true);
                    } else {
                        finishLoading();
                    }
                } else {
                    finishLoading();
                }
            }


            function removeFromMySchedule(e) {
                e.preventDefault();

                var text = (danish) ? "Vil du fjerne dette fra dit skema?" : "Do you want to remove this from your schedule?";
                var r = confirm(text);
                if (r === true) {
                    var schedule    = JSON.parse(localStorage.getItem('mySchedule'));
                    var data        = $(e.target).data();
                    var result;

                    var id          = data.id;
                    var type        = data.type;

                    if (data !== null && schedule !== null && schedule.length) {
                        for (i = 0, len = schedule.length; i < len; i++) {
                            if (schedule[i].id === id && schedule[i].type === type) {
                                schedule.splice(i, 1);
                                break;
                            }
                        }
                    }

                    var set = setLocalStorage('mySchedule', JSON.stringify(schedule));

                    var onMySchedule = $(e.target).hasClass('remove_my_schedule');

                    if (set === true) {
                        if (onMySchedule === true) {
                            getMySchedule();
                        } else {
                            $(e.target).removeClass('remove-from-schedule');
                            $(e.target).addClass('add-to-schedule');
                            var str = (danish) ? 'Tilføj til mit skema' : 'Add to My Schedule';
                            $(e.target).text(str);
                        }

                        finishLoading(true);
                    } else {
                        finishLoading();
                    }
                } else {
                    finishLoading();
                }
            }


            function createTooltip(obj) {
                var html = mustache(templates.tooltip, obj);
                var boxText = document.getElementById("dynamic");
                boxText.style.width = '160px';
                boxText.innerHTML   = '<span style="width: 20px; height: 20px; display: block; float: right; vertical-align: top; margin: 2px;"></span>' + html; // span is to compensate for the close button

                var myOptions = {
                    content: html,
                    pixelOffset: new google.maps.Size(-80, (boxText.offsetHeight + 40) * -1),
                    closeBoxURL: "/new-images/close.gif",
                    infoBoxClearance: new google.maps.Size(20, 40),
                    pane: "floatPane",
                    enableEventPropagation: false
                };

                return new InfoBox(myOptions);
            }


            function getMySchedule(sort) {
                var data    = JSON.parse(localStorage.getItem('mySchedule'));

                var past    = [];
                var sorted  = [];
                var exists  = 0;

                sort        = (sort) ? sort.replace('sort-', '') : null;

                if (data !== null && data.length) {
                    var a = 0;
                    var e = 0;

                    $.each(data, function(i,v) {
                        v.formattedStart    = formatTime(v.start);
                        v.formattedEnd      = formatTime(v.end);

                        if (v.type === 'artist') {
                            a++;
                            if (sort === 'artist') { sorted.push(v); }
                        }
                        if (v.type === 'event') {
                            e++;
                            if (sort === 'event') { sorted.push(v); }
                        }
                    });

                    if (sort) { data = sorted; }

                    data.sort(function(a, b) {
                        return a.start - b.start;
                    });

                    exists = data.length;

                    var now     = new Date().getTime();

                    data = $.grep(data, function(v,i) {
                        var p = (v.end > now);
                        if (!p) { past.push(v); }
                        return p;
                    });

                }

                $content.html(mustache(templates.mySchedule, {
                    results:    data,
                    length:     exists,
                    past:       past,
                    pastLength: past.length,
                    restore:    (user && user.backup === '1'),
                    sort:       (a > 0 && e > 0),
                    sortAll:    (!sort),
                    sortArtist: (sort === 'artist'),
                    sortEvent:  (sort === 'event'),
                    user:       (user) ? user : false
                }));
                finishLoading();
            }


            function getArtists() {
                if (schedule && schedule.artists) {
                    processArtists(schedule);
                } else {
                    getAllJSON(function(data) {
                        schedule = data;
                        processArtists(data);
                    });
                }
            }


            function processArtists(artists) {
                /*
                var a = [];
                $.each(artists.artists, function(i,v) {
                    if (artists.indexes[i]) {
                        a.push({header: artists.indexes[i]});
                    }

                    a.push(v);
                });
                */

                var a = [];
                // Hate this, but what can I do?
                var headers = {
                    0: 'a',
                    8: 'b',
                    26: 'c',
                    38: 'd',
                    52: 'e',
                    59: 'f',
                    64: 'g',
                    71: 'h',
                    77: 'i',
                    81: 'j',
                    83: 'k',
                    99: 'l',
                    105: 'm',
                    115: 'n',
                    121: 'o',
                    126: 'p',
                    131: 'q',
                    133: 'r',
                    138: 's',
                    156: 't',
                    168: 'u',
                    172: 'v',
                    178: 'w'
                }

                $.each(schedule.artists, function(i,v) {
                    if (headers[i]) {
                        a.push({header: headers[i]});
                    }
                    a.push(v);
                });

                $content.html(mustache(templates.listArtists, {artists: a}));
                changeTitle('getArtists');

                finishLoading();
            }


            function getNews() {
                if (navigator.onLine) {
                    var lang = (danish === true) ? '?dn=true' : '';

                    xhr = $.getJSON('/php/feeds/newsJSON.php' + lang, function(data) {
                        $content.html(mustache(templates.news, {news: data}));

                        $content.find('a').each(function(i, v) {
                            $(this).attr('target', '_blank');
                        });

                        changeTitle('getNews');
                        finishLoading();
                    });
                } else {
                    alert('Sorry, Roskilde news can only be accessed when online.');
                    finishLoading();
                }
            }


            function getTweets() {
                if (navigator.onLine) {
                    xhr = $.getJSON('/php/feeds/twitterJSON.php', function(data) {
                        $content.html(mustache(templates.tweets, {tweets: data}));

                        changeTitle('getTweets');
                        finishLoading();
                    });
                } else {
                    alert('Sorry, Roskilde tweets can only be accessed when online.');
                    finishLoading();
                }
            }

            function backupSchedule() {
                var data = localStorage.getItem('mySchedule');

                if (data) {
                    xhr = $.ajax({
                        type: "POST",
                        url: "/php/api.php",
                        data: {action: 'backupSchedule', data: data }
                    }).done(function(data) {
                        if (data.status === true) {
                            user.backup = '1';

                            if (!document.getElementById('restoreSchedule')) {
                                $(document.getElementById('cloud-schedule')).append(templates.restoreButton);
                            }
                        }

                        finishLoading(true);
                    });
                } else {
                    alert('Sorry, cannot access your schedule.');
                    finishLoading();
                }
            }

            function restoreSchedule() {
                xhr = $.ajax({
                    type: "POST",
                    url: "/php/api.php",
                    data: {action: 'restoreSchedule' }
                }).done(function(data) {
                    if (data.result && data.result[0]) {
                        setLocalStorage('mySchedule', data.result[0]);
                        getMySchedule();
                    } else {
                        alert('Sorry, couldn\'t restore your schedule. Try refreshing the page and trying again');
                        finishLoading();
                    }
                });
            }


            function displayArtist(e) {
                e.preventDefault();

                var id     = $(e.currentTarget).data('artist');

                var artist = schedule.artists[id];

                if (artist) {
                    artist.start = artist.original_timestamp * 1000;
                    artist.end   = artist.start + 3600000;

                    var daysShort   = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                    var monthNames  = [ "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" ];
                    var nth         = ['st', 'nd', 'rd', 'th'];

                    var d           = new Date(artist.start);

                    var digit       =   d.getDate().toString().slice(-1);
                    var day         =   (digit > 0 && digit <= 3) ? nth[digit - 1] : nth[3];

                    artist.formattedStartTime   = d.getHours().pad() + ':' + d.getMinutes().pad() + ' - ' + daysShort[d.getDay()];
                    artist.formattedStartDate   = d.getDate() + day + ' ' + monthNames[d.getMonth()];

                    var data    = JSON.parse(localStorage.getItem('mySchedule'));
                    var artId   = parseInt(artist['@id'], 10);

                    if (data !== null) {
                        for (i = 0, len = data.length; i < len; i++) {
                            if (data[i].id === artId && data[i].type === 'artist') {
                                artist.subscribed = true;
                                break;
                            }
                        }
                    }
                    $(document.getElementById('artist-page')).remove();
                    $(document.getElementById('hide-content')).hide();
                    $content.append(mustache(templates.artist_page, artist));
                } else {
                    alert('Sorry, artist not found.')
                    finishLoading();
                }
            }