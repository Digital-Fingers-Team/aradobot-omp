<?php

/**
 * @file plugins/themes/modern/ModernThemePlugin.php
 *
 * Copyright (c) 2024 Arado / Digital Fingers
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ModernThemePlugin
 *
 * @brief Modern: a clean, book-cover-forward child theme for Open Monograph
 *  Press. It inherits everything from the bundled Default theme and layers a
 *  modern publishing aesthetic on top (moss/paper palette, serif display
 *  headings, generous whitespace, full RTL + AA accessibility). Only the
 *  markup that actually changes is overridden in templates/.
 */

namespace APP\plugins\themes\modern;

use PKP\plugins\ThemePlugin;

class ModernThemePlugin extends ThemePlugin
{
    /**
     * Initialize the theme. Runs on the active theme and its parents.
     */
    public function init()
    {
        // Inherit all styles, scripts, options, menu areas and templates from
        // the bundled Default theme. This also registers the Default theme's
        // template directory *before* ours, so our templates/ overrides win.
        $this->setParent('defaultthemeplugin');

        // ------------------------------------------------------------------
        // Theme options
        // ------------------------------------------------------------------

        // Accent (moss) colour. Drives links, buttons, header and highlights.
        $this->addOption('accentColour', 'FieldColor', [
            'label' => __('plugins.themes.modern.option.accent.label'),
            'description' => __('plugins.themes.modern.option.accent.description'),
            'default' => '#1e6b47',
        ]);

        // Optional hero band on the homepage.
        $this->addOption('showHero', 'FieldOptions', [
            'label' => __('plugins.themes.modern.option.hero.label'),
            'description' => __('plugins.themes.modern.option.hero.description'),
            'options' => [
                [
                    'value' => true,
                    'label' => __('plugins.themes.modern.option.hero.option'),
                ],
            ],
            'default' => true,
        ]);

        // ------------------------------------------------------------------
        // Design tokens → recolour & re-font the inherited Default stylesheet
        // ------------------------------------------------------------------
        $accent = $this->getOption('accentColour');
        if (!preg_match('/^#[0-9a-fA-F]{3,6}$/', (string) $accent)) {
            $accent = '#1e6b47';
        }

        // Font stacks (Lora everywhere) with Arabic fallback.
        $bodyFont = '"Lora", "IBM Plex Sans Arabic", Georgia, "Times New Roman", serif';
        $headFont = '"Lora", "IBM Plex Sans Arabic", Georgia, "Times New Roman", serif';

        $lessVariables = [
            // Palette (paper / ink / deep-green / gold) — kept in sync with the
            // CSS custom properties in styles/modern.less.
            '@bg: #f7f4ec;',                      // paper
            '@bg-shade: #ddd6c4;',                // line
            '@bg-base: #0e3323;',                 // green-950 (header background base)
            '@primary: ' . $accent . ';',        // green-700 (links / buttons)
            '@primary-lift: lighten(' . $accent . ', 8%);',
            '@text: #1c211d;',                    // ink
            '@text-light: #5c6a5f;',
            '@bg-border-color: #ddd6c4;',
            '@text-bg-base: #ffffff;',
            // Typography
            '@font: ' . $bodyFont . ';',
            '@font-heading: ' . $headFont . ';',
            '@font-site-title: ' . $headFont . ';',
        ];

        $this->modifyStyle('stylesheet', [
            'addLessVariables' => join("\n", $lessVariables),
        ]);

        // Web fonts (Lora + IBM Plex Sans + Arabic). Loaded from Google Fonts;
        // an empty baseUrl stops the theme resolving it inside the plugin dir.
        $this->addStyle(
            'modernFonts',
            'https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400&family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap',
            ['baseUrl' => '']
        );

        // Structural polish (hero, cover grids, book layout, sticky header,
        // focus states, footer, RTL). Registered on the child theme, so it is
        // printed after the inherited stylesheet and wins the cascade.
        $this->addStyle('modern', 'styles/modern.less');
    }

    /**
     * Validate the accent colour before saving.
     *
     * @copydoc ThemePlugin::saveOption()
     */
    public function saveOption($name, $value, $contextId = null)
    {
        if ($name == 'accentColour' && !preg_match('/^#[0-9a-fA-F]{3,6}$/', (string) $value)) {
            $value = null;
        }
        parent::saveOption($name, $value, $contextId);
    }

    /**
     * @copydoc PKPPlugin::getDisplayName()
     */
    public function getDisplayName()
    {
        return __('plugins.themes.modern.name');
    }

    /**
     * @copydoc PKPPlugin::getDescription()
     */
    public function getDescription()
    {
        return __('plugins.themes.modern.description');
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\themes\modern\ModernThemePlugin', '\ModernThemePlugin');
}
