<?php

namespace Bank;

class Bank{
    /**
     * 通过银行编码获取银行名称
     *
     * @return void
     */
    static function getBankNameByCode($code){
        $bankListStr = file_get_contents(env('root_path') . 'extend/Bank/bankList.json');
        $bankListArr = json_decode($bankListStr, true);
        try{
            return $bankListArr[$code];
        }catch(\Exception $e){
            return null;
        }
    }
} 