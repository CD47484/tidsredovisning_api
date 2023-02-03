<?php

declare (strict_types=1);
require_once __DIR__ . '/../src/tasks.php';

/**
 * Funktion för att testa alla aktiviteter
 * @return string html-sträng med resultatet av alla tester
 */
function allaTaskTester(): string {
// Kom ihåg att lägga till alla testfunktioner
    $retur = "<h1>Testar alla uppgiftsfunktioner</h1>";
    $retur .= test_HamtaEnUppgift();
    $retur .= test_HamtaUppgifterSida();
    $retur .= test_RaderaUppgift();
    $retur .= test_SparaUppgift();
    $retur .= test_UppdateraUppgifter();
    return $retur;
}

/**
 * Funktion för att testa en enskild funktion
 * @param string $funktion namnet (utan test_) på funktionen som ska testas
 * @return string html-sträng med information om resultatet av testen eller att testet inte fanns
 */
function testTaskFunction(string $funktion): string {
    if (function_exists("test_$funktion")) {
        return call_user_func("test_$funktion");
    } else {
        return "<p class='error'>Funktionen $funktion kan inte testas.</p>";
    }
}

/**
 * Tester för funktionen hämta uppgifter för ett angivet sidnummer
 * @return string html-sträng med alla resultat för testerna 
 */
function test_HamtaUppgifterSida(): string {
    $retur = "<h2>test_HamtaUppgifterSida</h2>";
    try {
        // Testa hämta felaktigt sidnummer (-1) => 400
        $svar = hamtaSida(-1);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Hämta felaktigt sidnummer (-1) gav förväntat svar 400</p>";
        } else {
            $retur .= "<p class='error'>Hämta felaktigt sidnummer (-1) gav {$svar->getStatus()} "
                    . "istället för förväntat svar 400</p>";
        }

        // Testa hämta giltigt sidnummer (1) => 200 + rätt egenskaper
        $svar = hamtaSida(1);
        if ($svar->getStatus() !== 200) {
            $retur .= "<p class='error'>Hämta giltigt sidnummer (1) gav {$svar->getStatus()} "
                    . "istället för förväntat svar 200</p>";
        } else {
            $retur .= "<p class='ok'>Hämta giltigt sidnummer (1) gav förväntat svar 200</p>";
            $result = $svar->getContent()->tasks;
            foreach ($result as $task) {
                if (!isset($task->id)) {
                    $retur .= "<p class='error'>Egenskapen id saknas</p>";
                    break;
                }
                if (!isset($task->activityId)) {
                    $retur .= "<p class='error'>Egenskapen activityId saknas</p>";
                    break;
                }
                if (!isset($task->activity)) {
                    $retur .= "<p class='error'>Egenskapen activity saknas</p>";
                    break;
                }
                if (!isset($task->date)) {
                    $retur .= "<p class='error'>Egenskapen date saknas</p>";
                    break;
                }
                if (!isset($task->time)) {
                    $retur .= "<p class='error'>Egenskapen time saknas</p>";
                    break;
                }
            }
        }
        // Testa hämta för stor sidnr => 200 + tom array
        $svar = hamtaSida(100);
        if ($svar->getStatus() !== 200) {
            $retur .= "<p class='error'>Hämta för stort sidnummer (100) gav {$svar->getStatus()} "
                    . "istället för förväntat svar 200</p>";
        } else {
            $retur .= "<p class='ok'>Hämta för stort sidnummer (100) gav förväntat svar 200</p>";
            $resultat = $svar->getContent()->tasks;
            if (!$resultat === []) {
                $retur .= "<p class='error'>Hämta för stort sidnummer ska innehålla en tom array för tasks<br>"
                        . print_r($resultat, true) . " <br>returnerades</p>";
            }
        }
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }
    return $retur;
}

/**
 * Test för funktionen hämta uppgifter mellan angivna datum
 * @return string html-sträng med alla resultat för testerna
 */
