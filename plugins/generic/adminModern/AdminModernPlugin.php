<?php

/**
 * @file plugins/generic/adminModern/AdminModernPlugin.php
 *
 * Copyright (c) 2024 Arado / Digital Fingers
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class AdminModernPlugin
 *
 * @brief Re-skins the OMP editorial backend (dashboard, settings, workflow) to
 *  match the public "Modern" theme: green gradient header with the Arado logo,
 *  retinted design tokens, and Lora/IBM Plex typography. It does this the
 *  update-safe way — by injecting a stylesheet (and web fonts) into the
 *  `backend` template context — instead of forking any PKP core component.
 */

namespace APP\plugins\generic\adminModern;

use APP\core\Application;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;

class AdminModernPlugin extends GenericPlugin
{
    public function getDisplayName()
    {
        return 'Modern Admin Skin';
    }

    public function getDescription()
    {
        return 'Re-skins the editorial backend to match the Modern theme (green gradient header, Arado logo, matching palette & fonts).';
    }

    /**
     * @copydoc Plugin::register()
     *
     * @param null|mixed $mainContextId
     */
    public function register($category, $path, $mainContextId = null)
    {
        if (parent::register($category, $path, $mainContextId)) {
            if ($this->getEnabled($mainContextId)) {
                // Fires at the top of TemplateManager::display(), before the
                // backend layout renders its <head>, so the stylesheets we
                // register here are picked up by {load_stylesheet}.
                Hook::add('TemplateManager::display', $this->injectBackendAssets(...));
            }
            return true;
        }
        return false;
    }

    /**
     * Register the admin stylesheet + web fonts for the backend context only.
     */
    public function injectBackendAssets($hookName, $args)
    {
        $templateMgr = $args[0];
        $request = Application::get()->getRequest();
        $base = $request->getBaseUrl() . '/' . $this->getPluginPath();

        // Google Fonts (same families as the Modern theme).
        $templateMgr->addStyleSheet(
            'adminModernFonts',
            'https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=Lora:ital,wght@0,500;0,600;0,700;1,500&family=Noto+Naskh+Arabic:wght@500;600;700&display=swap',
            ['contexts' => ['backend'], 'priority' => \APP\template\TemplateManager::STYLE_SEQUENCE_LATE]
        );

        // The re-skin itself. STYLE_SEQUENCE_LAST → loads after PKP's core
        // backend stylesheet so our overrides win the cascade.
        $templateMgr->addStyleSheet(
            'adminModern',
            $base . '/css/admin.css',
            ['contexts' => ['backend'], 'priority' => \APP\template\TemplateManager::STYLE_SEQUENCE_LAST]
        );

        return false;
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\generic\adminModern\AdminModernPlugin', '\AdminModernPlugin');
}
