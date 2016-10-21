<?php

$PluginInfo['themeSwitcher'] = [
    'Name' => 'Theme Switcher',
    'Description' => 'Give users the possibility to choose one of the available sub themes (if current theme provides them)',
    'Version' => '0.1',
    'RequiredApplications' => ['Vanilla' => '>=2.2'],
    'SettingsUrl' => '/settings/themeswitcher',
    'SettingsPermission' => 'Garden.Settings.Manage',
    'MobileFriendly' => true,
    'Author' => 'Robin Jurinka',
    'AuthorUrl' => 'http://vanillaforums.org/profile/r_j',
    'License' => 'MIT'
];
/**
 * Theme Switcher Plugin.
 */
class ThemeSwitcherPlugin extends Gdn_Plugin {
    /**
     * Initialize theme styles info when plugin is enabled.
     *
     * @return void.
     */
    public function setup() {
        // Refresh current styles.
        // Further changes will be catched be afterEnableTheme hook.
        $this->getThemeStyles(true);
    }

    /**
     * Refresh styles after theme has changed.
     *
     * @param SettingsController $sender Instance of the calling class.
     *
     * @return void.
     */
    public function settingsController_afterEnableTheme_handler($sender) {
        $this->getThemeStyles(true);
    }

    /**
     * Insert theme switcher into html.
     *
     * @param Garden Controller $sender Instance of the calling class.
     *
     * @return void.
     */
    public function base_afterBody_handler($sender) {
        // Don't show anything if current theme has no sub themes.
        $styles = $this->getThemeStyles();
        if (!$styles) {
            return;
        }

        $sender->Form = new Gdn_Form();
        $sender->Form->addHidden('Target', $sender->SelfUrl, true);

        $sender->setData(
            'ThemeSwitcherStyles',
            array_combine(array_keys($styles), array_keys($styles))
        );

        $style = $this->getUserStyle();
        if ($style) {
            $sender->Form->setFormValue('UserTheme', $style);
        }

        include $this->getView('themeswitcher.php');
    }

    /**
     * Set UserMeta (or Cookie for guests) to the individual theme.
     *
     * @param PluginController $sender Instance of the calling class.
     *
     * @return void.
     */
    public function pluginController_themeSwitcher_create($sender) {
        $requestArgs = Gdn::request()->getRequestArguments('get');

        if (Gdn::session()->validateTransientKey($requestArgs['TransientKey'])) {
            // Set theme for current user.
            UserModel::setMeta(
                Gdn::session()->UserID,
                ['Plugin.ThemeSwitcher' => $requestArgs['UserTheme']]
            );
        } else {
            Gdn::session()->setcookie(
                'ThemeSwitcher',
                $requestArgs['UserTheme'],
                360000
            );
        }
        // Go back to where request came from.
        redirect($requestArgs['Target']);
    }

    /**
     * Set custom css based on user setting.
     *
     * @param Garden Controller $sender Instance of the calling class.
     *
     * @return void.
     */
    public function assetModel_styleCss_handler($sender) {
        $style = $this->getUserStyle();
        if ($style) {
            // Set custom style if there is one.
            Gdn::controller()->ThemeOptions['Styles']['Value'] = $this->getThemeStyles()[$style];
        }
    }

    /**
     * Helper function that returns all available themes.
     *
     * Themes are also saved to config.
     *
     * @param boolean $refresh Whether info shall be retrieved from
     *                         config or read again from theme info.
     *
     * @return array List of available sub themes.
     */
    public function getThemeStyles($refresh = false) {
        // Get styles from config.
        $styles = c('themeSwitcher.Styles');

        // If that fails or styles should be refreshed.
        if (!$styles || $refresh === true) {
            $styles = [];
            $themeManager = new Gdn_ThemeManager();
            $themeInfo = $themeManager->enabledThemeInfo();
            if (array_key_exists('Options', $themeInfo)) {
                $styles = val('Styles', $themeInfo['Options'], false);
                if ($styles) {
                    // Loop through all styles.
                    foreach ($styles as $styleName => $styleInfo) {
                        $styles[$styleName] = $styleInfo['Basename'];
                    }
                }
            }
            // Save styles to config.
            saveToConfig('themeSwitcher.Styles', serialize($styles));
        }
        return $styles;
    }

    /**
     * Get the individual style.
     *
     * Tries to fetch it from user meta. If unsuccessful, try to get it from
     * cookie.
     *
     * @return string|null Name of the style.
     */
    public function getUserStyle() {
        // Try to get individual theme from user.
        $style = UserModel::getMeta(
            Gdn::session()->UserID,
            'Plugin.ThemeSwitcher',
            'Plugin.ThemeSwitcher',
            []
        )[0];
        if ($style) {
            return $style;
        }

        // Alternativly try to get style from cookie.
        return Gdn::session()->getCookie('ThemeSwitcher', null);
    }
}
