<?php
/**
* $Header: /cvsroot/bitweaver/_bit_stars/LibertyStars.php,v 1.12 2007/07/01 23:43:54 spiderr Exp $
* date created 2006/02/10
* @author xing <xing@synapse.plus.com>
* @version $Revision: 1.12 $ $Date: 2007/07/01 23:43:54 $
* @package stars
*/

/**
 * Setup
 */
require_once( KERNEL_PKG_PATH.'BitBase.php' );

/**
 * Liberty Stars
 * 
 * @package stars
 */
class LibertyStars extends LibertyBase {
	var $mContentId;

	/**
	 * Initiate Liberty Stars
	 * 
	 * @param array $pContentId Content id of the item being rated
	 * @access public
	 * @return void
	 */
	function LibertyStars( $pContentId=NULL ) {
		LibertyBase::LibertyBase();
		$this->mContentId = $pContentId;
	}

	/**
	 * Load the data from the database
	 * 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function load() {
		if( $this->isValid() ) {
			global $gBitSystem;
			$stars = $gBitSystem->getConfig( 'stars_used_in_display', 5 );
			$pixels = $stars *  $gBitSystem->getConfig( 'stars_icon_width', 22 );
			$query = "
				SELECT ( `rating` * $pixels / 100 ) AS `stars_pixels`, `rating` AS `stars_rating`, `update_count` AS `stars_update_count`, `content_id`
				FROM `".BIT_DB_PREFIX."stars`
				WHERE `content_id`=?";
			$this->mInfo = $this->mDb->getRow( $query, array( $this->mContentId ) );
		}
		return( count( $this->mInfo ) );
	}

	/**
	 * get list of all rated content
	 *
	 * @param $pListHash contains array of items used to limit search results
	 * @param $pListHash[sort_mode] column and orientation by which search results are sorted
	 * @param $pListHash[find] search for a pigeonhole title - case insensitive
	 * @param $pListHash[max_records] maximum number of rows to return
	 * @param $pListHash[offset] number of results data is offset by
	 * @access public
	 * @return array of rated content
	 **/
	function getList( &$pListHash ) {
		global $gBitSystem, $gBitUser, $gLibertySystem;

		$ret = $bindVars = array();
		$where = $join = $select = '';

		// set custom sorting before we call prepGetList()
		if( !empty( $pListHash['sort_mode'] )) {
			$order = " ORDER BY ".$this->mDb->convertSortmode( $pListHash['sort_mode'] )." ";
		} else {
			// set a default sort_mode
			$order = " ORDER BY sts.`rating` DESC";
		}

		LibertyBase::prepGetList( $pListHash );

		if( !empty( $pListHash['user_id'] )) {
			$where      .= empty( $where ) ? ' WHERE ' : ' AND ';
			$where      .= " sth.`user_id`=? ";
			$bindVars[]  = $pListHash['user_id'];
			$select     .= ", sth.`rating` AS `user_rating`";
			$join       .= " LEFT OUTER JOIN `".BIT_DB_PREFIX."stars_history` sth ON( sts.`content_id` = sth.`content_id` ) ";
			$order       = " ORDER BY sth.`rating` DESC";
		}

		if( !empty( $pListHash['find'] )) {
			$where      .= empty( $where ) ? ' WHERE ' : ' AND ';
			$where      .= " UPPER( lc.`title` ) LIKE ? ";
			$bindVars[]  = '%'.strtoupper( $pListHash['find'] ).'%';
		}

		$query = "
			SELECT sts.*, lch.`hits`, lch.`last_hit`, lc.`event_time`, lc.`title`,
			lc.`last_modified`, lc.`content_type_guid`, lc.`ip`, lc.`created` $select
			FROM `".BIT_DB_PREFIX."stars` sts
				INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( lc.`content_id` = sts.`content_id` )
				LEFT JOIN `".BIT_DB_PREFIX."liberty_content_hits` lch ON ( lc.`content_id` = lch.`content_id` )
			$join $where $order";

		$result = $this->mDb->query( $query, $bindVars, $pListHash['max_records'], $pListHash['offset'] );

		while( $aux = $result->fetchRow() ) {
			$type = &$gLibertySystem->mContentTypes[$aux['content_type_guid']];
			if( empty( $type['content_object'] )) {
				include_once( $gBitSystem->mPackages[$type['handler_package']]['path'].$type['handler_file'] );
				$type['content_object'] = new $type['handler_class']();
			}
			if( !empty( $gBitSystem->mPackages[$type['handler_package']] )) {
				$aux['display_link'] = $type['content_object']->getDisplayLink( $aux['title'], $aux );
				$aux['title']        = $type['content_object']->getTitle( $aux );
				$aux['display_url']  = $type['content_object']->getDisplayUrl( $aux['content_id'], $aux );
			}
			$ret[] = $aux;
		}

		$query = "
			SELECT COUNT( sts.`content_id` )
			FROM `".BIT_DB_PREFIX."stars` sts
				INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( lc.`content_id` = sts.`content_id` )
				LEFT JOIN `".BIT_DB_PREFIX."liberty_content_hits` lch ON ( lc.`content_id` = lch.`content_id` )
			$join $where";
		$pListHash['cant'] = $this->mDb->getOne( $query, $bindVars );

		LibertyContent::postGetList( $pListHash );
		return $ret;
	}

