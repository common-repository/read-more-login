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

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\Utils\SecurityFilter;
use WP_PluginFramework\Utils\DebugLogger;

class ContentLoader
{
	static $linefeed = '';

	static function detect_linefeed_characters($content) {
		/* Detect linefeed, assuming only one type is used in entire text. */
		$rn_position = strpos( $content, "\r\n");
		$nr_position = strpos( $content, "\n\r");
		if($rn_position and $nr_position) {
			if($rn_position < $nr_position) {
				self::$linefeed = "\r\n";
			} else {
				self::$linefeed = "\n\r";
			}
		} elseif ( $rn_position ) {
			self::$linefeed = "\r\n";
		} elseif ( $nr_position ) {
			self::$linefeed = "\n\r";
		} elseif ( strstr( $content, "\n" ) ) {
			self::$linefeed = "\n";
		} else {
			self::$linefeed = "\r";
		}
	}

	static function FilterContentEarly($content) {
		$GLOBALS['rml_read_more_content_has_start_shortcode'] = 0;
		$GLOBALS['rml_read_more_content_has_end_shortcode'] = 0;
		if ( strstr($content, ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE) ) {
			$GLOBALS['rml_read_more_content_has_start_shortcode'] = 1;

			self::detect_linefeed_characters($content);

			$content = str_replace( self::$linefeed . ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE . self::$linefeed, ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE . self::$linefeed.self::$linefeed, $content );

			$linefeed_after = strpos($content, ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE . self::$linefeed );
			$linefeed_ahead = strpos($content, self::$linefeed . ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE );

			if ( (!$linefeed_ahead)  and (!$linefeed_after )) {
				/* Ensure short-code always appended with a line feed, otherwise <p> tags will be open when loading texts.  */
				$content = str_replace( ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE, ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE . self::$linefeed, $content );
			}

			if ( strstr($content, ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END) ) {
				$GLOBALS['rml_read_more_content_has_end_shortcode'] = 1;
				$content = str_replace( self::$linefeed . ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END . self::$linefeed, self::$linefeed.self::$linefeed . ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END, $content );

				$linefeed_after = strpos($content, ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END . self::$linefeed );
				$linefeed_ahead = strpos($content, self::$linefeed . ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END );

				if ( (!$linefeed_ahead) and (!$linefeed_after) ) {
					/* Ensure short-code always appended with a line feed, otherwise <p> tags will be open when loading texts.  */
					$content = str_replace( ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END, self::$linefeed . ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END, $content );
				}
			}
		}
		return $content;
	}

	static function FilterContentAfterBlock($content) {
		if($GLOBALS['rml_read_more_content_has_start_shortcode'] > 0) {
			$content = str_replace( "<p>" . ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE . "</p>", ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE, $content );
			if($GLOBALS['rml_read_more_content_has_end_shortcode'] > 0) {
				$content = str_replace( "<p>" . ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END . "</p>", ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END, $content );
			}
		}
		return $content;
	}

