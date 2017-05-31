<?php
use Zend\Crypt\PublicKey\Rsa;
use Zend\Crypt\BlockCipher;
use Zend\Crypt\PublicKey\RsaOptions;

class Util
{
    public static function encryptRsa(
        $clientId,
        $stringEncryption,
        $pass_phrase = null,
        $padding = 1,
        $binary_output = false
    ) {

        try {
            $rsa = Rsa::factory([
                'public_key'    => './data/ssh/'.strtolower($clientId) . '/s_public_key.pub',
                //'private_key'   => './data/ssh/private_key.pem',
                'pass_phrase'   => $pass_phrase,
                'binary_output' => $binary_output,
                'opensslPadding' => $padding
            ]);
            return $rsa->encrypt($stringEncryption, $rsa->getOptions()->getPublicKey());
        } catch (\Exception $e) {
        }
        return false;
    }

    public static function decryptRsa(
        $clientId,
        $stringEncryption,
        $pass_phrase = null,
        $padding = 1,
        $binary_output = false
    ) {
        $pub = './data/ssh/'.strtolower($clientId).'/public_key.pub';
        $priv = './data/ssh/'.strtolower($clientId).'/private_key.pem';
        if(!file_exists($pub)) {
            $pub = './data/ssh/public_key.pub';
        }
        if(!file_exists($priv)) {
            $priv = './data/ssh/private_key.pem';
        }
        var_dump($pub);die;
        try {
            $rsa = Rsa::factory([
                'public_key'    => $pub,
                'private_key'   => $priv,
                'pass_phrase'   => $pass_phrase,
                'binary_output' => $binary_output,
                'opensslPadding' => $padding
            ]);

            return $rsa->decrypt($stringEncryption, $rsa->getOptions()->getPrivateKey(), Rsa::MODE_BASE64);
        } catch (\Exception $e) {
            //var_dump($e->getMessage());die;
        }
        return false;
    }

    public static function encryptAes(
        $decryptKey,
        $stringEncryption,
        $fname = 'openssl',
        $algo = 'aes',
        $mode = 'cbc',
        $hash = 'sha256'
    ) {

        return aes256_encrypt($decryptKey, $stringEncryption);

        try {
            $blockCipher = BlockCipher::factory(
                $fname,
                [
                        'algo' => $algo,
                        'mode' => $mode,
                        'hash' => $hash
                    ]
            );
            $blockCipher->setKey($decryptKey);
            return $blockCipher->encrypt($stringEncryption);
        } catch (\Exception $e) {
        }
        return false;
    }

    public static function decryptAes(
        $decryptKey,
        $encryptedData,
        $fname = 'openssl',
        $algo = 'aes',
        $mode = 'cbc',
        $hash = 'sha256'
    ) {

        return aes256_decrypt($decryptKey, $encryptedData);

        try {
            $blockCipher = BlockCipher::factory(
                $fname,
                [
                    'algo' => $algo,
                    'mode' => $mode,
                    'hash' => $hash
                ]
            );
            $blockCipher->setKey($decryptKey);
            return $blockCipher->decrypt($encryptedData);
        } catch (\Exception $e) {
        }
        return false;
    }

    public static function generateRsa($client, $pass_phrase)
    {
        $rsaOptions = new RsaOptions([
            'pass_phrase' => $pass_phrase
        ]);

        $rsaOptions->generateKeys([
            'private_key_bits' => 2048,
        ]);

        file_put_contents('./data/'.$client.'/private_key.pem', $rsaOptions->getPrivateKey());
        file_put_contents('./data/'.$client.'/public_key.pub', $rsaOptions->getPublicKey());

        var_dump($rsaOptions->getPublicKey());
        die;
    }
}
