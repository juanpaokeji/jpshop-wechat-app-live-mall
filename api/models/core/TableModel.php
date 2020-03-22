<?php

/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace app\models\core;

//引入各表实体
use yii;
use yii\db\Exception;
use yii\web\Response;
use yii\base\Model;

/**
 * 通用表格操作 model
 *
 * @version   2018年03月19日
 * @author    JYS <272074691@qq.com>
 * @copyright Copyright 2010-2016 Swoft software
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 *
 * @Bean()
 */
class TableModel extends Model {

    /** 查询列表
     * $table 为必传参
     *
     * table 表名，where where条件，orderBy 排序，limit 限制
     * @param array|null $params
     *
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function tableList($params) {
        if (isset($params['table'])) {
            $table = $params['table'];
            unset($params['table']);
        }

        if (isset($params['fields'])) {
            $fields = $params['fields'];
            unset($params['fields']);
        }
        if (isset($params['orderby'])) {
            $orderBy = $params['orderby'];
            unset($params['orderby']);
        }
        if (isset($params['groupby'])) {
            $groupby = $params['groupby'];
            unset($params['groupby']);
        }

        $join = "";
        if (isset($params['join'])) {
            $join = $params['join'];
            unset($params['join']);
        }

        if (isset($params['limit'])) {
            $limit = $params['limit'];
            if (!empty($limit)) {
                if (isset($params['page']) && isset($params['limit'])) {
                    $num = (int)$params['limit'];
                    $page = (int)$params['page'];
                    if($page==0){
                        $limit = ' limit 0,' . $num;
                    }else{
                        $limit = ' limit ' . ($page - 1) * $num . "," . $num;
                    }
                } else {
                    $limit = ' limit ' . $limit . ' ';
                }
            } else {
                $limit = ' ';
            }
            unset($params['limit']);
            unset($params['page']);
        } else {
            $limit = "";
        }

        $where = $params;

        if (empty($table)) {
            return ['The table name of the query cannot be empty'];
        }

        $whereStr = ' ';
        if (!empty($fields)) {
            $fields = $fields . ' ';
        } else {
            $fields = ' * ';
        }
        if (!empty($where) && is_array($where)) {
            $whereStr = self::getWhere($where);
        }
        if (!empty($orderBy)) {
            $orderBy = ' ORDER BY  ' . $orderBy . ' ';
        } else {
            $orderBy = ' ';
        }
        if (!empty($groupby)) {
            $groupby = ' GROUP BY  ' . $groupby . ' ';
        } else {
            $groupby = ' ';
        }

        $res = Yii::$app->db->createCommand('SELECT ' . $fields . ' FROM ' . $table . $join . $whereStr . $groupby . $orderBy . $limit)->queryAll();
        $num = Yii::$app->db->createCommand("SELECT count({$table}.id)as num FROM " . $table . $join . $whereStr . $groupby)->queryOne();

        $array['app'] = $res;
        $array['count'] = $num['num'];

        Yii::$app->response->format = Response::FORMAT_JSON;
//        echo yii::$app->db->createCommand('SELECT ' . $fields . ' FROM ' . $table . $whereStr . $orderBy . $limit)->getRawSql();
//        exit;
        return $array;
    }

    public function querySql($sql) {
        $res = Yii::$app->db->createCommand($sql)->queryAll();
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $res;
    }

    /**
     * 查询单条记录
     * $table 为必传参
     *
     * table 表名，fields 需要查询的字典，where where条件，orderBy 排序
     * @param string $table
     * @param string $fields
     * @param array|null $where
     * @param string|null $orderBy
     *
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function tableSingle($table, $where = [], $fields = '', $orderBy = '') {
        if (empty($table)) {
            return ['The table name of the query cannot be empty'];
        }
        if (!empty($fields)) {
            $fields = $fields . ' ';
        } else {
            $fields = ' * ';
        }
        $whereStr = ' ';
        if (!empty($where) && is_array($where)) {
            $whereStr = self::getWhere($where);
        }
        if (!empty($orderBy)) {
            $orderBy = ' order by ' . $orderBy . ' ';
        } else {
            $orderBy = ' ';
        }
        $res = yii::$app->db->createCommand('SELECT ' . $fields . ' FROM ' . $table . $whereStr . $orderBy . ' limit 1')->queryOne();
        Yii::$app->response->format = Response::FORMAT_JSON;
//        echo yii::$app->db->createCommand('SELECT ' . $fields . ' FROM ' . $table . $whereStr . $orderBy . ' limit 1')->getRawSql();
//        exit;
        return $res;
    }

    /**
     * 新增记录
     * $table 为必传参
     *
     * @param string $table
     * @param array|null $data
     * @throws Exception if the model cannot be found
     * @return string
     */
    public function tableAdd($table = '', $data = []) {
        if (empty($table)) {
            return false;
        }
        $setStr = ' ';
        if (!empty($data) && is_array($data)) {
            $count = count($data); //条件数量
            $setStr = ' set '; //最终执行的where条件
            $keys = array_keys($data);
//            //添加防注入
//            foreach ($data as $key => $value) {
//                $pKey = addslashes($key);
//                $data[$pKey] = addslashes($value);
//            }
            for ($i = 0; $i < $count; $i++) {
                $setStr .= $keys[$i] . "='" . $data[$keys[$i]] . "',";
            }
            $setStr = trim($setStr, ',');
        }
        Yii::$app->db->createCommand('INSERT ' . $table . $setStr)->execute();
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->db->getLastInsertId();
        return $id;
    }

