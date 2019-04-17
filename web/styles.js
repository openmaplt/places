function typeIcon(type) {
  if (type == 'HIS') { return 'img/ruins_.png';
  } else if (type == 'HIL') { return 'img/hillfort_.png';
  } else if (type == 'HER') { return 'img/paveldas_.png';
  } else if (type == 'MON') { return 'img/memorial_.png';
  } else if (type == 'TUM') { return 'img/tumulus_.png';
  } else if (type == 'MAN') { return 'img/dvarai_.png';
  } else if (type == 'TOW') { return 'img/tower_.png';
  } else if (type == 'ATT') { return 'img/footprint_.png';
  } else if (type == 'VIE') { return 'img/viewpoint_.png';
  } else if (type == 'TRE') { return 'img/tree_.png';
  } else if (type == 'STO') { return 'img/stone_.png';
  } else if (type == 'SPR') { return 'img/spring_.png';
  } else if (type == 'HIK') { return 'img/hiking_.png';
  } else if (type == 'HOT') { return 'img/hotel_.png';
  } else if (type == 'INF') { return 'img/information_.png';
  } else if (type == 'PIF') { return 'img/fire_.png';
  } else if (type == 'PIC') { return 'img/picnic_.png';
  } else if (type == 'CAM') { return 'img/camping_.png';
  } else if (type == 'GUE') { return 'img/hostel_.png';
  } else if (type == 'FUE') { return 'img/fillingstation_.png';
  } else if (type == 'SPE') { return 'img/speed_.png';
  } else if (type == 'WAS') { return 'img/carwash_.png';
  } else if (type == 'CAR') { return 'img/repair_.png';
  } else if (type == 'CAF') { return 'img/coffee_.png';
  } else if (type == 'FAS') { return 'img/burger_.png';
  } else if (type == 'RES') { return 'img/restaurant_.png';
  } else if (type == 'PUB') { return 'img/bar_.png';
  } else if (type == 'MUS') { return 'img/museum_.png';
  } else if (type == 'THE') { return 'img/theater_.png';
  } else if (type == 'CIN') { return 'img/cinema_.png';
  } else if (type == 'ART') { return 'img/art-museum_.png';
  } else if (type == 'LIB') { return 'img/library_.png';
  } else if (type == 'HOS') { return 'img/hospital_.png';
  } else if (type == 'CLI') { return 'img/firstaid_.png';
  } else if (type == 'DEN') { return 'img/dentist_.png';
  } else if (type == 'DOC') { return 'img/medicine_.png';
  } else if (type == 'PHA') { return 'img/drugstore_.png';
  } else if (type == 'SUP') { return 'img/supermarket_.png';
  } else if (type == 'CON') { return 'img/convenience_.png';
  } else if (type == 'KIO') { return 'img/market_.png';
  } else if (type == 'DIY') { return 'img/workshop_.png';
  } else if (type == 'OSH') { return 'img/departmentstore_.png';
  } else if (type == 'CHU') { return 'img/cathedral_.png';
  } else if (type == 'LUT') { return 'img/lutheran_.png';
  } else if (type == 'ORT') { return 'img/orthodox_.png';
  } else if (type == 'ORE') { return 'img/prayer_.png';
  } else if (type == 'MNS') { return 'img/convent_.png';
  } else if (type == 'GOV') { return 'img/congress_.png';
  } else if (type == 'COU') { return 'img/court_.png';
  } else if (type == 'NOT') { return 'img/administration_.png';
  } else if (type == 'COM') { return 'img/office-building_.png';
  } else if (type == 'POL') { return 'img/police_.png';
  } else if (type == 'POS') { return 'img/postal_.png';
  } else if (type == 'BAN') { return 'img/bigcity_.png';
  } else if (type == 'INS') { return 'img/umbrella_.png';
  } else if (type == 'ATM') { return 'img/euro_.png';
  } else { return 'img/kita_.png';
  }
} // typeIcon

