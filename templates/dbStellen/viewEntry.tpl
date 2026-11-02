{*
   ____  __      __    ___  _  _  ___    __   ____     ___  __  __  ___
  (  _ \(  )    /__\  / __)( )/ )/ __)  /__\ (_  _)   / __)(  \/  )/ __)
   ) _ < )(__  /(__)\( (__  )  (( (__  /(__)\  )(    ( (__  )    ( \__ \
  (____/(____)(__)(__)\___)(_)\_)\___)(__)(__)(__)    \___)(_/\/\_)(___/

   @author          Matthias Glienke, creativecat
   @copyright       2017 Matthias Glienke, creativecat
   @link            http://blackcat-cms.org
   @license         http://www.gnu.org/licenses/gpl.html
   @category        CAT_Module
   @package         blacknews

*}
<section id="ics_Entry">
	<figure>
		<div><div style="background-image:url({$entry.image});"></div></div>
		<figcaption class="c_1024">
			<h1>{$entry.title}</h1>
			<small>{assign var=class value=$entry.category.0.url}
				<a href="{cat_url}/{$options.permalink}/{$entry.category.0.url}" class="{$catIcons.$class}" title="{$entry.category.0.category}">{$entry.category.0.category}</a>
				<span class="ics-calendar">{format_date(strtotime($entry.publishDate))}</span>
			</small>
			<img src="{$entry.image}" alt="{$entry.title}">
		</figcaption>
		<img src="{cat_url}/templates/ics/css/default/images/bottom.svg" class="ics_EntryBottom"  alt="Bogen">
	</figure>
	{*<a class="button icon-circle-left" href="{cat_url}/{$options.permalink}/"> Zur&uuml;ck zur &Uuml;bersicht</a> *}
	<article class="c_1024">
		{*if $entry.image}<figure style="background-image:url('{$entry.image}')" id="ics_mImage" >{/if*}{*<img src="{$entry.image}" alt="Startbild zu {$entry.title}" class="right"></figure>*}{*/if*}
			{$entry.content}
		<p><br><a class="button {if $referer>0}{$catIcons.$class}{else}ics-arrow-left{/if}" href="{if $referer>0}{cat_url}/{$options.permalink}/{$entry.category.0.url}{elseif $referer<0}{cmsplink(-$referer)}{else}{cat_url}/{$options.permalink}{/if}"> Zur&uuml;ck zu{if $referer>0} {$entry.category.0.category}{elseif $referer<0} {menutitle(-$referer)}{else}r &Uuml;bersicht{/if}</a></p>
		{$gallery}
	</article>
</section>