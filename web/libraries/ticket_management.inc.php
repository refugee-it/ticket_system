<?php
/* Copyright (C) 2012-2016  Stephan Kreutzer
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
 * @file $/web/libraries/ticket_management.inc.php
 * @author Stephan Kreutzer
 * @since 2016-07-25
 */



require_once(dirname(__FILE__)."/database.inc.php");



define("TICKET_STATUS_NOT_PUBLIC", 1);
define("TICKET_STATUS_PUBLIC", 2);
define("TICKET_STATUS_TRASHED", 3);

define("TICKET_UPLOAD_STATUS_NOT_PUBLIC", 1);
define("TICKET_UPLOAD_STATUS_PUBLIC", 2);
define("TICKET_UPLOAD_STATUS_TRASHED", 3);



function AddNewTicket($title,
                      $description,
                      $creatorName,
                      $creatorEMail,
                      $creatorPhone,
                      $notificationText)
{
    /** @todo Check for empty parameters. Check, if $title exists already in the database. */

    if (Database::Get()->IsConnected() !== true)
    {
        return -1;
    }

    if (Database::Get()->BeginTransaction() !== true)
    {
        return -2;
    }

    $handle = md5(uniqid(rand(), true));
    $userId = 1;

    $id = Database::Get()->Insert("INSERT INTO `".Database::Get()->GetPrefix()."tickets` (`id`,\n".
                                  "    `handle`,\n".
                                  "    `title`,\n".
                                  "    `description`,\n".
                                  "    `creator_name`,\n".
                                  "    `creator_e_mail`,\n".
                                  "    `creator_phone`,\n".
                                  "    `status`,\n".
                                  "    `datetime_created`,\n".
                                  "    `id_user`)\n".
                                  "VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)\n",
                                  array(NULL, $handle, $title, $description, $creatorName, $creatorEMail, $creatorPhone, TICKET_STATUS_NOT_PUBLIC, $userId),
                                  array(Database::TYPE_NULL, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_INT, Database::TYPE_INT));

    if ($id <= 0)
    {
        Database::Get()->RollbackTransaction();
        return -3;
    }

    if (Database::Get()->CommitTransaction() === true)
    {
        $user = Database::Get()->Query("SELECT `e_mail`\n".
                                       "FROM `".Database::Get()->GetPrefix()."users`\n".
                                       "WHERE `id`=?\n",
                                       array($userId),
                                       array(Database::TYPE_INT));   

        if (is_array($user) === true)
        {
            $message = "Time: ".date("c")."\n";
            $message .= "Type: Ticket System\n";
            $message .= "E-Mail: noreply@example.org\n";
            $message .= "Message: ".htmlspecialchars($notificationText, ENT_COMPAT | ENT_HTML401, "UTF-8")."\n";

            @mail($user['e_mail'],
                  "Ticket System",
                  $message,
                  "From: noreply@example.org\n".
                  "MIME-Version: 1.0\n".
                  "Content-type: text/plain; charset=UTF-8\n");
        }

        return array("id" => $id, "handle" => $handle);
    }

    Database::Get()->RollbackTransaction();
    return -4;
}

function UpdateTicket($id, $title, $description, $creatorName, $creatorEMail, $creatorPhone, $status)
{
    /** @todo Check for empty parameters. */

    if (Database::Get()->IsConnected() !== true)
    {
        return -1;
    }

    if ($status !== TICKET_STATUS_NOT_PUBLIC &&
        $status !== TICKET_STATUS_PUBLIC &&
        $status !== TICKET_STATUS_TRASHED)
    {
        return -2;
    }

    $result =  Database::Get()->Execute("UPDATE `".Database::Get()->GetPrefix()."tickets`\n".
                                        "SET `title`=?,\n".
                                        "    `description`=?,\n".
                                        "    `creator_name`=?,\n".
                                        "    `creator_e_mail`=?,\n".
                                        "    `creator_phone`=?,\n".
                                        "    `status`=?\n".
                                        "WHERE `id`=?\n",
                                        array($title, $description, $creatorName, $creatorEMail, $creatorPhone, $status, $id),
                                        array(Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_INT, Database::TYPE_INT));

    if ($result === true)
    {
        return 0;
    }
    else
    {
        return -1;
    }
}

