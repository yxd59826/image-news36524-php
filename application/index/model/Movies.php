<?php
namespace app\index\model;

use think\Model;

class Movies extends Model
{
    protected $type = [

        'create_time' => 'string',
        'update_time' => 'string',
        
    ];

    public function getCreateTimeAttr($value)
    {
        return date('Y-m-d H:i:s',$value);
    }

    public function getUpdateTimeAttr($value)
    {
        return date('Y-m-d H:i:s',$value);
    }
}