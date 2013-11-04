<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://fend.gimoo.net)
 *
 * ������ ��������APP��ض���
 * ͨ���������뿪����Ա��д�ķǿ��ģ���
 *
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Acl.php 4 2011-12-29 11:01:08Z gimoo $
**/

class Fend_Acl extends Fend
{
    private $_module=array();//��ע���ģ��
    private $_routeobj=null;//·��������
    private $_route=array();//��·�ɵ�ģ��
    private static $in;//����ȫ��״̬����

    /**
     * ����ģʽ: ������ض���
     *
     * @return object
    **/
    public static function Factory()
    {
        if(!isset(self::$in)){
            self::$in=new self();
        }
        return self::$in;
    }

    /**
     * ��ʼ���������
     * ��һЩ������ñ�����ʼ����ϵͳȫ�ֱ�����
     * ���κ�PHP�����п���ͨ��this��GLOBAL����
     *
     * @return object
    **/
    public function __construct()
    {
        //��ʼ��Ĭ�����õ�ȫ�ֱ���
        $this->aclcfg=array(
            'appsDir'=> null,        //APPSĿ¼
            'deFunc'=> 'index',      //Ĭ�Ϸ���
            'deLib'=> 'index',       //Ĭ��Controller����
            'deMod'=> 'default',     //Ĭ��ģ��
            'sufObj'=> 'lib',        //Controller��׺[����ĺ�׺]
            'sufLib'=> '_lib',       //Controller�ļ���׺[����ĺ�׺]
            'sufFunc'=> 'Fend',      //Action��׺[�����ڷ����ĺ�׺]
            'isCase'=> 1,            //�Ƿ����ִ�Сд-������
        );
        $this->uri=array($_SERVER['HTTP_HOST'],null,null,null);
        //$this->getParam();
    }

    /**
     * �������ü���������������
     *
     * @param array $cfg ������
    **/
    public function setAcl(array $cfg)
    {
        $this->aclcfg=array_merge($this->aclcfg,$cfg);
    }

    /**
     * ע��ģ��[ģ��ע����]
     * ģ��ע����Ҳ��һ�ֹ�����,����ģ�鱻ע��ʱ
     * �ұ�run��ģ�鲻�ڱ�ע��ķ��������ģ�鱻ֹͣ���в��ͳ��쳣
     *
     * Example:
     * ��ʽһ $mods=array('sys','news','blog')
     * ��ʽ�� $mods=array('sys'=>array(),'news'=>array('path'=>'/web/cms/news/'),'blog')
     * @param array $mods ��ע���ģ��
    **/
    public function setModule(array $mods)
    {
        foreach($mods as $k=>&$v){
            if(is_array($v)){
                $this->_module[$k]=$v;
            }else{
                $this->_module[$v]=array();
            }
        }
    }

    /**
     * ����·����
     *
     * Example:
     * http://example/news/
     * array('news'=>array('mod'=>'blogs',))
     * @param array $mods ��ע���ģ��
    **/
    public function setRoute(array $mods)
    {
        $this->_route=$mods;
    }

    /**
     * ��ȡģ����󲢶�λģ�� ACL_url
     * ���ǵ�������/·�����������������еĴ��ݽ�����ģ��/������/�����������ִ�Сд
     *
     * @param array $app ��ע���ģ��
    **/
    public function run($app)
    {
        if(!is_array($app)){
            $this->getParam($app);
        }

        //�����ִ�Сд
        foreach($app as $k=>&$v){
            $this->uri[$k]=strtolower($v);
        }

        //����Ƿ����·��
        if(!empty($this->uri[2]) && isset($this->_route[$this->uri[2]])){//����·������
            $this->_getRoute()->toRoute($this->_route[$this->uri[2]]);
        }

        //��ģ�����ע�뵽ȫ����
        $module=&$this->uri[1];
        $controller=&$this->uri[2];
        $action=&$this->uri[3];
        empty($controller) && $controller=$this->aclcfg['deLib'];
        empty($module) && $module=$this->aclcfg['deMod'];

        //�ֽⲢ���ģ���Ƿ����
        $path=$this->aclcfg['appsDir'].$module.'/';
        !empty($this->_module) && $this->_isModule($module,$controller,$path);
        $fclass=$path.$controller.$this->aclcfg['sufLib'].'.php';

        //�������
        if(is_file($fclass)){
            @include_once($path.'common.php');//���ع���ģ��
            include_once($fclass);
            $fclass=$controller.$this->aclcfg['sufObj'];
            $c=new $fclass;

            //��ȡ���������Public����
            $item = array_map('strtolower', get_class_methods($c));

            //��ⱻָ���ķ����Ƿ���ڲ�������ʹ��Index��ΪĬ�Ϸ���
            (empty($action) || !in_array($action.strtolower($this->aclcfg['sufFunc']),$item)) && $action=$this->aclcfg['deFunc'];
            unset($item);

            //ִ��Ĭ��Init����
            call_user_func_array(array($c,'Init'),array());

            //ִ��ָ���ķ���
            call_user_func_array(array($c,$action.$this->aclcfg['sufFunc']),array());
        }else{
            throw new Fend_Acl_Exception("Not Found Object: {$controller}",404);
        }
        $this->cfg['debug'] && Fend_Debug::factory()->dump();
    }

    /**
     * ��ȡURL
     *
     * @param int $tp ��ȡ���ϻ���һ��
    **/
    public function getParam(&$app)
    {
        $app=strtok($app, '?');
        $app=explode('/',$app);
        $this->url=$app;
    }

    /**
     * ���ģ���Ƿ��ִ��
     *
     * @param string $module     ģ��
     * @param string $controller controller
     * @param string $path       Controller����·��
    **/
    public function _isModule(&$module,&$controller,&$path)
    {
        //δ�������ģ��
        if(!isset($this->_module[$module])){
            throw new Fend_Acl_Exception("Not Found Modules: {$module}",403);
        }elseif(isset($this->_module[$module]['controller']) && !in_array($controller,$this->_module[$module]['controller'])){
            throw new Fend_Acl_Exception("Not Found Controller: {$controller}",403);
        }
        isset($this->_module[$module]['path']) && $path=$this->_module[$module]['path'];
    }

    /**
     * �ڲ��������ACL·��������
     * �ö���ͬFend_Aclһ�����
     *
     * @return object
    **/
    private function _getRoute()
    {
        if(null === $this->_routeobj){
            $this->_routeobj = new Fend_Acl_Router();
        }
        return $this->_routeobj;
    }
}
?>