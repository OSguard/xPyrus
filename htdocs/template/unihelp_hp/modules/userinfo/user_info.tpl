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

*}{* $Id: user_info.tpl 6210 2008-07-25 17:29:44Z trehn $ 
   $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/userinfo/user_info.tpl $ *}

<a id="topofpage" name="topofpage"></a>

{if $userinfo_user->getGender() == 'm'}
	{assign var="gender" value="m&auml;nnlich"}
{elseif $userinfo_user->getGender() == 'f'}
	{assign var="gender" value="weiblich"}
{else}
	{assign var="gender" value="?"}
{/if}

<div class="shadow" id="userpic">
  <div>
    <img src="{userpic_url big=$userinfo_user}" alt="Userpic von {$userinfo_user->getUsername()}" />
    {if $userinfo_user->getSignature()}
        <div class="signature">
        {$userinfo_user->getSignature()}
        </div>
    {/if}
    {* do not escape signature, because it is already parsed *}
  {if $userinfo_user->getWarningCard()}
  	{assign var="warningCard" value=$userinfo_user->getWarningCard()}
  	{if $warningCard->getType() == $warningCard->TYPE_YELLOW}
  	<div style="position:absolute; top:0; right: 0px; width: 100px; height:100px; z-index: 2000;">
  		<img src="/images/yellow_card.png" alt="gelbe Karte" title="Mit gelber Karte verwarnt" />
  	</div>
  	{elseif $warningCard->getType() == $warningCard->TYPE_RED}
  	<div style="position:absolute; top:0; right: 0px; width: 100px; height:100px; z-index: 2000;">
  		<img src="/images/red_card.png" alt="rote Karte" title="Mit roter Karte verwarnt." />
  	</div>
  	{elseif $warningCard->getType() == $warningCard->TYPE_YELLOWRED}
  	<div style="position:absolute; top:0; right: 0px; width: 100px; height:100px; z-index: 2000;">
  		<img src="/images/yellow_red_card.png" alt="gelb/rote Karte" title="Mit gelb-roter Karte verwarnt." />
  	</div>
  	{/if}
  {/if}

  </div>
</div><!-- /userpic -->

{dynamic}
<div class="shadow"  id="usericons">
 <div>
    {if !$userinfo_user->equals($visitor)}
    <a href="#guestbook_anchor" title="Im G&auml;stebuch von {$userinfo_user->getUsername()} lesen">
        <img src="{$TEMPLATE_DIR}/images/gbook.gif" alt="G&auml;stebuch lesen" />
    </a>
    <a href="#postenanker" title="Einen neuen G&auml;stebucheintrag verfassen">
        <img src="{$TEMPLATE_DIR}/images/compose.gif" alt="neuen G&auml;stebucheintrag verfassen" />
    </a>
    <a href="{pm_url new=true receivers=$userinfo_user->getUsername()}" title="Private Nachricht an {$userinfo_user->getUsername()} versenden">
        <img src="{$TEMPLATE_DIR}/images/message.gif" alt="PN senden" />
    </a>
    <a href="{course_url couseUserFiles=$userinfo_user}" title="Von {$userinfo_user->getUsername()} hochgeladene Unterlagen">
        <img src="{$TEMPLATE_DIR}/images/upload.gif" alt="hochgeladene Unterlagen" />
    </a>
   {else}
    <a href="#guestbook_anchor" title="In Deinem G&auml;stebuch lesen">
        <img src="{$TEMPLATE_DIR}/images/gbook.gif" alt="G&auml;stebuch lesen" />
    </a>
    <a href="#postenanker" title="Einen neuen Tagebucheintrag verfassen">
        <img src="{$TEMPLATE_DIR}/images/compose.gif" alt="neuen Tagebucheintrag verfassen" />
    </a>
    <a href="{pm_url receivers=$userinfo_user->getUsername()}" title="Zu Deinen Privaten Nachrichten">
        <img src="{$TEMPLATE_DIR}/images/message.gif" alt="PNs lesen" />
    </a>
    <a href="{course_url couseUserFiles=$userinfo_user}" title="von Dir hochgeladene Unterlagen">
        <img src="{$TEMPLATE_DIR}/images/upload.gif" alt="hochgeladene Unterlagen" />
    </a>
   {/if}
    {if !$userinfo_is_friend && $userinfo_permissions.friendlist_modify} {* $friendlistmodifypermisson *}
        <a href="/user/{$userinfo_user->getUsername()|escape:'url'}/addfriend" title="{$userinfo_user->getUsername()} zu meiner Freundesliste hinzuf&uuml;gen"> 
        <img src="{$TEMPLATE_DIR}/images/add_friend.gif" alt="Freundesliste hinzuf&uuml;gen" /></a>
    {elseif $userinfo_is_friend && $userinfo_permissions.friendlist_modify}
        <a href="/user/{$userinfo_user->getUsername()|escape:'url'}/removefriend" title="{$userinfo_user->getUsername()} von meiner Freundesliste entfernen">
        <img src="{$TEMPLATE_DIR}/images/remove_friend.gif" alt="Freundesliste entfernen" />
        </a>
    {/if}
    <br />
    {*
    {if !$userinfo_user->equals($visitor)}
      <a href="/em2008/user/{$userinfo_user->getUsername()}">
    {else}
      <a href="/em2008">
    {/if}
        <img src="/images/icons/sport_soccer.png" alt="Fussball" />
        <strong>EM-Tipps von {$userinfo_user->getUsername()}</strong>
    </a>
    *}
 </div>	
 </div><!-- /usericons -->
{/dynamic}

