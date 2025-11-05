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
 * \file lib/dc1.lib.php
 * \ingroup dc1
 * \brief Library file for the DC1 module.
 */

/**
 * Prepare tabs for DC1 administration pages.
 *
 * @return array
 */
function dc1AdminPrepareHead()
{
	global $langs;

	// EN: Build the administration tabs for the DC1 module.
	// FR: Construit les onglets d'administration du module DC1.
	$head = array();

	$h = 0;
	$head[$h][0] = dol_buildpath('/dc1/admin/index.php', 1);
	$head[$h][1] = $langs->trans('DC1AdminHomeTitle');
	$head[$h][2] = 'home';
	$h++;

	$head[$h][0] = dol_buildpath('/dc1/admin/setup.php', 1);
	$head[$h][1] = $langs->trans('SetupG');
	$head[$h][2] = 'setup';

	return $head;
}
