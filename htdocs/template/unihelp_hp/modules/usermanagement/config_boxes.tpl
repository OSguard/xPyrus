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

*}{* $Id: config_boxes.tpl 5807 2008-04-12 21:23:22Z trehn $ *}

{* <h2 id="pagename">{$user->getUsername()} &ndash; Persönliche Einstellungen</h2> *}

{if $admin_mode}
<span class="adminNote">(ADMIN)</span>
	<a href="{admin_url user=$user}">zurück zum Adminbereich</a>

{else}
<div class="shadow"><div>
<h3>Hilfe</h3>
  <ul class="bulleted">
    <li>Ordne die einzelnen Boxen so an, wie Du sie sehen willst (Drag & Drop &ndash; probier es einfach).</li>
    <li>Die Boxen können am linken und rechten Rand beliebig verschoben, ganz ausgeblendet oder mehrfach angezeigt werden.</li>
  </ul>
</div></div>
{/if} {* end if admin mode *}

{* mode internal tab navigation *}
{include file="modules/usermanagement/navigation.tpl" usermanagement_tabpanemode="boxes"}
<div class="shadow"><div>
	<noscript><p>Du solltest JavaScript aktivieren, um unsere coolen Ajax-Features zu nutzen ;-) <a href="?nojs=1">Zur Version ohne Javascript</a></p></noscript>

    {* ajax_submit is used for Behaviour *}
	<form action="#" method="post" id="ajax_submit" class="right">
        <input type="submit" value="Konfiguration speichern" /><br /> (&Auml;nderungen werden erst nach einem Neuladen der Seite aktiv)
	</form>
	<span id="ajax_status" class="right">&nbsp;</span>
<br class="clear" />

<div id="box-selection">
<div>
<h4>Boxen links</h4>
<ul id="config-boxes-left" class="config-boxes">
<li>Login-Box</li>
{foreach from=$user_boxes_left item=box name=boxes_left}
    {if $box.0}
    <li class="move" id="box-{$box.0}">{translate box=$box.0}</li>
    {/if}
{/foreach}
</ul>
</div>

<div>
<h4>Boxen rechts</h4>
<ul id="config-boxes-right" class="config-boxes">
{foreach from=$user_boxes_right item=box name=boxes_right}
    {if $box.0}
    <li class="move" id="box-{$box.0}">{translate box=$box.0}</li>
    {/if}
{/foreach}
</ul>
</div>

<div>
<h4>Verbleibende Auswahl</h4>
<ul id="config-boxes-free" class="config-boxes">
{foreach from=$user_boxes_free item=box name=boxes_free}
    <li class="move" id="box-{$box}">{translate box=$box}</li>
{/foreach}
</ul>
</div>

</div>


	{literal}
	<script type="text/javascript" language="javascript" charset="utf-8">
    Sortable.create("config-boxes-left",
     {dropOnEmpty:true,containment:["config-boxes-right", "config-boxes-left", "config-boxes-free"],constraint:false, only: 'move'});
    Sortable.create("config-boxes-right",
     {dropOnEmpty:true,containment:["config-boxes-right", "config-boxes-left", "config-boxes-free"],constraint:false, only: 'move'});
    Sortable.create("config-boxes-free",
     {dropOnEmpty:true,containment:["config-boxes-right", "config-boxes-left", "config-boxes-free"],constraint:false, only: 'move'});

     function _sendSubmit() {
        var boxesLeft = Array();
        var boxesRight = Array();
        $$("#config-boxes-left li.move").each(function(el) { boxesLeft.push(el.id.substr(4)) } );
        $$("#config-boxes-right li.move").each(function(el) { boxesRight.push(el.id.substr(4)) } );
        
        $('ajax_submit').disabled = true;
        $('ajax_status').innerHTML = "Sende...";

        var url = '/index.php'
        var pars = {
            method:      'ajaxConfigBoxes',
            mod:         'usermanagement',
            view:        'ajax',
            boxesLeft:   '' + boxesLeft,
            boxesRight:  '' + boxesRight
        }
        
        var myAjaxRequest = new Ajax.Request(url, {
            parameters: $H(pars).toQueryString(),
            onSuccess : function(request) {
                var status = request.responseText;
                if (status == '0') {
                    $('ajax_status').innerHTML = "Speichern erfolgreich!";
                    $('ajax_submit').disabled = false;
                    
                } else {
                    $('ajax_status').innerHTML = "Dir fehlen die Rechte für diese Aktion";
                    $('ajax_submit').disabled = false;
                }
            },
            onFailure: function(request) {
                $('ajax_status').innerHTML = "Fehler beim Speichern!";
                $('ajax_submit').disabled = false;
            }
        });
        
        return false;
     }

    </script>
    {/literal}

<br class="clear" />
</div></div>
