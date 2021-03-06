<?php
  setlocale(LC_ALL, 'C');
  mb_internal_encoding("UTF-8");

  $csvfile = fopen("booklist.csv", "r");
  if($csvfile === FALSE) {
    echo 'Failed to open booklist.csv';
    die();
  }

  $jsonfile = fopen("booklist.geojson", "w");
  if($jsonfile === FALSE) {
    echo 'Failed to open booklist.geojson';
    die();
  }

  $geojson = array(
     'type'      => 'FeatureCollection',
     'crs'       => array('type' => 'name',
                          'properties' => array('name' => 'urn:ogc:def:crs:OGC:1.3:CRS84') ),
     'features'  => array()
  );

  $header = fgetcsv($csvfile);
  $lat = array_search('lat', $header);
  $lon = array_search('lon', $header);
  if($lat === FALSE || $lon === FALSE) {
    echo 'Missing lat/lon';
    die();
  }

  while (($data = fgetcsv($csvfile))) {
    $properties = array();
    for($i = 0 ; $i < count($data) ; $i++ ) {
      if( $i != $lat && $i != $lon) {
        $properties += array( $header[$i] => $data[$i] );
      }
    }

    $feature = array(
      'type' => 'Feature', 
      'geometry' => array(
        'type' => 'Point',
        'coordinates' => [ $data[$lon], $data[$lat] ]
      ),
      'properties' => $properties
    );
    array_push($geojson['features'], $feature);
  }

  ini_set('serialize_precision', '-1');
  fwrite($jsonfile, json_encode($geojson, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
  fclose($jsonfile);
  fclose($csvfile);
?>