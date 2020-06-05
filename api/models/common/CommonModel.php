<?php

namespace app\models\common;

use yii\db\ActiveRecord;

class CommonModel extends ActiveRecord {

    protected $tableName;
    protected $_transaction;
    public $where = array();
    public $field = "*";
    public $order = "id desc";
    public $groupBy = false;
    public $limit = 10;
    public $offset = 0;
    public $count = false;
    public $join = array();
    public $or = array();
    public $col= array();
    /**
     * 创建查询器
     */
    public function get_db() {

        return (new \yii\db\Query())->from($this->tableName());
    }

    /**
     * 获取单列单条记录
     */
    public function get_value2($where = [], $field = 'id', $order = 'id asc') {
        $value = $this->get_db()->select($field)->where($where)->andWhere(['delete_time' => NULL])->orderBy($order)->limit(1)->column();
        if (empty($value)) {
            return false;
        } else {
            return $value[0];
        }
    }

    /**
     * 获取单条记录
     */
    public function get_info2($where = [], $field = "id", $order = 'id asc') {
        $res = $this->get_db()->select($field)->andWhere(['delete_time' => NULL]);
        foreach ($where as $key => $value) {
            if (is_array($value)) {
                $res->andWhere([$value[0], $key, $value[1]]);
            } else {
                $res->andWhere([$key => $value]);
            }
        }
        $info = $res->orderBy($order)->limit(1)->one();
        if (empty($info)) {
            return false;
        } else {
            empty($info['create_time']) ? false : $info['format_create_time'] = date('Y-m-d H:i:s', $info['create_time']);
            empty($info['update_time']) ? false : $info['format_update_time'] = date('Y-m-d H:i:s', $info['update_time']);
            return $info;
        }
    }

    /**
     * 获取单条记录
     */
    public function get_info3($where = [], $field = "id", $order = 'id asc') {
        $res = $this->get_db()->select($field);
        foreach ($where as $key => $value) {
            if (is_array($value)) {
                $res->andWhere([$value[0], $key, $value[1]]);
            } else {
                $res->andWhere([$key => $value]);
            }
        }
        $info = $res->orderBy($order)->limit(1)->one();
        if (empty($info)) {
            return false;
        } else {
            empty($info['create_time']) ? false : $info['format_create_time'] = date('Y-m-d H:i:s', $info['create_time']);
            empty($info['update_time']) ? false : $info['format_update_time'] = date('Y-m-d H:i:s', $info['update_time']);
            return $info;
        }
    }

