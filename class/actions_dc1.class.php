<?php
/* Copyright (C) 2026 Pierre Ardoin <developpeur@lesmetiersdubatiment.fr>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

/** Keep the procurement forms together after native tab permission checks. */
class ActionsDc1
{
	/** @var DoliDB */
	public $db;
	/** @var string */
	public $error = '';
	/** @var string[] */
	public $errors = array();
	/** @var array<string, mixed> */
	public $results = array();
	/** @var string|null HookManager resets this property to null. */
	public $resprints;

	/** @param DoliDB $db Database handler */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * Reorder only tabs already admitted by Dolibarr; never create a tab.
	 *
	 * @param array{mode?: string, head?: array<int, array<int, string>>} $parameters Hook parameters
	 * @param CommonObject $object Current object
	 * @param string $action Current action
	 * @param HookManager $hookmanager Native hook manager
	 * @return int 0 to preserve native and other modules' results
	 */
	public function completeTabsHead($parameters, &$object, &$action, $hookmanager)
	{
		if (!is_object($object) || $object->element !== 'propal'
			|| ($parameters['mode'] ?? '') !== 'add' || !isset($parameters['head'])) {
			return 0;
		}

		$head = array_values($parameters['head']);
		$dc1 = null;
		$dc2 = null;
		foreach ($head as $index => $tab) {
			if (($tab[2] ?? '') === 'dc1') {
				$dc1 = $index;
			} elseif (($tab[2] ?? '') === 'dc2') {
				$dc2 = $index;
			}
		}
		if ($dc1 === null || $dc2 === null || $dc2 === $dc1 + 1) {
			return 0;
		}

		$tab = $head[$dc2];
		array_splice($head, $dc2, 1);
		if ($dc2 < $dc1) {
			$dc1--;
		}
		array_splice($head, $dc1 + 1, 0, array($tab));
		$parameters['head'] = $head;
		return 0;
	}
}
