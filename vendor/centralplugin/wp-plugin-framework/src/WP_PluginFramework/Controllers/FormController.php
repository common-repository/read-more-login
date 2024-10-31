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

namespace WP_PluginFramework\Controllers;

defined( 'ABSPATH' ) || exit;

class FormController extends Controller
{
    protected function LoadFormValues($values = array())
    {
        $values = parent::LoadFormValues($values);

        foreach($_POST as $key => $value)
        {
            if((is_string($key)) and (is_string($value)))
            {
                if (($key[0] != '_') and (!($key == 'action')))
                {
                    /* jQuery ajax post adds double slashes */
                    $values[$key] = stripslashes($value);
                }
            }
        }

        if (isset($this->View))
        {
            $this->View->ClearCheckboxBeforeFormPost();
        }

        return $values;
    }

    protected function InitView($values)
    {
        parent::InitView($values);

        $this->View->SetValues($values);
    }
}
