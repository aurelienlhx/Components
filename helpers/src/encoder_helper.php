<?php

/**
 * Encode an int according to an alphabetic key
 */
function encode_int($id, $key = 'abcdefghij', $min = 4){

	$id = intval($id,10);
	$tobase = strlen($key);

	if($tobase != 10)
		$id = base_convert($id,10,$tobase);

	if( count(array_unique(str_split($key))) != strlen($key) )
		trigger_error(sprintf('All character of key must be uniques',$tobase),E_USER_ERROR);

	while(strlen($id)<$min)
		$id = '0'.$id;

	$numbers =str_split($id);

	$id_encoded = '';
	foreach($numbers as $index => $number){
		$id_encoded .= $key[(int) $number];	
		$key = substr($key,$number).substr($key,0,$number);
	}
	return $id_encoded;
}

/**
 *  Decode an encoded int according to an alphabetic key use for encoding
 */
function decode_int($encoded_id,$key = 'abcdefghij', $min = 4){

	if(strlen($encoded_id) < $min)
		return null;

	$frombase = strlen($key);

	$letters = str_split($encoded_id);

	$id_decoded = '';
	foreach($letters as $index => $letter){
		$number = strpos($key,$letter);
		
		if(false === $number) 
			return null;
		
		$id_decoded .= 	$number;
		$key = substr($key,$number).substr($key,0,$number);
	}

	$id_decoded = intval($id_decoded, $frombase);
	
	return $id_decoded;

}