{*

xPyrus - Framework for Community and knowledge exchange
Copyright (C) 2003-2008 UniHelp e.V., Germany

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, only version 3 of the
License.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program. If not, see http://www.gnu.org/licenses/agpl.txt

*}{* $Id: default.tpl 5442 2007-10-14 00:10:53Z schnueptus $
   $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/mauritius-1.1/htdocs/template/unihelp_hp/default.tpl $ *}
<!DOCTYPE html 
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
{if $requested_method && $requested_method->getFeedURL()}
<link rel="alternate" type="application/rss+xml" title="{$requested_method->getFeedTitle()}" href="{$requested_method->getFeedURL()}" />
{/if}

{* generate default, optimized JS and CSS headers *}
{generate_headers}

<link rel="shortcut icon" href="{$TEMPLATE_DIR}/{$CSS_DIR}/images/icon.ico" />
<title>[[local.local.project_domain]] &ndash; {if $requested_method}{$requested_method->getName()}{else}##error##{/if}</title>

</head>
<body>
{if count($boxes_right) == 0}
	{literal}
	<style type="text/css">
	#right{
		display: none;
	}
	#centerpad{
		margin-right: 20px;
	}
	</style>
	{/literal}
{/if}
<div id="body">
<h1 class="header"><a href="/home" title="backToHome">
    <img src="{$TEMPLATE_DIR}/css/images/weblogo.png" alt="[[local.local.project_domain]] - [[local.local.project_title]]" style="margin-left: 20px; margin-top: 27px" />
</a></h1>

<div class="hidden">
 <ul class="hidden">
   <li><a href="#centerpad">##default_toContent##</a></li>
   <li><a href="#nav">##default_toMenu##</a></li>
   <li><a href="#left">##default_leftBoxes##</a></li>
   <li><a href="#right">##default_rightBoxes##</a></li>
   <li><a href="#footer">##default_toFooter##</a></li>
 </ul>
</div>

<p id="random-user">
        {user_random_pic user=$box_random_userpic_user}
</p>
<p id="sunflower"><img src="{$TEMPLATE_DIR}/images/sunflower.png" alt=""/></p>

<div id="wrapper">
<div id="contentfloatholder">
<div id="content">
<div id="centerpad">
{if !$nobanner}
    {dynamic}{include file="banner.tpl"}{/dynamic}
{/if}

{if !$nobreadcrumbs}
    {breadcrumbs method=$requested_method}
{/if}

{* display main content *}
{$central->getContent()}
</div>
</div><!-- /content -->
<div id="left">
{* variable stating if partners ads box has been shown *}
{assign var="partnersAdsShown" value=0}
{* display boxes on the left *}
{foreach from=$boxes_left item=box name="boxLeft"}
    {$box->getContent()}
    {if $smarty.foreach.boxLeft.iteration >= 1 && $partnersAdsShown == 0}
       	{assign var="partnersAdsShown" value=1}
    	{include file="boxes/ads_partners.tpl"}
    {/if}
{/foreach}
</div><!-- /left -->
<div id="right">
{* variable stating if google ads box has been shown *}
{assign var="googleAdsShown" value=0}
{* display boxes on the right *}
{foreach from=$boxes_right item=box name="boxRight"}
    {$box->getContent()}
    {if $smarty.foreach.boxRight.iteration >= 3 && $googleAdsShown == 0}
    	{assign var="googleAdsShown" value=1}
    	{include file="boxes/ads_google.tpl"}
    {/if}
{/foreach}
{if !$googleAdsShown}
   	{include file="boxes/ads_google.tpl"}
{/if}
</div><!-- /right -->
</div><!-- /contentfloatholder -->
</div><!-- /wrapper -->

	{include file="nav.tpl"}

<div id="footer"><div>
<p>[[local.local.project_domain]] &ndash; [[local.local.project_subtitle]] &ndash; © [[local.local.project_start_year]]&ndash;{$THIS_YEAR} [[local.local.project_organisation]]
</p>
<ul>
<li><a href="/imprint">##imprint##</a></li>
<li><a href="/privacy">##dataProtection##</a></li>
<li><a href="/terms_of_use">##termsOfUse##</a></li>
<li><a href="{mantis_url}">##contact##</a></li>
{if $smarty.const.DEVEL}
	{if $showDebugOn}
		<li><a href="/index.php?showDebug=on&amp;dest=view">##default_showDebugMessage##</a></li>
	{/if}
	{if $showDebugOff}
		<li><a href="/index.php?showDebug=off&amp;dest=view">##default_hideDebugMessage##</a></li>
	{/if}
{/if}
{if $visitor->hasRight('SERVER_STATS')}
	{if $smarty.cookies.showStats == $smarty.const.STAT_SECRET}
		<li><a href="/index.php?showStats={$smarty.const.STAT_SECRET}Off">##default_stat-##</a></li>
	{else}
		<li><a href="/index.php?showStats={$smarty.const.STAT_SECRET}On">##default_stat+##</a></li>
	{/if}
{/if}
</ul>
<br class="clear" />
</div></div>

<p id="endPage"></p>
</div> {* End of id="body" *}
</body>
</html>