function RemoveTicketHandle($ticketHandle)
{
    /** @todo Check for empty parameters. */

    if (Database::Get()->IsConnected() !== true)
    {
        return -1;
    }

    $result = Database::Get()->Execute("UPDATE `".Database::Get()->GetPrefix()."tickets`\n".
                                       "SET `handle`=?\n".
                                       "WHERE `handle` LIKE ?",
                                       array(NULL, $ticketHandle),
                                       array(Database::TYPE_NULL, Database::TYPE_STRING));

    if ($result === true)
    {
        return 0;
    }
    else
    {
        return -1;
    }
}

function AttachUpload($ticketId, $displayName, $internalName)
{
    /** @todo Check for empty parameters. */

    if (Database::Get()->IsConnected() !== true)
    {
        return -1;
    }

    if (Database::Get()->BeginTransaction() !== true)
    {
        return -2;
    }

    $id = Database::Get()->Insert("INSERT INTO `".Database::Get()->GetPrefix()."uploaded_images` (`id`,\n".
                                  "    `display_name`,\n".
                                  "    `internal_name`,\n".
                                  "    `status`,\n".
                                  "    `ticket_id`)\n".
                                  "VALUES (?, ?, ?, ?, ?)\n",
                                  array(NULL, $displayName, $internalName, TICKET_UPLOAD_STATUS_NOT_PUBLIC, $ticketId),
                                  array(Database::TYPE_NULL, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_INT, Database::TYPE_INT));

    if ($id <= 0)
    {
        Database::Get()->RollbackTransaction();
        return -3;
    }

    if (Database::Get()->CommitTransaction() === true)
    {
        return array("id" => $id);
    }

    Database::Get()->RollbackTransaction();
    return -4;
}

function TrashUploads(array $handleUploads)
{
    /** @todo Check for empty parameters. */

    if (Database::Get()->IsConnected() !== true)
    {
        return -1;
    }

    $whereClause = "";
    $arguments = array(TICKET_UPLOAD_STATUS_TRASHED);
    $types = array(Database::TYPE_INT);

    foreach ($handleUploads as $handleUpload)
    {
        if (empty($whereClause) == true)
        {
            $whereClause = "WHERE `internal_name` LIKE ?";
        }
        else
        {
            $whereClause .= " OR `internal_name` LIKE ?";
        }

        $arguments[] = $handleUpload;
        $types[] = Database::TYPE_STRING;
    }

    $result = Database::Get()->Execute("UPDATE `".Database::Get()->GetPrefix()."uploaded_images`\n".
                                       "SET `status`=?\n".
                                       $whereClause,
                                       $arguments,
                                       $types);

    if ($result === true)
    {
        return 0;
    }
    else
    {
        return -1;
    }
}

function GetUploads($ticketId)
{
    /** @todo Check for empty parameters. */

    if (Database::Get()->IsConnected() !== true)
    {
        return -1;
    }

    $uploads = Database::Get()->Query("SELECT `id`,\n".
                                      "    `display_name`,\n".
                                      "    `internal_name`,\n".
                                      "    `status`\n".
                                      "FROM `".Database::Get()->GetPrefix()."uploaded_images`\n".
                                      "WHERE `ticket_id`=?\n",
                                      array($handleTicket),
                                      array(Database::TYPE_INT));

    if (is_array($uploads) !== true)
    {
        return null;
    }

    return $uploads;
}

