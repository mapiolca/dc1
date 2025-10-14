<?php
/* Copyright (C) 2018-2019      Pierre Ardoin
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

require_once DOL_DOCUMENT_ROOT ."/core/class/commonobject.class.php";

require_once(DOL_DOCUMENT_ROOT ."/core/lib/functions.lib.php");
require_once(DOL_DOCUMENT_ROOT ."/core/lib/functions2.lib.php");

require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';


/* Liste des variables
    `rowid`             int(11) AUTO_INCREMENT,
    `fk_object`             int(11)  NOT NULL,
    `fk_element`             VARCHAR(255)  NOT NULL,  
    `type_mou`          int(11) NOT NULL,
    `ref_chantier`          VARCHAR(255)  NOT NULL,   
    `adresse_chantier`         TEXT  NOT NULL,  
    `nature_travaux`          TEXT NOT NULL,
    `fk_moe`            int(11)  NOT NULL,
    `n_lot`             VARCHAR(255)  NOT NULL,
    `libelle_lot`          VARCHAR(255) NOT NULL,
    `marche_defense`            int(11)  NOT NULL,
    `rg_sstt`          VARCHAR(255) NOT NULL,
*/

/**
 *      \class      DetailsLines
 *      \brief      Class to manage margin
 */
class Details extends CommonObject
{
	var $db;
	var $error;
	var $element = 'Details';
	var $table_element = '';
	var $table_element_line = 'Details';
	var $fk_element = '';
	var $ismultientitymanaged = 0;	// 0=No test on entity, 1=Test with field entity, 2=Test with link by societe

	var $lines = array();
    var $line;

   /**
	*  \brief  Constructeur de la classe
	*  @param  DB          handler acces base de donnees
	*/
	function __construct($db)
	{
		$this->db = $db;
	}

   /**
	*  \brief  Execute action
	*  @param  action      action to execute
    *  @param  args        arguments array
    *  @return int         <0 if KO, >0 if OK
	*/
    function call($action, $args)
    {
        global $langs;


        if (empty($action))
        {
            return 0;
        }

        if (method_exists($this, $action))
        {
            $result = call_user_func_array(array($this, $action), $args);

            return $result;
        }
        else
        {
            return 0;
        }
    }

	/**
	 * Fetch object lines from database
	 *
	 * @return 	int		<0 if KO, >0 if OK
	 */
	function fetch()
	{
	    global $conf, $langs, $object;

        if ($object->project->id != 0) {
            $lineid = $object->project->id;
        }else{
            $lineid = $object->id;
        }
	
        $this->lines = array();

		$sql = "SELECT *";
        $sql.= " FROM ".MAIN_DB_PREFIX."projet_detail WHERE `fk_object` = ".$lineid;

		dol_syslog("Details::fetch sql=".$sql, LOG_DEBUG);

		$result = $this->db->query($sql);

		if ($result > 0)
		{
            $num = $this->db->num_rows($result);
        	$i = 0 ;

			if ($num == 1)
            {
                while ($i < $num)
                {
                    $obj = $this->db->fetch_object($result);


                    $this->lines[$i] = $obj;
                    $this->lines[$i]->type_mou = $obj->type_mou ? $obj->type_mou : 0;
                    $this->lines[$i]->ref_chantier = trim($obj->ref_chantier);
                    $this->lines[$i]->adresse_chantier = trim($obj->adresse_chantier);
                    $this->lines[$i]->nature_travaux =  trim($obj->nature_travaux);
                    $this->lines[$i]->fk_moe = $obj->fk_moe ? $obj->fk_moe : 0;
                    $this->lines[$i]->n_lot =  trim($obj->n_lot);
                    $this->lines[$i]->libelle_lot =  trim($obj->libelle_lot);
                    $this->lines[$i]->marche_defense = $obj->marche_defense ? $obj->marche_defense : 0;
                    $this->lines[$i]->rg_sstt =  trim($obj->rg_sstt);

                    $i++;
                }
            } 
            else
            {

                $this->line  = new DetailsLine($this->db);
            
                $this->line->fk_object = $object->id;
                $this->line->fk_element = $object->element;
                $this->line->type_mou = "0";
                $this->line->ref_chantier = "";
                $this->line->adresse_chantier = "";
                $this->line->nature_travaux =  "";
                $this->line->n_lot =  "";
                $this->line->libelle_lot =  "";
                $this->line->marche_defense = "0";
                $this->line->rg_sstt =  "0";
                
                $result = $this->line->insert();

                print $db;

                $this->lines = array();

                $sql = "SELECT *";
                $sql.= " FROM ".MAIN_DB_PREFIX."projet_detail WHERE `fk_object` = ".$object->id;

                dol_syslog("Details::fetch sql=".$sql, LOG_DEBUG);

                $result = $this->db->query($sql);
                
                if ($result > 0)
                {
                    $num = $this->db->num_rows($result);
                    $i = 0 ;

                    if ($num == 1)
                    {
                        while ($i < $num)
                        {
                            $obj = $this->db->fetch_object($result);

                            $this->lines[$i] = $obj;

                            $this->lines[$i]->type_mou = $obj->type_mou ? $obj->type_mou : 0;
                            $this->lines[$i]->ref_chantier = trim($obj->ref_chantier);
                            $this->lines[$i]->adresse_chantier = trim($obj->adresse_chantier);
                            $this->lines[$i]->nature_travaux =  trim($obj->nature_travaux);
                            $this->lines[$i]->fk_moe = $obj->fk_moe ? $obj->fk_moe : 0;
                            $this->lines[$i]->n_lot =  trim($obj->n_lot);
                            $this->lines[$i]->libelle_lot =  trim($obj->libelle_lot);
                            $this->lines[$i]->marche_defense = $obj->marche_defense ? $obj->marche_defense : 0;
                            $this->lines[$i]->rg_sstt =  trim($obj->rg_sstt);

                            $i++;
                        }
                    }
                }
            }  
            return 1;   
        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            return -1;
        }

	}