    /**
     * 获取列表记录
     */
    public function get_list2( $where = [] , $field = "*" , $order = 'id desc' , $offset =0 , $limit = 50 ,$count = false){


        $res = $this->get_db()->select($field)->orderBy($order)->limit($limit)->offset($offset);
        foreach ($where as $key=>$value){
            if (is_array($value)){
                $res->andWhere([$value[0],$key,$value[1]]);
            }else{
                $res->andWhere([$key=>$value]);
            }
        }
        $list['data']= $res->all();
        if(empty($list['data'])){
            return false;
        }
        foreach($list['data'] as $key => $value){
            empty($value['create_time'])?false:$list['data'][$key]['format_create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            empty($value['update_time'])?false: $list['data'][$key]['format_update_time']= date('Y-m-d H:i:s',$value['update_time']);
        }
        if($count==true){
            $count = $this->find();
            foreach ($where as $key=>$value){
                if (is_array($value)){
                    $count->andWhere([$value[0],$key,$value[1]]);
                }else{
                    $count->andWhere([$key=>$value]);
                }
            }
            $list['count']=$count->count(1);
        }
        return $list;
    }
    /**
     * 获取单条记录
     */
    public function get_info($params) {
        $this->getWhere($params);
        $res = $this->get_db()->select($this->field)->andWhere(['delete_time' => null]);

        foreach ($this->where as $key => $value) {
            if (is_array($value)) {

                if (count($value) < 2) {
                    $res->andWhere([$key => $value]);
                } else {
                    if (is_array($value[0]) && is_array($value[1])) {
                        for ($i = 0; $i < count($value); $i++) {
                            $res->andWhere([$value[$i][0], $key, $value[$i][1]]);
                        }
                    } else if (is_int($value[0]) && is_int($value[1])) {

                        $res->andWhere([$key => $value]);
                    } else {

                        $res->andWhere([$key, $value[0], $value[1]]);
                    }
                }
            } else {
                $res->andWhere([$key => $value]);
            }
        }
        if (!empty($this->or) && is_array($this->or)) {
            $res->andWhere($this->or);
        }

        $info = $res->orderBy($this->order)->limit(1)->one();
        if (empty($info)) {
            return false;
        } else {
            empty($info['create_time']) ? false : $info['format_create_time'] = date('Y-m-d H:i:s', $info['create_time']);
            empty($info['update_time']) ? false : $info['format_update_time'] = date('Y-m-d H:i:s', $info['update_time']);
            return $info;
        }
    }

    /**
     * 获取单条记录
     */
    public function get_info_del($params) {
        $this->getWhere($params);
        $res = $this->get_db()->select($this->field);

        foreach ($this->where as $key => $value) {
            if (is_array($value)) {
                $res->andWhere([$value[0], $key, $value[1]]);
            } else {
                $res->andWhere([$key => $value]);
            }
        }

        $info = $res->orderBy($this->order)->limit(1)->one();
        if (empty($info)) {
            return false;
        } else {
            empty($info['create_time']) ? false : $info['format_create_time'] = date('Y-m-d H:i:s', $info['create_time']);
            empty($info['update_time']) ? false : $info['format_update_time'] = date('Y-m-d H:i:s', $info['update_time']);
            return $info;
        }
    }

    /**
     * 获取单列单条记录
     */
    public function get_value($params) {
        $this->getWhere($params);
        $value = $this->get_db()->select($this->field)->where($this->where)->andWhere(['delete_time' => null])->orderBy($this->order)->limit(1)->column();
        if (empty($value)) {
            return false;
        } else {
            return $value[0];
        }
    }
    /**
     * 获取单列记录
     */
    public function get_column3( $where = [] , $field ='id',$index = NULL){
        if(empty($where)){
            $where = [];
        }
        $res = $this->get_db()->select($field);
        foreach ($where as $key=>$value){
            if (is_array($value)){
                $res->andWhere([$value[0],$key,$value[1]]);
            }else{
                $res->andWhere([$key=>$value]);
            }
        }
        if(empty($index)){
            $column=$res->column();
        }else{
            $column=$res->indexBy($index)->column();
        }
        return $column;
    }
    /**
     * 获取单列记录
     */
    public function get_column($params, $index = null) {
        $this->getWhere($params);
        $column = empty($index) ? $this->get_db()->select($this->field)->where($this->where)->andWhere(['delete_time' => null])->column() :
                $this->get_db()->select($this->field)->where($this->where)->indexBy($index)->column();

        return $column;
    }

    /**
     * 获取列表记录
     */
    public function get_list($params) {
        $this->getWhere($params);
        $table = $this->tableName();
        if ($this->limit != false) {
            if ($this->groupBy == false) {
                $res = $this->get_db()->select($this->field)->andWhere(["{$table}.delete_time" => null])->orderBy($this->tableName() . "." . $this->order)->limit($this->limit)->offset($this->offset);
            } else {
                $res = $this->get_db()->select($this->field)->andWhere(["{$table}.delete_time" => null])->orderBy($this->tableName() . "." . $this->order)->groupBy($this->tableName() . "." . $this->groupBy)->limit($this->limit)->offset($this->offset);
            }
        } else {
            if ($this->groupBy != false) {
                $res = $this->get_db()->select($this->field)->andWhere(["{$table}.delete_time" => null])->orderBy($this->tableName() . "." . $this->order)->groupBy($this->groupBy)->offset($this->offset);
            } else {
                $res = $this->get_db()->select($this->field)->andWhere(["{$table}.delete_time" => null])->orderBy($this->tableName() . "." . $this->order);
            }
        }


        if (!empty($this->join)) {
            $join = $this->join;

            for ($i = 0; $i < count($join); $i++) {
                $res->join($join[$i][0], $join[$i][1], $join[$i][2]);
            }
        }

        if (!empty($this->col)) {
            $res->andWhere(new \yii\db\Expression($this->col[0]." =" .$this->col[1]));
        }

        foreach ($this->where as $key => $value) {
            if (is_array($value)) {

                if (count($value) < 2) {
                    $res->andWhere([$key => $value]);
                } else {
                    if (is_array($value[0]) && is_array($value[1])) {
                        for ($i = 0; $i < count($value); $i++) {
                            $res->andWhere([$value[$i][0], $key, $value[$i][1]]);
                        }
                    } else if (is_int($value[0]) && is_int($value[1])) {

                        $res->andWhere([$key => $value]);
                    } else {
                        if ($value[0] == "like") {
                            $res->andWhere([$value[0], $key, $value[1]]);
                        } else {
                            $res->andWhere([$key, $value[0], $value[1]]);
                        }
                    }
                }
            } else {
                $res->andWhere([$key => $value]);
            }
        }
        if (!empty($this->or) && is_array($this->or)) {
            $res->andWhere($this->or);
        }



        $list['data'] = $res->all();

        if (empty($list['data'])) {
            return false;
        }
        foreach ($list['data'] as $key => $value) {
            empty($value['create_time']) ? false : $list['data'][$key]['format_create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            empty($value['update_time']) ? false : $list['data'][$key]['format_update_time'] = date('Y-m-d H:i:s', $value['update_time']);
        }
        if ($this->count == true) {

            //$list['count'] = $this->find()->where($this->where)->andWhere(['delete_time' => null])->count(1);
            // $list['count'] = $this->find()->andWhere(['delete_time' => null])->count(1);
            $res = $this->find()->andWhere(["{$table}.delete_time" => null]);
            if (!empty($this->join)) {
                $join = $this->join;

                for ($i = 0; $i < count($join); $i++) {
                    $res->join($join[$i][0], $join[$i][1], $join[$i][2]);
                }
            }
            foreach ($this->where as $key => $value) {
                if (is_array($value)) {
                    if (count($value) < 2) {
                        $res->andWhere([$key => $value]);
                    } else {
                        if (is_array($value[0]) && is_array($value[1])) {
                            foreach ($value as $k => $v) {
                                foreach ($v as $kk => $vv)
                                    $res->andWhere([$kk, $key, $vv]);
                            }
                        } else if (is_int($value[0]) && is_int($value[1])) {
                            $res->andWhere([$key => $value]);
                        } else {

                            if ($value[0] == "like") {
                                $res->andWhere([$value[0], $key, $value[1]]);
                            } else {
                                $res->andWhere([$key, $value[0], $value[1]]);
                            }
                        }
                    }
                } else {
                    $res->andWhere([$key => $value]);
                }
            }
            if (!empty($this->or) && is_array($this->or)) {
                $res->andWhere($this->or);
            }
            if (!empty($this->groupBy)) {
                $res->groupBy($this->groupBy);
            }
            $list['count'] = $res->count(1);
        }
        return $list;
    }

    /**
     * 获取列表数量
     */
    public function get_count($params) {
        $this->getWhere($params);
        $count = $this->get_db()->where($this->where)->andWhere(['delete_time' => null])->count(1);
        return $count;
    }

    /**
     * 新增修改记录  bool==false 修改  ==true 新增
     */
    public function modify($params, $data) {

        if (empty($data)) {
//新增          
            if (isset($params['id'])) {
                unset($params['id']);
            }
            foreach ($params as $key => $val) {
                $this->$key = $val;
            }
            $this->create_time = $_SERVER["REQUEST_TIME"];
            $this->update_time = $_SERVER["REQUEST_TIME"];
            $result = $this->insert();
            $insertId = $this->primaryKey;
            if ($result == true && !empty($insertId)) {
                return $insertId;
            }
        } else {
//修改  

            $data['update_time'] = $_SERVER["REQUEST_TIME"];

            $result = $this->updateAll($data, $params);
        }
        return $result;
    }

    /**
     * 软删除记录
     */
    public function soft_delete($where) {

        $data['delete_time'] = $_SERVER["REQUEST_TIME"];
        $this->where['delete_time'] = null;
        $result = $this->updateAll($data, $where);
        return $result;
    }

    public function soft_del($where) {
        $result = $this->deleteAll($where);
        return $result;
    }

    /**
     * 累加记录
     */
    public function setInc($where = [], $field = '', $offset = 1) {
        return $this->updateAllCounters([$field => $offset], $where);
    }

    /**
     * 累减记录
     */
    public function setDec($where = [], $field = '', $offset = 1) {
        return $this->updateAllCounters([$field => (-1 * $offset)], $where);
    }

    /**
     * 开启一个事物
     */
    public function begin() {

        $this->_transaction = static::getDb()->beginTransaction();
    }

    /**
     * 提交一个事物
     */
    public function commit() {
        if (!empty($this->_transaction)) {
            $this->_transaction->commit();
        }
    }

    /**
     * 回退一个事物
     */
    public function rollback() {
        if (!empty($this->_transaction)) {
            $this->_transaction->rollBack();
        }
    }

    public function getWhere($params) {

        if (isset($params['field'])) {
            $this->field = $params['field'];
            unset($params['field']);
        }
        if (isset($params['orderby'])) {
            $this->order = $params['orderby'];
            unset($params['orderby']);
        }
        if (isset($params['groupBy'])) {
            $this->groupBy = $params['groupBy'];
            unset($params['groupBy']);
        }
        if (isset($params['limit'])) {
            $this->limit = $params['limit'];
            unset($params['limit']);
        }
        if (isset($params['page'])) {
            if ($params['page'] - 1 < 0) {
                $this->offset = 0;
            } else {
                $this->offset = ($params['page'] - 1) * $this->limit;
            }
            unset($params['page']);
        }
        if (isset($params['count'])) {
            $this->count = $params['count'];
            unset($params['count']);
        }
        if (isset($params['join'])) {
            $this->join = $params['join'];
            unset($params['join']);
        }
        if (isset($params['or'])) {
            $this->or = $params['or'];
            unset($params['or']);
        }
        if (isset($params['col'])) {
            $this->col = $params['col'];
            unset($params['col']);
        }

        $this->where = $params;
    }

}
