<?PHP

require_once "defaultincludes.inc";
require_once "mrbs_sql.inc";

require_once 'ical_func.inc.php';
require_once 'fact0rfunc.inc.php';

# Generates an "ical" compatible listing of bookings.
# Will show all bookings from a fortnight before now to a year hence
# either booked by a particular user or all bookings for a particular room ID
# Expects to be called as follows:
# ical.php?user=abcd
# ical.php?room=23
#
# v0.1 - fact0r 22 May 2005, 4pm

### Stage 1 - grab an array of room ids -> room names
	$rooms = rooms_array();

### Stage 2 - parse the request.
	if ($_GET['user']) {
	  $user = strtolower($user);
	  $user = preg_replace("/[^a-z]/","//", $_GET['user']);
	  if ($user == '') {
	    print 'Error - invalid username - use characters a-z only.';
	    exit();
	  } 
	  $calendarName = "VRAC $user";
	  $calendarDesc = "Bookings from the Resource Booking System owned by $user.";
	  $sql_where = "create_by = '$user'";
	}elseif ($_GET['room']) {
	  $room = preg_replace("/[^0-9]/","//", $_GET['room']);
	  if ($room == '') {
	    print 'Error - invalid room id - use characters 0-9 only.';
	    exit();
	  } 
	  $roomName = $rooms[$room]['roomName'];
	  $calendarName = "VRAC $roomName";
	  $calendarDesc = "Bookings from the Resource Booking System for resource: $roomName.";
	  $sql_where = "room_id = $room";
	}else {
	  print 'Error - called without valid arguments. Try ical.php?user=username or ical.php?room=1 (roomid)';
	  exit();
	}

### Stage 3 - assemble and execute sql request.
	$start = time() - 14*24*60*60;  #Earliest event to return is a fortnight before now.
	$end = time() + 365*24*60*60;   #Last event to return is a year from now;

        $sql = "SELECT start_time, end_time, type, name, id, description, create_by, room_id, timestamp
                FROM $tbl_entry
                WHERE $sql_where
                AND start_time <= $end AND end_time > $start ORDER BY start_time";
        $res = sql_query($sql);

### Stage 4 - output an iCal calendar if we have a db result.
  # note all times are in GMT - let the client worry about conversion into a local time zone.
        if (! $res) echo sql_error();
        else {
	  #Rightio - lets output an iCal calendar:
	  header('Content-type: text/calendar');
          print ical_header($calendarName,$calendarDesc);
	  for ($i = 0; ($row = sql_row($res, $i)); $i++)
          {
	    if ($row[1] > $row[0]) { //if end_time > start_time; just a sanity check.
	      $description = 'Booked by: ' . $row[6] . "\n" . $row[5];
	      $roomName = $rooms[$row[7]]['areaName'] . ' - ' . $rooms[$row[7]]['roomName'];
	      // ical_event($start,$end,$last_modified,$summary,$description,$room_name,$id,$booker)
	      print ical_event( $row[0], $row[1], $row[8], $row[3], $description, $roomName, 
			$row[4], $row[6]);
	    } 
	  } 
	  print ical_footer();
	}
?>
