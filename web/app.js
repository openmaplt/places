var map;
var view;
var center = [25.29, 54.69];
var zoom = 13;
var pois = '';
var layer = 'T';
var lPoi;
var visible = false;
var des;

var lTour = new ol.layer.Tile({source: new ol.source.OSM({url:'https://dev.openmap.lt/tiles/{z}/{x}/{y}.png',crossOrigin: null}),
                         visible: false});
var lTransp = new ol.layer.Tile({source: new ol.source.OSM({url:'https://dev.openmap.lt/transp/{z}/{x}/{y}.png',crossOrigin: null, opaque: false}),
                         visible: false});
var lOrto = new ol.layer.Tile({source: new ol.source.OSM({url:'https://ort10lt.openmap.lt/g16/{z}/{x}/{y}.jpeg'}),
                         visible: false});

function poiLoader(extent, resolution, projection) {
    var epsg4326Extent =
        ol.proj.transformExtent(extent, projection, 'EPSG:4326');
    epsg4326Extent[0] = Math.round(epsg4326Extent[0]*100000)/100000;
    epsg4326Extent[1] = Math.round(epsg4326Extent[1]*100000)/100000;
    epsg4326Extent[2] = Math.round(epsg4326Extent[2]*100000)/100000;
    epsg4326Extent[3] = Math.round(epsg4326Extent[3]*100000)/100000;
    var url = 'https://places.openmap.lt/list.php?type=' + pois + '&bbox=' + epsg4326Extent.join(',');
    $.ajax(url).then(function(response) {
      var format = new ol.format.GeoJSON();
      var features = format.readFeatures(response,
          {featureProjection: projection});
      lSource.addFeatures(features);
    });
}

var lSource = new ol.source.Vector({
   attributions: [ol.source.OSM.ATTRIBUTION]
  ,loader: poiLoader
  ,strategy: ol.loadingstrategy.bbox
  ,dataProjection: 'EPSG:4326'
  ,projection: 'EPSG:900913'
});

lPOI = new ol.layer.Vector({source: lSource, style: styleFunction, visible: false});

var inter = new ol.interaction.Select();

function hideDescription() {
  if (des.style.visibility == 'visible') {
    des.style.visibility = 'hidden';
  }
} // hideDescription

function hideLayers() {
  $('#buttons').show();
  $('#lay').hide("slide", { direction: "right"}, 400);
}

function hidePois() {
  $('#buttons').show();
  $('#poi').hide("slide", { direction: "right"}, 400);
}

function saveCookie(hash) {
  var d = new Date();
  d.setTime(d.getTime() + (365*24*60*60*1000));
  var expires = "expires="+ d.toUTCString();
  document.cookie = "data=" + hash + ";" + expires + ";path=/";
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
  return data;
}

function mapChanged() {
  var center = ol.proj.transform(view.getCenter(), 'EPSG:102100', 'EPSG:4326');
  var hash = '#m=' +
    view.getZoom() + '/' +
    Math.round(center[0] * 1000) / 1000 + '/' +
    Math.round(center[1] * 1000) / 1000 + '/' +
    pois + '/' +
    layer;
  window.location.hash = hash;
  saveCookie(hash.replace('#m=', ''));
} // mapChanged

function readUrl() {
  var cookieLoaded = false;
  if (window.location.hash !== '') {
    var hash = window.location.hash.replace('#m=', '');
  } else {
    var hash = loadCookie();
    if (hash !== '') {
      cookieLoaded = true;
    }
  }
  if (hash !== '') {
    var parts = hash.split('/');
    if (parts.length === 5) {
      zoom = parseInt(parts[0], 10);
      center = [
        parseFloat(parts[1]),
        parseFloat(parts[2])
      ];
      pois = parts[3];
      if (pois == '-') {
        pois = '';
      }
      layer = parts[4];
      for (var i = 0, len = pois.length; i < len; i++) {
        $("#" + pois[i]).toggleClass('selected');
      }
    }
  }
  if (pois == '' && cookieLoaded == false) {
    $("#hint").show();
  }
}

