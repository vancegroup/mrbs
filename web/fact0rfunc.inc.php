<?PHP

function rooms_array() {
	global $tbl_room;
	global $tbl_area;

	$rooms = array();
        $sql = "SELECT $tbl_room.id, $tbl_room.room_name, $tbl_room.area_id, $tbl_area.area_name
		FROM $tbl_room left join $tbl_area on $tbl_room.area_id = $tbl_area.id
                ORDER BY area_name, room_name";

        $res = sql_query($sql);

        if (! $res) echo sql_error();
        else {
          for ($i = 0; ($row = sql_row($res, $i)); $i++) {
	    $rooms[$row[0]] = array( 'roomName'=>$row[1], 'roomID'=>$row[0], 
		'areaName'=>$row[3], 'areaID'=>$row[2]);
	  }
	}
	return $rooms;
}


?>
