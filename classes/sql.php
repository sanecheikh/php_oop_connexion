<?php class Sql
{
    private $serverName = "localhost";
    private $userName = "root";
    private $userPassword = "";
    private $database = "dbetablissement";
    private $connexion;
    
    public function __construct()
    {
        try {
            //code...
            $this->connexion = new PDO("mysql:host=$this->serverName;dbname=$this->database", $this->userName, $this->userPassword);
            $this->connexion->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            die("Erreur : ".$e->getMessage() );
        }  
    }

    public function inserer($requete)
    {
        try {
            $this->connexion->exec($requete);
        }
        catch(Exception $e) {
            $myfile = fopen("errorSql.txt", "a") or die("Unable to open file!");
            $txt = $e."\n";
            fwrite($myfile, $txt);
            fclose($myfile);
        }    
    }

    public function lister($requete)
    {
        try {
            return $this->connexion->query($requete)->fetchAll();
         }
         catch(Exception $e) {
             $myfile = fopen("errorSql.txt", "a") or die("Unable to open file!");
             $txt = $e."\n";
             fwrite($myfile, $txt);
             fclose($myfile);
         }    
        
    }

    public function __destruct()
    {
        $this->connexion = null;
    }

    
}