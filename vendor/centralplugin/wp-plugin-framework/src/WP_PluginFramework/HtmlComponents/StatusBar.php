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

namespace WP_PluginFramework\HtmlComponents;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\HtmlElements\Div;
use WP_PluginFramework\HtmlElements\P;
use WP_PluginFramework\HtmlElements\Strong;
use WP_PluginFramework\HtmlElements\Span;
use WP_PluginFramework\HtmlElements\Button;

class StatusBar extends HtmlBaseComponent
{
    const STATUS_SUCCESS = 'success';
    const STATUS_INFO = 'info';
    const STATUS_WARNING = 'warning';
    const STATUS_ERROR = 'error';

    const TYPE_REMOVABLE_BLOCK = 'block';
    const TYPE_INLINE_TEXT = 'text';

    /** @var string Type const code. */
    protected $type;
    /** @var string Status const code. */
    protected $status;
    /** @var string Text to be displayed on StatusBar. */
    protected $text;


    public function __construct($type=null, $text=null, $status=null)
    {
        $attributes = array();

        $properties['type'] = $type;
        $properties['text'] = $text;
        $properties['status'] = $status;

        parent::__construct($attributes, $properties, null, 'div', true);
    }

    public function SetStatusHtml($text, $status)
    {
        if(is_string($text))
        {
            if (strpos(strtolower($text), '<strong>') == false)
            {
                $text = new HtmlText($text);
                $text = new Strong($text);
            }
            else
            {
                $text = new HtmlText($text);
            }
        }
        $this->SetStatusText($text, $status);
    }

    public function SetStatusText($text, $status)
    {
        $this->text = $text;
        $this->status = $status;

        $id = $this->attributes['id'];
        //$selector = $this->GetViewSelector() . ' div#' . $id;
        $selector = 'div#' . $id;
        $bar = $this->CreateHtmlBar($text, $status);
        $html = $bar->DrawHtml();

        $this->UpdateClientDom($selector, 'html', array($html));
    }

    public function CreateHtmlBar($text, $status)
    {
        if ($this->type == self::TYPE_REMOVABLE_BLOCK)
        {
            $strong_text = new Strong($text);
            $p = new P($strong_text);
            //$attributes['id'] = "setting-error-settings_updated";
            $attributes['class'] = "wppmvcf-status-bar updated settings-error notice is-dismissible";
            $div = new Div($p, $attributes);

            $span = new Span('Dismiss this notice.', ['class' => "screen-reader-text"]);
            $button = new Button($span, ['class' => "notice-dismiss"]);
            $div->AddContent($button);
        }
        else
        {
            $text = $this->text;
            if(is_string($text))
            {
                if (strpos(strtolower($text), '<strong>') == false)
                {
                    $text = new Strong($text);
                }
            }
            $p = new P($text);

            $attributes['class'] = "wppmvcf-status-bar-" . $status;
            $div = new Div($p, $attributes);
        }

        return $div;
    }

    public function CreateContent($config = null)
    {
        if($this->text)
        {
            $bar = $this->CreateHtmlBar($this->text, $this->status);
            $this->AddContent($bar);
        }
    }
}
