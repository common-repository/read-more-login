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

namespace WP_PluginFramework\Utils;

defined( 'ABSPATH' ) || exit;

class Mailer
{
    private $receiver_address_list = array();
    private $from_address = '';
    private $copy_address_list = array();
    private $subject = '';
    private $body = '';

    public function __construct($receiver_address='')
    {
        if($receiver_address != '')
        {
            $this->AddReceiverAddress($receiver_address);
        }
    }

    public function SetFromAddress($email_adr)
    {
	    $email_adr = $this->ValidateEmailAddress($email_adr);
        if ($email_adr)
        {
            $this->from_address = $email_adr;
            return true;
        }
        else
        {
            return false;
        }
    }

    public function AddReceiverAddress($email_adr)
    {
	    $email_adr = $this->ValidateEmailAddress($email_adr);
	    if ($email_adr)
        {
            $this->receiver_address_list[] = $email_adr;
            return true;
        }
        else
        {
            return false;
        }
    }

    public function AddCopyAddress($email_adr)
    {
	    $email_adr = $this->ValidateEmailAddress($email_adr);
	    if ($email_adr)
        {
            $this->copy_address_list[] = $email_adr;
            return true;
        }
        else
        {
            return false;
        }
    }

    public function SetSubject($subject_str)
    {
        $this->subject = $subject_str;
    }

    public function SetBody($body_str)
    {
        $this->body = wpautop($body_str);
    }

    public function Send()
    {
        $result = false;
        $headers = array('Content-Type: text/html');

        if($this->from_address != '')
        {
            $headers[] = 'From: ' . $this->from_address;
            $headers[] = 'Reply-To: ' . $this->from_address;
        }

        foreach($this->copy_address_list as $cc)
        {
            $headers[] = 'Cc: ' . $cc;
        }

        if($this->receiver_address_list[0] != '')
        {
            DebugLogger::WriteDebugNote(DebugLogger::Obfuscate($this->receiver_address_list[0]), $this->subject);

            if(wp_mail($this->receiver_address_list[0], $this->subject, $this->body, $headers))
            {
                $result = true;
            }
            else
            {
                DebugLogger::WriteDebugWarning('E-mail not sent.', DebugLogger::Obfuscate($this->receiver_address_list[0]), $this->subject);
            }
        }

        return $result;
    }

    public function ValidateEmailAddress($email)
    {
	    $from_name = '';
	    $from_email = '';

	    $bracket_pos = strpos( $email, '<' );
	    if ( false !== $bracket_pos ) {
		    // Text before the bracketed email is the "From" name.
		    if ( $bracket_pos > 0 ) {
			    $from_name = substr( $email, 0, $bracket_pos - 1 );
			    $from_name = str_replace( '"', '', $from_name );
			    $from_name = trim( $from_name );
		    }

		    $from_email = substr( $email, $bracket_pos + 1 );
		    $from_email = str_replace( '>', '', $from_email );
		    $from_email = trim( $from_email );

		    // Avoid setting an empty $from_email.
	    } elseif ( '' !== trim( $email ) ) {
		    $from_email = trim( $email );
	    }

	    if (filter_var($from_email, FILTER_VALIDATE_EMAIL))
	    {
	    	if($from_name) {
	    		$email = $from_name . ' <' . $from_email . '>';
		    } else {
			    $email = $from_email;
		    }
	    }
	    else
		{
			$email = false;
	    }

	    return $email;
    }
}
