<?php
/* Copyright (C) 2014-2017  Stephan Kreutzer
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
 * @file $/web/ticket_view.php
 * @brief Views a ticket.
 * @author Stephan Kreutzer
 * @since 2014-06-08
 */



session_start();

if (isset($_SESSION['user_id']) === true)
{
    require_once("./libraries/session.inc.php");
}

$id = null;

if (isset($_POST['id']) === true)
{
    $id = (int)$_POST['id'];
}

if (isset($_GET['id']) === true)
{
    $id = (int)$_GET['id'];
}

if ($id == null)
{
    header("HTTP/1.1 404 Not Found");
    exit();
}

require_once("./libraries/ticket_management.inc.php");

$ticket = GetTicketById($id);

if (is_array($ticket) != true)
{
    header("HTTP/1.1 404 Not Found");
    exit();
}

$isOwner = false;

if (isset($_SESSION['user_id']) === true)
{
    if ((int)$_SESSION['user_id'] === (int)$ticket['id_user'] ||
        (int)$_SESSION['role'] === USER_ROLE_ADMIN)
    {
        $isOwner = true;
    }
}

if ($isOwner == false &&
    $ticket['status'] != TICKET_UPLOAD_STATUS_PUBLIC)
{
    header("HTTP/1.1 401 Unauthorized");
    exit();
}



require_once("./libraries/languagelib.inc.php");
require_once(getLanguageFile("ticket_view"));

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
     "<!DOCTYPE html\n".
     "    PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\n".
     "    \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n".
     "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"".getCurrentLanguage()."\" lang=\"".getCurrentLanguage()."\">\n".
     "    <head>\n".
     "        <title>".LANG_PAGETITLE."</title>\n".
     "        <link rel=\"stylesheet\" type=\"text/css\" href=\"mainstyle.css\"/>\n".
     "        <link rel=\"stylesheet\" type=\"text/css\" href=\"uploaded_images_style.css\"/>\n".
     "        <style type=\"text/css\">\n".
     "          .ticket_info_label\n".
     "          {\n".
     "              font-weight: bold;\n".
     "          }\n".
     "        </style>\n".
     "        <meta http-equiv=\"expires\" content=\"1296000\"/>\n".
     "        <meta http-equiv=\"content-type\" content=\"application/xhtml+xml; charset=UTF-8\"/>\n".
     "    </head>\n".
     "    <body>\n".
     "        <div class=\"mainbox\">\n".
     "          <div class=\"mainbox_header\">\n".
     "            <h1 class=\"mainbox_header_h1\">".LANG_HEADER."</h1>\n".
     "          </div>\n".
     "          <div class=\"mainbox_body\">\n".
     "            <div>\n".
     "              <h2>".htmlspecialchars($ticket['title'], ENT_COMPAT | ENT_HTML401, "UTF-8")."</h2>\n".
     "              <div>\n".
     "                <span class=\"ticket_info_label\">".LANG_TICKET_CREATED."</span> ".htmlspecialchars($ticket['datetime_created'], ENT_COMPAT | ENT_HTML401, "UTF-8")."<br/>\n";

if ($isOwner === true)
{
    echo "                <span class=\"ticket_info_label\">".LANG_TICKET_STATUS."</span> ";

    switch ($ticket['status'])
    {
    case TICKET_STATUS_NOT_PUBLIC:
        echo LANG_TICKET_STATUS_NOT_PUBLISHED;
        break;
    case TICKET_STATUS_PUBLIC:
        echo LANG_TICKET_STATUS_PUBLIC;
        break;
    case TICKET_STATUS_TRASHED:
        echo LANG_TICKET_STATUS_TRASHED;
        break;
    default:
        echo LANG_TICKET_STATUS_UNKNOWN;
        break;
    }

    echo "<br/>";

    echo "                <span class=\"ticket_info_label\">".LANG_TICKET_CREATOR_NAME."</span> ".htmlspecialchars($ticket['creator_name'], ENT_COMPAT | ENT_HTML401, "UTF-8")."<br/>\n".
         "                <span class=\"ticket_info_label\">".LANG_TICKET_CREATOR_E_MAIL_ADDRESS."</span> ".htmlspecialchars($ticket['creator_e_mail'], ENT_COMPAT | ENT_HTML401, "UTF-8")."<br/>\n".
         "                <span class=\"ticket_info_label\">".LANG_TICKET_CREATOR_PHONE_NUMBER."</span> ".htmlspecialchars($ticket['creator_phone'], ENT_COMPAT | ENT_HTML401, "UTF-8")."<br/>\n";
}

echo "                <span class=\"ticket_info_label\">".LANG_TICKET_DESCRIPTION."</span>\n".
     "                <p>".htmlspecialchars($ticket['description'], ENT_COMPAT | ENT_HTML401, "UTF-8")."</p>\n".
     "              </div>\n";

$imagesHTML = null;

if (is_array($ticket['images']) === true)
{
    foreach ($ticket['images'] as $image)
    {
        if ((int)$image['status'] == TICKET_UPLOAD_STATUS_TRASHED)
        {
            continue;
        }

        if ($imagesHTML === null)
        {
            $imagesHTML = "              <div>\n".
                            "                <span class=\"ticket_info_label\">".LANG_TICKET_UPLOADED_IMAGES."</span>\n";
        }

        $imageDisplayName = htmlspecialchars($image['display_name'], ENT_COMPAT | ENT_HTML401, "UTF-8");

        $imagesHTML .= "                <div>\n".
                        "                  <span>\n".
                        "                    ".$imageDisplayName."\n".
                        "                  </span>\n".
                        "                  <br/>\n".
                        "                  <a href=\"./uploads/images/".htmlspecialchars($image['internal_name'], ENT_COMPAT | ENT_HTML401, "UTF-8")."\">\n".
                        "                    <img class=\"image_preview\" src=\"./uploads/images/".htmlspecialchars($image['internal_name'], ENT_COMPAT | ENT_HTML401, "UTF-8")."\" alt=\"".$imageDisplayName."\"/>\n".
                        "                  </a>\n".
                        "                </div>\n";
    }

    if ($imagesHTML != null)
    {
        $imagesHTML .= "              </div>\n";
    }

    if ($imagesHTML != null)
    {
        echo $imagesHTML;
    }
}

if (isset($_SESSION['user_id']) === true)
{
    if ((int)$_SESSION['user_id'] === (int)$ticket['id_user'])
    {
        echo "              <form action=\"ticket_edit.php\" method=\"post\">\n".
             "                <fieldset>\n".
             "                  <input type=\"hidden\" name=\"id\" value=\"".$id."\"/>\n".
             "                  <input type=\"submit\" value=\"".LANG_BUTTON_EDIT."\"/><br/>\n".
             "                </fieldset>\n".
             "              </form>\n";
    }
}

echo "              <form action=\"tickets_list.php\" method=\"post\">\n".
     "                <fieldset>\n".
     "                  <input type=\"hidden\" name=\"id\" value=\"".$id."\"/>\n".
     "                  <input type=\"submit\" value=\"".LANG_BUTTON_BACK."\"/><br/>\n".
     "                </fieldset>\n".
     "              </form>\n".
     "            </div>\n".
     "          </div>\n".
     "        </div>\n".
     "        <div class=\"footerbox\">\n".
     "          <a href=\"license.php\" class=\"footerbox_link\">".LANG_LICENSE."</a>\n".
     "        </div>\n".
     "    </body>\n".
     "</html>\n";




?>
