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


use app\admin\controller\Fund;
use app\common\util\forms\input;

class UserOrders extends HouseUserBase
{

    private $house_appointment = "house_appointment";


    public function index($status = 0)
    {

        $where = [];

        $user_id = $this->user_id;
        $where['user_id'] = $user_id;

        $this->view->lists = set_model('orders')->where($where)->order('create_time desc')->paginate();

        return $this->view->fetch();
    }

    public function create()
    {

        if (!$this->agent) {
            $this->error("对不起，您还不是经济人，先去申请一下吧");
        }

        $appoint_model = set_model($this->house_appointment);
        $model_info = $appoint_model->model_info;

        if ($this->isPost(true)) {

            $base_info = input('param.data/a');
            $res = $model_info->add_content($base_info);
            $base_info['user_id'] = 0; // 清空uid
            $base_info['agent_id'] = $this->agent['id'];
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'] . " 感谢您信息，我们将尽快处理，祝您生活愉快！", 1, 'true', 1000, "'/house/user'", "''");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }
        } else {
            $this->view->field_list = $model_info->get_user_publish_fields();
            return $this->view->fetch();
        }


    }

    /**
     * 消费房宝查看
     * @param $id 房源id
     * @param $type 类型  1-租房 2-二手房
     */
    public function pay_for_see()
    {
        $id = trim(input('param.id'));
        $type = trim(input('param.type'));
        /**
         * 1.检查房宝余额
         * 2.余额不足，提示余额不足
         * 3.余额充足，则消费对应余额
         * 4.写入对应查看权限记录
         */
        //检查房宝余额
        $balance = $this->user['balance'];
        $fb_value = config("pay.fangbao_ratio");

        $left_value = $balance - $fb_value;
        if ($balance <= 0.00 || $left_value < 0.00) {
            $this->zbn_msg("余额不足，请先去充值");
            return false;
        }

        $model_name = "";
        $base_info['user_id'] = $this->user_id;
        if ($type == 1) {//租房
            $model_name = 'house_rent_order';
            $model = set_model($model_name);
            $base_info['rent_id'] = $id;
        } else if ($type == 2) {//二手房
            $model_name = "house_esf_order";
            $model = set_model($model_name);
            $base_info['esf_id'] = $id;
        }
        $fund = new Fund();
        $info = $model->field('id')->find();
        $data['seller_user_id'] = $info['user_id'];
        $data['amount'] = $fb_value;
        $data['source_type'] = 2;
        $data['source_id'] = $this->user_id;
        $data['note'] = '支付查看消费';
        $result_json = $fund->fangbao_pay($data);
        //房宝消费订单号获取，生产真正的消费记录
        $fb_order_id = "";
        if ($result_json['result'] == 0) {
            $fb_order_id = $result_json['data']['order_id'];
        } else {
            $this->zbn_msg($result_json['reason'], 2);
            return false;
        }

        $base_info['order_id'] = $fb_order_id;
        $res = $model->add_content($base_info);
        if ($res['code'] != 1) {
            $this->zbn_msg($res['msg'], 2);
            return false;
        }else{
            return $this->view-$this->fetch();
        }
    }

    /**
     * 房宝退款申请
     * @param $order_id 消费权限订单id
     */
    public function refund_fangbao($id, $type)
    {
        $where['user_id'] = $this->user_id;
        if ($type == 1) {//租房
            $model_name = 'house_rent_order';
            $model = set_model($model_name);
            $where['rent_id'] = $id;
            $m = $model->where($where)->find();
        } else if ($type == 2) {//二手房
            $model_name = "house_esf_order";
            $model = set_model($model_name);
            $where['esf_id'] = $id;
            $m = $model->where($where)->find();
        }
        $order_id = $m['order_id'];
        if (isset($order_id)) {

        } else {
            return false;
        }

    }

}