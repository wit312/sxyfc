<?php

namespace app\member\controller;

use app\common\controller\ModuleUserBase;
use app\core\util\MhcmsMenu;

class Index extends ModuleUserBase
{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
    }

    public function index()
    {
        $this->view->seo = $this->seo($this->mapping);

        $menu = new MhcmsMenu();
        $this->view->menus = $menu->get_member_menu("member");

        $where = [];
        $where['is_app'] = 1;
        $this->view->modules = set_model("modules")->where($where)->select();

        return $this->view->fetch();
    }

    public function main()
    {
        return $this->view->fetch();
    }
}