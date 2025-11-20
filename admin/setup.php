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
 * \file admin/setup.php
 * \ingroup dc1
 * \brief General setup page for the DC1 module.
 */

$res = @include '../../main.inc.php';
if (! $res) {
	$res = @include '../../../main.inc.php';
}

require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
dol_include_once('/dc1/lib/dc1.lib.php');

if (empty($user->admin)) {
	accessforbidden();
}

$langs->loadLangs(array('admin', 'dc1@dc1'));

llxHeader('', $langs->trans('DC1Setup'));

$head = dc1AdminPrepareHead();
dol_fiche_head($head, 'setup', $langs->trans('Module450005Name'), 0, '');

print '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';

print '<table class="noborder" width="100%">';
print '<thead>';
print '<tr class="liste_titre">';
print '<th>' . $langs->trans('Name') . '</th>';
print '<th class="center" width="260">' . $langs->trans('Value') . '</th>';
print '</tr>';
print '</thead>';
print '<tbody>';

// EN: Toggle the activation of the DC1 tab without reloading the page.
// FR: Permet d'activer l'onglet DC1 sans recharger la page.
print '<tr class="oddeven">';
print '<td>' . $langs->trans('LMDB_DC1_ACTIVATED') . '</td>';
print '<td class="center">' . ajax_constantonoff('LMDB_DC1_ACTIVATED') . '</td>';
print '</tr>';

print '</tbody>';
print '</table>';

print '</form>';

dol_fiche_end();

llxFooter();
$db->close();
