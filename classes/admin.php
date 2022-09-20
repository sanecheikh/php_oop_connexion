<?php 

class Admin
{
    private Sql $db;

    public function __construct()
    {
        $this->db = new Sql();
    }

    public function __call($name, $arguments)
    {
        echo "La methode $name n'est pas accessible";
        echo "Les arguments passés sont  ".implode('/',$arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        
    }

    public function __get($name)
    {
        echo "La variable  $name n'existe pas";
    }

    public function __set($name, $value)
    {
        echo "Vous essayez de mettre la valeur $value a la variable  $name";
    }

    public function __toString()
    {
        return "Vous tentez d'afficher un objet";
    }

    public function __invoke($arguments)
    {
        echo "Vous tentez d'utiliser un objet comme fonction avec pour argumets $arguments";
    }

    public function __clone()
    {
        //invoqué lors du clonage d'objet

    }
    public function ajouterEtablissementHasEnseignant(string $idEtablissement, string $idProf):bool
    {
        $requete = "SELECT id_etablissement,id_enseignant FROM ETABLISSEMENTS_has_UTILISATEUR WHERE id_etablissement = '$idEtablissement' AND id_enseignant = '$idProf';";
        $condition = $this->db->lister($requete);

        if(empty($condition))
        {
            $requete = "INSERT INTO ETABLISSEMENTS_has_UTILISATEUR(id_etablissement,id_enseignant) VALUES ('$idEtablissement','$idProf');";
            $this->db->inserer($requete);
            return true;
        }
        return false;
        
    }
    
    public function ajouterEtablissement(string $nom, string $ville, string $idProf=null)
    {

        // Creation d'une ligne etablissements
            $requete = "INSERT INTO etablissements (nom_etablissement,ville) VALUES ('$nom','$ville');";
            $this->db->inserer($requete);
    }

    public function ajouterEnseignant(string $identifiant, string $password, string $nom , string $prenom,string $mail)
    {
        // Generation unique token et date
        $date = date("Y-m-d H:i:s");
        $token = bin2hex(random_bytes(50)); 

        // Creation d'une ligne compte pour gerer token et validation email
        $requete = "INSERT INTO comptes(creation_compte,envoi_email,token,email_verification,email) VALUES ('$date','$date','$token','0','$mail');";
        $this->db->inserer($requete);

        // Creation d'une ligne professeurs
        $requete = "SELECT id_compte FROM comptes WHERE email = '$mail';";
        $idCompte = $this->db->lister($requete);
        $idCompte= $idCompte[0]['id_compte'];
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $requete = "INSERT INTO enseignants (identifiant,password,nom,prenom,email,id_compte) VALUES ('$identifiant','$passwordHash','$nom','$prenom','$mail','$idCompte');";
        $this->db->inserer($requete);

        //Envoi email contenant le token + email pour la verification
        $url = $_SERVER['HTTP_ORIGIN'] . dirname($_SERVER['REQUEST_URI']) . "/index.php?email=$mail&token=$token";
        $toEmail = $mail;
        $fromEmail = 'admin@contact.com';
        $messageEmail = "$url Clicker pour verifier l'email";
        $sujetEmail = 'Verify Your Email Address';
        $headers = "From: $fromEmail\n"; 
        $headers .= "MIME-Version: 1.0\n"; 
        $headers .= "Content-type: text/html; charset=iso-8859-1\n"; 

        sendMail($toEmail,$fromEmail,$sujetEmail,$messageEmail,$headers);

    }

    public function ajouterEleve(string $identifiant, string $password, string $nom , string $prenom,string $mail, string $id_promotion)
    {
        // Generation unique token et date
        $date = date("Y-m-d H:i:s");
        $token = bin2hex(random_bytes(50)); 

        // Creation d'une ligne compte pour gerer token et validation email
        $requete = "INSERT INTO comptes(creation_compte,envoi_email,token,email_verification,email) VALUES ('$date','$date','$token','0','$mail');";
        $this->db->inserer($requete);

        // Creation d'une ligne eleves
        $requete = "SELECT id_compte FROM comptes WHERE email = '$mail';";
        $idCompte = $this->db->lister($requete);
        $idCompte= $idCompte[0]['id_compte'];
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $requete = "INSERT INTO eleves (identifiant,password,nom,prenom,email,id_promotion,id_compte) VALUES ('$identifiant','$passwordHash','$nom','$prenom','$mail','$id_promotion','$idCompte');";
        $this->db->inserer($requete);

        //Envoi email contenant le token + email pour la verification
        $url = $_SERVER['HTTP_ORIGIN'] . dirname($_SERVER['REQUEST_URI']) . "/index.php?email=$mail&token=$token";
        $toEmail = $mail;
        $fromEmail = 'admin@contact.com';
        $messageEmail = "$url Clicker pour verifier l'email";
        $sujetEmail = 'Verify Your Email Address';
        $headers = "From: $fromEmail\n"; 
        $headers .= "MIME-Version: 1.0\n"; 
        $headers .= "Content-type: text/html; charset=iso-8859-1\n"; 

        sendMail($toEmail,$fromEmail,$sujetEmail,$messageEmail,$headers);

    }

    public function verifUtilisateur(string $mail, string $token)
    {
        $requete = "SELECT envoi_email FROM comptes WHERE token = '$token' AND email = '$mail';";
        $dateNow = date("Y-m-d H:i:s");
        $resultat = $this->db->lister($requete);
        $dateToken =  $resultat[0]['envoi_email'];
        if(dateDifference($dateNow,$dateToken) === 0){
            $requete = "UPDATE comptes SET email_verification='1' WHERE token = '$token' AND email = '$mail'";
            $this->db->inserer($requete);
            echo "Votre email est validé";
        }
        else
        {
            echo "PLus de 24h sont passé, le lien n'est plus validé";
        }

    }

     public function modifierUtilisateur()
    {

    }
    public function supprimerUtilisateur()
    {

    }
    public function connecterUtilisateur():bool
    {

        $requeteLogin = "SELECT password FROM utilisateurs WHERE mail='$this->mail'";
        $resultatLogin = $this->db->lister($requeteLogin);

        if (count($resultatLogin) > 0) {
            // Traitement pour vérifier le mot de passe
            $resultatPassword = $resultatLogin[0]['password'];

            if (password_verify($this->password, $resultatPassword)) {
                $_SESSION['login'] = true;
                $messageEmail = $this->mail . ' vous êtes connecté !';
                sendMail($this->mail, 'contact@ceppic-php-file-rouge.fr', 'Login Success', $messageEmail);
                return true;
            } else {
                $_SESSION['login'] = false;
                return false;
            }
        }

        return false;
    }

    public function deconncterUtilisateur()
    {

    } 

}