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

*}{* $Id: error.tpl 5807 2008-04-12 21:23:22Z trehn $ *}

<h2 id="pagename" class="standalone">##error_occurred##</h2>

<div class="box errorbox"><h3>##error##</h3>
    {$error_string}
</div>

<a href="{$controller_url}" onclick="history.back(); return false;">##error_goBack##</a>
