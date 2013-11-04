<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://fend.gimoo.net)
 *
 * Soap��������չ����
 *
 *
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Server.php 4 2011-12-29 11:01:08Z gimoo $
**/

class Fend_Fcli_Server
{
    private $_cfg=array();//������Ϣ
    private $_fmod=array();
    private $_fclass=array();

    private static $in=null;
    public static function Factory()
    {
        if(null===self::$in) self::$in=new self();
        return self::$in;
    }

    /**
     * ��ʼ������
     * ������ز�����ͨ�Žӿ�
     * �������Ϊurl
     * ��ȡ��������val('time:30','agent:get')
     *
     * @param  mixed ��Ҫ���õĲ������ϸ�ʽ:('name:appCMS','user:appCMS','pwd:fendcms')
     * @return void
    **/
    public function Init()
    {
        if(func_num_args()>0){
            $_cfg=&self::$in->_cfg;
            $args=func_get_args();
            foreach($args as $v){
                $v=explode(':',$v,2);
                $_cfg[$v[0]]=$v[1];
            }
        }
    }

    /**
     * ע��һ������
     * �ṩԶ�̵���,��ε��ûḲ��֮ǰע��Ķ���
     *
     * @param string fclass ��������
     * @param array func   ��������ṩ�ķ�������
     * @return void
    **/
    public function regClass($fclass,array $func=array())
    {
        //�����������
        if(is_array($fclass)){
            $_fk=$fclass[0];
            $_fv=$fclass[1];
        }else{
            $_fk=$_fv=$fclass;
        }

        //ת��ΪСд
        $_fk=strtolower($_fk);
        $this->_fclass[$_fk]=$_fv;

        //ת��������Сд
        if(count($func)>0){
            foreach($func as &$v) $v=strtolower($v);
        }
        $this->_fmod[$_fk]=$func;
    }

    /**
     * ע��һ������
     * �ṩԶ�̵���,ע�������Զ�ε���
     *
     * @param string func ��������
     * @return void
    **/
    public function regfunc($func)
    {
        !isset($this->_fmod[0]) && $this->_fmod[0]=array();
        $this->_fmod[0][]=strtolower($func);
    }

    /**
     * �������Զ�̷���
     * //eval("class appfcli{};");
     *
     * @param  string $fc ��ѡ����
     * @return object Fend_Fcli_Base
    **/
    public function run()
    {
        //��֤��Կ
        if(!empty($this->_cfg['key'])){
            if(!isset($_COOKIE['key']) || $_COOKIE['key']!==$this->_cfg['key']){
                self::showMSG("Authentication Failed.");
            }
        }

        //����Ƿ�����API
        if(count($this->_fmod)<=0){
            self::showMSG("Function OR Class is not registered.");
        }

        //���ܴ��ݹ����Ĳ�������
        $this->_cfg['_fkey']=&$_SERVER['HTTP_FCLI_KEY'];//ΨһKEY
        $fcli_mod=&$_SERVER['HTTP_FCLI_MOD'];//����
        $fcli_func=&$_SERVER['HTTP_FCLI_FUNC'];//����
        $fcli_pars=&$_POST;
        $fcli_mod=strtolower($fcli_mod);
        $fcli_func=strtolower($fcli_func);

        //��֤ģ��
        if(empty($this->_cfg['_fkey'])){
            self::showMSG("Authentication Fkey.");
        }

        //�����ʷ���
        if($fcli_mod=='#'){//��������
            $fcli_mod=0;
            if(!isset($this->_fmod[$fcli_mod])){
                self::showMSG("Function is not registered.");
            }
        }elseif(empty($fcli_mod)){
            $fcli_mod=key($this->_fmod);
        }elseif(!isset($this->_fmod[$fcli_mod])){
            self::showMSG("Class is not registered[$fcli_mod].");
        }

        //��֤����
        try{
            if($fcli_mod===0){//��������
                if(!in_array($fcli_func,$this->_fmod[$fcli_mod])){//��֤�û�Ȩ��
                    self::showMSG("Function is not registered[$fcli_func].");
                }elseif(!function_exists($fcli_func)){//��֤ϵͳȨ��
                    self::showMSG("Function not found[$fcli_func].");
                }
                $_fmod=&$fcli_func;
            }else{

                //�������쳣����
                if(!class_exists($this->_fclass[$fcli_mod])) self::showMSG("Class not found[$fcli_mod].");

                //����Ƕ������Ȩ��
                if(!empty($this->_fmod[$fcli_mod]) && !in_array($fcli_func,$this->_fmod[$fcli_mod])){
                    self::showMSG("Class Function is not registered[$fcli_func].ED");
                }

                $fcli_mod=$this->_fclass[$fcli_mod];
                $fcli_mod=new $fcli_mod();
                if(!method_exists($fcli_mod,$fcli_func)){
                    self::showMSG("Class Function not found[$fcli_func].ED");
                }
                $_fmod=array(&$fcli_mod,$fcli_func);
            }

            $res=call_user_func_array($_fmod , $fcli_pars);
            self::showMSG($res,200);
        }catch(Exception $e){
            self::showMSG($e->getMessage(),500);
        }
    }

    /**
     * �ͳ��������
     *
     * @param  string $res   �������
     * @param  string $appid ״̬
     * @return void
    **/
    private function showMSG($res,$appid=0)
    {
        $_type=0;//����Ϊ1,�ַ���Ϊ0
        if(is_array($res)){
            $_type=1;
            $res=http_build_query($res);
        }

        !isset($this->_cfg['_fkey']) && $this->_cfg['_fkey']=uniqid();

        //����ͷ��Ϣ
        header("Content-Length: ".strlen($res));
        header("Fend-TYPE: {$_type}");//��������
        header("Fend-APP: {$appid}");//����״̬
        header("Fend-KEY: {$this->_cfg['_fkey']}");//����ΨһKEY
        die("{$res}");
    }

}

?>