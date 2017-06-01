<?php 
if(!function_exists('env')) {
	function env($key, $defaultValue = null) {
		return getenv($key) ? getenv($key) : $defaultValue;
	}
}

if(!function_exists('_t')) {
    function _t($msg) {
        return $msg;
    }
}

function getConfigToken()
{
	$now = new \DateTime();
	$future = new \DateTime("now +24 hours");
	
	if(PHP_VERSION_ID < 70010) {
		$tokenId    = base64_encode(mcrypt_create_iv(32));
	}else {
		$tokenId    = base64_encode(random_bytes(32));
	}
	$issuedAt   = time();
	$notBefore  = $now->getTimeStamp() + 1;             //Adding 1 seconds
	$serverName = $_SERVER['SERVER_NAME'];
	$data = [
			'iat'  => $now->getTimeStamp(),         // Issued at: time when the token was generated
			'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
			'iss'  => $serverName,       // Issuer
			'nbf'  => $notBefore,        // Not before
			'exp'  => $future->getTimeStamp()            // Expire
	];
	
	return $data;
}

function generateRandomString($length = 10) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}

function RSAEncryptData($client, $source)
{
    $pubKey = "./data/ssh/".$client."/public_key.pub";
    if(!file_exists($pubKey)){
        $pubKey = "./data/ssh/public_key.pub";
    }
    $pub_key=openssl_pkey_get_public(file_get_contents($pubKey));
	openssl_public_encrypt($source,$crypttext,$pub_key);//, OPENSSL_PKCS1_OAEP_PADDING);
	return(base64_encode($crypttext));
}

function RSADecryptData($client, $source, $passphrase = null)
{
    $privKey = "./data/ssh/".$client."/private_key.pem";
    if(!file_exists($privKey)) {
        $privKey = "./data/ssh/private_key.pem";
    }
	$priv_key=file_get_contents($privKey);
	$res = openssl_pkey_get_private($priv_key);
	if($passphrase) {
		$res = openssl_pkey_get_private($priv_key, $passphrase);
	}
	$decoded_source = base64_decode($source);
	openssl_private_decrypt($decoded_source,$newsource,$res, 1);
	return $newsource;
}

function aes256_encrypt($decryptKey, $stringEncryption) {
	if(PHP_VERSION_ID < 70010) {
		$size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
		$pad = $size - (strlen($stringEncryption) % $size);
		$input = $stringEncryption . str_repeat(chr($pad), $size);
		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
		$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		mcrypt_generic_init($td, $decryptKey, $iv);
		$data = mcrypt_generic($td, $input);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
	}else {
		$padding = 16 - (strlen($stringEncryption) % 16);
		$stringEncryption .= str_repeat(chr($padding), $padding);
		// 			$iv = openssl_random_pseudo_bytes(16);
		$data = openssl_encrypt($stringEncryption, 'AES-128-ECB', $decryptKey, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING);
	}
	
	return base64_encode($data);
}

function aes256_decrypt($decryptKey, $encryptedData) {
	if(PHP_VERSION_ID < 70010) {
		$decrypted= mcrypt_decrypt(
				MCRYPT_RIJNDAEL_128,
				$decryptKey,
				base64_decode($encryptedData),
				MCRYPT_MODE_ECB
		);
	}else {
		$decrypted = openssl_decrypt(base64_decode($encryptedData), 'AES-128-ECB', $decryptKey, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING);
	}
	$dec_s = strlen($decrypted);
	$padding = ord($decrypted[$dec_s-1]);
	$decrypted = substr($decrypted, 0, -$padding);
	return $decrypted;
}