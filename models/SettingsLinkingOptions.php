<?php
/** Read-More-Login plugin for WordPress.
 *  Puts a login/registration form in your posts and pages.
 *
 *  Copyright (C) 2018 Arild Hegvik
 *
 *  GNU GENERAL PUBLIC LICENSE (GNU GPLv3)
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace ARU_ReadMoreLogin;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\Models\OptionModel;
use WP_PluginFramework\Utils\DebugLogger;

class SettingsLinkingOptions extends OptionModel
{
    const OPTION_NAME = 'ari_read-more-login_linking_option';

    const REGISTER_PAGE_LINK = 'RegisterPageLink';
    const LOGIN_PAGE_LINK = 'LoginPageLink';
    const PROFILE_PAGE_LINK = 'ProfilePageLink';
    const PASSWORD_PAGE_LINK = 'PasswordPageLink';
    const TERMS_PAGE = 'TermsPage';
    const LOGOUT_PAGE_REDIRECT = 'LogoutPage';


    /* Metadata describing database fields and data properties: */
    static $MetaData = array
    (
        self::REGISTER_PAGE_LINK => array(
            'data_type'     => 'StringType',
            'default_value' => '/register'
        ),
        self::LOGIN_PAGE_LINK => array(
            'data_type'     => 'StringType',
            'default_value' => '/login'
        ),
        self::PROFILE_PAGE_LINK => array(
            'data_type'     => 'StringType',
            'default_value' => '/profile'
        ),
        self::PASSWORD_PAGE_LINK => array(
            'data_type'     => 'StringType',
            'default_value' => '/password'
        ),
        self::TERMS_PAGE => array(
            'data_type'     => 'StringType',
            'default_value' => '/terms'
        ),
        self::LOGOUT_PAGE_REDIRECT => array(
            'data_type'     => 'StringType',
            'default_value' => '/'
        )
    );

    public function __construct( $model_name = null ) {
        parent::__construct( $model_name );
        $this->LoadData();
    }

    public function GetCompleteLinkUrl($link_id, $check_link=false)
    {
        $url = false;
        $link_is_my_site = false;

        $raw_link = $this->GetData($link_id);

        if(isset($raw_link))
        {
            $raw_link = trim($raw_link);
            if($raw_link)
            {
                if($raw_link === '/')
                {
                    return get_site_url();
                }

                $link = $raw_link;

                $wp_post = get_page_by_path($link);
                if (!$wp_post)
                {
                    if ($link[0] === '/')
                    {
                        $link_is_my_site = true;
                    }
                    else
                    {
                        $site_url = get_site_url();
                        if (substr($link, 0, strlen($site_url)) === $site_url)
                        {
                            $link_is_my_site = true;
                            $link = substr($link, strlen($site_url));
                            $wp_post = get_page_by_path($link);
                        }
                    }
                }

                if (!$wp_post)
                {
                    if ($link_is_my_site)
                    {
                        $url_parse = wp_parse_url($link);
                        if (array_key_exists('query', $url_parse))
                        {
                            parse_str($url_parse['query'], $query_args);
                            foreach ($query_args as $key => $value)
                            {
                                $key = strtolower($key);
                                if (($key === 'p') or ($key === 'page_id'))
                                {
                                    $post_id = intval($value);
                                    $wp_post = get_post($post_id);
                                    if ($wp_post)
                                    {
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }

                if ($wp_post)
                {
                    return get_permalink($wp_post);
                }
                else
                {
                    if (!$link_is_my_site)
                    {
                        $validated_url = wp_http_validate_url($raw_link);
                        if ($validated_url)
                        {
                            $url = $validated_url;
                        }
                        else
                        {
                            if($check_link)
                            {
                                DebugLogger::WriteDebugWarning('Invalid login url ' . $raw_link);
                            }
                            else
                            {
                                $url = $raw_link;
                            }
                        }
                    }
                    else
                    {
                        if($check_link)
                        {
                            DebugLogger::WriteDebugWarning('Invalid login page ' . $raw_link);
                        }
                        else
                        {
                            $url = $raw_link;
                        }
                    }
                }
            }
        }

        if($url !== false)
        {
            if ($url !== '')
            {
                if ((substr($url, 0, 7) === 'http://') or (substr($url, 0, 8) === 'https://'))
                {
                    return $url;
                }
                else
                {
                    if ((substr($url, 0, 1) !== '/') and (substr($url, 0, 1) !== '\\'))
                    {
                        $url = '/' . $url;
                    }

                    return site_url() . $url;
                }
            }
            else
            {
                return '';
            }
        }

        return $url;
    }

    public function GetLink($link_id)
    {
        return $this->GetData($link_id);
    }

    public function GetRegisterUrl()
    {
        $linking_options = new SettingsLinkingOptions();
        $url = $linking_options->GetCompleteLinkUrl(SettingsLinkingOptions::REGISTER_PAGE_LINK, true);

        if(!$url)
        {
            $url = wp_registration_url();
        }

        return $url;
    }

    public function GetLoginUrl()
    {
        $linking_options = new SettingsLinkingOptions();
        $url = $linking_options->GetCompleteLinkUrl(SettingsLinkingOptions::LOGIN_PAGE_LINK, true);

        if(!$url)
        {
            $url = wp_login_url();
        }

        return $url;
    }

    public function GetLostPasswordUrl()
    {
        $linking_options = new SettingsLinkingOptions();
        $url = $linking_options->GetCompleteLinkUrl(SettingsLinkingOptions::PASSWORD_PAGE_LINK, true);

        if(!$url)
        {
            $url = wp_lostpassword_url();
        }

        return $url;
    }
}