	static function FilterContent($content)
    {
	    /* This function will insert read more login form when the post has no short-code end tag. */
	    $modified_content = '';

	    if($GLOBALS['rml_read_more_content_has_start_shortcode'] > 0) {
		    $position = strpos( $content, ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE );
		    if ( $position > 0 ) {
			    $content = str_replace( "<br />" . self::$linefeed . ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE, "</p>" . ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE . "<p>", $content );
			    $content = str_replace( ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE . "<br />", "</p>" . ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE . "<p>", $content );
			    $content = str_replace( "<p>" . ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE . "</p>", ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE, $content );
			    $content = str_replace( ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE . "</p>", "</p>" . ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE, $content );
			    $content = str_replace( "<p>" . ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE, ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE . "<p>", $content );

				self::detect_linefeed_characters($content);

				$content_no_spacing = str_replace(self::$linefeed, "", $content);
				$content_no_spacing = str_replace(" ", "", $content_no_spacing);

				$start_p_ok = strpos( $content_no_spacing, ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE . "<p>" );
				if( $start_p_ok == false) {
					$content = str_replace( ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE, ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE . "<p>", $content );
				}

			    if($GLOBALS['rml_read_more_content_has_end_shortcode'] > 0) {
				    $end_position = strpos( $content, ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END );
				    if ( $end_position > 0 ) {
					    $content = str_replace( ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END . "<br />", "</p>" . ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END . "<p>", $content );
					    $content = str_replace( "<br />\n" . ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END, "</p>" . ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END . "<p>", $content );
					    $content = str_replace( "<p>" . ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END . "</p>", ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END, $content );
					    $content = str_replace( ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END . "</p>", "</p>" . ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END, $content );
					    $content = str_replace( "<p>" . ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END, ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END . "<p>", $content );
				    }

					$content_no_spacing = str_replace(self::$linefeed, "", $content);
					$content_no_spacing = str_replace(" ", "", $content_no_spacing);

					$end_p_ok = strpos( $content_no_spacing, "</p>" . ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END );
					if( $end_p_ok == false) {
						$content = str_replace( ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END, "</p>" . ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END, $content );
					}
			    }
		    }

			if ( defined( 'READ_MORE_LOGIN_TEST_P_ELEMENT' ) and ( READ_MORE_LOGIN_TEST_P_ELEMENT === true ) ) {
				$content = str_replace( "<p>", "<p class='rml-readback-test-p'>", $content );
			}

		    /* When loading remaining content, we dont need any filtering. Short-code will be removed in controller function. */
		    if ( isset( $GLOBALS['rml_read_more_loading_remaining_text'] ) and $GLOBALS['rml_read_more_loading_remaining_text'] ) {
			    return $content;
		    }

		    if ( $position > 0 ) {
			    /* Re-search position, it may have been changed above */
			    $position = strpos( $content, ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE );

			    /* Check if shortcode is surrounded in double brackets, then it shall not be filtered. */
			    $anti_short_code = strpos( $content, '[' . ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE . ']', $position - 1 );
			    if ( $anti_short_code !== false ) {
				    $position = false;
			    }
		    }

		    if ( $position ) {
			    $post_id_val = get_the_ID();

			    $free_visitor_from_google = false;
			    $must_login_first         = false;

			    $draw_free_content                  = false;
			    $draw_login_form                    = false;
			    $draw_protected_content_placeholder = false;
			    $draw_all_content                   = false;

			    $add_ajax_handling = false;
			    $send_js_data      = array();

			    $member_options = get_option( SettingsAccessOptions::OPTION_NAME );

			    if ( isset( $member_options[ SettingsAccessOptions::GOOGLE_READ ] ) and $member_options[ SettingsAccessOptions::GOOGLE_READ ] == '1' ) {

				    if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
					    $agent = SecurityFilter::SanitizeText( $_SERVER['HTTP_USER_AGENT'] );

					    $notice = "HTTP_USER_AGENT=" . $agent;
					    DebugLogger::WriteDebugNote( $notice );

					    if ( preg_match( '/bot|crawl|slurp|spider|Google|Yahoo|msnbot/i', $agent ) ) {
						    DebugLogger::WriteDebugNote( $notice, 'Search agent. Show all page.' );
						    $free_visitor_from_google = true;
					    }

				    }

				    if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
					    $from_url = SecurityFilter::SanitizeText( $_SERVER['HTTP_REFERER'] );

					    $notice = "HTTP_REFERER=" . $from_url;
					    DebugLogger::WriteDebugNote( $notice );

					    $domain = parse_url( $from_url, PHP_URL_HOST );
					    $domain = explode( '.', $domain );
					    if ( count( $domain ) >= 2 ) {
						    $notice = "domain=" . $domain[ count( $domain ) - 2 ];
						    DebugLogger::WriteDebugNote( $notice );

						    if ( ( $domain[ count( $domain ) - 2 ] == 'google' ) || ( $domain[ count( $domain ) - 2 ] == 'googleboot' ) ) {
							    DebugLogger::WriteDebugNote( $notice, 'Google search. Show all page.' );
							    $free_visitor_from_google = true;
						    }
					    }
				    }
			    }

			    if ( ! is_user_logged_in() ) {
				    $must_login_first = true;
			    }

			    if ( $free_visitor_from_google ) {
				    $draw_all_content = true;
			    } else {
				    if ( $must_login_first ) {
					    $draw_free_content                  = true;
					    $draw_login_form                    = true;
					    $draw_protected_content_placeholder = true;
				    } else {
					    $draw_all_content = true;
				    }
			    }

			    if ( $draw_all_content ) {
                    DebugLogger::WriteDebugNote( 'Load all protected content post_id=' . strval( $post_id_val ) );
                    $modified_content .= str_replace( ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE, '', $content );
					$modified_content = str_replace( ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END, '', $modified_content );
			    } else {
                    DebugLogger::WriteDebugNote( 'Load free protected content post_id=' . strval( $post_id_val ) );
                    $controller = new ReadMoreLoginController();

				    if ( $draw_free_content ) {
					    $free_content = substr( $content, 0, $position );

					    $modified_content .= $controller->GetFreeContentHtmlPart( $free_content );
				    }

				    if ( $draw_login_form ) {
					    $register_form = $controller->Draw();

					    if ( isset( $register_form ) ) {
						    /* A registration form is active and needs more attention */
						    $modified_content .= '<div id="rml_readmorelogin_placeholder" style="position:relative;">';
						    $modified_content .= $controller->GetFadeCover();
						    $modified_content .= $register_form;
						    $modified_content .= '</div>';
					    }

					    $add_ajax_handling = true;
				    }

				    if ( $draw_protected_content_placeholder ) {
					    $modified_content .= $controller->GetProtectedContentPlaceholder();
				    }

				    if( isset($end_position) ) {
					    if ( $end_position > 0 ) {
						    /* End position has changed */
						    $end_position = strpos( $content, ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END );
						    if ( $end_position > 0 ) {
							    $short_code_len   = strlen( ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END );
							    $end_content      = substr( $content, $end_position + $short_code_len );
							    $modified_content .= $end_content;
						    }
					    }
				    }


				    if ( $add_ajax_handling ) {
					    $nonce_string             = 'aru_rml_load_rest_of_content-' . strval( get_the_ID() );
					    $wp_nonce                 = wp_create_nonce( $nonce_string );
					    $send_js_data['wp_nonce'] = $wp_nonce;
					    $send_js_data['post_id']  = get_the_ID();
				    }
			    }
		    }
	    }

        if ($modified_content == '')
        {
            return $content;
        }
        else
        {
            return $modified_content;
        }
    }

	static function InsertReadMoreLogin($atts = array(), $content = null)
	{
		if (isset($GLOBALS['rml_read_more_loading_remaining_text'])
		    and $GLOBALS['rml_read_more_loading_remaining_text']) {

			if($content === '')
			{
				/* This was needed for Elementor plugin to do shortcode handling of the hidden block. */
				$content = ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE;
			} else {
				/* If end short code is used, content will be filled with text between start and end short code.
				 * Pick up this text and save if for loading. */
				/* This was specially needed to get the Elementor plugin working. */
				$GLOBALS['rml_read_more_login_remaining_text'] = $content;
			}
		} elseif (!is_user_logged_in()) {
			/* This short-code function will insert read more login form when the post has short-code end tag. */
			if($content === '')
			{
				/* The function will be executed whatever short-code end tag exists. If post has no short-code end tag
				   the content will be empty. Return the short code, which will be inserted back into the post.
				   Filter Content function will do the form insert. */
				$content = ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE;
			} else {
				/* If end short code is used, content will be filled with text between start and end short code.
				 * Replace this with the login form. */
				$controller = new ReadMoreLoginController();

				$content = '<div id="rml_readmorelogin_placeholder" style="position:relative;">';
				$content .= $controller->GetFadeCover();
				$content .= $controller->Draw();
				$content .= '</div>';
				$content .= $controller->GetProtectedContentPlaceholder();
			}
		} else {
			if($content === '') {
				$content = ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE;
			}
		}

		return $content;
	}

    static function PageLoadInit()
    {
        if (isset($_GET['rml']))
        {
            $controller = new ReadMoreLoginController();
            $reg_type = $controller->GetRegType();
            if ($reg_type == RegistrationDbTable::REG_TYPE_READ_MORE_REGISTRATION)
            {
                $controller->InitHandler('InitLogin');
            }
            elseif ($reg_type == RegistrationDbTable::REG_TYPE_USER_REGISTRATION)
            {
                $controller = new RegistrationController();
                $controller->InitHandler('Registering');
            }
        }
    }
}
