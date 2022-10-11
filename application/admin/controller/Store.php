<?php

namespace app\admin\controller;

use app\admin\model\Commodity;
use think\App;
use think\Db;
use think\Controller;
use think\Exception;
use think\facade\Validate;

class Store extends Controller
{
    public $commodity;
    public function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->commodity = new Commodity();
    }

    public function index($page = 1)
    {
        $page = request()->get('page');

        $data = Db::name('commodity')->paginate(5, false, ["page" => $page]);
        $MaxPage = $data->lastPage();
        if ($page > $MaxPage) {
            return "不要超过最大值";
        }
        return  $this->fetch('admin/storehouse',
                [
                    "data"          => $data,
                    "MaxPage"       => $MaxPage,
                    'title'         => '商品管理',
                    'classify'      => Db::name('classify')->where('state', 1)->field('classify_name')->select()
                ]
            );
    }

    public function add()
    {
        $name = request()->post('name');
        $price = request()->post('price');
        $num = request()->post('num');
        $classify = request()->post('classify');
        $classify_id = Db::name('classify')->where('classify_name', $classify)->find()['classify_id'];
        try {
            $this->commodity->insert(
                [
                    'comm_name'             => $name,
                    'comm_quantity'         => (int) $num,
                    'comm_reserve'          => (int) $price,
                    'classify_id'           => $classify_id
                ]
            );
            return 'success';
        } catch (Exception $e) {
            $this->error($e);
        }
    }

    public function del()
    {
        $del_id = request()->post('id');
        try {
            $this->commodity->delete((int) $del_id);
            return 'success';
        } catch (Exception $e) {
            return $e;
        }
    }

    public function clear($page = 1)
    {
        $getPage = $this->commodity->where('comm_reserve', 0)->paginate(3, false, ['page' => $page]);
        $max_page = $getPage->lastPage();
        if ($page > $max_page) {
            $this->error("没有库存为空的商品");
        }

        $rule = ['page' => 'integer'];
        $msg  = ['page.integer' => '页码必须是数字,整数'];
        $validate   = Validate::make($rule, $msg);
        $result = $validate->check(['page' => $page]);
        if (!$result) {
            $this->error($validate->getError());
        }

        return $this->fetch('admin/clear', [
            'title'     => '库存为空'
        ]);
    }

    public function update()
    {
        return $this->fetch('admin/edit', [
            "title"                 => '修改',
            'classify'              => Db::name('classify')->where('state', 1)->field('classify_name')->select()
        ]);
    }

    public function clean()
    {
        return json(Db::name('commodity')->where(''));
    }


    public function editor()
    {
        return $this->fetch('admin/editor');
    }

    public function test()
    {
        return $this->commodity->clear();
    }
}
