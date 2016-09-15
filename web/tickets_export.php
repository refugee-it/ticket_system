<?php
/* Copyright (C) 2014-2016  Stephan Kreutzer
 *
 * This file is part of ticket system for refugee-it.de.
 *
 * ticket system for refugee-it.de is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License version 3 or any later version,
 * as published by the Free Software Foundation.
 *
 * ticket system for refugee-it.de is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License 3 for more details.
 *
 * You should have received a copy of the GNU Affero General Public License 3
 * along with ticket system for refugee-it.de. If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * @file $/web/tickets_export.php
 * @author Stephan Kreutzer
 * @since 2014-06-08
 */



session_start();



require_once("./libraries/languagelib.inc.php");
require_once(getLanguageFile("tickets_export"));
require_once("./libraries/ticket_management.inc.php");

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
     "<!DOCTYPE html\n".
     "    PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\n".
     "    \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n".
     "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"".getCurrentLanguage()."\" lang=\"".getCurrentLanguage()."\">\n".
     "    <head>\n".
     "        <title>".LANG_PAGETITLE."</title>\n".
     "        <meta http-equiv=\"expires\" content=\"1296000\"/>\n".
     "        <meta http-equiv=\"content-type\" content=\"application/xhtml+xml; charset=UTF-8\"/>\n".
     "    </head>\n".
     "    <body>\n".
     "        <div>\n";

$tickets = GetTickets(null);

if (is_array($tickets) === true)
{
    $ticketsHTML = null;

    foreach ($tickets as $ticket)
    {
        if ((int)$ticket['status'] === TICKET_STATUS_PUBLIC)
        {
            if ($ticketsHTML == null)
            {
                $ticketsHTML .= "          <table border=\"1\">\n".
                                "            <tr>\n".
                                "              <th>".LANG_COLUMN_TITLE."</th>\n".
                                "              <th>".LANG_COLUMN_DESCRIPTION."</th>\n".
                                "            </tr>\n";
            }

            $ticketsHTML .= "            <tr>\n".
                            "              <td>".htmlspecialchars($ticket['title'], ENT_COMPAT | ENT_HTML401, "UTF-8")."</td>\n".
                            "              <td>".htmlspecialchars($ticket['description'], ENT_COMPAT | ENT_HTML401, "UTF-8")."</td>\n".
                            "            </tr>\n";
        }
    }

    if ($ticketsHTML !== null)
    {
        $ticketsHTML .= "          </table>\n";
        echo $ticketsHTML;
    }
}

echo "        </div>\n".
     "    </body>\n".
     "</html>\n";




?>
