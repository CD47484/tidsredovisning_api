<?php

declare (strict_types=1);
require_once'../src/activities.php';

/**
 * Funktion för att testa alla aktiviteter
 * @return string html-sträng med resultatet av alla tester
 */
function allaActivityTester(): string {
    // Kom ihåg att lägga till alla funktioner i filen!
    $retur = "";
    $retur .= test_hamtaAllaAktiviteter();
    $retur .= test_HamtaEnAktivitet();
    $retur .= test_SparaNyAktivitet();
    $retur .= test_UppdateraAktivitet();
    $retur .= test_RaderaAktivitet();

    return $retur;
}

/**
 * Funktion för att testa en enskild funktion
 * @param string $funktion namnet (utan test_) på funktionen som ska testas
 * @return string html-sträng med information om resultatet av testen eller att testet inte fanns
 */
function testActivityFunction(string $funktion): string {
    if (function_exists("test_$funktion")) {
        return call_user_func("test_$funktion");
    } else {
        return "<p class='error'>Funktionen test_$funktion finns inte.</p>";
    }
}

/**
 * Tester för funktionen hämta alla aktiviteter
 * @return string html-sträng med alla resultat för testerna 
 */
function test_hamtaAllaAktiviteter(): string {
    $retur = "<h2>test_hamtaAllaAktiviteter</h2>";
    try {
        $svar=hamtaAllaAktiviteter();

        //kontrollerar statuskoden
        if(!$svar->getStatus()===200){
            $retur .="<p class='error'>Felaktig statuskod förväntades 200 fick {$svar->getStatus()}</p>";
        } else {
            $retur .="<p class='ok'> Korrekt statuskod 200</p>";
        }
    
        //kontrollerar att ingen aktivitet är tom
        foreach ($svar->getContent()->activities as $aktivitet) {
            if(!isset($aktivitet->id)){
                $retur .="<p class='error'>Egenskapen id saknas</p>";
                break;
            }
            if(!isset($aktivitet->activity)){
                $retur .="<p class='error'>Egenskapen activity saknas</p>";
                break;
            }
        
        }
    }catch (Exception $ex) {
        $retur .="<p class='error'>Något gick fel, meddelandet säger {$ex->getMessage()}</p>";
    }
    

    return $retur;
}

