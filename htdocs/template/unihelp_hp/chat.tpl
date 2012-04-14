<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<link rel="shortcut icon" href="{$TEMPLATE_DIR}/{$CSS_DIR}/images/icon.ico" />
<link title="css" rel="stylesheet" href="/template/unihelp_hp/css/unihelp.css" type="text/css" />
<script src="/template/unihelp_hp/unihelp.js" type="text/javascript"></script>
<title>UniHelp-Chat</title>
</head>
<body>
<div id="wrapper">
<div id="contentfloatholder">
<div id="content" style="width: auto;">
<div id="centerpad" style="margin: 0;">
{if !$nobanner}
    {dynamic}{include file="banner.tpl"}{/dynamic}
{/if}

{* display main content *}
<div class="shadow">
  <div>
<applet code=IRCApplet.class codebase="/lib-irc/" archive="irc.jar,pixx.jar" width=640 height=400>
<param name="CABINETS" value="irc.cab,securedirc.cab,pixx.cab">

<param name="nick" value="{$user->getUsername()}">
<param name="alternatenick" value="{$user->getUsername()}23">
<param name="name" value="{$user->getUsername()}@UniHelp">
<param name="host" value="irc.freenode.net">
<param name="port" value="7000">{*
<param name="style:smileys" value="true">*}
<param name="gui" value="pixx">
<param name='style:sourcecolorrule1' value='all all 0=ffffff 1=000000 2=E85312 3=E853122 4=E853123 5=E85312 6=E85312'>
<param name="style:sourcefontrule1" value="all all Verdana 12">
<param name="pixx:color0" value="000000">
<param name="pixx:color1" value="000000">
<param name="pixx:color2" value="7F7F7F">
<param name="pixx:color3" value="7F7F7F">
<param name="pixx:color4" value="EFEFEF">
<param name="pixx:color5" value="BCA067">
<param name="pixx:color6" value="F9F6EC">
<param name="pixx:color7" value="E85312">
<param name="pixx:color8" value="40DF0D">
<param name="pixx:color9" value="E85312">
<param name="pixx:color10" value="BCA067">
<param name="pixx:color11" value="BCA067">
<param name="pixx:color12" value="40DF0D">
<param name="pixx:color13" value="40DF0D">
<param name="pixx:color14" value="40DF0D">
<param name="pixx:color15" value="BCA067">
<param name="language" value="german">
<param name="pixx:language" value="pixx-german">
{*<param name="languageencoding" value="UTF8">
<param name="pixx:languageencoding" value="UTF8">*}

<param name="command1" value="/join #unihelp">

</applet>
</div></div>
</div>
</div><!-- /content -->
</body>
</html>