    /**
     *  \brief Update a line 
     *
     *  @param     user             User who adds
     *  @return    int              <0 if KO, id the line added if OK
     */
    function updateline($user)
    {
        global $langs, $conf, $object;

		$lineid = GETPOST('lineid') ? GETPOST('lineid') : 0;
        $field = GETPOST('field');

        $type_mou =  GETPOST('type_mou');
        $ref_chantier = GETPOST('ref_chantier');
        $adresse_chantier = GETPOST('adresse_chantier');
        $nature_travaux = GETPOST('nature_travaux');
        $fk_moe = GETPOST('fk_moe');
        $n_lot = GETPOST('n_lot');
        $libelle_lot = GETPOST('libelle_lot');
        $marche_defense = GETPOST('marche_defense');
        $rg_sstt =  GETPOST('rg_sstt');

		$line = new DetailsLine($this->db);
		
		$result = $line->fetch($lineid);
		
		if ($result)
		{
            $line->field = $field;

            $line->type_mou = $type_mou ;
            $line->ref_chantier = $ref_chantier ;
            $line->adresse_chantier = $adresse_chantier ;
            $line->nature_travaux = $nature_travaux ;
            $line->fk_moe = $fk_moe ;
            $line->n_lot = $n_lot ;
            $line->libelle_lot = $libelle_lot ;
            $line->marche_defense = $marche_defense ;
            $line->rg_sstt = $rg_sstt ;
	
			$result = $line->update();

			if ($result > 0)
			{		
				$this->fetch();
			
				$this->error = $langs->trans('DetailsLineUpdated');		
				return $line->rowid;
			}
			else
			{
				$this->error = $this->line->error;

				return -2;
			}
		}
		else
		{
			$this->error = $langs->trans('DetailsLineDoesNotExist');
			return 0;
		}
	        
    }
        	
}

/**
 *	\class      	DetailsLine
 *	\brief      	Class to manage margins
 *	\remarks		Uses lines of llx_projet_detail tables
 */
class DetailsLine
{
    var $db;
    var $error;

    var $oldline;


	var $rowid;
	var $fk_object;
	var $fk_element;

    var $type_mou ;
    var $ref_chantier ;
    var $adresse_chantier ;
    var $nature_travaux ;
    var $fk_moe ;
    var $n_lot ;
    var $libelle_lot ;
    var $marche_defense;
    var $rg_sstt ;

   /**
	*  \brief  Constructeur de la classe
	*  @param  DB          handler acces base de donnees
	*/
    function DetailsLine($DB)
    {
        $this->db = $DB;
    }


   /**
     *	\brief     	Insert line in database
     *	@param      notrigger		1 no triggers
     *	@return		int				<0 if KO, >0 if OK
     */
    function fetch($lineid = 0)
    {
        global $langs, $user, $conf;

        if ($projectid > 0) {
            $lineid = $projectid;
        }

        $sql = "SELECT *";
		$sql.= " FROM ".MAIN_DB_PREFIX."projet_detail";
        $sql.= " WHERE `rowid` = ".$lineid;

        dol_syslog("DetailsLine::fetch sql=".$sql);

        $result = $this->db->query($sql);

        if ($result)
        {

            $num = $this->db->num_rows($result);

            if ($num)
            {
                $obj = $this->db->fetch_object($result);

				$this->rowid		= $obj->rowid ? $obj->rowid : 0;
				$this->fk_object	= $obj->fk_objectdet ? $obj->fk_objectdet : 0;
				$this->fk_element	= $obj->fk_element;

                $this->field = $field;
				
                $this->line            = $obj;
                $this->line->type_mou = $obj->type_mou ? $obj->type_mou : 0;
                $this->line->ref_chantier = trim($obj->ref_chantier);
                $this->line->adresse_chantier = trim($obj->adresse_chantier);
                $this->line->nature_travaux =  trim($obj->nature_travaux);
                $this->line->fk_moe = $obj->fk_moe ? $obj->fk_moe : 0;
                $this->line->n_lot =  trim($obj->n_lot);
                $this->line->libelle_lot =  trim($obj->libelle_lot);
                $this->line->marche_defense = $obj->marche_defense ? $obj->marche_defense : 0;
                $this->line->rg_sstt =  trim($obj->rg_sstt);
				
                //$this->db->free($result);
				
				return $this->rowid;
            }
            else
            {
                $this->error = $langs->trans('DetailsLineDoesNotExist');
                return -1;
            }

        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;

            return -1;
        }
    }
	