/**
 * Tester för funktionen hämta enskild aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_HamtaEnAktivitet(): string {
    $retur = "<h2>test_HamtaEnAktivitet</h2>";
    try {
        //Testa negativt tal
        $svar= hamtaEnskildAktivitet(-1);
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'>Hämta enskild med negativt tal ger förväntat svar 400</p>";
        } else {
            $retur .="<p class='error'>Hämta enskild med negativt tal ger {$svar->getStatus()} " 
            ."inte förväntat svar 400</p>";
        }
        //Testa för stort tal
        $svar= hamtaEnskildAktivitet(100);
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'>Hämta enskild med för stort tal ger förväntat svar 400</p>";
        } else {
            $retur .="<p class='error'>Hämta enskild med för stort (100) tal ger {$svar->getStatus()} " 
            ."inte förväntat svar 400</p>";
        }
        //Testa bokstäver
        $svar= hamtaEnskildAktivitet((int)"sju");
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'>Hämta enskild med bokstäver ger förväntat svar 400</p>";
        } else {
            $retur .="<p class='error'>Hämta enskild med bokstäver ('sju') ger {$svar->getStatus()} " 
            ."inte förväntat svar 400</p>";
        }
        //Testa giltigt tal
        $svar= hamtaEnskildAktivitet(2);
        if($svar->getStatus()===200) {
            $retur .="<p class='ok'>Hämta enskild med 2 ger förväntat svar 200</p>";
        } else {
            $retur .="<p class='error'>Hämta enskild med 2 ger {$svar->getStatus()} " 
            ."inte förväntat svar 200</p>";
        }

    } catch (Exception $exc) {
        $retur .="<p class='error'>Något gick fel, meddelandet säger {$exc->getMessage()}</p>";
    }

    return $retur;
}

/**
 * Tester för funktionen spara aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_SparaNyAktivitet(): string {
    $retur = "<h2>test_SparaNyAktivitet</h2>";

    //testa tom aktivitet
    $aktivitet="";
    $svar=sparaNyAktivitet($aktivitet);
    if($svar->getStatus()===400) {
        $retur .="<p class='ok'>Spara tom aktivitet misslyckades som förväntat</p>";
    } else {
        $retur .= "<p class='error'>Spara tom aktivitet returnerade {$svar->getStatus()} förväntades 400</p>";
    }

    //testa lägg till 
    $db=connectDb();
    $db->beginTransaction();
    $aktivitet="Nizze";
    $svar= sparaNyAktivitet($aktivitet);
    if($svar->getStatus()===200){
        $retur .="<p class='ok'>Spara aktivitet misslyckades som förväntat</p>";
    } else {
        $retur .= "<p class='error'>Spara aktivitet returnerade {$svar->getStatus()} förväntades 200</p>";
    }
    $db->rollback();

    //testa lägg till samma
    $db->beginTransaction();
    $aktivitet="Nizze";
    $svar= sparaNyAktivitet($aktivitet); //Spara första gågnen, borde lyckas
    $svar= sparaNyAktivitet($aktivitet); //Faktiskst test, funkar det andra gången
    if($svar->getStatus()===400){ 
        $retur .="<p class='ok'>Spara aktivitet två gånger misslyckades som förväntat</p>";
    } else {
        $retur .= "<p class='error'>Spara aktivitet två gånger returnerade {$svar->getStatus()} förväntades 400</p>";
    }
    $db->rollback();

    return $retur;
}

/**
 * Tester för uppdatera aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_UppdateraAktivitet(): string {
    $retur = "<h2>test_UppdateraAktivitet</h2>";
    try{
    //testa uppdatera med ny text i aktivitet
    $db=connectDb();
    $db->beginTransaction(); // Skapa en transaktion för att inte rådda till databasen i onödan
    $nyPost=sparaNyAktivitet("Nizze");   // Skapa en ny post som vi kan rådda med
    if($nyPost->getStatus()!==200) {
        throw new Exception("Skapa ny post misslyckades", 10001);
    }
    $uppdateringsId=(int) $nyPost->getContent()->id; // Den nya postens id
    $svar=uppdateraAktivitet($uppdateringsId, "Pelle");  // Prova att uppdatera
    if($svar->getStatus()===200 && $svar->getContent()->result===true) {
        $retur .="<p class='ok'>Uppdatera aktivitet lyckades</p>";
    } else {
        $retur .="<p class='error'>Uppdatera aktivitet misslyckades "; 
        if(isset($svar->getContent()->result)) { 
        $retur .=var_export($svar->getContent()->result). " returnerades istället för förväntat 'true'";
        } else {
            $retur .="{$svar->getstatus()} returnerades istället för förväntat 200";
        }
        $retur .="</p>";
    }
    $db->rollBack();

    //Testa uppdatera med samma text i aktivitet
    $db->beginTransaction(); // Skapa en transaktion för att inte rådda till databasen i onödan
    $nyPost=sparaNyAktivitet("Nizze");   // Skapa en ny post som vi kan rådda med
    if($nyPost->getStatus()!==200) {
        throw new Exception("Skapa ny post misslyckades", 10001);
    }
    $uppdateringsId=(int) $nyPost->getContent()->id; // Den nya postens id
    $svar=uppdateraAktivitet($uppdateringsId, "Nizze");  // Prova att uppdatera
    if($svar->getStatus()===200 && $svar->getContent()->result===false) {
        $retur .="<p class='ok'>Uppdatera aktivitet med samma text lyckades</p>";
    } else {
        $retur .="<p class='error'>Uppdatera aktivitet med samma text misslyckades "; 
        if(isset($svar->getContent()->result)) { 
        $retur .=var_export($svar->getContent()->result). " returnerades istället för förväntat 'false'";
        } else {
            $retur .="{$svar->getstatus()} returnerades istället för förväntat 200";
        }
        $retur .="</p>";
    }
    $db->rollBack();

    //Testa med tom aktivitet
    $db->beginTransaction(); // Skapa en transaktion för att inte rådda till databasen i onödan
    $nyPost=sparaNyAktivitet("Nizze");   // Skapa en ny post som vi kan rådda med
    if($nyPost->getStatus()!==200) {
        throw new Exception("Skapa ny post misslyckades", 10001);
    }
    $uppdateringsId=(int) $nyPost->getContent()->id; // Den nya postens id
    $svar=uppdateraAktivitet($uppdateringsId, "");  // Prova att uppdatera
    if($svar->getStatus()===400) {
        $retur .="<p class='ok'>Uppdatera aktivitet med tom misslyckades som förväntat</p>";
    } else {
        $retur .="<p class='error'>Uppdatera aktivitet med tom returnerade "
        . "{$svar->getstatus()} istället för förväntat 400</p>"; 
    }
    $db->rollBack();

    //testa med ogiltigt id(-1)
    $db->beginTransaction(); // Skapa en transaktion för att inte rådda till databasen i onödan
    $uppdateringsId= -1; // Den nya postens id
    $svar=uppdateraAktivitet($uppdateringsId, "Test");  // Prova att uppdatera
    if($svar->getStatus()===400) {
        $retur .="<p class='ok'>Uppdatera aktivitet med ogiltigt id (-1) misslyckades som förväntat</p>";
    } else {
        $retur .="<p class='error'>Uppdatera aktivitet med ogiltigt id (-1) returnerade "
        . "{$svar->getstatus()} istället för förväntat 400</p>"; 
    }
    $db->rollBack();

    //testa med obefintligt id(100)
    $db->beginTransaction(); // Skapa en transaktion för att inte rådda till databasen i onödan
    $uppdateringsId= 100; // Den nya postens id
    $svar=uppdateraAktivitet($uppdateringsId, "Test");  // Prova att uppdatera
    if($svar->getStatus()===200 && $svar->getContent()->result===false) {
        $retur .="<p class='ok'>Uppdatera aktivitet med obefintligt id (100) misslyckades som förväntat</p>";
    } else {
        $retur .="<p class='error'>Uppdatera aktivitet med obefintligtogiltigt id (100) returnerade ";
        if(isset($svar->getContent()->result)) { 
            $retur .=var_export($svar->getContent()->result). " returnerades istället för förväntat 'false'";
            } else {
                $retur .="{$svar->getstatus()} returnerades istället för förväntat 200";
            }
            $retur .="</p>";
    }
    $db->rollBack();

     //Testa cipis bugg - testa med mellanslag som aktivitet
    $db->beginTransaction(); // Skapa en transaktion för att inte rådda till databasen i onödan
    $nyPost=sparaNyAktivitet("Nizze");   // Skapa en ny post som vi kan rådda med
    if($nyPost->getStatus()!==200) {
        throw new Exception("Skapa ny post misslyckades", 10001);
    }
    $uppdateringsId=(int) $nyPost->getContent()->id; // Den nya postens id
    $svar=uppdateraAktivitet($uppdateringsId, " ");  // Prova att uppdatera
    if($svar->getStatus()===400) {
        $retur .="<p class='ok'>Uppdatera aktivitet med mellanslag lyckades som förväntat</p>";
    } else {
        $retur .="<p class='error'>Uppdatera aktivitet med mellanslag returnerade "
        . "{$svar->getstatus()} istället för förväntat 400</p>"; 
    }
    $db->rollBack();

    } catch (Exception $ex) {
        $db->rollBack();
        if($ex->getCode()===10001){
            $retur .= "<p class='error'>Spara ny post misslyckades, uppdatera går inte att testa!!!</p>";
        } else {
            $retur .= "<p class='error'>Fel inträffande:<br>{$ex->getMessage()}</p>";
        }
    }
    return $retur;
}

/**
 * Tester för funktionen radera aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_RaderaAktivitet(): string {
    $retur = "<h2>test_RaderaAktivitet</h2>";
try{
    //Testa felaktigt id(-1)
    $svar= raderaAktivitet(-1);
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'>Radera post med negativt tal ger förväntat svar 400</p>";
        } else {
            $retur .="<p class='error'>Radera post med negativt tal ger {$svar->getStatus()} " 
            ."inte förväntat svar 400</p>";
        }

    //Testa felaktigt id(sju)
    $svar= raderaAktivitet((int)"sju");
    if($svar->getStatus()===400) {
        $retur .="<p class='ok'>Radera post med felaktigt id ('sju') ger förväntat svar 400</p>";
    } else {
        $retur .="<p class='error'>Radera post med felaktigt id ('sju') ger {$svar->getStatus()} " 
        ."inte förväntat svar 400</p>";
    }
    //Testa id som inte finns (100)
    $svar= raderaAktivitet(1000);
        if($svar->getStatus()===200 && $svar->getContent()->result===false) {
            $retur .="<p class='ok'>Radera post med id som inte finns (1000) ger förväntat svar 200"
            . " inte result=false</p>";
        } else {
            $retur .="<p class='error'>Radera post med id som inte finns (1000) ger {$svar->getStatus()} " 
            ." inte förväntat svar 200</p>";
        }
    //Testa radera nyskapat id
    $db = connectDb();
    $db->beginTransaction(); // Skapa en transaktion för att inte rådda till databasen i onödan
    $nyPost=sparaNyAktivitet("Nizze");   // Skapa en ny post som vi kan rådda med
    if($nyPost->getStatus()!==200) {
        throw new Exception("Skapa ny post misslyckades", 10001);
    }
    $nyttId=(int) $nyPost->getContent()->id; // Den nya postens id
    $svar=raderaAktivitet($nyttId);
    if($svar->getStatus()===200 && $svar->getContent()->result===true) {
        $retur .="<p class='ok'>Radera post med nyskapat id ger förväntat svar 200"
        . " inte result=true</p>";
    } else {
        $retur .="<p class='error'>Radera post med nyskapat id ger {$svar->getStatus()} " 
        ." inte förväntat svar 200</p>";
    }
    $db->rollBack();

} catch (Exception $ex) {
    $db->rollBack();
        if($ex->getCode()===10001){
            $retur .= "<p class='error'>Radera ny post misslyckades, radera går inte att testa!!!</p>";
        } else {
            $retur .= "<p class='error'>Fel inträffande:<br>{$ex->getMessage()}</p>";
        }
}
    return $retur;
}
