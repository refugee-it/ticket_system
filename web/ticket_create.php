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
 * @file $/web/ticket_create.php
 * @brief Create a new ticket.
 * @author Stephan Kreutzer
 * @since 2014-06-08
 */



session_start();


require_once("./libraries/languagelib.inc.php");
require_once(getLanguageFile("ticket_create"));
require_once("./language_selector.inc.php");

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
     "<!DOCTYPE html\n".
     "    PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\n".
     "    \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n".
     "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"".getCurrentLanguage()."\" lang=\"".getCurrentLanguage()."\">\n".
     "    <head>\n".
     "        <title>".LANG_PAGETITLE."</title>\n".
     "        <link rel=\"stylesheet\" type=\"text/css\" href=\"mainstyle.css\"/>\n".
     "        <meta http-equiv=\"expires\" content=\"1296000\"/>\n".
     "        <meta http-equiv=\"content-type\" content=\"application/xhtml+xml; charset=UTF-8\"/>\n".
     "    </head>\n".
     "    <body>\n".
     getHTMLLanguageSelector("ticket_create.php").
     "        <div class=\"mainbox\">\n".
     "          <div class=\"mainbox_header\">\n".
     "            <h1 class=\"mainbox_header_h1\">".LANG_HEADER."</h1>\n".
     "          </div>\n".
     "          <div class=\"mainbox_body\">\n";

if (isset($_POST['title']) == false ||
    isset($_POST['description']) == false ||
    isset($_POST['creatorname']) == false ||
    isset($_POST['creatoremail']) == false ||
    isset($_POST['creatorphone']) == false)
{
    echo "            <form action=\"ticket_create.php\" method=\"post\">\n".
         "              <fieldset>\n".
         "                <input name=\"title\" type=\"text\" size=\"40\" maxlength=\"60\"/> ".LANG_TICKETTITLECAPTION."<br/>\n".
         "                <textarea name=\"description\" cols=\"80\" rows=\"12\">".LANG_TICKETDESCRIPTIONCAPTION."</textarea><br/>\n".
         "                <input type=\"text\" name=\"creatorname\" value=\"\" size=\"40\" maxlength=\"255\"/> ".LANG_CREATORNAMECAPTION."<br/>\n".
         "                <input type=\"text\" name=\"creatoremail\" value=\"\" size=\"40\" maxlength=\"255\"/> ".LANG_CREATOREMAILCAPTION."<br/>\n".
         "                <input type=\"text\" name=\"creatorphone\" value=\"\" size=\"40\" maxlength=\"255\"/> ".LANG_CREATORPHONECAPTION."<br/>\n".
         "                <input type=\"submit\" value=\"".LANG_TICKETCREATEBUTTON."\"/>\n".
         "              </fieldset>\n".
         "            </form>\n";

}
else
{
    require_once("./libraries/ticket_management.inc.php");

    $result = AddNewTicket($_POST['title'],
                           $_POST['description'],
                           $_POST['creatorname'],
                           $_POST['creatoremail'],
                           $_POST['creatorphone'],
                           LANG_NEWTICKETNOTIFICATIONTEXT);

    if (is_array($result) === true)
    {
        echo "            <p>\n".
             "              <span class=\"success\">".LANG_TICKETCREATEDSUCCESSFULLY."</span>\n".
             "            </p>\n".
             "            <form action=\"ticket_upload.php\" method=\"post\">\n".
             "              <fieldset>\n".
             "                <input type=\"hidden\" name=\"ticket_handle\" value=\"".$result['handle']."\"/>\n".
             "                <input type=\"submit\" value=\"".LANG_CONTINUE."\"/>\n".
             "              </fieldset>\n".
             "            </form>\n";
    }
    else
    {
        echo "            <p>\n".
             "              <span class=\"error\">".LANG_TICKETCREATEFAILED."</span>\n".
             "            </p>\n".
             "            <form action=\"ticket_create.php\" method=\"post\">\n".
             "              <fieldset>\n".
             "                <input type=\"submit\" value=\"".LANG_BACK."\"/>\n".
             "              </fieldset>\n".
             "            </form>\n";
    }
}

echo "          </div>\n".
     "        </div>\n".
     "        <div class=\"footerbox\">\n".
     "          <a href=\"license.php\" class=\"footerbox_link\">".LANG_LICENSE."</a>\n".
     "        </div>\n".
     "    </body>\n".
     "</html>\n";




?>
