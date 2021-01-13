<?php
namespace app\index\model;

use think\Model;

class NewsData extends Model
{
    protected $autoWriteTimestamp = true;

    public function profile()
    {
        return $this->hasOne('News','id');
    }
}