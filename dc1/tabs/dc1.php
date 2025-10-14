<?php
/* Copyright (C) 2018-2020      Pierre Ardoin        <mapiolca@me.com>
 *                                             
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**	    \file       htdocs/delegation/tabs/DC1.php
 *		\ingroup    delegation
 *		\brief      DC1 module tabs view
 */


$res=@include("../../main.inc.php");					// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory

require_once DOL_DOCUMENT_ROOT . '/core/modules/propale/modules_propale.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT . '/comm/propal/class/propal.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/propal.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';

if (! empty($conf->projet->enabled)) 
{
	require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
	require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
}
if (!empty($conf->variants->enabled)) {
	require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductCombination.class.php';
}

dol_include_once("/dc1/class/dc1.class.php");

$langs->loadLangs(array('companies', 'propal', 'compta', 'bills', 'orders', 'products', 'deliveries', 'sendings', 'other'));
if (! empty($conf->incoterm->enabled)) $langs->load('incoterm');
if (! empty($conf->margin->enabled)) $langs->load('margins');
if (! empty($conf->projet->enabled)) $langs->load("projects");

$id = GETPOST('id', 'int');
$lineid = GETPOST('lineid') ? GETPOST('lineid') : 0;
$field = GETPOST('field');

$action = GETPOST('action', 'alpha');
$cancel = GETPOST('cancel') ? true : false;
$error = false;
$message = '';
$formconfirm = null;

$form = new Form($db);
$object = new Propal($db);
$dc1 = new DC1($db);

$canAddLines = $user->admin || $user->rights->delegation->myactions->create;
$canReadLines = $user->admin || $user->rights->delegation->myactions->read;
$canDeleteLines = $user->admin || $user->rights->delegation->myactions->delete;

if (!$canReadLines)
{
	accessforbidden();
}

if ($id > 0)
{
	$result = $object->fetch($id);

	if ($result > 0)
	{
		$dc1->fetch();
		
		if ($object->element != 'propal')
		{
			$error = true;
			$message = $langs->trans('NotAPropale');
		}

	}
	else
	{
		$error = true;
		$message = $langs->trans('ObjectNotFound');
	}
}
else
{
	$error = true;
	$message = $langs->trans('ObjectNotFound');
}

if (!$error && !$cancel)
{
    $result = $dc1->call($action, array($user));

    if ($result > 0)
    {
        $message = $dc1->error; //
    }
    else
    {
        $message = $dc1->error;
        $error = true;
    }
}

$head = propal_prepare_head($object);
$current_head = 'dc1';
 
$soc = new Societe($db);
$soc->fetch($object->socid);

// Security check
if (!empty($user->socid)) $socid = $user->socid;
$result = restrictedArea($user, 'propal', $id);


if (! empty($conf->projet->enabled)) 
{
	$project = new Project($db);
	$project->fetch($object->fk_project);
}

$numLines = sizeof($dc1->lines);
	    	
include '../tpl/dc1.default.tpl.php';

$db->close();
?>
