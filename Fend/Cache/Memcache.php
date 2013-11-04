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
 * @version $Id: Memcache.php 4 2011-12-29 11:01:08Z gimoo $
**/

class Fend_Cache_Memcache extends Fend
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
     * ��ʼ��MC����
     *
     * @return void
    **/
    public function __construct()
    {
        $this->mc=new Memcache;
        @$this->mc->connect(
            isset($this->mccfg['host']) ? $this->mccfg['host'] : '127.0.0.1',
            isset($this->mccfg['port']) ? $this->mccfg['port'] : '11211'
        ) or self::showMsg('[MemCache:]Could not connect');

        !isset($this->mccfg['pre']) && $this->mccfg['pre']='';
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
    public function set($key,$value,$expire=0,$iszip=false)
    {
        $expire>0 && $expire=self::setLifeTime($expire);
        return $this->mc->set($this->mccfg['pre'].$key,$value,$iszip,$expire);
    }

    /**
     * ��ȡ���ݻ���
     *
     * @param  string $key    ���ݵı�ʶ
     * @return string
    **/
    public function get($key)
    {
        return $this->mc->get($this->mccfg['pre'].$key);
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
    public function add($key,$value,$expire=0,$iszip=false)
    {
        $expire>0 && $expire=self::setLifeTime($expire);
        return $this->mc->add($this->mccfg['pre'].$key,$value,$iszip,$expire);
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
    public function replace($key,$value,$expire=0,$iszip=false)
    {
        $expire>0 && $expire=self::setLifeTime($expire);
        return $this->mc->replace($this->mccfg['pre'].$key,$value,$iszip,$expire);
    }

    /**
     * ��⻺���Ƿ����
     *
     * @param  string $key ���ݵı�ʶ
     * @return bool
    **/
    public function isKey($key)
    {
        if($this->mc->add($this->mccfg['pre'].$key,1)){//������
            $this->mc->delete($key,0);
            return false;
        }else{//����
            return true;
        }
    }

    /**
     * ɾ��һ�����ݻ���
     *
     * @param  string $key    ���ݵı�ʶ
     * @param  string $expire ɾ���ĵȴ�ʱ��,���������⾡����Ҫʹ��
     * @return bool
    **/
    public function del($key,$expire=0)
    {
        return $this->mc->delete($this->mccfg['pre'].$key,$expire);
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