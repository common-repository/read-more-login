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

use WP_PluginFramework\HtmlComponents\CheckBox;
use WP_PluginFramework\HtmlComponents\PushButton;
use WP_PluginFramework\HtmlComponents\TextBox;
use WP_PluginFramework\HtmlComponents\TextLine;

class AdminAdvancedView extends AdminPanelView
{
    /** @var CheckBox */
    public $uninstall_keep_db_data;
    /** @var CheckBox */
    public $enable_debug_log;
    /** @var CheckBox */
    public $enable_extra_debug_logging;
	/** @var TextLine */
	public $license_key;
	/** @var CheckBox */
	public $download_rc_versions;
	/** @var CheckBox */
	public $download_beta_versions;
	/** @var CheckBox */
	public $download_dev_versions;
    /** @var PushButton */
    public $StdSubmit;

    public function __construct($id, $controller)
    {
        parent::__construct($id, $controller);

        /* translators: Admin panel headline */
        $this->AddHeader('Header1', esc_html__('Advanced settings', 'read-more-login'));

        $this->AddFormInput('uninstall_keep_db_data', new CheckBox());
        $this->AddFormInput('enable_debug_log', new CheckBox());
        $this->AddFormInput('enable_extra_debug_logging', new CheckBox());

	    if ( defined( 'READ_MORE_LOGIN_ENTER_LICENSE_EDITION' ) and (READ_MORE_LOGIN_ENTER_LICENSE_EDITION === True)) {
		    $this->AddFormInput( 'license_key', new TextLine() );
	    }

	    if ( defined( 'READ_MORE_LOGIN_DOWNLOAD_PRE_RELEASE' ) and (READ_MORE_LOGIN_DOWNLOAD_PRE_RELEASE === True)) {
		    $this->AddFormInput( 'download_rc_versions', new CheckBox() );
		    $this->AddFormInput( 'download_beta_versions', new CheckBox() );
	    }

	    if ( defined( 'READ_MORE_LOGIN_DOWNLOAD_DEV_VERSION' ) and (READ_MORE_LOGIN_DOWNLOAD_DEV_VERSION === True)) {
		    $this->AddFormInput( 'download_dev_versions', new CheckBox() );
	    }

        /* translators: Button label */
        $this->AddButton('StdSubmit', new PushButton(esc_html__('Save changes', 'read-more-login')));
    }

