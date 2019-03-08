<?php

// +----------------------------------------------------------------------
// | Description: 推送通知
// +----------------------------------------------------------------------
// | Author:  orchief
// +----------------------------------------------------------------------

namespace Express;

class Express
{
    /**
     *快递接口
     */
    static public function get($num,$company)
    {
        //获取配置的物流数据
        $exp=setting('EXPRESS');
        $post_data = array();
        $post_data["customer"] = $exp['customer'];
        $key= $exp['key'];
        $data['com']=$company;  //查询的快递公司的编码， 一律用小写字母
        $data['num']=$num;  //查询的快递单号， 单号的最大长度是32个字符 358263398950
        $post_data["param"] =json_encode($data);
        $post_data["sign"] = md5($post_data["param"].$key.$post_data["customer"]);
        $post_data["sign"] = strtoupper($post_data["sign"]);
        $o="";
        foreach ($post_data as $k=>$v)
        {
            $o.= "$k=".urlencode($v)."&";      //默认UTF-8编码格式
        }
        $post_data=substr($o,0,-1);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $exp['sendUrl']);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT,3);
        $result = curl_exec($ch);
        $data = json_decode($result,true);
        return $data;
    }
}
