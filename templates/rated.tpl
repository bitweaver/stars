<div class="display stars">
	<div class="header">
		<h1>{tr}Rated Content{/tr}</h1>
	</div>

	<div class="body">
		<table class="data">
			<caption>{tr}List of rated content{/tr}</caption>
			<tr>
				<th>{smartlink ititle="Title" isort="title"}</th>
				<th>{smartlink ititle="Content Type" isort="content_type_guid"}</th>
				<th>{smartlink ititle="Number of ratings" isort="update_count"}</th>
				<th>{smartlink ititle="Rating" isort="sts.rating" iorder=desc idefault=1}</th>
			</tr>

			{foreach from=$ratedContent item=item}
				<tr class="{cycle values="odd,even"}">
					<td>{$item.display_link}</td>
					<td>{$item.content_type_guid}</td>
					<td style="text-align:right;">{$item.update_count}</td>
					<td style="text-align:right;">
						<a href="{$smarty.const.STARS_PKG_URL}details.php?content_id={$item.content_id}">{$item.rating}%</a>
					</td>
				</tr>
			{foreachelse}
				<tr class="norecords">
					<td colspan="4">{tr}No records found{/tr}</td>
				</tr>
			{/foreach}
		</table>

		{pagination}
	</div><!-- end .body -->
</div><!-- end .stars -->
