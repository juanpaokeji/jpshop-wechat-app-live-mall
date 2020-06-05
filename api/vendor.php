<?php
vendorBool();
function vendor(){
    {
        $url = "http://shouquanjs.juanpao.com/vendor.zip";
        $upgrade_file = get_file($url);

        if(!$upgrade_file){
            echo(json_encode(array('status'=>500,'message'=>'下载升级文件失败')));
            die();
        }
        unzip_file($upgrade_file,'./');
        $filename = '../vendor.txt';
        $fp= fopen($filename, "w");  //w是写入模式，文件不存在则创建文件写入。
        $len = fwrite($fp, "true");
        fclose($fp);
        echo(json_encode(array('status'=>200,'message'=>'请求成功')));
        die();
    }
}

function vendorBool(){
    $filename = '../vendor.txt';
    $bool = file_get_contents($filename);
    if($bool=='false'){
        vendor();
        echo(json_encode(array('status'=>500,'message'=>'请求成功')));
        die();
    }else{

        echo(json_encode(array('status'=>200,'message'=>'请求成功')));
        die();
    }

}

function unzip_file($zipName,$dest)
{
    if (!is_file($zipName)) {
        return false;
    }
    if (!is_dir($dest)) {
        mkdir($dest, 0777, true);
    }
    $zip = new \ZipArchive();
    if ($zip->open($zipName)) {
        $zip->extractTo($dest);
        $zip->close();
        return true;
    } else {
        return false;
    }
}

function curl_get($url, $timeout = 10){
    $ch=curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_HEADER,0);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; CPU iPhone OS 8_4 like Mac OS X)AppleWebKit/600.1.4 (KHTML, like Gecko) Mobile/12H143 MicroMessenger/6.3.9)');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $res  =  curl_exec($ch);
    if(!$res){
        $res  =  curl_exec($ch);
    }
    curl_close($ch);
    return $res;
}

function get_file($url, $folder = './data/') {
    set_time_limit(24 * 60 * 60);
    $target_dir = $folder . '';
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $newfname = date('Ymd') . rand(1000, 10000000) . uniqid() . '.zip';
    $newfname = $target_dir . $newfname;
    $file = fopen($url, "rb");
    if ($file) {
        $newf = fopen($newfname, "wb");
        if ($newf) while (!feof($file)) {
            $buf = fread($file, 1024 * 8);
            if(strpos($buf,'{"status":0') === 0){
                $data = json_decode($buf, true);
                exit($data['msg']);
            }
            fwrite($newf, $buf, 1024 * 8);
        }
    }
    if ($file) {
        fclose($file);
    }
    if ($newf) {
        fclose($newf);
    }
    return $newfname;
}

?>

