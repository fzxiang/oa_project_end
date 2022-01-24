<?php

namespace App\Libs;

class TokenSsl
{
    const TOKEN_KEY = 'kkkasrf9jhdc2~456';

    // 加密
    public static function encryptOpenssl($input, $key)
    {
        $newInput = is_array($input) ? json_encode($input, true) : $input;

        $data = openssl_encrypt($newInput, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        $data = base64_encode($data);
        return $data;
    }

    // 解密
    public static function decryptOpenssl($str, $key)
    {
        $decrypted = self::decryptOnlyOpenssl($str, $key);
        if ($decrypted === false) {
            return false;
        }
        $newDecrypted = @json_decode($decrypted, true);
        if (is_array($newDecrypted)) {
            return $newDecrypted;
        } else {
            return $decrypted;
        }
    }

    // 数据解密
    public static function decryptOnlyOpenssl($str, $key)
    {
        $decodeStr = base64_decode($str);
        if ($decodeStr === false) {
            return false;
        }
        return openssl_decrypt($decodeStr, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
    }

    // 生成hash值
    public static function generateHash($str)
    {
        return hash('crc32', $str, false);
    }

}
