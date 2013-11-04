<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://fend.gimoo.net)
 *
 * Mysql DB����
 * ���ø�ʽ: $_dbcfg[0]=array('dbhost'=>'localhost','dbname'=>'fend','dbuser'=>'fend','dbpwd'=>'fend','lang'=>'GBK');
 * ����������ģ�����: class Fend_Db_Mysql1 implements Fend_Db_Base
 *
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Mysql.php 4 2011-12-29 11:01:08Z gimoo $
**/

class Fend_Db_Mysql extends Fend
{
    public $dbLink=array();//����ָ��
    public $dbR=0;//��ǰ���ӱ�ʶ
    public $dbLang='gbk';//����Ĭ�����ݿ����
    public $dbError=false;//�Ƿ��������׳�

    /**
     * �������ݿⲢ�����ʶ,ʵ�ֶ�����Ӳ��л�
     *
     * @param integer $r ���ӱ�ʶ�� $_dbcfg�����е�Key�仯���仯
    **/
    public function getConn($r)
    {
        $this->dbR=$r;//���õ�ǰ����
        if(!isset($this->dbLink[$r])){
            $this->dbLink[$r]=@mysql_connect($this->dbcfg[$r]['dbhost'],$this->dbcfg[$r]['dbuser'],$this->dbcfg[$r]['dbpwd']) or
                              self::showMsg('Your Connection Failure ');

            !empty($this->dbcfg[$r]['lang']) && $this->dbLang=$this->dbcfg[$r]['lang'];
            self::query("SET character_set_connection={$this->dbLang},character_set_results={$this->dbLang},character_set_client=binary,sql_mode='';");
        }
        $this->useDb();
    }

    /**
     * ѡ�в������ݿ�
     *
     * @param string $dbname ��Ҫ�򿪵����ݿ�
    **/
    public function useDb($dbname=null)
    {
        !$dbname && $dbname=$this->dbcfg[$this->dbR]['dbname'];
        mysql_select_db($dbname,$this->dbLink[$this->dbR]) or self::showMsg("Can't use foo ");
    }

    /**
     * ��ȡ��¼����,����¼��Ϊһ���ֶ�ʱ���������� ����¼��Ϊ����ֶ�ʱ���һά����������
     *
     * @param  string  $sql ��׼��ѯSQL���
     * @param  integer $r   ���ӱ�ʶ
     * @return string|array
    **/
    public function get($sql,$r=null)
    {
        $rs=self::fetch(self::query($sql,$r));
        $rs && count($rs)==1 && $rs=join(',',$rs);
        return $rs;
    }

    /**
     * ���ز�ѯ��¼����������
     *
     * @param  string  $sql ��׼SQL���
     * @param  integer $r ���ӱ�ʶ
     * @return array
    **/
    public function getall($sql,$r=null)
    {
        $item=array();
        $q=self::query($sql,$r);
        while($rs=self::fetch($q)) $item[]=$rs;
        return $item;
    }

    /**
     * ��ȡ���������ID
     *
     * @return integer
    **/
    public function getId()
    {
        return mysql_insert_id();
    }

    /**
     * ���Ͳ�ѯ
     *
     * @param  string  $sql ��׼SQL���
     * @param  integer $r   ���ӱ�ʶ
     * @return resource
    **/
    public function query($sql,$r=null)
    {
        $r=isset($r) ? $r : $this->dbR;
        if(empty($this->cfg['debug'])){
            $q=mysql_query($sql,$this->dbLink[$r]) or self::showMsg("Query to [{$sql}] ");
        }else{
            $stime = $etime = 0;
            $m = explode(' ', microtime());
            $stime = number_format(($m[1] + $m[0] - $_SERVER['REQUEST_TIME']), 8) * 1000;
            $q=mysql_query($sql,$this->dbLink[$r]) or self::showMsg("Query to [{$sql}] ");

            $m = explode(' ', microtime());
            $etime = number_format(($m[1] + $m[0] - $_SERVER['REQUEST_TIME']), 8) * 1000;
            $sqltime = round(($etime - $stime), 5);

            $explain = array();
            $info = mysql_info();
            if($q && preg_match("/^(select )/i", $sql)) {
                $qs=mysql_query('EXPLAIN '.$sql, $this->dbLink[$r]);
                while($rs=self::fetch($qs)){
                    $explain[] = $rs;
                }
            }
            $this->DB_debug[] = array('sql'=>$sql, 'time'=>$sqltime, 'info'=>$info, 'explain'=>$explain);


        }
        return $q;
    }

    /**
     * �����ֶ���Ϊ���������鼯��
     *
     * @param  results $q ��ѯָ��
     * @return array
    **/
    public function fetch($q)
    {
        return mysql_fetch_assoc($q);
    }

    /**
     * ��ʽ��MYSQL��ѯ�ַ���
     *
     * @param  string $str ��������ַ���
     * @return string
    **/
    public function escape($str)
    {
        return mysql_escape_string($str);
    }

    /**
     * �رյ�ǰ���ݿ�����
     * ע��: ������lock������,�����ر�
     *
     * @param  string $str ��������ַ���
     * @return string
    **/
    public function close()
    {
        if(empty($this->dbLink[$this->dbR]['lock'])){
            mysql_close($this->dbLink[$this->dbR]);
            unset($this->dbLink[$this->dbR]);
            $this->dbR=(int)key($this->dbLink);
        }
    }

    /**
     * ȡ�����ݿ������б�����
     *
     * @param  string $db ���ݿ���,Ĭ��Ϊ��ǰ���ݿ�
     * @return array
    **/
    public function getTB($db=NULL)
    {
        $item=array();
        $q=self::query('SHOW TABLES '.(empty($db) ? null : 'FROM '.$db));
        while($rs=self::fetchs($q)) $item[]=$rs[0];
        return $item;
    }

