var token='';
var user='';
var map;
var view;
var map_drawn=0;
var source;
var lat = 25;
var lon = 54;

function renderButton() {
  gapi.signin2.render('prisijungti', {
    'scope': 'profile',
    'width': 100,
    'height': 30,
    'longtitle': false,
    'theme': 'dark',
    'onsuccess': onSignIn,
    'onfailure': onFailure
  });
}

function onFailure(error) {
  alert('Nepavyko: ' + error);
  $('#login').show();
  $('#logout').hide();
}

function onSignIn(googleUser) {
  console.log('Start onSignIn');
  var profile = googleUser.getBasicProfile();
  //console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
  console.log('Name: ' + profile.getName());
  //console.log('Image URL: ' + profile.getImageUrl());
  console.log('Email: ' + profile.getEmail()); // This is null if the 'email' scope is not present.
  token = googleUser.getAuthResponse().id_token;
  $('#login').hide();
  $('#logout').show();
  check();
}

function signOut() {
  console.log('Start signOut');
  var auth2 = gapi.auth2.getAuthInstance();
  auth2.signOut().then(function () {
    console.log('Atsijungta');
  });
  window.location.href = '';
}

function check() {
  console.log('Start');
  var auth2 = gapi.auth2.getAuthInstance();
  if (auth2.isSignedIn.get()) {
    $('#login').hide();
    var profile = auth2.currentUser.get().getBasicProfile();
    //console.log('ID: ' + profile.getId());
    //console.log('Full Name: ' + profile.getName());
    //console.log('Given Name: ' + profile.getGivenName());
    //console.log('Family Name: ' + profile.getFamilyName());
    //console.log('Image URL: ' + profile.getImageUrl());
    //console.log('Email: ' + profile.getEmail());
    $('#user').html('<img src="' + profile.getImageUrl() + '" style="max-height: 30px;"> ' + profile.getName());
    verifyToken();
  } else {
    $('#logout').hide();
    $('#poi').html('Prisijunkite, kad galėtumėte kolekcionuoti lankytinas vietas');
    console.log('Neprisijungta');
  }
}

function verifyToken() {
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'https://places.openmap.lt/verify.php');
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onload = function() {
    console.log('Signed in as: ' + xhr.responseText);
    user = xhr.responseText;
    fetchList();
  };
  xhr.send('idtoken=' + token);
}

function loadCookie() {
  var name = "data=";
  var c = decodeURIComponent(document.cookie);
  var ca = c.split(';');
  var data = '';
  for (var i = 0; i < ca.length; i++) {
    var cc = ca[i];
    while (cc.charAt(0) == ' ') {
      cc = cc.substring(1);
    }
    if (cc.indexOf(name) == 0) {
      var data = cc.substring(name.length, cc.length);
    }
  }
  if (data !== '') {
    var parts = data.split('/');
    if (parts.length === 5) {
      lat = parts[1];
      lon = parts[2];
    }
  }
}

function fetchList() {
  $('#neprisijungta').hide();
  $('#prisijungta').show();
  loadCookie();
  console.log('lat=' + lat + ', lon=' + lon);
  switchToList();
} // fetchList

function switchToGroups() {
  $('#grupes').show();
  $('#sarasas').hide();
  $('#info').hide();
  $('#grupiu_sarasas').load('list_groups.php?t=' + user);
}

function switchToList() {
  console.log('Switching to list');
  $('#grupes').hide();
  $('#sarasas').show();
  $('#info').hide();
  console.log('fetching list for ' + user + ' lat=' + lat + ' lon=' + lon);
  console.log('url=' + 'list_collection.php?t=' + user + '&lat=' + lat + '&lon=' + lon + '&nf=yes');
  $('#vietu_sarasas').load('list_collection.php?t=' + user + '&lat=' + lat + '&lon=' + lon + '&nf=yes');
}

function showPoi(uid, lon, lat) {
  $('#grupes').hide();
  $('#sarasas').hide();
  $('#info').show();
  console.log('loading poi.php?t=' + user + '&uid=' + uid);
  $('#status').load('poi.php?t=' + user + '&uid=' + uid);
  console.log('loading https://places.openmap.lt/info.php?uid=' + uid);
  $('#detail').load('info.php?id=' + uid);
  initMap(lon, lat);
}

function changeStatus(uid, stat) {
  console.log('Status change ' + 'poi.php?t=' + user + '&uid=' + uid + '&op=' + stat);
  $('#status').load('poi.php?t=' + user + '&uid=' + uid + '&op=' + stat);
}

function groupOn(g) {
  $('#grupiu_sarasas').load('list_groups.php?t=' + user + '&on=' + g);
}

function groupOff(g) {
  $('#grupiu_sarasas').load('list_groups.php?t=' + user + '&off=' + g);
}

function initMap(lat, lon) {
  if (map_drawn == 1) {
    moveMarker(lat, lon);
    return;
  }
  var zoom = 16;
  //var center = new ol.proj.fromLonLat([25.29, 54.69]);
  var center = new ol.proj.fromLonLat([lat, lon]);
  var layer = new ol.layer.Tile({source: new ol.source.OSM({url:'https://dev.openmap.lt/tiles/{z}/{x}/{y}.png',crossOrigin:null}), visible: true});
  view = new ol.View({
          center: center,
          zoom: zoom,
          minZoom: 1,
          maxZoom: 18
        });

  var marker = new ol.Feature({
        type: 'icon',
        geometry: new ol.geom.Point(center)
      });
  source = new ol.source.Vector({features: [marker]});
  var vectorLayer = new ol.layer.Vector({
    source: source,
    style: new ol.style.Style({
          image: new ol.style.Circle({
            radius: 7,
            snapToPixel: false,
            /*fill: new ol.style.Fill({color: 'black'}),*/
            stroke: new ol.style.Stroke({
              color: 'black', width: 2
            })
          })
        })
  });

  map = new ol.Map({
        target: 'map',
        layers: [layer, vectorLayer],
        view: view
      });
  map_drawn = 1;
} // initMap

function moveMarker(lat, lon) {
  console.log('moving to ' + lat + ',' + lon);
  var center = new ol.proj.fromLonLat([lat, lon]);
  view.setCenter(center);
  view.setZoom(16);
  source.clear();
  var marker = new ol.Feature({
        type: 'icon',
        geometry: new ol.geom.Point(center)
      });
  source.addFeature(marker);
}
