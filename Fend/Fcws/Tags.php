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
 * @version $Id: Tags.php 4 2011-12-29 11:01:08Z gimoo $
**/

define('FD_SPACETAGS',' ');//��ǩ�ļ����
class Fend_Fcws_Tags extends Fend_Fcws_Db
{
    private $charset='gbk';
    private $loops=1;
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
     * @param  string $key   �ؼ���
     * @param  string $value ����Ӧ��ֵ
     * @return string
     */
    public function put($key, $value)
    {
        $value=strtolower($value);//�����ִ�Сд
        $value=explode(FD_SPACETAGS,$value);

        foreach($value as &$_key){
            if(empty($_key)) continue;

            //�ؼ��ֶ�Ӧ�ı�ǩ
            $rsc=parent::get($_key);//����Ƿ��Ѿ��м�¼
            if($rsc===false || $rsc=='9'){
                $_tmp=$key;
            }elseif(false===stripos($rsc,$key)){
                $_tmp=$rsc.FD_SPACETAGS.$key;
            }else{
                $_tmp=$rsc.FD_SPACETAGS.$key;
                $_tmp=explode(FD_SPACETAGS,$_tmp);
                $_tmp=array_unique($_tmp);
                $_tmp=join(FD_SPACETAGS,$_tmp);
            }
            parent::put($_key,$_tmp);

            //�������
            $_len =strlen($_key);
            for($i=0;$i<$_len;++$i){
                $old=ord($_key[$i]);
                $_tmp=null;
                if($old<0x80){
                    if(($i+1)<$_len && ord($_key[$i+1])<0x80) continue;
                    $_tmp=substr($_key,0,$i+1);
                }else{
                    $i+=$this->loops;
                     $_tmp=substr($_key,0,$i+1);
                }
                !parent::get($_tmp) && parent::put($_tmp,9);
            }

        }
    }

    /**
     * �������Ĵ����Ƿ��йؼ��ʰ������ֿ���
     * ����ҵ��򷵻ر��ҵ��ĵ�һ���ؼ���,���򷵻�false
     *
     * @param  string $str ��Ҫ������ַ���
     * @param  int    $mx ȡ�õı�ǩ����
     * @return string
     */
    public function get($str,$mx=10)
    {
        $str=strtolower($str);//�����ִ�Сд
        $len=strlen($str);
        $item=array();

        //˫�������ģʽ���м��
        for($i=0;$i<$len;$i+=$_spa){
            $_c=$str[$i];
            $_o=ord($_c);
            if($_o<0x80){//���ֽ�
                $_spa=1;//�������
                if(self::_is_en_token($_o)) continue;//������Ӣ��
            }else{//˫�ֽ�
                $_spa=$this->loops+1;//�������
                $_c.=substr($str,$i+1,$this->loops);
                if(false===parent::get($_c)) continue;//������ڴ�
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
                    $j+=$this->loops;
                }
                $_tmp=substr($str,$i,$j+1);
                $_res=parent::get($_tmp);
                if(false===$_res){
                    break;//�����ڴ�
                }elseif($_res==9){
                    continue;
                }
                $_res=explode(FD_SPACETAGS,$_res);
                foreach($_res as &$v) $item[$v]=$v;
                //�Ƿ���������Ŀ
                if(count($item)>=$mx){
                    $item=array_slice($item,0,$mx);
                    break 2;
                }
            }
        }
        return join(FD_SPACETAGS,$item);
    }

    /**
     * �����ַ���
     *
     * @param  string $var1    ����˵��
     * @param  string $var2    ����˵��
     * @return array  $tplPre  ģ���׺
    **/
    public function SetChar($str)
    {
        $str=strtolower($str);
        $str=='utf-8' && $str='utf8';
        $this->charset=$str;
        $str=='utf8' && $this->loops=2;
    }


    /**
     * ����Ƿ����Ӣ�ı�����,�ո���س�
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