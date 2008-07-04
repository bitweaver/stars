{strip}
{assign var=stars value=$gBitSystem->getConfig('stars_used_in_display',5)}
{assign var=icon_width value=$gBitSystem->getConfig('stars_icon_width',22)}
{assign var=icon_height value=$gBitSystem->getConfig('stars_icon_height',22)}
{assign var=icon_height_current value=-`$icon_height*2`}
.stars-rating	{ldelim}line-height:1px; list-style:none; margin:0px; padding:0px; width:{$stars*$icon_width}px; height:{$icon_height}px; position:relative; background:url( {biticon ipackage=stars iname=stars url=true} ) top left repeat-x;{rdelim}
.stars-rating li	{ldelim}list-style:none; padding:0px; margin:0px; /*\*/ float:left; /* */}
.stars-rating li a	{ldelim}display:block; width:{$icon_width}px; height:{$icon_height}px; text-decoration:none; text-indent:-9000px; z-index:20; position:absolute; padding:0px;{rdelim}
.stars-rating li a:hover	{ldelim}background:url( {biticon ipackage=stars iname=stars url=true} ) left center; z-index:2; left:0px;{rdelim}
.stars-rating li a:hover	{ldelim}background:url( {biticon ipackage=stars iname=stars url=true} ) left center; z-index:2; left:0px;{rdelim}
{* starsLinks is not set at the point when this file is used *}
{section name=ratei start=1 loop=$stars+1 step=1}
{assign var=rate value=$smarty.section.ratei.index}
.stars-rating a.stars-{$rate}	{ldelim}left:{$rate*$icon_width-$icon_width}px;{rdelim}
.stars-rating a.stars-{$rate}:hover	{ldelim}width:{$rate*$icon_width}px;{rdelim}
{/section}
.stars-rating .stars-current	{ldelim}
	background:url( {biticon ipackage=stars iname=stars url=true} ) left bottom; 
	position:absolute; 
	height:{$icon_height}px; 
	display:block; 
	text-indent:-9000px; 
	z-index:1;
	text-align: left;
{rdelim}
.stars-rating	{ldelim}
	background-position: 0px 0px;
	width:{$icon_width*$stars}px;
{rdelim}
.stars-current	{ldelim}
	background-position: 0px {$icon_height_current}px;
{rdelim}
{/strip}
