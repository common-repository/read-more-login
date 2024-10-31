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

use WP_PluginFramework\Models\Model;
use WP_PluginFramework\Views\FormView;
use WP_PluginFramework\PluginContainer;
use WP_PluginFramework\Utils\SecurityFilter;
use WP_PluginFramework\Utils\DebugLogger;

abstract class Controller
{
    const EVENT_TYPE_NONE = 0;
    const EVENT_TYPE_POST = 'post';
    const EVENT_TYPE_GET = 'get';
    const EVENT_TYPE_CLICK = 'click';
    const EVENT_TYPE_CALLBACK = 'callback';
    const EVENT_TYPE_INIT = 'init';

    const EVENT_METHOD_GET = 'get';
    const EVENT_METHOD_POST = 'post';
    const EVENT_METHOD_AJAX = 'ajax';
    const EVENT_METHOD_INIT = 'init';

    const PROTECTED_DATA_WP_NONCE = '_wpnonce';
    const PROTECTED_DATA_CONTROLLER = '_controller';
    const PROTECTED_DATA_PROXY_CONTROLLER = '_proxy_controller';
    const PROTECTED_DATA_VIEW = '_view';

    /* Received data from client side, nonce protected. */
    private $NonceProtectedData = array(
        self::PROTECTED_DATA_WP_NONCE => null,
        self::PROTECTED_DATA_CONTROLLER => null,
        self::PROTECTED_DATA_PROXY_CONTROLLER => null,
        self::PROTECTED_DATA_VIEW => null
    );

    private $ClientContextData = array();
    private $ClientContextData_touched = false;
    private $ServerContextData = array();
    private $ServerContextData_touched = false;
    private $Event = null;
    private $EventType = self::EVENT_TYPE_NONE;
    private $EventSource = null;
    private $InitFunction = null;
    protected $RegisteredEvents = array();

    private $RequiredCapabilities = null;
    private $RequiredLogin = null;

    protected $Id = null;

    /** @var Controller Current active controller as changed by ReloadController. */
    private $_active_controller = null;

    /** @var FormView */
    protected $View = null;
    /** @var Model */
    protected $Model = null;
    protected $AjaxResponse = array();

    public function __construct($model_class=null, $view_class=null, $id=null)
    {
        $this->_active_controller = $this;

        $my_controller_name = get_called_class();
        $this->NonceProtectedData[self::PROTECTED_DATA_CONTROLLER] = $my_controller_name;
        $this->NonceProtectedData[self::PROTECTED_DATA_PROXY_CONTROLLER] = $my_controller_name;

        if(!isset($id))
        {
            /* Replace namespace indicator '\' with '_' to get better names. Will be used in jquery selectors. */
            $id_name = str_replace('\\', '_', $my_controller_name);
            $this->Id = $id_name;
        }
        else
        {
            $this->Id = $id;
        }

        if($model_class)
        {
            $this->Model = new $model_class();
        }

        if($view_class)
        {
            $this->NonceProtectedData[self::PROTECTED_DATA_VIEW] = $view_class;
        }

        $this->ReadEventRequestData();
    }

    public function SetEvent($event)
    {
        $this->Event = $event;
    }

    public function GetEvent()
    {
        return $this->Event;
    }

    public function GetEventType()
    {
        return $this->EventType;
    }

    public function GetEventFunction()
    {
        if(isset($this->Event))
        {
            if(isset($this->EventType))
            {
                if(!($this->EventType === self::EVENT_TYPE_NONE))
                {
                    return $this->Event . '_' . $this->EventType;
                }
            }
        }

        return null;
    }


    public function SetInitCallback($init_function)
    {
        $this->InitFunction = $init_function;
    }

