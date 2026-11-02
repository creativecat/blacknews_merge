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

<section id="dbStellen_{$section_id}" class="dbStellen">
	<div class="dbStellen__grid">

	{if $options.sidebar}<aside class="dbStellen__info">{$options.sidebar}</aside>{/if}
	<div class="dbStellen__list">
	<form class="dbStellen__filter" aria-label="Stellen filtern">
		
			<div class="select" data-select data-filter-name="standort">
				<button type="button" class="select__btn" aria-haspopup="listbox" aria-expanded="false">
					<span class="select__label">Standort</span>
				</button>
				<ul class="select__list" role="listbox" hidden>
					<li role="option" data-value="" aria-selected="true">Alle Standorte</li>
					<li role="option" data-value="nuernberg">Nürnberg</li>
					<li role="option" data-value="ansbach">Ansbach</li>
				</ul>
			</div>
		
		<button type="button" class="dbStellen__filterBtn" data-filter="rechtsanwalt">Rechtsanwalt</button>
		<button type="button" class="dbStellen__filterBtn" data-filter="assistenz">Assistenzstellen</button>
		<button type="button" class="dbStellen__filterBtn" data-filter="praktikum">Praktikum</button>
	</form>
	<ul >
	{foreach $entries entry}{if $entry.publish}
	<li>

    {if $category}{assign var=temp value=$categories.$category}{$temp}{assign var=class value=$temp.url}
	  {*<h2 class="c_1024 {$catIcons.$class}">{$temp.category}</h2>*}
      {elseif $options.subtitle}<h2 class="c_1024">{$options.subtitle}</h2>{/if}
    <p class="label">Nürnberg | Sekretariat Arbeits- und Prozessrecht | Zwangsvollstreckung</p>
        <h3>{$entry.title}</h3>
        <small>
          <a href="{cat_url}/{$options.permalink}/{$entry.seoURL}/" class="btn btn--text-primary">Mehr erfahren</a>
        </small>

  </article>
  </li>
  {/if}{/foreach}
  </ul></div>
</section>