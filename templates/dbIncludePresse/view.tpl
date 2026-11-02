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

{if count($entries)>0}
<section id="dbNews_{$section_id}" class="dbNews">
  {*<a href="{cat_url}/{$options.permalink}/{if $options.category}{$firstEntry.url}{/if}" class="button">Alle {if $options.category}{$firstEntry.category}{else}Neuigkeiten{/if}</a>*}
  {if $options.title}<h2>{$options.title}{if $options.title} <span>{$options.sub_title}</span>{/if}</h2>{/if}
  <div>{assign var=count value=0}
    {foreach $entries entry}
      {if $entry.publish && (!$options.newsCount || $count < $options.newsCount)}{assign var=count value=$count+1}{include file="view/entryList.tpl"}{/if}
    {/foreach}
  </div>
  <nav class="dbList__nav" aria-label="News-Navigation">
	<button type="button" class="btn-icon btn-icon--left" aria-label="Vorheriger Beitrag"></button>
	<div class="dbList__navDots" role="tablist">
	{foreach $entries entry}<button type="button" class="dbList__navDot" role="tab" aria-label="{$entry.title}"></button>{/foreach}
	</div>
	<button type="button" class="btn-icon btn-icon--right" aria-label="Nächster Beitrag"></button>
  </nav>
</section>{/if}