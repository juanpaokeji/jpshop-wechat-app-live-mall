<?php

/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/4/18 9:03
 */

namespace app\controllers\wechat\officialAccount;

use app\controllers\WxController;

class UserController extends WxController
{
    public $enableCsrfValidation = false;//禁用CSRF令牌验证，可以在基类中设置
    public $wechat_type = 'oa';

    /**
     * 控制器默认获取用户 openId 列表 get 请求
     * @throws
     */
    public function actionIndex()
    {
        $request = request();
        //判断请求方式
        if ($request['method'] != 'GET') {
            return result('500', '请求失败');
        }

        //获取公众号实例 必须
        $app = $this->getApp($this->wechat_type);

        //获取已创建菜单列表
        $list = $app->user->list($nextOpenId = null);// $nextOpenId 可选

        //无用户会返回 errcode ，有用户直接返回，不返回状态码，比较坑
        if (isset($list['errcode'])) {
            return result('400', '暂无用户');
        }
        return result('200', '请求成功', $list);
    }

    /**
     * 通过 openId 查询单个或多个用户信息
     * @throws
     */
    public function actionSelect()
    {
        $request = request();
        //判断请求方式
        if ($request['method'] != 'GET') {
            return result('500', '请求失败');
        }

        //获取公众号实例 必须
        $app = $this->getApp($this->wechat_type);

        //获取请求参数 openId 为单条查询 ，openIds 为多条查询
        $params = $request['params'];
        if (isset($params['openId'])) {
            $res = $app->user->get($params['openId']);
        } else if (isset($params['openIds'])) {
            $res = $app->user->select($params['openIds']);
        } else {
            return result('400', '请求参数错误');
        }
        if (isset($res['errcode'])) {
            return result('400', '未查到用户信息');
        }
        return result('200', '请求成功', $res);
    }

    /**
     * 修改用户备注
     * @throws
     */
    public function actionRemark()
    {
        $request = request();
        //判断请求方式
        if ($request['method'] != 'PUT') {
            return result('500', '请求失败');
        }

        //获取公众号实例 必须
        $app = $this->getApp($this->wechat_type);

        //获取请求参数 openId 为单条查询 ，openIds 为多条查询
        $params = $request['params'];
        if (isset($params['openId']) && isset($params['remark'])) {
            $res = $app->user->remark($params['openId'], $params['remark']);
        } else {
            return result('400', '请求参数错误');
        }
        if ($res['errcode'] != 0) {
            return result('500', '修改失败');
        }
        return result('200', '修改成功');
    }

    /**
     * 获取黑名单 openId 列表
     * @throws
     */
    public function actionBlack()
    {
        $request = request();
        //判断请求方式
        if ($request['method'] != 'GET') {
            return result('500', '请求失败');
        }

        //获取公众号实例 必须
        $app = $this->getApp($this->wechat_type);

        //获取黑名单没有错误代码返回
        $res = $app->user->blacklist($beginOpenid = null);
        return result('200', '请求成功', $res);
    }

    /**
     * 拉黑用户
     * @throws
     */
    public function actionBlock()
    {
        $request = request();
        //判断请求方式
        if ($request['method'] != 'PUT') {
            return result('500', '请求失败');
        }

        //获取公众号实例 必须
        $app = $this->getApp($this->wechat_type);

        $params = $request['params'];
        if (isset($params['openId'])) {
            $res = $app->user->block($params['openId']);
        } else {
            return result('400', '请求参数错误');
        }
        if ($res['errcode'] != 0) {
            return result('500', '请求失败');
        }
        return result('200', '请求成功');
    }

    /**
     * 取消拉黑用户
     * @throws
     */
    public function actionUnblock()
    {
        $request = request();
        //判断请求方式
        if ($request['method'] != 'PUT') {
            return result('500', '请求失败');
        }

        //获取公众号实例 必须
        $app = $this->getApp($this->wechat_type);

        $params = $request['params'];
        if (isset($params['openId'])) {
            $res = $app->user->unblock($params['openId']);
        } else {
            return result('400', '请求参数错误');
        }
        if ($res['errcode'] != 0) {
            return result('500', '请求失败');
        }
        return result('200', '请求成功');
    }
}