    public function ReloadView($view_class = null, $clear_client_context_data = false)
    {
        if(!isset($view_class))
        {
            $view_class = $this->NonceProtectedData[self::PROTECTED_DATA_VIEW];
        }

        if($view_class)
        {
            $this->AjaxResponse = array();
            if($clear_client_context_data)
            {
                $this->ClientContextData = array();
                $this->ClientContextData_touched = false;
            }

            switch ($this->EventSource)
            {
                case self::EVENT_METHOD_AJAX:
                    $this->View = $this->InstantiateView($view_class);
                    $values = array();
                    $values = $this->LoadModelValues($values);
                    $this->InitView($values);
                    $this->View->RemoveDivWrapper();
                    $html = $this->DrawView();

                    if(!isset($html))
                    {
                        DebugLogger::WriteDebugError('DrawView returned nothing.');
                    }

                    $selector = 'div#' . $this->Id;
                    $this->View->UpdateClientDom($selector, 'html', $html);
                    break;

                case self::EVENT_METHOD_GET:
                case self::EVENT_METHOD_POST:
                    $this->View = $this->InstantiateView($view_class);
                    $values = array();
                    $values = $this->LoadModelValues($values);
                    $this->InitView($values);
                    break;

                default:
                    DebugLogger::WriteDebugError('Unhandled event source ' . $this->EventSource);
                    break;
            }
        }
        else
        {
            DebugLogger::WriteDebugError('No view has been set.');
        }
    }

    /**
     * @param $controller Controller
     * @return null
     */
    public function ReloadController($controller)
    {
        $this->AjaxResponse = array();
        $this->ClientContextData = array();
        $this->ClientContextData_touched = false;

        if(isset($controller))
        {
            if(is_string($controller))
            {
                $controller = new $controller();
            }
        }

        $this->_active_controller = $controller;
        $controller->NonceProtectedData[self::PROTECTED_DATA_PROXY_CONTROLLER] = $this->NonceProtectedData[self::PROTECTED_DATA_PROXY_CONTROLLER];

        switch ($this->EventSource)
        {
            case self::EVENT_METHOD_AJAX:
                $controller->LoadContextData();
                $this->View = $controller->InstantiateView();
                $values = array();
                $values = $controller->LoadModelValues($values);
                $controller->InitView($values);
                $html = $controller->DrawView();

                if(!isset($html))
                {
                    DebugLogger::WriteDebugError('DrawView returned nothing.');
                }

                $selector = 'div#' . $this->Id;
                $this->View->UpdateClientDom($selector, 'replaceWith', $html);
                break;

            case self::EVENT_METHOD_GET:
            case self::EVENT_METHOD_POST:
                $controller->LoadContextData();
                $this->View = $controller->InstantiateView();
                $values = array();
                $values = $controller->LoadModelValues($values);
                $controller->InitView($values);
                break;

            default:
                DebugLogger::WriteDebugError('Unhandled event source ' . $this->EventSource);
                $controller = null;
                break;
        }

        return $controller;
    }