function showError(error) {
    switch(error.code) {
        case error.PERMISSION_DENIED:
            alert("Jūsų naršyklė neleidžia nustatyti savo pozicijos.")
            break;
        case error.POSITION_UNAVAILABLE:
            alert("Pozicijos informacija neprieinama.")
            break;
        case error.TIMEOUT:
            alert("Baigėsi pozicijos nustatymo laikas.")
            break;
        case error.UNKNOWN_ERROR:
            alert("Įvyko nežinoma klaida.")
            break;
    }
}

function showPosition(position) {
    var lonlat = ol.proj.fromLonLat([position.coords.longitude, position.coords.latitude]);
    view.animate({center: lonlat, zoom: 17});
}

function showMainContent() {
  $("#loader").hide();
  $("#buttons").removeClass("hidden");
  $("#hint").removeClass("hiddenhint");
} // ShowMainContent

function init() {
try {
  readUrl();
  des = document.getElementById('desc');
  des.style.visibility = 'hidden';
  var attr = new ol.control.Attribution({collapsible: false});
  view = new ol.View({
      center: ol.proj.transform(center, 'EPSG:4326', 'EPSG:102100'),
      projection: 'EPSG:102100',
      zoom: zoom,
      minZoom: 8,
      maxZoom: 18,
      enableRotation: false
    });
  switchToLayer(layer);
  map = new ol.Map({
    target: 'map',
    layers: [
      lTour,
      lOrto,
      lTransp
    ],
    view: view,
    controls: ol.control.defaults({zoom: false, attribution: false}).extend([attr])
  });
  map.on('moveend', mapChanged);
  map.addInteraction(inter);
  inter.on('select', function(e) {
    var c = inter.getFeatures().getLength();
    if (c == 0) {
      //$('#desc').hide();
      des.style.visibility = 'hidden';
    } else {
      var f = inter.getFeatures().item(0);
      $('#desc').load("https://places.openmap.lt/info.php?id=" + f.getProperties().oid + "&map=y");
      //$('#desc').show("slide");
      des.style.visibility = 'visible';
    }
  });

  $('#poi_button').on('click', '', function() {
    hideDescription();
    $('#hint').hide();
    $('#buttons').hide();
    $('#poi').show("slide", { direction: "right" }, 400);
    $('#back').show();
  });
  $('#poih').on('click', '', function() {
    $('#buttons').show();
    $('#poi').hide("slide", { direction: "right" }, 400);
    $('#back').hide();
  });
  $('#lay_button').on('click', '', function() {
    hideDescription();
    $('#buttons').hide();
    $('#lay').show("slide", { direction: "right"}, 400);
    $('#back').show();
  });
  $('#layh').on('click', '', function() {
    $('#buttons').show();
    $('#lay').hide("slide", { direction: "right" }, 400);
    $('#back').hide();
  });
  $('#back').on('click', '', function() {
    hideLayers();
    hidePois();
    $('#back').hide();
  });
  $('#loc_button').on('click', '', function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition, showError, {timeout: 10000});
    } else {
        alert("Geolocation is not supported by this browser.");
    }
  });
  $('#sea_button').on('click', '', function() {
    $('#search').show();
  });
  $("#hint").click(function(){
    $("#hint").remove();
  });

  if (pois != '') {
    initPoi();
  }

  showMainContent();
} catch(e) {
  alert('Error in init: ' + e);
} // try
}

function toMap() {
  switchToLayer('T');
} // toMap
function toOrto() {
  switchToLayer('M');
} // toOrto

function switchToLayer(l) {
  switch (layer) {
    case 'T':
      lTour.setVisible(false);
      break;
    case 'M':
      lTransp.setVisible(false);
      lOrto.setVisible(false);
      break;
  }
  layer = l;
  switch (layer) {
    case 'T':
      lTour.setVisible(true);
      break;
    case 'M':
      lTransp.setVisible(true);
      lOrto.setVisible(true);
      break;
  }
  hideLayers();
  $('#back').hide();
  mapChanged();
}

function initPoi() {
  lSource.clear();
  poiLoader(map.getView().calculateExtent(map.getSize()) // extent
           ,map.getView().getResolution() // resolution
           ,map.getView().getProjection() // projection
  );
  visible = true;
  lPOI.setVisible(true);
} // initPoi

