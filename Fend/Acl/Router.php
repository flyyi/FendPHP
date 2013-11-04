<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://fend.gimoo.net)
 *
 * ·����
 * ���¶�λģ�� ������·��
 * ͬʱ���Զ���·�ɱ�Ȩ��
 *
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Router.php 4 2011-12-29 11:01:08Z gimoo $
**/

class Fend_Acl_Router extends Fend_Acl
{
    public function __construct(){}
    /**
     * ��ʼ·��
     *
     * @param integer $tp ���漶��,1-3
     * @param integer $sp �Ƿ񷵻ش�
     * @return string|echo
    **/
    public function toRoute(array &$route)
    {
        //ָ������ƶ�һλ
        $this->uri[1]=$this->uri[0];
        array_shift($this->uri);
        $this->uri[1]=$route['module'];
        $controller=&$this->uri[2];

        if($controller && isset($route['controller']) && is_array($route['controller']) && !in_array($controller,$route['controller'])){
            //����ķ���δ��������Ȩ����
            throw new Fend_Acl_Exception("Not Found Modules: {$controller}",403);
        }
    }
}
?>