function test_HamtaAllaUppgifterDatum(): string {
    $retur = "<h2>test_HamtaAllaUppgifterDatum</h2>";
    // Testa fel ordning på datum => 400
    $datum1 = new DateTimeImmutable();
    $datum2 = new DateTime("yesterday");
    $svar = hamtaDatum($datum1, $datum2);
    if ($svar->getStatus() === 400) {
        $retur .= "<p class='ok'>Hämta fel ordning på datum gav förväntat svar 400</p>";
    } else {
        $retur .= "<p class='error'>Hämta fel ordning på datum gav {$svar->getStatus()} "
                . "istället för förväntat svar 400</p>";
    }

    // Testa datum utan poster => 200 och tom array för tasks
    $datum1 = new DateTimeImmutable("1970-01-01");
    $datum2 = new DateTimeImmutable("1970-01-01");
    $svar = hamtaDatum($datum1, $datum2);
    if ($svar->getStatus() !== 200) {
        $retur .= "<p class='error'>Hämta datum (1970-01-01 -- 1970-01-01) gav {$svar->getStatus()} "
                . "istället för förväntat svar 200</p>";
    } else {
        $retur .= "<p class='ok'>Hämta datum (1970-01-01 -- 1970-01-01) gav förväntat svar 200</p>";
        $resultat = $svar->getContent()->tasks;
        if (!$resultat === []) {
            $retur .= "<p class='error'>Hämta datum (1970-01-01 -- 1970-01-01) ska innehålla en tom array för tasks<br>"
                    . print_r($resultat, true) . " <br>returnerades</p>";
        }
    }

    // Testa giltiga datum med poster => 200 och giltiga egenskaper
    $datum1 = new DateTimeImmutable("1970-01-01");
    $datum2 = new DateTimeImmutable();
    $svar = hamtaDatum($datum1, $datum2);
    if ($svar->getStatus() !== 200) {
        $retur .= "<p class='error'>Hämta poster för datum (1970-01-01 -- {$datum2->format('Y-m-d')} "
                . " gav {$svar->getStatus()} istället för förväntat svar 200</p>";
    } else {
        $retur .= "<p class='ok'>Hämta poster för datum (1970-01-01 -- {$datum2->format('Y-m-d')}"
                . " gav förväntat svar 200</p>";
        $result = $svar->getContent()->tasks;
        foreach ($result as $task) {
            if (!isset($task->id)) {
                $retur .= "<p class='error'>Egenskapen id saknas</p>";
                break;
            }
            if (!isset($task->activityId)) {
                $retur .= "<p class='error'>Egenskapen activityId saknas</p>";
                break;
            }
            if (!isset($task->activity)) {
                $retur .= "<p class='error'>Egenskapen activity saknas</p>";
                break;
            }
            if (!isset($task->date)) {
                $retur .= "<p class='error'>Egenskapen date saknas</p>";
                break;
            }
            if (!isset($task->time)) {
                $retur .= "<p class='error'>Egenskapen time saknas</p>";
                break;
            }
        }
    }

    return $retur;
}

/**
 * Test av funktionen hämta enskild uppgift
 * @return string html-sträng med alla resultat för testerna
 */
function test_HamtaEnUppgift(): string {
    $retur = "<h2>test_HamtaEnUppgift</h2>";
    try {
        // Testa negativt tal
        $svar = hamtaEnskildUppgift(-1);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Hämta enskild med negativt tal ger förväntat svar 400</p>";
        } else {
            $retur .= "<p class='error'>Hämta enskild med negativt tal ger {$svar->getStatus()} "
                    . "inte förväntat svar 400</p>";
        }
        // Testa för stort tal
        $svar = hamtaEnskildUppgift(100);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Hämta enskild med stort tal ger förväntat svar 400</p>";
        } else {
            $retur .= "<p class='error'>Hämta enskild med stort (100) tal ger {$svar->getStatus()} "
                    . "inte förväntat svar 400</p>";
        }
        // Testa bokstäver
        $svar = hamtaEnskildUppgift((int) "sju");
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Hämta enskild med bokstäver ger förväntat svar 400</p>";
        } else {
            $retur .= "<p class='error'>Hämta enskild med bokstäver ('sju') tal ger {$svar->getStatus()} "
                    . "inte förväntat svar 400</p>";
        }
        // Testa giltigt tal
        $svar = hamtaEnskildUppgift(3);
        if ($svar->getStatus() === 200) {
            $retur .= "<p class='ok'>Hämta enskild med 3 ger förväntat svar 200</p>";
        } else {
            $retur .= "<p class='error'>Hämta enskild med 3 ger {$svar->getStatus()} "
                    . "inte förväntat svar 200</p>";
        }
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}

/**
 * Test för funktionen spara uppgift
 * @return string html-sträng med alla resultat för testerna
 */