function refreshPoi() {
  if (pois.length == 0) {
    visible = false;
    lPOI.setVisible(false);
  } else {
    lSource.clear();
    poiLoader(map.getView().calculateExtent(map.getSize()) // extent
             ,map.getView().getResolution() // resolution
             ,map.getView().getProjection() // projection
    );
    visible = true;
    lPOI.setVisible(true);
  }
  mapChanged();
} // refreshPoi

function switchPoi(a) {
  var p;
  p = document.getElementById(a);
  if (pois == pois.replace(a, '')) {
    pois = pois.concat(a);
    $("#" + a).toggleClass('selected');
  } else {
    pois = pois.replace(a, '');
    $("#" + a).toggleClass('selected');
  }
  refreshPoi();
} // switchPoi

function getType(t) {
  switch (t) {
    case 'HIS': return 'a';
    case 'HIL': return 'b';
    case 'HER': return 'c';
    case 'MON': return 'd';
    case 'TUM': return 'e';
    case 'MAN': return 'f';
    case 'TOW': return 'g';
    case 'ATT': return 'h';
    case 'VIE': return 'W';
    case 'HOT': return 's';
    case 'INF': return 't';
    case 'PIF': return 'j';
    case 'PIC': return 'k';
    case 'CAM': return 'l';
    case 'GUE': return 'm';
    case 'FUE': return 'n';
    case 'SPE': return 'w';
    case 'WAS': return 'T';
    case 'CAR': return 'G';
    case 'CAF': return 'o';
    case 'FAS': return 'p';
    case 'RES': return 'q';
    case 'PUB': return 'r';
    case 'MUS': return 'i';
    case 'THE': return 'u';
    case 'CIN': return 'v';
    case 'ART': return 'x';
    case 'LIB': return 'y';
    case 'HOS': return 'z';
    case 'CLI': return 'A';
    case 'DEN': return 'B';
    case 'DOC': return 'C';
    case 'PHA': return 'D';
    case 'SUP': return 'E';
    case 'CON': return 'F';
    case 'KIO': return 'H';
    case 'DIY': return 'I';
    case 'OSH': return 'R';
    case 'CHU': return 'J';
    case 'LUT': return 'K';
    case 'ORT': return 'L';
    case 'ORE': return 'M';
    case 'MNS': return 'X';
    case 'GOV': return 'N';
    case 'COU': return 'O';
    case 'NOT': return 'P';
    case 'COM': return 'Q';
    case 'POS': return 'S';
    case 'BAN': return 'U';
    case 'INS': return 'Y';
    case 'ATM': return 'V';
    case 'HIK': return '1';
    case 'POL': return '2';
    case 'STO': return '3';
    case 'TRE': return '3';
    case 'SPR': return '3';
    default: return '-';
  }
} // getTpe

function goTo(x, y, t) {
  $("#search").hide();
  var lonlat = ol.proj.fromLonLat([x, y]);
  view.animate({center: lonlat, zoom: 17});
  var type = getType(t);
  if (pois == pois.replace(type, '')) {
    pois = pois.concat(type);
    $("#" + type).toggleClass('selected');
    refreshPoi();
  }
} // goTo

function hideSearch() {
  $("#search").hide();
}

function searchChange() {
  try {
    input = document.getElementById('filter');
    filter = input.value.toUpperCase();
    var center = ol.proj.transform(view.getCenter(), 'EPSG:102100', 'EPSG:4326');
    $.getJSON('search.php?f=' + filter + '&x=' + center[0] + '&y=' + center[1], function(data) {
      var items = [];
      $.each(data.features, function (key, val) {
        items.push('<li onClick="goTo(' + val.properties.lat + ',' + val.properties.lon + ', \'' + val.properties.type + '\');">' +
          '<img src="' + typeIcon(val.properties.type) + '"> <b>' +
          val.properties.name + '</b> (' + val.properties.distance + 'km) <i>' +
          val.properties.city + ' ' + val.properties.street + ' ' + val.properties.house + '</i></li>');
      });
      $("#result").html(items);
    });
  } catch(e) {
    alert('Nepavyko gauti rezultatų: ' + e);
  }
} // searchChange
