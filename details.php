<?php
/**
* $Header: /cvsroot/bitweaver/_bit_stars/details.php,v 1.2 2006/10/13 12:46:44 lsces Exp $
* date created 2006/02/10
* @author xing <xing@synapse.plus.com>
* @version $Revision: 1.2 $ $Date: 2006/10/13 12:46:44 $
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
$stars->getRatingDetails( !empty( $_REQUEST['show_raters'] ) );

$gBitSmarty->assign( 'starsDetails', $stars->mInfo );
$gBitSystem->display( 'bitpackage:stars/details.tpl', tra( 'Details of Rated Content' ) );
?>
