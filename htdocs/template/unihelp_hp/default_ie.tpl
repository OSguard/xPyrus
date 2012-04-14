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

*}{* $Id: default_ie.tpl 5963 2008-05-17 14:19:43Z lboelling $
   $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/default_ie.tpl $ *}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />

{* generate default, optimized JS and CSS headers *}
{generate_headers}
<link title="css" rel="stylesheet" href="{$TEMPLATE_DIR}/{$CSS_DIR}/ie6.css?5075" type="text/css" />

<link rel="shortcut icon" href="{$TEMPLATE_DIR}/{$CSS_DIR}/images/icon.ico" />
<title>[[local.local.project_domain]] - [[local.local.project_title]] - {$local_city->getName()}</title>
</head>
<body>
{* we support firefox; www.firefox.com; code from http://www.spreadfirefox.com/?q=affiliates/homepage *}
{*<p>
<a href="http://www.spreadfirefox.com/?q=affiliates&amp;id=0&amp;t=218"><img border="0" alt="Firefox 2" title="Firefox 2" src="http://sfx-images.mozilla.org/affiliates/Buttons/firefox2/ff2o80x15.gif"/></a>
</p>*}
{* ---- firefox support ---- *}
<h1 class="header"><a href="/home" title="##backToHome##">
    <img src="{$TEMPLATE_DIR}/css/images/weblogo.gif" alt="[[local.local.project_domain]] - [[local.local.project_title]]" style="margin-left: 20px; margin-top: 27px" />
</a></h1>

<p id="random-user">
        {user_random_pic user=$box_random_userpic_user}
</p>        
 <p id="sunflower"><img src="{$TEMPLATE_DIR}/images/sunflower.gif" alt=""/></p>


  <table id="mainTable" style="clear: both;">
    <tr>
      <td id="left">
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
      </td>
      <td id="content">
      	<div style="width: 97%; text-align: left; padding-top: 40px;">
      	{*Sie benutzen IE6 oder niedriger, installieren sie jetzt Firefox<br /><br />*}
      	{if !$nobanner}
            {dynamic}{include file="banner.tpl"}{/dynamic}
        {/if}

        {if !$nobreadcrumbs}
            {breadcrumbs method=$requested_method}
        {/if}
        
		{* display main content *}
		{$central->getContent()}
		</div>
	</td>
	{if count($boxes_right) != 0}
      <td id="right">
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
	   </td>
	{/if}   
    </tr>
  
  </table>
  

	{include file="nav.tpl"} 

{literal}
<script language="JavaScript">

sfHover = function() {
	var sfEls = document.getElementById("nav").getElementsByTagName("LI");
	for (var i=0; i<sfEls.length; i++) {
		sfEls[i].onmouseover=function() {
			this.className+=" sfhover";
		}
		sfEls[i].onmouseout=function() {
			this.className=this.className.replace(new RegExp(" sfhover\\b"), "");
		}
	}
}
if (window.attachEvent) window.attachEvent("onload", sfHover);
</script>
{/literal}

<div id="footer">
<p>[[local.local.project_domain]] &ndash; [[local.local.project_subtitle]] &ndash; © [[local.local.project_start_year]]&ndash;{$THIS_YEAR} [[local.local.project_organisation]]</p>
<p>
<span><a href="/imprint">##imprint##</a></span>
<span><a href="/privacy">##dataProtection##</a></span>
<span><a href="/terms_of_use">##termsOfUse##</a></span>
<span><a href="{mantis_url}">##contact##</a></span>
</p>
</div>
</body>
</html>
