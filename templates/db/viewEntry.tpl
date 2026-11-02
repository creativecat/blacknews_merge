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
<section id="dbListEntry_Entry" class="dbListEntry">
	<header class="dbListEntry__header">
		<h1>{$entry.title}</h1>
		<p class="dbListEntry__meta">
			<a href="{cat_url}/{$options.permalink}/{$entry.category.0.url}">{$entry.category.0.category}</a>
			<span>{format_date(strtotime($entry.publishDate))}</span>
		</p>
	</header>

	<div class="dbListEntry__grid">

		<aside class="dbListEntry__contact">
			<h2>Pressekontakt für weitere Informationen</h2>
			<p>Bei Fragen zu dieser Pressemitteilung stehen wir Ihnen gerne zur Verfügung.</p>
			<p>
				<strong>Anja Eckerstorfer</strong><br>
				Personalbüro / Presse<br>
				Dr. Beck &amp; Partner GbR<br>
				Eichendorffstr. 1<br>
				90491 Nürnberg<br>
				<a href="tel:+499119512850">+49 911 951 285-0</a><br>
				<a href="mailto:presse@ra-dr-beck.de">presse@ra-dr-beck.de</a>
			</p>
			<p>Wir gehören mit derzeit 38 Rechtsanwälten und acht Standorten in Bayern zu den führenden Insolvenzverwalterkanzleien in Deutschland. Grundlage unseres Erfolgs ist unsere interdisziplinäre Ausrichtung, mit der wir juristische Kompetenz und betriebswirtschaftliches Know-how vereinen.</p>

			{if $entry.options.pdf}
			<p class="dbListEntry__download">
				<a href="{$entry.options.pdf}" class="btn btn--text-primary" download>
					Pressebericht als PDF herunterladen
				</a>
			</p>
			{/if}
		</aside>

		<article class="dbListEntry__content">
			{$entry.content}
		</article>

		<figure class="dbListEntry__media">
			<img src="{$entry.image}" alt="{$entry.title}">
		</figure>

	</div>

	<p class="dbListEntry__back">
		<a class="btn btn--text-primary btn--text-primary--back"
		   href="{if $referer>0}{cat_url}/{$options.permalink}/{$entry.category.0.url}{elseif $referer<0}{cmsplink(-$referer)}{else}{cat_url}/{$options.permalink}{/if}">
			Zurück zu{if $referer>0} {$entry.category.0.category}{elseif $referer<0} {menutitle(-$referer)}{else}r Übersicht{/if}
		</a>
	</p>

	{$gallery}
</section>