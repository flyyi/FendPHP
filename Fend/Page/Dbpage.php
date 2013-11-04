<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://fend.gimoo.net)
 *
 * ��ҳ����
 * �����ڶ�̬URL��ҳ ���õ�ǰ��������Ϊ��ҳ��ʶ
 *
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Dbpage.php 4 2011-12-29 11:01:08Z gimoo $
**/

class Fend_Page_Dbpage extends Fend
{
    public $pagesum=0;//ȡ�ò�ѯ�����ҳ��
    public $psize=20;//ÿҳ��¼��
    public $total=0;//��ѯ������
    public $query=null;//���ز�ѯָ��
    public $sql=null;//��ѯ���

    /**
     * ��ȡ��ҳ����
     * @param integer $psize ÿҳ��ʾ��
     * @param string  $pname ��ҳ��ʶ
     * @param integer $pin   ��������
     * @return string
    **/
    public function getPage($psize=20,$pname='pg',$pin=10)
    {
        !empty($this->sql) && $this->getSum($this->sql,$this->total);//��ѯ����Ŀ
        $psum=&$this->total;
        $psize<=0 && $psize=20;

        //�õ���ǰ��ҳ�α�-�����㵱ǰҳ��
        $cpage=(int)@$_GET[$pname];
        $cpage=$cpage<=0 ? 1 : ceil($cpage/$psize);

        //��ҳ��
        $total=ceil($psum/$psize);
        $cpage>$total && $cpage=$total;//��ǰҳ������ܳ�����ҳ��
        $cpage<=0 && $cpage=1;

        //�õ���ѯSQL
        if(!empty($this->sql)){
            $this->sql=$this->sql.' LIMIT '.(($cpage-1)*$psize).','.$psize;
            $this->query=$this->db->query($this->sql);
        }else{
            $this->sql=' LIMIT '.(($cpage-1)*$psize).','.$psize;
        }

        $stem=null;
        if($total<=1 || $psum<=$psize) return $stem;//��ҳ����С�ڷ�ҳ����

        //ȡ��URL QUERY_STRING ���в���������Ϊ���鼯��
        $url_param=@$_SERVER['QUERY_STRING'];
        parse_str($url_param,$url_param);

        //�ֲ���ʾ��ʽ
        $txpg=empty($this->cfg['sys_pgtx'])?'&lt;&lt;':$this->cfg['sys_pgtx'];
        $nxpg=empty($this->cfg['sys_pgnx'])?'&gt;&gt;':$this->cfg['sys_pgnx'];

        if($total<=($pin+2)){ //����������-ֱ����ʾ���з�ҳ
            $stem.=' <a'.(($cpage-1)>0 ? ' href="?'.self::getParam($url_param,$pname,$psize,$cpage-1).'"' : '').">{$txpg}</a>";
            for($i=1;$i<=$total;$i++){
                $stem.=($i==$cpage) ? " <b>{$i}</b>" : ' <a href="?'.self::getParam($url_param,$pname,$psize,$i)."\">{$i}</a>" ;
            }
            $stem.=' <a'.(($cpage+1)<=$total ? ' href="?'.self::getParam($url_param,$pname,$psize,$cpage+1).'"' : '').">{$nxpg}</a>";

        }elseif($cpage<=($pin-2)){//β������
            $stem.=' <a'.(($cpage-1)>0 ? ' href="?'.self::getParam($url_param,$pname,$psize,$cpage-1).'"' : '').">{$txpg}</a>";
            for($i=1;$i<=$pin;$i++){
                $stem.=($i==$cpage) ? ' <b>'.$i.'</b>' : ' <a href="?'.self::getParam($url_param,$pname,$psize,$i).'" >'.$i.'</a>';
            }
            $stem.=' <span>...</span>';
            $stem.=' <a href="?'.self::getParam($url_param,$pname,$psize,$total-1).'" >'.($total-1).'</a>';
            $stem.=' <a href="?'.self::getParam($url_param,$pname,$psize,$total).'" >'.$total.'</a>';
            $stem.=' <a'.(($cpage+1)<=$total ? ' href="?'.self::getParam($url_param,$pname,$psize,$cpage+1).'"' : '').">{$nxpg}</a>";

        }elseif($cpage>2 && $cpage<($total-$pin+3)){//��β˫������
            $stem.=' <a'.(($cpage-1)>0 ? ' href="?'.self::getParam($url_param,$pname,$psize,$cpage-1).'"' : '').">{$txpg}</a>";
            $stem.=' <a href="?'.self::getParam($url_param,$pname,$psize,1).'" >1</a>';
            $stem.=' <a href="?'.self::getParam($url_param,$pname,$psize,2).'" >2</a>';
            $stem.=' <span>...</span>';
            for($i=($cpage-(ceil($pin/2)-1));$i<=($cpage+(ceil($pin/2)-2));$i++){
                $stem.=($i==$cpage) ? ' <b>'.$i.'</b>' : ' <a href="?'.self::getParam($url_param,$pname,$psize,$i).'" >'.$i.'</a>';
            }
            $stem.=' <span>...</span>';
            $stem.=' <a href="?'.self::getParam($url_param,$pname,$psize,$total-1).'" >'.($total-1).'</a>';
            $stem.=' <a href="?'.self::getParam($url_param,$pname,$psize,$total).'" >'.$total.'</a>';
            $stem.=' <a'.(($cpage+1)<=$total ? ' href="?'.self::getParam($url_param,$pname,$psize,$cpage+1).'"' : '').">{$nxpg}</a>";

        }else{//�ײ�����
            $stem.=' <a'.(($cpage-1)>0 ? ' href="?'.self::getParam($url_param,$pname,$psize,$cpage-1).'"' : '').">{$txpg}</a>";
            $stem.=' <a href="?'.self::getParam($url_param,$pname,$psize,1).'" >1</a>';
            $stem.=' <a href="?'.self::getParam($url_param,$pname,$psize,2).'" >2</a>';
            $stem.=' <span>...</span>';
            for($i=($total-$pin+1);$i<=$total;$i++){
                $stem.=($i==$cpage) ? ' <b>'.$i.'</b>' : ' <a href="?'.self::getParam($url_param,$pname,$psize,$i).'" >'.$i.'</a>';
            }
            $stem.=' <a'.(($cpage+1)<=$total ? ' href="?'.self::getParam($url_param,$pname,$psize,$cpage+1).'"' : '').">{$nxpg}</a>";
        }
        return $stem;
    }

