<?php

$GLOBALS['formats'] = array(
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'txt' => 'text/plain',
    'odt' => 'application/msword',  
    'log' => 'text/plain'   
);

function formulaires_cv_charger_dist(){
    $valeurs = array('email'=>'','texte'=>'','nom'=>'','prenom'=>'','sujet'=>'','adresse'=>'','cp'=>'','ville'=>'','tel'=>'' );
    return $valeurs;
}

function formulaires_cv_verifier_dist(){
    $formats = $GLOBALS['formats'];
    $erreurs = array();
    
    include_spip('inc/filtres');

    if (!$nom=_request('nom'))
        $erreurs['nom'] = _T("info_obligatoire");

    if (!$prenom=_request('prenom'))
        $erreurs['prenom'] = _T("info_obligatoire");
    
    if (!$email_from = _request('email'))
        $erreurs['email'] = _T("form_prop_indiquer_email");
    elseif(!email_valide($email_from))
        $erreurs['email'] = _T('form_prop_indiquer_email');

    //Connaitre les piéces jointes
    $_FILES ? $_FILES : $GLOBALS['HTTP_POST_FILES'];
    
    //Est ce qu'il y a une pièce jointe ?
    //if($_FILES['cv']['error'] == UPLOAD_ERR_NO_FILE)
        //$erreurs['cv'] .= _T("info_obligatoire");

    //Test de la taille
    if($_FILES['cv']['error'] == UPLOAD_ERR_FORM_SIZE || $_FILES['cv']['size'] > 2097152)
        $erreurs['cv'] .= "La taille de votre CV excéde la taille autorisée";

    //Test des extensions
    //$fichier = pathinfo($_FILES['cv']['name']);
    //if (!in_array($fichier['extension'],array_keys($formats))) 
        //$erreurs['cv'] .= "Le fichier de votre CV n'a pas un format accepté";

    if (!$sujet=_request('sujet'))
        $erreurs['sujet'] = _T("info_obligatoire");
    
    if (count($erreurs))
        $erreurs['message_erreur'] = 'Ce formulaire contient des erreurs... les champs précédés d\' un * sont obligatoires.';
    return $erreurs;
}

function formulaires_cv_traiter_dist(){
    $formats = $GLOBALS['formats'];

    $envoyer_mail = charger_fonction('envoyer_mail','inc');
    $email_to = $GLOBALS['meta']['email_webmaster'];
    $email_from = _request('email');
    $sujet = _request('sujet');
    $texte = _request('texte');
    $nom= _request('nom');
    $prenom= _request('prenom');
    $adres= _request('email');
    $tel= _request('tel');
    
    //Déclarer un mail en partie multiple
    $boundary .= "piecejointe";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
    
    //Le corps de mail
    $message_mail ="--". $boundary ."\n";
    $message_mail .="Content-Type: text/plain; charset=ISO-8859-1\r\n\n";
    $message_mail.= "Un message a été posté depuis le site par ".$prenom." ".$nom." \n";
    $message_mail.= "E-mail: ".$email_from."\n";
    $message_mail.= "Téléphone: ".$tel." \n";
    $message_mail.= "Sujet: ".$sujet." \n";
    $message_mail.= "Message « ".$texte." »\n\n";

    //Connaitre les piéces jointes
    $_FILES ? $_FILES : $GLOBALS['HTTP_POST_FILES'];

    //Connaitre l'extension
    $fichier = pathinfo($_FILES['cv']['name']);
    $extension = $fichier['extension'];

    //Formater le fichier pour le mail
    $fichier=file_get_contents($_FILES['cv']['tmp_name']);
    /* On utilise aussi chunk_split() qui organisera comme il faut l'encodage fait en base 64 pour se conformer aux standards */
    $fichier=chunk_split( base64_encode($fichier) );
    
    $message_mail.= "--" .$boundary. "\n";
    $message_mail.= "Content-Type: ".$formats[$extension]."; name=\"".$_FILES['cv']['name']."\"\n";
    $message_mail.= "Content-Transfer-Encoding: base64\n";
    $message_mail.= "Content-Disposition: attachment; filename=\"".$_FILES['cv']['name']."\"\r\n\n";
    $message_mail.= $fichier;

    //Cloture du mail
    $message_mail.= "--".$boundary."--";

    $envoyer_mail($email_to,$sujet,$message_mail,$email_from,$headers);

    return array('message_ok'=>'Merci pour votre message, il a bien &eacute;t&eacute; pris en compte. Nous vous contacterons prochainement.');}
?>
