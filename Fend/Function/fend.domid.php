<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://Fend.Gimoo.Net)
 *
 * ����Ƿ����趨��������֮��
 * ������ǳ����ڱ߽�
 * ����:
 * domid(985,0,100)=100 �ޱ߽�����
 * domid(985,0,100,20,96)=96 ��߽�
 * domid(0,0,100,20,96)=20 С�߽�
 *
 * @param int $it     һ������
 * @param int $min    �߽�,��С����
 * @param int $max    �߽�,�ϴ����
 * @param int $min_de С�߽��Ĭ����ֵ
 * @param int $max_de ��߽��Ĭ����ֵ
 * @return int
 * @--------------------------------
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: fend.domid.php 4 2011-12-29 11:01:08Z gimoo $
**/

function doMid($it,$min,$max,$min_de=null,$max_de=null)
{
    if(null!==$min_de){
        $it<=$min && $it=$min_de;
    }else{
        $it=max($it,$min);
    }

    if(null!==$max_de){
        $it>=$max && $it=$max_de;
    }else{
        $it=min($it,$max);
    }
    return $it;
}
?>