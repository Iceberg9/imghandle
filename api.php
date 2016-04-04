<?php

function file_get_contents_curl($url) {
  if (strpos($url,'http://') !== FALSE) {
    $fc = curl_init();
    curl_setopt($fc, CURLOPT_URL,$url);
    curl_setopt($fc, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($fc, CURLOPT_HEADER,0);
    curl_setopt($fc, CURLOPT_VERBOSE,0);
    curl_setopt($fc, CURLOPT_SSL_VERIFYPEER,FALSE);
    curl_setopt($fc, CURLOPT_TIMEOUT,30);
    $res = curl_exec($fc);
    curl_close($fc);
  }
  else $res = file_get_contents($url);
  return $res;
}

$key = 'm6bt0x9KAGdEEUiH_M8HwzM6nuRZFxTK';
//$file = 'https://cdn.shopify.com/s/files/1/1141/0348/products/andean_dream_chocochip_galles_01_1024x1024.png?v=1458065262';
//$fileOutput = 'output.png';
$file = $_GET['src'];
$r2d2 = explode('products/', $file);
$c3po = explode('?v=', $r2d2[1]);
$fileOutput = $c3po[0];
$verbose = true;

// Sanity checks
if ( empty( $file ) ) die( 'Lo siento, el campo imagen esta vacio o llenado incorrectamente.' );
//if ( ! is_file( $file ) ) die( 'Lo siento, no puedo encontrar el archivo:' . " $file\n" );

// Le damos el mismo nombre al output. Esto remplazara la imagen OJO.
$input = $output = $file;

// tinypng.com example API code: 
$request = curl_init();
curl_setopt_array($request, array(
	CURLOPT_URL => "https://api.tinypng.com/shrink",
	CURLOPT_USERPWD => "api:" . $key,
	//CURLOPT_POSTFIELDS => file_get_contents($input), --> Solo si jalamos imagenes DENTRO del servior
	CURLOPT_POSTFIELDS => file_get_contents_curl($input), // Solo si jalamos imagenes FUERA del servidor
	CURLOPT_BINARYTRANSFER => true,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_HEADER => true,
	/* Uncomment below if you have trouble validating our SSL certificate.
		Download cacert.pem from: http://curl.haxx.se/ca/cacert.pem */
	//CURLOPT_CAINFO => __DIR__ . "/cacert.pem",
	CURLOPT_SSL_VERIFYPEER => true
));

$response = curl_exec($request);

// for verbose output
$results = json_decode( preg_replace('/HTTP(.*)json/s',"",$response) );
$input_size = $results->input->size;
$output_size = $results->output->size;
$percent = 100 - $results->output->ratio * 100 . "%";

if (curl_getinfo($request, CURLINFO_HTTP_CODE) === 201) {
	/* Compression was successful, retrieve output from Location header. */
	$headers = substr($response, 0, curl_getinfo($request, CURLINFO_HEADER_SIZE));
	foreach (explode("\r\n", $headers) as $header) {
		if (substr($header, 0, 10) === "Location: ") {
			$request = curl_init();
			curl_setopt_array($request, array(
				CURLOPT_URL => substr($header, 10),
				CURLOPT_RETURNTRANSFER => true,
				/* Uncomment below if you have trouble validating our SSL certificate. */
				//CURLOPT_CAINFO => __DIR__ . "/cacert.pem",
				CURLOPT_SSL_VERIFYPEER => true
			));
			//file_put_contents($output, curl_exec($request)); /*Para guardar con el mismo nombre del input (sobrescribir)*/
			file_put_contents($fileOutput, curl_exec($request)); /* Para guardar con OTRO nombre.*/
		}
	}
	// Hacemos visible la respuesta
	if ( $verbose ) {
		//echo "$file: $input_size => $output_size - $percent\n"; // Para debugear que la tarea este completa
		echo 'http://iceberg9.com/tinyAPI/' . $fileOutput;
	}
} else {
	print(curl_error($request));
	/* Cachamos error general */
	print("Compresión Fallida :(");
}