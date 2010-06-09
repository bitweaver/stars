<?php
/**
* $Header$
* date created 2006/02/10
* @author xing <xing@synapse.plus.com>
* @version $Revision$
* @package stars
*/

/**
 * Setup
 */
require_once( "../kernel/setup_inc.php" );
require_once( STARS_PKG_PATH."LibertyStars.php" );

$gBitSystem->verifyPackage( 'stars' );

if( !@BitBase::verifyId( $_REQUEST['content_id'] ) && !@BitBase::verifyId( $_REQUEST['user_id'] )) {
	header( "Location: ".BIT_ROOT_URL );
}

if( @BitBase::verifyId( $_REQUEST['content_id'] )) {
	// content details
	$stars = new LibertyStars( $_REQUEST['content_id'] );
	$stars->loadRatingDetails();
	$gBitSmarty->assign( 'starsDetails', $stars->mInfo );
} elseif( @BitBase::verifyId( $_REQUEST['user_id'] )) {
	// user details
	$stars = new LibertyStars();
	$listHash = array(
		'user_id' => $_REQUEST['user_id'],
	);
	$userRatings = $stars->getList( $listHash );

	// calculate this users average ratings
	$sum = 0;
	foreach( $userRatings as $rating ) {
		$sum += $rating['user_rating'];
	}
	$average = round( $sum / count( $userRatings ));
	$stars = $gBitSystem->getConfig( 'stars_used_in_display', 5 );
	$pixels = $stars *  $gBitSystem->getConfig( 'stars_icon_width', 22 );
	$average_pixels = $average * $pixels / 100;

	$gBitSmarty->assign( 'average_pixels', $average_pixels );
	$gBitSmarty->assign( 'userRatings', $userRatings );
}

$gBitSystem->display( 'bitpackage:stars/details.tpl', tra( 'Details of Rated Content' ) , array( 'display_mode' => 'display' ));
?>
