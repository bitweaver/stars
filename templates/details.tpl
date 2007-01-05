<div class="display stars">
	<div class="header">
		<h1>{tr}Rating Details{/tr}</h1>
	</div>

	<div class="body">
		{legend legend="Rating Details"}
			<div class="row">
				{formlabel label="Title"}
				{forminput}
				<a href="{$smarty.const.BIT_ROOT_URL}index.php?content_id={$starsDetails.content_id}">{$starsDetails.title|escape}</a> <small>({$starsDetails.content_type.content_description})</small>
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Creator"}
				{forminput}
					{displayname real_name=$starsDetails.creator_real_name login=$starsDetails.creator_user}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Last Editor"}
				{forminput}
					{displayname real_name=$starsDetails.modifier_real_name login=$starsDetails.modifier_user}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Hits"}
				{forminput}
					{$starsDetails.hits}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Rating"}
				{forminput}
					{$starsDetails.stars_rating} / 100
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Number of ratings"}
				{forminput}
					{$starsDetails.stars_update_count}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Users who have rated"}
				{forminput}
					<ul class="data">
						{foreach from=$starsDetails.user_ratings item=user}
							<li class="item {cycle values="odd,even"}">
								{displayname hash=$user} <small>({tr}weighting{/tr}: {$user.weight})</small> &bull; {$user.rating} / 100
							</li>
						{/foreach}
					</ul>
				{/forminput}
			</div>
		{/legend}
	</div><!-- end .body -->
</div><!-- end .stars -->
