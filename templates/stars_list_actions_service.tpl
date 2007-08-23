{strip}
{if $loadStars}
	{if $serviceHash.stars_pixels || $gBitSystem->isFeatureActive('stars_always_list') }
		<div class="stars-rating"><div class="stars-current" style="width:{$serviceHash.stars_pixels}px;"></div></div>
	{/if}
{/if}
{/strip}
