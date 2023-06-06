<?php

namespace itbbn\admin\model;

class Base extends \think\Model
{
    public static function returnFormat($code = 0, $msg = "", $data = [])
    {
        $res['code'] = $code;
        $res['data'] = $data;
        $res['msg'] = $msg;
        return $res;
    }

}