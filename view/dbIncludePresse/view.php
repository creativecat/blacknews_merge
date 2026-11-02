<?php
/**
 * ,-----.  ,--.              ,--.    ,-----.          ,--.       ,-----.,--.   ,--. ,---.
 * |  |) /_ |  | ,--,--. ,---.|  |,-.'  .--./ ,--,--.,-'  '-.    '  .--./|   `.'   |'   .-'
 * |  .-.  \|  |' ,-.  || .--'|     /|  |    ' ,-.  |'-.  .-'    |  |    |  |'.'|  |`.  `-.
 * |  '--' /|  |\ '-'  |\ `--.|  \  \'  '--'\\ '-'  |  |  |      '  '--'\|  |   |  |.-'    |
 * `------' `--' `--`--' `---'`--'`--'`-----' `--`--'  `--'       `-----'`--'   `--'`-----'
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *   @author			Matthias Glienke
 *   @copyright			2018, Black Cat Development
 *   @link				http://blackcat-cms.org
 *   @license			http://www.gnu.org/licenses/gpl.html
 *   @category			CAT_Modules
 *   @package			blackNews
 *
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined("CAT_PATH")) {
    include CAT_PATH . "/framework/class.secure.php";
} else {
    $root = "../";
    $level = 1;

    while ($level < 10 && !file_exists($root . "framework/class.secure.php")) {
        $root .= "../";
        $level++;
    }

    if (file_exists($root . "framework/class.secure.php")) {
        include $root . "framework/class.secure.php";
    } else {
        trigger_error(
            sprintf(
                "[ <b>%s</b> ] Can't include class.secure.php!",
                $_SERVER["SCRIPT_NAME"]
            ),
            E_USER_ERROR
        );
    }
}
// end include class.secure.php

$bN = blackNews::getParserValue();

$entries = $bN["entries"] ?? [];
if (!is_array($entries)) {
    $entries = [];
}

#$first = array_shift($entries);
#blackNews::setParserValue("firstEntry", $first);
#blackNews::setParserValue("entries", $entries);

#blackNews::setParserValue("entries", $entries);

$getOptions = CAT_Helper_Page::getInstance()
    ->db()
    ->query(
        "SELECT * FROM `:prefix:mod_blackNewsOptions` WHERE `section_id` = :secID",
        [
            "secID" => $bN["options"]["setNews"],
        ]
    );

if ($getOptions && $getOptions->rowCount() > 0) {
    while (!false == ($get = $getOptions->fetch())) {
        $opt[$get["name"]] = $get["value"];
    }
}
blackNews::setParserValue("orgOptions", $opt);

// blackNews::setParserValue("catIcons", [
//     "ics-events" => "ics-star-full",
//     "allgemeine-neuigkeiten" => "ics-ics-logo",
//     "erfahrungsberichte" => "ics-quill",
//     "ics-karriere-workshops" => "ics-brain",
//     "ics-award" => "ics-trophy1",
//     "ics-starter-duales-studium" => "ics-graduation",
// ]);

?>