var styles = {
  'HIS': [new ol.style.Style({image: new ol.style.Icon({src: 'img/ruins.png'})})],
  'HIL': [new ol.style.Style({image: new ol.style.Icon({src: 'img/hillfort.png'})})],
  'HER': [new ol.style.Style({image: new ol.style.Icon({src: 'img/paveldas.png'})})],
  'MON': [new ol.style.Style({image: new ol.style.Icon({src: 'img/memorial.png'})})],
  'TUM': [new ol.style.Style({image: new ol.style.Icon({src: 'img/tumulus.png'})})],
  'MAN': [new ol.style.Style({image: new ol.style.Icon({src: 'img/dvarai.png'})})],
  'TOW': [new ol.style.Style({image: new ol.style.Icon({src: 'img/tower.png'})})],
  'ATT': [new ol.style.Style({image: new ol.style.Icon({src: 'img/footprint.png'})})],
  'VIE': [new ol.style.Style({image: new ol.style.Icon({src: 'img/viewpoint.png'})})],
  'TRE': [new ol.style.Style({image: new ol.style.Icon({src: 'img/tree.png'})})],
  'STO': [new ol.style.Style({image: new ol.style.Icon({src: 'img/stone.png'})})],
  'SPR': [new ol.style.Style({image: new ol.style.Icon({src: 'img/spring.png'})})],
  'HIK': [new ol.style.Style({image: new ol.style.Icon({src: 'img/hiking.png'})})],
  'HOT': [new ol.style.Style({image: new ol.style.Icon({src: 'img/hotel.png'})})],
  'INF': [new ol.style.Style({image: new ol.style.Icon({src: 'img/information.png'})})],
  'PIF': [new ol.style.Style({image: new ol.style.Icon({src: 'img/fire.png'})})],
  'PIC': [new ol.style.Style({image: new ol.style.Icon({src: 'img/picnic.png'})})],
  'CAM': [new ol.style.Style({image: new ol.style.Icon({src: 'img/camping.png'})})],
  'GUE': [new ol.style.Style({image: new ol.style.Icon({src: 'img/hostel.png'})})],
  'FUE': [new ol.style.Style({image: new ol.style.Icon({src: 'img/fillingstation.png'})})],
  'SPE': [new ol.style.Style({image: new ol.style.Icon({src: 'img/speed.png'})})],
  'WAS': [new ol.style.Style({image: new ol.style.Icon({src: 'img/carwash.png'})})],
  'CAR': [new ol.style.Style({image: new ol.style.Icon({src: 'img/repair.png'})})],
  'CAF': [new ol.style.Style({image: new ol.style.Icon({src: 'img/coffee.png'})})],
  'FAS': [new ol.style.Style({image: new ol.style.Icon({src: 'img/burger.png'})})],
  'RES': [new ol.style.Style({image: new ol.style.Icon({src: 'img/restaurant.png'})})],
  'PUB': [new ol.style.Style({image: new ol.style.Icon({src: 'img/bar.png'})})],
  'MUS': [new ol.style.Style({image: new ol.style.Icon({src: 'img/museum.png'})})],
  'THE': [new ol.style.Style({image: new ol.style.Icon({src: 'img/theater.png'})})],
  'CIN': [new ol.style.Style({image: new ol.style.Icon({src: 'img/cinema.png'})})],
  'ART': [new ol.style.Style({image: new ol.style.Icon({src: 'img/art-museum.png'})})],
  'LIB': [new ol.style.Style({image: new ol.style.Icon({src: 'img/library.png'})})],
  'HOS': [new ol.style.Style({image: new ol.style.Icon({src: 'img/hospital.png'})})],
  'CLI': [new ol.style.Style({image: new ol.style.Icon({src: 'img/firstaid.png'})})],
  'DEN': [new ol.style.Style({image: new ol.style.Icon({src: 'img/dentist.png'})})],
  'DOC': [new ol.style.Style({image: new ol.style.Icon({src: 'img/medicine.png'})})],
  'PHA': [new ol.style.Style({image: new ol.style.Icon({src: 'img/drugstore.png'})})],
  'SUP': [new ol.style.Style({image: new ol.style.Icon({src: 'img/supermarket.png'})})],
  'CON': [new ol.style.Style({image: new ol.style.Icon({src: 'img/convenience.png'})})],
  'KIO': [new ol.style.Style({image: new ol.style.Icon({src: 'img/market.png'})})],
  'DIY': [new ol.style.Style({image: new ol.style.Icon({src: 'img/workshop.png'})})],
  'OSH': [new ol.style.Style({image: new ol.style.Icon({src: 'img/departmentstore.png'})})],
  'CHU': [new ol.style.Style({image: new ol.style.Icon({src: 'img/cathedral.png'})})],
  'LUT': [new ol.style.Style({image: new ol.style.Icon({src: 'img/lutheran.png'})})],
  'ORT': [new ol.style.Style({image: new ol.style.Icon({src: 'img/orthodox.png'})})],
  'ORE': [new ol.style.Style({image: new ol.style.Icon({src: 'img/prayer.png'})})],
  'MNS': [new ol.style.Style({image: new ol.style.Icon({src: 'img/convent.png'})})],
  'GOV': [new ol.style.Style({image: new ol.style.Icon({src: 'img/congress.png'})})],
  'COU': [new ol.style.Style({image: new ol.style.Icon({src: 'img/court.png'})})],
  'NOT': [new ol.style.Style({image: new ol.style.Icon({src: 'img/administration.png'})})],
  'COM': [new ol.style.Style({image: new ol.style.Icon({src: 'img/office-building.png'})})],
  'POL': [new ol.style.Style({image: new ol.style.Icon({src: 'img/police.png'})})],
  'POS': [new ol.style.Style({image: new ol.style.Icon({src: 'img/postal.png'})})],
  'BAN': [new ol.style.Style({image: new ol.style.Icon({src: 'img/bigcity.png'})})],
  'INS': [new ol.style.Style({image: new ol.style.Icon({src: 'img/umbrella.png'})})],
  'ATM': [new ol.style.Style({image: new ol.style.Icon({src: 'img/euro.png'})})]
};

