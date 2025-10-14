<?php
/* Copyright (C) 2018-2019      Pierre Ardoin
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 */

require_once DOL_DOCUMENT_ROOT . "/core/class/commonobject.class.php";
require_once DOL_DOCUMENT_ROOT . "/core/lib/functions.lib.php";
require_once DOL_DOCUMENT_ROOT . "/core/lib/functions2.lib.php";
require_once DOL_DOCUMENT_ROOT . '/core/lib/price.lib.php';
require_once DOL_DOCUMENT_ROOT . "/core/lib/files.lib.php";
require_once DOL_DOCUMENT_ROOT . '/comm/propal/class/propal.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT . '/comm/action/class/actioncomm.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/modules/propale/modules_propale.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/propal.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
require_once DOL_DOCUMENT_ROOT . '/fourn/class/fournisseur.commande.class.php';

/**
 * Class DC1
 * Gestion d'un bloc DC1 lié à un object (ex : propal, commande...)
 */
class DC1 extends CommonObject{
    /** @var DoliDB */
    public $db;

    public $error;
    public $element = 'DC1';
    public $table_element_line = 'DC1';
    public $lines = [];
    public $line;

    /**
     * DC1 constructor.
     * @param DoliDB $db
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Generic call wrapper (exécution d'action si méthode existante)
     *
     * @param string $action
     * @param array $args
     * @return mixed
     */
    public function call($action, $args)
    {
        if (empty($action)) {
            return 0;
        }

        if (method_exists($this, $action)) {
            return call_user_func_array([$this, $action], $args);
        }

        return 0;
    }

    /**
     * Récupère les lignes DC1 pour l'objet courant (global $object)
     *
     * @return int 1 si OK, <0 si erreur
     */
    public function fetch()
    {
        global $object, $langs;

        $this->lines = [];

        if (!isset($object->id) || empty($object->id)) {
            $this->error = 'ObjectNotProvided';
            return -1;
        }

        $fk_object = (int)$object->id;
        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "DC1 WHERE fk_object = " . $fk_object;

        dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);

        $resql = $this->db->query($sql);
        if ($resql === false) {
            $this->error = $this->db->lasterror() . " sql=" . $sql;
            return -1;
        }

        $num = $this->db->num_rows($resql);

        if ($num === 0) {
            // Si aucune ligne existante, on crée une ligne par défaut et on relance fetch
            $defaultLine = new DC1Line($this->db);
            $defaultLine->fk_object = $fk_object;
            $defaultLine->fk_element = isset($object->element) ? $this->db->escape($object->element) : '';
            $defaultLine->id_acheteur = isset($object->socid) ? (int)$object->socid : 0;
            $res = $defaultLine->insert();
            if ($res <= 0) {
                $this->error = $defaultLine->error;
                return -1;
            }
            // rappel récursif simple : la nouvelle ligne sera lue
            return $this->fetch();
        }

        while ($obj = $this->db->fetch_object($resql)) {
            $this->lines[] = $this->hydrateLineFromDbObject($obj);
        }

