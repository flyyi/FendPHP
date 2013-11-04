<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://fend.gimoo.net)
 *
 * ������� ��Ҫ�������������ļ�,��������
 * �罫ҳ�沿����Ϣ,���ݿ��ѯ��������浽�ļ�
 *
 * ע��:�и�ģ��д��Ļ����ļ�,������Ϊ�༭�޸�,�����޷���ȡ�����ļ�
 *
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Fcache.php 4 2011-12-29 11:01:08Z gimoo $
**/

!defined('FD_FCACHE_LIFE') && define('FD_FCACHE_LIFE', 31536000);//Ĭ��һ��
class Fend_Cache_Fcache extends Fend
{
    public $mc;//���ӳɹ�ʱ�ı�ʶ
    //Fcache�����ļ�
    private $fc=array(
        'froot'=>'/tmp/',//�����Ŀ¼
        'type'=>0,       //����ʲô��ʽ����
        'fmod'=>0755,    //д���ļ���Ȩ��
        'fext'=>'.php',  //д���ļ��ĺ�׺
        'fdef'=>array(), //Ĭ�Ͻ������
    );
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
     * ��ʼ��FC����
     *
     * @param  string $var1    ����˵��
     * @param  string $var2    ����˵��
     * @return array  $tplPre  ģ���׺
    **/
    public function __construct()
    {
        isset($this->fccfg) && $this->fc=array_merge($this->fc,$this->fccfg);//���������ļ�
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
        $key = $this->_fpath($key,1);
        $value = "<?php\n".date('//Y-m-d H:i:s')."\n return ".var_export($value,true).";\n?>";
        $value = file_put_contents($key, $value);
        self::_setLife($key, $expire);
        return $value;
    }

    /**
     * ��ȡ���ݻ���
     *
     * @param  string  $key �����ļ����ư�����׺
     * @param  integer $t   �ѱ�����--����ʱ������(��λ��),ȡ�ö೤ʱ����д��Ļ���
     * @return string|array
    **/
    public function get($key)
    {
        $key = $this->_fpath($key);
        if(!is_file($key) || !self::_isLife($key)) return $this->fc['fdef'];
        return include($key);
    }

    /**
     * �������ݻ���
     * ��key�����ڻ�������ѹ���ʱ����ֵ
     *
     * @param  string $key    ���ݵı�ʶ
     * @param  string $value  ʵ������
     * @param  string $expire ����ʱ��[��d|��w|Сʱh|����i] ��:8d=8�� Ĭ��Ϊ0��������
     * @param  bool   $iszip  �Ƿ�����ѹ��
     * @return bool   �����ɹ�ʱ����ture,������ڷ���false���򷵻�true
    **/
    public function add($key,$value,$expire=0,$iszip=false)
    {
        $key = $this->_fpath($key,1);
        if( is_file($key) && self::_isLife($key) ) return false;//����δʧЧֱ�ӷ���
        $value = "<?php\n".date('//Y-m-d H:i:s')."\n return ".var_export($value,true).";\n?>";
        $value = file_put_contents($key, $value);
        self::_setLife($key, $expire);
        return $value;
    }

    /**
     * �滻���ݻ���
     * �� add|set ������ͬ,��set�Ƚ�����
     * Ψһ��������: ֻ�е�key������δ����ʱ���ܱ���ֵ
     *
     * @param  string $key    ���ݵı�ʶ
     * @param  string $value  ʵ������
     * @param  string $expire ����ʱ��[��d|��w|Сʱh|����i] ��:8d=8�� Ĭ��Ϊ0��������
     * @param  bool   $iszip  �Ƿ�����ѹ��
     * @return bool
    **/
    public function replace($key,$value,$expire=0,$iszip=false)
    {
        $key = $this->_fpath($key);
        if(!is_file($key) || !self::_isLife($key)) return false;//�ļ�������
        $value = "<?php\n".date('//Y-m-d H:i:s')."\n return ".var_export($value,true).";\n?>";
        $value = file_put_contents($key, $value);
        self::_setLife($key, $expire);
        return $value;
    }

    /**
     * ��⻺���Ƿ����
     *
     * @param  string $key ���ݵı�ʶ
     * @return bool
    **/
    public function isKey($key)
    {
        $key=$this->_fpath($key);
        if(is_file($key)){
            return self::_isLife($key);
        }else{
            return false;
        }
    }

    /**
     * ɾ�����ݻ���
     *
     * @param  string $key    ���ݵı�ʶ
     * @param  string $expire ɾ���ĵȴ�ʱ��,���������⾡����Ҫʹ��
     * @return bool
    **/
    public function del($key,$expire=0)
    {
        return @unlink($this->_fpath($key));
    }

    /**
     * ��ȡ�洢·��
     *
     * @param  string  $fn �����ļ����ư�����׺
     * @param  integer $t  �Ƿ���Ŀ¼,1������ʱ����֮|0������� Ĭ��Ϊ0
     * @return string      ϵͳ������·��
    **/
    private function _fpath($fn,$t=0)
    {
        $fpath=str_replace('_','/',$fn,$i);
        if($i<=0){//û����Ŀ¼
            $fpath=$this->fc['froot'].$fpath;
        }else{
            $fpath=dirname($fpath);
            $t && !is_dir($this->fc['froot'].$fpath) && mkdir($this->fc['froot'].$fpath,$this->fc['fmod'],true);
            $fpath=$this->fc['froot'].$fpath.'/'.$fn;
        }
        return $fpath.$this->fc['fext'];
    }

    /**
     * �������
     *
     * @param  string  $key �ļ���ʶ
     * @return bool 1��ʾδ����|0��ʶ�ѹ���
    **/
    private function _isLife($key)
    {
        $fm=filemtime($key);
        if($fm>0 && $fm<=time()) return false;
        return true;
    }

    /**
     * �����ļ�����ʱ��
     *
     * @param  string  $key    �ļ���ʶ
     * @param  integer $expire ����ʱ��
     * @return void
    **/
    private function _setLife($key,$expire)
    {
        if(!is_numeric($expire)){
            switch(substr($expire,-1)){
                case 'w'://��
                    $expire=(int)$expire*7*24*3600;
                    break;
                case 'd'://��
                    $expire=(int)$expire*24*3600;
                    break;
                case 'h'://Сʱ
                    $expire=(int)$expire*3600;
                    break;
                case 'i'://����
                    $expire=(int)$expire*60;
                    break;
                default:
                    $expire=(int)$expire;
                    break;
            }
        }

        touch($key, $expire>0 ? $expire+time() : time()+FD_FCACHE_LIFE);
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