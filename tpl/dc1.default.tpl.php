<?php

/* Copyright (C) 2019-2025      Pierre Ardoin        <developpeur@lesmetiersdubatiment.fr>
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

/**	    \file       htdocs/delegation/tpl/delegation.default.tpl.php
 *		\ingroup    delegation
 *		\brief      Delegation module default view
 */

llxHeader();

print ($message ? dol_htmloutput_mesg($message, '', ($error ? 'error' : 'ok'), 0) : '');

print dol_get_fiche_head($head, $current_head, $langs->trans('dc1'), -1, 'propal', 0, '', '', 0, '', 1);

$object->fetch_thirdparty();

// Proposal card

    $linkback = '<a href="'.DOL_URL_ROOT.'/comm/propal/list.php?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';

    $morehtmlref = '<div class="refidno">';
    // Ref customer
    $morehtmlref .= $form->editfieldkey("RefCustomer", 'ref_client', $object->ref_client, $object, $usercancreate, 'string', '', 0, 1);
    $morehtmlref .= $form->editfieldval("RefCustomer", 'ref_client', $object->ref_client, $object, $usercancreate, 'string'.(isset($conf->global->THIRDPARTY_REF_INPUT_SIZE) ? ':' . getDolGlobalString('THIRDPARTY_REF_INPUT_SIZE') : ''), '', null, null, '', 1);
    // Thirdparty
    $morehtmlref .= '<br>'.$soc->getNomUrl(1, 'customer');
    if (!getDolGlobalString('MAIN_DISABLE_OTHER_LINK') && $soc->id > 0) {
        $morehtmlref .= ' (<a href="'.DOL_URL_ROOT.'/comm/propal/list.php?socid='.$soc->id.'&search_societe='.urlencode($soc->name).'">'.$langs->trans("OtherProposals").'</a>)';
    }
    // Project
    if (isModEnabled('project')) {
        $langs->load("projects");
        $morehtmlref .= '<br>';
        if ($usercancreate) {
            $morehtmlref .= img_picto($langs->trans("Project"), 'project', 'class="pictofixedwidth"');
            if ($action != 'classify') {
                $morehtmlref .= '<a class="editfielda" href="'.$_SERVER['PHP_SELF'].'?action=classify&token='.newToken().'&id='.$object->id.'">'.img_edit($langs->transnoentitiesnoconv('SetProject')).'</a> ';
            }
            $morehtmlref .= $form->form_project($_SERVER['PHP_SELF'].'?id='.$object->id, $object->socid, (string) $object->fk_project, ($action == 'classify' ? 'projectid' : 'none'), 0, 0, 0, 1, '', 'maxwidth300');
        } else {
            if (!empty($object->fk_project)) {
                $proj = new Project($db);
                $proj->fetch($object->fk_project);
                $morehtmlref .= $proj->getNomUrl(1);
                if ($proj->title) {
                    $morehtmlref .= '<span class="opacitymedium"> - '.dol_escape_htmltag($proj->title).'</span>';
                }
            }
        }
    }
    $morehtmlref .= '</div>';

    dol_banner_tab($object, 'ref', $linkback, 0, 'ref', 'ref', $morehtmlref);
?>
  
<?php print $formconfirm ? $formconfirm : ''; ?>

<table class="border" width="100%">
    <tr>
        <td>
            <div class="info"><?php print $langs->trans('DC1_Consigne_entete'); ?>
                <br>
                <p>
                    <a href="<?php print DOL_URL_ROOT.'../core/modules/propale/doc/DC1_2019/notice-dc1-2019.pdf';?>"><?php print $langs->trans('DC1_Consigne_notice'); ?></a>
                </p>
            </div>
        </td>
    </tr>
</table>