        return 1;
    }

    /**
     * Met à jour une ligne DC1 (sert pour l'ajax editing)
     *
     * @param User $user
     * @return int rowid si OK (>0), <0 si erreur
     */
    public function updateline($user)
    {
        global $object, $langs;

        $lineid = (int)GETPOST('lineid', 'int');
        if ($lineid <= 0) {
            $this->error = $langs->trans('InvalidParameter');
            return -1;
        }

        $field = GETPOST('field', 'alpha');
        // On récupère tous les champs attendus
        $input = [
            'id_acheteur' => isset($object->socid) ? (int)$object->socid : 0,
            'objet_consultation' => GETPOST('objet_consultation', 'alpha'),
            'ref_consultation' => GETPOST('ref_consultation', 'alpha'),
            'objet_candidature' => GETPOST('objet_candidature', 'int'),
            'n_lots' => GETPOST('n_lots', 'alpha'),
            'designation_lot' => GETPOST('designation_lot', 'alpha'),
            'candidat_statut' => GETPOST('candidat_statut', 'int'),
            'F_engagement' => GETPOST('F_engagement', 'int'),
            'adresse_internet' => GETPOST('adresse_internet', 'alpha'),
            'renseignement_adresse' => GETPOST('renseignement_adresse', 'alpha'),
            'dc2' => GETPOST('dc2', 'int'),
        ];

        $line = new DC1Line($this->db);
        $fetched = $line->fetch($lineid);
        if ($fetched <= 0) {
            $this->error = $langs->trans('DC1LineDoesNotExist');
            return -1;
        }

        // Si field renseigné, ne modifier que celui-ci (sécurité)
        if (!empty($field) && array_key_exists($field, $input)) {
            $line->$field = $input[$field];
            $line->field = $field;
        } else {
            // sinon on met à jour l'ensemble
            foreach ($input as $k => $v) {
                $line->$k = $v;
            }
            $line->field = ''; // vide => update de tout
        }

        $result = $line->update();
        if ($result > 0) {
            // recharge les lignes en mémoire
            $this->fetch();
            return $line->rowid;
        }

        $this->error = $line->error;
        return -2;
    }

    /**
     * Hydrate un objet DC1Line à partir d'un stdClass retourné par la DB.
     *
     * @param stdClass $obj
     * @return DC1Line
     */
    protected function hydrateLineFromDbObject($obj)
    {
        $line = new DC1Line($this->db);

        $line->rowid = isset($obj->rowid) ? (int)$obj->rowid : 0;
        $line->fk_object = isset($obj->fk_object) ? (int)$obj->fk_object : (isset($obj->fk_objectdet) ? (int)$obj->fk_objectdet : 0);
        $line->fk_element = isset($obj->fk_element) ? $this->db->escape($obj->fk_element) : '';
        $line->id_acheteur = isset($obj->id_acheteur) ? (int)$obj->id_acheteur : 0;
        $line->objet_consultation = isset($obj->objet_consultation) ? trim($obj->objet_consultation) : '';
        $line->ref_consultation = isset($obj->ref_consultation) ? trim($obj->ref_consultation) : '';
        $line->objet_candidature = isset($obj->objet_candidature) ? (int)$obj->objet_candidature : 0;
        $line->n_lots = isset($obj->n_lots) ? trim($obj->n_lots) : '';
        $line->designation_lot = isset($obj->designation_lot) ? trim($obj->designation_lot) : '';
        $line->candidat_statut = isset($obj->candidat_statut) ? (int)$obj->candidat_statut : 0;
        $line->F_engagement = isset($obj->F_engagement) ? (int)$obj->F_engagement : 0;
        $line->adresse_internet = isset($obj->adresse_internet) ? trim($obj->adresse_internet) : '';
        $line->renseignement_adresse = isset($obj->renseignement_adresse) ? trim($obj->renseignement_adresse) : '';
        $line->dc2 = isset($obj->dc2) ? (int)$obj->dc2 : 0;

        return $line;
    }
}

/**
 * Class DC1Line
 * Représente une ligne DC1 (table llx_DC1)
 */
class DC1Line
{
    /** @var DoliDB */
    protected $db;

    public $error;
    public $oldline;

    // propriétés correspondant aux colonnes
    public $rowid;
    public $fk_object;
    public $fk_element;
    public $id_acheteur;
    public $objet_consultation;
    public $ref_consultation;
    public $objet_candidature;
    public $n_lots;
    public $designation_lot;
    public $candidat_statut;
    public $F_engagement;
    public $adresse_internet;
    public $renseignement_adresse;
    public $dc2;

    /** @var string|null champ modifié en mode "update field" (si vide => update full) */
    public $field;

    /**
     * DC1Line constructor.
     * @param DoliDB $DB
     */
    public function __construct($DB)
    {
        $this->db = $DB;

        // valeurs par défaut
        $this->rowid = 0;
        $this->fk_object = 0;
        $this->fk_element = '';
        $this->id_acheteur = 0;
        $this->objet_consultation = '';
        $this->ref_consultation = '';
        $this->objet_candidature = 0;
        $this->n_lots = '';
        $this->designation_lot = '';
        $this->candidat_statut = 0;
        $this->F_engagement = 0;
        $this->adresse_internet = '';
        $this->renseignement_adresse = '';
        $this->dc2 = 0;
        $this->field = null;
    }

