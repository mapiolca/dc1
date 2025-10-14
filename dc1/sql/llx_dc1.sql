-- ============================================================================
-- Copyright (C) 2018 Pierre Ardoin
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program. If not, see <http://www.gnu.org/licenses/>.
--
-- ============================================================================

CREATE TABLE IF NOT EXISTS `llx_DC1`( 
  	`rowid`				int(11) AUTO_INCREMENT,
  	`fk_object` 			int(11)  NOT NULL, 
  	`fk_element` 			VARCHAR(255) NOT NULL,   
  	`id_acheteur`		   TEXT  NOT NULL,  
	`objet_consultation`          TEXT NOT NULL,
	`ref_consultation`          VARCHAR(255) NOT NULL,
	`objet_candidature`          int(11) NOT NULL,
	`n_lots`          VARCHAR(255) NOT NULL,
	`designation_lot`          TEXT NOT NULL,
	`candidat_statut`          int(11) NOT NULL,
	`F_engagement`          int(11) NOT NULL,
	`adresse_internet`          VARCHAR(255) NOT NULL,
	`renseignement_adresse`          VARCHAR(255) NOT NULL,
	`dc2`          int(11) NOT NULL, 

	PRIMARY KEY (`rowid`) 
)ENGINE=innodb DEFAULT CHARSET=utf8 ;


CREATE TABLE IF NOT EXISTS `llx_DC1_groupement`( 
  	`rowid`				int(11) AUTO_INCREMENT,
  	`fk_object` 			int(11)  NOT NULL,
  	`fk_dc1` 			int(11)  NOT NULL,  
  	`n_lot`          VARCHAR(255) NOT NULL,
	`societe_lot`          int(11) NOT NULL,
	`prestation_lot`          TEXT NOT NULL,
	`mandataire`          int(11) NOT NULL,

	PRIMARY KEY (`rowid`) 
)ENGINE=innodb DEFAULT CHARSET=utf8 ;

