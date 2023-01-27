<?php

declare (strict_types=1);
require_once __DIR__ .'/funktioner.php';

/**
 * Läs av rutt-information och anropa funktion baserat på angiven rutt
 * @param Route $route Rutt-information
 * @param array $postData Indata för behandling i angiven rutt
 * @return Response
 */
function activities(Route $route, array $postData): Response {
 //   var_dump($route, $postData);
    try {
        if (count($route->getParams()) === 0 && $route->getMethod() === RequestMethod::GET) {
            return hamtaAllaAktiviteter();
        }
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::GET) {
            return hamtaEnskildAktivitet((int) $route->getParams()[0]);
        }
        if (isset($postData["activity"]) && (count($route->getParams()) === 0 && $route->getMethod() === RequestMethod::POST)) {
            return sparaNyAktivitet((string) $postData["activity"]);
        }
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::PUT) {
            return uppdateraAktivitet((int) $route->getParams()[0], (string) $postData["activity"]);
        }
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::DELETE) {
            return raderaAktivitet((int) $route->getParams()[0]);
        }
    } catch (Exception $exc) {
        return new Response($exc->getMessage(), 400);
    }

    return new Response("Okänt anrop", 400);
}

/**
 * Returnerar alla aktiviteter som finns i databasen
 * @return Response
 */
function hamtaAllaAktiviteter(): Response {
    //koppla mot databasen
    $db=connectDb();
    //Hämta alla poster från tabellen
    $resultat=$db->query("SELECT ID, kategori from kategorier");
    //lägga in posterna i en array
    $retur=[];
    while($row=$resultat->fetch()){
        $post = new stdClass();
        $post->id=$row['ID'];
        $post->activity=$row['kategori'];
        $retur[]=$post;
    }

    $out=new stdClass();
    $out->activities=$retur;
    //return svaret
    return new Response($out, 200);
}

/**
 * Returnerar en enskild aktivitet som finns i databasen
 * @param int $id Id för aktiviteten
 * @return Response
 */
function hamtaEnskildAktivitet(int $id): Response {
    //kontrollera indata
    $kollatID=filter_var($id,FILTER_VALIDATE_INT);
    if(!$kollatID || $kollatID<1){
        $out=new stdClass();
        $out->error=["Felaktig indata","$id är inget heltal"];
        return new Response($out,400);
    }
    //koppla databas och hämta post
    $db=connectDb();
    $stmt=$db->prepare("SELECT id, kategori FROM kategorier WHERE id=:id");
    if (!$stmt->execute(["id"=>$kollatID])){
        $out=new stdClass();
        $out->error=["Fel vid läsning från databasen",implode(",", $stmt->errorInfo())];
        return new Response($out,400);
    }

    // Sätt utdata och returnera
    if($row=$stmt->fetch()){
        $out=new stdClass();
        $out->id=$row["id"];
        $out->activity=$row["kategori"];
        return new Response($out);
    } else {
        $out=new stdClass();
        $out->error=["Hittade ingen post med id=$kollatID"];
        return new Response($out, 400);
    }

}

/**
 * Lagrar en ny aktivitet i databasen
 * @param string $aktivitet Aktivitet som ska sparas
 * @return Response
 */
function sparaNyAktivitet(string $aktivitet): Response {
    //kontrollera indata
    $kontrolleraAktivitet=trim($aktivitet);
    $kontrolleraAktivitet=filter_var($kontrolleraAktivitet, FILTER_SANITIZE_ENCODED);
    if($kontrolleraAktivitet==="") {
        $out=new stdClass();
        $out->error=["Fel vid spara", "activity kan inte vara tom"];
        return new Response($out,400);
    }
    try{
    //koppla mot databas
    $db=connectdb();
    //spara till databasen 
    $stmt=$db->prepare("INSERT INTO kategorier (kategori) VALUES (:activity)");
    $stmt->execute(["activity"=>$kontrolleraAktivitet]);
    $antalPoster=$stmt->rowCount();
    
    //returnera svaret 
    if($antalPoster>0) {
        $out=new stdClass();
        $out->message=["Spara lyckades", "$antalPoster post(er) lades till"];
        $out->id=$db->lastInsertId();
        return new Response($out);
    } else {
        $out=new stdClass();
        $out->error=["Något gick fel vid spara", implode(",", $db->errorInfo())];
        return new Response($out,400);
    }
    } catch(exception $ex) {
        $out=new stdClass();
        $out->error=["Något gick fel vid spara", $ex->getMessage()];
        return new Response($out,400);
    }

}