<div class="shadow" id="userinfo">
  <div>
    {* TODO: privacy checks in data *}
    {include file="modules/userinfo/user_info_box.tpl"}
  </div>
</div><!-- /userinfo -->
  
  
  <div class="shadow" id="userfriends">
  <div class="nopadding">
  <h3>Auf {if $userinfo_user->equals($visitor)}meiner{elseif strlen($userinfo_user->getUsername())>16}der{else}{$userinfo_user->getUsername()|genitiv}{/if} Freundesliste</h3>
	{if $userinfo_user->isFriendListpublic() || $visitor->isLoggedIn() || $userinfo_onignorelist}
	<ul class="friendhover">
       {foreach from=$userinfo_friends item=friend name=friends}
		{strip}<li>{user_info_link user=$friend}
        <div>
        	<a href="{user_info_url user=$friend}">
	            <img src="{userpic_url small=$friend}" alt="Userpic von {$friend->getUsername()}" />
	            {if $friend->getFriendType()=='Friend'}
	              <img src="/images/symbols/friend_friendship.gif" alt="{$friend->getUsername()} ist 
	              {if $friend->getGender()!='f'} ein guter Freund
	              {else} eine gute Freundin
	              {/if}
	              " title="{$friend->getUsername()} ist 
	              {if $friend->getGender()!='f'} ein guter Freund
	              {else} eine gute Freundin
	              {/if}
	              " />
	            {elseif $friend->getFriendType()=='Family'}
	              <img src="/images/symbols/friend_family.gif" alt="{$friend->getUsername()} 
	              kommt aus der Familie
	              " title="{$friend->getUsername()} kommt aus der Familie" />
	            {elseif $friend->getFriendType()=='Love'}
	              <img src="/images/symbols/friend_love.gif" alt="{$friend->getUsername()} und {$userinfo_user->getUsername()} sind ein Paar"
	              title="{$friend->getUsername()} und {$userinfo_user->getUsername()} sind ein Paar" />
	            {/if}
            </a>
        </div></li>{/strip}
        {foreachelse}
        	{* in case friendslist is empty *}
        	<li>Freundesliste ist noch leer.</li>
      {/foreach}
	</ul>
	{else}
		Freundesliste nicht verfügbar.
	{/if}
      <br class="clear" />
  </div>
  </div><!-- /userfriends -->
  
  <script type="text/javascript" src="{$TEMPLATE_DIR}/javascript/userinfo.js"></script>
  
{dynamic}
  <br class="clear" />
  <div id="tabNavigation" style="margin-bottom: -37px;">
  <ul style="margin: 0px;">
      <li>
        <a{if $userinfo_tabpanemode=='description'} class="active"{/if} href="{user_info_url user=$userinfo_user tab="description"}#tabNavigation" id="tablink_description" onclick="return loadTab('description');">Eigene Beschreibung</a></li>
      <li>
        <a{if $userinfo_tabpanemode=='user_contact'} class="active"{/if} href="{user_info_url user=$userinfo_user tab="user_contact"}#tabNavigation" id="tablink_user_contact" onclick="return loadTab('user_contact');">Kontakt-Daten</a></li>
      <li>
        <a{if $userinfo_tabpanemode=='user_stats'} class="active"{/if} href="{user_info_url user=$userinfo_user tab="user_stats"}#tabNavigation" id="tablink_user_stats" onclick="return loadTab('user_stats');">User-Statistiken</a></li>
      {if $visitor->hasRight('FEATURE_SMALLWORLD') && $smarty.const.DIJKSTRA_AVAILABLE && $userinfo_is_external_view}
      <li>
        <a{if $userinfo_tabpanemode=='smallworld'} class="active"{/if} href="{user_info_url user=$userinfo_user tab="smallworld"}#tabNavigation" id="tablink_smallworld" onclick="return loadTab('smallworld');">Vernetzung</a></li>{/if}{* end rights if *}
      {if $visitor->hasRight('GB_ADVANCED_STATS') && !$userinfo_is_external_view || $visitor->hasRight('GB_ADVANCED_STATS_ALL')}
      <li>
        <a{if $userinfo_tabpanemode=='guestbook_stats'} class="active"{/if} href="{user_info_url user=$userinfo_user tab="guestbook_stats"}#tabNavigation" id="tablink_guestbook_stats" onclick="return loadTab('guestbook_stats');">G&auml;stebuch-Statistiken</a></li>{/if}{* end rights if *}
      {if 1 == 1}
      <li>
        <a{if $userinfo_tabpanemode=='user_awards'} class="active"{/if} href="{user_info_url user=$userinfo_user tab="user_awards"}#tabNavigation" id="tablink_user_awards" onclick="return loadTab('user_awards');">Awards</a></li>{/if}{* end rights if *}  
  </ul>
  <br class="clear" />
  </div>
  <div class="shadow ie6static">
  <div id="tabcontent" class="entry">
    {* do not escape here, the variable contains valid XHTML *}
    {$userinfo_tabpane}
  </div></div>
{/dynamic}

  {*<div class="shadow" id="userAboForum">
  <div>
  <h3>Meine Forum Abos</h3>
  <ul>
  {foreach from=$userinfo_aboForum item=forum}
  	<li>{forum_link forum=$forum}</li>
  {foreachelse}
  	<li>Forum? wie? wo?</li>	
  {/foreach}
  </ul>
  </div>
 </div><!-- /userAboForum -->*}

