<?php
// +----------------------------------------------------------------------
// | 鸣鹤CMS [ New Better  ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2017 http://www.mhcms.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( 您必须获取授权才能进行使用 )
// +----------------------------------------------------------------------
// | Author: new better <1620298436@qq.com>
// +----------------------------------------------------------------------
namespace app\house\controller;

use app\common\controller\HomeBase;
use app\common\model\Hits;
use app\common\model\Models;
use app\core\util\ContentTag;
use think\Db;
use think\Log;

class Esf extends HouseBase
{
    private $house_esf = "house_esf";

    public function index()
    {
        $select = array();
        $select['zhuangxiu'] = array('装修', '毛坯', '简装', '精装', '豪装');
        $select['huxing'] = array('0室', '1室', '2室', '3室', '4室', '5室');
        $select['ting'] = array('0厅', '1厅', '2厅', '3厅', '4厅', '5厅');

        $options = Db::table('mhcms_option')->where(['model_id' => '553'])->field('id,option_name,field_name')->select()->toArray();
        foreach ($options as $value) {
            if ($value['field_name'] == 'tag') {
                $select['tags'][$value['id']] = $value['option_name'];
            }
        }

        foreach ($options as $value) {
            if ($value['field_name'] == 'use') {
                $select['yongtu'][$value['id']] = $value['option_name'];
            }
        }

        foreach ($options as $value) {
            if ($value['field_name'] == 'price') {
                $select['jiage'][$value['id']] = $value['option_name'];
            }
        }

        foreach ($options as $value) {
            if ($value['field_name'] == 'size') {
                $select['size'][$value['id']] = $value['option_name'];
            }
        }

        // 筛选条件
        $where = array();
        if ($_GET['area_province'] != null) {
            $area = 1;
            if ($_GET['area_province'] != null) $area = $_GET['area_province'];
            if ($_GET['area_city'] != null) $area = $_GET['area_city'];
            if ($_GET['area_area'] != null) $area = $_GET['area_area'];
            $where['mhcms_house_esf.area_id'] = $area;
            $this->assign('area', $_GET['area_province']);
        }
        if ($_GET['xiaoqu'] != null) {
            $where['mhcms_house_esf.xiaoqu_id'] = $_GET['xiaoqu'];
            $this->assign('xiaoqu', $_GET['xiaoqu']);
        }
        if (!empty($_GET['yongtu'])) {
            $where['mhcms_house_esf.yongtu'] = $_GET['yongtu'];
            $this->assign('yongtu', $_GET['yongtu']);
        }
        if (!empty($_GET['zhuangxiu'])) {
            $where['mhcms_house_esf.zhuangxiu'] = $_GET['zhuangxiu'];
            $this->assign('zhuangxiu', $_GET['zhuangxiu']);
        }
        if (!empty($_GET['tag'])) {
            $where['mhcms_house_esf.tags'] = array('LIKE', '%' . $_GET['tag'] . '%');
            $this->assign('tag', $_GET['tag']);
        }
        if (!empty($_GET['jiage'])) {
            $where['mhcms_house_esf.price'] = $_GET['jiage'];
            $this->assign('jiage', $_GET['jiage']);
        }
        if ($_GET['huxing'] != null) {
            $where['mhcms_house_esf.shi'] = $_GET['huxing'];
            $this->assign('huxing', $_GET['huxing']);
        }
        if ($_GET['ting'] != null) {
            $where['mhcms_house_esf.ting'] = $_GET['ting'];
            $this->assign('ting', $_GET['ting']);
        }
        if (!empty($_GET['size'])) {
            $where['mhcms_house_esf.size'] = $_GET['size'];
            $this->assign('size', $_GET['size']);
        }

        $model = set_model('house_esf');
        if (($_GET['huxing'] != null) || $_GET['tag'] || $_GET['zhuangxiu'] || $_GET['yongtu'] || $_GET['area_province'] || $_GET['xiaoqu'] || $_GET['size'] || $_GET['jiage'] || ($_GET['ting'] != null)) {
            $query = array('huxing'=>$_GET['huxing'],'tag'=>$_GET['tag'],'zhuangxiu'=>$_GET['zhuangxiu'],'yongtu'=>$_GET['yongtu'],'xiaoqu'=>$_GET['xiaoqu'],'size'=>$_GET['size'],'jiage'=>$_GET['jiage'],'ting'=>$_GET['ting']);
            $this->view->lists = $model->join('mhcms_file', 'mhcms_file.file_id=mhcms_house_esf.thumb')->where($where)->order('mhcms_house_esf.update_at desc')->paginate(config('list_rows'),false, ['query' => $query]);
        } else {
            $this->view->lists = $model->join('mhcms_file', 'mhcms_file.file_id=mhcms_house_esf.thumb')->where($where)->order('mhcms_house_esf.update_at desc')->paginate();
        }

        //设置筛选数据
        $area_data = set_model('area')->order(['parent_id'=>'asc'])->field('id,area_name,parent_id')->select()->toArray();
        $xiaoqu_data = set_model('house_xiaoqu')->field('id,xiaoqu_name')->select()->toArray();
        $area_province = array();
        foreach ($area_data as $area_item) {
            if ($area_item['parent_id'] == 0) {
                array_push($area_province, $area_item);//省
                $key = array_search($area_item, $area_data);
                array_splice($area_data, $key, 1);
            }
        }
        $this->assign('area_data', json_encode($area_data));
        $this->assign('area_province', json_encode($area_province));
        $this->assign('xiaoqu_data', $xiaoqu_data);
        $this->assign('select', $select);

        return $this->view->fetch();
    }


