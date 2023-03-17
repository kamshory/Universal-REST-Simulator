<?php
class HTPasswd
{
    const APRMD5_ALPHABET = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    const BASE64_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
    public static function hash($mdp, $salt = null) 
	{
        if (is_null($salt))
        {
			$salt = self::salt();
		}
        $salt = substr($salt, 0, 8);
        $max = strlen($mdp);
        $context = $mdp.'$apr1$'.$salt;
        $binary = pack('H32', md5($mdp.$salt.$mdp));
        for($i=$max; $i>0; $i-=16)
        {
			$context .= substr($binary, 0, min(16, $i));
		}
        for($i=$max; $i>0; $i>>=1)
		{
            $context .= ($i & 1) ? chr(0) : $mdp[0];
		}
        $binary = pack('H32', md5($context));
        for($i=0; $i<1000; $i++) 
		{
            $new = ($i & 1) ? $mdp : $binary;
            if($i % 3) $new .= $salt;
            if($i % 7) $new .= $mdp;
            $new .= ($i & 1) ? $binary : $mdp;
            $binary = pack('H32', md5($new));
        }
        $hash = '';
        for ($i = 0; $i < 5; $i++) 
		{
            $k = $i+6;
            $j = $i+12;
            if($j == 16) $j = 5;
            $hash = $binary[$i].$binary[$k].$binary[$j].$hash;
        }
        $hash = chr(0).chr(0).$binary[11].$hash;
        $hash = strtr(
            strrev(substr(base64_encode($hash), 2)),
            self::BASE64_ALPHABET,
            self::APRMD5_ALPHABET
        );
        return '$apr1$'.$salt.'$'.$hash;
    }
    // 8 character salts are the best. Don't encourage anything but the best.
    public static function salt() 
	{
        $alphabet = self::APRMD5_ALPHABET;
        $salt = '';
        for($i=0; $i<8; $i++) {
            $offset = hexdec(bin2hex(openssl_random_pseudo_bytes(1))) % 64;
            $salt .= $alphabet[$offset];
        }
        return $salt;
    }
    public static function check($plain, $hash) 
	{
        $parts = explode('$', $hash);
        return self::hash($plain, $parts[2]) === $hash;
    }
	public static function crypt_sha1($password)
	{
		return "{SHA}".base64_encode(hex2bin(sha1($password)));
	}

	public static function crypt_apr1_md5($plainpasswd)
	{
		$salt = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 8);
		$len = strlen($plainpasswd);
		$text = $plainpasswd.'$apr1$'.$salt;
		$bin = pack("H32", md5($plainpasswd.$salt.$plainpasswd));
		for($i = $len; $i > 0; $i -= 16) { $text .= substr($bin, 0, min(16, $i)); }
		for($i = $len; $i > 0; $i >>= 1) { $text .= ($i & 1) ? chr(0) : $plainpasswd[0]; }
		$bin = pack("H32", md5($text));
		for($i = 0; $i < 1000; $i++)
		{
			$new = ($i & 1) ? $plainpasswd : $bin;
			if ($i % 3) 
			{
				$new .= $salt;
			}
			if ($i % 7) 
			{
				$new .= $plainpasswd;
			}
			$new .= ($i & 1) ? $bin : $plainpasswd;
			$bin = pack("H32", md5($new));
		}
		$tmp = '';
		for ($i = 0; $i < 5; $i++)
		{
			$k = $i + 6;
			$j = $i + 12;
			if ($j == 16) $j = 5;
			$tmp = $bin[$i].$bin[$k].$bin[$j].$tmp;
		}
		$tmp = chr(0).chr(0).$bin[11].$tmp;
		$tmp = strtr(strrev(substr(base64_encode($tmp), 2)),
		"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
		"./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz");
		
		return "$"."apr1"."$".$salt."$".$tmp;
	}
	
	

	public static function auth($username, $password, $stored)
	{
		$buff = $stored;
		$buff = str_replace("\n", "\r\n", $buff);
		$buff = str_replace("\r\n\n", "\r\n", $buff);
		$buff = str_replace("\r", "\r\n", $buff);
		$buff = str_replace("\r\r\n", "\r\n", $buff);
		$buffs = explode("\r\n", $buff);
		foreach($buffs as $line)
		{
			$line = trim($line, "\r\n");
			$arr = explode(":", $line, 2);
			if($arr[0] == $username)
			{
				if(stripos($arr[1], '{SHA}') === 0)
				{
					if(self::crypt_sha1($password) == $arr[1])
					{
						return true;
					}
					else
					{
						return false;
					}
				}
				else if(stripos($arr[1], '$apr1$') === 0)
				{
					if(self::check($password, $arr[1]))
					{
						return true;
					}
					else
					{
						return false;
					}
				}
				else
				{
					return false;
				}
				break;
			}
		}
	}
}