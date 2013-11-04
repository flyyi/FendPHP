<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://Fend.Gimoo.Net)
 *
 * ��Ⲣ����URL
 * �ֽ�URL��ַ��ȡ�����ϸ��Ϣ
 *
 * @param string $string    ��Ҫ������ַ���
 * @param string $operation �ӽ�����,de����|en����
 * @param string $key       �������,Ĭ��ΪFDKEY
 * @param string $expiry    ����ʱ��
 * @return string
 * @--------------------------------
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: fend.dourl.php 4 2011-12-29 11:01:08Z gimoo $
**/

function doUrl($url,$type=null)
{
    $url=strtolower($url);
    $tem=array('http'=>'',//HTTPЭ������
        'url'=>'',//�����ȫ��URL��
        'turl'=>'',//����URL
        'uri'=>'',//URI����
        'domain'=>'',//�������������
        'tdomain'=>'',//��������
        );
    $netKo=array('cn','com','org','gov','mobi','me','net','info','name','biz','cc','tv','asia','hk');
    if(preg_match('/^(http:\/\/|https:\/\/)?([a-z0-9\-_\.]+)(.*)/i',$url,$url)){
        list($url,$http,$domain, $uri) = $url;
        //���˷Ƿ�����
        $domain{0}=='.' && $domain=substr($domain,1,strlen($domain));
        empty($uri) && $uri='/';

        //��������Ƿ���ȷ
        $domain=preg_replace('/[\.]{1,}/','.',$domain);//ȥ���Ƿ�����
        $tdomain=explode('.',$domain);
        if(count($tdomain)>1){
            $tm=array();
            for($i=count($tdomain)-1;$i>=0;$i--){
                array_unshift($tm,$tdomain[$i]);
                if(!in_array($tdomain[$i],$netKo)){break;}
            }
            if(count($tm)>1){//���и�ֵ
                $tem['http']=empty($http) ? 'http://' : $http;
                $tem['turl']=$tem['http'].$domain;
                $tem['url']=$tem['turl'].$uri;
                $tem['uri']=$uri;
                $tem['domain']=$domain;
                $tem['tdomain']=join('.',$tm);
            }
        }
    }
    return isset($tem[$type]) ? $tem[$type] : $tem;
}

?>