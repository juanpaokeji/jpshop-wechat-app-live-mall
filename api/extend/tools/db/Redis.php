<?php
namespace tools\db;

class Redis{
    /**
     *
     * @var object redis client
     */
    private $redis;
    protected $host = '127.0.0.1';
    protected $port = '6379';
    protected $timeout = '0.0';
    protected $password = '';

    /**
     * 构造函数
     * @param object $redis 已连接redis的phpredis的对象
     */
    public function __construct($redis = [])
    {
        if ($redis) {
            $this->redis = $redis;
        } else {
            $this->redis = new \Redis();
            $this->redis->connect($this->host, $this->port, $this->timeout);
            if (!empty($this->password)) {
                $this->redis->auth($this->password);
            }
        }
    }
    /**
     * 初始化redis
     */
    public function get_db(){
        return $this->redis;
    }
    /**
     * 获取key的值
     */
    public function get($key){
        $result = $this->get_db()->get($key);
        return $result;
    }
    /**
     * 设置key的值
     */
    public function set($key,$value,$timeout = 0){
        $result = $this->get_db()->set($key,$value,$timeout);
        return $result;
    }
    /**
     * 删除某个key
     */
    public function del($key){
        $result = $this->get_db()->del($key);
        return $result;
    }
    /**
     * 向集合添加一个或多个成员
     */
    public function sAdd($key,$value1=NULL){
        $result = $this->get_db()->sAdd($key,$value1);
        return $result;
    }
    /**
     * 移除集合中的一个成员
     */
    public function sRem($key,$member){
        $result = $this->get_db()->sRem($key,$member);
        return $result;
    }
    /**
     * 查询集合中成员的分数
     */
    public function zScore($key,$member){
        $result = $this->get_db()->zScore($key,$member);
        return $result;
    }
    /**
     * 获取集合中所有的成员
     */
    public function sMembers($key){
        $result = $this->get_db()->sMembers($key);
        return $result;
    }
    /**
     * 获取集合中元素的数量
     */
    public function sCard($key){
        $result = $this->get_db()->sCard($key);
        return $result;
    }
    /**
     * 有序集合新增
     */
    public function zAdd($key,$value,$score){
        $result = $this->get_db()->zAdd($key,$score,$value);
        return $result;
    }
    /**
     * 获取成员排名
     */
    public function zRevRank($key,$member){
        $result = $this->get_db()->zRevRank($key,$member);
        return $result;
    }
    /**
     * 递减排序
     */
    public function zRevRange($key,$start=0,$stop=-1,$withscores=NULL){
        $result = $this->get_db()->zRevRange($key,$start,$stop,$withscores);
        return $result;
    }
    /**
     * 递增排序
     */
    public function zRange($key,$start=0,$stop=-1,$withscores=NULL){
        $result = $this->get_db()->zRange($key,$start,$stop,$withscores);
        return $result;
    }
    /**
     * 根据分值递减排序
     */
    public function zRevRangeByScore($key,$withscores=NULL,$limit=NULL,$offset=NULL,$count=NULL,$min='-inf',$max='+inf'){
        if($limit){
            $result = $this->get_db()->zRevRangeByScore($key,$max,$min, ['WITHSCORES'=>$withscores, 'LIMIT'=>[$offset,$count]]);
        }else{
            $result = $this->get_db()->zRevRangeByScore($key,$max,$min, ['WITHSCORES'=>$withscores]);
        }
        return $result;
    }
    /**
     * 根据分值递增排序
     */
    public function zRangeByScore($key,$withscores=NULL,$limit=NULL,$offset=NULL,$count=NULL,$min='-inf',$max='+inf'){
        if($limit){
            $result = $this->get_db()->zRangeByScore($key,$max,$min, ['WITHSCORES'=>$withscores, 'LIMIT'=>[$offset,$count]]);
        }else{
            $result = $this->get_db()->zRangeByScore($key,$max,$min, ['WITHSCORES'=>$withscores]);
        }
        return $result;
    }
    /**
     * 指定成员增减量
     */
    public function zIncrBy($key,$value,$member){
        $result = $this->get_db()->zIncrBy($key,$value,$member);
        return $result;
    }


}