    /**
     * 删除记录
     * $table 为必传参
     *
     * @param string $table
     * @param array|null $where
     * @throws Exception if the model cannot be found
     * @return string
     */
    public function tableDelete($table = '', $where = []) {
        if (empty($table)) {
            return false;
        }
        if (gettype($where) != 'array') {
            return false;
        }
        $whereStr = ' ';
        if (!empty($where) && is_array($where)) {
            $whereStr = self::getWhere($where);
        }
        $res = Yii::$app->db->createCommand('DELETE FROM ' . $table . $whereStr)->execute();
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $res;
    }

    /**
     * 更新记录
     * $table 为必传参
     *
     * @param string $table
     * @param array|null $data
     * @param array|null $where
     *
     * @throws Exception if the model cannot be found
     * @return string
     */
    public function tableUpdate($table = '', $data = [], $where = []) {
        if (empty($table)) {
            return false;
        }
        $setStr = ' ';
        if (!empty($data) && is_array($data)) {
            $count = count($data); //条件数量
            $setStr = ' set '; //最终执行的where条件
            $keys = array_keys($data);
            for ($i = 0; $i < $count; $i++) {
                if ($i == 0) {
                    if (!is_null($data[$keys[$i]])) {
                        $setStr .= $keys[$i] . "='" . $data[$keys[$i]] . "',";
                    } else {
                        $setStr .= $keys[$i] . " , ";
                    }
                } else {
                    if (!is_null($data[$keys[$i]])) {
                        $setStr .= $keys[$i] . "='" . $data[$keys[$i]] . "',";
                    } else {
                        $setStr .= $keys[$i];
                    }
                }
            }
            $setStr = rtrim($setStr);
            $setStr = rtrim($setStr, ",");
        }
        $whereStr = ' ';
        if (!empty($where) && is_array($where)) {
            $whereStr = self::getWhere($where);
        }
        $res = Yii::$app->db->createCommand('UPDATE ' . $table . $setStr . $whereStr)->execute();
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $res;
    }

//

    /**
     * 通用处理where条件
     * @param $where
     * @return string
     */
    private function getWhere($where) {
//        //添加防注入
//        foreach ($where as $key => $value) {
//            $pKey = addslashes($key);
//            $where[$pKey] = addslashes($value);
//        }

        $count = count($where); //条件数量
        $whereStr = ''; //最终执行的where条件
        $keys = array_keys($where);
        for ($i = 0; $i < $count; $i++) {
            if ($i == 0) {
                if (!is_null($where[$keys[$i]])) {
                    $whereStr .= ' where ' . $keys[$i] . ' = \'' . $where[$keys[$i]] . "'";
                } else {
                    $whereStr .= ' where ' . $keys[$i];
                }
            } else {
                if (!is_null($where[$keys[$i]])) {
                    $whereStr .= ' and ' . $keys[$i] . ' = \'' . $where[$keys[$i]] . "'";
                } else {
                    $whereStr .= ' and ' . $keys[$i];
                }
            }
        }
        return $whereStr;
    }

    public function getLimit($params) {
        $num = $params['limit'];
        $page = $params['page'];
        $limit = ($page - 1) * $num . "," . $num;

        return $limit;
    }

}
