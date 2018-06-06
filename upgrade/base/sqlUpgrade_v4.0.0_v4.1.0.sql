
-- Retrait des placeholders
update data_types set placeholder="", description="poids du patient en kg" where name="poids";
update data_types set placeholder="", description="taille du patient en cm" where name="taillePatient";
update data_types set placeholder="", description="clairance de la créatinine en mL/min" where name="clairanceCreatinine";
update data_types set placeholder="" where name="imc";
