<?php
namespace app\index\controller;

use think\Controller;
use think\Loader;
use think\Request;
use app\common\controller\IndexBase;
use app\admin\model\Images as imagesModel;

class Index extends IndexBase
{
    public function index()
    {	
    	$data = model('news')->order('update_time','desc')->paginate(20);
        $page = $data->render();
        $goods = model('news')->where('position',1)->order('create_time','desc')->limit(4)->select();
        foreach ($data as $key => $value) {
            $data[$key]['catname'] = model('catgory')->where('id',$value['catid'])->value('name');
        }
        
        $this->assign('page', $page);
    	$this->assign('news',$data);
        $this->assign('goods',$goods);
        return view();
    }

    public function content(){
    	$id = input('id');
        $data = [];
    	if($id){
    		$data = model('news')->where('id',$id)->find();
            $views = $data['views'] + 1;
            model('News')->where('id',$id)->update(['views'=>$views]);    		
    	}
        $this->assign('data',$data);
        if($data['catid']==1){
            return view('blogcontent');
        }else{
            return view();
        }    	
    }

    public function cats(){
        $catid = input('id');
        $data = model('news')->order('create_time','desc')->where('catid',$catid)->paginate(20);
        $page = $data->render();
        $catname = model('catgory')->where('id',$catid)->value('name');
        foreach ($data as $key => $value) {
            $data[$key]['catname'] = model('catgory')->where('id',$value['catid'])->value('name');
        }
        $this->assign('page', $page);
        $this->assign('catname',$catname);
        $this->assign('currid',$catid);
        $this->assign('news',$data);
        return view();
    }

    public function index2(){
        return view();
    }

    public function vip(Request $request){
        if($request->isPost()){
            $params = $request->param();
            //https://www.administrator5.com/admin.php?url=
            // dump($params['url']);
            $this->redirect('https://www.administrator5.com/admin.php?url='.$params['url']);
        }
        return view();
    }
}
