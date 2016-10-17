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
 * @file $/web/ticket_edit.php
 * @brief Views a ticket.
 * @author Stephan Kreutzer
 * @since 2014-06-08
 */



session_start();

if (isset($_SESSION['user_id']) !== true)
{
    exit();
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
    exit();
}

require_once("./libraries/ticket_management.inc.php");

$ticket = GetTicketById($id);

if (is_array($ticket) !== true)
{
    exit();
}

if ((int)$_SESSION['user_id'] != (int)$ticket['id_user'] &&
    (int)$_SESSION['user_role'] !== USER_ROLE_ADMIN)
{
    exit();
}



require_once("./libraries/languagelib.inc.php");
require_once(getLanguageFile("ticket_edit"));

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
     "          <div class=\"mainbox_body\">\n";

$title = $ticket['title'];
$description = $ticket['description'];
$creatorName = $ticket['creator_name'];
$creatorEMail = $ticket['creator_e_mail'];
$creatorPhone = $ticket['creator_phone'];
$status = (int)$ticket['status'];

if (isset($_POST['title']) === true &&
    isset($_POST['description']) === true &&
    isset($_POST['creatorname']) === true &&
    isset($_POST['creatoremail']) === true &&
    isset($_POST['creatorphone']) === true &&
    isset($_POST['status']) === true)
{
    if (UpdateTicket($id, $_POST['title'], $_POST['description'], $_POST['creatorname'], $_POST['creatoremail'], $_POST['creatorphone'], (int)$_POST['status']) === 0)
    {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $creatorName = $_POST['creatorname'];
        $creatorEMail = $_POST['creatoremail'];
        $creatorPhone = $_POST['creatorphone'];
        $status = (int)$_POST['status'];
    }
    else
    {
        /** @todo Notify about error. */
    }
}

echo "            <form action=\"ticket_edit.php\" method=\"post\">\n".
     "              <fieldset>\n".
     "                <input name=\"title\" type=\"text\" size=\"40\" maxlength=\"60\" value=\"".htmlspecialchars($title, ENT_COMPAT | ENT_HTML401, "UTF-8")."\"/> ".LANG_TICKETTITLECAPTION."<br/>\n".
     "                <textarea name=\"description\" cols=\"80\" rows=\"12\">".htmlspecialchars($description, ENT_COMPAT | ENT_HTML401, "UTF-8")."</textarea> ".LANG_TICKETDESCRIPTIONCAPTION."<br/>\n".
     "                <input type=\"text\" name=\"creatorname\" size=\"40\" maxlength=\"255\" value=\"".htmlspecialchars($creatorName, ENT_COMPAT | ENT_HTML401, "UTF-8")."\"> ".LANG_CREATORNAMECAPTION."<br/>\n".
     "                <input type=\"text\" name=\"creatoremail\" size=\"40\" maxlength=\"255\" value=\"".htmlspecialchars($creatorEMail, ENT_COMPAT | ENT_HTML401, "UTF-8")."\"/> ".LANG_CREATOREMAILCAPTION."<br/>\n".
     "                <input type=\"text\" name=\"creatorphone\" size=\"40\" maxlength=\"255\" value=\"".htmlspecialchars($creatorPhone, ENT_COMPAT | ENT_HTML401, "UTF-8")."\"/> ".LANG_CREATORPHONECAPTION."<br/>\n".
     "                <select name=\"status\" size=\"1\">\n";

if ($status === TICKET_STATUS_NOT_PUBLIC)
{
    echo "                  <option value=\"".TICKET_STATUS_NOT_PUBLIC."\" selected=\"selected\">".LANG_TICKETSTATUSNOTPUBLIC."</option>\n";
}
else
{
    echo "                  <option value=\"".TICKET_STATUS_NOT_PUBLIC."\">".LANG_TICKETSTATUSNOTPUBLIC."</option>\n";
}

if ($status === TICKET_STATUS_PUBLIC)
{
    echo "                  <option value=\"".TICKET_STATUS_PUBLIC."\" selected=\"selected\">".LANG_TICKETSTATUSPUBLIC."</option>\n";
}
else
{
    echo "                  <option value=\"".TICKET_STATUS_PUBLIC."\">".LANG_TICKETSTATUSPUBLIC."</option>\n";
}

echo "                </select>\n".
     "                <input type=\"hidden\" name=\"id\" value=\"".$id."\"/>\n".
     "                <input type=\"submit\" value=\"".LANG_TICKETSAVEBUTTON."\"/>\n".
     "              </fieldset>\n".
     "            </form>\n";

if (isset($_POST['trash_images']) === true)
{
    if (is_array($_POST['trash_images']) === true)
    {
        TrashUploads($_POST['trash_images']);

        $trashCount = count($ticket['images']);

        for ($i = 0; $i < $trashCount; $i++)
        {
            if (in_array($ticket['images'][$i]['internal_name'], $_POST['trash_images']) === true)
            {
                unset($ticket['images'][$i]);
            }
        }
    }
}

if (is_array($ticket['images']) === true)
{
    $imagesHTML = null;

    foreach ($ticket['images'] as $image)
    {
        if ((int)$image['status'] == TICKET_UPLOAD_STATUS_TRASHED)
        {
            continue;
        }

        if ($imagesHTML == null)
        {
            $imagesHTML = "              <div>\n".
                          "                <span class=\"ticket_info_label\">".LANG_TICKET_UPLOADED_IMAGES."</span>\n".
                          "                <form action=\"ticket_edit.php\" method=\"post\">\n".
                          "                  <fieldset>\n";
        }

        $imagesHTML .= "                    <div>\n".
                       "                      <input type=\"checkbox\" name=\"trash_images[]\" value=\"".htmlspecialchars($image['internal_name'], ENT_COMPAT | ENT_HTML401, "UTF-8")."\"/>\n".
                       "                      <a href=\"./uploads/images/".htmlspecialchars($image['internal_name'], ENT_COMPAT | ENT_HTML401, "UTF-8")."\" target=\"_blank\">\n".
                       "                        <img class=\"image_preview\" src=\"./uploads/images/".htmlspecialchars($image['internal_name'], ENT_COMPAT | ENT_HTML401, "UTF-8")."\"/>\n".
                       "                      </a>\n".
                       "                      <br/>\n".
                       "                      <span>\n".
                       "                        ".htmlspecialchars($image['display_name'], ENT_COMPAT | ENT_HTML401, "UTF-8")."\n".
                       "                      </span>\n".
                       "                    </div>\n";
    }

    if ($imagesHTML != null)
    {
        $imagesHTML .= "                    <input type=\"submit\" value=\"".LANG_BUTTON_TRASH_SELECTED_IMAGES."\"/>\n".
                       "                    <input type=\"hidden\" name=\"id\" value=\"".$id."\"/>\n".
                       "                  </fieldset>\n".
                       "                </form>\n".
                       "              </div>\n";
    }

    if ($imagesHTML != null)
    {
        echo $imagesHTML;
    }
}

echo "              <form action=\"ticket_view.php\" method=\"post\">\n".
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