function test_SparaUppgift(): string {
    $retur = "<h2>test_SparaUppgift</h2>";
    try {
        // Testa allt OK
        $igar = new DateTimeImmutable("yesterday");
        $imorgon = new DateTimeImmutable("tomorrow");

        $postdata = ["date" => $igar->format('Y-m-d'),
            "time" => "05:00",
            "activityId" => 1,
            "description" => "Hurra vad bra"];
        $db = connectDb();
        $db->beginTransaction();
        $svar = sparaNyUppgift($postdata);
        if ($svar->getStatus() === 200) {
            $retur .= "<p class='ok'>Spara ny uppgift lyckades</p>";
        } else {
            $retur .= "<p class='error'>Spara ny uppgift misslyckades {$svar->getStatus()} "
                    . "returnerades istället för förväntat 200</p>";
        }
        $db->rollBack();

        // Testa felaktigt datum (i morgon) => 400
        $postdata["date"] = $imorgon->format("Y-m-d");
        $db->beginTransaction();
        $svar = sparaNyUppgift($postdata);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Spara ny uppgift misslyckades som förväntat (date = imorgon)</p>";
        } else {
            $retur .= "<p class='error'>Spara ny uppgift returnerade {$svar->getStatus()} "
                    . " istället för förväntat 400</p>";
        }
        $db->rollBack();

        // Testa felaktig datumformat => 400
        $postdata["date"] = $igar->format("d.m.Y");
        $db->beginTransaction();
        $svar = sparaNyUppgift($postdata);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Spara ny uppgift misslyckades som förväntat (felaktigt datumformat)</p>";
        } else {
            $retur .= "<p class='error'>Spara ny uppgift med felaktigt datumformat "
                    . "returnerade {$svar->getStatus()} istället för förväntat 400</p>";
        }
        $db->rollBack();

        // Testa datum saknas =>400
        unset($postdata["date"]);
        $db->beginTransaction();
        $svar = sparaNyUppgift($postdata);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Spara ny uppgift misslyckades som förväntat (datum saknas)</p>";
        } else {
            $retur .= "<p class='error'>Spara ny uppgift utan datum "
                    . "returnerade {$svar->getStatus()} istället för förväntat 400</p>";
        }
        $db->rollBack();

        // Testa felaktig tid (12 timmar) => 400
        $db->beginTransaction();
        $postdata["date"] = $igar->format('Y-m-d');
        $postdata["time"] = "12:00";
        $svar = sparaNyUppgift($postdata);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Spara ny uppgift misslyckades som förväntat (felaktig tid 12:00)</p>";
        } else {
            $retur .= "<p class='error'>Spara ny uppgift med felaktig tid (12:00) "
                    . "returnerade {$svar->getStatus()} istället för förväntat 400</p>";
        }
        $db->rollBack();

        // Testa felaktigt tidsformat => 400
        $db->beginTransaction();
        $postdata["time"] = "5_30";
        $svar = sparaNyUppgift($postdata);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Spara ny uppgift misslyckades som förväntat (felaktig tidsformat)</p>";
        } else {
            $retur .= "<p class='error'>Spara ny uppgift med felaktig tidsformat "
                    . "returnerade {$svar->getStatus()} istället för förväntat 400</p>";
        }
        $db->rollBack();

        // Testa tid saknas => 400
        $db->beginTransaction();
        unset($postdata["time"]);
        $svar = sparaNyUppgift($postdata);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Spara ny uppgift misslyckades som förväntat (tid saknas)</p>";
        } else {
            $retur .= "<p class='error'>Spara ny uppgift utan tid "
                    . "returnerade {$svar->getStatus()} istället för förväntat 400</p>";
        }
        $db->rollBack();

        // Testa description saknas => 200
        unset($postdata["description"]);
        $postdata["time"] = "3:15";
        $db->beginTransaction();
        $svar = sparaNyUppgift($postdata);
        if ($svar->getStatus() === 200) {
            $retur .= "<p class='ok'>Spara ny uppgift utan beskrivning lyckades</p>";
        } else {
            $retur .= "<p class='error'>Spara ny uppgift utan beskrivning "
                    . "returnerade {$svar->getStatus()} istället för förväntat 200</p>";
        }
        $db->rollBack();

        // Testa aktivitetsid felaktigt (-1) => 400
        $postdata["activityId"] = -1;
        $db->beginTransaction();
        $svar = sparaNyUppgift($postdata);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Spara ny uppgift med felaktigt aktivityId (-1) misslyckades, som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Spara ny uppgift utan felaktivt activityId (-1) "
                    . "returnerade {$svar->getStatus()} istället för förväntat 400</p>";
        }
        $db->rollBack();

        // Testa aktivitetsid som saknas (100) => 400
        $postdata["activityId"] = 100;
        $db->beginTransaction();
        $svar = sparaNyUppgift($postdata);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Spara ny uppgift med felaktigt aktivityId (100) misslyckades, som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Spara ny uppgift med felaktivt activityId (100) "
                    . "returnerade {$svar->getStatus()} istället för förväntat 400</p>";
        }
        $db->rollBack();
    } catch (Exception $ex) {
        $db->rollBack();
        $retur .= $ex->getMessage();
    }

    return $retur;
}

