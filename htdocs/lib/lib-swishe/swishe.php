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

// $Id: swishe.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/lib/lib-swishe/swishe.php $

class SwishResult {
    public $weight;
    public $path;
    public $title;

    public function __construct($weight,$path,$title) {
        $this->weight = $weight;
        $this->path = $path;
        $this->title = $title;
    }
}

/**
 * this class encapsulates operations on the swish-e utility
 */
class SwishE {

    /**
     * creates an index for course files
     */
    public static function createIndex() {
        $swish = popen('/usr/bin/swish-e -c ' . SWISHE_COURSE_CONFIG, 'r');
        while (!feof($swish)) {
            $line = fgets($swish, 1024);
            echo $line . "<br />";
        }
        pclose($swish);
    }
    
    /**
     * queries the course file index
     */
    public static function query($query, $limit = 10, $offset = 1) {
        $swish = popen('/usr/bin/swish-e -f ' . UPLOAD_DIR . '/course.index -w ' . escapeshellarg($query) . ' -m ' . $limit . ' -b ' . $offset, 'r');
        $results = array();
        $totalNumber = 0;
        $runTime = 0;
        
        while (!feof($swish)) {
            $line = fgets($swish, 1024);
            #echo $line . "<br />";
            if ($line[0] == '#') {
                if (preg_match('/^# Number of hits: (\d+)/', $line, $matches)) {
                    $totalNumber = $matches[1];
                }
                if (preg_match('/^# Search time: ([\d\.]+)/', $line, $matches)) {
                    $runTime = $matches[1];
                }
            }
            if (preg_match('/^(\d+) ([^ ]+) "([^"]+)" (\d+)/', $line, $matches)) {
                $results[] = new SwishResult($matches[1], $matches[2], $matches[3]);
            }
        }
        pclose($swish);

        return array($totalNumber, $runTime, $results);
    }
}

?>