    public function Draw()
    {
        if($this->CheckPermissions())
        {
            $this->LoadContextData();

            $controller = $this;

            $nonceProtectedData = $this->ReadNonceProtectedData($this->EventSource);
            if($nonceProtectedData)
            {
                if($this->CheckWpNonce($nonceProtectedData))
                {
                    /* Don't flood the debug log due to hacking attempt. Nonce now confirmed and we can write to log. */
                    DebugLogger::ContinueWpDebugLogging();

                    $my_controller_class = get_called_class();

                    /* Check if nonce protected data is sent to this controller */
                    if ($my_controller_class == $nonceProtectedData[self::PROTECTED_DATA_PROXY_CONTROLLER])
                    {
                        /* Yes, it is our data and valid. Handle the events. */

                        if($my_controller_class != $nonceProtectedData[self::PROTECTED_DATA_CONTROLLER])
                        {
                            /* The view on client side has been reloaded with another controller. */
                            $controller = new $nonceProtectedData[self::PROTECTED_DATA_CONTROLLER]();

                            $proxy_ctrl = $this->NonceProtectedData[self::PROTECTED_DATA_PROXY_CONTROLLER];
                            $controller->NonceProtectedData[static::PROTECTED_DATA_PROXY_CONTROLLER] = $proxy_ctrl;
                        }

                        /* The nonce protected data is valid, save it to the controller's instance. */
                        $controller->NonceProtectedData = $nonceProtectedData;

                        $controller->InstantiateView();
                        /* Need to have view instantiated to check if it have events registered */

                        $values = $controller->LoadValues();
                        $controller->InitView($values);

                        if ($controller->CheckEventExist($controller->Event, $controller->EventType, $controller->EventSource))
                        {
                            $controller = $controller->EventHandler();
                        }
                        else
                        {
                            DebugLogger::WriteDebugError('Invalid ajax event: Event=' . $this->Event . ' EventType=' . $this->EventType . ' EventSource=' . $this->EventSource);
                        }
                    }
                    else
                    {
                        /* Nonce protected data not for us, only load view and do not handle events. */
                        $controller->InstantiateView();
                        /* Can not load values from client, it belongs to some other controllers. Load model value only. */
                        $values = $controller->LoadModelValues(null);
                        $controller->InitView($values);
                    }
                }
                else
                {
                    DebugLogger::WriteDebugNote('Invalid wpnonce. (Or nonce expired because user logged out and in again.)');

                    $controller->InstantiateView();
                    /* Can not load values from client, it belongs to some other controllers. Load model value only. */
                    $values = $controller->LoadModelValues(null);
                    $controller->InitView($values);
                }
            }
            else
            {
                $controller->LoadContextData();
                $controller->InstantiateView();
                /* Can not load values from client, it belongs to some other controllers. Load model value only. */
                $values = $controller->LoadModelValues(null);
                $controller->InitView($values);
            }

            $response = $controller->DrawView();

            if (!isset($response))
            {
                DebugLogger::WriteDebugError('DrawView returned nothing.');
            }
        }
        else
        {
            $response = esc_html__('Error: Invalid permission.', 'read-more-login');
        }

        return $response;
    }

    public function AjaxHandler()
    {
        $response = array();

        $this->EventSource = self::EVENT_METHOD_AJAX;

        if($this->CheckPermissions())
        {
            $this->LoadContextData();

            $nonceProtectedData = $this->ReadNonceProtectedData($this->EventSource);
            if($nonceProtectedData)
            {
                if ($this->CheckWpNonce($nonceProtectedData))
                {
                    /* Don't flood the debug log due to hacking attempt. Nonce now confirmed and we can write to log. */
                    DebugLogger::ContinueWpDebugLogging();

                    $this->NonceProtectedData = $nonceProtectedData;

                    $this->InstantiateView();
                    /* Need to have view instantiated to check if it have events registered */

                    if ($this->CheckEventExist($this->Event, $this->EventType, $this->EventSource))
                    {
                        $values = $this->LoadValues();
                        $this->InitView($values);

                        $this->EventHandler();

                        $response['result'] = 'ok';

                        $work_items = $this->GetViewResponse();
                        if (isset($work_items))
                        {
                            if (!empty($work_items))
                            {
                                $this->AjaxResponse = array_merge($this->AjaxResponse, $work_items);
                            }
                        }

                        if (!is_array($this->ClientContextData))
                        {
                            /* Must only send array, or client javascript will not understand. */
                            $this->ClientContextData = array();
                        }

                        $response['context_data'] = $this->ClientContextData;
                        $response['work'] = $this->AjaxResponse;
                    }
                    else
                    {
                        $response['result'] = 'error';
                        $response['message'] = esc_html__('Error: Invalid server request.', 'read-more-login');
                        DebugLogger::WriteDebugError('Invalid ajax event: Event=' . $this->Event . ' EventType=' . $this->EventType . ' EventSource=' . $this->EventSource);
                    }
                }
                else
                {
                    $response['result'] = 'error';
                    $response['message'] = esc_html__('Error: Session has expired. Please reload page.', 'read-more-login');
                    DebugLogger::WriteDebugNote('Invalid wpnonce. (Or nonce expired because user logged out and in again.)');
                }
            }
            else
            {
                $response['result'] = 'error';
                $response['message'] = esc_html__('Error: Invalid server request.', 'read-more-login');
                DebugLogger::WriteDebugError('Missing nonce.');
            }

        }
        else
        {
            $response['result'] = 'error';
            $response['message'] = esc_html__('Error: Invalid permission.', 'read-more-login') . $this->InvalidPermissionsMessage();
        }

        $response_json = json_encode($response);

        return $response_json;
    }

