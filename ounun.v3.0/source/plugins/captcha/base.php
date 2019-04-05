<?php
/** 命名空间 */

namespace plugins\captcha;

/**
 * 认证码类
 * @package module
 */
class base
{
    /** @var string 随机码 */
    public $code = '';

    /** @var null 图片句柄 */
    protected $_img = null;

    /**
     * 生成图片
     *
     * @param int $img_width
     * @param int $img_height
     * @param int $img_lenght
     */
    public function make($img_width = 75, $img_height = 24, $img_lenght = 4)
    {
        if (!function_exists("imagecreatetruecolor")) {
            $input = ['1335', '3114', '4922', '2320', '1268', '9011'];
            $this->code = $input[array_rand($input)];

            // setcookie($cookie,md5($input),time()+3600);
            header('Content-type: image/png');
            readfile(__DIR__ . "/res/no_gd/{$this->code}.png");
            exit();
        }
        $font = [
            __DIR__ . '/res/font/a.ttf',
            __DIR__ . '/res/font/b.ttf',
            __DIR__ . '/res/font/c.ttf',
        ];
        $this->code = '';
        for ($ti = 0; $ti < $img_lenght; $ti++) {
            $this->code .= dechex(mt_rand(0, 9));
        }
        $number_img = imagecreatetruecolor($img_width + 10, $img_height);
        $white = imagecolorallocate($number_img, 255, 255, 255);
        imagefilledrectangle($number_img, 0, 0, $img_width + 10 - 1, $img_height - 1, $white);
        for ($i = 1; $i <= 100; $i++) {
            \imagefttext($number_img, mt_rand(5, 10), mt_rand(0, 60), mt_rand(1, $img_width), mt_rand(1, $img_height), imagecolorallocate($number_img, mt_rand(180, 255), mt_rand(180, 255), mt_rand(180, 255)), $font[mt_rand(0, 2)], "*");
        }
        for ($i = 1; $i <= 100; $i++) {
            \imagefttext($number_img, mt_rand(5, 10), mt_rand(0, 180), mt_rand(1, $img_width), mt_rand(1, $img_height), imagecolorallocate($number_img, mt_rand(180, 255), mt_rand(180, 255), mt_rand(180, 255)), $font[mt_rand(0, 2)], "\\");
        }
        for ($i = 0; $i < strlen($this->code); $i++) {
            \imagefttext($number_img, mt_rand(14, 16), mt_rand(-30, 30), $i * $img_width / 4 + mt_rand(5, 15), mt_rand($img_height * 4 / 5, $img_height * 8 / 9), imageColorAllocate($number_img, mt_rand(0, 100), mt_rand(0, 150), mt_rand(0, 200)), $font[mt_rand(0, 2)], $this->code[$i]);
        }

        $this->_img = $number_img;
    }

    /** 输出图片 */
    public function output()
    {
        header("Content-type: image/png");
        imagepng($this->_img);
        imagedestroy($this->_img);
    }

}
