<div class="display stars">
	<div class="header">
		<h1>{tr}Rating Details{/tr}</h1>
	</div>

	<div class="body">
		{legend legend="Rating Details"}
			{if $starsDetails}
				<div class="control-group">
					{formlabel label="Title"}
					{forminput}
					<a href="{$smarty.const.BIT_ROOT_URL}index.php?content_id={$starsDetails.content_id}">{$starsDetails.title|escape}</a> <small>({$starsDetails.content_type.content_name})</small>
					{/forminput}
				</div>

				<div class="control-group">
					{formlabel label="Creator"}
					{forminput}
						{displayname real_name=$starsDetails.creator_real_name login=$starsDetails.creator_user}
					{/forminput}
				</div>

				<div class="control-group">
					{formlabel label="Last Editor"}
					{forminput}
						{displayname real_name=$starsDetails.modifier_real_name login=$starsDetails.modifier_user}
					{/forminput}
				</div>

				<div class="control-group">
					{formlabel label="Hits"}
					{forminput}
						{$starsDetails.hits}
					{/forminput}
				</div>

				<div class="control-group">
					{formlabel label="Rating"}
					{forminput}
						{$starsDetails.stars_rating}%
					{/forminput}
				</div>

				<div class="control-group">
					{formlabel label="Number of ratings"}
					{forminput}
						{$starsDetails.stars_update_count}
					{/forminput}
				</div>

				<div class="control-group">
					{formlabel label="Users who have rated"}
					{forminput}
						<ul class="data">
							{foreach from=$starsDetails.user_ratings item=user}
								<li class="item {cycle values="odd,even"}">
									{displayname hash=$user} <small>({tr}weighting{/tr}: {$user.weight})</small> &bull; {$user.rating}%
								</li>
							{/foreach}
						</ul>
					{/forminput}
				</div>
			{elseif $userRatings}
				{include file="bitpackage:stars/user_ratings.tpl"}

				<div class="control-group">
					{formlabel label="Individual Ratings" for=""}
					{forminput}
						<ul class="data">
							{foreach from=$userRatings item=rating}
								<li class="item {cycle values="odd,even"}">
									{$rating.display_link} &bull; {$rating.user_rating}%
								</li>
							{/foreach}
						</ul>
					{/forminput}
				</div>
			{/if}
		{/legend}
	</div><!-- end .body -->
</div><!-- end .stars -->
