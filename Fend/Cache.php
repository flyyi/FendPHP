<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://fend.gimoo.net)
 *
 * ������� ��Ҫ�������������ļ�,��������
 * �罫ҳ�沿����Ϣ,���ݿ��ѯ��������浽�ļ�
 *
 * ��������:
 * 0 �ļ�����
 * 1 memcache����
 * 2 redis����
 *
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Cache.php 4 2011-12-29 11:01:08Z gimoo $
**/

!defined('FD_CACHE_TYPE') && define('FD_CACHE_TYPE',0);//Ĭ��ѡ���ļ�����
class Fend_Cache
{
    private static $in=null;
    //Ԥ������ ��չʹ��
    public static function factory($t)
    {
        if($t==1){
            self::$in=Fend_Cache_Memcache::Factory();
        }elseif($t==2){
            self::$in=Fend_Cache_Redis::Factory();
        }else{
            self::$in=Fend_Cache_Fcache::Factory();
        }
    }

    /**
     * ----������
     * �ļ����ڴ滺����л�
     * ��$tΪ��ʱ,�л�Ϊ��һ����ģʽ
     * ��$t�ǿ�ʱ,�л�ָ���Ļ���
     * 0���ļ����� 1���ڴ滺��
     *
     * @param  string $t ����:0�ļ�|1�ڴ�|null����|-1Ĭ��
     * @return void
    **/
    public static function Change($t=null)
    {
        if(null==$t){//���л�
            $t= FD_CACHE_TYPE ^ 1;
        }elseif($t==-1){//�л���Ĭ��
            $t= FD_CACHE_TYPE;
        }
        Fend_Cache::factory($t);
    }

    /**
     * �������ݻ���
     * ��add|replace�Ƚ�����
     * Ψһ��������: ����key�Ƿ����,�Ƿ���ڶ�����д������
     *
     * @param  string $key    ���ݵı�ʶ
     * @param  string $value  ʵ������
     * @param  string $expire ����ʱ��[��d|��w|Сʱh|����i] ��:8d=8�� Ĭ��Ϊ0��������
     * @param  bool   $iszip  �Ƿ�����ѹ��
     * @return bool
    **/
    public static function set($key,$value,$expire=0,$iszip=false)
    {
        return self::$in->set($key,$value,$expire,$iszip);
    }

    /**
     * ��ȡ���ݻ���
     *
     * @param  string  $key �����ļ����ư�����׺
     * @param  integer $t   ����ʱ������(��λ��),ȡ�ö೤ʱ����д��Ļ���
     * @return string|array
    **/
    public static function get($key,$t=0)
    {
        return self::$in->get($key,$t);
    }

    /**
     * �������ݻ���
     * ֻ�е�key����,���ڵ��ѹ���ʱ����ֵ
     *
     * @param  string $key    ���ݵı�ʶ
     * @param  string $value  ʵ������
     * @param  string $expire ����ʱ��[��d|��w|Сʱh|����i] ��:8d=8�� Ĭ��Ϊ0��������
     * @param  bool   $iszip  �Ƿ�����ѹ��
     * @return bool   �����ɹ�ʱ����ture,������ڷ���false���򷵻�true
    **/
    public static function add($key,$value,$expire=0,$iszip=false)
    {
        return self::$in->add($key,$value,$expire,$iszip);
    }

    /**
     * �滻���ݻ���
     * �� add|set ������ͬ,��set�Ƚ�����
     * Ψһ��������: ֻ�е�key������δ����ʱ���ܱ��滻����
     *
     * @param  string $key    ���ݵı�ʶ
     * @param  string $value  ʵ������
     * @param  string $expire ����ʱ��[��d|��w|Сʱh|����i] ��:8d=8�� Ĭ��Ϊ0��������
     * @param  bool   $iszip  �Ƿ�����ѹ��
     * @return bool
    **/
    public static function replace($key,$value,$expire=0,$iszip=false)
    {
        return self::$in->replace($key,$value,$expire,$iszip);
    }

    /**
     * ��⻺���Ƿ����
     *
     * @param  string $key ���ݵı�ʶ
     * @return bool
    **/
    public static function isKey($key)
    {
        return self::$in->isKey($key);
    }

    /**
     * ɾ�����ݻ���
     *
     * @param  string $key    ���ݵı�ʶ
     * @param  string $expire ɾ���ĵȴ�ʱ��,���������⾡����Ҫʹ��
     * @return bool
    **/
    public static function del($key,$expire=0)
    {
        return self::$in->del($key,$expire);
    }

    /**
     * ֱ�ӷ����ڲ�����
     *
     * @param  int $tp ��������,1��ǰ����,0�ӿڶ���
     * @return bool
    **/
    public static function obj($tp=0)
    {
        return $tp ? self::$in : self::$in->mc;
    }
}

Fend_Cache::factory(FD_CACHE_TYPE);
?>