var styleFunction = function(feature, resolution) {
  var type = feature.getProperties().tp;
  if (type != 'ART' &&
      type != 'ATM' &&
      type != 'ATT' &&
      type != 'BAN' &&
      type != 'CAF' &&
      type != 'CAM' &&
      type != 'CAR' &&
      type != 'CHU' &&
      type != 'CIN' &&
      type != 'CLI' &&
      type != 'COM' &&
      type != 'CON' &&
      type != 'COU' &&
      type != 'DEN' &&
      type != 'DIY' &&
      type != 'DOC' &&
      type != 'FAS' &&
      type != 'FUE' &&
      type != 'GOV' &&
      type != 'GUE' &&
      type != 'HER' &&
      type != 'HIK' &&
      type != 'HIL' &&
      type != 'HIS' &&
      type != 'HOS' &&
      type != 'HOT' &&
      type != 'INF' &&
      type != 'INS' &&
      type != 'KIO' &&
      type != 'LIB' &&
      type != 'LUT' &&
      type != 'MAN' &&
      type != 'MNS' &&
      type != 'MON' &&
      type != 'MUS' &&
      type != 'NOT' &&
      type != 'ORE' &&
      type != 'ORT' &&
      type != 'OSH' &&
      type != 'PHA' &&
      type != 'PIC' &&
      type != 'PIF' &&
      type != 'POL' &&
      type != 'POS' &&
      type != 'PUB' &&
      type != 'RES' &&
      type != 'SPE' &&
      type != 'SPR' &&
      type != 'STO' &&
      type != 'SUP' &&
      type != 'THE' &&
      type != 'TOW' &&
      type != 'TRE' &&
      type != 'TUM' &&
      type != 'VIE' &&
      type != 'WAS'
     ) {
    alert ("unknown type " + type);
  }
  return styles[feature.getProperties().tp];
};
