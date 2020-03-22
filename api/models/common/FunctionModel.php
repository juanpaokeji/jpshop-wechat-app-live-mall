<?php
namespace app\models\common;

use yii\base\Model;

class time{
    function getFirstYear($year){
        $year=date("Y",time());
        $first=$year."-01-01";
        $end=$year."-12-31";
    }
}
