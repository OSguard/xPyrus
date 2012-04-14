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

*}<dl id="userstat">
    <dt>Uploads des Users</dt><dd>{$userinfo_user->getCourseFilesUploads()|default:0}</dd>
	<dt>Downloads vom User</dt><dd>{$userinfo_user->getCourseFilesDownloadsByOthers()|default:0}</dd>
	<dt>Downloads des Users</dt><dd>{$userinfo_user->getCourseFilesDownloads()|default:0}</dd>
	<dt>Eintr&auml;ge im GB</dt><dd>{$userinfo_user->getGBEntries()|default:0}</dd>
	<dt>Beitr&auml;ge im Forum</dt><dd>{$userinfo_user->getForumEntries()|default:0}</dd>
	<dt>Views</dt><dd>{$userinfo_user->getProfileViews()|default:0}</dd>
</dl>
