<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://fend.gimoo.net)
 *
 * ����ͼƬ
 * ������Ѿ�������ͼƬ
 *
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Zoomimg.php 4 2011-12-29 11:01:08Z gimoo $
**/

//��������ͼ
class Fend_File_Zoomimg
{
    static $_Swidth=0;//ͼƬʵ�ʵĿ��
    static $_Sheight=0;//ͼƬʵ�ʵĸ߶�

    //������ͼ��*�ʺ����������
    //toZoom(�ļ����Ե�ַ,���ſ��,���Ÿ߶�,���ź��ļ���׺)
    static function toZoom($filename,$W=0,$H=0,$fgoal=null)
    {
        if($W==0 && $H==0) return FALSE;//û�����ÿ�͸�ʱ���账��
        if(false===($im = self::GetImgType($filename))) return FALSE;
        $filename=self::getImgSmall($filename,$fgoal);//�õ�Сͼ��ַ

        if($W==0){
            //����Ϊ0ʱ����Ӧ��
            if($H>self::$_Sheight){
                //����ԭͼ��С��������
                ImageDestroy($im);//������ʱͼ���ͷ��ڴ�
                return FALSE;
            }else{
                $W=ceil(self::$_Swidth*$H/self::$_Sheight);
                $newim = imagecreatetruecolor($W,$H);
                imagecopyresampled($newim,$im,0,0,0,0,$W,$H,self::$_Swidth,self::$_Sheight);
                ImageJpeg($newim,$filename);//����������ͼƬ
            }

        }elseif($H==0){
            //���ߵ���0ʱ�Զ�ʹ�ÿ�
            if($W>self::$_Swidth){
                //����ԭͼ��С��������
                ImageDestroy($im);//������ʱͼ���ͷ��ڴ�
                return FALSE;
            }else{
                $H=ceil(self::$_Sheight*$W/self::$_Swidth);
                $newim = imagecreatetruecolor($W,$H);
                imagecopyresampled($newim,$im,0,0,0,0,$W,$H,self::$_Swidth,self::$_Sheight);
                ImageJpeg ($newim,$filename);//����������ͼƬ
            }
        }else{
            //���������
            $ImgWall=self::MakeZoom(self::$_Swidth,self::$_Sheight,$W,$H);
            $newim = imagecreatetruecolor($W,$H);
            //������------------------------------------------//
            $back = imagecolorallocate($newim, 255, 255, 255);//
            imagefilledrectangle($newim, 0, 0, $W, $H, $back);//
            //������------------------------------------------//
            imagecopyresampled($newim,$im,$ImgWall[2],$ImgWall[3],0,0,$ImgWall[0],$ImgWall[1],self::$_Swidth,self::$_Sheight);
            ImageJpeg($newim,$filename);//����������ͼƬ
            ImageDestroy($im);//������ʱͼ���ͷ��ڴ�
        }
        return TRUE;
    }

    //���ˮӡ�����ʺ�Ӣ���ַ�
    static function toSeal($filename,$ImgText=NULL)
    {
        if(!$ImgText) return FALSE;
        if(false===($im = self::GetImgType($filename))) return false;

        $bg=imagecolorallocate($im, 218, 218, 218);//����ˮӡ����ɫ
        $textcolor=imagecolorallocate($im, 0, 0, 0);//������ɫ
        imagefilledrectangle($im,self::$_Swidth-180,self::$_Sheight-16,self::$_Swidth-2,self::$_Sheight-2,$bg);
        //$string = iconv('gb2312','utf-8',$ImgText);
        //$fon=imagettftext($im, 12, 0, 11, 21, $textcolor, 'simhei.ttf', $string);
        imagestring($im,4, self::$_Swidth-175, self::$_Sheight-18,$ImgText, $textcolor);
        ImageJpeg ($im,$filename);
        //header("Content-type: image/png");
        //imagepng($im);
        return true;
    }

    //�õ�Сͼ�ĵ�ַ
    static function getImgSmall($filename,$fgoal=null)
    {
        if(!empty($fgoal)){
            if(false===($fed=strripos($filename,'.',strlen($filename)-5))){
                $filename.=$fgoal;
            }else{
                $fed=substr($filename,$fed);
                $filename=str_replace($fed,$fgoal,$filename);
            }
        }
        return $filename;
    }

    ///��������
    static function MakeZoom(&$Sw,&$Sh,&$Dw,&$Dh)
    {
        $Ful=array(0=>0,1=>0,2=>0,3=>0);
        if($Sw<=$Dw && $Sh<=$Dh){
            $W=$Sw;
            $H=$Sh;
            $Ful[2]=($W< $Dw) ? ceil(($Dw-$W)/2) : 0;
            $Ful[3]=($H< $Dh) ? ceil(($Dh-$H)/2) : 0;
        }else{
            //����ͼ�����Ч��
            $W=ceil($Dh*$Sw/$Sh);
            $W=($W>=$Dw-1 && $W<=$Dw+1) ? $Dw : $W;
            if($W<$Dw){
                $H=ceil($Dw*$Sh/$Sw);
                $H=($H>=$Dh-1 && $H<=$Dh+1) ? $Dh : $H;
                $W=$Dw;
                $Ful[3]=ceil(($Dh-$H)/2);
            }else{
                $H=ceil($W*$Sh/$Sw);
                $H=($H>=$Dh-1 && $H<=$Dh+1) ? $Dh : $H;
                $Ful[2]=ceil(($Dw-$W)/2);
            }
        }
        $Ful[0]=$W;
        $Ful[1]=$H;
        return $Ful;
    }

    //ȡ��ԴͼƬ�ĸ�/������ͬ������ʱ��ͼ
    static function GetImgType($filename)
    {
        $name = getimagesize($filename); //ȡ��ͼ����Ϣ
        self::$_Swidth = $name[0];	//ȡ��ͼ��ʵ�ʿ��
        self::$_Sheight = $name[1];	//ȡ��ͼ��ʵ�ʸ߶�
        //����ʵ�����ʹ�����ʱͼ��
        switch($name[2]){
            case 1:
                $ImageTemp = imagecreatefromgif($filename);
                break;
            case 2:
                $ImageTemp = imagecreatefromjpeg($filename);
                break;
            case 3:
                $ImageTemp = imagecreatefrompng($filename);
                break;
            default:
                return false;
        }
        return $ImageTemp;
    }
}

/*
  ʹ��ʵ��------------------------------------------------------
  Fend_File_Zoomimg::toZoom('bb.jpg',130,0,'_small');//����ͼƬ,������Ӧ
  Fend_File_Zoomimg::toZoom('bb.jpg',0,500,'_small');//����ͼƬ,������Ӧ
  Fend_File_Zoomimg::toZoom('bb.jpg',200,100,'_small');//����ͼƬ,�̶����
  Fend_File_Zoomimg::toSeal('bb.jpg','hello');//����ˮӡ
*/
?>