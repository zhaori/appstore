<?php

namespace app\admin\controller;
use app\admin\model\CommodityModel;
use think\Db;
use think\Controller;
use think\Exception;

class Store extends Controller
{
    public function index(){
        return  $this
                ->fetch('admin/Commodity/storehouse',
                [
                    'title'              => '商品管理',
                    'classify'           => Db::name('classify')->field('classify_name')->select()            
                ]);
    }

    public function add(){
        $name = request()->post('name');
        $price = request()->post('price');
        $num = request()->post('num');
        $classify = request()->post('classify');
        $classify_id = Db::name('classify')->where('classify_name', $classify)->find()['classify_id'];
        try{
            Db::name('commodity')
            ->insert(
                [
                'comm_name'             =>$name,
                'comm_quantity'         =>(int)$num,
                'comm_reserve'          =>(int)$price, 
                'classify_id'           =>$classify_id
            ]);
            return 'success';
        }catch(Exception $e){
            $this->error($e);
        }
            
        // $this->commodity_list = Db::name('commodity')->select();
    }

    public function del(){
        
        $del_id = request()->post('id');
        try{
            Db::name('commodity')->delete((int)$del_id);
            return 'success';
        }catch(Exception $e){
            return $e;
        }
        
    }

    public function false_frame($page=1){
        $page = request()->get('page');
        
        $data = Db::name('commodity')->paginate(5, false, ["page"=>$page]);
        $MaxPage = $data->lastPage();
        if($page>$MaxPage){
            return "不要超过最大值";
        }elseif($page==1){
            return redirect('admin/Commodity/storehouse');
        }

        return $this->fetch('admin/Commodity/storehouse-list',
            [
               "data"       =>$data,
               "MaxPage"    =>$MaxPage
            ]
        );
    }

    public function clear(){
        $update_id = request()->post("id");
        try{
            $data = Db::name('commodity')->where('comm_id', $update_id)->select()[0];

            // return $data["comm_name"];
        }catch(Exception $e){
            return $e;
        }
    }

    public function edit(){
        return $this->fetch('admin/edit',[
            "title"                 =>'修改',
            'classify'              => Db::name('classify')->field('classify_name')->select()            
        ]);
    }
    
    public function clean(){
        return json(Db::name('commodity')->where(''));
    }


    public function test(){
        return (new CommodityModel())->clear();
//        $data = Db::name('commodity')->paginate(5, false, ["page"=>$page]);
//        return json($data);
    }

}