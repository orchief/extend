<?php
namespace com;

class MyTree{
    private $pidName = 'Pid';
    private $id = 'Id';
    private $children = 'children';
    private $data;

    public function __construct($data)
    {
        $this -> data = $data;
    }
    /**
     * 生成树形数据
     *
     * @return void
     */
    public function tree($pid = 0)
    {
        $res = [];
        foreach($this -> data as $k => $v){
            if(null == $v[$this -> pid] ){
                $v[$this -> children] = $this -> tree($v[$this -> id]);
                $res[] = $v;
            }
        }
        return $res;
    }
}