	/**
	 * Get the rating history of a loaded content
	 * 
	 * @param boolean $pExtras loading the extras will get all users who have rated in the past and their ratings
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function loadRatingDetails() {
		if( $this->isValid() ) {
			global $gBitSystem;
			$stars = $gBitSystem->getConfig( 'stars_used_in_display', 5 );
			$pixels = $stars *  $gBitSystem->getConfig( 'stars_icon_width', 22 );
			$query = "
				SELECT ( `rating` * $pixels / 100 ) AS `stars_pixels`, `rating` AS `stars_rating`, `update_count` AS `stars_update_count`, `content_id`
				FROM `".BIT_DB_PREFIX."stars`
				WHERE `content_id`=?";
			$obj = $this->getLibertyObject( $this->mContentId );
			$this->mInfo = $this->mDb->getRow( $query, array( $this->mContentId ) );
			$this->mInfo = array_merge( $this->mInfo, $obj->mInfo );
			$query = "
				SELECT sth.`content_id` as `hash_key`, sth.*, uu.`login`, uu.`real_name`
				FROM `".BIT_DB_PREFIX."stars_history` sth
					INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON sth.`user_id`=uu.`user_id`
				WHERE sth.`content_id`=?
				ORDER BY sth.`rating` ASC";
			$this->mInfo['user_ratings'] = $this->mDb->getAll( $query, array( $this->mContentId ) );
		}
		return( count( $this->mInfo ) );
	}

	/**
	 * Quick method to get a nice summary of past ratings for a given content
	 * 
	 * @param array $pContentId 
	 * @access public
	 * @return usable hash with a summary of ratings of a given content id
	 */
	function getRatingSummary( $pContentId = NULL ) {
		if( !@BitBase::verifyId( $pContentId ) && $this->isValid() ) {
			$pContentId = $this->mContentId;
		}

		$ret['sum'] = $ret['weight'] = $ret['count'] = 0;
		if( @BitBase::verifyId( $pContentId ) ) {
			$query = "
				SELECT
					sth.`rating`,
					COUNT( sth.`rating`) AS `update_count`,
					SUM( sth.`weight` ) AS `weight`
				FROM `".BIT_DB_PREFIX."stars_history` sth
				WHERE sth.`content_id`=?
				GROUP BY sth.`rating`";
			$result = $this->mDb->getAll( $query, array( $pContentId ) );

			foreach( $result as $set ) {
				$ret['sum']    += $set['weight'] * $set['rating'];
				$ret['weight'] += $set['weight'];
				$ret['count']  += $set['update_count'];
			}
		}
		return $ret;
	}