    public function InitHandler($init_event)
    {
        if($this->CheckPermissions())
        {
            $this->LoadContextData();

            $nonceProtectedData = $this->ReadNonceProtectedData($this->EventSource);
            if($nonceProtectedData)
            {
                if ($this->CheckWpNonce($nonceProtectedData))
                {
                    /* Don't flood the debug log due to hacking attempt. Nonce now confirmed and we can write to log. */
                    DebugLogger::ContinueWpDebugLogging();

                    $this->NonceProtectedData = $nonceProtectedData;

                    $this->InstantiateView();

                    if ($this->CheckEventExist($init_event, 'init'))
                    {
                        if ($this->CheckEventExist($this->Event, $this->EventType))
                        {
                            $values = $this->LoadValues();
                            $this->InitView($values);

                            $init_function = $init_event . '_init';
                            $event_function = $this->Event . '_' . $this->EventType;

                            $no_response = $this->$init_function($event_function);

                            if (isset($no_response))
                            {
                                DebugLogger::WriteDebugError('Non-expected return from ' . $init_function);
                            }
                        }
                    }

                    $this->SaveContextData();
                }
            }
        }
    }

    protected function SetPermission($required_login, $capabilities=null)
    {
        $this->RequiredLogin = $required_login;
        $this->RequiredCapabilities = $capabilities;
    }

    protected function CheckPermissions()
    {
        $permission_ok = true;

        if(isset($this->RequiredLogin))
        {
            if ($this->RequiredLogin === true)
            {
                if (!is_user_logged_in())
                {
                    DebugLogger::WriteDebugNote('Invalid permission. Not logged in. (Or could be indication of hacking.)');
                    $permission_ok = false;
                }
            }

            if (isset($this->RequiredCapabilities))
            {
                if (!current_user_can($this->RequiredCapabilities))
                {
                    DebugLogger::WriteDebugNote('Invalid permission. No capabilities. (Or could be indication of hacking.)');
                    $permission_ok = false;
                }
            }
        }
        else
        {
            DebugLogger::WriteDebugError('Permission not defined.');
            $permission_ok = false;
        }

        return $permission_ok;
    }

    protected function InvalidPermissionsMessage()
    {
        if(!isset($this->RequiredLogin))
        {
            return esc_html__('Error. Invalid permission.', 'read-more-login');
        }

        if($this->RequiredLogin === true)
        {
            return esc_html__('Error. You are not logged in.', 'read-more-login');
        }

        if(isset($this->RequiredCapabilities))
        {
            if(!current_user_can($this->RequiredCapabilities))
            {
                return esc_html__('Error. Access denied.', 'read-more-login');
            }
        }

        return esc_html__('Undefined error.', 'read-more-login');
    }

    protected function LoadValues($values = array())
    {
        if(isset($_POST['action']) and ($_POST['action'] === PluginContainer::WP_PLUGIN_FRAMEWORK_AJAX_HANDLER))
        {
            $values = $this->LoadFormValues($values);
        }
        else
        {
            $values = $this->LoadModelValues($values);
        }

        return $values;
    }

    protected function LoadModelValues($values = array())
    {
        if(isset($this->Model))
        {
            if($this->Model->LoadData() === 1)
            {
                $values = $this->Model->GetDataRecord();
            }
        }

        return $values;
    }

    protected function LoadFormValues($values = array())
    {
        return $values;
    }