<table id="tablelines" class="noborder" width="100%">
<?php if ($numLines > 0){ ?>
    <tr class="liste_titre nodrag nodrop">
        <td><?php print $langs->trans('Label'); ?></td>
		<td><?php print $langs->trans('Value'); ?></td>
		<td width="">&nbsp;</td>
	</tr>
    <?php 
    for($i = 0; $i < $numLines; $i++){
        $line = $dc1->lines[$i]; 

        if ($action == 'editline' && $lineid == $line->rowid){ ?>

        <form action="<?php print $_SERVER["PHP_SELF"].'?id='.$object->id; ?>" method="POST">
        <input type="hidden" name="token" value="<?php  print $_SESSION['newtoken']; ?>" />
        <input type="hidden" name="action" value="updateline" />
        <input type="hidden" name="id" value="<?php print $object->id; ?>" />
        <input type="hidden" name="lineid" value="<?php print $line->rowid; ?>"/>
        <input type="hidden" name="field" value="<?php print $field ?>"/>

         <?php } ?>

        <tr class="<?php print ($i%2==0 ? 'impair' : 'pair'); ?>">
            <td> 
                <?php print $langs->trans('id_acheteur'); ?>

                <img src="/theme/md/img/info.png" alt="" title="<div class=&quot;centpercent&quot;><?php print $langs->trans("DC1_A_Tooltip") ; ?></div>" class="paddingright classfortooltip valigntextbottom">
                
            </td>

            <td>
                <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "id_acheteur"){ ?>
                    <input type="text" size="8" id="id_acheteur" name="id_acheteur" value="<?php print $line->id_acheteur; ?>" />
                <?php }else{ 
                print $object->thirdparty->getNomUrl();
                } ?>
            </td>

            <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "id_acheteur"){ ?>
                <td align="right">
                    <input type="submit" class="button" name="save" value="<?php print $langs->trans("Save"); ?>" />&nbsp;<input type="submit" class="button" name="cancel" value="<?php print $langs->trans("Cancel"); ?>" />
                </td>
            <?php }else{ ?>
                <td align="right">
                    <?php if ($canAddLines) { ?>       
                        <?php /*<a href="<?php print $_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=editline&amp;lineid='.$line->rowid.'&amp;field=id_acheteur'; ?>">
                            <?php print img_edit(); ?>
                        */ ?>
                        </a>
                    <?php } ?>
                </td>
            <?php } ?>
        </tr> 

        <tr class="<?php print ($i%2==0 ? 'impair' : 'pair'); ?>">
            <td> 
                <?php print $langs->trans('objet_consultation'); ?>
                <img src="/theme/md/img/info.png" alt="" title="<div class=&quot;centpercent&quot;><?php print $langs->trans("DC1_B_Tooltip") ?></div>" class="paddingright classfortooltip valigntextbottom">
            </td>

            <td>
                <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "objet_consultation" ){ ?>
                    <textarea type="text" size="8" id="objet_consultation" name="objet_consultation" ><?php print $line->objet_consultation; ?></textarea>
                <?php }else{ 
                print $line->objet_consultation ; 
                } ?>
            </td>

            <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "objet_consultation"){ ?>
                <td align="right">
                    <input type="submit" class="button" name="save" value="<?php print $langs->trans("Save"); ?>" />&nbsp;<input type="submit" class="button" name="cancel" value="<?php print $langs->trans("Cancel"); ?>" />
                </td>
            <?php }else{ ?>
                <td align="right">
                    <?php if ($canAddLines) { ?>       
                        <a href="<?php print $_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=editline&amp;lineid='.$line->rowid.'&amp;field=objet_consultation'; ?>">
                            <?php print img_edit(); ?>
                        </a>
                    <?php } ?>
                </td>
            <?php } ?>
        </tr>

        <tr class="<?php print ($i%2==0 ? 'impair' : 'pair'); ?>">
            <td> 
                <?php print "&nbsp;&nbsp;&nbsp;-&nbsp;".$langs->trans('ref_consultation'); ?>       
            </td>

            <td>
                <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "ref_consultation" ){ ?>
                    <input type="text" size="8" id="ref_consultation" name="ref_consultation" value="<?php print $line->ref_consultation; ?>" />
                <?php }else{ 
                print $line->ref_consultation ; 
                } ?>
            </td>

            <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "ref_consultation"){ ?>
                <td align="right">
                    <input type="submit" class="button" name="save" value="<?php print $langs->trans("Save"); ?>" />&nbsp;<input type="submit" class="button" name="cancel" value="<?php print $langs->trans("Cancel"); ?>" />
                </td>
            <?php }else{ ?>
                <td align="right">
                    <?php if ($canAddLines) { ?>       
                        <a href="<?php print $_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=editline&amp;lineid='.$line->rowid.'&amp;field=ref_consultation'; ?>">
                            <?php print img_edit(); ?>
                        </a>
                    <?php } ?>
                </td>
            <?php } ?>
        </tr>

        <tr class="<?php print ($i%2==0 ? 'impair' : 'pair'); ?>">
            <td> 
                <?php print "".$langs->trans('objet_candidature'); ?>
                <img src="/theme/md/img/info.png" alt="" title="<div class=&quot;centpercent&quot;><?php print $langs->trans("DC1_C_Tooltip") ?></div>" class="paddingright classfortooltip valigntextbottom">   
            </td>

            <td>
                <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "objet_candidature" ){ ?>
                    
                    <select id="objet_candidature" name="objet_candidature" value="<?php print $line->objet_candidature; ?>">
                        <option value="1" <?php if ($line->objet_candidature == 1) { print "selected" ; } ?>><?php print $langs->trans('marche_public'); ?></option>
                        <option value="3" <?php if ($line->objet_candidature == 3) { print "selected" ; } ?>><?php print $langs->trans('tous_les_lots'); ?></option>
                        <option value="2" <?php if ($line->objet_candidature == 2) { print "selected" ; } ?>><?php print $langs->trans('un_seul_lot'); ?></option>
                        <option value="4" <?php if ($line->objet_candidature == 4) { print "selected" ; } ?>><?php print $langs->trans('plusieurs_lots'); ?></option>
                    </select>

                <?php }else{ 

                if ($line->objet_candidature == 1) {
                   print $langs->trans('marche_public') ; 
                }elseif ($line->objet_candidature == 2 ) {
                    print $langs->trans('lots_separe').' (1)' ; 
                }elseif ($line->objet_candidature == 3) {
                    print $langs->trans('tous_les_lots') ; 
                }elseif ($line->objet_candidature == 4 ) {
                    print $langs->trans('lots_separe').' (2+)' ; 
                }else{
                    print $langs->trans('non_renseigne') ; 
                }
                
                } ?>
            </td>

            <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "objet_candidature"){ ?>
                <td align="right">
                    <input type="submit" class="button" name="save" value="<?php print $langs->trans("Save"); ?>" />&nbsp;<input type="submit" class="button" name="cancel" value="<?php print $langs->trans("Cancel"); ?>" />
                </td>
            <?php }else{ ?>
                <td align="right">
                    <?php if ($canAddLines) { ?>       
                        <a href="<?php print $_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=editline&amp;lineid='.$line->rowid.'&amp;field=objet_candidature'; ?>">
                            <?php print img_edit(); ?>
                        </a>
                    <?php } ?>
                </td>
            <?php } ?>
        </tr>

        <tr class="<?php print ($i%2==0 ? 'impair' : 'pair'); ?>">
            <td> 
                <?php print "&nbsp;&nbsp;&nbsp;-&nbsp;".$langs->trans('n_lots'); ?>
                <img src="/theme/md/img/info.png" alt="" title="<div class=&quot;centpercent&quot;><?php print $langs->trans("DC1_n_lots_Tooltip") ?></div>" class="paddingright classfortooltip valigntextbottom">       
            </td>

            <td>
                <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "n_lots" ){ ?>
                    <textarea type="text" size="8" id="n_lots" name="n_lots"><?php print $line->n_lots; ?></textarea>
                <?php }else{ 
                print $line->n_lots ; 
                } ?>
            </td>

            <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "n_lots"){ ?>
                <td align="right">
                    <input type="submit" class="button" name="save" value="<?php print $langs->trans("Save"); ?>" />&nbsp;<input type="submit" class="button" name="cancel" value="<?php print $langs->trans("Cancel"); ?>" />
                </td>
            <?php }else{ ?>
                <td align="right">
                    <?php if ($canAddLines) { ?>       
                        <a href="<?php print $_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=editline&amp;lineid='.$line->rowid.'&amp;field=n_lots'; ?>">
                            <?php print img_edit(); ?>
                        </a>
                    <?php } ?>
                </td>
            <?php } ?>
        </tr>

        <tr class="<?php print ($i%2==0 ? 'impair' : 'pair'); ?>">
            <td> 
                <?php print "&nbsp;&nbsp;&nbsp;-&nbsp;".$langs->trans('designation_lot') ?>
                <img src="/theme/md/img/info.png" alt="" title="<div class=&quot;centpercent&quot;><?php print $langs->trans("DC1_C_Designation_Tooltip") ?>En cas de non numérotation</div>" class="paddingright classfortooltip valigntextbottom">   
            </td>

            <td>
                <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "designation_lot" ){ ?>
                    <textarea type="text" size="8" id="designation_lot" name="designation_lot"><?php print $line->designation_lot; ?></textarea>
                <?php }else{ 
                print $line->designation_lot ; 
                } ?>
            </td>

            <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "designation_lot"){ ?>
                <td align="right">
                    <input type="submit" class="button" name="save" value="<?php print $langs->trans("Save"); ?>" />&nbsp;<input type="submit" class="button" name="cancel" value="<?php print $langs->trans("Cancel"); ?>" />
                </td>
            <?php }else{ ?>
                <td align="right">
                    <?php if ($canAddLines) { ?>       
                        <a href="<?php print $_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=editline&amp;lineid='.$line->rowid.'&amp;field=designation_lot'; ?>">
                            <?php print img_edit(); ?>
                        </a>
                    <?php } ?>
                </td>
            <?php } ?>
        </tr>

        <tr class="<?php print ($i%2==0 ? 'impair' : 'pair'); ?>">
            <td> 
                <?php print $langs->trans('candidat_statut'); ?>
                <img src="/theme/md/img/info.png" alt="" title="<div class=&quot;centpercent&quot;><?php print $langs->trans("DC1_D_Tooltip") ?></div>" class="paddingright classfortooltip valigntextbottom">       
            </td>

            <td>
                <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "candidat_statut" ){ ?>

                    <select id="candidat_statut" name="candidat_statut" value="<?php print $line->candidat_statut; ?>">
                        <option value="1" <?php if ($line->candidat_statut == 1) { print "selected" ; } ?>><?php print $langs->trans('candidat_statut1'); ?></option> 
                        <option value="2" <?php if ($line->candidat_statut == 2) { print "selected" ; } ?>><?php print $langs->trans('candidat_statut2'); ?></option>
                        <option value="3" <?php if ($line->candidat_statut == 3) { print "selected" ; } ?>><?php print $langs->trans('candidat_statut3'); ?></option>
                        <option value="4" <?php if ($line->candidat_statut == 4) { print "selected" ; } ?>><?php print $langs->trans('candidat_statut4'); ?></option>
                    </select>

                <?php }else{ 

                if ($line->candidat_statut == 1) {
                   print $langs->trans('candidat_statut1') ; 
                }elseif ($line->candidat_statut == 2) {
                    print $langs->trans('candidat_statut2') ; 
                }elseif ($line->candidat_statut == 3) {
                    print $langs->trans('candidat_statut3') ; 
                }elseif ($line->candidat_statut == 4) {
                    print $langs->trans('candidat_statut4') ; 
                }else{
                    print $langs->trans('non_renseigne') ; 
                }
                
                } ?>
            </td>

            <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "candidat_statut"){ ?>
                <td align="right">
                    <input type="submit" class="button" name="save" value="<?php print $langs->trans("Save"); ?>" />&nbsp;<input type="submit" class="button" name="cancel" value="<?php print $langs->trans("Cancel"); ?>" />
                </td>
            <?php }else{ ?>
                <td align="right">
                    <?php if ($canAddLines) { ?>       
                        <a href="<?php print $_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=editline&amp;lineid='.$line->rowid.'&amp;field=candidat_statut'; ?>">
                            <?php print img_edit(); ?>
                        </a>
                    <?php } ?>
                </td>
            <?php } ?>
        </tr>

        <tr class="<?php print ($i%2==0 ? 'impair' : 'pair'); ?>">
            <td> 
                <?php print "".$langs->trans('id_membre'); ?>       
            </td>

            <td>
                <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "id_membre" ){ ?>
                    <?php /* <input type="text" size="8" id="id_membre" name="id_membre" value="<?php print $line->id_membre; ?>" /> */ ?>
                <?php }else{ 
                /* print $line->id_membre ; */ print "<div class='warning'>Non pris en charge pour le moment</div>";
                } ?>
            </td>

            <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "id_membre"){ ?>
                <td align="right">
                    <input type="submit" class="button" name="save" value="<?php print $langs->trans("Save"); ?>" />&nbsp;<input type="submit" class="button" name="cancel" value="<?php print $langs->trans("Cancel"); ?>" />
                </td>
            <?php }else{ ?>
                <td align="right">
                    <?php if ($canAddLines) { ?>       
                    <?php /* <a href="<?php print $_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=editline&amp;lineid='.$line->rowid.'&amp;field=id_membre'; ?>"><?php print img_edit(); ?></a>*/?>
                    <?php } ?>
                </td>
            <?php } ?>
        </tr>

        <tr class="<?php print ($i%2==0 ? 'impair' : 'pair'); ?>">
            <td> 
                <?php print "".$langs->trans('DC1_F'); ?>
            </td>
            <td></td>
            <td></td>
        </tr>

        <tr class="<?php print ($i%2==0 ? 'impair' : 'pair'); ?>">
            <td> 
                <?php print "&nbsp;&nbsp;&nbsp;".$langs->trans('F_engagement'); ?>
                <img src="/theme/md/img/info.png" alt="" title="<div class=&quot;centpercent&quot;><?php print $langs->trans("DC1_F1_Tooltip"); ?></div>" class="paddingright classfortooltip valigntextbottom">
            </td>

            <td>
                <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "F_engagement" ){ ?>
                    <select id="F_engagement" name="F_engagement" value="<?php print $line->F_engagement; ?>">
                        <option value="1" <?php if ($line->F_engagement == 1) { print "selected" ; } ?>><?php print $langs->trans('F_engagement1'); ?></option> 
                        <option value="2" <?php if ($line->F_engagement == 2) { print "selected" ; } ?>><?php print $langs->trans('F_engagement2'); ?></option>
                    </select>

                <?php }else{ 

                if ($line->F_engagement == 1) {
                   print $langs->trans('F_engagement1') ; 
                }elseif ($line->F_engagement == 2) {
                    print $langs->trans('F_engagement2') ; 
                }else{
                    print $langs->trans('non_renseigne') ; 
                }
                
                } ?>

            </td>

            <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "F_engagement"){ ?>
                <td align="right">
                    <input type="submit" class="button" name="save" value="<?php print $langs->trans("Save"); ?>" />&nbsp;<input type="submit" class="button" name="cancel" value="<?php print $langs->trans("Cancel"); ?>" />
                </td>
            <?php }else{ ?>
                <td align="right">
                    <?php if ($canAddLines) { ?>       
                        <a href="<?php print $_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=editline&amp;lineid='.$line->rowid.'&amp;field=F_engagement'; ?>">
                            <?php print img_edit(); ?>
                        </a>
                    <?php } ?>
                </td>
            <?php } ?>
        </tr>

        <tr class="<?php print ($i%2==0 ? 'impair' : 'pair'); ?>">
            <td>
                <?php print "&nbsp;&nbsp;&nbsp;".$langs->trans('F_documents'); ?>
                <img src="/theme/md/img/info.png" alt="" title="<div class=&quot;centpercent&quot;><?php print $langs->trans('DC1_F2_Tooltip'); ?></div>" class="paddingright classfortooltip valigntextbottom">
            </td>
            <td></td>
            <td></td>
        </tr>

        <tr class="<?php print ($i%2==0 ? 'impair' : 'pair'); ?>">
            <td>          
                <?php print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;".$langs->trans('adresse_internet'); ?>      
            </td>

            <td>
                <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "adresse_internet" ){ ?>
                    <textarea type="text" size="8" id="adresse_internet" name="adresse_internet" ><?php print $line->adresse_internet; ?></textarea>
                <?php }else{ 
                print $line->adresse_internet ; 
                } ?>
            </td>

            <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "adresse_internet"){ ?>
                <td align="right">
                    <input type="submit" class="button" name="save" value="<?php print $langs->trans("Save"); ?>" />&nbsp;<input type="submit" class="button" name="cancel" value="<?php print $langs->trans("Cancel"); ?>" />
                </td>
            <?php }else{ ?>
                <td align="right">
                    <?php if ($canAddLines) { ?>       
                        <a href="<?php print $_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=editline&amp;lineid='.$line->rowid.'&amp;field=adresse_internet'; ?>">
                            <?php print img_edit(); ?>
                        </a>
                    <?php } ?>
                </td>
            <?php } ?>
        </tr>

        <tr class="<?php print ($i%2==0 ? 'impair' : 'pair'); ?>">
            <td>        
                <?php print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;".$langs->trans('renseignement_adresse'); ?>       
            </td>

            <td>
                <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "renseignement_adresse" ){ ?>
                    <textarea type="text" size="8" id="renseignement_adresse" name="renseignement_adresse" ><?php print $line->renseignement_adresse; ?></textarea>
                <?php }else{ 
                print $line->renseignement_adresse ; 
                } ?>
            </td>

            <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "renseignement_adresse"){ ?>
                <td align="right">
                    <input type="submit" class="button" name="save" value="<?php print $langs->trans("Save"); ?>" />&nbsp;<input type="submit" class="button" name="cancel" value="<?php print $langs->trans("Cancel"); ?>" />
                </td>
            <?php }else{ ?>
                <td align="right">
                    <?php if ($canAddLines) { ?>       
                        <a href="<?php print $_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=editline&amp;lineid='.$line->rowid.'&amp;field=renseignement_adresse'; ?>">
                            <?php print img_edit(); ?>
                        </a>
                    <?php } ?>
                </td>
            <?php } ?>
        </tr>

        <tr class="<?php print ($i%2==0 ? 'impair' : 'pair'); ?>">
            <td>           
                    <?php print "&nbsp;&nbsp;&nbsp;".$langs->trans('DC1_F3'); ?>

                <img src="/theme/md/img/info.png" alt="" title="<div class=&quot;centpercent&quot;>Le candidat individuel, ou les membres du groupement, produisent, aux fins de vérification de l’aptitude à exercer l’activité professionnelle, de la capacité économique et financière et des capacités techniques et professionnelles : <i> (Cocher la case correspondante.)<i><ul><li>Le Formulaire DC2</li><li>les documents établissant ses capacités, tels que demandés dans les documents de la consultation(*).</li><li>Tout</li><li>Aucun</li></div>" class="paddingright classfortooltip valigntextbottom">     
            </td>

            <td>
                <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "dc2" ){ ?>
                    <select id="dc2" name="dc2" value="<?php print $line->dc2; ?>">
                        <option value="1" <?php if ($line->dc2 == 1) { print "selected" ; } ?>><?php print $langs->trans('dc21'); ?></option> 
                        <option value="2" <?php if ($line->dc2 == 2) { print "selected" ; } ?>><?php print $langs->trans('dc22'); ?></option>
                        <option value="3" <?php if ($line->dc2 == 3) { print "selected" ; } ?>><?php print $langs->trans('dc23'); ?></option> 
                        <option value="4" <?php if ($line->dc2 == 4) { print "selected" ; } ?>><?php print $langs->trans('dc24'); ?></option>
                    </select>

                <?php }else{ 

                if ($line->dc2 == 1) {
                   print $langs->trans('dc21') ; 
                }elseif ($line->dc2 == 2) {
                    print $langs->trans('dc22') ; 
                }elseif ($line->dc2 == 3) {
                    print $langs->trans('dc23') ; 
                }elseif ($line->dc2 == 4) {
                    print $langs->trans('dc24') ; 
                }else{
                    print $langs->trans('non_renseigne') ; 
                }
                
                } ?>
            </td>

            <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "dc2"){ ?>
                <td align="right">
                    <input type="submit" class="button" name="save" value="<?php print $langs->trans("Save"); ?>" />&nbsp;<input type="submit" class="button" name="cancel" value="<?php print $langs->trans("Cancel"); ?>" />
                </td>
            <?php }else{ ?>
                <td align="right">
                    <?php if ($canAddLines) { ?>       
                        <a href="<?php print $_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=editline&amp;lineid='.$line->rowid.'&amp;field=dc2'; ?>">
                            <?php print img_edit(); ?>
                        </a>
                    <?php } ?>
                </td>
            <?php } ?>
        </tr> 

        <tr class="<?php print ($i%2==0 ? 'impair' : 'pair'); ?>">
            <td> 
                <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "mandataire"){ ?>           
                    <?php print $langs->trans('mandataire'); ?>
                <?php }else{ 
                    print $langs->trans('mandataire'); 
                } ?>
                <img src="/theme/md/img/info.png" alt="" title="<div class=&quot;centpercent&quot;><?php print $langs->trans("DC1_G_Tooltip"); ?></div>" class="paddingright classfortooltip valigntextbottom">
            </td>

            <td>
                <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "mandataire" ){ ?>
                    <?php /* <input type="text" size="8" id="mandataire" name="mandataire" value="<?php print $line->mandataire; ?>" /> */ ?>
                <?php }else{ 
                /* print $line->mandataire ; */ print "<div class='warning'>Non pris en charge pour le moment</div>";
                } ?>

                
            </td>

            <?php if ($action == 'editline' && $lineid == $line->rowid && $field == "mandataire"){ ?>
                <td align="right">
                    <input type="submit" class="button" name="save" value="<?php print $langs->trans("Save"); ?>" />&nbsp;<input type="submit" class="button" name="cancel" value="<?php print $langs->trans("Cancel"); ?>" />
                </td>
            <?php }else{ ?>
                <td align="right">
                    <?php if ($canAddLines) { ?>       
                    <?php /* <a href="<?php print $_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=editline&amp;lineid='.$line->rowid.'&amp;field=mandataire'; ?>"><?php print img_edit(); ?></a>*/?>
                    <?php } ?>
                </td>
            <?php } ?>
        </tr>




    <?php } ?>
	</form>
<?php } ?>

</table>
<table class="border" width="100%">
    <tr>
        <td>
            <div class="info"><?php print $langs->trans('DC1_generate'); ?></div>
        </td>
    </tr>
</table>

</div>

<br />

<?php dol_fiche_end(); ?>

<?php llxFooter(''); ?>

