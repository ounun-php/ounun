<?php
/** 命名空间 */
namespace plugins\captcha;

/** 本插件所在目录 */
define('Dir_Plugins_Captcha',           realpath(__DIR__) .'/');
/**
 * 认证码类
 * @package module
 */
class Captcha
{
	/**
	 * 输出图片
	 *
	 * @param string $cookie
	 * @param int $img_width
	 * @param int $img_height
	 * @param int $img_lenght
	 */
	public static function output($cookie = 'Captcha', $img_width = 75, $img_height = 24, $img_lenght = 4)
	{
		if(!function_exists("imagecreatetruecolor"))
		{
			$input = array('1335', '3114', '4922', '2320', '1268', '9011');
			$input = $input[array_rand($input)];
			setcookie($cookie,md5($input));
			//setcookie($cookie,md5($input),time()+3600);
			header('Content-type: image/png');
			readfile(Dir_Plugins_Captcha . "res/no_gd/{$input}.png");
			exit();
		}
		$font = array(
            Dir_Plugins_Captcha . 'res/font/a.ttf',
            Dir_Plugins_Captcha . 'res/font/b.ttf',
            Dir_Plugins_Captcha . 'res/font/c.ttf'
        );
		$input = null;
		for($ti = 0; $ti < $img_lenght; $ti++)
		{
			$input .= dechex(mt_rand(0, 9));
		}			
		$number_img = imagecreatetruecolor($img_width + 10, $img_height);
		$white 		= imagecolorallocate($number_img, 255, 255, 255);
		imagefilledrectangle($number_img, 0, 0, $img_width + 10 - 1, $img_height - 1, $white);
		for($i = 1; $i <= 100; $i++)
		{
			imagefttext($number_img, mt_rand(5, 10), mt_rand(0, 60), mt_rand(1, $img_width), mt_rand(1, $img_height), imagecolorallocate($number_img, mt_rand(180, 255), mt_rand(180, 255), mt_rand(180, 255)), $font[mt_rand(0, 2)], "*");
		}
		for($i = 1; $i <= 100; $i++)
		{
			imagefttext($number_img, mt_rand(5, 10), mt_rand(0, 180), mt_rand(1, $img_width), mt_rand(1, $img_height), imagecolorallocate($number_img, mt_rand(180, 255), mt_rand(180, 255), mt_rand(180, 255)), $font[mt_rand(0, 2)], "\\");
		}
		for($i = 0; $i < strlen($input); $i++)
		{
			imagefttext($number_img, mt_rand(14, 16), mt_rand(-30, 30), $i * $img_width / 4 + mt_rand(5, 15), mt_rand($img_height * 4 / 5, $img_height * 8 / 9), imageColorAllocate($number_img, mt_rand(0, 100), mt_rand(0, 150), mt_rand(0, 200)), $font[mt_rand(0, 2)], $input[$i]);
		}
		setcookie($cookie,md5($input));
		//setcookie($cookie,md5($input),time()+3600);
		header("Content-type: image/png");
		imagepng($number_img);
		imagedestroy($number_img);
	}
	/**
	 * 确认认证码
	 *
	 * @param string $Captcha
	 * @param string $cookie
	 * @return boolean
	 */
	public static function check($Captcha,$cookie = 'Captcha')
	{
		$rs = ($Captcha && $_COOKIE[$cookie] && md5($Captcha) == $_COOKIE[$cookie])?true:false;
		setcookie($cookie,'',-3600);
		return $rs;
	}
}
