<?php
/* Copyright (C) 2025 Pierre Ardoin <developpeur@lesmetiersdubatiment.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */
/**
 * \file admin/index.php
 * \ingroup dc1
 * \brief DC1 module administration home page.
 */


$res = @include '../../main.inc.php';
if (! $res) {
	$res = @include '../../../main.inc.php';
}

require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once dol_buildpath('/dc1/lib/dc1.lib.php', 1);
require_once dol_buildpath('/dc1/core/modules/modDc1.class.php', 1);

if (empty($user->admin)) {
	accessforbidden();
}

$langs->loadLangs(array('admin', 'dc1@dc1'));

$module = new modDc1($db);

llxHeader('', $langs->trans('DC1AdminHomeTitle'));

$head = dc1AdminPrepareHead();
dol_fiche_head($head, 'home', $langs->trans('Module450005Name'), 0, '');

// EN: Provide a short introduction for administrators.
// FR: Fournit une courte introduction pour les administrateurs.
print '<p class="opacitymedium">' . $langs->trans('DC1AdminHomeIntro') . '</p>';

print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	// EN: Present the module purpose for administrators.
	// FR: Présente l'objectif du module aux administrateurs.
	print '<div class="underbanner">';
	print '<p>' . $langs->trans('Module450005Desc') . '</p>';
	print '</div>';
	// EN: Offer quick access to the official documentation.
	// FR: Offre un accès rapide à la documentation officielle.
	print '<div class="underbanner">';
	print '<h3>' . $langs->trans('DC1AdminHomeDocumentation') . '</h3>';
	print '<p><a class="classfortooltip" target="_blank" rel="noopener" href="https://www.economie.gouv.fr/cedef/dematerialisation-formulaires-dc1-dc2">' . $langs->trans('DC1AdminHomeDocumentationLink') . '</a></p>';
	print '</div>';
	print '</div>';
	print '<div class="fichehalfright">';
	// EN: Display module version and contact reference.
	// FR: Affiche la version du module et le contact de référence.
	print '<div class="underbanner">';
	print '<h3>' . $langs->trans('DC1AdminHomeVersion') . '</h3>';
	print '<p>' . dol_escape_htmltag($module->version) . '</p>';
	print '</div>';
	print '<div class="underbanner">';
	print '<h3>' . $langs->trans('DC1AdminHomeSupport') . '</h3>';
	print '<p><a class="classfortooltip" target="_blank" rel="noopener" href="mailto:developpeur@lesmetiersdubatiment.fr">' . $langs->trans('DC1AdminHomeSupportLink') . '</a></p>';
	print '</div>';
	print '</div>';
print '</div>';
dol_fiche_end();

llxFooter();
$db->close();
