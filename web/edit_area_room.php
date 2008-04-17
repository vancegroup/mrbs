<?php
// $Id$

require_once "grab_globals.inc.php";
include "config.inc.php";
include "functions.inc";
include "$dbsys.inc";
include "mrbs_auth.inc";

// Get form variables
$day = get_form_var('day', 'int');
$month = get_form_var('month', 'int');
$year = get_form_var('year', 'int');
$area = get_form_var('area', 'int');
$room = get_form_var('room', 'int');
$room_name = get_form_var('room_name', 'string');
$area_name = get_form_var('area_name', 'string');
$description = get_form_var('description', 'string');
$capacity = get_form_var('capacity', 'int');
$room_admin_email = get_form_var('room_admin_email', 'string');
$area_admin_email = get_form_var('area_admin_email', 'string');
$change_done = get_form_var('change_done', 'string');
$change_room = get_form_var('change_room', 'string');
$change_area = get_form_var('change_area', 'string');

// If we dont know the right date then make it up
if (!isset($day) or !isset($month) or !isset($year))
{
  $day   = date("d");
  $month = date("m");
  $year  = date("Y");
}

if (!getAuthorised(2))
{
  showAccessDenied($day, $month, $year, $area);
  exit();
}

// Done changing area or room information?
if (isset($change_done))
{
  if (!empty($room)) // Get the area the room is in
  {
    $area = sql_query1("SELECT area_id from $tbl_room where id=$room");
  }
  Header("Location: admin.php?day=$day&month=$month&year=$year&area=$area");
  exit();
}

print_header($day, $month, $year, isset($area) ? $area : "");

?>

<h2><?php echo get_vocab("editroomarea") ?></h2>

<?php
if (!empty($room))
{
  include_once 'Mail/RFC822.php';
  (!isset($room_admin_email)) ? $room_admin_email = '': '';
  $emails = explode(',', $room_admin_email);
  $valid_email = TRUE;
  $email_validator = new Mail_RFC822();
  foreach ($emails as $email)
  {
    // if no email address is entered, this is OK, even if isValidInetAddress
    // does not return TRUE
    if ( !$email_validator->isValidInetAddress($email, $strict = FALSE)
         && ('' != $room_admin_email) )
    {
      $valid_email = FALSE;
    }
  }
  //
  if ( isset($change_room) && (FALSE != $valid_email) )
  {
    if (empty($capacity))
    {
      $capacity = 0;
    }
    $sql = "UPDATE $tbl_room SET room_name='" . slashes($room_name)
      . "', description='" . slashes($description)
      . "', capacity=$capacity, room_admin_email='"
      . slashes($room_admin_email) . "' WHERE id=$room";
    if (sql_command($sql) < 0)
    {
      fatal_error(0, get_vocab("update_room_failed") . sql_error());
    }
  }

  $res = sql_query("SELECT * FROM $tbl_room WHERE id=$room");
  if (! $res)
  {
    fatal_error(0, get_vocab("error_room") . $room . get_vocab("not_found"));
  }
  $row = sql_row_keyed($res, 0);
  sql_free($res);
?>
<h3 style="text-align:center;"><?php echo get_vocab("editroom") ?></h3>

<form action="edit_area_room.php" method="post">
  <input type=hidden name="room" value="<?php echo $row["id"]?>">
  <div align="center">
    <table>
      <tr>
        <td><?php echo get_vocab("name") ?>:       </td>
        <td>
          <input type="text" name="room_name" value="<?php
echo htmlspecialchars($row["room_name"]); ?>">
        </td>
      </tr>
      <tr>
        <td><?php echo get_vocab("description") ?>:</td>
        <td>
          <input type="text" name="description" value="<?php
echo htmlspecialchars($row["description"]); ?>">
        </td>
      </tr>
      <tr>
        <td><?php echo get_vocab("capacity") ?>:   </td>
        <td>
          <input type="text" name="capacity" value="<?php
echo $row["capacity"]; ?>">
        </td>
      </tr>
      <tr>
        <td><?php echo get_vocab("room_admin_email") ?>:</td>
        <td>
          <input type="text" name="room_admin_email" maxlength="75" value="<?php
echo htmlspecialchars($row["room_admin_email"]); ?>">
        </td>
<?php

  if (FALSE == $valid_email)
  {
    echo ("<td>&nbsp;</td><td><strong>" . get_vocab('invalid_email') . "</strong></td>");
  }
?>
      </tr>
    </table>
    <input type=submit name="change_room" value="<?php echo get_vocab("change") ?>">
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <input type=submit name="change_done" value="<?php echo get_vocab("backadmin") ?>">
  </div>
</form>
<?php
}
?>

<?php
if (!empty($area))
{
  include_once 'Mail/RFC822.php';
  (!isset($area_admin_email)) ? $area_admin_email = '': '';
  $emails = explode(',', $area_admin_email);
  $valid_email = TRUE;
  $email_validator = new Mail_RFC822();
  foreach ($emails as $email)
  {
    // if no email address is entered, this is OK, even if isValidInetAddress
    // does not return TRUE
    if ( !$email_validator->isValidInetAddress($email, $strict = FALSE)
         && ('' != $area_admin_email) )
    {
      $valid_email = FALSE;
    }
  }
  //
  if ( isset($change_area) && (FALSE != $valid_email) )
  {
    $sql = "UPDATE $tbl_area SET area_name='" . slashes($area_name)
      . "', area_admin_email='" . slashes($area_admin_email)
      . "' WHERE id=$area";
    if (sql_command($sql) < 0)
    {
      fatal_error(0, get_vocab("update_area_failed") . sql_error());
    }
  }

  $res = sql_query("SELECT * FROM $tbl_area WHERE id=$area");
  if (! $res)
  {
    fatal_error(0, get_vocab("error_area") . $area . get_vocab("not_found"));
  }
  $row = sql_row_keyed($res, 0);
  sql_free($res);
?>
<h3 style="text-align:center;"><?php echo get_vocab("editarea") ?></h3>

<form action="edit_area_room.php" method="post">
  <input type="hidden" name="area" value="<?php echo $row["id"]?>">
  <div align="center">
    <table>
      <tr>
        <td><?php echo get_vocab("name") ?>:</td>
        <td>
          <input type="text" name="area_name" value="<?php
echo htmlspecialchars($row["area_name"]); ?>">
        </td>
      </tr>
      <tr>
        <td><?php echo get_vocab("area_admin_email") ?>:</td>
        <td>
          <input type="text" name="area_admin_email" maxlength="75" value="<?php
echo htmlspecialchars($row["area_admin_email"]); ?>">
        </td>
<?php if (FALSE == $valid_email) {
    echo ("<td>&nbsp;</td><td><strong>" . get_vocab('invalid_email') . "</strong></td>");
} ?>
      </tr>
    </table>
    <input type="submit" name="change_area" value="<?php echo get_vocab("change") ?>">
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <input type="submit" name="change_done" value="<?php echo get_vocab("backadmin") ?>">
  </div>
</form>
<?php
}
?>
<?php include "trailer.inc" ?>
