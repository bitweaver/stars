{strip}
{if $loadStars}
	{if $serviceHash.stars_pixels}
		<div class="stars-rating"><div class="stars-current" style="width:{$serviceHash.stars_pixels}px;"></div></div>
	{/if}
	<br />
{/if}
{/strip}