    /**
     * Récupère la ligne par rowid
     *
     * @param int $lineid
     * @return int rowid si ok (>0), <0 si erreur
     */
    public function fetch($lineid = 0)
    {
        global $langs;

        $lineid = (int)$lineid;
        if ($lineid <= 0) {
            $this->error = $langs->trans('DC1LineDoesNotExist');
            return -1;
        }

        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "DC1 WHERE rowid = " . $lineid;
        dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);

        $resql = $this->db->query($sql);
        if ($resql === false) {
            $this->error = $this->db->lasterror() . " sql=" . $sql;
            return -1;
        }

        $num = $this->db->num_rows($resql);
        if ($num === 0) {
            $this->error = $langs->trans('DC1LineDoesNotExist');
            return -1;
        }

        $obj = $this->db->fetch_object($resql);

        // Remplissage sécurisé des propriétés
        $this->rowid = isset($obj->rowid) ? (int)$obj->rowid : 0;
        $this->fk_object = isset($obj->fk_object) ? (int)$obj->fk_object : (isset($obj->fk_objectdet) ? (int)$obj->fk_objectdet : 0);
        $this->fk_element = isset($obj->fk_element) ? $this->db->escape($obj->fk_element) : '';
        $this->id_acheteur = isset($obj->id_acheteur) ? (int)$obj->id_acheteur : 0;
        $this->objet_consultation = isset($obj->objet_consultation) ? trim($obj->objet_consultation) : '';
        $this->ref_consultation = isset($obj->ref_consultation) ? trim($obj->ref_consultation) : '';
        $this->objet_candidature = isset($obj->objet_candidature) ? (int)$obj->objet_candidature : 0;
        $this->n_lots = isset($obj->n_lots) ? trim($obj->n_lots) : '';
        $this->designation_lot = isset($obj->designation_lot) ? trim($obj->designation_lot) : '';
        $this->candidat_statut = isset($obj->candidat_statut) ? (int)$obj->candidat_statut : 0;
        $this->F_engagement = isset($obj->F_engagement) ? (int)$obj->F_engagement : 0;
        $this->adresse_internet = isset($obj->adresse_internet) ? trim($obj->adresse_internet) : '';
        $this->renseignement_adresse = isset($obj->renseignement_adresse) ? trim($obj->renseignement_adresse) : '';
        $this->dc2 = isset($obj->dc2) ? (int)$obj->dc2 : 0;

