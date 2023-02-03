<?php

declare (strict_types=1);
require_once  __DIR__ . '/../src/compilations.php';
/**
 * Funktion för att testa alla aktiviteter
 * @return string html-sträng med resultatet av alla tester
 */
function testCompilationFunction(string $funktion): string {
    if (function_exists("test_$funktion")) {
        return call_user_func("test_$funktion");
    } else {
        return "<p class='error'>Funktionen $funktion kan inte testas.</p>";
    }
}

/**
 * Tester för funktionen hämta en sammmanställning av uppgifter mellan två datum
 * @return string html-sträng med alla resultat för testerna 
 */
function test_HamtaSammanstallning(): string {
    $retur = "<h2>test_HamtaSammanstallning</h2>";
    // Testa felaktig ordning på datum
    $svar = hamtaSammanstallning(new DateTimeImmutable(), new DateTimeImmutable("1970-01-01"));
    if ($svar->getStatus() === 400) {
        $retur .= "<p class='ok'>Hämta sammanställning med felaktig ordning på datum returnerade 400 som förväntat</p>";
    } else {
        $retur .= "<p class='error'>Hämta sammanställning med felaktig ordning på datum returnerade {$svar->getStatus()} "
                . "istället för 400 som förväntat</p>";
    }

    // Testa utan resultat
    $svar = hamtaSammanstallning(new DateTimeImmutable("1970-01-01"), new DateTimeImmutable("1970-01-01"));
    if ($svar->getStatus() === 200) {
        if ($svar->getContent()->tasks === []) {
            $retur .= "<p class='ok'>Hämta sammanställning utan poster returnerade 200 och tom array som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Hämta sammanställning utan poster returnerade "
                    . print_r($svar->getContent()->tasks, true)
                    . " istället för tom array som förväntat</p>";
        }
    } else {
        $retur .= "<p class='error'>Hämta sammanställning  utan poster returnerade returnerade {$svar->getStatus()} "
                . "istället för 200 som förväntat</p>";
    }

    // Testa ok
    $svar = hamtaSammanstallning(new DateTimeImmutable("1970-01-01"), new DateTimeImmutable());
    if ($svar->getStatus() === 200) {
            $retur .= "<p class='ok'>Hämta sammanställning returnerade 200 som förväntat</p>";
    } else {
        $retur .= "<p class='error'>Hämta sammanställning returnerade {$svar->getStatus()} "
                . "istället för 200 som förväntat</p>";
    }

    return $retur;
}
