<?php

namespace itbbn\admin\model;

use think\facade\Db;

class Admin extends Base
{

    /**
     * 关联角色
     * @return \think\model\relation\HasOne
     */
    public function role()
    {
        return $this->hasOne(Role::class, "id", "role_id");
    }

    /**
     * 修改管理员信息
     * @param $id
     * @param $phone
     * @param $roleId
     * @param $valid
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function edit($id, $phone, $roleId, $valid)
    {
        $admin = Admin::find($id);
        if (!$admin) {
            return self::returnFormat(999, "记录未找到");
        }
        $wherePhone = [];
        $wherePhone[] = ["phone", "=", $phone];
        $wherePhone[] = ["id", "<>", $id];
        $exit = Admin::where($wherePhone)->find();
        if ($exit) {
            return self::returnFormat(999, "该手机号已被其它管理员绑定，请更换手机号");
        }
        $admin->phone = $phone;
        $admin->role_id = $roleId;
        $admin->valid = $valid;
        $res = $admin->save();
        if ($res === false) {
            return self::returnFormat(999, "提交失败：数据库写入失败");
        }
        return self::returnFormat(0, "", $admin);
    }

    public static function resetPwd($id, $password)
    {
        $admin = Admin::find($id);
        if (!$admin) {
            return self::returnFormat(999, "记录未找到");
        }
        $admin->password = self::md5($admin->salt, $password);
        $res = $admin->save();
        if ($res === false) {
            return self::returnFormat(999, "提交失败：数据库写入失败");
        }
        return self::returnFormat(0, "", $admin);
    }

    /**
     * 删除数据
     * @param $ids
     * @return array|mixed
     */
    public static function del($ids)
    {
        $whereDelete = [];
        $whereDelete[] = ["id", "in", $ids];
        $updateData = [
            "delete_time" => getNow(),
        ];
//        Log::record("whereDelete".print_r($whereDelete,true),"debug");
//        Log::record("updateData".print_r($updateData,true),"debug");
        $res = (new Admin())->where($whereDelete)->update($updateData);
        if ($res === false) {
            return self::returnFormat(999, "删除失败：数据库写入失败");
        }
        return self::returnFormat(0, "", $res);
    }

    /**
     * 添加管理员
     * @param $name
     * @param $password
     * @param $phone
     * @param $roleId
     * @param $valid
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function add($name, $password, $phone, $roleId, $valid)
    {

        $admin = Admin::where("name", $name)->find();
        if ($admin) {
            return self::returnFormat(999, "账号已存在，请修改账号");
        }
        $admin = Admin::where("phone", $phone)->find();
        if ($admin) {
            return self::returnFormat(999, "手机号已存在，请修改手机号");
        }
        $admin = new Admin([
            "name" => $name,
            "phone" => $phone,
            "role_id" => $roleId,
            "valid" => $valid,
        ]);
        $salt = rand(1000, 9999);
        $admin->salt = $salt;
        $admin->password = self::md5($salt, $password);
        $res = $admin->save();
        if ($res === false) {
            return self::returnFormat(999, "提交失败：数据库写入失败");
        }
        return self::returnFormat(0, "", $admin);
    }


    /**
     * 获取管理员列表
     * @param $keyword
     * @param $listRow
     * @return void
     * @throws \think\db\exception\DbException
     */
    public static function getList($keyword = "", $listRow = 20)
    {
        $where = [];
        if ($keyword) {
            $where[] = ["name|phone", "like", "%" . $keyword . "%"];
        }
        $list = Admin::with(['role' => function ($query) {
            $query->field("id,name,valid");
        }])->where($where)->order("id desc")->paginate($listRow);
        return self::returnFormat(0, '', $list);
    }

    /**
     * 加密密码
     * @param $pwd
     * @param $salt
     * @return string
     */
    private static function md5($salt, $pwd)
    {
        $str = md5($salt . $pwd);
        return $str;
    }

    /**
     * 登录接口
     * @param string $name
     * @param string $password
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function login(string $name, string $password)
    {
        $where = [];
        $where[] = ["name|phone", "=", $name];
        //$where[] = ["pwd", "=", $password];
        $admin = self::where($where)->find();
        if (!$admin) {
            return self::returnFormat(999, "用户未找到");
        }
        if ($admin->getAttr('password') != self::md5($admin->getAttr('salt'), $password)) {
//            Log::record("明文密码：" . $admin->getAttr('salt') . $password, "debug");
//            Log::record("原密码：", $admin->getAttr("password"));
//            Log::record("加密后密码：" . self::md5($admin->getAttr('salt'), $password), "debug");
            return self::returnFormat(999, '登录密码不正确' . $admin->getAttr('salt') . $password);
        }
        if (!$admin->valid) {
            return self::returnFormat(999, "账号被禁用，请联系管理员");
        }
        //更新登录信息
        $admin->login_count = $admin->login_count + 1;
        $admin->login_last_time = getNow();
        $admin->token = $admin->getToken();//更新token
        $admin->save();
        return self::returnFormat(0, "", $admin);
    }


    /**
     * 获取token
     * @return string
     */
    public function getToken()
    {
        $expireDays = 7;//过期时间，单位天
        //token:  md5([用户名][当前时间])|[用户id]|[过期时间]
        $token = base64_encode(md5($this->login_name . getNow()) . "|" . $this->id . "|" . (time() + 86400 * $expireDays));
        return $token;
    }

}