/**
 * Test för funktionen uppdatera befintlig uppgift
 * @return string html-sträng med alla resultat för testerna
 */
function test_UppdateraUppgifter(): string {
    $retur = "<h2>test_UppdateraUppgifter</h2>";
    try {
        $db = connectDb();
        $db->beginTransaction();
        $igar = new DateTimeImmutable("yesterday");
        $imorgon = new DateTimeImmutable("tomorrow");

        $postdata = ["date" => $igar->format('Y-m-d'),
            "time" => "05:00",
            "activityId" => 1,
            "description" => "Hurra vad bra"];
        // Skapa en ny post som vi kan manipulera
        $svar = sparaNyUppgift($postdata);
        if ($svar->getStatus() !== 200) {
            throw new Exception("Kunde inte skapa ny post. Tester för uppdatering avbryts");
        }
        $id = (int) $svar->getContent()->id;

        // Testa felaktigt datum (i morgon)
        $postdata["date"] = $imorgon->format("Y-m-d");
        $svar = uppdaterUppgift($id, $postdata);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Uppdatera med felaktigt datum (imorgon) misslyckades som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Uppdatera med felaktigt datum (imorgon) misslyckades "
                    . "{$svar->getStatus()} returnerade istället för 400</p>";
        }

        // Testa felaktigt formatterat datum
        $postdata["date"] = $igar->format("Y_m_d");
        $svar = uppdaterUppgift($id, $postdata);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Uppdatera med felaktigt formaterat datum ({$postdata['date']}) misslyckades som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Uppdatera med felaktigt formaterat datum ({$postdata['date']}) misslyckades "
                    . "{$svar->getStatus()} returnerade istället för 400</p>";
        }

        // Testa datum saknas
        unset($postdata["date"]);
        $svar = uppdaterUppgift($id, $postdata);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Uppdatera med saknar datum misslyckades som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Uppdatera med saknat datum misslyckades "
                    . "{$svar->getStatus()} returnerade istället för 400</p>";
        }

        // Testa felaktig tid (>8h)
        $postdata["date"] = $igar->format('Y-m-d');
        $postdata["time"] = "11:30";
        $svar = uppdaterUppgift($id, $postdata);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Uppdatera med för lång tid (11:30) misslyckades som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Uppdatera med för lång tid (11:30) misslyckades "
                    . "{$svar->getStatus()} returnerade istället för 400</p>";
        }

        // Testa felaktigt formatterad tid
        $postdata["time"] = "1_30";
        $svar = uppdaterUppgift($id, $postdata);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Uppdatera med felformatterad tid (1_30) misslyckades som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Uppdatera med felformatterad tid (1_30) misslyckades "
                    . "{$svar->getStatus()} returnerade istället för 400</p>";
        }

        // Testa tid saknas
        unset($postdata["time"]);
        $svar = uppdaterUppgift($id, $postdata);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Uppdatera utan tid misslyckades som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Uppdatera utan tid misslyckades "
                    . "{$svar->getStatus()} returnerade istället för 400</p>";
        }

        // Testa activityId är fel (-1)
        $postdata["time"] = "1:03";
        $postdata["activityId"] = -1;
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Uppdatera med felaktigt activityId misslyckades som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Uppdatera med felaktigt activityId misslyckades "
                    . "{$svar->getStatus()} returnerade istället för 400</p>";
        }

        // Testa activityId som inte finns (100)
        $postdata["activityId"] = 100;
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Uppdatera med activityId som inte finns (100) misslyckades som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Uppdatera med activityId som inte finns (100) misslyckades "
                    . "{$svar->getStatus()} returnerade istället för 400</p>";
        }

        // Testa activityId saknas
        unset($postdata["activityId"]);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Uppdatera utan activityId misslyckades som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Uppdatera utan activityId misslyckades "
                    . "{$svar->getStatus()} returnerade istället för 400</p>";
        }

        // Testa felaktigt id (-1)
        $postdata["activityId"] = 1;
        $svar = uppdaterUppgift(-1, $postdata);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Uppdatera uppgift med felaktigt id (-1) misslyckades som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Uppdatera uppgift med felaktigt id (-1) returnerade"
                    . "{$svar->getStatus()} istället för förväntat 400</p>";
        }

        // Testa id som inte finns (100)
        $svar = uppdaterUppgift(100, $postdata);
        if ($svar->getStatus() === 200) {
            if ($svar->getContent()->result === false) {
                $retur .= "<p class='ok'>Uppdatera uppgift som inte finns returnerade false som förväntat</p>";
            } else {
                $retur .= "<p class='error'>Uppdatera uppgift som inte finns returnerade true istället för false som förväntat</p>";
            }
        } else {
            $retur .= "<p class='error'>Uppdatera uppgift med felaktigt id (-1) returnerade"
                    . "{$svar->getStatus()} istället för förväntat 200</p>";
        }

        // Testa allt OK!
        $postdata = ["time" => "1:30", "date" => date('Y-m-d'), "activityId" => 1, "description" => "Bara ett fånigt test"];
        $svar = uppdaterUppgift($id, $postdata);
        if ($svar->getStatus() === 200) {
            if ($svar->getContent()->result === true) {
                $retur .= "<p class='ok'>Uppdatera uppgift uppdaterade post som förväntat</p>";
            } else {
                $retur .= "<p class='error'>Uppdatera uppgift returnerade false istället för true som förväntat</p>";
            }
        } else {
            $retur .= "<p class='error'>Uppdatera uppgift returnerade"
                    . "{$svar->getStatus()} istället för förväntat 200</p>";
        }
        $db->rollBack();
    } catch (Exception $ex) {
        $db->rollBack();
        $retur .= "<p class='error'>Något gick fel, tester kan inte fortsätta. {$ex->getMessage()}</p>";
    }

    return $retur;
}

