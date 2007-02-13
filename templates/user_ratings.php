<?php
$stars = new LibertyStars();
$listHash = array(
	'user_id' => $gQueryUserId,
);
$userRatings = $stars->getList( $listHash );

// calculate this users average rating
$sum = 0;
foreach( $userRatings as $rating ) {
	$sum += $rating['user_rating'];
}
$average = round( $sum / count( $userRatings ));
$stars = $gBitSystem->getConfig( 'stars_used_in_display', 5 );
$pixels = $stars *  $gBitSystem->getConfig( 'stars_icon_width', 22 );
$average_pixels = $average * $pixels / 100;
$gBitSmarty->assign( 'average_pixels', $average_pixels );
?>
