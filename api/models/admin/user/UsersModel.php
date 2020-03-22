<?php

namespace app\models\admin\user;
use app\models\common\Base;
/**
 * This is the model class for table "admin_user".
 *
 * @property int $id
 * @property string $phone 手机号
 * @property string $name 名称
 * @property string $password 密码
 * @property string $intro 简单介绍
 * @property string $balance 余额
 * @property int $status 状态 1=正常 0=审核中 2=禁用
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 * @property int $delete_time 删除时间
 */


class UsersModel extends Base
{
    public $code;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'admin_user';
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name','password'],'required','message'=>'{attribute}不能为空'],
            [['id', 'type', 'status', 'create_time', 'update_time', 'delete_time'], 'integer','message'=>'{attribute}必须为整数'],
            [['phone', 'name', 'intro'], 'safe'],
            [['balance'], 'number','message'=>'{attribute}必须为数字'],
            ['code', 'captcha', 'message'=>'{attribute}有误','captchaAction'=>'/user/captcha','on'=>'login'],
        ];
    }
    //登录
    public function login($params){
        $this->attributes=$params;
        if (!$this->validate()) {
            return error(array_values($this->errors)[0][0],'','417');
        }
        $where=[
            'name'=>$params['name'],
            'password'=>MD5($params['password']),
        ];
        $field='id,nickname,phone';
        if(($data=$this->get_info($where,$field))==false){
            return error('账号或密码有误');
        }else{
            $data['access_token']=1111;
            return success('登录成功',$data);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phone' => '手机号',
            'nickname' => '昵称',
            'name' => '名称',
            'password' => '密码',
            'intro' => '简介',
            'type' => '标签',
            'balance' => '余额',
            'status' => '状态码',
            'create_time' => '创建时间',
            'update_time' => '更新时间',
            'delete_time' => '删除时间',
            'code' => '验证码',
        ];
    }
}
