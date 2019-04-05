<?php

namespace ounun\image;

/**
 * file: image.class.php 类名为Image
 *      图像处理类，可以完成对各种类型的图像进行缩放、加图片水印和剪裁的操作。
 * Class Image
 * @package ounun
 */
class thumb
{
    /* 图片保存的路径 */
//    private $path;
    /**
     *  * 实例图像对象时传递图像的一个路径，默认值是当前目录
     *  * @param  string $path  可以指定处理图片的路径
     *  */
//    public function __construct($path="./")
//    {
//        $this->path = rtrim($path,"/")."/";
//    }

    /**
     *  * 对指定的图像进行缩放
     *  * @param  string $name  是需要处理的图片名称
     *  * @param  int $width    缩放后的宽度
     *  * @param  int $height   缩放后的高度
     *  * @param  string $qz    是新图片的前缀
     *  * @return mixed      是缩放后的图片名称,失败返回false;
     *  */
    public function thumb($name, $width, $height, $out_file_name, $force = false)
    {
        /* 获取图片宽度、高度、及类型信息 */
        $img_info = $this->_info($name);
        /* 获取背景图片的资源 */
        $src_img = $this->_img($name, $img_info);
        /* 获取新图片尺寸 */
        $size = $this->_new_size($width, $height, $img_info, $force);
        /* 获取新的图片资源 */
        $newImg = $this->_kid_of_image($src_img, $size, $img_info);
        /* 通过本类的私有方法，保存缩略图并返回新缩略图的名称，以"th_"为前缀 */
        return $this->_create_image($newImg, $out_file_name, $img_info);
    }

    /**
     *  * 为图片添加水印
     *  * @param  string $groundName 背景图片，即需要加水印的图片，暂只支持GIF,JPG,PNG格式
     *  * @param  string $waterName 图片水印，即作为水印的图片，暂只支持GIF,JPG,PNG格式
     *  * @param  int $waterPos    水印位置，有10种状态，0为随机位置；
     *  *                1为顶端居左，2为顶端居中，3为顶端居右；
     *  *                4为中部居左，5为中部居中，6为中部居右；
     *  *                7为底端居左，8为底端居中，9为底端居右；
     *  * @param  string $qz     加水印后的图片的文件名在原文件名前面加上这个前缀
     *  * @return  mixed        是生成水印后的图片名称,失败返回false
     *  */
    public function water_mark($ground_name, $water_name, $water_pos = 0, $out_file_name = '')
    {
        /*获取水印图片是当前路径，还是指定了路径*/
//        $dir = dirname($waterName);
//        if($dir == ".")
//        {
//            $wpath = $curpath;
//        }else
//        {
//            $wpath = $dir."/";
//            $waterName = basename($waterName);
//        }

        /*水印图片和背景图片必须都要存在*/
        if (file_exists($ground_name) && file_exists($water_name)) {
            $ground_info = $this->_info($ground_name);      //获取背景信息
            $water_info = $this->_info($water_name);  //获取水印图片信息
            /*如果背景比水印图片还小，就会被水印全部盖住*/
            if (!$pos = $this->_position($ground_info, $water_info, $water_pos)) {
                echo '水印不应该比背景图片小！';
                return false;
            }
            $groundImg = $this->_img($ground_name, $ground_info); //获取背景图像资源
            $waterImg = $this->_img($water_name, $water_info); //获取水印图片资源
            /* 调用私有方法将水印图像按指定位置复制到背景图片中 */
            $groundImg = $this->_copy_image($groundImg, $waterImg, $pos, $water_info);
            /* 通过本类的私有方法，保存加水图片并返回新图片的名称，默认以"wa_"为前缀 */
            return $this->_create_image($groundImg, $out_file_name, $ground_info);
        } else {
            echo '图片或水印图片不存在！';
            return false;
        }
    }

