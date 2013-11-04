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
 * @version $Id: Mysqli.php 4 2011-12-29 11:01:08Z gimoo $
**/

class Fend_Db_Mysqli extends Fend
{
    protected $_db=null;//���ӳر�ʶ
    protected $_cfg=null;//����������Ϣ
    public $dbError=false;//�Ƿ����쳣�׳�

    /**
     * �������ݿⲢ�����ʶ,ʵ�ֶ�����Ӳ��л�
     *
     * @param integer $r ���ӱ�ʶ�� $_dbcfg�����е�Key�仯���仯
    **/
    public function getConn($r)
    {
        $this->_cfg=&$this->dbcfg[$r];
        !isset($this->_cfg['port']) && $this->_cfg['port']=3306;
        $this->_db=new mysqli($this->_cfg['host'],$this->_cfg['user'],$this->_cfg['pwd'],$this->_cfg['name'],$this->_cfg['port']);

        if (mysqli_connect_errno()) {
            self::showMsg("Connect failed: DBA\n");
        }

        $this->_db->query("SET character_set_connection={$this->_cfg['lang']},character_set_results={$this->_cfg['lang']},character_set_client=binary,sql_mode='';");
    }

    /**
     * ѡ�в������ݿ�
     *
     * @param string $name ����ѡ�����ݿ�,Ϊ��ʱѡ��Ĭ�����ݿ�
    **/
    public function useDb($name=null)
    {
        null===$name && $name=$this->_cfg['name'];
        $this->_db->select_db($name) or self::showMsg("Can't use foo");
    }

    /**
     * ��ȡ��¼����,����¼��Ϊһ���ֶ�ʱ���������� ����¼��Ϊ����ֶ�ʱ���һά����������
     *
     * @param  string  $sql ��׼��ѯSQL���
     * @param  integer $r   �Ƿ�ϲ�����
     * @return string|array
    **/
    public function get($sql,$r=null)
    {
        $rs=self::fetch(self::query($sql,$r));
        null!==$r AND $rs=join(',',$rs);
        return $rs;
    }

    /**
     * ���ز�ѯ��¼����������
     *
     * @param  string  $sql ��׼SQL���
     * @return array
    **/
    public function getall($sql)
    {
        $item=array();
        $q=self::query($sql);
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
        return $this->_db->insert_id;
    }

    /**
     * ���Ͳ�ѯ
     *
     * @param  string  $sql ��׼SQL���
     * @param  integer $r   ���ӱ�ʶ
     * @return resource
    **/
    public function query($sql)
    {
        if(empty($this->cfg['debug'])){
            $q=$this->_db->query($sql) or self::showMsg("Query to [{$sql}] ");
        }else{
            $stime = $etime = 0;
            $m = explode(' ', microtime());
            $stime = number_format(($m[1] + $m[0] - $_SERVER['REQUEST_TIME']), 8) * 1000;
            $q=$this->_db->query($sql) or self::showMsg("Query to [{$sql}] ");

            $m = explode(' ', microtime());
            $etime = number_format(($m[1] + $m[0] - $_SERVER['REQUEST_TIME']), 8) * 1000;
            $sqltime = round(($etime - $stime), 5);

            $explain = array();
            $info = $this->_db->info;
            if($q && preg_match("/^(select )/i", $sql)) {
                $qs=$this->_db->query('EXPLAIN '.$sql);
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
        return $q->fetch_assoc();
    }

    /**
     * ��ʽ��MYSQL��ѯ�ַ���
     *
     * @param  string $str ��������ַ���
     * @return string
    **/
    public function escape($str)
    {
        return $this->_db->real_escape_string($str);
    }

    /**
     * �رյ�ǰ���ݿ�����
     * ע��: ������lock������,�����ر�
     *
     * @return bool
    **/
    public function close()
    {
        return $this->_db->close();
    }

    /**
     * ȡ�����ݿ������б�����
     *
     * @param  string $db ���ݿ���,Ĭ��Ϊ��ǰ���ݿ�
     * @return array
    **/
    public function getTB($db=null)
    {
        $item=array();
        $q=self::query('SHOW TABLES '.(null==$db ? null : 'FROM '.$db));
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

        $this->_cfg['lang']!='utf-8' && $temTable_sql=iconv($this->_cfg['lang'],'utf-8',$temTable_sql);

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
    public function fetchs($q)
    {
        return $q->fetch_row();
    }

    /**
     * ȡ�ý�������е���Ŀ
     *
     * @param  resource $query ��Դ��ʶָ��
     * @return array
    **/
    public function reRows($q)
    {
        return $q->num_rows;
    }

    /**
     * ȡ�ñ�INSERT��UPDATE��DELETE��ѯ��Ӱ��ļ�¼����
     *
     * @return int
    **/
    public function afrows()
    {
        return $this->_db->affected_rows;
    }

    /**
     * �ͷŽ��������
     *
     * @param  resource $query ��Դ��ʶָ��
     * @return boolean
    **/
    public function refree($q)
    {
        return $q->free_result();
    }

    /**
     * �����쳣��Ϣ ����ͨ��try���в�׽����Ϣ
     *
     * @param  resource $query ��Դ��ʶָ��
     * @return boolean
    **/
    public function showMsg($str)
    {
        if($this->dbError) throw new Fend_Exception($str.$this->_db->error);
    }
}
?>