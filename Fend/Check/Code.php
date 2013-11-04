<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://fend.gimoo.net)
 *
 * ������֤�����
 *
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Code.php 4 2011-12-29 11:01:08Z gimoo $
**/

class Fend_Check_Code
{
    public static $CodeMax=4;//��֤�볤��
    public static $CodeStr='__FD__code';

    /**
     * ������֤�벢��ͼƬ��ʽ����
    **/
    public static function Index()
    {
        $SecCode=self::Random(self::$CodeMax);
        setcookie(self::$CodeStr,md5($SecCode));//���ô���
        //---------------------------------------------------------bg
        $im = imagecreate(64, 20);
        $background_color = imagecolorallocate ($im, 220, 220, 220);
        for ($i=0; $i <= 128; $i++){
            $point_color = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagesetpixel($im, mt_rand(0, 64), mt_rand(0, 25), $point_color);
        }

        for($i = 0; $i < self::$CodeMax; $i++){
            $text_color = imagecolorallocate($im, mt_rand(0,255), mt_rand(0,128), mt_rand(0,255));
            $x = 5 + $i * 15;
            $y = mt_rand(0, 7);
            imagechar($im, 5, $x, $y,  $SecCode{$i}, $text_color);
        }

        header("Expires: 0");
        @header("Cache-Control: private, post-check=0, pre-check=0, max-age=0", FALSE);
        header("Pragma: no-cache");
        header('Content-type: image/png');
        header( "Content-Disposition:attachment;filename=chackcode.png ");
        //header( "Content-Disposition:inline;filename=chackcode.gif ");
        imagepng($im);
        imagedestroy($im);
    }

    /**
     * �����֤��
     * @param integer $str ��֤��
     * @return boolean �Ƿ�ͨ����֤
    **/
    public static function isCode($str)
    {
        $item=@$_COOKIE[self::$CodeStr];
        return md5($str)==$item ? true : false;
    }

    /**
     * �����ȡ��֤�ַ�����
     * @param integer $length ��֤������
     * @return string �����ȡ����֤��
    **/
    public static function Random($length)
    {
        $hash = null;
        $chars = '0123456789';
        $max = strlen($chars) - 1;
        for($i = 0; $i < $length; $i++){
            $hash .= $chars{mt_rand(0, $max)};
        }
        return $hash;
    }
}
?>