    /**
     *  * 在一个大的背景图片中剪裁出指定区域的图片
     *  * @param  string $name  需要剪切的背景图片
     *  * @param  int $x     剪切图片左边开始的位置
     *  * @param  int $y     剪切图片顶部开始的位置
     *  * @param  int $width   图片剪裁的宽度
     *  * @param  int $height   图片剪裁的高度
     *  * @param  string $qz   新图片的名称前缀
     *  * @return  mixed      裁剪后的图片名称,失败返回false;
     *  */
    public function cut($name, $x, $y, $width, $height, $outFileName)
    {
        $img_info = $this->_info($name); //获取图片信息
        /* 裁剪的位置不能超出背景图片范围 */
        if ((($x + $width) > $img_info['width']) || (($y + $height) > $img_info['height'])) {
            echo "裁剪的位置超出了背景图片范围!";
            return false;
        }
        $back = $this->_img($name, $img_info); //获取图片资源
        /* 创建一个可以保存裁剪后图片的资源 */
        $cutimg = imagecreatetruecolor($width, $height);
        /* 使用imagecopyresampled()函数对图片进行裁剪 */
        imagecopyresampled($cutimg, $back, 0, 0, $x, $y, $width, $height, $width, $height);
        imagedestroy($back);
        /* 通过本类的私有方法，保存剪切图并返回新图片的名称，默认以"cu_"为前缀 */
        return $this->_create_image($cutimg, $outFileName, $img_info);
    }

    /**
     * 内部使用的私有方法，用来确定水印图片的位置
     * @param $ground_info
     * @param $water_info
     * @param $water_pos
     * @return array|bool
     */
    private function _position($ground_info, $water_info, $water_pos)
    {
        /* 需要加水印的图片的长度或宽度比水印还小，无法生成水印 */
        if (($ground_info["width"] < $water_info["width"]) || ($ground_info["height"] < $water_info["height"])) {
            return false;
        }
        switch ($water_pos) {
            case 1: //1为顶端居左
                $posX = 0;
                $posY = 0;
                break;
            case 2: //2为顶端居中
                $posX = ($ground_info["width"] - $water_info["width"]) / 2;
                $posY = 0;
                break;
            case 3: //3为顶端居右
                $posX = $ground_info["width"] - $water_info["width"];
                $posY = 0;
                break;
            case 4: //4为中部居左
                $posX = 0;
                $posY = ($ground_info["height"] - $water_info["height"]) / 2;
                break;
            case 5: //5为中部居中
                $posX = ($ground_info["width"] - $water_info["width"]) / 2;
                $posY = ($ground_info["height"] - $water_info["height"]) / 2;
                break;
            case 6: //6为中部居右
                $posX = $ground_info["width"] - $water_info["width"];
                $posY = ($ground_info["height"] - $water_info["height"]) / 2;
                break;
            case 7: //7为底端居左
                $posX = 0;
                $posY = $ground_info["height"] - $water_info["height"];
                break;
            case 8: //8为底端居中
                $posX = ($ground_info["width"] - $water_info["width"]) / 2;
                $posY = $ground_info["height"] - $water_info["height"];
                break;
            case 9: //9为底端居右
                $posX = $ground_info["width"] - $water_info["width"];
                $posY = $ground_info["height"] - $water_info["height"];
                break;
            case 0:
            default: //随机
                $posX = rand(0, ($ground_info["width"] - $water_info["width"]));
                $posY = rand(0, ($ground_info["height"] - $water_info["height"]));
                break;
        }
        return array("posX" => $posX, "posY" => $posY);
    }

    /**
     * 内部使用的私有方法，用于获取图片的属性信息（宽度、高度和类型）
     * @param $name
     * @return mixed
     */
    private function _info($name)
    {
        // $spath = $path=="." ? rtrim($this->path,"/")."/" : $path.'/';
        $data = getimagesize($name);
        $img_info = [];
        $img_info["width"] = $data[0];
        $img_info["height"] = $data[1];
        $img_info["type"] = $data[2];
        return $img_info;
    }

