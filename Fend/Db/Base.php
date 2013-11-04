<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://fend.gimoo.net)
 *
 * ���ݿ�ģ����� [Interface]
 * �����ֻҪͨ��Fend_Db��̬����Ķ��������ϸö���ָ���ı�׼
 *
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Base.php 4 2011-12-29 11:01:08Z gimoo $
**/

interface Fend_Db_Base
{
    //�������ݿⲢ����һ�����ӱ�ʶ
    public function getConn($r);

    //ѡ�񲢴����ݿ�
    public function getDb($str=NULL);

    //ȡ��һ�����ݼ�
    public function get($sql);

    //���ز�ѯ�����м�¼��������
    public function getall($sql);

    //���ز��������ID
    public function getid();

    //���Ͳ�ѯ
    public function query($sql,$r=null);

    //���ؼ���Ϊ�ֶ��������鼯��
    public function fetch($query);

    //��ʽ��MYSQL��ѯ�ַ���
    public function escape($str);

    //�رյ�ǰ���ݿ�����
    public function close();

    //--�ṹӦ��--------------------

    //ȡ�õ�ǰ���ݿ����������ݱ�����
    public function getTB($db=NULL);

    //����һ�ű� (Դ��,Ŀ���,��������Ƿ�ɾ��Ŀ���1Ϊ�Զ�ɾ��0Ϊ����)
    public function copyTB($souTable,$temTable,$isdel=FALSE);

    //ȡ�����ݱ�������ֶ��Լ��������;
    public function getFD($table);

    //ȡ��һ�����ݱ��Create��׼SQL
    public function sqlTB($table);

    //ɾ����
    public function delTB($tables);

    //�Ż���
    public function setTB($tables);

    //����SELECT,REPLACE,UPDATE�ɲ�ѯ�ı�׼SQL���
    public function subSQL($arr,$dbname,$type='update',$where=NULL);

    //���ؼ���Ϊ�������ֵ����鼯��
    public function fetchs($query);

    //ȡ�� RESULT ��Ľ����Ŀ
    public function rerows($query);

    //���ر�INSERT��UPDATE��DELETE��ѯ��Ӱ��ļ�¼����
    public function afrows();

    //�ͷŽ��������
    public function refree($query);

    //�ͳ��쳣
    public function showMsg($str);
}


?>