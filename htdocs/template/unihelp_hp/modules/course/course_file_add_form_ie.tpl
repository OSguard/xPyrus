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

*}<fieldset>
<input name="id" id="id" value="{$course->id}" type="hidden" />
{if $coursefiles_file}
<input name="file_id" id="file_id" value="{$coursefiles_file->id}" type="hidden" />
{/if}
<table>
<tr>
<td>
{if $coursefiles_file && $coursefiles_file->id && $coursefiles_file->getFileName()} {* if file is kept in session/has been uploaded yet *}
	Bisherige Datei: {$coursefiles_file->getFileName()} <br />
	Neue Version:
{/if}
	{if $central_errors.missingFieldsObj.course_file}<span class="missing">{/if}
	    <label for="course_file">Datei ausw&auml;hlen (max. {$coursefiles_maxattachmentsize_kb}KB):</label>
	    <input type="file" id="course_file" name="course_file" maxlength="{$coursefiles_maxattachmentsize}" />
	{if $central_errors.missingFieldsObj.course_file}</span>{/if}

    <br />
    <label for="description">Kurzbeschreibung:</label>
    <textarea rows="4" cols="55" id="description" name="description" style="width: 95%">{if $coursefiles_file}{$coursefiles_file->getDescription()}{/if}</textarea>
    <br />
    <span class="warning">
      Bitte alle Felder sorgf&auml;ltig ausf&uuml;llen. Danke Dir.
    </span>
</td>
<td>
{if $central_errors.missingFieldsObj.category}<span class="missing">{/if}
    <label for="category">Rubrik:</label>
    {if $coursefiles_file}
	    {assign var="categoryId" value=$coursefiles_file->getCategoryId()}
	{else}
		{assign var="categoryId" value="-1"}
	{/if}
    
    <select id="category" name="category">
        <option value="0">Bitte ausw&auml;hlen</option>
        {foreach item=category from=$coursefilescategories}
        	<option value="{$category->id}" {if $categoryId==$category->id}selected="selected"{/if}>{$category->getName()}</option>	
        {/foreach}
    </select>
{if $central_errors.missingFieldsObj.category}</span>{/if}
    <br />

{if $central_errors.missingFieldsObj.semester}<span class="missing">{/if}    
    <label for="semester">Semester:</label>
    {if $coursefiles_file}
	    {assign var="semesterId" value=$coursefiles_file->getSemesterId()}
	{else}
		{assign var="semesterId" value="-1"}
	{/if}
    
    <select id="semester" name="semester">
        <option value="0">Bitte ausw&auml;hlen</option>
        {foreach item=semester from=$coursefilessemesters}
        <option value="{$semester->id}" {if $semesterId==$semester->id}selected="selected"{/if}>{$semester->getName()}</option>
        {/foreach}
    </select>
{if $central_errors.missingFieldsObj.semester}</span>{/if}    
    <br />
    
    <label for="costs">Preis:</label>
    {if $coursefiles_file}
	    {assign var="costs" value=$coursefiles_file->getCosts()}
	{else}
		{assign var="costs" value="-1"}
	{/if}
    <select id="costs" name="costs">
        <option value="1" {if $costs==1}selected="selected"{/if}>1 Punkt</option>
        <option value="2" {if $costs==2}selected="selected"{/if}>2 Punkte</option>
        <option value="3" {if $costs==3}selected="selected"{/if}>3 Punkte</option>
        <option value="4" {if $costs==4}selected="selected"{/if}>4 Punkte</option>
        <option value="5" {if $costs==5}selected="selected"{/if}>5 Punkte</option>
        <option value="6" {if $costs==6}selected="selected"{/if}>6 Punkte</option>
        <option value="7" {if $costs==7}selected="selected"{/if}>7 Punkte</option>
        <option value="8" {if $costs==8}selected="selected"{/if}>8 Punkte</option>
        <option value="9" {if $costs==9}selected="selected"{/if}>9 Punkte</option>
        <option value="10" {if $costs==10}selected="selected"{/if}>10 Punkte</option>
    </select>
    <br />
    
    
    <input type="submit" value="{if $coursefiles_file}&Auml;nderungen hochladen{else}Datei ver&ouml;ffentlichen{/if}" accesskey="s" name="upload_submit" />
    <br />
    <span class="warning">
     1 mal klicken. Hochladen dauert etwas !
    </span>
</td>
</tr>
</table>
</fieldset>
