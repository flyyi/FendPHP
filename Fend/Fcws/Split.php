<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2010 Gimoo Inc. (http://fend.gimoo.net)
 *
 * CMS���¹������ӿڶ���
 * �����ȡվ�㻺�沢����վ�����ز���
 *
 * ����---
 * ASCII   [1]0x00-0x7F(0-127)
 * GBK     [1]0x81-0xFE(129-254) [2]0x40-0xFE(64-254)
 * GB2312  [1]0xB0-0xF7(176-247) [2]0xA0-0xFE(160-254)
 * Big5    [1]0x81-0xFE(129-255) [2]0x40-0x7E(64-126)| 0xA1��0xFE(161-254)
 * UTF8    ���ֽ� 0x00-0x7F(0-127) ���ֽ� [1]0xE0-0xEF(224-239) [2]0x80-0xBF(128-191) [3]0x80-0xBF(128-191)
 *
 * @Package GimooFend
 * @Support http://bbs.eduu.com
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Split.php 4 2011-12-29 11:01:08Z gimoo $
**/

define('FD_SPACESPLIT','#');//��ǩ�ļ����
class Fend_Fcws_Split extends Fend_Fcws_Db
{

    /**
     * Ԥ������ ��չʹ��
     *
     * @return object
    **/
    private static $in=null;
    public static function Factory()
    {
        if(null===self::$in) self::$in = new self;
        return self::$in;
    }

    /**
     * д��ʵ��
     *
     * �����ʽΪ�Ե�һ�������ַ���ʼ�ݲ�����:
     * ��������VC -> �� ���� ������ �������� ��������VC
     *
     * @param  string $key   �ؼ���
     * @param  string $value ����Ӧ��ֵ
     * @return string
     */
    public function put($key, $value)
    {
        $key=strtolower($key);//�����ִ�Сд
        $_len=strlen($key);
        for($i=0;$i<$_len;++$i){
            $old=ord($key[$i]);
            $_tmp=null;
            if($old<0x80){
                if(($i+1)<$_len && ord($key[$i+1])<0x80) continue;
                $_tmp=substr($key,0,$i+1);
            }else{
                $old_1=ord($key[++$i]);
                if($old>=0x80 && $old<=0xBF && $old_1>=0x80 && $old_1<=0x80 ){//UTF8
                    $_tmp=substr($key,0,++$i+1);
                }else{
                    $_tmp=substr($key,0,$i+1);
                }
            }
            !parent::get($_tmp) && parent::put($_tmp,FD_SPACESPLIT);
        }
        parent::put($key,$value);
    }

    /**
     * �������Ĵ����Ƿ��йؼ��ʰ������ֿ���
     * ����ҵ��򷵻ر��ҵ��ĵ�һ���ؼ���,���򷵻�false
     *
     * @param  str $str  ��Ҫ������ַ���
     * @param  int $rank �ؼ��ʼ���,����С�ڵ���ָ���ļ���
     * @param  int $mx   �����ְ����е����趨����Ĵ�ʱ,��ȡָ��������ֹͣ
     * @param  int $item һ��ͬmxһ��ʹ��,mx�����õĴ����������ص������
     * @return string
     */
    public function get($str,$rank=0,$mx=5,&$item=null)
    {
        $str=strtolower($str);//�����ִ�Сд
        $len=strlen($str);
        $_isout=false;//����ֵ
        $item=array();

        //˫�������ģʽ���м��
        for($i=0;$i<$len;$i+=$_spa){
            $_c=$str[$i];
            $_o=ord($_c);
            if($_o<0x80){//���ֽ�
                $_spa=1;//�������
                if(self::_is_en_token($_o)) continue; //������Ӣ��
            }else{//˫�ֽ�
                $_spa=2;//�������
                $_c.=$str[$i+1];
                if(false===parent::get($_c)) continue; //������ڴ�
            }

            //�ڶ�������
            $_len=($len-$i)<$this->_keymax ? ($len-$i) : $this->_keymax;
            for($j=$_spa; $j<$_len ; ++$j ){
                $_o=ord($str[$i+$j]);
                if($_o<0x80){//���ֽ�
                    //�����ڵ��ַ����ǵ��ֽ��ַ�,�ҷǷ���ʱ,������������
                    if(!self::_is_en_token($_o) && $j<$_len-1 && ord($str[$i+$j+1])<0x80 && !self::_is_en_token(ord($str[$i+$j+1]))) continue;
                    $_spa==1 && $_spa=$j+1;
                }else{//˫�ֽ�
                    ++$j;
                }
                $_tmp=substr($str,$i,$j+1);
                $_res=parent::get($_tmp);
                if(false===$_res){
                    break;//�����ڴ�
                }elseif($_res==FD_SPACESPLIT){
                    continue;
                }elseif($rank==0 || $_res<=$rank){//�ҵ��趨����Ĺؼ���
                    $item[$_tmp]=$_tmp;
                    $_isout=true;
                    break 2;
                }else{//�ͼ����
                    //$_isout=false===$_isout ? $_res : min($_res,$_isout);
                    $item[$_tmp]=$_tmp;

                    //�Ƿ���������Ŀ
                    if(count($item)>=$mx) break 2;
                    continue;
                }
                break;
            }
        }
        $item=join(' ',$item);
        return $_isout;
    }

    /**
     * ����Ƿ����Ӣ�ı�����
     *
     * @param  string $str �ַ�
     * @return bool
    **/
    private function _is_en_token($_o)
    {
        return ($_o<=47 || ($_o>=58 && $_o<=64) || ($_o>=91 && $_o<=96) || $_o>=123);
    }
}
?>