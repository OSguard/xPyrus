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

*}{*	$Id: smileys.tpl 5807 2008-04-12 21:23:22Z trehn $
	$HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/smileys.tpl $ *}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head><title>::: SMILEY-CODES :::</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<script src="/template/unihelp_hp/javascript/lib/behaviour.js" type="text/javascript"></script>
<script src="/template/unihelp_hp/javascript/autoload.js" type="text/javascript"></script>
<script src="/template/unihelp_hp/javascript/entryfunctions.js" type="text/javascript"></script>
<link title="css" rel="stylesheet" href="{$TEMPLATE_DIR}/css/standard.css" type="text/css" />
{*<link title="css" rel="stylesheet" href="{$TEMPLATE_DIR}/css/boxes.css" type="text/css" />*}
{literal}
<style type="text/css">
/* <![CDATA[ */
a {
 /*background-color: #F4FFFF;*/
}
img {
 padding: 7px;
}
p {
 margin: 10px;
}
/* ]]> */
</style>
{/literal}

</head>
<body>
<div id="content">
<div class="shadow"><div>
<h3>Unihelp-Smileys</h3>
<p id="smileys">
{foreach from=$smileys item=s}
<a href="#" onclick="return AddTextExternal('{$s->getText()}');" title="smiley in Beitrag &uuml;bernehmen"><img src="{$smiley_path}/{$s->getURL()}" alt="{$s->getText()}" /></a>
{/foreach}
</p>
</div></div>{* closing .shadow and inner div of .shadow *}
</div>{* #content *}
</body>
</html>