	/**
	 * @param array pParams hash of values that will be used to store the page
	 *
	 * @return bool TRUE on success, FALSE if store could not occur. If FALSE, $this->mErrors will have reason why
	 * @access public
	 **/
	function store( &$pParamHash ) {
		global $gBitUser;
		if( $this->verify( $pParamHash ) ) {
			$table = BIT_DB_PREFIX."stars";
			$this->mDb->StartTrans();
			if( !empty( $this->mInfo ) ) {
				if( $this->getUserRating( $pParamHash['content_id'] ) ) {
					$result = $this->mDb->associateUpdate( $table."_history", $pParamHash['stars_history_store'], array( "content_id" => $this->mContentId, "user_id" => $gBitUser->mUserId ) );
					// we don't have a new entry in the database and the update_count stays the same
					unset( $pParamHash['stars_store']['update_count'] );
				} else {
					$result = $this->mDb->associateInsert( $table."_history", $pParamHash['stars_history_store'] );
				}
				$result = $this->mDb->associateUpdate( $table, $pParamHash['stars_store'], array( "content_id" => $this->mContentId ) );
			} else {
				$result = $this->mDb->associateInsert( $table, $pParamHash['stars_store'] );
				$result = $this->mDb->associateInsert( $table."_history", $pParamHash['stars_history_store'] );
			}
			$this->mDb->CompleteTrans();
		}
		return( count( $this->mErrors )== 0 );
	}

	/**
	 * Make sure the data is safe to store
	 *
	 * @param array pParams reference to hash of values that will be used to store the page, they will be modified where necessary
	 * @return bool TRUE on success, FALSE if verify failed. If FALSE, $this->mErrors will have reason why
	 * @access private
	 **/
	function verify( &$pParamHash ) {
		global $gBitUser, $gBitSystem;

		if( $gBitUser->isRegistered() && $this->isValid() ) {
			$this->load();
			$pParamHash['content_id'] = $this->mContentId;

			// only store stuff if user hasn't rated this content before
			if( $this->calculateRating( $pParamHash ) ) {
				// stars table
				$pParamHash['stars_store']['rating']              = ( int )$pParamHash['summary']['rating'];
				$pParamHash['stars_store']['update_count']        = ( int )$pParamHash['summary']['count'] + 1;

				// keep this entry in the history
				$pParamHash['stars_history_store']['content_id']  = $pParamHash['stars_store']['content_id'] = ( int )$this->mContentId;
				$pParamHash['stars_history_store']['rating']      = ( int )$pParamHash['rating'];
				$pParamHash['stars_history_store']['weight']      = ( int )$pParamHash['user']['weight'];
				$pParamHash['stars_history_store']['rating_time'] = ( int )BitDate::getUTCTime();
				$pParamHash['stars_history_store']['user_id']     = ( int )$gBitUser->mUserId;
			} else {
				$this->mErrors['calculate_rating'] = "There was a problem calculating the rating.";
			}
		} else {
			$this->mErrors['unregistered'] = "You have to be registered to rate content.";
		}

		return( count( $this->mErrors )== 0 );
	}

