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



require_once(DOL_DOCUMENT_ROOT ."/core/class/commonobject.class.php");
require_once(DOL_DOCUMENT_ROOT ."/core/lib/functions.lib.php");
require_once(DOL_DOCUMENT_ROOT ."/core/lib/functions2.lib.php");
require_once(DOL_DOCUMENT_ROOT.'/core/lib/price.lib.php');
require_once(DOL_DOCUMENT_ROOT."/core/lib/files.lib.php");
require_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/propale/modules_propale.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/propal.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.commande.class.php';


/**
 *      \class      DC1s
 *      \brief      Class to manage margin
 */
class DC1 extends CommonObject
{
	var $db;
	var $error;
	var $element = 'DC1';
	var $table_element = '';
	var $table_element_line = 'DC1';
    var $table_element_line2 = 'DC1_groupement';
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
			
        $this->lines = array();

		$sql = "SELECT *";
        $sql.= " FROM ".MAIN_DB_PREFIX."DC1 WHERE `fk_object` = ".$object->id;

		dol_syslog("DC1::fetch sql=".$sql, LOG_DEBUG);

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


                    $this->lines[$i]            = $obj;
                    $this->lines[$i]->id_acheteur = $obj->id_acheteur ? $obj->id_acheteur : 0; 
                    $this->lines[$i]->objet_consultation = trim($obj->objet_consultation); 
                    $this->lines[$i]->ref_consultation = trim($obj->ref_consultation); 
                    $this->lines[$i]->objet_candidature = $obj->objet_candidature ? $obj->objet_candidature : 0;
                    $this->lines[$i]->n_lots = trim($obj->n_lots); 
                    $this->lines[$i]->designation_lot = trim($obj->designation_lot); 
                    $this->lines[$i]->candidat_statut = $obj->candidat_statut ? $obj->candidat_statut : 0;
                    $this->lines[$i]->F_engagement = $obj->F_engagement ? $obj->F_engagement : 0;
                    $this->lines[$i]->adresse_internet = trim($obj->adresse_internet); 
                    $this->lines[$i]->renseignement_adresse = trim($obj->renseignement_adresse); 
                    $this->lines[$i]->dc2 = $obj->dc2 ? $obj->dc2 : 0;

                    $i++;
                }

            } 
            else
            {

                $this->line  = new DC1Line($this->db);
            
                $this->line->fk_object = $object->id;
                $this->line->fk_element = $object->element; 
                $this->line->id_acheteur = $object->socid;    
                $this->line->objet_consultation = "";
                $this->line->ref_consultation = ""; 
                $this->line->objet_candidature = "0";
                $this->line->n_lots = ""; 
                $this->line->designation_lot = ""; 
                $this->line->candidat_statut = "0"; 
                $this->line->F_engagement = "0"; 
                $this->line->adresse_internet = ""; 
                $this->line->renseignement_adresse = ""; 
                $this->line->dc2 = "0"; 
                
                $result = $this->line->insert();

                $this->lines = array();

                $sql = "SELECT *";
                $sql.= " FROM ".MAIN_DB_PREFIX."DC1 WHERE `fk_object` = ".$object->id;

                dol_syslog("DC1::fetch sql=".$sql, LOG_DEBUG);

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


                            $this->lines[$i]            = $obj;
                            $this->lines[$i]->id_acheteur = $obj->id_acheteur ? $obj->id_acheteur : 0; 
                            $this->lines[$i]->objet_consultation = trim($obj->objet_consultation);  
                            $this->lines[$i]->ref_consultation = trim($obj->ref_consultation); 
                            $this->lines[$i]->objet_candidature = $obj->objet_candidature ? $obj->objet_candidature : 0;
                            $this->lines[$i]->n_lots = trim($obj->n_lots); 
                            $this->lines[$i]->designation_lot = trim($obj->designation_lot); 
                            $this->lines[$i]->candidat_statut = $obj->candidat_statut ? $obj->candidat_statut : 0;
                            $this->lines[$i]->F_engagement = $obj->F_engagement ? $obj->F_engagement : 0;
                            $this->lines[$i]->adresse_internet = trim($obj->adresse_internet); 
                            $this->lines[$i]->renseignement_adresse = trim($obj->renseignement_adresse); 
                            $this->lines[$i]->dc2 = $obj->dc2 ? $obj->dc2 : 0;

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
  
        $id_acheteur = GETPOST('id_acheteur');   
        $objet_consultation = GETPOST('objet_consultation');
        $ref_consultation = GETPOST('ref_consultation');
        $objet_candidature = GETPOST('objet_candidature');
        $n_lots = GETPOST('n_lots');
        $designation_lot = GETPOST('designation_lot');
        $candidat_statut = GETPOST('candidat_statut');
        $F_engagement = GETPOST('F_engagement');
        $adresse_internet = GETPOST('adresse_internet');
        $renseignement_adresse = GETPOST('renseignement_adresse');
        $dc2 = GETPOST('dc2');

		$line = new DC1Line($this->db);
		
		$result = $line->fetch($lineid);
		
		if ($result)
		{
            $line->field = $field;
            $line->id_acheteur = $object->socid;    
            $line->objet_consultation = $objet_consultation;
            $line->ref_consultation = $ref_consultation; 
            $line->objet_candidature = $objet_candidature;
            $line->n_lots = $n_lots; 
            $line->designation_lot = $designation_lot; 
            $line->candidat_statut = $candidat_statut;
            $line->F_engagement = $F_engagement;
            $line->adresse_internet = $adresse_internet; 
            $line->renseignement_adresse = $renseignement_adresse; 
            $line->dc2 = $dc2;
	
			$result = $line->update();

			if ($result > 0)
			{		
				$this->fetch();
			
				$this->error = $langs->trans('DC1LineUpdated');		
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
			$this->error = $langs->trans('DC1LineDoesNotExist');
			return 0;
		}
	        
    }
        	
}

