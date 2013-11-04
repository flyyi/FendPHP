<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://fend.gimoo.net)
 *
 * �洢����ĸ����� [COOKIE]
 *
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Token.php 4 2011-12-29 11:01:08Z gimoo $
**/

Fend_Func::Init('fdcode');
class Fend_Check_Token
{
    public static $CodeMax=5;//settings Code length
    public static $CodeStr='__FD__token';
    public static $CodeItem=null;

    public static function Init()
    {
        return self::toCode(1);
    }

    /**
     * ���ɼ��ܺ��C��
     * @param   integer $t �Ƿ��������ɴ� 1Ϊ��������
     * @return  string ������ܴ�
    **/
    public static function toCode($t=0)
    {
        if($t && (!empty(self::$CodeItem) || !empty($_COOKIE[self::$CodeStr]))){
            return fdcode(empty(self::$CodeItem) ? $_COOKIE[self::$CodeStr] : self::$CodeItem);
        }
        $item=$SecCode=self::Random(self::$CodeMax);
        !empty($_COOKIE[self::$CodeStr]) && $item=fdcode($_COOKIE[self::$CodeStr]);
        self::$CodeItem=fdcode($SecCode,'encode');
        setcookie(self::$CodeStr,self::$CodeItem);//���ô���
        return $item;
    }

    /**
     * ��֤����Ĵ��Ƿ���ȷ
     * @param   string $str ����֤���ַ���
     * @return  Boolean �Ƿ�ͨ����֤
    **/
    public static function isCode($str)
    {
        return $str===self::toCode() ? true : false;
    }

    /**
     * �����ȡ��֤�ַ�����
     * @param   integer $length ��֤������
     * @return  string �����ȡ����֤��
    **/
    public static function Random($length)
    {
        $hash=null;
        $chars='qwertyuiopasdfghjklzxcvbnmQWERTYUIOPLKJHGFDSAZXCVBNM0123456789';
        $max=strlen($chars)-1;
        for($i=0;$i<$length;$i++){
            $hash.=$chars{mt_rand(0,$max)};
        }
        return $hash;
    }
}
//Fend_Check_Token::Init();
?>
