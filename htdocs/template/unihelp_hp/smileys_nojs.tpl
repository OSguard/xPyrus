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

*}{*	$Id: smileys_nojs.tpl 5898 2008-05-04 19:32:32Z schnueptus $
	$HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/smileys_nojs.tpl $ *}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head><title>::: SMILEY-CODES :::</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link title="css" rel="stylesheet" href="{$TEMPLATE_DIR}/css/standard.css" type="text/css" />

{literal}
<style type="text/css">
/* <![CDATA[ */
td {
  padding: 3px 0;
}
table {
  /*background-color: #F4FFFF;*/
/*  display: inline;*/
  margin: 3px;
  height: 100%;
  float: left;
}
td {
  border-bottom: 1px dotted #F7FBC3;
}
/* ]]> */
</style>
{/literal}

</head>
<body>
<div id="content">
<div class="shadow"><div style="vertical-align:top;">
<h3>Unihelp-Smileys</h3>
{foreach from=$smileys item=col}
{counter print=false assign="colNo"}
<table summary="Smileycodes in [[local.local.project_name]]"><caption>[[local.local.project_name]]-Smiley-Codes</caption>
<thead>
<tr><th id="file{$colNo}">Smiley</th><th id="code{$colNo}">Code</th></tr>
</thead>
<tbody>

{foreach from=$col item=s}
<tr>
<td headers="file{$colNo}"><img src="{$smiley_path}/{$s->getURL()}" alt="{$s->getText()}" /></td>
<td headers="code{$colNo}">{$s->getText()}</td>
</tr>
{/foreach}
</tbody>
</table>
{/foreach}
<br clear="all" />
</div></div>{* closing .shadow and inner div of .shadow *}
</div>{* #content *}
</body>
</html>