    /**
     * 内部使用的私有方法， 用于创建支持各种图片格式（jpg,gif,png三种）资源
     * @param $name
     * @param $img_info
     * @return bool|resource
     */
    private function _img($name, $img_info)
    {
        // $spath = $path=="." ? rtrim($this->path,"/")."/" : $path.'/';
        // $srcPic = $spath.$name;
        switch ($img_info["type"]) {
            case 1: //gif
                $img = imagecreatefromgif($name);
                break;
            case 2: //jpg
                $img = imagecreatefromjpeg($name);
                break;
            case 3: //png
                $img = imagecreatefrompng($name);
                break;
            default:
                return false;
                break;
        }
        return $img;
    }

    /**
     * 内部使用的私有方法，返回等比例缩放的图片宽度和高度，如果原图比缩放后的还小保持不变
     * @param $width
     * @param $height
     * @param $img_info
     * @param $force
     * @return array
     */
    private function _new_size($width, $height, $img_info, $force)
    {
        $size = ['width' => $width, 'height' => $height];
        if ($force) {
            return $size;
        }
        $size["width"] = $img_info["width"];  //原图片的宽度
        $size["height"] = $img_info["height"]; //原图片的高度

        if ($width < $img_info["width"]) {
            $size["width"] = $width;    //缩放的宽度如果比原图小才重新设置宽度
        }
        if ($height < $img_info["height"]) {
            $size["height"] = $height; //缩放的高度如果比原图小才重新设置高度
        }
        /* 等比例缩放的算法 */
        if ($img_info["width"] * $size["width"] > $img_info["height"] * $size["height"]) {
            $size["height"] = round($img_info["height"] * $size["width"] / $img_info["width"]);
        } else {
            $size["width"] = round($img_info["width"] * $size["height"] / $img_info["height"]);
        }
        return $size;
    }

    /**
     * 内部使用的私有方法，用于保存图像，并保留原有图片格式
     * @param $new_img
     * @param $new_file_name
     * @param $img_info
     * @return mixed
     */
    private function _create_image($new_img, $new_file_name, $img_info)
    {
        //$this->path = rtrim($this->path,"/")."/";
        switch ($img_info["type"]) {
            case 1: //gif
                // $result = imageGIF($new_img, $new_file_name);
                imageGIF($new_img, $new_file_name);
                break;
            case 2: //jpg
                // $result = imageJPEG($new_img,$new_file_name);
                imageJPEG($new_img, $new_file_name);
                break;
            case 3: //png
                // $result = imagePng($new_img, $new_file_name);
                imagePng($new_img, $new_file_name);
                break;
        }
        imagedestroy($new_img);
        return $new_file_name;
    }

    /**
     * 内部使用的私有方法，用于加水印时复制图像
     * @param $ground_img
     * @param $water_img
     * @param $pos
     * @param $water_info
     * @return mixed
     */
    private function _copy_image($ground_img, $water_img, $pos, $water_info)
    {
        imagecopy($ground_img, $water_img, $pos["posX"], $pos["posY"], 0, 0, $water_info["width"], $water_info["height"]);
        imagedestroy($water_img);
        return $ground_img;
    }

    /**
     * 内部使用的私有方法，处理带有透明度的图片保持原样
     * @param $src_img
     * @param $size
     * @param $img_info
     * @return resource
     */
    private function _kid_of_image($src_img, $size, $img_info)
    {
        $newImg = imagecreatetruecolor($size["width"], $size["height"]);
        $otsc = imagecolortransparent($src_img);
        if ($otsc >= 0 && $otsc < imagecolorstotal($src_img)) {
            $transparentcolor = imagecolorsforindex($src_img, $otsc);
            $newtransparentcolor = imagecolorallocate($newImg, $transparentcolor['red'], $transparentcolor['green'], $transparentcolor['blue']);
            imagefill($newImg, 0, 0, $newtransparentcolor);
            imagecolortransparent($newImg, $newtransparentcolor);
        }
        imagecopyresized($newImg, $src_img, 0, 0, 0, 0, $size["width"], $size["height"], $img_info["width"], $img_info["height"]);
        imagedestroy($src_img);
        return $newImg;
    }
}
