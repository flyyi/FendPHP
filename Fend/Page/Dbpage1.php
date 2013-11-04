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
 * @version $Id: Dbpage1.php 4 2011-12-29 11:01:08Z gimoo $
**/

class Fend_Page_Dbpage1 extends Fend
{
    public $psum=0;//��ҳ��
    public $psize=20;//ÿҳ��¼��
    public $total=0;//�ܼ�¼
    public $query=null;//��ѯSQLָ��
    public $sql=null;//��ѯSQL���
    public $preg='[*]';//�滻ĸ��

    /**
     * ��ȡ��ҳ����
     * @param integer $_url   URLת����ַ
     * @param string  $_cpage ��ǰҳ��
     * @param integer $_pin   ��������
     * @return string
    **/
    public function getPage($_url,$_cpage,$_pin=10)
    {
        !empty($this->sql) && $this->getSum($this->sql,$this->total);//��ѯ����Ŀ
        $this->psum=$this->total<=$this->psize ? 1 : ceil($this->total/$this->psize);//��ҳ��
        $_cpage=$_cpage<=1 ? 1 : min($this->psum,$_cpage);//��ǰҳ��

        //��ҳ��
        $_psum=&$this->psum;
        $_cpage>$_psum && $_cpage=$_psum;//��ǰҳ������ܳ�����ҳ��
        $_cpage<=0 && $_cpage=1;

        //�õ���ѯSQL
        if(!empty($this->sql)){
            $this->sql=$this->sql.' LIMIT '.(($_cpage-1)*$this->psize).','.$this->psize;
            $this->query=$this->db->query($this->sql);
        }else{
            $this->sql=' LIMIT '.(($_cpage-1)*$this->psize).','.$this->psize;
        }

        $stem=null;
        if($_psum<=1) return $stem;//��ҳ����С�ڷ�ҳ����

        //�ֲ���ʾ��ʽ
        $txpg=empty($this->cfg['sys_pgtx'])?'&lt;&lt;':$this->cfg['sys_pgtx'];
        $nxpg=empty($this->cfg['sys_pgnx'])?'&gt;&gt;':$this->cfg['sys_pgnx'];

        if($_psum<=($_pin+2)){ //����������-ֱ����ʾ���з�ҳ
            $stem.=' <a'.(($_cpage-1)>0 ? ' href="'.str_replace($this->preg,$_cpage-1,$_url).'"' : '').">{$txpg}</a>";
            for($i=1;$i<=$_psum;$i++){
                $stem.=($i==$_cpage) ? " <b>{$i}</b>" : ' <a href="'.str_replace($this->preg,$i,$_url)."\">{$i}</a>" ;
            }
            $stem.=' <a'.(($_cpage+1)<=$_psum ? ' href="'.str_replace($this->preg,$_cpage+1,$_url).'"' : '').">{$nxpg}</a>";

        }elseif($_cpage<=($_pin-2)){//β������
            $stem.=' <a'.(($_cpage-1)>0 ? ' href="'.str_replace($this->preg,$_cpage-1,$_url).'"' : '').">{$txpg}</a>";
            for($i=1;$i<=$_pin;$i++){
                $stem.=($i==$_cpage) ? ' <b>'.$i.'</b>' : ' <a href="'.str_replace($this->preg,$i,$_url).'" >'.$i.'</a>';
            }
            $stem.=' <span>...</span>';
            $stem.=' <a href="'.str_replace($this->preg,$_psum-1,$_url).'" >'.($_psum-1).'</a>';
            $stem.=' <a href="'.str_replace($this->preg,$_psum,$_url).'" >'.$_psum.'</a>';
            $stem.=' <a'.(($_cpage+1)<=$_psum ? ' href="'.str_replace($this->preg,$_cpage+1,$_url).'"' : '').">{$nxpg}</a>";

        }elseif($_cpage>2 && $_cpage<($_psum-$_pin+3)){//��β˫������
            $stem.=' <a'.(($_cpage-1)>0 ? ' href="'.str_replace($this->preg,$_cpage-1,$_url).'"' : '').">{$txpg}</a>";
            $stem.=' <a href="'.str_replace($this->preg,1,$_url).'" >1</a>';
            $stem.=' <a href="'.str_replace($this->preg,2,$_url).'" >2</a>';
            $stem.=' <span>...</span>';
            for($i=($_cpage-(ceil($_pin/2)-1));$i<=($_cpage+(ceil($_pin/2)-2));$i++){
                $stem.=($i==$_cpage) ? ' <b>'.$i.'</b>' : ' <a href="'.str_replace($this->preg,$i,$_url).'" >'.$i.'</a>';
            }
            $stem.=' <span>...</span>';
            $stem.=' <a href="'.str_replace($this->preg,$_psum-1,$_url).'" >'.($_psum-1).'</a>';
            $stem.=' <a href="'.str_replace($this->preg,$_psum,$_url).'" >'.$_psum.'</a>';
            $stem.=' <a'.(($_cpage+1)<=$_psum ? ' href="'.str_replace($this->preg,$_cpage+1,$_url).'"' : '').">{$nxpg}</a>";

        }else{//�ײ�����
            $stem.=' <a'.(($_cpage-1)>0 ? ' href="'.str_replace($this->preg,$_cpage-1,$_url).'"' : '').">{$txpg}</a>";
            $stem.=' <a href="'.str_replace($this->preg,1,$_url).'" >1</a>';
            $stem.=' <a href="'.str_replace($this->preg,2,$_url).'" >2</a>';
            $stem.=' <span>...</span>';
            for($i=($_psum-$_pin+1);$i<=$_psum;$i++){
                $stem.=($i==$_cpage) ? ' <b>'.$i.'</b>' : ' <a href="'.str_replace($this->preg,$i,$_url).'" >'.$i.'</a>';
            }
            $stem.=' <a'.(($_cpage+1)<=$_psum ? ' href="'.str_replace($this->preg,$_cpage+1,$_url).'"' : '').">{$nxpg}</a>";

        }
        return $stem;
    }

    /**
     * ȡ������
     * @param string $sql ��׼��ѯSQL���
     * @return string
    **/
    private function getSum($sql,&$total)
    {
        $tsum=0;
        $coun=$this->db->query(substr_replace($sql,'SELECT COUNT(*)',0,stripos($sql,' from ')));
        while($rs=$this->db->fetchs($coun)) $tsum+=$rs[0];
        $total=$total>0 ? min($total,$tsum) : $tsum;
    }

}

?>