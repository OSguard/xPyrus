<?php
#
# xPyrus - Framework for Community and knowledge exchange
# Copyright (C) 2003-2008 UniHelp e.V., Germany
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as
# published by the Free Software Foundation, only version 3 of the
# License.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU Affero General Public License
# along with this program. If not, see http://www.gnu.org/licenses/agpl.txt
#
/*
 * Created on 08.06.2007 by schnueptus
 * sunburner Unihelp.de
 */
 
/**
 * Bug and Project Class used by Mantis-Webservice (NuSOAP Engine)
 * Have a look at http://bugs.unihelp.de/mc/mantisconnect?wsdl
 */

 class BugData {
    var $id ="0";
    var $view_state = null;
    var $last_updated = null;
    var $project = null;
    var $category = null;
    var $priority = null;
    var $severity = null;
    var $status = null;
    var $reporter = null;
    var $summary = null;
    var $version = null;
    var $build = null;
    var $platform = null;
    var $os = null;
    var $os_build = null;
    var $reproducibility = null;
    var $date_submitted = null;
    var $sponsorship_total = null;
    var $handler = null;
    var $projection = null;
    var $eta = null;
    var $resolution = null;
    var $fixed_in_version = null;
    var $description = "";
    var $steps_to_reproduce = null;
    var $additional_information = null;
    var $attachments = null;
    var $relationships = null;
    var $notes = null;
    var $custom_fields = null;
}

class ProjectData {
    var $id = 0;
    var $name = null;
}

class BugAttachment{
    var $id=0;
    var $name = null;
    var $file_type = null;
    var $content = null;
}

class Note{
    var $id = 0;
    var $reporter = null;
    var $text = null;
    var $view_state = null;
    var $date_submitted = null;
    var $last_modified = null;
}
 
?>
