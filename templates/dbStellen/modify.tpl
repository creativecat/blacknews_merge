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

{include file="../default/modify/javascript.tpl"}
{include file="modify/generalOptions.tpl"}
<div id="blackNews_{$section_id}">
	{include file="../default/modify/set_skin.tpl"}
	<div class="blackNews fc_br_all">
		<aside class="bc_sidebar fc_br_left fc_gradient1">
			<button class="bc_add fc_gradient_blue fc_gradient_hover icon-plus fc_br_topleft"> Eintrag hinzuf&uuml;gen</button>
			<ul id="blackNewsList_{$section_id}">{foreach $entries entry}{include file="../default/modify/entryList.tpl"}{/foreach}</ul>
		</aside><div class="bc_Main" class="fc_br_right fc_border_all">
			<form action="{$CAT_URL}/modules/blackNews/save.php" id="bc_Form_{$section_id}" class="bc_Form" method="post" data-entryid="16">
				{include file="../default/modify/buttons.tpl"}
				<div class="bc_MainContent">
					{include file="modify/entryForm.tpl"}
				</div>
				{include file="../default/modify/buttons.tpl"}
			</form>
			<footer>Zuletzt geändert von: <strong class="icon-user"> {$user|default:"—"}</strong> &bull; Erstellt: <strong class="icon-calendar"> {$created|default:"—"}</strong> &bull; Letzte &Auml;nderung: <strong class="icon-modify"> {$modified|default:"—"}</strong></footer>
		</div>
	</div>
	<small class="bcVersion">Modulversion: {$version}</small>
</div>