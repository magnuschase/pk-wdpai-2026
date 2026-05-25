## TEMAT

Aplikacja dla studia ceramiki Terra & Craft, sprzedającego customowe produkty przez Custom Order Wizard, z panelem administracyjnym i galerią przedmiotów

## TECHNOLOGIE

Użyte języki/technologie: Docker, GIT, HTML5, CSS, JavaScript (w tym FETCH API), PHP - obiektowy, baza danych PostgreSQL. Bez użycia frameworka i gotowych szablonów.

## ARCHITEKTURA APLIKACJI

Zastosowana architektura MVC, frontend-backend lub inna - zapewniająca bezpieczeństwo aplikacji.

## DESIGN

Aplikacja estetyczna pod względem graficznym. Responsywna, użycie CSS media queries.

## ELEMENTY APLIKACJI

Proces logowania, utrzymanie sesji, uprawnienia użytkowników (weryfikowane przez system w trakcie działania/testowania), zarządzanie użytkownikami, wylogowanie + wybrana funkcjonalność w ramach założeń do projektu.

## BAZA DANYCH

Baza danych powinna zawierać relacje między tabelami, w tym wszystkie typy relacji (jeden-do-wielu, wiele-do-wielu, jeden-do-jednego).
Należy również stworzyć:

- minimum 2 widoki (użyte złączenia z kilkoma tabelami)
- minimum 1 wyzwalacz
- minimum 1 funkcja
- transakcje na odpowiednim poziomie izolacji
- akcje na referencjach klucz główny - klucz obcy (użycie zapytań z JOIN) - spełnione 3 postacie normalne.

W bazie nie może występować redundancja danych, anomalia modyfikacji i usunięć. Należy zastosować odpowiednie typy danych dla przechowywanych danych w tabelach.

Należy dołączyć kompletną bazę danych wraz z przykładowymi danymi wyeksportowaną do pliku SQL.

## DOKUMENTACJA

- Należy dostarczyć dokumentację w pliku readme.md
- diagram ERD (np. PNG/SVG w repo) i plik źródłowy (.mmd).
- screeny aplikacji (WERSJA WEBOWA I MOBILNA)
- architekturę (krótki diagram warstwowy).
- instrukcję uruchomienia (docker-compose up), zmienne środowiskowe (.env.example).
- scenariusz testowy (krok po kroku: logowanie, role, CRUD, błąd 403/401, widoki/wyzwalacze).
- checklistę z informacjami co udało sie zrobić

## WYMAGANIA KONIECZNE

Aplikację należy napisać zgodnie z filarami obiektowości i zasadami SOLID. Całość powinna być systematycznie dokumentowana za pomocą commitów na repozytorium GIT, które należy ustawić na publiczną widoczność.

Projekty napisane strukturalnie zostaną odrzucone – w przypadku próby oddania projektu napisanego strukturalnie zostaje wystawiona ocena 2.0.

Kod aplikacji należy przechowywać na repozytorium git z dostępem publicznym lub udostępnionym prawem odczytu dla nauczyciela prowadzącego laboratoria. Kod należy przechowywać w celach dokumentacji przez 5 lat.

Należy dołączyć diagram ERD bazy danych.
Brak duplikacji kodu. Testy (choć symboliczne): PHPUnit (1–2 testy usług/repozytoriów) + testy integracyjne endpointów (np. prosty skrypt curl/bash). Obsługa błędów globalnie (strony 400/403/404/500).

## SECURITY BINGO

Przed oddaniem projektu muszą zostać spełnione wszystkie punkty security bingo.

- Ochrona przed SQL injection (prepared statements / brak konkatenacji SQL)
- Nie zdradzam, czy email istnieje – komunikat typu „Email lub hasło niepoprawne”
- Walidacja formatu email po stronie serwera
- UserRepository zarządzany jako singleton
- Metoda login/register przyjmuje dane tylko na POST, GET tylko renderuje widok
- CSRF token w formularzu logowania
- CSRF token w formularzu rejestracji
- Ograniczona długość wejścia (email, hasło, imię...)
- Hasła przechowywane jako hash (bcrypt?Argon2, password_hash)
- Hasła nigdy nie są logowane w logach / errorach
- Po poprawnym logownaiu regeneruję ID sesji
- Cookie sesji ma flagę HttpOnly
- Cookie sesji ma flagę Secure
- Cookie ma ustawione SameSite (np. Lax/Strict)
- Limit prób logowania / blokada czasowa / CAPTCHA / CloudFlare po wielu nieudanych próbach
- Waliduję złozoność hasłą (min. długość itd)
- Przy rejestracji sprawdzam, czy email jest juz w bazie
- Dane wyświetlane w widokach są escapowane (ochrona przez XSS)
- W produkcji nie pokazuję stack trace / surowych błędów uzytkownikowi
- Zwracam sensowne kody HTTP (np. 400/401/403 przy bledach)
- Hasło nie jest przekazywane do widoków ani echo/var_dump
- Z bazy pobieram tylko minimalny zestaw danych o uzytkowniku
- Mam poprawne wylogowanie - niszczę sesję uzytkownika
- Loguję nieudane próby logowania (bez haseł) do audytu