/**
 *	\class      	DC1Line
 *	\brief      	Class to manage margins
 *	\remarks		Uses lines of llx_DC1 tables
 */
class DC1Line
{
    var $db;
    var $error;

    var $oldline;


	var $rowid;
	var $fk_object;
	var $fk_element;

	var $id_acheteur;    
    var $objet_consultation;
    var $ref_consultation;
    var $objet_candidature;
    var $n_lots;
    var $designation_lot;
    var $candidat_statut;
    var $F_engagement;
    var $adresse_internet;
    var $renseignement_adresse;
    var $dc2;


   /**
	*  \brief  Constructeur de la classe
	*  @param  DB          handler acces base de donnees
	*/
    function __construct($DB)
    {
        $this->db = $DB;

    }

/**
     * 	Delete line in database
     *
     *	@return	 int  <0 si ko, >0 si ok
     */
 /*
    function delete()
    {
        global $conf, $user, $langs;

		$error=0;

        $sql = "DELETE FROM ".MAIN_DB_PREFIX."DC1 WHERE rowid=".$this->rowid;

        dol_syslog("DC1Line::delete sql=".$sql);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            // Appel des triggers
            include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
            $interface=new Interfaces($this->db);
            $result=$interface->run_triggers('LINEDC1_DELETE',$this,$user,$langs,$conf);
            if ($result < 0) { $error++; $this->errors=$interface->errors; }
            // Fin appel triggers

            return 1;
        }
        else
        {
            $this->error=$this->db->lasterror();
            dol_syslog("DC1Line::delete ".$this->error, LOG_ERR);
            return -1;
        }
    }
*/
   /**
     *	\brief     	Insert line in database
     *	@param      notrigger		1 no triggers
     *	@return		int				<0 if KO, >0 if OK
     */
    function fetch($lineid = 0)
    {
        global $langs, $user, $conf;

        
        $sql = "SELECT *";
		$sql.= " FROM ".MAIN_DB_PREFIX."DC1";
        $sql.= " WHERE `rowid` = ".$lineid;

        dol_syslog("DC1Line::fetch sql=".$sql);

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
				
                $this->line->id_acheteur = $object->socid;    
                $this->line->objet_consultation = trim($obj->objet_consultation); 
                $this->line->ref_consultation = trim($obj->ref_consultation); 
                $this->line->objet_candidature = $obj->objet_candidature ? $obj->objet_candidature : 0;
                $this->line->n_lots = trim($obj->n_lots); 
                $this->line->designation_lot = trim($obj->designation_lot); 
                $this->line->candidat_statut = $obj->candidat_statut ? $obj->candidat_statut : 0;
                $this->line->F_engagement = $obj->F_engagement ? $obj->F_engagement : 0;
                $this->line->adresse_internet = trim($obj->adresse_internet); 
                $this->line->renseignement_adresse = trim($obj->renseignement_adresse); 
                $this->line->dc2 = $obj->dc2 ? $obj->dc2 : 0;
				
                //$this->db->free($result);
				
				return $this->rowid;
            }
            else
            {
                $this->error = $langs->trans('DC1LineDoesNotExist');
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
        $sql = "INSERT INTO ".MAIN_DB_PREFIX."DC1";
        $sql.= " (`fk_object`, `fk_element`, `id_acheteur`, `objet_consultation`, `ref_consultation`, `objet_candidature` ,`n_lots`, `designation_lot`, `candidat_statut`,`F_engagement`, `adresse_internet`, `renseignement_adresse`, `dc2`)";
        $sql.= " VALUES (".$this->fk_object.",";
        $sql.= " '".$this->fk_element."', ";
        $sql.= " '".$this->id_acheteur."', ";
        $sql.= " '".$this->objet_consultation."', ";
        $sql.= " '".$this->ref_consultation."', ";
        $sql.= " '".$this->objet_candidature."', ";
        $sql.= " '".$this->n_lots."', ";
        $sql.= " '".$this->designation_lot."', ";
        $sql.= " '".$this->candidat_statut."', ";
        $sql.= " '".$this->F_engagement."', ";
        $sql.= " '".$this->adresse_internet."', ";
        $sql.= " '".$this->renseignement_adresse."', ";
        $sql.= " ".$this->dc2;
        $sql.= ')';

        dol_syslog("DC1Line::insert sql=".$sql);

        $resql = $this->db->query($sql);
        
      if ($resql)
        {			
            //$this->update_ndfp_tms();

            if (! $notrigger)
            {
                // Appel des triggers
                include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
                $interface = new Interfaces($this->db);
                $result = $interface->run_triggers('LINEDELEGATION_INSERT', $this, $user ,$langs, $conf);
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
                
        $this->id_acheteur = $this->id_acheteur; 
        $this->objet_consultation = trim($this->objet_consultation); 
        $this->ref_consultation = trim($this->ref_consultation); 
        $this->objet_candidature = $this->objet_candidature ? $this->objet_candidature : 0;
        $this->n_lots = $this->n_lots; 
        $this->designation_lot = trim($this->designation_lot); 
        $this->candidat_statut = $this->candidat_statut ? $this->candidat_statut : 0;
        $this->F_engagement = $this->F_engagement ? $this->F_engagement : 0;
        $this->adresse_internet = trim($this->adresse_internet); 
        $this->renseignement_adresse = trim($this->renseignement_adresse); 
        $this->dc2 = $this->dc2 ? $this->dc2 : 0;
				
        $this->db->begin();
	
        //
        $sql = "UPDATE ".MAIN_DB_PREFIX."DC1";
        $sql.= " SET rowid = '".$this->rowid."'";
        if ($this->field == "id_acheteur") {
            $sql.= ", `id_acheteur` = '".$this->id_acheteur."'";
        }
        if ($this->field == "objet_consultation") {
            $sql.= ", `objet_consultation` = '".$this->objet_consultation."'";
        }
        if ($this->field == "ref_consultation") {
		  $sql.= ", `ref_consultation` = '".$this->ref_consultation."'";
        }
        if ($this->field == "objet_candidature") {
            $sql.= ", `objet_candidature` = '".$this->objet_candidature."'";
        }
        if ($this->field == "n_lots") {
            $sql.= ", `n_lots` = '".$this->n_lots."'";
        }
        if ($this->field == "designation_lot") {
            $sql.= ", `designation_lot` = '".$this->designation_lot."'";
        }
        if ($this->field == "candidat_statut") {
            $sql.= ", `candidat_statut` = '".$this->candidat_statut."'";
        }
        if ($this->field == "F_engagement") {
            $sql.= ", `F_engagement` = '".$this->F_engagement."'";
        }
        if ($this->field == "adresse_internet") {
            $sql.= ", `adresse_internet` = '".$this->adresse_internet."'";
        }
        if ($this->field == "renseignement_adresse") {
            $sql.= ", `renseignement_adresse` = '".$this->renseignement_adresse."'";
        }
        if ($this->field == "dc2") {
            $sql.= ", `dc2` = '".$this->dc2."'";
        }
		$sql.= " WHERE rowid = '".$this->rowid."'";

        dol_syslog("DC1Line::update sql=".$sql);

        $resql = $this->db->query($sql);
        
        if ($resql)
        {

            if (! $notrigger)
            {
                // Appel des triggers
                include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
                $interface = new Interfaces($this->db);
                $result = $interface->run_triggers('LINEDC1_UPDATE', $this, $user ,$langs, $conf);
                if ($result < 0) {
                    $this->error = $langs->trans('ErrorCallingTrigger');
                    $this->db->rollback();
                    return -1;
                }
                // Fin appel triggers
            }

            $this->db->commit();

            return $this;

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