function test_KontrolleraIndata(): string {
    $retur = "<h2>test_KontrolleraIndata</h2>";
    try {
        $postdata = ["time" => "1:30", "date" => date('Y-m-d'), "activityId" => 1, "description" => "Bara ett fånigt test"];
        $igar = new DateTimeImmutable("yesterday");
        $imorgon = new DateTimeImmutable("tomorrow");
        // Testa felaktigt datum (i morgon)
        $postdata["date"]=$imorgon->format('Y-m-d');
        $svar=kontrolleraIndata($postdata);
        if($svar!=="") {
                $retur .= "<p class='ok'>Testa felaktigt datum (imorgon) returnerade $svar</p>";
        } else {
                $retur .= "<p class='error'>Testa felaktigt datum (imorgon) returnerade tom sträng</p>";
        }

        // Testa felaktigt formatterat datum 
        $postdata["date"]=$imorgon->format('Y_m_d');
        $svar=kontrolleraIndata($postdata);
        if($svar!=="") {
                $retur .= "<p class='ok'>Testa felaktigt formatterat datum returnerade $svar</p>";
        } else {
                $retur .= "<p class='error'>Testa felaktigt formatterat datum returnerade tom sträng</p>";
        }
        
        // Testa utan datum
        unset($postdata["date"]);
        $svar=kontrolleraIndata($postdata);
        if($svar!=="") {
                $retur .= "<p class='ok'>Testa utan datum returnerade $svar</p>";
        } else {
                $retur .= "<p class='error'>Testa utan datum returnerade tom sträng</p>";
        }
        
        // Testa felaktig tid (11:00)
        $postdata["date"]=$igar->format('Y-m-d');
        $postdata["time"]="11:00";
        $svar=kontrolleraIndata($postdata);
        if($svar!=="") {
                $retur .= "<p class='ok'>Testa felaktig tid (11:00) returnerade $svar</p>";
        } else {
                $retur .= "<p class='error'>Testa felaktig tid (11:00) returnerade tom sträng</p>";
        }
        
        // Testa felaktigt formatterad tid
        $postdata["time"]="1.00";
        $svar=kontrolleraIndata($postdata);
        if($svar!=="") {
                $retur .= "<p class='ok'>Testa felaktigt formatterad tid (1.00) returnerade $svar</p>";
        } else {
                $retur .= "<p class='error'>Testa felaktigt formatterad tid (1.00) returnerade tom sträng</p>";
        }
        
        // Testa tid saknas
        unset($postdata["time"]);
        $svar=kontrolleraIndata($postdata);
        if($svar!=="") {
                $retur .= "<p class='ok'>Testa tid saknas returnerade $svar</p>";
        } else {
                $retur .= "<p class='error'>Testa tid saknas returnerade tom sträng</p>";
        }
        
        // Testa felaktigt activityId (-1)
        $postdata["time"]="1:30";
        $postdata["activityId"]=-1;
        $svar=kontrolleraIndata($postdata);
        if($svar!=="") {
                $retur .= "<p class='ok'>Testa felaktigt activityId (-1) returnerade $svar</p>";
        } else {
                $retur .= "<p class='error'>Testa felaktigt activityId (-1) returnerade tom sträng</p>";
        }
        
        // Testa med activityId som saknas (100)
        $postdata["activityId"]=100;
        $svar=kontrolleraIndata($postdata);
        if($svar!=="") {
                $retur .= "<p class='ok'>Testa activityId som saknas (100) returnerade $svar</p>";
        } else {
                $retur .= "<p class='error'>Testa activityId som saknas (100) returnerade tom sträng</p>";
        }
        
        // Testa med saknat activityId
        unset($postdata["activityId"]);
        $svar=kontrolleraIndata($postdata);
        if($svar!=="") {
                $retur .= "<p class='ok'>Testa utan activityId returnerade $svar</p>";
        } else {
                $retur .= "<p class='error'>Testa utan activityId returnerade tom sträng</p>";
        }
        
        // Testa OK
        $postdata["activityId"]=1;
        $svar=kontrolleraIndata($postdata);
        if($svar==="") {
                $retur .= "<p class='ok'>Testa kontrollera indata returnerade tom sträng som förväntat</p>";
        } else {
                $retur .= "<p class='error'>Testa kontrollera indata returnerade $svar "
                        . "istället för tom sträng</p>";
        }
    } catch (Exception $ex) {
    }

    return $retur;
}

