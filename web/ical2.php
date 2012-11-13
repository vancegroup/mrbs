<?PHP

require_once "defaultincludes.inc";
require_once "mrbs_sql.inc";
require_once "functions_ical.inc";
#require_once 'ical_func.inc.php';

# Generates an "ical" compatible listing of bookings.
# Will show all bookings from a fortnight before now to a year hence
# either booked by a particular user or all bookings for a particular room ID
# Expects to be called as follows:
# ical.php?user=abcd
# ical.php?room=23
#
# v0.1 - fact0r 22 May 2005, 4pm


### Stage 2 - parse the request.
	if ($_GET['user']) {
	  $user = strtolower($user);
	  $user = preg_replace("/[^a-z]/","//", $_GET['user']);
	  if ($user == '') {
	    print 'Error - invalid username - use characters a-z only.';
	    exit();
	  } 
	  $calendarName = "$mrbs_company $user";
	  $calendarDesc = "Bookings from the " . get_vocab("mrbs") . " owned by $user.";
	  $sql_where = "E.create_by = '$user'";
	}elseif ($_GET['room']) {
	  $room = preg_replace("/[^0-9]/","//", $_GET['room']);
	  if ($room == '') {
	    print 'Error - invalid room id - use characters 0-9 only.';
	    exit();
	  } 
	  $sql_where = "E.room_id = $room";
	  require_once 'fact0rfunc.inc.php';
	  $rooms = rooms_array();
	  $roomName = $rooms[$room]['roomName'];
	  $calendarName = "$roomName - $mrbs_company";
	  $calendarDesc = "Bookings for resource: $roomName.";
	  
	}else {
	  print 'Error - called without valid arguments. Try ical.php?user=username or ical.php?room=1 (roomid)';
	  exit();
	}

### Stage 3 - assemble and execute sql request.
	$start = time() - 14*24*60*60;  #Earliest event to return is a fortnight before now.
	$end = time() + 365*24*60*60;   #Last event to return is a year from now;

        $sql = "SELECT E.*, "
				 .  sql_syntax_timestamp_to_unix("E.timestamp") . " AS last_updated, "
				 . "A.area_name, R.room_name "
				 . "FROM $tbl_area A, $tbl_room R, $tbl_entry E "
                 . "WHERE $sql_where "
                 . "AND E.start_time<=$end AND E.end_time>$start"
                 . " ORDER BY E.ical_recur_id";
        $res = sql_query($sql);

### Stage 4 - output an iCal calendar if we have a db result.
  # note all times are in GMT - let the client worry about conversion into a local time zone.
        if (! $res) echo sql_error();
        else {
	  #Rightio - lets output an iCal calendar:
      header("Content-Type: application/ics;  charset=" . get_charset(). "; name=\"" . $mail_settings['ics_filename'] . ".ics\"");
      header("Content-Disposition: attachment; filename=\"" . $mail_settings['ics_filename'] . ".ics\"");
      $extras = array();
      $extras[] = "X-WR-CALNAME:$calendarName";
      $extras[] = "X-WR-CALDESC:$calendarDesc";
	  export_icalendar($res, $keep_private, $end, $extras);
      exit;
	}
?>
