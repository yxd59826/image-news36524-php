<?php
namespace app\admin\controller;

use app\common\controller\Adminbase;
use app\admin\model\Admin;

class Auths extends Adminbase
{
	public function users(){		
        return view();
	}

    public function userslist(){
        if(request()->isPost()){
            $params = request()->param();
            $map=[];
            $page = $params['page'];
            $start = ($page-1)*$params['limit'];
            if(isset($params['username'])){
                $map[] = ['username','like',"%".$params['username']."%"];
            }
            $adminModel = new Admin();
            $list = $adminModel->where($map)->limit($start,$params['limit'])->select();
            $count = $adminModel->where($map)->count();
            foreach ($list as $key => $value) {
                $list[$key]['group'] = model('role')->where('id',$value['groupid'])->value('name');
            }
            return json(['code'=>0,'msg'=>'','count'=>$count,'data'=>$list]);
        }
    }

    public function adminform(){
        return view();
    }
}