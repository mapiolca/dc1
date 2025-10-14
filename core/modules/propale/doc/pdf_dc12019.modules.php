<?php
/* Copyright (C) 2004-2014 Laurent Destailleur   <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2011 Regis Houssin         <regis.houssin@capnetworks.com>
 * Copyright (C) 2007      Franky Van Liedekerke <franky.van.liedekerke@telenet.be>
 * Copyright (C) 2010-2014 Juanjo Menent         <jmenent@2byte.es>
 * Copyright (C) 2015       Marcos García        <marcosgdf@gmail.com>
 * Copyright (C) 2017      Ferran Marcet         <fmarcet@2byte.es>
 * Copyright (C) 2018      Pierre Ardoin         <pierre.ardoin@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * or see http://www.gnu.org/
 */

/**
 *	\file       htdocs/core/modules/supplier_order/pdf/pdf_DC42017modules.php
 *	\ingroup    fournisseur
 *	\brief      File of class to generate suppliers orders from Délégation INPOSE model
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/propale/modules_propale.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';

require_once DOL_DOCUMENT_ROOT.'/custom/dc1/class/dc1.class.php';



/**
 *	Class to generate the supplier orders with the Délégation INPOSE model
 */
class pdf_DC12019 extends ModelePDFPropales
{
    	/**
	 * @var DoliDB Database handler
	 */
	public $db;

	/**
	 * @var string model name
	 */
	public $name;

	/**
	 * @var string model description (short text)
	 */
	public $description;

	/**
	 * @var int    Save the name of generated file as the main doc when generating a doc with this template
	 */
	public $update_main_doc_field;

	/**
	 * @var string document type
	 */
	public $type;

	/**
	 * Dolibarr version of the loaded document
	 * @var string Version, possible values are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'''|'development'|'dolibarr'|'experimental'
	 */
	public $version = 'dolibarr';

	/**
	 * @var array<string,array{rank:int,width:float|false,status:bool|int<0,1>,border-left?:bool,title:array{textkey:string,label?:string,align?:string,padding?:array{0:float,1:float,2:float,3:float}},content?:array{align?:string,padding?:array{0:float,1:float,2:float,3:float}}}>	Array of document table columns
	 */
	public $cols;

    var $page_largeur;
    var $page_hauteur;
    var $format;
	var $marge_gauche;
	var	$marge_droite;
	var	$marge_haute;
	var	$marge_basse;

	var $emetteur;	// Objet societe qui emet


	/**
	 *	Constructor
	 *
	 *  @param	DoliDB		$db      	Database handler
	 */
	function __construct($db)
	{
		global $conf,$langs,$mysoc, $object;

		$langs->load("main");
		$langs->load("bills");
		$langs->load("btp@btp");
		$langs->load("dc1@dc1");

		$this->db = $db;
		$this->name = "DC1 2019";
		$this->description = $langs->trans('PDFDC1LMDBDescription2019');

		// Dimension page pour format A4
		$this->type = 'pdf';
		$formatarray=pdf_getFormat();
		$this->page_largeur = $formatarray['width'];
		$this->page_hauteur = $formatarray['height'];
		$this->format = array($this->page_largeur,$this->page_hauteur);
		$this->marge_gauche=isset($conf->global->MAIN_PDF_MARGIN_LEFT)?$conf->global->MAIN_PDF_MARGIN_LEFT:10;
		$this->marge_droite=isset($conf->global->MAIN_PDF_MARGIN_RIGHT)?$conf->global->MAIN_PDF_MARGIN_RIGHT:10;
		$this->marge_haute =isset($conf->global->MAIN_PDF_MARGIN_TOP)?$conf->global->MAIN_PDF_MARGIN_TOP:10;
		$this->marge_basse =isset($conf->global->MAIN_PDF_MARGIN_BOTTOM)?$conf->global->MAIN_PDF_MARGIN_BOTTOM:10;

		$this->emetteur=$mysoc;
	}