    protected function InstantiateView($view_class=null)
    {
        $view = null;

        if($view_class)
        {
            $this->NonceProtectedData[self::PROTECTED_DATA_VIEW] = $view_class;
        }

        if($this->NonceProtectedData[self::PROTECTED_DATA_VIEW])
        {
            $my_controller_name = get_called_class();

            $this->View = new $this->NonceProtectedData[self::PROTECTED_DATA_VIEW]($this->Id, $my_controller_name);

            $view = $this->View;
        }

        return $view;
    }

    protected function InitView($values)
    {
        if(isset($this->InitFunction))
        {
            $this->View->AddHiddenFields('_init_callback', $this->InitFunction);
        }
    }

    protected function CreateWpNonce()
    {
        $this->NonceProtectedData = $this->CalculateWpNonce($this->NonceProtectedData);
        return $this->NonceProtectedData[self::PROTECTED_DATA_WP_NONCE];
    }

    protected function CalculateWpNonce($nonce_protected_data = array())
    {
        $nonce_string = $this->FormatWpNonceString($nonce_protected_data);

        if($nonce_string)
        {
            $wp_nonce = wp_create_nonce($nonce_string);
            $nonce_protected_data[self::PROTECTED_DATA_WP_NONCE] = $wp_nonce;
            return $nonce_protected_data;
        }
        else
        {
            DebugLogger::WriteDebugError('Failed to create nonce string.');
            return null;
        }
    }

    protected function CheckWpNonce($nonce_protected_data)
    {
        $nonce_string = $this->FormatWpNonceString($nonce_protected_data);

        if($nonce_string)
        {
            $calculated_wpnonce = wp_create_nonce($nonce_string);

            if($calculated_wpnonce === $nonce_protected_data[self::PROTECTED_DATA_WP_NONCE])
            {
                return true;
            }
        }
        else
        {
            DebugLogger::WriteDebugError('Failed to create nonce string.');
        }

        return false;
    }

    protected function FormatWpNonceString($nonce_protected_data)
    {
        $nonce_string = '';
        foreach($nonce_protected_data as $key => $value)
        {
            if($key != self::PROTECTED_DATA_WP_NONCE)
            {
                $nonce_input_type = gettype($value);

                if($nonce_string)
                {
                    $nonce_string .= '_';
                }

                switch($nonce_input_type)
                {
                    case 'string':
                        $nonce_string .= $value;
                        break;

                    case 'integer':
                        $nonce_string .= strval($value);
                        break;

                    default:
                        DebugLogger::WriteDebugError('Undefined type ' . $nonce_input_type . ' for nonce protected data ' . $key);
                        break;
                }
            }
        }

        return $nonce_string;
    }