    public function CreateContent($parameters = null, $wrapper = null)
    {
        if (!isset($wrapper))
        {
            $wrapper = $this;
        }

        $category = array(
            'name' => 'uninstall',
            'header' => esc_html__('Uninstall options', 'read-more-login')
        );
        $this->AddInputFormCategory($category);

        $log_file_url = '\wp-content\read_more_login_plugin.log';
        $category = array(
            'name' => 'debug',
            'header' => esc_html__('Debug logging', 'read-more-login'),
            /* translators: %s: Path and filename to log file. */
            'description' => sprintf(esc_html__('Enable logging of debug information from this plugin. In case you have problem with the plugin, this can give helpful information to solve it. Log will be saved to the file %s.', 'read-more-login'), $log_file_url));
        $this->AddInputFormCategory($category);

	    if ( defined( 'READ_MORE_LOGIN_ENTER_LICENSE_EDITION' ) and (READ_MORE_LOGIN_ENTER_LICENSE_EDITION === True)) {
		    $category = array(
			    'name'        => 'license',
			    'header'      => esc_html__( 'Download licensed editions', 'read-more-login' ),
			    /* translators: %s: Path and filename to log file. */
			    'description' => esc_html__( 'Download and install licensed premium editions. The download will be available in the list of installed plugins.', 'read-more-login' )
		    );
		    $this->AddInputFormCategory( $category );
	    }

	    if (( defined( 'READ_MORE_LOGIN_DOWNLOAD_PRE_RELEASE' ) and (READ_MORE_LOGIN_DOWNLOAD_PRE_RELEASE === True))
	    or( defined( 'READ_MORE_LOGIN_DOWNLOAD_DEV_VERSION' ) and (READ_MORE_LOGIN_DOWNLOAD_DEV_VERSION === True))) {
		    $category = array(
			    'name'        => 'testing',
			    'header'      => esc_html__( 'Download pre-release versions', 'read-more-login' ),
			    /* translators: %s: Path and filename to log file. */
			    'description' => esc_html__( 'Download and update of beta and release candidate versions ahead of public release. These versions are not recommended for production servers, but for testing ahead of final release. The download will be available in the list of installed plugins.', 'read-more-login' )
		    );
		    $this->AddInputFormCategory( $category );
	    }

        $this->uninstall_keep_db_data->SetProperty('label', esc_html__('Preserve plugin data:', 'read-more-login'));
        $items = array();
        $items[SettingsAdvancedOptions::UNINSTALL_KEEP_DB_DATA] = esc_html__('Keep the plugin\'s settings and data after uninstall.', 'read-more-login');
        $this->uninstall_keep_db_data->SetProperty('Items', $items);
        $this->uninstall_keep_db_data->SetProperty('description', esc_html__('If you want to uninstall this plugin and reinstall it later, this will save the settings, status and statistics data. Registered users will not be affected by this setting, that data is preserved by WordPress\'s own database tables.'  , 'read-more-login'));
        $this->uninstall_keep_db_data->SetProperty('category', 'uninstall');

        $this->enable_debug_log->SetProperty('label', esc_html__('Enable debug log:', 'read-more-login'));
        $items = array();
        $items[SettingsAdvancedOptions::ENABLE_DEBUG_LOG] = esc_html__('Switch on logging of debug info.', 'read-more-login');
        $this->enable_debug_log->SetProperty('Items', $items);
        $this->enable_debug_log->SetProperty('description', esc_html__('This will switch on error and warning messages.', 'read-more-login'));
        $this->enable_debug_log->SetProperty('category', 'debug');

	    $this->enable_extra_debug_logging->SetProperty('label', esc_html__('Log extra debug info:', 'read-more-login'));
	    $items = array();
	    $items[SettingsAdvancedOptions::ENABLE_EXTRA_DEBUG_LOGGING] = esc_html__('Switch on extra debug info.', 'read-more-login');
	    $this->enable_extra_debug_logging->SetProperty('Items', $items);
	    $this->enable_extra_debug_logging->SetProperty('description', esc_html__('Note! Do not the enable extra debug logging continuously as the log will grow very large over time.', 'read-more-login'));
	    $this->enable_extra_debug_logging->SetProperty('category', 'debug');

	    if ( defined( 'READ_MORE_LOGIN_ENTER_LICENSE_EDITION' ) and ( READ_MORE_LOGIN_ENTER_LICENSE_EDITION === true ) ) {
		    $this->license_key->SetProperty('label', esc_html__('License key:', 'read-more-login'));
		    $this->license_key->SetProperty('description', esc_html__('License key to be provided by plugin developer. Go to www.readmorelogin.com.', 'read-more-login'));
		    $this->license_key->SetProperty('category', 'license');
	    }

	    if (( defined( 'READ_MORE_LOGIN_DOWNLOAD_PRE_RELEASE' ) and (READ_MORE_LOGIN_DOWNLOAD_PRE_RELEASE === True))
	        or( defined( 'READ_MORE_LOGIN_DOWNLOAD_DEV_VERSION' ) and (READ_MORE_LOGIN_DOWNLOAD_DEV_VERSION === True))) {

		    if ( defined( 'READ_MORE_LOGIN_DOWNLOAD_PRE_RELEASE' ) and (READ_MORE_LOGIN_DOWNLOAD_PRE_RELEASE === True)) {
			    $this->download_rc_versions->SetProperty( 'label', esc_html__( 'Release candidates:', 'read-more-login' ) );
			    $items                                                  = array();
			    $items[ SettingsAdvancedOptions::DOWNLOAD_RC_VERSIONS ] = esc_html__( 'Download and install release candidates.', 'read-more-login' );
			    $this->download_rc_versions->SetProperty( 'Items', $items );
			    $this->download_rc_versions->SetProperty( 'category', 'testing' );

			    $this->download_beta_versions->SetProperty( 'label', esc_html__( 'Beta versions:', 'read-more-login' ) );
			    $items                                                    = array();
			    $items[ SettingsAdvancedOptions::DOWNLOAD_BETA_VERSIONS ] = esc_html__( 'Download and install beta test versions.', 'read-more-login' );
			    $this->download_beta_versions->SetProperty( 'Items', $items );
			    $this->download_beta_versions->SetProperty( 'category', 'testing' );
		    }

		    if ( defined( 'READ_MORE_LOGIN_DOWNLOAD_DEV_VERSION' ) and ( READ_MORE_LOGIN_DOWNLOAD_DEV_VERSION === true ) ) {
			    $this->download_dev_versions->SetProperty( 'label', esc_html__( 'Development versions:', 'read-more-login' ) );
			    $items                                                   = array();
			    $items[ SettingsAdvancedOptions::DOWNLOAD_DEV_VERSIONS ] = esc_html__( 'Download and install latest development version.', 'read-more-login' );
			    $this->download_dev_versions->SetProperty( 'Items', $items );
			    $this->download_dev_versions->SetProperty( 'category', 'testing' );
		    }
	    }

	    $this->StdSubmit->SetPrimary(true);

        parent::CreateContent($parameters, $wrapper);
    }
}
