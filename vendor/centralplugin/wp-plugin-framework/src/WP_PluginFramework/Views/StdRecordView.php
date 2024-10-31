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

use WP_PluginFramework\HtmlElements\Table;
use WP_PluginFramework\HtmlElements\Tr;
use WP_PluginFramework\HtmlElements\Td;
use WP_PluginFramework\HtmlElements\Div;
use WP_PluginFramework\HtmlElements\InputButton;

class StdRecordView extends StdView
{
    protected $buttons = array();

    public function __construct($id, $controller)
    {
        parent::__construct($id, $controller);

        $button = array(
            'value' => 'Save',
            'name' => 'Update',
        );
        $this->AddButtons($button);
    }

    public function AddButton($name, $label)
    {
        $button = array(
            'value' => $label,
            'name' => $name,
        );
        $this->AddButtons($button);
    }

    public function AddButtons($button)
    {
        $this->buttons[] = $button;
    }

    public function CreateContent($parameters=null)
    {
        parent::CreateContent($parameters);
    }

    public function DrawStdView($dataobj_records)
    {
        $this->AddDataObject($dataobj_records);
        $this->CreateContent();
        return $this->DrawHtml();
    }
}