    protected function ReadNonceProtectedData($event_source)
    {
        $nonceProtectedData = array();

        switch($event_source)
        {
            case 'ajax':
            case 'post':
                $nonceProtectedData[self::PROTECTED_DATA_WP_NONCE] = SecurityFilter::SafeReadPostRequest(self::PROTECTED_DATA_WP_NONCE, SecurityFilter::ALPHA_NUM);

                if(isset($nonceProtectedData[self::PROTECTED_DATA_WP_NONCE]))
                {
                    $controller_class = SecurityFilter::SafeReadPostRequest(self::PROTECTED_DATA_CONTROLLER, SecurityFilter::CLASS_NAME);
                    /* jQuery ajax post adds double slashes */
                    $controller_class = stripslashes($controller_class);
                    $nonceProtectedData[self::PROTECTED_DATA_CONTROLLER] = $controller_class;

                    $controller_class = SecurityFilter::SafeReadPostRequest(self::PROTECTED_DATA_PROXY_CONTROLLER, SecurityFilter::CLASS_NAME);
                    /* jQuery ajax post adds double slashes */
                    $controller_class = stripslashes($controller_class);
                    $nonceProtectedData[self::PROTECTED_DATA_PROXY_CONTROLLER] = $controller_class;

                    $view_class = SecurityFilter::SafeReadPostRequest(self::PROTECTED_DATA_VIEW, SecurityFilter::CLASS_NAME);
                    /* jQuery ajax post adds double slashes */
                    $view_class = stripslashes($view_class);
                    $nonceProtectedData[self::PROTECTED_DATA_VIEW] = $view_class;
                }
                break;

            case 'get':
                $nonceProtectedData[self::PROTECTED_DATA_WP_NONCE] = SecurityFilter::SafeReadGetRequest(self::PROTECTED_DATA_WP_NONCE, SecurityFilter::ALPHA_NUM);

                if(isset($nonceProtectedData[self::PROTECTED_DATA_WP_NONCE]))
                {
                    $controller_class = SecurityFilter::SafeReadGetRequest(self::PROTECTED_DATA_CONTROLLER, SecurityFilter::CLASS_NAME);
                    /* jQuery ajax post adds double slashes */
                    $controller_class = stripslashes($controller_class);
                    $nonceProtectedData[self::PROTECTED_DATA_CONTROLLER] = $controller_class;

                    $controller_class = SecurityFilter::SafeReadGetRequest(self::PROTECTED_DATA_PROXY_CONTROLLER, SecurityFilter::CLASS_NAME);
                    /* jQuery ajax post adds double slashes */
                    $controller_class = stripslashes($controller_class);
                    $nonceProtectedData[self::PROTECTED_DATA_PROXY_CONTROLLER] = $controller_class;

                    $view_class = SecurityFilter::SafeReadGetRequest(self::PROTECTED_DATA_VIEW, SecurityFilter::CLASS_NAME);
                    /* jQuery ajax post adds double slashes */
                    $view_class = stripslashes($view_class);
                    $nonceProtectedData[self::PROTECTED_DATA_VIEW] = $view_class;
                }
                break;

            default:
                break;
        }

        if(isset($nonceProtectedData[self::PROTECTED_DATA_WP_NONCE]))
        {
            return $nonceProtectedData;
        }
        else
        {
            return null;
        }
    }

    protected function GetNonceProtectedData()
    {
        return $this->NonceProtectedData;
    }

    protected function GetViewClass()
    {
        return $this->NonceProtectedData[self::PROTECTED_DATA_VIEW];
    }

    protected function RegisterEvent($event, $event_type, $event_source)
    {
        $event_data = array();
        $event_data['Type'] = $event_type;
        $event_data['Source'] = $event_source;
        $this->RegisteredEvents[$event] = $event_data;
    }

    protected function CheckEventExist($event, $event_type, $event_source=null)
    {
        $event_exist = false;

        if(isset($event) and isset($event_type))
        {
            $correct_event_types = array('click', 'init', 'post', 'get', 'callback');
            if (in_array($event_type, $correct_event_types))
            {
                foreach ($this->RegisteredEvents as $key => $event_data)
                {
                    if ($event === $key)
                    {
                        if ($event_type === '*' or $event_type === $event_data['Type'])
                        {
                            if ((!isset($event_source)) or ($event_source === '*') or ($event_source === $event_data['Source']))
                            {
                                $event_exist = true;
                                break;
                            }
                        }
                    }
                }

                if(!$event_exist)
                {
                    /* If controller has no such event, check views */
                    if (isset($this->View))
                    {
                        $event_exist = $this->View->CheckEventExist($event, $event_type, $event_source);
                    }
                }
            }
            else
            {
                DebugLogger::WriteDebugError('Undefined event type ' .$event_type . ' for event=' . $event);
            }

            if ($event_exist)
            {
                $event_function = $event . '_' . $event_type;

                if (!method_exists($this, $event_function))
                {
                    $event_exist = false;
                    DebugLogger::WriteDebugError('Missing event function ' .$event_function);
                }
            }
            else
            {
                DebugLogger::WriteDebugError('Undefined event event=' . $event . ' type=' . $event_type);
            }
        }

        return $event_exist;
    }

