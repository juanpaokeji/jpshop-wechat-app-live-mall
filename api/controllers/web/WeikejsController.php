<?php

namespace app\controllers\web;

use app\models\weikejs\CategoryModel;
use app\models\weikejs\PostCategoryModel;
use app\models\weikejs\PostModel;
use app\models\weikejs\SlideModel;
use yii;
use yii\web\Controller;


/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class WeikejsController extends Controller {
    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置
    //获取banner信息
    public function actionGetbanner(){
        $request = request();
        $params=$request['params'];
        $where=[];
        $params['page']=1;
        $params['limit']=100;
        $list = SlideModel::instance()->get_list2($where,'*','id desc',($params['page']-1)*$params['limit'],$params['limit'],true);
        if(empty($list)){
            return result2(-100,'暂无数据');
        }
        return result2(200,'获取成功',$list['data'],$list['count']);
    }
    public function actionChanpin(){
        $request = request();
        $params=$request['params'];
        $where=[];
        $params['page']=1;
        $params['limit']=100;
        $where['parent_id']=3;

        $next_ids = CategoryModel::instance()->get_column3($where,'id');
        if(empty($next_ids)){
            return result2(-100,'暂无数据');
        }


        $ids = CategoryModel::instance()->get_column3(['parent_id'=>['in',$next_ids],'delete_time'=>0],'id');
        if(empty($ids)){
            return result2(-100,'暂无数据');
        }
        $ids = CategoryModel::instance()->get_column3(['parent_id'=>['in',$ids],'delete_time'=>0],'id');
        if(empty($ids)){
            return result2(-100,'暂无数据');
        }

        $post_ids = PostCategoryModel::instance()->get_column3(['category_id'=>['in',$ids]],'post_id');
        if(empty($post_ids)){
            return result2(-100,'暂无数据');
        }

        $where=[];
        $where['id']=['in',$post_ids];
        $where['delete_time']=0;
        $list = PostModel::instance()->get_list2($where,'*','id desc',($params['page']-1)*$params['limit'],$params['limit'],true);
        if(empty($list)){
            return result2(-100,'暂无数据');
        }
        foreach ($list['data'] as $key=>$value){
            $list['data'][$key]['src']='upload/'.$value['thumbnail'];
            $list['data'][$key]['name']=$value['post_title'];
        }
        return result2(200,'获取成功',$list['data'],$list['count']);
    }
    public function actionInfo(){
        $request = request();
        $params=$request['params'];
        $id = $params['id'];
        if(empty($id)){
            return result2(-100,'暂无数据');
        }
        $info = PostModel::instance()->get_info3(['id'=>$id,'delete_time'=>0],'*');
        if(empty($info)){
            return result2(-100,'暂无数据');
        }else{
            $info['post_content']=htmlspecialchars_decode($info['post_content']);
            $info['post_content']=str_replace('src="default/','src="../upload/default/',$info['post_content']);

            $info['post_content']=str_replace('src="portal/','src="../upload/portal/',$info['post_content']);

            return result2(200,'获取成功',$info);
        }
    }
    public function actionRecommended(){
        $request = request();
        $params=$request['params'];
        $where=[];
        $params['page']=1;
        $params['limit']=100;

        $where['delete_time']=0;
        $where['parent_id']=9;
        $data = [];

        $list = CategoryModel::instance()->get_list2($where,'*','id desc',($params['page']-1)*$params['limit'],$params['limit'],true);
        if(!empty($list)){
            foreach ($list['data'] as $key=>$value){
                $list['data'][$key]['img']=json_decode($value['more'],true);
            }
            $data[]=$list['data'];
        }

        $where['parent_id']=10;
        $list = CategoryModel::instance()->get_list2($where,'*','id desc',($params['page']-1)*$params['limit'],$params['limit'],true);
        if(!empty($list)){
            foreach ($list['data'] as $key=>$value){
                $list['data'][$key]['img']=json_decode($value['more'],true);
            }
            $data[]=$list['data'];
        }

        $where['parent_id']=11;
        $list = CategoryModel::instance()->get_list2($where,'*','id desc',($params['page']-1)*$params['limit'],$params['limit'],true);
        if(!empty($list)){
            foreach ($list['data'] as $key=>$value){
                $list['data'][$key]['img']=json_decode($value['more'],true);
            }
            $data[]=$list['data'];
        }

        return result2(200,'获取成功',$data,1);
    }
    public function actionChanpinlist(){
        $request = request();
        $params=$request['params'];
        $where=[];
        $params['page']=1;
        $params['limit']=100;
        $where['parent_id']=$params['id'];
        $where['delete_time']=0;

        if(empty($params['id'])){
            $ext_msg='产品信息';
        }else{
            $t=CategoryModel::instance()->get_info3(['id'=>$params['id'],'delete_time'=>0],'name,parent_id');
            if(!empty($t)){
                $s=CategoryModel::instance()->get_info3(['id'=>$t['parent_id'],'delete_time'=>0],'name,parent_id');
                if(!empty($s)){
                    $y=CategoryModel::instance()->get_info3(['id'=>$s['parent_id'],'delete_time'=>0],'name,parent_id');
                    if(!empty($y)){

                    }
                }
            }
            $ext_msg='';
            if(!empty($y)){
                $ext_msg.='>'.$y['name'];
            }
            if(!empty($s)){
                $ext_msg.='>'.$s['name'];
            }
            if(!empty($t)){
                $ext_msg.='>'.$t['name'];
            }
        }
        if(!empty($params['keywords'])) {
            $where['name'] = ['like', urldecode($params['keywords'])];
        }

        $list = CategoryModel::instance()->get_list2($where,'*','id desc',($params['page']-1)*$params['limit'],$params['limit'],true);
        if(empty($list)){

            $where=[];
            if(empty($params['keywords'])){
                $post_ids = PostCategoryModel::instance()->get_column3(['category_id'=>$params['id']],'post_id');
                if(empty($post_ids)){
                    return result2(-100,'暂无数据');
                }
                $where['id']=['in',$post_ids];
            }

            $where['delete_time']=0;
            if(!empty($params['keywords'])) {
                $where['post_title'] = ['like', urldecode($params['keywords'])];
            }

            $list = PostModel::instance()->get_list2($where,'*','id desc',($params['page']-1)*$params['limit'],$params['limit'],true);
            if(empty($list)){
                return result2(-100,'暂无数据');
            }
            foreach ($list['data'] as $key=>$value){
                $list['data'][$key]['src']='upload/'.$value['thumbnail'];
                $list['data'][$key]['name']=$value['post_title'];
            }
            return result2(200,'获取成功',$list['data'],0,$ext_msg);

        }
        foreach ($list['data'] as $key=>$value){
            $list['data'][$key]['img']=json_decode($value['more'],true);
        }
        return result2(200,'获取成功',$list['data'],1,$ext_msg);
    }
    public function actionMenu(){
        $request = request();
        $params=$request['params'];
        $list = $this->tree2(3);
        return result2(200,'获取成功',$list);
    }

    public function actionMenus(){
        $request = request();
        $params=$request['params'];
        $list = $this->tree2(0);
        return result2(200,'获取成功',$list);
    }
    public function actionSuccessmenu(){
        $request = request();
        $params=$request['params'];
        $list = $this->tree2(4);
        return result2(200,'获取成功',$list);
    }
    public function tree2($pid=0,$level=0){
        $params['page']=1;
        $params['limit']=500;
        $where['parent_id']=$pid;
        $where['delete_time']=0;
        $list = CategoryModel::instance()->get_list2($where,'id,name,more','id asc',($params['page']-1)*$params['limit'],$params['limit'],true);
        $level++;
        $tree=[];
        if(!empty($list)){

            foreach ($list['data'] as $value){
                $child = $this->tree2($value['id'],$level);
                $tree[]=['name'=>$value['name'],'id'=>$value['id'],'child'=>$child,'level'=>$level];
            }
        }
        return $tree;
    }

////递归实现无限级菜单
//    public function tree2($pid=0,$level=0){
//        $cat = M('category');
//        $data = $cat ->where("parentid = $pid") ->field('id,catname,parentid') ->select();
//
//        $level ++;
//        if(!empty($data)){
//            $tree = array();
//            foreach ($data as $val) {
//                $val['catname'] = str_repeat('|—', $level-1).$val['catname'];
//                $child = $this ->tree2($val[id],$level);
//                $tree[] = array('self'=>$val,'child'=>$child,'level'=>$level);
//            }
//        }
//        return $tree;
//    }
//
//    function test(){
//        $allcat = $this ->tree2(0);
//        dump(json_encode($allcat));
//    }






    public function actionIndex() {
        return;
        $table = new TableModel();
        $sql = "SET @ids := '';";
        $a = Yii::$app->db->createCommand($sql)->execute();
        $sql = " UPDATE system_mini_template_access   SET status= 0,number = number+1 WHERE status =-1 AND number <=5 AND ( SELECT @ids := CONCAT_WS(',', id, @ids) );";
        $a = Yii::$app->db->createCommand($sql)->execute();
        $sql = "SELECT @ids;";
        $res = Yii::$app->db->createCommand($sql)->queryOne()  ;
        var_dump($res);
        return;
        if ($res == "") {
            $sql = "SET @ids := '';";
            $a = Yii::$app->db->createCommand($sql)->execute();
            return result(500, "请求失败");
        }
        $ids = substr($res['@ids'], 0, strlen($res['@ids']) - 1);
        $ids = explode(",", $ids);
//        $ids = [231];
        $model = new SystemMerchantMiniAccessModel();
        $message = $model->do_select(['in' => ['id', $ids]]);
        if ($message['status'] == 204) {
            return $message;
        }
        if ($message['status'] == 500) {
            return $message;
        }
        for ($i = 0; $i < count($message['data']); $i++) {
            $config = $this->getSystemConfig($message['data'][$i]['key'], "miniprogram");
            $openPlatform = Factory::openPlatform($this->config);

            if ($message['data'][$i]['template_purpose'] == 'order') {
//                $formModel = new SystemFormModel();
//                $form = $formModel->do_one(['mini_open_id' => $message['data'][$i]['mini_open_id'], 'merchant_id' => $message['data'][$i]['merchant_id'], 'key' => $message['data'][$i]['key'], 'status' => 1]);
//                if ($form['status'] != 200) {
//                    return result(500, "请求失败");
//                }
//
//                $mtemp = new \app\models\system\SystemMerchantMiniTemplateModel;
//                $mmtemp = $mtemp->do_one(['system_mini_template_id' => $message['data'][$i]['template_id']]);
//
//                $rs = $formModel->do_update(['mini_open_id' => $message['data'][$i]['mini_open_id'], 'merchant_id' => $message['data'][$i]['merchant_id'], 'key' => $message['data'][$i]['key']], ['status' => 0]);
//
//                $model = new SystemMerchantMiniAccessModel();
//                $model->do_update(['id' => $message['data'][$i]['id']], ['status' => 1]);
//                // 代小程序实现业务
//                $miniProgram = $openPlatform->miniProgram($config['app_id'], $config['refresh_token']);
//                $data = json_decode($message['data'][$i]['template_params'], true);
//                $res = $miniProgram->template_message->send([
//                    'touser' => $message['data'][$i]['mini_open_id'],
//                    'template_id' => $mmtemp['data']['template_id'],
//                    'page' => "/pages/orderItem/orderItem/orderItem?order_sn={$data['keyword1']}",
//                    'form_id' => $form['data']['formid'],
//                    'data' => $data
//                ]);
            }
            if ($message['data'][$i]['template_purpose'] == 'message') {
                $formModel = new SystemFormModel();
                $form = $formModel->do_one(['mini_open_id' => $message['data'][$i]['mini_open_id'], 'merchant_id' => $message['data'][$i]['merchant_id'], 'key' => $message['data'][$i]['key'], 'status' => 1]);
                if ($form['status'] != 200) {
                    return result(500, "请求失败");
                }
//                $mtemp = new \app\models\system\SystemMerchantMiniTemplateModel;
//                $mmtemp = $mtemp->do_one(['system_mini_template_id' => $message['data'][$i]['template_id']]);
                // $rs = $formModel->do_update(['mini_open_id' => $message['data'][$i]['mini_open_id'], 'merchant_id' => $message['data'][$i]['merchant_id'], 'key' => $message['data'][$i]['key']], ['status' => 0]);
//                $model = new SystemMerchantMiniAccessModel();
//                $model->do_update(['id' => $message['data'][$i]['id']], ['status' => 1]);
                // 代小程序实现业务
                $miniProgram = $openPlatform->miniProgram($config['app_id'], $config['refresh_token']);
                $data = json_decode($message['data'][$i]['template_params'], true);
                $page = json_decode($message['data'][$i]['page'], true);
//                var_dump($data);
//                var_dump($form['data']['formid']);
//                var_dump($message['data'][$i]['page']);
//                var_dump($message['data'][$i]['template_id']);
//                var_dump($message['data'][$i]['mini_open_id']);
//                echo "</br>";
                $res = $miniProgram->template_message->send([
                    'touser' => $message['data'][$i]['mini_open_id'],
                    'template_id' => $message['data'][$i]['template_id'],
                    'page' => $page[0]['page_url'],
                    'form_id' => $form['data']['formid'],
                    'data' => $data
                ]);
                var_dump($res);
            }
        }
        die();
    }

}
