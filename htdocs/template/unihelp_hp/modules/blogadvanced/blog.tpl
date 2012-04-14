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

*}{* $Id: blog.tpl 5898 2008-05-04 19:32:32Z schnueptus $ *}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<title>{if $requested_method}{$requested_method->getName()}{else}Fehler{/if}</title>
{if $requested_method && $requested_method->getFeedURL()}
<link rel="alternate" type="application/rss+xml" title="{$requested_method->getFeedTitle()}" href="{$requested_method->getFeedURL()}" />
{/if}
<link title="css" rel="stylesheet" href="{$TEMPLATE_DIR}/css/blog.css?5197" type="text/css" />
{if $ie6}
<link title="css" rel="stylesheet" href="{$TEMPLATE_DIR}/css/ie6.css" type="text/css" />
{/if}
<script src="{$TEMPLATE_DIR}/javascript/lib/behaviour.js" type="text/javascript"></script>
{*<script src="{$TEMPLATE_DIR}/javascript/lib/protoculous.js" type="text/javascript"></script>*}
<script src="{$TEMPLATE_DIR}/javascript/lib/prototype.js" type="text/javascript"></script>
<script src="{$TEMPLATE_DIR}/javascript/lib/scriptaculous.js" type="text/javascript"></script>
<script src="{$TEMPLATE_DIR}/javascript/lib/controls.js" type="text/javascript"></script>

{* autoload must be included as the first custom js file *}
<script src="{$TEMPLATE_DIR}/javascript/autoload.js" type="text/javascript"></script>
<script src="{$TEMPLATE_DIR}/javascript/entryfunctions.js" type="text/javascript"></script>
<script src="{$TEMPLATE_DIR}/javascript/base_blog.js" type="text/javascript"></script>
<link rel="shortcut icon" href="{$TEMPLATE_DIR}/css/images/icon.ico" />
</head>
<body>

<div id="page"><a id="topofpage" name="topofpage"></a>
<div id="title">
  <h1><a class="hidden" href="/home" title="Zur [[local.local.project_name]]-Startseite"><span>[[local.local.project_domain]] - [[local.local.project_title]]</span></a></h1>
  {if $blog_model} 
  <h2>{blog_link owner=$blog_model->getOwner() content=$blog_model->getTitle()}</h2>
  <h3><a href="{blog_url owner=$blog_model->getOwner()}">
    {if $blog_selected_category}Kategorie {$blog_selected_category->name}
    {elseif $blog_selected_archive_day}Archiv {$blog_selected_archive_day|unihelp_strftime:'%A, %e. %B %Y'}
    {elseif $blog_selected_archive}Archiv {$blog_selected_archive|unihelp_strftime:'%B %Y'}
    {else}{$blog_model->getSubtitle()}
    {/if}
  </a></h3>
  {else}
  <h2>[[local.local.project_name]] &ndash; Blogosphäre</h2>
  {/if}
</div>{* end div title  *}

{* display main content in a div#content *}
{dynamic}
{$central->getContent()}
{/dynamic}

{if $show_sidebar}
{include file="modules/blogadvanced/sidebar.tpl"}
{elseif $show_sidebar_alternative} {* show alternative sidebar *} 
{include file="modules/blogadvanced/sidebar_alternative.tpl"}
{/if}

<div id="footer">
<p>[[local.local.project_domain]] &ndash; [[local.local.project_subtitle]] &ndash; © [[local.local.project_start_year]]&ndash;{$THIS_YEAR} [[local.local.project_organisation]]</p>
<ul>
<li><a href="/imprint">Impressum</a></li>
<li><a href="/privacy">Datenschutz</a></li>
<li><a href="/terms_of_use">Nutzungsbedingungen</a></li>
<li><a href="/imprint">Kontakt</a></li>
</ul>
</div>

</div> {* end div page  *}

</body>
</html>
