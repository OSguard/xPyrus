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

*}{* $Id: banner.tpl 5807 2008-04-12 21:23:22Z trehn $ *}
{* $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/banner.tpl $ *}

{* fill banner_object with a banner (smarty function) *}
{banner}

{if $banner_object != null}
    {if $banner_object->getName() == "GOOGLE"}
       <br />
       <div class="commercial">
                 {literal}
                       <script type="text/javascript"><!--
                       google_ad_client = "pub-4717911137929763";
                       //Bannerrotation, Magdeburg (468x60, Erstellt 18.12.07)
                       google_ad_slot = "7941909243";
                       google_ad_width = 468;
                       google_ad_height = 60;
                       //--></script>
                       <script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
               {/literal}
               </div>
       {elseif !$banner_object->isFlash()}
	<div class="commercial">
		<a href="/banner/{$banner_object->id}" target="_blank" title="{$banner_object->getName()}">
			<img src="{$banner_object->getFilePath()|escape:"html"}" title="{$banner_object->getName()}" alt="{$banner_object->getName()}" />
		</a>
	</div>
	{else}
	<div class="commercial">
				<a style="left: 590px ! important; top: 0px ! important;" class="abp-objtab visible ontop" href="{$banner_object->getFilePath()|escape:"html"}"></a>
				<a href="/banner/{$banner_object->id}" target="_blank" title="{$banner_object->getName()}">
				<object data="{$banner_object->getFilePath()|escape:"html"}" type="application/x-shockwave-flash" style="-moz-user-focus: ignore;" height="60" width="468">
				<param value="transparent" name="wmode" />
				<param value="default" name="salign" />
				<param name="movie" value="{$banner_object->getFilePath()|escape:"html"}" />
				</object>
				</a>
	</div>
	{/if}

{else}
 <div class="commercial">
	<a href="http://www.unihelp.org/" target="_blank" title="Wie kann ich helfen?">
		<img src="/images/banner.gif" title="Wie kann ich helfen?" alt="UniHelp Supporter" />
	</a>
</div>
{/if}