    protected function EnqueueScript()
    {
        $unique_prefix = PluginContainer::GetPrefixedPluginSlug();

        $script_handler = $unique_prefix . '_script_handler';
        $src = plugin_dir_url( __FILE__ ) . '../../../js/view-controller.js';
        wp_enqueue_script($script_handler, $src, array( 'jquery' ), PluginContainer::WP_PLUGIN_MVC_FRAMEWORK_VERSION, true);

        $ajax_handler = '/wp-admin/admin-ajax.php';
        $url_to_my_site = site_url() . $ajax_handler;

        $data_array = array(
            'url_to_my_site' => $url_to_my_site,
            'form_input_selector' => 'input, textarea, select',
            'wp_ajax_function' => PluginContainer::WP_PLUGIN_FRAMEWORK_AJAX_HANDLER,
            'context_data' => $this->ClientContextData
        );
        wp_localize_script($script_handler, 'wp_plugin_framework_script_vars', $data_array);

        $style_handler1 = $unique_prefix . '_style_handler';
        $style_url = plugin_dir_url( __FILE__ ) . '../../../css/style.css';
        $style_version = PluginContainer::WP_PLUGIN_MVC_FRAMEWORK_VERSION;
        wp_enqueue_style($style_handler1, $style_url, array(), $style_version);

        if(is_rtl())
        {
            $style_rtl_handler = $unique_prefix . '_style_rtl_handler';
            $style_rtl_url = plugin_dir_url( __FILE__ ) . '../../../css/style-rtl.css';
            $style_version = PluginContainer::WP_PLUGIN_MVC_FRAMEWORK_VERSION;
            wp_enqueue_style($style_rtl_handler, $style_rtl_url, array(), $style_version);
        }
        else
        {
            $style_ltr_handler = $unique_prefix . '_style_ltr_handler';
            $style_ltr_url = plugin_dir_url( __FILE__ ) . '../../../css/style-ltr.css';
            $style_version = PluginContainer::WP_PLUGIN_MVC_FRAMEWORK_VERSION;
            wp_enqueue_style($style_ltr_handler, $style_ltr_url, array(), $style_version);
        }
    }

    protected function DrawView($parameters=null)
    {
        $this->EnqueueScript();

        $this->CreateWpNonce();
        foreach ($this->NonceProtectedData as $name => $protected_data)
        {
            $this->View->AddHiddenFields($name, $protected_data);
        }

        return $this->View->DrawView($parameters);
    }

    protected function GetViewResponse()
    {
        return $this->View->GetAjaxResponse();
    }

    /* ClientContextData is a buffer keeping data between user interactions.
       Note! ClientContextData will be sent to visitor via AJAX calls.
       Do not store secret data like password in this buffer! */

    protected function SetClientContextData($key, $value)
    {
        $this->ClientContextData[$key] = $value;
        $this->ClientContextData_touched = true;
    }

    protected function GetClientContextData($key)
    {
        if(isset($this->ClientContextData[$key]))
        {
            return $this->ClientContextData[$key];
        }
        else
        {
            return null;
        }
    }

    protected function SetServerContextData($key, $value)
    {
        $this->ServerContextData[$key] = $value;
        $this->ServerContextData_touched = true;
    }

    protected function GetServerContextData($key)
    {
        if(isset($this->ServerContextData[$key]))
        {
            return $this->ServerContextData[$key];
        }
        else
        {
            return null;
        }
    }

    protected function SaveContextData()
    {
        if ($this->ClientContextData_touched)
        {
            /* If we have touched the context data, must save otherwise it will be forgotten. */
            $key = PluginContainer::GetPrefixedPluginSlug() . '_client_context_data';
            $GLOBALS[$key] = $this->ClientContextData;
        }

        if ($this->ServerContextData_touched)
        {
            /* If we have touched the context data, must save otherwise it will be forgotten. */
            $key = PluginContainer::GetPrefixedPluginSlug() . '_server_context_data';
            $GLOBALS[$key] = $this->ServerContextData;
        }
    }

