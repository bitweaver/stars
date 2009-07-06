{strip}
{if $serviceHash.stars_load}
	{if $type != 'mini'}
		{capture name=starsText}
			{if $serviceHash.stars_rating}
				{math equation="rating * stars / 100" stars=$gBitSystem->getConfig('stars_used_in_display') rating=$serviceHash.stars_rating format="%.1f"} / {$gBitSystem->getConfig('stars_used_in_display')} {tr}in {$serviceHash.stars_update_count} votes{/tr}
			{else}
				{tr}Waiting for {$gBitSystem->getConfig('stars_minimum_ratings',5)} ratings{/tr}
			{/if}
			{assign var=userRating value=$serviceHash.stars_user_rating}
			{if $serviceHash.stars_user_rating} &nbsp;&bull;&nbsp; {tr}Your rating{/tr}: {$ratingNames.$userRating}{/if}
		{/capture}
	{/if}

	{assign var=divid value="stars-display-`$serviceHash.content_id`"}
	<div class="stars-container" id="stars-{$serviceHash.content_id}">
		<ul class="stars-rating">
			<li class="stars-current" style="width:{$serviceHash.stars_pixels|default:0}px;">{if $serviceHash.stars_user_pixels}{tr}Your rating{/tr}: {$serviceHash.stars_user_rating} / {$gBitSystem->getConfig('stars_used_in_display')}{else}{tr}Rate{/tr}{/if}</li>
			{if !$serviceHash.stars_user_rating || $gBitSystem->isFeatureActive( "stars_rerating" )}
				{foreach from=$starsLinks item=k key=rate}
					{assign var=rname value=$ratingNames.$rate}
					<li>
						{if !$gBitUser->isRegistered()}
							<a {if $type != 'mini'}onmouseover="document.getElementById('{$divid}').innerHTML='{tr}You need to log in to rate{/tr}';" {/if}class="stars-{$rate}" href="{$smarty.const.USERS_PKG_URL}login.php">{tr}You need to log in to rate{/tr}</a>
						{elseif $gBitSystem->isFeatureActive( 'stars_use_ajax' )}
						<a {if $type != 'mini'}onmouseover="document.getElementById('{$divid}').innerHTML='{$rname|escape:javascript}';" onmouseout="document.getElementById('{$divid}').innerHTML='{$smarty.capture.starsText|escape:javascript}';" {/if}class="stars-{$rate}" href="javascript:void(0);" 

onclick="var ajax = new BitBase.SimpleAjax(); ajax.update( 'stars-{$serviceHash.content_id}', '{$smarty.const.STARS_PKG_URL}rate.php', 'content_id={$serviceHash.content_id}&amp;stars_rating={$rate}{if $type == 'mini'}&amp;type=mini{/if}' );" title="{$rname}">{$rate}</a>
						{else}
							<a {if $type != 'mini'}onmouseover="$('{$divid}').innerHTML='{$rname|escape:javascript}';" onmouseout="$('{$divid}').innerHTML='{$smarty.capture.starsText|escape:javascript}';" {/if}class="stars-{$rate}" href="{$smarty.const.STARS_PKG_URL}rate.php?content_id={$serviceHash.content_id}&amp;stars_rating={$rate}" title="{$rname}">{$rate}</a>
						{/if}
					</li>
				{/foreach}
			{/if}
		</ul>
		{if $type != 'mini'}
			<div id="{$divid}" class="small">
				{$smarty.capture.starsText}
			</div>
		{/if}
	</div>
	{formfeedback hash=$starsfeed}
{/if}
{/strip}