function GetTickets($userId)
{
    if (Database::Get()->IsConnected() !== true)
    {
        return -1;
    }

    $whereClause = "";
    $arguments = array();
    $types = array();

    if ($userId !== null)
    {
        $whereClause = "WHERE `id_user`=?\n";
        $arguments[] = $userId;
        $types[] = Database::TYPE_INT;
    }
    else
    {
        $whereClause = "WHERE `status`=?\n";
        $arguments[] = TICKET_STATUS_PUBLIC;
        $types[] = Database::TYPE_INT;
    }

    $tickets = Database::Get()->Query("SELECT `id`,\n".
                                      "    `handle`,\n".
                                      "    `title`,\n".
                                      "    `description`,\n".
                                      "    `status`\n".
                                      "FROM `".Database::Get()->GetPrefix()."tickets`\n".
                                      $whereClause,
                                      $arguments,
                                      $types);

    if (is_array($tickets) !== true)
    {
        return null;
    }

    return $tickets;
}

function GetTicketById($id)
{
    /** @todo Check for empty parameters. */

    if (Database::Get()->IsConnected() !== true)
    {
        return -1;
    }

    $ticket = Database::Get()->Query("SELECT `title`,\n".
                                     "    `description`,\n".
                                     "    `creator_name`,\n".
                                     "    `creator_e_mail`,\n".
                                     "    `creator_phone`,\n".
                                     "    `status`,\n".
                                     "    `datetime_created`,\n".
                                     "    `handle`,\n".
                                     "    `id_user`\n".
                                     "FROM `".Database::Get()->GetPrefix()."tickets`\n".
                                     "WHERE `id`=?\n",
                                     array($id),
                                     array(Database::TYPE_INT));

    if (is_array($ticket) !== true)
    {
        return null;
    }

    if (count($ticket) <= 0)
    {
        return null;
    }

    $ticket = $ticket[0];

    $images = Database::Get()->Query("SELECT `display_name`,\n".
                                     "    `internal_name`,\n".
                                     "    `status`\n".
                                     "FROM `".Database::Get()->GetPrefix()."uploaded_images`\n".
                                     "WHERE `ticket_id`=?\n",
                                     array($id),
                                     array(Database::TYPE_INT));

    if (is_array($images) === true)
    {
        if (count($images) > 0)
        {
            $ticket['images'] = $images;
        }
        else
        {
            $ticket['images'] = null;
        }
    }
    else
    {
        $ticket['images'] = null;
    }

    return $ticket;
}

function GetTicketByHandle($ticketHandle)
{
    /** @todo Check for empty parameters. */

    if (Database::Get()->IsConnected() !== true)
    {
        return -1;
    }

    $ticket = Database::Get()->Query("SELECT `id`,\n".
                                     "    `title`,\n".
                                     "    `description`,\n".
                                     "    `creator_name`,\n".
                                     "    `creator_e_mail`,\n".
                                     "    `creator_phone`,\n".
                                     "    `status`,\n".
                                     "    `datetime_created`,\n".
                                     "    `id_user`\n".
                                     "FROM `".Database::Get()->GetPrefix()."tickets`\n".
                                     "WHERE `handle` LIKE ?\n",
                                     array($ticketHandle),
                                     array(Database::TYPE_STRING));

    if (is_array($ticket) !== true)
    {
        return null;
    }

    if (count($ticket) <= 0)
    {
        return null;
    }

    $ticket = $ticket[0];

    $images = Database::Get()->Query("SELECT `display_name`,\n".
                                     "    `internal_name`,\n".
                                     "    `status`\n".
                                     "FROM `".Database::Get()->GetPrefix()."uploaded_images`\n".
                                     "WHERE `ticket_id`=?\n",
                                     array($ticket['id']),
                                     array(Database::TYPE_INT));

    if (is_array($images) === true)
    {
        if (count($images) > 0)
        {
            $ticket['images'] = $images;
        }
        else
        {
            $ticket['images'] = null;
        }
    }
    else
    {
        $ticket['images'] = null;
    }

    return $ticket;
}




?>
