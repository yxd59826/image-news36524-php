<?php
namespace app\admin\controller;
use app\common\controller\Adminbase;
use think\Request;
use app\admin\model\Advers as adversModel;

class Advers extends Adminbase
{
    /**
     * Notes   : 首页
     * Created by yxd
     * DateTime: 2019/9/3 13:07
     */
	public function index(){
		return view();
	}

    /**
     * Notes   :获取数据
     * Created by yxd
     * DateTime: 2019/9/3 13:07
     */
	public function getData(Request $request){
        if($request->isGet()){
            $params = $request->param();
            $page = $params['page'];
            $start = ($page-1)*$params['limit'];
            $data = adversModel::order('id','desc')->limit($start,$params['limit'])->select();
            $count = adversModel::count();
            return json(['code'=>0,'msg'=>'','count'=>$count,'data'=>$data]);
        }
    }

    public function listform(){
	    return view();
    }
    public function adveradd(Request $request){
	    if($request->isPost()){
            $params = $request->param();
            $data = $params['data'];
            unset($data['image']);

            try {
                $res = adversModel::create($data);
                if($res){
                    return json(['code'=>200,'msg'=>'添加成功']);
                }
            } catch (ValidateException $e) {
                // 这是进行验证异常捕获
                return json(['code'=>201,'msg'=>$e->getError()]);
            } catch (\Exception $e) {
                // 这是进行异常捕获
                return json(['code'=>201,'msg'=>$e->getMessage()]);
            }
        }

    }
    public function imgup(Request $request){
        $file = $request->file('image');
        if($file){
            $info = $file->validate(['size'=>9004800,'ext'=>'jpg,png,gif,jpeg'])->move('advers');
            if($info){
                $src = '/advers/'.$info->getSaveName();
                return json(['code'=>0,'msg'=>'上传成功','data'=>$src]);
            }else{
                // 上传失败获取错误信息
                $error = $file->getError();
                return json(['code'=>2,'msg'=>'上传失败','data'=>$error]);
            }
        }
    }

    public function adversdel(Request $request){
	    if($request->isPost()){
            $params = $request->param();
            $data = $params['data'];
            foreach ($data as $key=>$value){
                $res = adversModel::destroy($value['id']);
            }
            return json(['code'=>200,'msg'=>'删除成功']);

        }


    }
}