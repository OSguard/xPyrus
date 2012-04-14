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

*}<p>{$userinfo_user->getUsername()} hat folgende Kontaktinformationen für Dich freigegeben:</p>
<dl id="usercontact">
  {* IM stuff *}
  {if $userinfo_user->hasImICQ()} 
    <dt>ICQ</dt><dd>
     <a href="http://web.icq.com/whitepages/add_me/1,,,00.icq?uin={$userinfo_user->getImICQ()}&amp;action=add" title="Zur Kontaktliste hinzuf&uuml;gen">{$userinfo_user->getImICQ()}</a>
     <img src="http://web.icq.com/scripts/online.dll/?icq={$userinfo_user->getImICQ()}&amp;img=5" alt="" />
    </dd> 
  {/if}
  {if $userinfo_user->hasSkype()} 
      <dt>Skype</dt><dd><a href="skype:{$userinfo_user->getSkype()}?call">{$userinfo_user->getSkype()}
      <img src="http://goodies.skype.com/graphics/skypeme_btn_small_green.gif" border="0"></a></dd> 
  {/if}
  {if $userinfo_user->hasImJabber()} 
      <dt>Jabber</dt><dd>{$userinfo_user->getImJabber()}</dd> 
  {/if}
  {if $userinfo_user->hasImMSN()} 
      <dt>MSN</dt><dd>{$userinfo_user->getImMSN()}</dd> 
  {/if}
  {if $userinfo_user->hasImYahoo()} 
      <dt>Yahoo</dt><dd>{$userinfo_user->getImYahoo()}</dd> 
  {/if}
  {if $userinfo_user->hasImAIM()} 
      <dt>AIM</dt><dd>{$userinfo_user->getImAIM()}</dd> 
  {/if}
  {* phone numbers *}
  {if $userinfo_user->hasTelephoneMobil()} 
      <dt>Funk</dt><dd>{$userinfo_user->getTelephoneMobil()}</dd> 
  {/if}
  
  
  {* e-mail stuff *}
  {if $userinfo_user->hasPublicPGPKey()}
  <dt>PGP-Key</dt><dd>
   {$userinfo_user->getPublicPGPKey()}
  </dd>
  {/if}
  {if $userinfo_user->hasPublicEmail()}
  <dt>E-Mail-Adresse</dt><dd>
   {$userinfo_user->getPublicEmail()}
  </dd>
  {/if}
  
  {* name stuff *}
  {if $userinfo_user->hasFirstName() || $userinfo_user->hasLastName()}
  <dt>Name</dt><dd>{$userinfo_user->getFirstName()} {$userinfo_user->getLastName()}</dd>
  {/if}
  
  {* address/location stuff *}
  {if $userinfo_user->hasStreet() || $userinfo_user->hasZipCode() || $userinfo_user->hasLocation() }
  <dt>Adresse</dt><dd style="padding-left: 2em">
   {if $userinfo_user->hasStreet()}<br />{* break line here to get consistent layout *}
     {$userinfo_user->getStreet()}
   {/if}{* end if street *}  <br />
   {$userinfo_user->getZipCode()} {$userinfo_user->getLocation()}
  </dd>
  {/if}
</dl>
