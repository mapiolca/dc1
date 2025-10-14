<?php 

 /* Copyright (C) 2018      Pierre Ardoin         <pierre.ardoin@gmail.com>

*/
	$object->fetch_projet();

	$object->fetch_thirdparty();

	$details_lines = array();
	if ($conf->dc1->enabled)
	{
		dol_include_once("/dc1/class/detailprojet.class.php");

		$projectid = $object->fetch_projet();

		$details = new Details($this->db);

		if ($projectid > 0)
		{
			$result = $object->project->id;

			if ($result > 0)
			{
				$details->fetch();
				$details_lines = $details->lines;
				foreach ($details_lines as $details_line)
				{
					$index++;
					$details_line->type_mou ;
					$details_line->ref_chantier ;
					$details_line->adresse_chantier ;
					$details_line->nature_travaux ;
					$details_line->fk_moe ;
					$details_line->n_lot ;
					$details_line->libelle_lot ;
					$details_line->marche_defense ;
					$details_line->rg_sstt ;
				}
				
				if ($object->element != 'project')// || $object->type != 5)
				{
					$error = true;
					$message = $langs->trans('NotASupplierOrder');
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
	}

	$Societe = '

	'.$conf->global->MAIN_INFO_SOCIETE_NOM.'<br>
	'.$conf->global->MAIN_INFO_SOCIETE_ADDRESS.'<br>
	'.$conf->global->MAIN_INFO_SOCIETE_ZIP.' '.$conf->global->MAIN_INFO_SOCIETE_TOWN.'';

	$SIREN_Societe = '

	N° SIREN/CIF : '.$conf->global->MAIN_INFO_SIREN.'<br>
	N° TVA Intracommunautaire : '.$conf->global->MAIN_INFO_TVAINTRA.'<br>
	Code APE : '.$conf->global->MAIN_INFO_APE.'';

	$Forme_Juridique_Societe = getFormeJuridiqueLabel($conf->global->MAIN_INFO_SOCIETE_FORME_JURIDIQUE);

	$Client = '

	'.$object->thirdparty->name.'<br>
	'.$object->thirdparty->address.'<br>
	'.$object->thirdparty->zip.' '.$object->thirdparty->town.'';

	$SIREN_SousTraitant = '

	N° SIREN/CIF : '.$object->thirdparty->idprof1.''.$outputlangs->convToOutputCharset($object->thirdparty->cif).'<br>
	N° TVA Intracommunautaire : '.$object->thirdparty->tva_intra.'<br>
	Code APE : '.$object->thirdparty->idprof3.'';

	$Forme_Juridique_SousTraitant = getFormeJuridiqueLabel($object->thirdparty->forme_juridique_code) ;

	$Representant_SousTraitant = ''.$object->thirdparty->array_options['options_lmdb_representant'].', '.$object->thirdparty->array_options['options_lmdb_qualite_representant'].'';

	$Travaux = 'Travaux relatifs à '.$details_line->nature_travaux ; 



?>