    /**
     * ������֪�ı���һ���±�,��������IDʱ����ID����Ϊ��
     * ע��: �����Ʊ�ṹ������������,�������Ƽ�¼
     *
     * @param  string  $souTable Դ����
     * @param  string  $temTable Ŀ�����
     * @param  boolean $isdel    �Ƿ��ڴ���ǰ��鲢ɾ��Ŀ���
     * @return boolean
    **/
    public function copyTB($souTable,$temTable,$isdel=false)
    {
        $isdel && self::query("DROP TABLE IF EXISTS `{$temTable}`");//����������ֱ��ɾ��
        $temTable_sql=self::sqlTB($souTable);
        $temTable_sql=str_replace('CREATE TABLE `'.$souTable.'`','CREATE TABLE IF NOT EXISTS `'.$temTable.'`',$temTable_sql);

        $temTable_sql=iconv($this->dbLang,'utf-8',$temTable_sql);

        $result=self::query($temTable_sql);//�������Ʊ�
        stripos($temTable_sql,'AUTO_INCREMENT') && self::query("ALTER TABLE `{$temTable}` AUTO_INCREMENT =1");//���¸��Ʊ�����ID
        return $result;
    }

    /**
     * ��ȡ���������ֶμ�����
     *
     * @param  string $tb ����
     * @return array
    **/
    public function getFD($tb)
    {
        $item=array();
        $q=self::query("SHOW FULL FIELDS FROM {$tb}");//DESCRIBE users
        while($rs=self::fetch($q)) $item[]=$rs;
        return $item;
    }

    /**
     * ���ɱ�ı�׼Create����SQL���
     *
     * @param  string $tb ����
     * @return string
    **/
    public function sqlTB($tb)
    {
        $q=self::query("SHOW CREATE TABLE {$tb}");
        $rs=self::fetchs($q);
        return $rs[1];
    }

    /**
     * ����������ɾ��
     *
     * @param  string $tables ������
     * @return boolean
    **/
    public function delTB($tables)
    {
        return self::query("DROP TABLE IF EXISTS `{$tables}`");
    }

    /**
     * �����Ż���
     * ע��: �������ö���������д���
     *
     * Example: setTB('table0','table1','tables2',...)
     * @param string �����ƿ����Ƕ��
     * @return boolean
    **/
    public function setTB()
    {
        $args=func_get_args();
        foreach($args as &$v) self::query("OPTIMIZE TABLE {$v};");
    }

    /**
     * ����REPLACE|UPDATE|INSERT�ȱ�׼SQL���
     *
     * @param  string $arr    �������ݿ������Դ
     * @param  string $dbname ���ݱ���
     * @param  string $type   SQL���� UPDATE|INSERT|REPLACE|IFUPDATE
     * @param  string $where  where����
     * @return string         һ����׼��SQL���
    **/
    public function subSQL($arr,$dbname,$type='update',$where=NULL)
    {
        $tem=array();
        foreach($arr as $k=>$v) $tem[$k]="`{$k}`='{$v}'";
        switch(strtolower($type)){
            case 'insert'://����
                $sql="INSERT INTO {$dbname} SET ".join(',',$tem);
                break;
            case 'replace'://�滻
                $sql="REPLACE INTO {$dbname} SET ".join(',',$tem);
                break;
            case 'update'://����
                $sql="UPDATE {$dbname} SET ".join(',',$tem)." WHERE {$where}";
                break;
            case 'ifupdate'://��������¼�¼
                $tem=join(',',$tem);
                $sql="INSERT INTO {$dbname} SET {$tem} ON DUPLICATE KEY UPDATE {$tem}";
                break;
            default:
                $sql=null;
                break;
        }
        return $sql;
    }

    /**
     * ����REPLACE|UPDATE|INSERT�ȱ�׼SQL��� ͬsubsql�������Ƶ��ú�����ֱ��ִ�в�����SQL
     *
     * @param  string $arr �������ݿ������Դ
     * @param  string $dbname ���ݱ���
     * @param  string $type SQL���� UPDATE|INSERT|REPLACE|IFUPDATE
     * @param  string $where where����
     * @return boolean
    **/
    public function doQuery($arr,$dbname,$type='update',$where=NULL)
    {
        $sql=self::subSQL($arr,$dbname,$type,$where);
        return self::query($sql);
    }

    /**
     * ���ؼ���Ϊ���е����鼯��
     *
     * @param  resource $query ��Դ��ʶָ��
     * @return array
    **/
    public function fetchs($query)
    {
        return mysql_fetch_row($query);
    }

    /**
     * ȡ�ý�������е���Ŀ
     *
     * @param  resource $query ��Դ��ʶָ��
     * @return array
    **/
    public function reRows($query)
    {
        return mysql_num_rows($query);
    }

    /**
     * ȡ�ñ�INSERT��UPDATE��DELETE��ѯ��Ӱ��ļ�¼����
     *
     * @return int
    **/
    public function afrows($r=null)
    {
        $r=isset($r) ? $r : $this->dbR;
        return mysql_affected_rows($this->dbLink[$r]);
    }

    /**
     * �ͷŽ��������
     *
     * @param  resource $query ��Դ��ʶָ��
     * @return boolean
    **/
    public function refree($query)
    {
        return @mysql_free_result($query);
    }

    /**
     * �����쳣��Ϣ ����ͨ��try���в�׽����Ϣ
     *
     * @param  resource $query ��Դ��ʶָ��
     * @return boolean
    **/
    public function showMsg($str)
    {
        if($this->dbError) throw new Fend_Exception($str.mysql_error());
    }
}
?>