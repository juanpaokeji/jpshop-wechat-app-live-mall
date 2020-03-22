<?php
namespace app\models\common;

use yii\base\Model;

class BaseModel extends Model{
    protected $tableName;
    //获取单条记录
    public function get_info( $where , $field = "*" ){
        $result = (new \yii\db\Query())
            ->select($field)
            ->from($this->tableName())
            ->where($where)
            ->one();
        return $result;
    }
//    protected $table;
//    //获取单条记录
//    public function get_info( $where , $field = "*" ){
//
//    }
//    //获取列表记录
//    public function get_list( $where , $field = "*" , $order = 'id desc' , $limit = ""){
//
//    }
//    //新增更改记录
//    public function save( $where , $data ){
//
//    }
//    //获取某列记录
//    public function get_column ( $where ,$field ){
//
//    }
//    //获取统计数量
//    public function count ($where){
//
//    }
//    //获取某值记录
//    public function get_value ($where,$field){
//
//    }
//    //删除某行记录
//    public function delete( $where){
//
//    }
//    //累加字段
//    public function setInc($where,$field,$offset){
//
//    }
//    //累减字段
//    public function setDec($where,$field,$offset){
//
//    }
}