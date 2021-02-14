<?php
global $gBitSystem, $gBitSmarty, $gBitThemes;

$registerHash = array(
	'package_name' => 'stars',
	'package_path' => dirname( __FILE__ ).'/',
	'service' => LIBERTY_SERVICE_RATING,
);
$gBitSystem->registerPackage( $registerHash );

if( $gBitSystem->isPackageActive( 'stars' ) ) {
	require_once( STARS_PKG_CLASS_PATH.'LibertyStars.php' );

	// if we are using a text browser theme, make sure not to use ajax
	if( $gBitThemes->getStyle()  == 'lynx' ) {
		$gBitSystem->setConfig( 'recommends_use_ajax', FALSE );
	}

	// unfortunately we have situations where we can't load stars initiation 
	// from the sql service functions due to the way the center modules work.
	stars_template_setup();

	$gLibertySystem->registerService( LIBERTY_SERVICE_RATING, STARS_PKG_NAME, array(
//		'content_display_function'  => 'stars_content_display',
		'content_load_sql_function' => 'stars_content_load_sql',
		'content_list_sql_function' => 'stars_content_list_sql',
		'content_expunge_function'  => 'stars_content_expunge',
		'content_body_tpl'          => 'bitpackage:stars/stars_inline_service.tpl',
		'content_comment_tpl'       => 'bitpackage:stars/stars_inline_service.tpl',
		'content_list_sort_tpl'     => 'bitpackage:stars/stars_list_sort_service.tpl',
		'content_list_actions_tpl'  => 'bitpackage:stars/stars_list_actions_service.tpl',
		'users_expunge_function'	=> 'contests_user_expunge',
	));

	// make sure all stars votes are removed
	function stars_user_expunge( &$pObject ) {
		if( is_a( $pObject, 'BitUser' ) && !empty( $pObject->mUserId ) ) {
			$pObject->mDb->StartTrans();
			$pObject->mDb->query( "DELETE FROM `".BIT_DB_PREFIX."stars_history` WHERE user_id=?", array( $pObject->mUserId ) );
			$pObject->mDb->CompleteTrans();
		}
	}
	
}
?>
