{**
 * plugins/themes/modern/templates/frontend/pages/index.tpl
 *
 * Copyright (c) 2024 Arado / Digital Fingers
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Modern theme homepage. Keeps every original include, variable and
 *  hook from the Default homepage, and adds an editorial hero band (title,
 *  lead, inline catalog search, quick category chips) at the top.
 *
 * @uses $homepageImage array Details about the uploaded homepage image
 * @uses $featuredMonographs array List of featured releases in this press
 * @uses $newReleases array List of new releases in this press
 * @uses $announcements array List of announcements
 * @uses $numAnnouncementsHomepage int Number of announcements to display
 * @uses $additionalHomeContent string HTML blob added by an editor/admin
 *}
{include file="frontend/components/header.tpl"}

{* ---- Modern editorial hero band (opt-out via the theme's "showHero" option) ---- *}
{if $activeTheme && $activeTheme->getOption('showHero') && $currentContext}
	<section class="modern_hero" aria-labelledby="modernHeroTitle">
		<div class="modern_hero__inner">
			<span class="modern_hero__kicker">
				<span class="fa fa-book" aria-hidden="true"></span>
				{translate key="navigation.catalog"}
			</span>

			<h1 id="modernHeroTitle" class="modern_hero__title">
				{$currentContext->getLocalizedData('name')|escape}
			</h1>

			{assign var="heroLead" value=$currentContext->getLocalizedData('description')|strip_tags|trim}
			{if $heroLead}
				<p class="modern_hero__lead">{$heroLead|truncate:240:"…"|escape}</p>
			{/if}

			{* Inline catalog search *}
			<form class="modern_hero__search" role="search" method="get"
				action="{url page="search" op="index"}">
				<input type="search" name="query"
					aria-label="{translate key="common.search"}"
					placeholder="{translate key="common.searchFor"} {$currentContext->getLocalizedData('name')|escape}…">
				<button type="submit">{translate key="common.search"}</button>
			</form>

			{* Quick links *}
			<nav class="modern_hero__chips" aria-label="{translate|escape key="navigation.catalog"}">
				<a href="{url page="catalog"}">{translate key="navigation.catalog"}</a>
				<a href="{url page="catalog" op="newReleases"}">{translate key="catalog.newReleases"}</a>
				{if $enableAnnouncements}
					<a href="{url page="announcement"}">{translate key="announcement.announcements"}</a>
				{/if}
				<a href="{url page="about"}">{translate key="navigation.about"}</a>
			</nav>
		</div>
	</section>
{/if}

<div class="page page_homepage">

	{if $highlights->count()}
		{include file="frontend/components/highlights.tpl" highlights=$highlights}
	{/if}

	{* Homepage Image *}
	{if $activeTheme && !$activeTheme->getOption('useHomepageImageAsHeader') && $homepageImage}
		<img src="{$publicFilesDir}/{$homepageImage.uploadName|escape:"url"}" alt="{$homepageImageAltText|escape}">
	{/if}

	{* Press Description *}
	{if $activeTheme && $activeTheme->getOption('showDescriptionInPressIndex')}
		<section class="homepage_about">
			<a id="homepageAbout"></a>
			<h2>{translate key="about.aboutContext"}</h2>
			{$currentContext->getLocalizedData('description')}
		</section>
	{/if}

	{* Featured *}
	{if !empty($featuredMonographs)}
		{include file="frontend/components/monographList.tpl" monographs=$featuredMonographs titleKey="catalog.featured" authorUserGroups=$authorUserGroups}
	{/if}

	{* New releases *}
	{if !empty($newReleases)}
		{include file="frontend/components/monographList.tpl" monographs=$newReleases titleKey="catalog.newReleases"}
	{/if}

	{include file="frontend/objects/announcements_list.tpl" numAnnouncements=$numAnnouncementsHomepage}

	{* Additional Homepage Content *}
	{if $additionalHomeContent}
		<div class="additional_content">
			{$additionalHomeContent}
		</div>
	{/if}

</div>
{include file="frontend/components/footer.tpl"}
