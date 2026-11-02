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

<section id="dbList_{$section_id}" class="dbList">
	<nav class="dbList__anchors" aria-label="Auf dieser Seite">
		<span class="dbList__anchorsTitle">Auf dieser Seite</span>
		<ul>
			<li><a class="btn btn--outlined" href="#presseberichte">Presseberichte</a></li>
			<li><a class="btn btn--outlined"  href="#mediadaten">Mediadaten</a></li>
		</ul>
	</nav>

	<div class="dbList__main">

		<div class="dbList__topbar">
			<div class="dbList__filter" aria-label="Filter">
				<div class="select dbList__filterItem" data-select data-filter-name="year">
					<button type="button" class="select__btn" aria-haspopup="listbox" aria-expanded="false">
						<span class="select__label">Jahr</span>
					</button>
					<ul class="select__list" role="listbox" hidden>
						<li role="option" data-value="" aria-selected="true">Alle Jahre</li>
						{foreach $years year}
						<li role="option" data-value="{$year}">{$year}</li>
						{/foreach}
					</ul>
				</div>

				<div class="select dbList__filterItem" data-select data-filter-name="category">
					<button type="button" class="select__btn" aria-haspopup="listbox" aria-expanded="false">
						<span class="select__label">Kategorie</span>
					</button>
					<ul class="select__list" role="listbox" hidden>
						<li role="option" data-value="" aria-selected="true">Alle Kategorien</li>
						{foreach $categories cat}
						<li role="option" data-value="{$cat.url}">{$cat.category}</li>
						{/foreach}
					</ul>
				</div>

				<div class="select dbList__filterItem" data-select data-filter-name="source">
					<button type="button" class="select__btn" aria-haspopup="listbox" aria-expanded="false">
						<span class="select__label">Quelle</span>
					</button>
					<ul class="select__list" role="listbox" hidden>
						<li role="option" data-value="" aria-selected="true">Alle Quellen</li>
						{foreach $sources source}
						<li role="option" data-value="{$source}">{$source}</li>
						{/foreach}
					</ul>
				</div>

				<div class="select dbList__filterItem" data-select data-filter-name="search">
					<button type="button" class="select__btn" aria-haspopup="listbox" aria-expanded="false">
						<span class="select__label">Thema</span>
					</button>
					<ul class="select__list" role="listbox" hidden>
						<li role="option" data-value="" aria-selected="true">Alle Themen</li>
						{foreach $topics topic}
						<li role="option" data-value="{$topic}">{$topic}</li>
						{/foreach}
					</ul>
				</div>
			</div>

			<div class="dbList__pager">
				<button type="button" class="btn-icon btn-icon--left" aria-label="Vorherige Seite"></button>
				<button type="button" class="btn-icon btn-icon--right" aria-label="Nächste Seite"></button>
			</div>
		</div>

		<ul class="dbList__list">
		{foreach $entries entry}
		{if $entry.publish}
			<li class="dbEntries"
				data-category="{$entry.url}"
				data-year="{$entry.year}"
				data-source="{$entry.source}"
				data-search="{$entry.topic}">
				<div class="dbList__image">
					<img src="{$entry.image}" alt="{$entry.title}" loading="lazy">
				</div>
				<div class="dbEntries__content">
					<span class="dbEntries__category">{$entry.category}</span>
					<h3>{$entry.title}</h3>
					<p class="dbEntries__meta">
						<span>{format_date(strtotime($entry.publishDate))}</span>
						{if $entry.source} – Quelle: <span>{$entry.source}</span>{/if}
					</p>
					<a href="{cat_url}/{$options.permalink}/{$entry.seoURL}/" class="btn btn--text-primary">Mehr erfahren</a>
				</div>
			</li>
		{/if}
		{/foreach}
		</ul>

	</div>
</section>