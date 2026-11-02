	{*
   ____  __      __    ___  _  _  ___    __   ____     ___  __  __  ___
  (  _ \(  )    /__\  / __)( )/ )/ __)  /__\ (_  _)   / __)(  \/  )/ __)
   ) _ < )(__  /(__)\( (__  )  (( (__  /(__)\  )(    ( (__  )    ( \__ \
  (____/(____)(__)(__)\___)(_)\_)\___)(__)(__)(__)    \___)(_/\/\_)(___/

   @author          Black Cat Development
   @copyright       2016 Black Cat Development
   @link            http://blackcat-cms.org
   @license         http://www.gnu.org/licenses/gpl.html
   @category        CAT_Core
   @package         CAT_Core

*}

<article class="dbNews_Entries">
	<figure>
		{if $entry.image}
		{assign var=class value=$entry.url}
		<div style="background-image: url('{$entry.image}');">
			<img src="{$entry.image}" class="dbNews_mImage" alt="{$entry.title}">
			<span class="bN_author {$catIcons.$class}" title="{$entry.category}"></span>
		</div>{/if}
		<figcaption>
			<h3>{$entry.title}</h3>
			<span class="">{format_date(strtotime($entry.publishDate))}</span>
			{truncateHTML($entry.text,250," ...")}
			<aside>
				<a href="{cat_url}/{$orgOptions.permalink}/{$entry.seoURL}/" class="btn btn--text-primary">weiterlesen ...</a>
			</aside>
			{*<a href="{cat_url}/{$orgOptions.permalink}/{$entry.seoURL}/" title="{str_replace('"','',$entry.title)}" class="dbNews_fullLink"></a>*}
		</figcaption>
	</figure>
</article>