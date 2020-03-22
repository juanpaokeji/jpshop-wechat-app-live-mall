/**
 * Created by UICUT.com on 2016/12/31.
 * Contact QQ: 215611388
 */


let orderNum='123456'
let version='10'
let MD5key='e10adc3949ba59abbe56e057f20f883e'
let HOST='http://39.106.50.178:8080/api/'
let expireTime=604800000


// function secret(str){
// 	// console.log('version:',version)
// 	// console.log('orderNum:',orderNum)
// 	// console.log('str:',str)
// 	// console.log('MD5key:',MD5key)
// 	return hex_md5(version+orderNum+str+MD5key)
// }
// console.log(secret('18900220083'))



// 存储
Storage.prototype.setExpire=(key, value, expire) =>{
    let obj={
        data:value,
        time:Date.now(),
        expire:expire
    };
    localStorage.setItem(key,JSON.stringify(obj));
}
// 获取
Storage.prototype.getExpire= key =>{
    let val =localStorage.getItem(key);
    if(!val){
        return val;
    }
    val =JSON.parse(val);
    if(Date.now()-val.time>val.expire){
        localStorage.removeItem(key);
        return null;
    }
    return val.data;
}


// 测试
// localStorage.setExpire("token",'xxxxxx',expireTime);
// localStorage.getExpire("token")




// 5、获取url中指定参数：
function getUrlParam(url,paramName) {
    let name,value;
    let num=url.indexOf("?")
    url=url.substr(num+1);
    let arr=url.split("&");
    console.log(arr)
    for(let i=0;i < arr.length;i++){
        num=arr[i].indexOf("=");
        if(num>0){
            name=arr[i].substring(0,num);
            value=arr[i].substr(num+1);
            if (name==paramName) {
                return value;
                break;
            }
            this[name]=value;
        }
    }
}
// 调用：
// let url=location.href
// getUrlParam(url,'orderId')
// console.log('orderId',getUrlParam(str,'orderId'))



// 4、传参：
// <a :href="getGoodsHref(item.id)" class="title">
// methods: {
//     getGoodsHref:function(val){
//         return 'http://www.xxx.com/index.html?orderId='+val
//     }
// },