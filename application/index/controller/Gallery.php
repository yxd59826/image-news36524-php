<?php
namespace app\index\controller;

use think\Controller;
use think\Loader;
use think\Request;
use app\common\controller\IndexBase;
use app\admin\model\Images as imagesModel;
use think\facade\Debug;
use think\facade\Log;

class Gallery extends IndexBase
{
	public function index(){
		$imagesModel = new imagesModel();
        $data = $imagesModel::order('id desc')->limit(20)->select();
        $this->assign('lists',$data);
        return view();
	}

	public function photos(Request $request){
		$params = $request->param();
		$id = $params['id'];
		$imagesModel = new imagesModel();
		$data = $imagesModel::where('id',$id)->find();
		$photos = json_decode($data['pics'],true);		
		$this->assign('photos',$photos);
		$this->assign('data',$data);
		return view();
	}

	public function ptest(){
		$imagesModel = new imagesModel();
		$data = $imagesModel::order('id desc')->select();
		foreach ($data as $key => $value) {
			$imgs = json_decode($value['pics'],true);
			$res = $imagesModel->where('id',$value['id'])->update(['thumb'=>$imgs[0]]);
			dump($res);
		}
	}

	public function getlist(){
		header('Access-Control-Allow-Origin: *');  
		$request = request();
        $params = $request->param();
        if($params){
        	Debug::remark('begin');
            $page = $params['page'];
            $start = ($page-1)*20;
            $imagesModel = new imagesModel();
            $data = $imagesModel->field('thumb,id,title')->order('id','desc')->limit($start,20)->select();
            $xdata = [];
            foreach ($data as $key => $value) {
            	$xdata[$key]['src'] = "https://image.news36524.com".$value['thumb'];
            	$xdata[$key]['href'] = "https://image.news36524.com/photos/".$value['id'];
            	$xdata[$key]['info'] = $value['title'];
            }
            $count = $imagesModel->count();
            Debug::remark('end');
            Log::info(Debug::getRangeTime('begin','end').'s');
            return json(['code'=>0,'data'=>$xdata,'pages'=>ceil($count/20)]);
        }
	}
}