        return $this->rowid;
    }

    /**
     * Insert a new line in DB
     *
     * @param int $notrigger
     * @return int 1 si OK, <0 si erreur
     */
    public function insert($notrigger = 0)
    {
        global $user, $langs, $conf;

        // sanitation minimale : cast et escape
        $fk_object = (int)$this->fk_object;
        $fk_element = $this->db->escape((string)$this->fk_element);
        $id_acheteur = (int)$this->id_acheteur;
        $objet_consultation = $this->db->escape(trim((string)$this->objet_consultation));
        $ref_consultation = $this->db->escape(trim((string)$this->ref_consultation));
        $objet_candidature = (int)$this->objet_candidature;
        $n_lots = $this->db->escape(trim((string)$this->n_lots));
        $designation_lot = $this->db->escape(trim((string)$this->designation_lot));
        $candidat_statut = (int)$this->candidat_statut;
        $F_engagement = (int)$this->F_engagement;
        $adresse_internet = $this->db->escape(trim((string)$this->adresse_internet));
        $renseignement_adresse = $this->db->escape(trim((string)$this->renseignement_adresse));
        $dc2 = (int)$this->dc2;

        $this->db->begin();

        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "DC1 ";
        $sql .= "(`fk_object`, `fk_element`, `id_acheteur`, `objet_consultation`, `ref_consultation`, `objet_candidature`, `n_lots`, `designation_lot`, `candidat_statut`, `F_engagement`, `adresse_internet`, `renseignement_adresse`, `dc2`) VALUES (";
        $sql .= $fk_object . ", ";
        $sql .= "'" . $fk_element . "', ";
        $sql .= "'" . $id_acheteur . "', ";
        $sql .= "'" . $objet_consultation . "', ";
        $sql .= "'" . $ref_consultation . "', ";
        $sql .= "'" . $objet_candidature . "', ";
        $sql .= "'" . $n_lots . "', ";
        $sql .= "'" . $designation_lot . "', ";
        $sql .= "'" . $candidat_statut . "', ";
        $sql .= "'" . $F_engagement . "', ";
        $sql .= "'" . $adresse_internet . "', ";
        $sql .= "'" . $renseignement_adresse . "', ";
        $sql .= $dc2;
        $sql .= ")";

        dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);

        if ($resql === false) {
            $this->error = $this->db->lasterror() . " sql=" . $sql;
            $this->db->rollback();
            return -2;
        }

        // triggers
        if (!$notrigger) {
            include_once DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php";
            $interface = new Interfaces($this->db);
            $result = $interface->run_triggers('LINEDELEGATION_INSERT', $this, $user, $langs, $conf);
            if ($result < 0) {
                $this->error = $langs->trans('ErrorCallingTrigger');
                $this->db->rollback();
                return -1;
            }
        }

        $this->db->commit();

        // récupérer rowid inséré (compatible Dolibarr)
        $this->rowid = (int)$this->db->last_insert_id(MAIN_DB_PREFIX . "DC1");
        return 1;
    }

    /**
     * Update an existing line
     *
     * @param int $notrigger
     * @return mixed $this if ok (consistent with original pattern), <0 si erreur
     */
    public function update($notrigger = 0)
    {
        global $user, $langs, $conf;

        $this->rowid = (int)$this->rowid;
        if ($this->rowid <= 0) {
            $this->error = $langs->trans('DC1LineDoesNotExist');
            return -1;
        }

        // sanitize values
        $assign = [];
        $assignMap = [
            'id_acheteur' => (int)$this->id_acheteur,
            'objet_consultation' => $this->db->escape(trim((string)$this->objet_consultation)),
            'ref_consultation' => $this->db->escape(trim((string)$this->ref_consultation)),
            'objet_candidature' => (int)$this->objet_candidature,
            'n_lots' => $this->db->escape(trim((string)$this->n_lots)),
            'designation_lot' => $this->db->escape(trim((string)$this->designation_lot)),
            'candidat_statut' => (int)$this->candidat_statut,
            'F_engagement' => (int)$this->F_engagement,
            'adresse_internet' => $this->db->escape(trim((string)$this->adresse_internet)),
            'renseignement_adresse' => $this->db->escape(trim((string)$this->renseignement_adresse)),
            'dc2' => (int)$this->dc2,
        ];

        // Si un seul champ est ciblé, on ne met à jour que celui-ci
        if (!empty($this->field) && array_key_exists($this->field, $assignMap)) {
            $assign[$this->field] = $assignMap[$this->field];
        } else {
            $assign = $assignMap;
        }

        if (empty($assign)) {
            $this->error = 'NoFieldToUpdate';
            return -1;
        }

        $this->db->begin();

        // build set clause
        $sets = [];
        foreach ($assign as $col => $val) {
            if (is_int($val) || is_numeric($val)) {
                $sets[] = "`$col` = " . ((int)$val);
            } else {
                $sets[] = "`$col` = '" . $this->db->escape($val) . "'";
            }
        }

        $sql = "UPDATE " . MAIN_DB_PREFIX . "DC1 SET " . implode(', ', $sets) . " WHERE rowid = " . $this->rowid;

        dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql === false) {
            $this->error = $this->db->lasterror() . " sql=" . $sql;
            $this->db->rollback();
            return -2;
        }

        // triggers
        if (!$notrigger) {
            include_once DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php";
            $interface = new Interfaces($this->db);
            $result = $interface->run_triggers('LINEDC1_UPDATE', $this, $user, $langs, $conf);
            if ($result < 0) {
                $this->error = $langs->trans('ErrorCallingTrigger');
                $this->db->rollback();
                return -1;
            }
        }

        $this->db->commit();
        return $this;
    }
}
?>
