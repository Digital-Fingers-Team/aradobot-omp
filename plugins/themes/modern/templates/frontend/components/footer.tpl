{**
 * plugins/themes/modern/templates/frontend/components/footer.tpl
 *
 * Copyright (c) 2024 Arado / Digital Fingers
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Modern theme footer. Preserves the Default footer's structural closes,
 *  sidebar hook, script loading and "powered by" brand, but lays the content
 *  out as a rich, dark, multi-column footer.
 *
 * @hook Templates::Common::Sidebar []
 * @hook Templates::Common::Footer::PageFooter []
 *}

	</div><!-- pkp_structure_main -->

	{* Sidebars *}
	{if empty($isFullWidth)}
		{capture assign="sidebarCode"}{call_hook name="Templates::Common::Sidebar"}{/capture}
		{if $sidebarCode}
			<div class="pkp_structure_sidebar left" role="complementary">
				{$sidebarCode}
			</div><!-- pkp_sidebar.left -->
		{/if}
	{/if}
</div><!-- pkp_structure_content -->

<div class="pkp_structure_footer_wrapper" role="contentinfo">
	<a id="pkp_content_footer"></a>

	<div class="pkp_structure_footer">

		<div class="modern_footer__grid">

			<div class="modern_footer__col modern_footer__about">
				<div class="modern_footer__brand">
					{if $currentContext}{$currentContext->getLocalizedData('name')|escape}{else}{$siteTitle|escape}{/if}
				</div>
				{if $currentContext}
					{assign var="footerBlurb" value=$currentContext->getLocalizedData('description')|strip_tags|trim}
					{if $footerBlurb}
						<p class="modern_footer__blurb">{$footerBlurb|truncate:180:"…"|escape}</p>
					{/if}
				{/if}
			</div>

			<div class="modern_footer__col">
				<h3>{translate key="navigation.catalog"}</h3>
				<ul>
					<li><a href="{url page="catalog"}">{translate key="navigation.catalog"}</a></li>
					<li><a href="{url page="catalog" op="newReleases"}">{translate key="catalog.newReleases"}</a></li>
					{if $enableAnnouncements}
						<li><a href="{url page="announcement"}">{translate key="announcement.announcements"}</a></li>
					{/if}
					<li><a href="{url page="search"}">{translate key="common.search"}</a></li>
				</ul>
			</div>

			<div class="modern_footer__col">
				<h3>{translate key="navigation.about"}</h3>
				<ul>
					<li><a href="{url page="about"}">{translate key="about.aboutContext"}</a></li>
					<li><a href="{url page="about" op="editorialMasthead"}">{translate key="common.editorialMasthead"}</a></li>
					<li><a href="{url page="about" op="submissions"}">{translate key="about.submissions"}</a></li>
					{if $currentContext && ($currentContext->getData('mailingAddress') || $currentContext->getData('contactName'))}
						<li><a href="{url page="about" op="contact"}">{translate key="about.contact"}</a></li>
					{/if}
				</ul>
			</div>

		</div>

		<div class="modern_footer__bottom">
			{if $pageFooter}
				<div class="pkp_footer_content">
					{$pageFooter}
				</div>
			{else}
				<div class="pkp_footer_content">
					&copy; {if $currentContext}{$currentContext->getLocalizedData('name')|escape}{else}{$siteTitle|escape}{/if}
				</div>
			{/if}

			<div class="pkp_brand_footer">
				<a href="{url page="about" op="aboutThisPublishingSystem"}">
					<img alt="{translate key="about.aboutThisPublishingSystem"}" src="{$baseUrl}/{$brandImage}">
				</a>
			</div>
		</div>
	</div>
</div><!-- pkp_structure_footer_wrapper -->

</div><!-- pkp_structure_page -->

{load_script context="frontend"}

{call_hook name="Templates::Common::Footer::PageFooter"}
</body>
</html>