    /**
     *  Function to build pdf onto disk
     *
     *  @param		CommandeFournisseur	$object				Id of object to generate
     *  @param		Translate			$outputlangs		Lang output object
     *  @param		string				$srctemplatepath	Full path of source filename for generator using a template file
     *  @param		int					$hidedetails		Do not show line details
     *  @param		int					$hidedesc			Do not show desc
     *  @param		int					$hideref			Do not show ref
     *  @return		int										1=OK, 0=KO
     */
	function write_file($object,$outputlangs='',$srctemplatepath='',$hidedetails=0,$hidedesc=0,$hideref=0)
	{
		global $user,$langs,$conf,$hookmanager,$mysoc;

		if (! is_object($outputlangs)) $outputlangs=$langs;
		// For backward compatibility with FPDF, force output charset to ISO, because FPDF expect text to be encoded in ISO
		if (! empty($conf->global->MAIN_USE_FPDF)) $outputlangs->charset_output='ISO-8859-1';

		$outputlangs->load("main");
		$outputlangs->load("dict");
		$outputlangs->load("companies");
		$outputlangs->load("bills");
		$outputlangs->load("products");
		$outputlangs->load("propal");


		if ($conf->propal->dir_output)
		{
			$object->fetch_thirdparty();

			$deja_regle = 0;
			$amount_credit_notes_included = 0;
			$amount_deposits_included = 0;
			//$amount_credit_notes_included = $object->getSumCreditNotesUsed();
            //$amount_deposits_included = $object->getSumDepositsUsed();

			// Definition of $dir and $file
			if ($object->specimen)
			{
				$dir = $conf->propal->dir_output;
				$file = $dir . "/SPECIMEN.pdf";
			}
			else
			{
				$objectref = dol_sanitizeFileName($object->ref);
				$dir = $conf->propal->dir_output . "/" . $objectref;
				$file = $dir . "/" . $objectref . " - Lettre de Candidature - DC1.pdf";
			}

			if (! file_exists($dir))
			{
				if (dol_mkdir($dir) < 0)
				{
					$this->error=$langs->transnoentities("ErrorCanNotCreateDir",$dir);
					return 0;
				}
			}

			if (file_exists($dir))
			{
				// Add pdfgeneration hook
				if (! is_object($hookmanager))
				{
					include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
					$hookmanager=new HookManager($this->db);
				}
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters=array('file'=>$file,'object'=>$object,'outputlangs'=>$outputlangs);
				global $action;
				$reshook=$hookmanager->executeHooks('beforePDFCreation',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks

				$nblignes = count($object->lines);

                $pdf=pdf_getInstance($this->format);
                $default_font_size = pdf_getPDFFontSize($outputlangs);	// Must be after pdf_getInstance
                $heightforinfotot = 50;	// Height reserved to output the info and total part
		        $heightforfreetext= (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT)?$conf->global->MAIN_PDF_FREETEXT_HEIGHT:5);	// Height reserved to output the free text on last page
	            $heightforfooter = $this->marge_basse + 8;	// Height reserved to output the footer (value include bottom margin)
                $pdf->SetAutoPageBreak(1,0);

                if (class_exists('TCPDF'))
                {
                    $pdf->setPrintHeader(false);
                    $pdf->setPrintFooter(false);
                }
                $pdf->SetFont(pdf_getPDFFont($outputlangs));
                // Set path to the background PDF File

				$pdf->Open();
				$pagenb=0;
				$pdf->SetDrawColor(128,128,128);

				$pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
				$pdf->SetSubject($outputlangs->transnoentities("CommercialProposal"));
				$pdf->SetCreator("Dolibarr ".DOL_VERSION);
				$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
				$pdf->SetKeyWords($outputlangs->convToOutputCharset($object->ref)." ".$outputlangs->transnoentities("CommercialProposal")." ".$outputlangs->convToOutputCharset($object->thirdparty->name));
				if (! empty($conf->global->MAIN_DISABLE_PDF_COMPRESSION)) $pdf->SetCompression(false);

				$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite);   // Left, Top, Right

				$dc1_lines = array();
				if ($conf->dc1->enabled)
				{
					dol_include_once("/dc1/class/dc1.class.php");

					$id = GETPOST('id', 'int');
					$dc1 = new DC1($this->db);

					if ($id > 0)
					{
						$result = $object->fetch($id);

						if ($result > 0)
						{
							$dc1->fetch();
							$dc1_lines = $dc1->lines;
							foreach ($dc1_lines as $dc1_line)
							{
								$index++;
								$dc1_line->id_acheteur;
								$dc1_line->objet_consultation;
					            $dc1_line->objet_candidature;
					            $dc1_line->n_lots;
					            $dc1_line->designation_lot;
					            $dc1_line->candidat_statut;
					            $dc1_line->F_engagement;
					            $dc1_line->adresse_internet;
					            $dc1_line->renseignement_adresse;
					            $dc1_line->dc2;
							}
							
							if ($object->element != 'propal')// || $object->type != 5)
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
					
				}
					
				


			// Page 1
				$pdf->AddPage();
				
                $pagecount = $pdf->setSourceFile(DOL_DOCUMENT_ROOT.'/custom/dc1/core/modules/propale/doc/DC1_2019/DC1-2019.pdf');
                $tplidx = $pdf->importPage(1);
                
				
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);

				include DOL_DOCUMENT_ROOT.'/custom/dc1/core/modules/propale/doc/DC1-2019/DC1-2019.php';

				$logo=$conf->mycompany->dir_output.'/logos/'.$this->emetteur->logo;

				$height=pdf_getHeightForLogo($logo);
				$pdf->Image($logo, 10, 10, "", 10);

				$this->_pagefoot($pdf,$object,$outputlangs,1);
				if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();

				//Contenu
					$pdf->SetFont('','B',10); // fixe la police, le type ( 'B' pour gras, 'I' pour italique, '' pour normal,...)
					$object->fetch_thirdparty();
					$Client = '

					'.$object->thirdparty->name.'<br>
					'.$object->thirdparty->address.'<br>
					'.$object->thirdparty->zip.' '.$object->thirdparty->town.'';

					//var_dump($object->thirdparty);

					$pdf->writeHTMLCell(150,4, 20, 170, dol_htmlentitiesbr($outputlangs->convToOutputCharset($Client)),0,1);

					$pdf->writeHTMLCell(150,4, 20, 224, dol_htmlentitiesbr($outputlangs->convToOutputCharset($dc1_line->objet_consultation)),0,1);	


					$pdf->writeHTMLCell(100,4, 100, 276.7, $outputlangs->convToOutputCharset($dc1_line->ref_consultation),0,1);


			// Page 2
				$pdf->AddPage();

				$tplidx = $pdf->importPage(2);
                if (! empty($tplidx)) $pdf->useTemplate($tplidx);

				$pdf->Image($logo, 10, 10, "", 10);
				
				// Pied de page
					$this->_pagefoot($pdf, $object, $outputlangs);
					if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();

				//Contenu
					
					$pdf->SetFont('','B',10); // fixe la police, le type ( 'B' pour gras, 'I' pour italique, '' pour normal,...)
					
				if ($dc1_line->objet_candidature == '1') {
					
					$pdf->writeHTMLCell(190,4, 25, 43, dol_htmlentitiesbr("X"),0,1);

				} elseif ($dc1_line->objet_candidature == '2'|| $dc1_line->objet_candidature == '4') {
					
					$pdf->writeHTMLCell(190,4, 25, 59.4, dol_htmlentitiesbr("X"),0,1);

					if ($dc1_line->objet_candidature == '2') {

						$pdf->writeHTMLCell(190,4, 51, 59.4, dol_htmlentitiesbr($dc1_line->n_lots),0,1);

					} elseif ($dc1_line->objet_candidature == '4'){
						$pdf->writeHTMLCell(190,4, 80, 59.4, dol_htmlentitiesbr($dc1_line->n_lots),0,1);
					}

					$pdf->writeHTMLCell(150,4, 20, 75, dol_htmlentitiesbr($outputlangs->convToOutputCharset($dc1_line->designation_lot)),0,1);

				}elseif ($dc1_line->objet_candidature == '3') {
					
					$pdf->writeHTMLCell(190,4, 25, 51.2, dol_htmlentitiesbr("X"),0,1);

				}

				$Societe = '

					'.$conf->global->MAIN_INFO_SOCIETE_NOM.'<br>
					'.$conf->global->MAIN_INFO_SOCIETE_ADDRESS.'<br>
					'.$conf->global->MAIN_INFO_SOCIETE_ZIP.' '.$conf->global->MAIN_INFO_SOCIETE_TOWN.'';

					$Forme_Juridique_Societe = getFormeJuridiqueLabel($conf->global->MAIN_INFO_SOCIETE_FORME_JURIDIQUE);

					$carac_emetteur .= pdf_build_address($outputlangs, $this->emetteur, $object->thirdparty);

					$SIREN_Societe = '

					N° SIRET : '.$conf->global->MAIN_INFO_SIRET.'<br>
					N° TVA Intracommunautaire : '.$conf->global->MAIN_INFO_TVAINTRA.'<br>
					Code APE : '.$conf->global->MAIN_INFO_APE.'<br>
					Forme juridique : '.$Forme_Juridique_Societe;


					if ($dc1_line->candidat_statut == '1') {
					
						$pdf->writeHTMLCell(190,4, 25, 100.5, dol_htmlentitiesbr("X"),0,1);
						$pdf->writeHTMLCell(190,4, 25, 130, dol_htmlentitiesbr($conf->global->MAIN_INFO_SOCIETE_NOM),0,1);
						$pdf->writeHTMLCell(190,4, 25, 145, dol_htmlentitiesbr($conf->global->MAIN_INFO_SOCIETE_ADDRESS),0,1);
						$pdf->writeHTMLCell(190,4, 25, 150, dol_htmlentitiesbr($conf->global->MAIN_INFO_SOCIETE_ZIP.' '.$conf->global->MAIN_INFO_SOCIETE_TOWN),0,1);
						$pdf->writeHTMLCell(190,4, 25, 162.5, dol_htmlentitiesbr($conf->global->MAIN_INFO_SOCIETE_MAIL),0,1);
						$pdf->writeHTMLCell(190,4, 25, 180, dol_htmlentitiesbr($langs->trans('Tel.').' : '.dol_print_phone($conf->global->MAIN_INFO_SOCIETE_TEL, $object->country_code, 0, 0, 1)),0,1);
						$pdf->writeHTMLCell(190,4, 25, 200, dol_htmlentitiesbr($conf->global->MAIN_INFO_SIRET),0,1);

						$pdf->SetFont('','B',8); // fixe la police, le type ( 'B' pour gras, 'I' pour italique, '' pour normal,...)
						//$pdf->writeHTMLCell(100,4, 25, 70, dol_htmlentitiesbr($SIREN_Societe),0,1);
						//$pdf->writeHTMLCell(100,4, 120, 65, dol_htmlentitiesbr($carac_emetteur),0,1);
						
						


					}elseif ($dc1_line->candidat_statut == '2') {
						
						$pdf->writeHTMLCell(190,4, 25, 86.5, dol_htmlentitiesbr("X"),0,1);
						$pdf->writeHTMLCell(190,4, 35, 95.65, dol_htmlentitiesbr("X"),0,1);
						$pdf->writeHTMLCell(190,4, 85, 117, dol_htmlentitiesbr("X"),0,1);

					}elseif ($dc1_line->candidat_statut == '3') {

						$pdf->writeHTMLCell(190,4, 25, 86.5, dol_htmlentitiesbr("X"),0,1);
						$pdf->writeHTMLCell(190,4, 35, 95.65, dol_htmlentitiesbr("X"),0,1);
						$pdf->writeHTMLCell(190,4, 35, 117, dol_htmlentitiesbr("X"),0,1);

					}elseif ($dc1_line->candidat_statut == '4') {
						
						$pdf->writeHTMLCell(190,4, 25, 86.5, dol_htmlentitiesbr("X"),0,1);
						$pdf->writeHTMLCell(190,4, 85, 95.65, dol_htmlentitiesbr("X"),0,1);

					}

				$pdf->writeHTMLCell(100,4, 100, 276.7, $outputlangs->convToOutputCharset($dc1_line->ref_consultation),0,1);

			// Page 3
				$pdf->AddPage();

				$tplidx = $pdf->importPage(3);
                if (! empty($tplidx)) $pdf->useTemplate($tplidx);

				$pdf->Image($logo, 10, 10, "", 10);
				//Nature des Travaux
				//$pdf->setXY(10,10); // fixe les positions x et y courantes
				//$pdf->SetFont('','',10); // fixe la police, le type ( 'B' pour gras, 'I' pour italique, '' pour normal,...)
				//$pdf->writeHTMLCell(190,4, 10, 10, dol_htmlentitiesbr($text3),0,1);

				// Pied de page
					$this->_pagefoot($pdf, $object, $outputlangs);
					if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();

				//Contenu

					$pdf->SetFont('','B',10); // fixe la police, le type ( 'B' pour gras, 'I' pour italique, '' pour normal,...)


					$pdf->writeHTMLCell(100,4, 100, 276.7, $outputlangs->convToOutputCharset($dc1_line->ref_consultation),0,1);


			// Page 4
				$pdf->AddPage();

				$tplidx = $pdf->importPage(4);
                if (! empty($tplidx)) $pdf->useTemplate($tplidx);

				$pdf->Image($logo, 10, 10, "", 10);
				
				// Pied de page
					$this->_pagefoot($pdf, $object, $outputlangs);
					//if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();

				//Contenu

					$pdf->SetFont('','B',10); // fixe la police, le type ( 'B' pour gras, 'I' pour italique, '' pour normal,...)


					if ($dc1_line->F_engagement == '1') {
					
						$pdf->writeHTMLCell(190,4, 114.75, 82.1, dol_htmlentitiesbr("X"),0,1);
						
					}

					$pdf->writeHTMLCell(190,4, 20, 145, dol_htmlentitiesbr($dc1_line->adresse_internet),0,1);
					$pdf->writeHTMLCell(190,4, 20, 160, dol_htmlentitiesbr($dc1_line->renseignement_adresse),0,1);


					if ($dc1_line->dc2 == '1') {
					
						$pdf->writeHTMLCell(190,4, 24.6, 197.85, dol_htmlentitiesbr("X"),0,1);
						
					}elseif($dc1_line->dc2 == '2') {
					
						$pdf->writeHTMLCell(190,4, 95, 197.85, dol_htmlentitiesbr("X"),0,1);
						
					}elseif($dc1_line->dc2 == '3') {
					
						$pdf->writeHTMLCell(190,4, 24.6, 197.85, dol_htmlentitiesbr("X"),0,1);
						$pdf->writeHTMLCell(190,4, 95, 197.85, dol_htmlentitiesbr("X"),0,1);
						
					}
					$pdf->writeHTMLCell(100,4, 100, 276.7, $outputlangs->convToOutputCharset($dc1_line->ref_consultation),0,1);



			// Page 5
				$pdf->AddPage();

				$tplidx = $pdf->importPage(5);
                if (! empty($tplidx)) $pdf->useTemplate($tplidx);

				$pdf->Image($logo, 10, 10, "", 10);
				$pdf->writeHTMLCell(100,4, 100, 276.7, $outputlangs->convToOutputCharset($dc1_line->ref_consultation),0,1);
				
				// Pied de page
				$this->_pagefoot($pdf, $object, $outputlangs);
				if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();


				//Contenu

					$pdf->SetFont('','B',10); // fixe la police, le type ( 'B' pour gras, 'I' pour italique, '' pour normal,...)

					
				
			// Fermture Formulaire
				$pdf->Close();

				$pdf->Output($file,'F');


				// Add pdfgeneration hook
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters=array('file'=>$file,'object'=>$object,'outputlangs'=>$outputlangs);
				global $action;
				$reshook=$hookmanager->executeHooks('afterPDFCreation',$parameters,$this,$action);    // Note that $action and $object may have been modified by some hooks

				if (! empty($conf->global->MAIN_UMASK))
				@chmod($file, octdec($conf->global->MAIN_UMASK));

				return 1;   // Pas d'erreur

			}
			else
			{
				$this->error=$langs->trans("ErrorCanNotCreateDir",$dir);
				return 0;
			}
		}
		else
		{
			$this->error=$langs->trans("ErrorConstantNotDefined","PROP_OUTPUTDIR");
			return 0;
		}
	}


/**
	 *  Show top header of page.
	 *
	 *  @param	PDF			$pdf     		Object PDF
	 *  @param  CommandeFournisseur		$object     	Object to show
	 *  @param  int	    	$showaddress    0=no, 1=yes
	 *  @param  Translate	$outputlangs	Object lang for output
	 *  @return	void
	 */
	function _pagehead(&$pdf, $object, $showaddress, $outputlangs)
	{
		global $langs,$conf,$mysoc;

		$outputlangs->load("main");
		$outputlangs->load("bills");
		$outputlangs->load("propal");
		$outputlangs->load("companies");
		$outputlangs->load("sendings");
		$outputlangs->load("dc1@dc1");

		$object->fetch_projet();

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		// Do not add the BACKGROUND as this is for suppliers
		//pdf_pagehead($pdf,$outputlangs,$this->page_hauteur);

		//Affiche le filigrane brouillon - Print Draft Watermark
		/*if($object->statut==0 && (! empty($conf->global->COMMANDE_DRAFT_WATERMARK)) )
		{
            pdf_watermark($pdf,$outputlangs,$this->page_hauteur,$this->page_largeur,'mm',$conf->global->COMMANDE_DRAFT_WATERMARK);
		}*/
		//Print content

		$pdf->SetTextColor(0,0,60);
		$pdf->SetFont('','B',$default_font_size + 3);

		$posx=$this->page_largeur-$this->marge_droite-100;
		$posy=$this->marge_haute;

		$pdf->SetXY($this->marge_gauche,$posy);

		// Logo
		$logo=$conf->mycompany->dir_output.'/logos/'.$this->emetteur->logo;
		if ($this->emetteur->logo)
		{
			if (is_readable($logo))
			{
			    $height=pdf_getHeightForLogo($logo);
			    $pdf->Image($logo, $this->marge_gauche, $posy, 0, $height);	// width=0 (auto)
			}
			else
			{
				$pdf->SetTextColor(200,0,0);
				$pdf->SetFont('','B', $default_font_size - 2);
				$pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorLogoFileNotFound",$logo), 0, 'L');
				$pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorGoToModuleSetup"), 0, 'L');
			}
		}
		else
		{
			$text=$this->emetteur->name;
			$pdf->MultiCell(100, 4, $outputlangs->convToOutputCharset($text), 0, 'L');
		}

		$pdf->SetFont('', 'B', $default_font_size + 3);
		$pdf->SetXY($posx,$posy);
		$pdf->SetTextColor(0,0,60);
		$title=$outputlangs->transnoentities("Deleg")." ".$outputlangs->convToOutputCharset($object->ref);
		$pdf->MultiCell(100, 3, $title, '', 'R');
		$posy+=1;


		$pdf->SetFont('','', $default_font_size -1);

		/*if (! empty($object->date_commande))
		{
			$posy+=4;
			$pdf->SetXY($posx,$posy);
			$pdf->SetTextColor(0,0,60);
			$pdf->MultiCell(100, 3, $outputlangs->transnoentities("OrderDate")." : " . dol_print_date($object->date_commande,"day",false,$outputlangs,true), '', 'R');
		}
		else
		{
			$posy+=4;
			$pdf->SetXY($posx,$posy);
			$pdf->SetTextColor(255,0,0);
			$pdf->MultiCell(100, 3, $outputlangs->transnoentities("OrderToProcess"), '', 'R');
		}*/

		$pdf->SetTextColor(0,0,60);
		$usehourmin='day';
		/*if (!empty($conf->global->SUPPLIER_ORDER_USE_HOUR_FOR_DELIVERY_DATE)) $usehourmin='dayhour';
		if (! empty($object->date_livraison))
		{
			$posy+=4;
			$pdf->SetXY($posx-90,$posy);
			$pdf->MultiCell(190, 3, $outputlangs->transnoentities("DateDeliveryPlanned")." : " . dol_print_date($object->date_livraison,$usehourmin,false,$outputlangs,true), '', 'R');
		}*/

		/*if ($object->thirdparty->code_fournisseur)
		{
			$posy+=4;
			$pdf->SetXY($posx,$posy);
			$pdf->SetTextColor(0,0,60);
			$pdf->MultiCell(100, 3, $outputlangs->transnoentities("SupplierCode")." : " . $outputlangs->transnoentities($object->thirdparty->code_fournisseur), '', 'R');
		}*/

		$posy+=1;
		$pdf->SetTextColor(0,0,60);

		/*
		// Show list of linked objects
		$posy = pdf_writeLinkedObjects($pdf, $object, $outputlangs, $posx, $posy, 100, 3, 'R', $default_font_size);
		*/

		if ($showaddress)
		{
			// Sender properties
			$carac_emetteur = pdf_build_address($outputlangs, $this->emetteur, $object->thirdparty);

			// Show sender
			$posy=42;
			$posx=$this->marge_gauche;
			if (! empty($conf->global->MAIN_INVERT_SENDER_RECIPIENT)) $posx=$this->page_largeur-$this->marge_droite-80;
			$hautcadre=40;

			// Show sender frame
			$pdf->SetTextColor(0,0,0);
			$pdf->SetFont('','', $default_font_size - 2);
			$pdf->SetXY($posx,$posy-5);
			$pdf->MultiCell(66,5, $outputlangs->transnoentities("BillFrom").":", 0, 'L');
			$pdf->SetXY($posx,$posy);
			$pdf->SetFillColor(230,230,230);
			$pdf->MultiCell(82, $hautcadre, "", 0, 'R', 1);
			$pdf->SetTextColor(0,0,60);

			// Show sender name
			$pdf->SetXY($posx+2,$posy+3);
			$pdf->SetFont('','B', $default_font_size);
			$pdf->MultiCell(80, 4, $outputlangs->convToOutputCharset($this->emetteur->name), 0, 'L');
			$posy=$pdf->getY();

			// Show sender information
			$pdf->SetXY($posx+2,$posy);
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->MultiCell(80, 4, $carac_emetteur, 0, 'L');



			// If BILLING contact defined on order, we use it
			$usecontact=false;
			$arrayidcontact=$object->getIdContact('external','BILLING');
			if (count($arrayidcontact) > 0)
			{
				$usecontact=true;
				$result=$object->fetch_contact($arrayidcontact[0]);
			}

			//Recipient name
			// On peut utiliser le nom de la societe du contact
			if ($usecontact && !empty($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT)) {
				$thirdparty = $object->contact;
			} else {
				$thirdparty = $object->thirdparty;
			}

			$carac_client_name= pdfBuildThirdpartyName($thirdparty, $outputlangs);

			$carac_client=pdf_build_address($outputlangs,$this->emetteur,$object->thirdparty,($usecontact?$object->contact:''),$usecontact,'target',$object);

			// Show recipient
			$widthrecbox=100;
			if ($this->page_largeur < 210) $widthrecbox=84;	// To work with US executive format
			$posy=42;
			$posx=$this->page_largeur-$this->marge_droite-$widthrecbox;
			if (! empty($conf->global->MAIN_INVERT_SENDER_RECIPIENT)) $posx=$this->marge_gauche;
/*
			// Show recipient frame
			$pdf->SetTextColor(0,0,0);
			$pdf->SetFont('','', $default_font_size - 2);
			$pdf->SetXY($posx+2,$posy-5);
			$pdf->MultiCell($widthrecbox, 5, $outputlangs->transnoentities("BillTo").":",0,'L');
			$pdf->Rect($posx, $posy, $widthrecbox, $hautcadre);

			// Show recipient name
			$pdf->SetXY($posx+2,$posy+3);
			$pdf->SetFont('','B', $default_font_size);
			$pdf->MultiCell($widthrecbox, 4, $carac_client_name, 0, 'L');

			$posy = $pdf->getY();

			// Show recipient information
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->SetXY($posx+2,$posy);
			$pdf->MultiCell($widthrecbox, 4, $carac_client, 0, 'L');
*/
		}
	}


		/**
	 *   	Show footer of page. Need this->emetteur object
     *
	 *   	@param	PDF			$pdf     			PDF
	 * 		@param	CommandeFournisseur		$object				Object to show
	 *      @param	Translate	$outputlangs		Object lang for output
	 *      @param	int			$hidefreetext		1=Hide free text
	 *      @return	int								Return height of bottom margin including footer text
	 */
	function _pagefoot(&$pdf, $object, $outputlangs, $hidefreetext=0)
	{
		global $conf;
		$showdetails=$conf->global->MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS;
		//return pdf_pagefoot($pdf,$outputlangs,'',$this->emetteur,$this->marge_basse,$this->marge_gauche,$this->page_hauteur,$object,$showdetails,$hidefreetext);
	}

}