{* START dynamic template bottom *}

{dynamic}
 
{$userinfo_diary_view->getContent()}

{$userinfo_gb_view->getContent()}

{* display errors on entry creation *}
{errorbox caption="Fehler beim Erstellen" prestring='<a name="post"></a><a name="postdiary"></a>'}

{* after complete guest book insert google ads *}
  {include file='boxes/ads_google_GB.tpl'}
  {* closing google ads *}

{if $userinfo_editentry && $isPreview}
	<div id="userinfo_preview"><a name="postdiary"></a>
	{include file="modules/userinfo/userinfo_preview.tpl"}
	</div>
{/if}

<div class="shadow" id="makeEntry">
  <div class="nopadding">
  {if $userinfo_onignorelist}
    {$userinfo_user->getUsername()} hat Dich auf seiner Ignore-List.
  {else}
  {if !$userinfo_entrymode}
    <p>Es d&uuml;rfen nur registrierte Benutzer bewerten und Kommentare im G&auml;stebuch eines Users abgeben! <a href="/user_new.php" title="Als neuer User registrieren">Registriere Dich jetzt erstmalig</a> oder <a href="#topofpage" title="nach oben">melde Dich an</a>!</p></div>
  {elseif $userinfo_entrymode == "guestbook"}
    <h3>{$userinfo_user->getUsername()} freut sich &uuml;ber einen G&auml;stebucheintrag von Dir</h3>
    {if !$ie6}
       {* include right box containing formatting options and smileys *}
       {include file="common/entry_options.tpl"}
    
       <div id="entryfield">
         {include file="modules/userinfo/entry_guestbook.tpl"}
       </div>
    {else}
       <table id="ie6newentry">
       <tr>
       <td colspan="2" class="buttonbar">{include file="common/entry_buttonbar.tpl"}</td>
       </tr>
       <tr>
       <td>{include file="modules/userinfo/entry_guestbook.tpl"}</td>
       <td valign="top" width="150">{include file="common/entry_smileys.tpl"}</td>
       </tr>
       </table>
     {/if}
  {elseif $userinfo_entrymode == "diary"}
    {if $userinfo_state == 2}  {* state==1: add mode ; state==2: edit mode *}
        <h3>&Auml;ndere einen bestehenden Tagebucheintrag ab</h3>
    {elseif $userinfo_state == 1}
        <h3>F&uuml;ge einen Tagebucheintrag hinzu</h3>
    {/if}
    
    {if !$ie6}
       {* include right box containing formatting options and smileys *}
       {include file="common/entry_options.tpl"}
    
       <div id="entryfield">
         {include file="modules/userinfo/entry_diary.tpl"}
       </div>
    {else}
       <table id="ie6newentry">
       <tr>
       <td colspan="2" class="buttonbar">{include file="common/entry_buttonbar.tpl"}</td>
       </tr>
       <tr>
       <td>{include file="modules/userinfo/entry_diary.tpl"}</td>
       <td valign="top" width="150">{include file="common/entry_smileys.tpl"}</td>
       </tr>
       </table>
     {/if}
  {elseif $userinfo_entrymode == "comment"}
    <h3>F&uuml;ge einen Kommentar hinzu</h3>

    <div id="entryfield">
      {include file="modules/userinfo/entry_comment.tpl"}
    </div>
  {elseif $userinfo_entrymode == "prohibited"}
    Diese Funktion ist gesperrt. 
  {elseif $userinfo_entrymode == "frozen"}
    Das G&auml;stebuch des Users ist momentan eingefroren. Es k&ouml;nnen keine Eintr&auml;ge vorgenommen werden.
  {/if} {* end if entrymode *}
  {/if} {* end if: not on ignore list *}
  {* some code for IE ... *}
  <br class="clear" />&nbsp;
  </div>{* closind inner shadow div; no id or class given *}
</div>{* closing class shadow *}
<br class="clear" />
  

{/dynamic}
{* END dynamic template bottom *}
 