	/**
	 * Get the rating of the currently active user for the specified content
	 * 
	 * @param array $pContentId 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getUserRating( $pContentId = NULL ) {
		global $gBitSystem, $gBitUser;
		$ret = FALSE;
		if( !@BitBase::verifyId( $pContentId ) && $this->isValid() ) {
			$pContentId = $this->mContentId;
		}

		if( @BitBase::verifyId( $pContentId ) ) {
			$stars = $gBitSystem->getConfig( 'stars_used_in_display', 5 );
			$pixels = $stars *  $gBitSystem->getConfig( 'stars_icon_width', 22 );
			$query = "
				SELECT (`rating` * $pixels / 100) AS `stars_user_pixels`, ( `rating` * $stars / 100 ) AS `stars_user_rating`
				FROM `".BIT_DB_PREFIX."stars_history`
				WHERE `content_id`=? AND `user_id`=?";
			$ret = $this->mDb->getRow( $query, array( $pContentId, $gBitUser->mUserId ) );
		}
		return $ret;
	}

	/**
	 * Check if the mContentId is set and valid
	 * 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function isValid() {
		return( @BitBase::verifyId( $this->mContentId ) );
	}

	/**
	 * This function removes a stars entry
	 * 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function expunge() {
		$ret = FALSE;
		if( $this->isValid() ) {
			$query = "DELETE FROM `".BIT_DB_PREFIX."stars` WHERE `content_id` = ?";
			$result = $this->mDb->query( $query, array( $this->mContentId ) );
			$query = "DELETE FROM `".BIT_DB_PREFIX."stars_history` WHERE `content_id` = ?";
			$result = $this->mDb->query( $query, array( $this->mContentId ) );
		}
		return $ret;
	}

	// ============================ calculations ============================

	/**
	 * recalculate the rating of all objects - important when user changes weighting opions
	 * TODO: add some check to see if this was successfull, currenlty only returns true
	 * 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function reCalculateRating() {
		global $gBitSystem;

		// get all users that have rated and the content that has been rated
		$result = $this->mDb->query( "SELECT `user_id`, `content_id` FROM `".BIT_DB_PREFIX."stars_history`" );
		while( $aux = $result->fetchRow() ) {
			$userIds[]    = $aux['user_id'];
			$contentIds[] = $aux['content_id'];
		}
		$userIds    = array_unique( $userIds );
		$contentIds = array_unique( $contentIds );

		// --- Update user weighting first
		// update user weight in accordance with new settings
		foreach( $userIds as $userId ) {
			$userWeight = $this->calculateUserWeight( $userId );
			$result = $this->mDb->query( "UPDATE `".BIT_DB_PREFIX."stars_history` SET `weight`=? WHERE `user_id`=?", array( $userWeight, $userId ) );
		}

		// --- Update content rating
		// remove all entries in the aggregated list
		$result = $this->mDb->query( "DELETE FROM `".BIT_DB_PREFIX."stars`" );

		// update the calculations in the stars table
		foreach( $contentIds as $content_id ) {
			// get the rating history summary
			$summary = $this->getRatingSummary( $content_id );

			// set the aggregated rating to 0 if we aren't displaying the rating yet.
			$minRatings = $gBitSystem->getConfig( 'stars_minimum_ratings', 5 );
			if( $summary['count'] < $minRatings ) {
				$rating = 0;
			} else {
				$rating = round( $summary['sum'] / $summary['weight'] );
			}

			$storeHash = array(
				'content_id'   => $content_id,
				'rating'       => $rating,
				'update_count' => $summary['count'],
			);
			$result = $this->mDb->associateInsert( BIT_DB_PREFIX."stars", $storeHash );
		}
		return TRUE;
	}

	/**
	 * Calculate the correct value to insert into the database
	 * 
	 * @param array $pParamHash 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function calculateRating( &$pParamHash ) {
		global $gBitSystem, $gBitUser;
		$stars = $gBitSystem->getConfig( 'stars_used_in_display', 5 );
		$ret = FALSE;

		// TODO: factors that haven't been taken into accound yet:
		//       - time since last rating(s) - how should this be dealt with?
		//       - age of document - ???

		// number of ratings needed before value is displayed
		if( @BitBase::verifyId( $pParamHash['stars_rating'] ) && $pParamHash['stars_rating'] > 0 && $pParamHash['stars_rating'] <= $stars && $this->isValid() ) {
			// normalise to 100 weight
			$pParamHash['rating'] = $pParamHash['stars_rating'] / $stars * 100;

			// if the user is submitting his rating again, we need to update the value in the db before we get the summary
			if( $userRating = $this->getUserRating() ) {
				$tmpUpdate['rating'] = ( int )$pParamHash['rating'];
				$result = $this->mDb->associateUpdate( BIT_DB_PREFIX."stars_history", $tmpUpdate, array( "content_id" => $this->mContentId, "user_id" => $gBitUser->mUserId ) );
			}

			$pParamHash['user']['weight'] = $this->calculateUserWeight();

			// get the rating history summary
			$summary = $this->getRatingSummary();

			$minRatings = $gBitSystem->getConfig( 'stars_minimum_ratings', 5 );
			if( ( $summary['count'] + 1 ) < $minRatings ) {
				$pParamHash['summary']['rating'] = 0;
			} else {
				$pParamHash['summary']['rating'] = round( ( $summary['sum'] + ( $pParamHash['rating'] * $pParamHash['user']['weight'] ) ) / ( $summary['weight'] + $pParamHash['user']['weight'] ) );
			}
			$pParamHash['summary']['count'] = $summary['count'];
			$ret = TRUE;
		}
		return $ret;
	}

	/**
	 * Calculate the importance of a users rating
	 * 
	 * @param array $pUserId User id
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function calculateUserWeight( $pUserId = NULL ) {
		global $gBitUser, $gBitSystem;
		if( $gBitSystem->isFeatureActive( 'stars_user_weight' ) ) {

			// allow overriding of currently loaded user
			if( @BitBase::verifyId( $pUserId ) ) {
				$tmpUser = new BitPermUser( $pUserId );
				$tmpUser->load( TRUE );
			} else {
				$tmpUser = &$gBitUser;
			}

			// age relative to site age
			$query = "SELECT MIN( `registration_date` ) FROM `".BIT_DB_PREFIX."users_users`";
			$age['site'] = BitDate::getUTCTime() - $this->mDb->getOne( $query );
			$age['user'] = BitDate::getUTCTime() - $tmpUser->getField( 'registration_date' );
			$userWeight['age'] = $age['user'] / $age['site'];

			// permissioning relative to full number of permissions
			$query = "SELECT COUNT( `perm_name` ) FROM `".BIT_DB_PREFIX."users_permissions`";
			if( $tmpUser->isAdmin() ) {
				$userWeight['permission'] = 1;
			} else {
				$userWeight['permission'] = count( $tmpUser->mPerms ) / $this->mDb->getOne( $query );
			}

			// activity - we could to the same using the history as well.
			$query = "SELECT COUNT( `content_id` ) FROM `".BIT_DB_PREFIX."liberty_content` WHERE `user_id`=?";
			$activity['user'] = $this->mDb->getOne( $query, array( $tmpUser->getField( 'user_id' ) ) );

			$query = "SELECT COUNT( `content_id` ) FROM `".BIT_DB_PREFIX."liberty_content`";
			$activity['site'] = $this->mDb->getOne( $query );

			$userWeight['activity'] = $activity['user'] / $activity['site'];

			// here we can add some weight to various areas
			$custom['age']        = $gBitSystem->getConfig( 'stars_weight_age' );
			$custom['permission'] = $gBitSystem->getConfig( 'stars_weight_permission' );
			$custom['activity']   = $gBitSystem->getConfig( 'stars_weight_activity' );

			foreach( $userWeight as $type => $value ) {
				$$type = 10 * $value * $custom[$type];
				if( empty( $$type ) ) {
					$$type = 1;
				}
			}

			// TODO: run some tests to see if this is a good way of evaluating power of a user
			// ensure that we always have a positive number here to avoid chaos - this alse makes sure new users have at least a bit of a say
			if( ( $ret = round( log( $age * $permission * $activity, 2 ) ) ) < 1 ) {
				$ret = 1;
			}
		} else {
			$ret = 1;
		}

		return $ret;
	}
}

/********* SERVICE FUNCTIONS *********/

/**
 * Prepare and assign data to templates
 * 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function stars_template_setup() {
	global $gBitSystem, $gBitUser, $gBitSmarty;
	$stars = $gBitSystem->getConfig( 'stars_used_in_display', 5 );
	$default_names = array();
	for( $i = 0; $i < $stars; $i++ ) {
		$default_names[] = ( $i + 1 )." / ".$gBitSystem->getConfig( 'stars_used_in_display' );
	}
	$default_names_flat = implode( ",", $default_names );
	$ratingNames = explode( ",", "," . $gBitSystem->getConfig( 'stars_rating_names', $default_names_flat ));
	$gBitSmarty->assign( 'ratingNames', $ratingNames );
	$gBitSmarty->assign( 'starsLinks', $hash = array_fill( 1, $stars, 1 ));
	$gBitSmarty->assign( 'loadStars', TRUE );
}

/**
 * Content list sql service function
 * 
 * @param array $pObject 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function stars_content_list_sql( &$pObject ) {
	global $gBitSystem, $gBitUser, $gBitSmarty;
    if( method_exists( $pObject, 'getContentType' ) && $gBitSystem->isFeatureActive( 'stars_rate_'.$pObject->getContentType() ) ) {
		// in some cases, such as articles, rating is allowed when getList is called.
		// TODO: only load this when needed?
		if( $gBitSystem->isFeatureActive( 'stars_use_ajax' ) ) {
			$gBitSmarty->assign( 'loadAjax', 'prototype' );
		}
		$stars = $gBitSystem->getConfig( 'stars_used_in_display', 5 );
		$pixels = $stars *  $gBitSystem->getConfig( 'stars_icon_width', 22 );
		$ret['select_sql'] = ",
			lc.`content_id` AS `stars_load`,
			sts.`update_count` AS stars_update_count,
			sts.`rating` AS stars_rating,
			( sts.`rating` * $pixels / 100 ) AS stars_pixels,
			( sth.`rating` * $stars / 100 ) AS stars_user_rating,
			( sth.`rating` * $pixels / 100 ) AS stars_user_pixels ";
		$ret['join_sql'] = "
			LEFT JOIN `".BIT_DB_PREFIX."stars` sts
				ON ( lc.`content_id`=sts.`content_id` )
			LEFT JOIN `".BIT_DB_PREFIX."stars_history` sth
				ON ( lc.`content_id`=sth.`content_id` AND sth.`user_id`='".$gBitUser->mUserId."' )";
		return $ret;
	}
}

/**
 * Content load sql service function
 * 
 * @param array $pObject 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function stars_content_load_sql( &$pObject ) {
	global $gBitSystem, $gBitUser, $gBitSmarty;
    if( method_exists( $pObject, 'getContentType' ) && $gBitSystem->isFeatureActive( 'stars_rate_'.$pObject->getContentType() ) ) {
		if( $gBitSystem->isFeatureActive( 'stars_use_ajax' ) ) {
			$gBitSmarty->assign( 'loadAjax', 'prototype' );
		}
		$stars = $gBitSystem->getConfig( 'stars_used_in_display', 5 );
		$pixels = $stars *  $gBitSystem->getConfig( 'stars_icon_width', 22 );
		$ret['select_sql'] = ",
			lc.`content_id` AS `stars_load`,
			sts.`update_count` AS stars_update_count,
			sts.`rating` AS stars_rating,
			( sts.`rating` * $pixels / 100 ) AS stars_pixels,
			( sth.`rating` * $stars / 100 ) AS stars_user_rating,
			( sth.`rating` * $pixels / 100 ) AS stars_user_pixels ";
		$ret['join_sql'] = "
			LEFT JOIN `".BIT_DB_PREFIX."stars` sts
				ON ( lc.`content_id`=sts.`content_id` )
			LEFT JOIN `".BIT_DB_PREFIX."stars_history` sth
				ON ( lc.`content_id`=sth.`content_id` AND sth.`user_id`='".$gBitUser->mUserId."' )";
		return $ret;
	}
}

/**
 * Content expunge sql service function
 * 
 * @param array $pObject 
 * @param array $pParamHash 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function stars_content_expunge( &$pObject, &$pParamHash ) {
	$stars = new LibertyStars( $pObject->mContentId );
	$stars->expunge();
}

?>
