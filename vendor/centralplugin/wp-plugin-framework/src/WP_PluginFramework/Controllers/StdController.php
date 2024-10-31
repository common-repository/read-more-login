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

use WP_PluginFramework\HtmlComponents\StatusBar;
use WP_PluginFramework\Models\Model;

class StdController extends FormController
{
    public function StdSubmit_click()
    {
        $data_record = $this->View->GetValues();

        if($this->Model->ValidateDataRecord($data_record))
        {
            $this->HandleValidationSuccess($data_record);
        }
        else
        {
            $this->HandleValidationErrors($data_record);
        }
    }

    public function HandleValidationSuccess($data_record)
    {
        if($this->Model->SetDataRecord($data_record))
        {
            $this->View->HideInputErrorIndications();

            if($this->HandleSave($data_record))
            {
                $this->HandleSaveSuccess($data_record);
            }
            else
            {
                $this->HandleSaveErrors($data_record);
            }
        }
    }

    public function HandleValidationErrors($data_record)
    {
        $errors = $this->Model->GetValidateErrors();

        if($errors)
        {
            $required_errors = array();
            $invalid_errors = array();
            $other_error_message = array();

            $ok_components = $this->View->GetFormInputComponent();

            foreach ($errors as $key => $error)
            {
                $component = $this->View->GetFormInputComponent($key);
                if ($component)
                {
                    $component->AddInputClass('wpf-input-error');

                    if (($i = array_search($component, $ok_components)) !== false) {
                        unset($ok_components[$i]);
                    }

                    if (is_array($error))
                    {
                        /* Ony pop one first error message. Other will not be displayed. */
                        $error = array_pop($error);
                    }

                    if (is_string($error))
                    {
                        $other_error_message[] = $error;
                    }
                    elseif (is_integer($error))
                    {
                        $label = $component->GetProperty('label');
                        switch ($error)
                        {
                            case Model::VALIDATION_ERROR_REQUIRED_FIELD:
                                $required_errors[] = $label;
                                break;

                            case Model::VALIDATION_ERROR_INVALID:
                                $invalid_errors[] = $label;
                                break;
                        }

                    }
                }
            }

            $message = '';

            if(!empty($required_errors))
            {
                $labels = implode(', ', $required_errors);
                /* translators: %s: Lists missing input fields. */
                $message .= esc_html(sprintf(esc_html__('Error. Required field missing: %s.','read-more-login'), $labels));
            }

            if(!empty($invalid_errors))
            {
                if($message) {
                    $message .= ' ';
                }
                $labels = implode(', ', $invalid_errors);
                /* translators: %s: Lists entered input fields having errors. */
                $message = esc_html(sprintf(esc_html__('Error. Invalid data: %s.','read-more-login'), $labels));
            }

            if(!empty($other_error_message))
            {
                if($message) {
                    $message .= ' ';
                }
                $other_error_message = implode(' ', $other_error_message);
                $message .= $other_error_message;
            }

            if(!$message)
            {
                $message = esc_html__('Error. Invalid input data.', 'read-more-login');
            }

            $this->View->StatusBarFooter->SetStatusText($message, StatusBar::STATUS_ERROR);

            foreach ($ok_components as $component)
            {
                $component->HideInputErrorIndication();
            }
        }
        else
        {
            $this->View->StatusBarFooter->SetStatusText('Error. Invalid data.', StatusBar::STATUS_ERROR);
        }
    }

    public function HandleSave($data_record)
    {
        return $this->Model->SaveData();
    }

    public function HandleSaveSuccess($data_record)
    {
        $this->View->StatusBarFooter->SetStatusText('Your settings have been saved.', StatusBar::STATUS_SUCCESS);
    }

    public function HandleSaveErrors($data_record)
    {
        $this->View->StatusBarFooter->SetStatusText('Error saving data.', StatusBar::STATUS_ERROR);
    }
}