/**
 * uppdateraAktivitetr angivet id med ny text
 * @param int $id Id för posten som ska uppdateraAktivitets
 * @param string $aktivitet Ny text
 * @return Response
 */
function uppdateraAktivitet(int $id, string $aktivitet): Response {
    //kontrollera indata
    $kollatID=filter_var($id,FILTER_VALIDATE_INT);
    if(!$kollatID || $kollatID<1){
        $out=new stdClass();
        $out->error=["Felaktig indata","$id är inget heltal"];
        return new Response($out,400);
    }
    $kontrolleraAktivitet=trim($aktivitet);
    $kontrolleraAktivitet=filter_var($kontrolleraAktivitet, FILTER_SANITIZE_ENCODED);
    if($kontrolleraAktivitet==="") {
        $out=new stdClass();
        $out->error=["Fel vid uppdateraAktivitet", "activity kan inte vara tom"];
        return new Response($out,400);
    }
    try{
    //koppla databas
    $db=connectdb();

    //uppdateraAktivitet post
    $stmt=$db->prepare("UPDATE kategorier"
    ." SET kategori=:activity" 
    ." WHERE id=:id");
    $stmt->execute(["activity"=>$kontrolleraAktivitet, "id" => $kollatID]);
    $antalPoster=$stmt->rowCount();
    //returnera svar
    $out= new stdClass();
    if($antalPoster>0) {
        $out->result = true;
        $out->message=["Uppdatering lyckades", "$antalPoster poster uppdateraAktivitetdes"];
    } else {
        $out->result = false;
        $out->error=["Uppdatering lyckades", "0 poster uppdateraAktivitetdes"];
    }

        return new Response($out,200);
    } catch(exception $ex) {
        $out=new stdClass();
        $out->error=["Något gick fel vid uppdateraAktivitet", $ex->getMessage()];
        return new Response($out,400);
    }
}
/**
 * raderaAktivitetr en aktivitet med angivet id
 * @param int $id Id för posten som ska raderaAktivitets
 * @return Response
 */
function raderaAktivitet(int $id): Response {
    //Kontrollera id
    $kollatID=filter_var($id,FILTER_VALIDATE_INT);
    if(!$kollatID || $kollatID<1){
        $out=new stdClass();
        $out->error=["Felaktigt id","$id är inget giltigt heltal"];
        return new Response($out,400);
    }

    try{
    //Koppla mot databas
    $db=connectdb();
    
    //Skicka raderaAktivitet-kommando
    $stmt=$db->prepare("DELETE FROM kategorier"
        ." WHERE id=:id");
    $stmt->execute(["id" => $kollatID]);
    $antalPoster=$stmt->rowCount();
    //Kontrollera databas-svar och skapa utdata-svar
    $out=new stdClass();
    if($antalPoster>0) {
        $out->result=true;
        $out->message=["raderaAktivitet lyckades", "$antalPoster post(er) raderaAktivitetdes"];
    } else {
        $out->result=false;
        $out->message=["raderaAktivitet misslyckades", "Inga poster raderaAktivitetdes"];
    }
    

    return new Response($out);
    } catch(exception $ex) {
        $out=new stdClass();
        $out->error=["Något gick fel vid raderaAktivitet", $ex->getMessage()];
        return new Response($out,400);
}
}