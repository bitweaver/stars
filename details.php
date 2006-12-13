<?php
/**
* $Header: /cvsroot/bitweaver/_bit_stars/details.php,v 1.4 2006/12/13 18:15:07 squareing Exp $
* date created 2006/02/10
* @author xing <xing@synapse.plus.com>
* @version $Revision: 1.4 $ $Date: 2006/12/13 18:15:07 $
* @package stars
*/

/**
 * Setup
 */
require_once( "../bit_setup_inc.php" );
require_once( STARS_PKG_PATH."LibertyStars.php" );

$gBitSystem->verifyPackage( 'stars' );

if( !@BitBase::verifyId( $_REQUEST['content_id'] ) ) {
	header( "Location: ".BIT_ROOT_URL );
}

$stars = new LibertyStars( $_REQUEST['content_id'] );
$stars->loadRatingDetails();

$gBitSmarty->assign( 'starsDetails', $stars->mInfo );
$gBitSystem->display( 'bitpackage:stars/details.tpl', tra( 'Details of Rated Content' ) );
?>