    /**
     *	\brief     	Insert line in database
     *	@param      notrigger		1 no triggers
     *	@return		int				<0 if KO, >0 if OK
     */
    function insert($notrigger = 0)
    {
        global $langs, $user, $conf;
		
        $this->db->begin();
        
        //
        $sql = "INSERT INTO ".MAIN_DB_PREFIX."projet_detail";
        $sql.= " (`fk_object`, `fk_element`, `type_mou`, `ref_chantier`, `adresse_chantier`, `nature_travaux`, `n_lot`, `libelle_lot`, `marche_defense`,`rg_sstt`)";
        $sql.= " VALUES (".$this->fk_object.",";
        $sql.= " '".$this->fk_element."', ";
        $sql.= " '".$this->type_mou."', ";
        $sql.= " '".$this->ref_chantier."', ";
        $sql.= " '".$this->adresse_chantier."', ";
        $sql.= " '".$this->nature_travaux."', ";
        $sql.= " '".$this->n_lot."', ";
        $sql.= " '".$this->libelle_lot."', ";
        $sql.= " '".$this->marche_defense."', ";
        $sql.= " '".$this->rg_sstt."'";
        $sql.= ')';

        dol_syslog("DetailsLine::insert sql=".$sql);

        $resql = $this->db->query($sql);

      if ($resql)
        {			

            if (! $notrigger)
            {
                // Appel des triggers
                include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
                $interface = new Interfaces($this->db);
                $result = $interface->run_triggers('LINEDETAILS_INSERT', $this, $user ,$langs, $conf);
                if ($result < 0) {
                    $this->error = $langs->trans('ErrorCallingTrigger');
                    $this->db->rollback();
                    return -1;
                }
                // Fin appel triggers
            }

            $this->db->commit();

            return 1;

        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            $this->db->rollback();

            return -2;
        }


    }

    /**
     *	\brief     	Update line in database
     *	@param      notrigger		1 no triggers
     *	@return		int				<0 if KO, >0 if OK
     */
    function update($notrigger = 0)
    {
        global $langs, $user, $conf;


		// Clean parameters

        $this->type_mou = $this->type_mou ? $this->type_mou : 0;
        $this->ref_chantier = trim($this->ref_chantier);
        $this->B = trim($this->B);
        $this->type_mou = $this->type_mou ? $this->type_mou : 0;
        $this->ref_chantier = trim($this->ref_chantier);
        $this->adresse_chantier = trim($this->adresse_chantier);
        $this->fk_moe = $this->fk_moe ? $this->fk_moe : 0;
        $this->n_lot =  trim($this->n_lot);
        $this->libelle_lot =  trim($this->libelle_lot);
        $this->marche_defense = $this->marche_defense ? $this->marche_defense : 0;
        $this->rg_sstt =  trim($this->rg_sstt);
				
        $this->db->begin();

        //
        $sql = "UPDATE ".MAIN_DB_PREFIX."projet_detail";
        $sql.= " SET rowid = '".$this->rowid."'";
        if ($this->field == "type_mou") {
            $sql.= ", `type_mou` = '".$this->type_mou."'";
        }
        if ($this->field == "ref_chantier") {
            $sql.= ", `ref_chantier` = '".$this->ref_chantier."'";
        }
        if ($this->field == "adresse_chantier") {
		  $sql.= ", `adresse_chantier` = '".$this->adresse_chantier."'";
        }
        if ($this->field == "nature_travaux") {
            $sql.= ", `nature_travaux` = '".$this->nature_travaux."'";
        }
        if ($this->field == "fk_moe") {
            $sql.= ", `fk_moe` = '".$this->fk_moe."'";
        }
        if ($this->field == "n_lot") {
            $sql.= ", `n_lot` = '".$this->n_lot."'";
        }
        if ($this->field == "libelle_lot") {
            $sql.= ", `libelle_lot` = '".$this->libelle_lot."'";
        }
        if ($this->field == "marche_defense") {
            $sql.= ", `marche_defense` = '".$this->marche_defense."'";
        }
        if ($this->field == "rg_sstt") {
            $sql.= ", `rg_sstt` = '".$this->rg_sstt."'";
        }
		$sql.= " WHERE rowid = '".$this->rowid."'";

        dol_syslog("DetailsLine::update sql=".$sql);

        $resql = $this->db->query($sql);

        if ($resql)
        {

            if (! $notrigger)
            {
                // Appel des triggers
                include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
                $interface = new Interfaces($this->db);
                $result = $interface->run_triggers('LINEDETAILS_UPDATE', $this, $user ,$langs, $conf);
                if ($result < 0) {
                    $this->error = $langs->trans('ErrorCallingTrigger');
                    $this->db->rollback();
                    return -1;
                }
                // Fin appel triggers
            }

            $this->db->commit();

            return $this->rowid;

        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            $this->db->rollback();

            return -2;
        }
    } 
    
}
?>