/**
 * Test för funktionen radera uppgift
 * @return string html-sträng med alla resultat för testerna
 */
function test_RaderaUppgift(): string {
    $retur = "<h2>test_RaderaUppgift</h2>";
    try {
        // Testa ogiltigt tal (-1)
        $svar = raderaUppgift(-1);
        if ($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Radera uppgift med ogiltigt tal returnerade 400 som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Radera uppgift returnerade {$svar->getStatus()} istället för "
                    . " förväntat 400</p>";
        }

        // Testa ta bort post som finns
        $db = connectDb(); // Koppla databas
        $db->beginTransaction();
        $postData = ["time" => "1:00", "date" => date('Y-m-d'), "activityId" => 1];
        $svar = sparaNyUppgift($postData);
        if ($svar->getStatus() !== 200) {
            throw new Exception("Kunde inte skapa ny post, testerna avbryts!");
        }
        $nyttId = (int) $svar->getContent()->id;
        $svar = raderaUppgift($nyttId);
        if ($svar->getStatus() === 200) {
            if ($svar->getContent()->result === true) {
                $retur .= "<p class='ok'>Radera uppgift lyckades</p>";
            } else {
                $retur .= "<p class='error'>Radera uppgift returnerade false istället för "
                        . "förväntat true</p>";
            }
        } else {
            $retur .= "<p class='error'>Radera uppgift returnerade {$svar->getStatus()} istället"
                    . " för förväntat 200";
        }
        $db->rollBack();

        // Testa ta bort post som inte finns
        $svar = raderaUppgift($nyttId);
        if ($svar->getStatus() === 200) {
            if ($svar->getContent()->result === false) {
                $retur .= "<p class='ok'>Radera uppgift som inte finns lyckades</p>";
            } else {
                $retur .= "<p class='error'>Radera uppgift som inte finns returnerade true istället för "
                        . "förväntat false</p>";
            }
        } else {
            $retur .= "<p class='error'>Radera uppgift som inte finns returnerade {$svar->getStatus()} istället"
                    . " för förväntat 200";
        }
    } catch (\Exception $ex) {
        $retur .= "<p class='error'>Något gick fel: {$ex->getMessage()}</p>";
    }
    return $retur;
}