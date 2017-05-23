<?php
function hhb_encrypt($data, $password) {
	return hhb_encrypt1 ( $data, $password );
}
function hhb_decrypt($instr, $password) {
	return hhb_decrypt1 ( $data, $password );
}
function hhb_encrypt1(/*string*/ $data,/*string*/ $password)/*:string*/{
	$version = 1;
	// format: hhb_crypted:$version:strlen($encrypted).':'.base64_encode($iv.$encrypted).':';
	$strong = false;
	$iv = openssl_random_pseudo_bytes ( 16, $strong );
	if (! $strong) {
		// .,,
		trigger_error ( 'openssl_random_pseudo_bytes failed to generate a strong iv!' );
	}
	$encrypted = openssl_encrypt ( $data, 'AES-192-CFB', $password, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv );
	$ret = 'hhb_crypted:' . $version . ':' . strlen ( $encrypted ) . ':';
	$ret .= base64_encode ( $iv . $encrypted );
	$ret .= ';';
	return $ret;
}
function hhb_decrypt1(/*string*/ $instr,/*string*/ $password, bool &$success = NULL)/*:string*/{
	$success = false;
	$instr = trim ( $instr );
	if (false === ($to = strpos ( $instr, 'hhb_crypted:' ))) {
		return '!!decryption error: could not find hhb_crypt: header !!';
	}
	$instr = substr ( $instr, $to + strlen ( 'hhb_crypted:' ) );
	$to = strpos ( $instr, ':' );
	if (false === $to) {
		return '!!decryption error: could not find end of version number!!';
	}
	$versionstr = substr ( $instr, 0, $to );
	if ($versionstr !== '1') {
		return '!!decryption error: invalid version!!';
	}
	$instr = substr ( $instr, $to + 1 );
	$to = strpos ( $instr, ':' );
	if (false === $to) {
		return '!!decryption error: could not find length of encrypted data!!';
	}
	$lengthstr = substr ( $instr, 0, $to );
	$length = filter_var ( $lengthstr, FILTER_VALIDATE_INT );
	if (false === $length) {
		return '!!decryption error: length of encrypted data is not a valid int! (and non-8bit-aligned encrypted data is not supported!)!!';
	}
	unset ( $lengthstr );
	$instr = substr ( $instr, $to + 1 );
	$end = strpos ( $instr, ';' );
	if (false === $end) {
		return '!!decryption error: cannot find the ending semicolon (;) !!';
	}
	$b64 = substr ( $instr, 0, $end );
	unset ( $instr );
	$raw = base64_decode ( $b64 );
	unset ( $b64 );
	if (! is_string ( $raw )) {
		return '!!decryption error: not a valid base64 string !!';
	}
	$rawlen = strlen ( $raw );
	if ($rawlen < 16) {
		return '!!decryption error: the IV is 16 bytes, but only ' . $rawlen . ' bytes given!!';
	}
	$rawlen -= 16;
	if ($rawlen !== $length) {
		return '!!decryption error: header says the data is ' . $length . ' bytes, but only ' . $rawlen . ' bytes remain!!';
	}
	$iv = substr ( $raw, 0, 16 );
	$raw = substr ( $raw, 16 );
	$ret = openssl_decrypt ( $raw, 'AES-192-CFB', $password, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv );
	if (! is_string ( $ret )) {
		return '!!decryption error: openssl_decrypt failed for an unknown reason!!';
	}
	$success = true;
	return $ret;
}