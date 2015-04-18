<?php


require_once('../api_common_private.php');

//$rawdata = get_array('playlists');

$rawdata = array();

global $_GET;
global $db;

if(isset($_GET['OFFSET'])) $offset = $_GET['OFFSET']; else $offset = 0;
if(isset($_GET['LIMIT'])) $limit = $_GET['LIMIT']; else $limit = 100;



$query = '
    SELECT *,
    playlists.id as ps_id,
    podcast_episodes.id as ep_id
    FROM playlists
    LEFT JOIN podcast_episodes on playlists.podcast_episode = podcast_episodes.id
    WHERE playlists.show_id = '.users_show().'
    ORDER BY
      playlists.start_time
    DESC limit ' . $limit . ' OFFSET ' . $offset;


if ($result = mysqli_query($db, $query) ) {

  while ($row = mysqli_fetch_assoc($result)) {

    $rawdata [] = $row;

  }
} else {
  $error .= mysqli_error($db);
}




$data = $rawdata;

finish();