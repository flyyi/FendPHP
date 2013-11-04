<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://fend.gimoo.net)
 *
 * ������� ��Ҫ�������������ļ�,��������
 * �罫ҳ�沿����Ϣ,���ݿ��ѯ��������浽�ļ�
 * delect bug=0
 *
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Redis.php 4 2011-12-29 11:01:08Z gimoo $
**/

class Fend_Cache_Redis extends Fend
{
    public $mc;//���ӳɹ�ʱ�ı�ʶ
    private static $in=null;

    /**
     * Ԥ������ ��չʹ��
     *
    **/
    public static function Factory()
    {
        if(null===self::$in) self::$in = new self;
        return self::$in;
    }

    /**
     * ��ʼ������
     *
     * @return void
    **/
    public function __construct()
    {
        $this->mc=new Redis();
        $this->mc->connect(
            isset($this->rdscfg['host']) ? $this->rdscfg['host'] : '127.0.0.1',
            isset($this->rdscfg['port']) ? $this->rdscfg['port'] : '11211'
        ) or self::showMsg('[RedisCache:]Could not connect');
        !isset($this->rdscfg['pre']) && $this->rdscfg['pre']='';
    }

    /**
     * ��set������ͬ
     * Ψһ��������: ���Ӷ��������л�����
     *
     * @param  string $key    ���ݵı�ʶ
     * @param  string $value  ʵ������
     * @param  string $expire ����ʱ�䵥λ��
     * @return bool
    **/
    public function sets($key,$value,$expire=0)
    {
        $expire>0 && $expire=self::setLifeTime($expire);
        return $expire>0 ? $this->mc->setex($this->rdscfg['pre'].$key,$expire,$value) : $this->mc->set($this->rdscfg['pre'].$key,$value);
    }

    /**
     * ��ȡ���ݻ���
     *
     * @param  string $key    ���ݵı�ʶ
     * @return string
    **/
    public function gets($key)
    {
        return $this->mc->get($this->rdscfg['pre'].$key);
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
    public function set($key,$value,$expire=0)
    {
        $value=self::rdsCode($value,1);
        $expire>0 && $expire=self::setLifeTime($expire);
        return $expire>0 ? $this->mc->setex($this->rdscfg['pre'].$key,$expire,$value) : $this->mc->set($this->rdscfg['pre'].$key,$value);
    }

    /**
     * ��ȡ���ݻ���
     *
     * @param  string $key    ���ݵı�ʶ
     * @return string
    **/
    public function get($key)
    {
        $value=$this->mc->get($this->rdscfg['pre'].$key);
        return $value ? self::rdsCode($value) : $value;
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
    public function add($key,$value,$expire=0)
    {
        if($expire>0){
            $expire=self::setLifeTime($expire);
            if($this->mc->exists($this->rdscfg['pre'].$key)){
                return false;
            }else{
                return $this->set($key,$value,$expire);
            }
        }else{
            $value=self::rdsCode($value,1);
            return $this->mc->setnx($this->rdscfg['pre'].$key,$value);
        }
    }

    /**
     * �滻����
     * �� add|set ������ͬ,��set�Ƚ�����
     * Ψһ��������: ֻ�е�key������δ����ʱ���ܱ��滻����
     *
     * @param  string $key    ���ݵı�ʶ
     * @param  string $value  ʵ������
     * @param  string $expire ����ʱ��[��d|��w|Сʱh|����i] ��:8d=8�� Ĭ��Ϊ0��������
     * @param  bool   $iszip  �Ƿ�����ѹ��
     * @return bool
    **/
    public function replace($key,$value,$expire=0)
    {
        if(self::iskey($key)){
            return self::set($key,$value,$expire);
        }
        return false;
    }

    /**
     * ��⻺���Ƿ����
     *
     * @param  string $key ���ݵı�ʶ
     * @return bool
    **/
    public function isKey($key)
    {
        return $this->mc->exists($this->rdscfg['pre'].$key);
    }

    /**
     * ɾ��һ�����ݻ���
     *
     * @param  string $key    ���ݵı�ʶ
     * @param  string $expire ɾ���ĵȴ�ʱ��,���������⾡����Ҫʹ��
     * @return bool
    **/
    public function del($key)
    {
        return $this->mc->del($this->rdscfg['pre'].$key);
    }


    /**
     * ��ʽ������ʱ��
     * ע��: ����ʱ��С��2592000=30����
     *
     * @param  string $t Ҫ����Ĵ�
     * @return int
    **/
    private function setLifeTime($t)
    {
        if(!is_numeric($t)){
            switch(substr($t,-1)){
                case 'w'://��
                    $t=(int)$t*7*24*3600;
                    break;
                case 'd'://��
                    $t=(int)$t*24*3600;
                    break;
                case 'h'://Сʱ
                    $t=(int)$t*3600;
                    break;
                case 'i'://����
                    $t=(int)$t*60;
                    break;
                default:
                    $t=(int)$t;
                    break;
            }
        }
        if($t>2592000) self::showMsg('Memcached Backend has a Limit of 30 days (2592000 seconds) for the LifeTime');
        return $t;
    }

    /**
     * �������
     *
     * @param  string $str ��
     * @param  string $tp  ����,1����0Ϊ����
     * @return array|string
    **/
    private function rdsCode($str,$tp=0)
    {
        return $tp ? serialize($str) : unserialize($str);
    }

    /**
     * �����쳣��Ϣ ����ͨ��try���в�׽����Ϣ
     *
     * @param  resource $query ��Դ��ʶָ��
     * @return boolean
    **/
    private function showMsg($str)
    {
        throw new Fend_Exception($str);
    }
}

?>