    /**
     * ȡ������
     * @param string $sql ��׼��ѯSQL���
     * @return void
    **/
    private function getSum($sql,&$total)
    {
        $tsum=0;
        $l=stripos($this->sql,' from ');$r=strripos($sql,' order by ');
        $coun=$this->db->query('SELECT COUNT(*)'.($r<=0 ? substr($sql,$l) : substr($sql,$l,$r-$l)));
        //$coun=$this->db->query(substr_replace($sql,'SELECT COUNT(*)',0,stripos($sql,' from ')));
        while($rs=$this->db->fetchs($coun)) $tsum+=$rs[0];
        $total=$total>0 ? min($total,$tsum) : $tsum;
    }

    /**
     * ȡ�÷�ҳURL����
     * @param string $url_param URLparam
     * @param string  $pname    ��ҳ��ʶ
     * @param integer $psize    ÿҳ��ʾ��
     * @param integer $i        ��������
     * @return string
    **/
    private function getParam(&$url_param,&$pname,&$psize,$i)
    {
        $url_param[$pname]=$i*$psize;
        return http_build_query($url_param);
    }
}

/***************************

���÷�ʽ1
$Pg=Fend_Page_Init::Factory(1);
$Pg->sql="SELECT * FROM site_info {$sql}";
$tmy['bypage']=$Pg->getPage(20,'pg');
while($rs=$this->db->fetch($Pg->query)){
    $tmy['bylist'][]=$rs;
}
$tmy['bytotal']=$Pg->total;


���÷�ʽ2
$Pg=Fend_Page_Init::Factory(1);
$Pg->total=500;
$page=$Pg->getPage(20,'pg');
$Pg->sql;//��ȡLIMIT��ҳ��ѯ

***************************/

?>