<?php
/**
 * WordPress Plugin MVC Framework Library
 *
 * Copyright (C) 2018 Arild Hegvik.
 *
 * GNU LESSER GENERAL PUBLIC LICENSE (GNU LGPLv3)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace WP_PluginFramework\Views;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\HtmlElements\Div;
use WP_PluginFramework\HtmlComponents\StatusBar;
use WP_PluginFramework\HtmlComponents\NavTabMenu;
use WP_PluginFramework\HtmlElements\H;

class AdminPanelStdView extends AdminStdView
{
    /** @var StatusBar */
    public $AdminStatusBar;
    /** @var array */
    protected $NavTabs = null;
    /** @var array */
    protected $MyTabName = null;
    /** @var Div */
    protected $StdViewSection = null;
    /** @var Div */
    protected $TopHeaderSection = null;
    /** @var Div */
    protected $BottomFooterSection = null;
    /** @var H */
    protected $PageHeader = null;

    public function __construct($id, $controller)
    {
        parent::__construct($id, $controller);

        $this->AddComponent('AdminStatusBar', new StatusBar(StatusBar::TYPE_REMOVABLE_BLOCK));

        $this->DivWrapper = ['class' => 'wpf-admin-area-cell'];

        $this->ContentConfig['form_input_layout'] = 'double_column_table';
        $this->ContentConfig['form_placeholder_table_attr'] = array('class' => 'form-table');
        $this->ContentConfig['form_placeholder_tr_attr']  = null;
        $this->ContentConfig['form_placeholder_th_attr'] = array('class' => 'row');
        $this->ContentConfig['form_placeholder_td_attr'] = null;

        $this->ContentConfig['form_input_encapsulation'] = null;

        $this->ContentConfig['form_input_width'] = '100%';
    }

    public function SetHeader($header)
    {
        $this->PageHeader = new H(1, $header);
    }

    public function AddNavTab($navtab)
    {
        $this->NavTabs[] = $navtab;
    }

    public function SetTabName($name)
    {
        $this->MyTabName = $name;
    }

    public function SetFooterSection($footer)
    {
        $this->BottomFooterSection = $footer;
    }

    public function AddAdminColumns()
    {
        $std_view_attr = ['class' => 'wpf-admin-area-col2 wpf-admin-area-collapsible'];
        $this->StdViewSection = new Div(null, $std_view_attr);
    }

    public function CreateContent($parameters=null, $wrapper=null)
    {
        if (!isset($wrapper))
        {
            $wrapper = $this;
        }

        $attributes['class'] = 'wrap';
        $div_wrapper = new Div(null, $attributes);
        $wrapper->AddContent($div_wrapper);
        $wrapper = $div_wrapper;

        if(isset($this->PageHeader))
        {
            $wrapper->AddContent($this->PageHeader);
        }

        if(isset($this->TopHeaderSection))
        {
            $wrapper->AddContent($this->TopHeaderSection);
        }

        if(isset($this->NavTabs))
        {
            $attributes['class'] = 'nav-tab-wrapper';
            $navtab = new NavTabMenu($this->NavTabs, $this->MyTabName, $attributes);
            $wrapper->AddContent($navtab, $this->ContentConfig);
        }

        $wrapper->AddContent($this->AdminStatusBar);

        if(isset($this->StdViewSection))
        {
            $wrapper->AddContent($this->StdViewSection);
            $wrapper = $this->StdViewSection;
        }

        parent::CreateContent($parameters, $wrapper);

        if(isset($this->BottomFooterSection))
        {
            $wrapper->AddContent($this->BottomFooterSection);
        }
    }
}