    protected function LoadContextData()
    {
        $key = PluginContainer::GetPrefixedPluginSlug() . '_server_context_data';
        if (isset($GLOBALS[$key]))
        {
            $this->ServerContextData = $GLOBALS[$key];
        }

        $key = PluginContainer::GetPrefixedPluginSlug() . '_client_context_data';
        if (isset($GLOBALS[$key]))
        {
            $this->ClientContextData = $GLOBALS[$key];
        }
        else
        {
            if(isset($_POST['_context_data']))
            {
                if (is_array($_POST['_context_data']))
                {
                    /* Only array is acceptable from client */
                    $this->ClientContextData = $_POST['_context_data'];
                }
            }
        }
    }

    /**
     * @return Controller
     */
    protected function EventHandler()
    {
        if((isset($this->Event)) and (isset($this->EventType)))
        {
            $event_function = $this->GetEventFunction();

            if (method_exists($this, $event_function))
            {
                switch ($this->EventType)
                {
                    case self::EVENT_TYPE_CLICK:
                        $no_response = $this->$event_function();
                        break;

                    case self::EVENT_TYPE_CALLBACK:
                        $arguments = null;
                        if (isset($_POST['_arguments']))
                        {
                            $arguments = $_POST['_arguments'];
                            if (!is_array($arguments))
                            {
                                /* No arguments is converted to empty string. Convert back to empty array. */
                                $arguments = array();
                            }
                        }

                        $no_response = call_user_func_array(array($this, $event_function), $arguments);
                        break;

                    case self::EVENT_TYPE_POST:
                        $no_response = $this->$event_function($_POST);
                        break;

                    case self::EVENT_TYPE_GET:
                        $no_response = $this->$event_function($_GET);
                        break;

                    default:
                        DebugLogger::WriteDebugError('Undefined event type ' . $this->EventType . ' for event=' . $event_function);
                        break;
                }
            }
            else
            {
                DebugLogger::WriteDebugError('Event function ' . $event_function . ' missing in ' . get_called_class() . '.');
            }

            if (isset($no_response))
            {
                DebugLogger::WriteDebugError('Event ' . $event_function . ' function return non-expected data.');
            }
        }

        return $this->_active_controller;
    }

    protected function ReadEventRequestData()
    {
        /* By default assume method is get as normal http request. */
        $this->EventType = self::EVENT_TYPE_GET;

        $this->Event  = SecurityFilter::SafeReadPostRequest('_event', SecurityFilter::STRING_KEY_NAME);
        if(isset($this->Event))
        {
            /* Event found in post method. */
            $this->EventType  = SecurityFilter::SafeReadPostRequest('_event_type', SecurityFilter::STRING_KEY_NAME);
            if(!isset($this->EventType))
            {
                /* If no event type found, then is must be a post event type. */
                $this->EventType = self::EVENT_TYPE_POST;
            }

            $this->EventSource = self::EVENT_METHOD_POST;
        }
        else
        {
            /* Or it is a get event. */
            $this->Event = SecurityFilter::SafeReadGetRequest('_event', SecurityFilter::STRING_KEY_NAME);
            if(isset($this->Event))
            {
                $this->EventType  = SecurityFilter::SafeReadGetRequest('_event_type', SecurityFilter::STRING_KEY_NAME);
                if(!isset($this->EventType))
                {
                    /* If no event type found, then is must be a get event type. */
                    $this->EventType = self::EVENT_TYPE_GET;
                }
            }

            $this->EventSource = self::EVENT_TYPE_GET;
        }
    }

    public function RegisterCallback($event, $arguments=null)
    {
        $controller_class = get_called_class();
        $view_class = $this->NonceProtectedData[self::PROTECTED_DATA_VIEW];
        $wp_nonce = $this->CreateWpNonce();

        $this->View->UpdateClientAddCallback($event, $arguments, $controller_class, $view_class, $wp_nonce);
    }
}