    public function detail($id)
    {
        global $_W;
        $content_model_id = $this->house_esf;
        $model = set_model($content_model_id);
        $model_info = $model->model_info;

        $detail = Models::get_item($id, $content_model_id);
        $this->view->detail = $detail;
        $this->view->field_list = $model_info->get_admin_publish_fields($detail, []);
        Hits::hit($id, $this->house_esf);
        if ($this->user_id) {
            Hits::log($id, $this->house_esf, $this->user_id);
        }
//        $this->mapping = array_merge($this->mapping, $detail);
//        $this->view->seo = $this->seo($this->mapping);
//        $this->view->user_verify = set_model("users_verify")->where(['user_id' => $detail['user_id']])->find();

        //设置可见权限：支付查看信息
        $show_power = true;
        //设置支付查看交易结果
        $user_id = $this->user_id;
        $esf_id = $id;

        if ($result = Db::table('mhcms_house_esf_order')->where(['user_id' => $user_id, 'esf_id' => $esf_id])->find()) {
            $pay_result = true;
        } else {
            $pay_result = false;
        }

        $agent = Db::table('mhcms_house_esf')->where(['id' => $id])->find();

        if ($agent['user_id']) {
            $user_info = Db::table('mhcms_users')->where(['id' => $agent['user_id']])->find();
            $mobile = $user_info['mobile'];
        } else {
            $mobile = '';
        }

        $this->assign("mobile", $mobile);
        //查询对应表，通过esf_id和user_id
        $this->assign("pay_result", $pay_result);
        $this->assign("show_power", $show_power);
        return $this->view->fetch();
    }


    /**
     * 二手房一键导入
     * @param $id
     * @return void
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function autoAdd()
    {
        $esf_id = trim(input('param.id'));
        $user_id = $this->user['id'];
        $base_info['user_id'] = $where['user_id'] = $user_id;
        $base_info['esf_id'] = $where['esf_id'] = $esf_id;

        $url = '/house/esf/detail/id/' . $esf_id;

        if ($result = Db::table('mhcms_user_esf')->where($where)->find()) {
            echo "<script> alert('请勿重复导入！'); </script>";
            echo "<meta http-equiv='Refresh' content='0;URL=$url'>";
            exit();
        }

        $res = Db::table('mhcms_user_esf')->insert($base_info);
        if ($res) {
            echo "<script> alert('导入成功！'); </script>";
            echo "<meta http-equiv='Refresh' content='0;URL=$url'>";
            exit();
        } else {
            echo "<script> alert('导入失败，请稍后再试！'); </script>";
            echo "<meta http-equiv='Refresh' content='0;URL=$url'>";
            exit();
        }
    }
}