<?php
namespace app\common\controller;

use think\Controller;
use app\admin\model\Advers;
use app\admin\model\Links;

class IndexBase extends Controller 
{
	public function initialize()
    {
        $catid = input('id');
        $currid = $catid?$catid:"";
        //广告调用
        $advers = new Advers();
        $linksModel = new Links();
        $adverdata = $advers::order('id','desc')->find();
        //热榜
        $hotdata = model('news')->whereTime('create_time','month')->order('views','desc')->limit(10)->select();

    	$this->assign('currid',$catid);
		$cats = model('catgory')->where(['isshow'=>1,'status'=>1])->order('sort','asc')->select();
        $links = $linksModel->order('id','desc')->limit(10)->select();
        $this->assign('links',$links);
		$this->assign('currid',$catid);
        $this->assign('cats',$cats);
        $this->assign('ads',$adverdata);
        $this->assign('hots',$hotdata);
    }

    //空操作
    public function _empty(){
        return $this->error('空操作，返回上次访问页面中...');
    }
    
} 
