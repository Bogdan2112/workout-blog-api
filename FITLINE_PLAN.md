# Fitline — planul aplicației

Actualizat: 2026-11-25.

## Decizii și stare curentă — 2026-11-25

Aceste clarificări au prioritate față de propunerile istorice de mai jos.

- Lucrăm pas cu pas la cererea explicită a utilizatorului: explicație, implementare manuală, verificare. Prioritatea rămâne aplicația pentru membri până la 20 decembrie.
- Fluxul rezervării: abonament selectat în UI → programul sălii lui → ședință aleasă → membership_id obligatoriu în JSON. Backendul verifică proprietatea abonamentului selectat, aceeași sală și întregul interval prin MembershipAccessService, plus disponibilitatea/duplicatele/starea ședinței. Nu caută un alt abonament ca substitut pentru selecția utilizatorului.
- bookings conține id, user_id, training_session_id și timestamps, cu pereche unică ședință–utilizator și chei externe restrictive. Nu salvăm membership_id, status sau cancelled_at. Anularea șterge rezervarea și eliberează locul; rezervările trecute neanulate rămân. Aceste date măsoară rezervări, nu prezență efectivă.
- Anulare manuală înainte de începerea ședinței, fără penalizări; re-rezervare cu verificările obișnuite. Discutată eliminarea rezervărilor viitoare fără acces după anularea unui abonament; mecanismul trebuie adaptat lipsei membership_id și nu este implementat. Nu pretindem că există deja anularea abonamentelor sau eliberarea automată a locurilor.
- capacity = null înseamnă fără limită; înscrierea prin Booking rămâne necesară și atunci, pentru lista membrului și consultarea ulterioară a activității de către proprietar.
- Cel mult un abonament valabil simultan per membru și sală; istoric distinct permis. Aplicarea regulii la cumpărare și reînnoirea anticipată rămân de implementat/definit.
- Program recurent acceptat: o regulă TrainingSchedule va produce automat TrainingSession distincte pe date concrete, cu propriile rezervări/capacități. Înlocuiește amânarea anterioară a recurenței. Generarea în avans este direcția aleasă; numărul de săptămâni, scheduling-ul și tratarea modificărilor/excepțiilor se stabilesc înainte de implementare. Terminăm rezervările pe ședințele existente, apoi construim schedule-ul.
- TrainingSession migration era deja Ran; utilizatorul a confirmat prin ieșirea Artisan aplicarea migrărilor bookings și capacity nullable. Relațiile sunt completate, StoreBookingRequest este scris, BookingService are constructor și verificarea abonamentului în lucru (cu greșeli de corectat la reluare). Controllerul/rutele/resource-ul/factory/seeder/testele Booking generate ca schelete nu sunt funcționalități complete. Fără teste noi de comportament, UI sau verificare a concurenței în această sesiune. Detaliile reluării sunt în LEARNING_PROGRESS.md.

Document principal pentru cerințele produsului, structura funcțională și schema propusă a bazei de date. Îl actualizăm când se schimbă deciziile. `LEARNING_PROGRESS.md` urmărește învățarea și verificările; `rewpise.md` păstrează brief-ul pentru design/Lovable.

Numele produsului a fost schimbat din Repwise în Fitline. Numele folderelor și al bazei de date nu se schimbă automat odată cu marca. Fișierul de brief `rewpise.md` își păstrează numele cerut inițial, dar conținutul descrie Fitline.

**Statut:** cerințele de mai jos descriu ce construim. Tabelele, câmpurile, numele de modele și soluțiile marcate ca propuneri nu sunt migrations aprobate sau funcționalități deja implementate. În caz de contradicție cu un brief sau cu istoricul discuțiilor, deciziile confirmate din acest document au prioritate; starea implementării se verifică în cod.

## Livrarea aplicației înaintea parcurgerii cursului — 2026-11-24

Această decizie înlocuiește ordinea pedagogică și cerințele vechi de client instalabil/procesator sandbox din istoricul documentului. Ținta este 20 decembrie 2026: aplicație web pentru membri, utilizabilă pe telefon, rulată local. Nu cerem APK, App Store, backend public sau integrare bancară pentru această livrare. Pe telefonul fizic se configurează adresa calculatorului din rețeaua locală pentru frontend/API, CORS și accesul serverului; localhost-ul telefonului nu este calculatorul.

Plata va folosi un simulator Laravel, fără credențiale bancare, carduri reale sau bani reali. Integrarea maib/MIA poate fi adăugată ulterior dacă obținem acces. Simulatorul furnizează rezultate serviciului de cumpărare; activarea se verifică în backend și este idempotentă, nu un flag trimis de frontend. Rutele de simulare sunt limitate la local/testing. Nu numim acest simulator integrare maib sau webhook bancar verificat.

**Mod de lucru:** alegem următorul flux al produsului, explicăm pe scurt regulile și conceptele noi care îl susțin, apoi îl implementăm complet: API, teste relevante și interfață. Asistentul poate construi modulul și repetițiile autorizate; primul exemplu manual nu mai blochează fiecare subpunct. Utilizatorul poate cere explicații sau practică pe bucata care îl interesează. Codul implementat și conceptele înțelese rămân evidențe separate. Nu parcurgem PDF-ul în ordine până la termen și nu implementăm funcții ca să bifăm exerciții. După livrare reluăm aprofundarea cursului.

### Auditul aplicației și rezultatul urmărit

- **Ordine reconfirmată ulterior în sesiunea 2026-11-24:** ședințe și rezervări → acces/QR/vizite → cumpărare simulată. Folosim abonamentele demonstrative pentru a construi mai întâi operațiile disponibile după cumpărare. Aceasta înlocuiește propunerea inițială de a începe cu plata din calendarul orientativ de mai jos.
- **Implementare nouă 2026-11-24:** TrainingSession și relația Gym.trainingSessions, migration/factory/seeder separat, ListTrainingSessionsRequest, TrainingSessionService/Controller/Resource. GET /api/v1/gyms/{gym}/training-sessions filtrează după date=YYYY-MM-DD (implicit ziua curentă a sălii), ordonează și paginează; GET /api/v1/gyms/{gym}/training-sessions/{trainingSession} oferă detaliile, cu scoped binding. Consultarea cere Sanctum, fără abonament; sălile inactive și nepotrivirea sală–ședință răspund 404. Datele sunt UTC, filtrul folosește limitele zilei din fusul sălii. Ședințele anulate rămân vizibile cu cancelled_at; nu sunt deja rezervabile printr-un endpoint nou. Capacity este capacitatea totală, nu locuri disponibile. Fără UI, rezervări, trainer relation sau CRUD admin în acest pas. Migration/seeder nu au fost executate pe MySQL; 14 teste noi/46 aserțiuni, suita completă 97 teste/286 aserțiuni. Selecția anterioară despre module absente descrie începutul auditului.

- **Simplificare confirmată 2026-11-24:** nu implementăm acum drepturi separate ale planurilor la anumite ședințe/facilități. Pentru rezervări, orice ședință a aceleiași săli este eligibilă dacă abonamentul permite întregul interval, iar ședința are locuri și îndeplinește regulile de rezervare. Nu adăugăm pivoturi plan–ședință/plan–facilitate sau reguli pe categorii pentru această livrare. Facilitățile pot rămâne informații de prezentare ale sălii; nu pretindem control separat de acces la ele. Această decizie înlocuiește cerințele mai vechi de drepturi diferențiate din document.

- Backend verificat: autentificare Sanctum, jurnal Week → Workout → Exercise → Set, volume calculate, catalog WorkoutX, săli active/filtru city, galerii/program, planuri, consultarea abonamentelor și serviciul de eligibilitate pe interval. Nu există încă module de plăți/cumpărare, ședințe programate, rezervări sau vizite. Autentificarea existentă nu înseamnă profil/avatar/resetare parolă complet implementate.
- Frontendul separat C:/repwise-frontend este React/Vite și deservește autentificarea și jurnalul. Noile funcții pentru săli/abonamente/rezervări nu sunt deja conectate în această interfață. Designurile nu dovedesc existența fluxurilor. Extindem interfața pe măsură ce construim modulele; nu amânăm integrarea până la final.
- Verificare de bază 2026-11-24: php artisan test --compact — 83 teste trecute, 240 aserțiuni, SQLite :memory:. Aceasta confirmă suita existentă, nu modulele încă absente sau integrarea completă pe telefon. Auditul a modificat documentele, fără PHP, dependențe, schema sau datele MySQL.
- Rezultatul: membrul își gestionează contul, caută o sală și consultă detaliile, cumpără prin simulare un plan al acelei săli, vede abonamentul, consultă ședințe datate, rezervă/anulează, folosește fluxul demonstrativ QR/check-in/check-out și vede vizite, antrenamente, cronometru și progres. Accesul și datele sunt controlate de backend. Fluxul QR local nu certifică prezența fizică și nu include turnicheți.
- CRM-ul, gestiunea completă a personalului și CRUD-ul administrativ rămân ulterioare. Păstrăm proprietatea pe sală și definim relațiile necesare extensiei, fără să construim acum panouri. Datele de catalog/program se pot pregăti prin seeders.

### Selecția subpunctelor din PDF până la termen

„Acum” înseamnă aplicare acolo unde modulul o cere, nu că toate variantele enumerate sunt obligatorii sau deja învățate.

| Subpuncte | Decizie și folosire în Fitline |
|---|---|
| 10.1–10.5 lifecycle, container, providers, facades/contracts, attributes | Folosim baza deja parcursă. Contract PaymentGateway și implementare simulată legată în provider; păstrăm autorizarea existentă. Contextual/scoped binding, deferred providers și toate atributele posibile pot aștepta. |
| 11.1 casts/accessors; 11.2 scopes | Acum: date/boolean/array/enum unde trebuie, volume și filtre reutilizabile. Custom casts, AsCollection și global scopes nu sunt obligații. |
| 11.3 agregări/subquery-uri | Acum: locuri rezervate/disponibile și statistici relevante, fără încărcarea inutilă a tuturor relațiilor. Subquery-uri avansate doar dacă query-ul real le justifică. withCount din WeekController rămâne exemplul început, nu un capitol care blochează produsul. |
| 11.4 procesare volum mare; 11.5 observers | Amânate: nu avem cerință de import masiv. Regulile de cumpărare/rezervare rămân explicite în servicii, fără observers introduși doar ca exercițiu. |
| 11.6 tranzacții și lockForUpdate; 11.7 indexuri/N+1 | Acum: plată/activare atomică, protecția ultimului loc, operații duplicate, indexuri și integritate. SoftDeletes/Prunable doar după politica de păstrare a istoricului; nu confundăm anularea cu ștergerea. Concurența se verifică și pe motorul bazei aplicației, nu se consideră demonstrată doar prin SQLite în memorie. |
| 12.1–12.6 arhitectură, DTO, enums, state machines, pipelines, module | Service Layer existent, Form Requests/Policies/Resources, enum-uri unde stările sunt finite, tranziții explicite. Nu rescriem în Actions, nu instalăm spatie/laravel-data/model-states și nu reorganizăm pe domenii pentru bifarea cursului. DTO/value objects/pipeline doar dacă simplifică o nevoie concretă. |
| 12.7 Artisan; 12.8 JSON:API | Comenzi proprii doar la nevoie; seeders existenți pentru date. Păstrăm API Resources convenționale, fără migrare la standardul JSON:API. |
| 13.1–13.3 evenimente/notificări/mail | Recuperarea parolei și confirmările relevante folosesc mail/notificări locale. Events/listeners pentru reacții separate când există; după commit când depind de date persistate. Nu creăm mailables și fiecare canal fără un caz de utilizare. |
| 13.4 Reverb; 13.5 webhooks | WebSockets amânate; reîmprospătarea normală a datelor este suficientă. Webhook bancar, semnătură și integrare externă amânate odată cu maib. Idempotența și verificarea rezultatului sunt necesare și în simulator. |
| 14.1–14.7 cozi/scheduler/concurență | Cozi simple și retry/timeout doar pentru lucrări efectiv trimise în fundal; scheduler dacă introducem remindere/curățare. Valabilitatea accesului se calculează la cerere, fără dependență de un job de expirare. Redis/Horizon, batches/chains, priorități și Concurrency::run pot aștepta. Nu confundăm paralelizarea taskurilor cu blocarea DB a ultimului loc. |
| 15.1–15.7 testare | Acum: PHPUnit existent, factories/states, fakes, cazuri de acces și eșec, Pint, verificarea fluxurilor în browser și pe telefon. Nu schimbăm frameworkul în Pest; mutation/architecture testing, Rector și aprofundarea analizei statice pot aștepta. Nu urmărim un procent arbitrar în locul regulilor importante. |
| 16.1 storage; 16.2 media; 16.3 căutare | Upload validat pentru avatar și imagini locale; căutare/filtrare în baza existentă. MediaLibrary, S3/R2, Scout/Meilisearch/Typesense și conversii în coadă nu sunt condiții pentru aceste funcții. |
| 16.4 Socialite; 16.5 Cashier; 16.6 Pennant; 16.7 localizare | Login social, Stripe/Paddle/recurring și feature flags amânate. Plata simulată nu necesită Cashier. UI în română și afișare corectă a datei/monedei; multilingvismul rămâne ulterior. |
| 17 AI | Amânat integral ca funcționalitate de produs. |
| 18 performanță/observabilitate | Acum: query-uri rezonabile, paginare, măsurători unde există probleme și loguri utile fără secrete. Cache avansat, Octane și platformele de monitorizare pot aștepta. Nu memorăm locurile disponibile în cache fără strategie de consistență. |
| 19 DevOps | Acum: configurare locală repetabilă, secrete în afara codului și mod de testare clar. Docker avansat, cloud, zero-downtime, infrastructura workerilor și CI complex sunt ulterioare livrării locale. |
| 20 proiect final | Fitline este proiectul integrator. Nu preluăm lista generică obligatorie de pachete/funcții din exercițiul PDF. |

### Ordinea de execuție și punctele de control

Ferestre orientative, nu estimări validate de efort; disponibilitatea zilnică nu este cunoscută. După fiecare flux verificăm ce rămâne, fără să mutăm toate testele și UI-ul în ultima săptămână.

1. **24–29 noiembrie: cumpărare și abonament utilizabil.** Începem cu sala/planul din API în interfață, inițierea plății, simulator succes/refuz/anulare, istoric și activare unică. Refolosim MembershipAccessService. Salvăm suma/moneda și condițiile achiziției astfel încât schimbarea ofertei să nu rescrie comanda. Stabilim explicit momentul de început și politica achizițiilor suprapuse înainte de codul de activare.
2. **30 noiembrie–6 decembrie: ședințe și rezervări.** Ședințe datate, filtrare pe zi/sală, descriere/capacitate, rezervare/anulare și lista proprie. Nu diferențiem accesul la ședințe după plan; verificăm abonamentul aceleiași săli și întregul interval permis. Decidem politica anulării înainte de implementarea operației. Testăm expirarea, intervalul orar, sala greșită, duplicatele și ultimul loc. Ordinea discutată ulterior în aceeași sesiune începe cu ședințele și rezervările, apoi cumpărarea simulată; ferestrele rămân orientative.
3. **7–11 decembrie: QR și vizite.** Definim mecanismul demonstrativ local, apoi check-in/check-out, o vizită deschisă conform regulilor alese, acces permis până la ora calculată și istoric/durată. Ieșirea târzie înregistrează ora reală. Separăm QR-ul de acces de eventualul QR pentru plată.
4. **12–15 decembrie: închiderea lipsurilor aplicației.** Profil/avatar/resetare parolă, căutare, cronometru persistent la refresh și date pentru istoricul/progresul jurnalului. Distribuim aceste operații și mai devreme unde interfața le cere. Definim indicatorii de progres din datele reale, fără grafice fictive sau antrenamente generate AI.
5. **16–20 decembrie: integrare și corectări.** Parcurgem traseul complet de membru, erorile/reîncercările, separarea între utilizatori/săli, interfața pe telefon, resetarea datelor demonstrative și rularea locală repetabilă. Nu planificăm module noi majore în această fereastră.

Un flux este închis când funcționează în interfață cu date din API, backendul aplică regulile inclusiv la cereri directe, cazurile importante sunt testate și utilizatorul poate explica responsabilitățile codului. Documentăm separat ce necesită aprofundare după livrare.

## Priorități anterioare — 2026-11-23

- Obiectivul cerut este aplicația completă pentru membri până la 20 decembrie 2026: cont/profil, catalog și căutare, abonamente/plată de test, program și rezervări/anulare, QR și vizite/check-in/check-out, jurnal/cronometrul antrenamentului, istoric și progres. Nu reducem cererea la un traseu de demo. Fezabilitatea și alegerea clientului mobil/procesatorului trebuie evaluate; data este țintă, nu o garanție.
- CRM-ul nu este prioritatea acestei livrări. Pregătim relațiile și izolarea datelor pentru proprietari, administratori de sală, super administrator și antrenori. CRUD-ul, dashboardurile și interfețele lor așteaptă. Introducerea viitoare a antrenorului ca rol înlocuiește vechea limitare de viziune la simplu text pe ședință, fără să însemne că acel rol sau o schemă de personal sunt deja implementate.
- Ordinea de învățare este legată de funcțiile produsului: primul exemplu este implementat manual de utilizator, apoi asistentul completează autonom repetițiile relevante, inclusiv teste. Nu rescriem toate modulele pentru a folosi un mecanism care nu le ajută. Regulile de business noi și alegerile importante se explică înainte de implementare; pachetele, migrarea datelor reale, commit/push nu sunt autorizate implicit.
- Etapa 17 (AI) și aprofundarea 19 (DevOps) rămân amânate; 18 este după fluxurile funcționale. Optimizările de bază și prevenirea N+1 rămân parte din implementare. Păstrăm Service Layer, Form Requests, Policies, API Resources și PHPUnit; nu introducem automat Actions/DTO frameworks/Pest sau toate pachetele enumerate de curs.
- Datele necesare fluxului membrului pot fi pregătite prin seeders până la CRM. Relațiile și verificările de apartenență se construiesc odată cu funcțiile care le folosesc; nu producem acum întregul cod administrativ.
- Clarificare de învățare: PDF-ul rămâne traseul pentru conceptele selectate, inclusiv aprofundările avansate; nu le eliminăm doar fiindcă aplicația se poate construi fără ele. Când o funcție cere un concept dintr-o etapă ulterioară, îl parcurgem anticipat și îl notăm. Urmărim separat ce este explicat, exersat manual, implementat autonom și verificat; ordinea se adaptează dependențelor aplicației.
- Catalog, implementare verificată 2026-11-23: GET /api/v1/gyms acceptă filtrul opțional city, validat separat în ListGymsRequest, cu paginare și excluderea sălilor inactive. Scopes locale Gym::active/inCity și MembershipPlan::active reutilizează filtrele. Filtrarea orașului este prin egalitate, nu motor de căutare full-text sau sugestii. Lipsa/golirea filtrului păstrează lista tuturor sălilor active; linkurile paginării păstrează filtrul și dimensiunea paginii.
- Jurnal, implementare verificată 2026-11-23: fiecare serie expune volume = kg × reps; Exercise::volume și Workout::volume însumează seriile/exercițiile. Totalul antrenamentului este inclus explicit în endpointul de detalii, după încărcarea exercises.sets, fără appends global pe antrenamente sau coloană nouă. Nu există țintă de volum impusă, cronometru sau endpoint de analiză a progresului implementate prin această schimbare.

## Decizii confirmate și implementare — 2026-11-12

- Catalogul necesită autentificare Sanctum, inclusiv sălile, programul și planurile. Accesul public a fost discutat și respins pentru prima versiune.
- Program săptămânal în `gym_opening_hours`: șapte zile ISO (1=luni, 7=duminică), o singură pereche deschidere/închidere per zi, în fusul sălii. Ambele ore null înseamnă închis. Fără pauze, intervale nocturne sau excepții de sărbători în această versiune.
- Planuri Part Time (luni–vineri, până la 17:00), Full Time (toate zilele în programul sălii) și Weekend (sâmbătă/duminică în programul sălii). Configurația reală este în `allowed_days` și `access_ends_at`, nu dedusă din nume. Închiderea sălii și expirarea pot scurta intervalul permis.
- Durate confirmate: 1/3/6/12 luni comerciale = 30/90/180/360 de zile fixe, de la activare; nu luni calendaristice. Momentele concrete se păstrează în UTC.
- Planurile au nume, descriere, imagine opțională, preț în unități monetare minore și monedă. `60000 MDL` reprezintă 600,00 MDL; prețurile din seeders sunt exclusiv demonstrative.
- `Membership` păstrează copia numelui, prețului, monedei, duratei și regulilor de acces de la activare. Dezactivarea ofertei nu anulează abonamentele deja acordate; dezactivarea sălii refuză accesul.
- Modelul, relațiile, validarea programului, API-ul de consultare și seeders sunt implementate. Datele personale sunt limitate la utilizatorul autentificat; un abonament al altui utilizator răspunde 404.
- Stare implementată: nu există încă endpoint de cumpărare sau activare gratuită. Abonamentul demonstrativ se creează doar prin seeder, în mediile local/testing, cu `is_demo=true`. Din 2026-11-24 cumpărarea cu simulator Laravel este primul flux de construit, fără a aștepta etapa 16.
- Alegerea separată a antrenamentelor/facilităților incluse este amânată. Serviciul verifică acum perioada, zilele, orele și programul sălii; conectarea lui la rezervări/check-in urmează când implementăm acele module.
- `FitlineDemoSeeder` poate fi reluat fără duplicări: completează doar sălile demonstrative ale proprietarului „Proprietar Fitline Demo”, păstrează programul deja existent și nu prelungește abonamentul la rerulare. Creează 12 planuri per sală și un abonament pentru `member@fitline.example` (parolă demonstrativă `password`).
- Rute noi, toate sub `/api/v1` și Sanctum: `GET gyms/{gym}`, `GET gyms/{gym}/membership-plans`, `GET gyms/{gym}/membership-plans/{membershipPlan}`, `GET memberships`, `GET memberships/{membership}`. Paginarea listelor acceptă `page >= 1` și `per_page` între 1 și 100.
- Fără panou administrativ, rute CRUD de scriere, procesator de plăți, rezervări sau vizite implementate în acest pas. Nu s-au instalat pachete.

## 1. Scopul produsului

Fitline este o platformă cu mai mulți proprietari de săli și mai multe săli, prin care membrii pot:

- Explora sălile și facilitățile lor.
- Consulta programul ședințelor.
- Cumpăra abonamente specifice unei săli.
- Rezerva și anula participări la ședințe incluse în abonament.
- Înregistra intrarea și ieșirea din sală și consulta durata vizitelor.
- Păstra jurnalul personal de antrenamente existent.

Un proprietar poate deține mai multe săli. Fiecare sală are propriile planuri de abonament, facilități și ședințe. Abonamentul unei săli nu oferă automat acces la alte săli, nici dacă au același proprietar.

## 2. Livrarea pentru prezentare

Ținta este 20 decembrie 2026, aplicație web locală utilizabilă pe telefon. Timpul zilnic disponibil nu este stabilit; calendarul este o prioritizare, nu o garanție de termen.

### Inclus

- Interfața pentru membri, conectată la API-ul Laravel.
- Înregistrare, autentificare și profil de bază.
- Catalogul sălilor și detaliile fiecărei săli.
- Fotografii pentru săli (galerie) și o singură imagine de prezentare pentru fiecare plan de abonament. Numărul minim de imagini și obligativitatea la creare nu sunt încă stabilite.
- Facilități și planuri de abonament per sală.
- Programul pe zile, detalii și descrieri ale ședințelor.
- Cumpărarea unui abonament prin simulatorul de plăți Laravel, fără bancă.
- Activarea abonamentului după verificarea confirmării plății.
- Lista abonamentelor proprii și perioadele de acces.
- Rezervare, lista rezervărilor proprii și anulare.
- Check-in, check-out și durata vizitelor.
- Verificarea valabilității, sălii și programului permis de abonament, fără drepturi diferențiate pe ședințe/facilități.
- Jurnalul personal existent, păstrat și expus în interfață.
- Teste relevante pentru plăți, acces, rezervări și separarea datelor.

### Amânat

- Panoul proprietarului/administratorului sălii.
- Panoul unui administrator general al platformei.
- CRUD-ul administrativ care nu este necesar fluxurilor livrate acum.
- Încasarea banilor reali și integrarea sandbox maib/MIA; păstrăm o graniță de integrare prin contractul de plată.
- AI — etapa 17 a cursului.
- Aprofundarea performanței și observabilității — etapa 18, după fluxurile principale.
- DevOps avansat — etapa 19. Dacă prezentarea cere acces online, un deploy de bază devine necesar separat.
- Control fizic cu turnicheți, scanere sau alte dispozitive.
- Penalizări, liste de așteptare și rambursări automate, fără cerințe noi.
- Calendare și rezervări separate pentru utilizarea piscinei sau saunei.

### Pregătirea datelor fără panou administrativ

Propunere acceptată ca direcție de lucru: seeders pentru proprietari demonstrativi, săli, facilități, planuri și ședințe. Pot exista și abonamente pentru teste, dar fluxul prezentat de cumpărare trebuie să funcționeze prin simulatorul Laravel.

Lipsa panoului nu elimină verificările backend. Nu generăm obligatoriu toate metodele CRUD pentru fiecare controller. Le adăugăm când implementăm funcționalitatea care le folosește.

## 3. Utilizatori și proprietate

### Membru

- Are cont personal și date proprii: abonamente, rezervări, vizite și jurnal.
- Explorează mai multe săli din aplicație.
- Poate cumpăra acces pentru o sală, fără a primi implicit acces în celelalte.
- Nu își poate atribui rolul de proprietar prin înregistrarea publică.

### Proprietar de săli — Gym Owner

- Există mai mulți proprietari în platformă.
- Fiecare poate deține mai multe săli.
- Administrarea ulterioară este limitată la propriile săli și la datele relevante acestora.
- Un rol de tip `gym_owner` sau `admin` nu este suficient singur: verificăm și proprietatea sălii.

**Propunere tehnică:** proprietarul este un `User` existent, cu drepturile potrivite. `Gym.owner_id` indică acel utilizator. Nu avem nevoie acum de o tabelă separată `gym_owners` doar pentru a duplica identitatea, emailul și parola. Această propunere presupune un proprietar per sală; coproprietatea și administratorii delegați rămân în afara primei structuri până la o cerință explicită.

Nu presupunem că proprietar și membru trebuie să fie identități separate. Combinarea rolurilor și interfața de schimbare a contextului se stabilesc când construim administrarea.

### Administrator general și antrenori

- Administratorul general nu face parte din livrarea curentă; funcțiile lui nu sunt definite.
- Antrenorul este deocamdată informație opțională pe ședință, nu un rol sau un modul separat.

## 4. Concepte care trebuie păstrate distincte

| Concept | Semnificație |
|---|---|
| Sală (`Gym`) | Locația care oferă servicii și are un proprietar |
| Facilitate (`Facility`) | Ce oferă sala: zonă fitness, piscină, saună etc. |
| Plan (`MembershipPlan`) | Oferta cumpărabilă: preț, durată, facilități și acces |
| Abonament cumpărat (`Membership`) | Dreptul concret al unui utilizator, cu început și expirare |
| Ședință (`TrainingSession`) | Activitate programată la o sală, cu oră și capacitate |
| Rezervare (`Booking`) | Locul rezervat de un membru la o ședință |
| Vizită (`Visit`) | Prezența declarată în sală, de la check-in la check-out |
| Plată (`Payment`) | Încercarea și rezultatul achitării unui abonament |
| Antrenament personal (`Workout`) | Înregistrare din jurnalul privat, independentă de ședințe |

Numele pentru modelele noi sunt propuneri tehnice. `Visit` înlocuiește propunerea inițială de model `CheckIn`: intrarea și ieșirea sunt momente ale aceleiași vizite.

### Exemplu plan versus abonament

Sala A oferă planul „Fitness Day”: 30 de zile, acces fitness până la 17:00. Bogdan și Ana pot cumpăra același plan, dar primesc abonamente distincte cu propriile perioade de valabilitate.

Planurile și combinațiile lor se păstrează în date. Denumiri precum „Fitness”, „Fitness + Yoga”, „Wellness” și „Full Access” sunt exemple, nu valori fixe în cod. „Full Access” nu înseamnă automat acces la toate sălile platformei.

## 5. Experiența membrului

### Săli și planuri

- Listă de săli, cu acces la detaliile unei săli.
- Date de prezentare: nume, descriere, localizare, contact și facilități; fotografiile sălii sunt o cerință confirmată. Lista poate afișa imaginea principală, iar detaliile prezintă galeria; alegerea imaginii principale rămâne de definit.
- Planurile fiecărei săli, prețul, perioada oferită și condițiile de acces.
- Fiecare plan are o singură imagine de prezentare. Abonamentul cumpărat de utilizator poate afișa imaginea planului; nu are nevoie de o copie separată per cumpărător.
- Descrierea planului explică facilitățile incluse și restricțiile.
- Facilitățile au nume; nu necesită descriere separată în prima versiune.

### Programul ședințelor

- La deschidere afișăm ședințele din ziua curentă a sălii.
- Utilizatorul poate selecta luni, marți, miercuri etc.; dropdown sau taburi, alegerea vizuală rămâne flexibilă.
- Fiecare zi este legată de o dată calendaristică, nu de toate ședințele istorice din acea zi a săptămânii.
- Lista este pentru sala vizată și este ordonată după oră.
- Fiecare ședință are denumire și descriere, dată/oră, durată, capacitate și informații opționale despre antrenor.
- Afișăm locurile disponibile și starea, inclusiv complet/anulat.
- Pentru o zi fără ședințe afișăm o stare goală clară.
- În detalii se poate rezerva dacă regulile de acces sunt îndeplinite.

Nu introducem automat generarea săptămânală recurentă a ședințelor. Datele programului sunt ședințe cu date concrete.

### Contul membrului

- Abonamente proprii: sală, plan, valabilitate și condiții.
- Rezervări proprii: ședință, sală, dată și stare; anulare cu confirmare.
- Vizite: intrare, ieșire și durată atunci când vizita s-a încheiat.
- Jurnal personal: săptămâni, antrenamente, exerciții și seturi.
- Dashboard, filtre suplimentare și notificări sunt îmbunătățiri de UI care se prioritizează după fluxul principal.

## 6. Reguli de acces și rezervare

### Principii comune

- Toate verificările decisive sunt făcute în backend, nu doar prin ascunderea butoanelor.
- Abonamentul trebuie să aparțină utilizatorului și să fie pentru sala corectă.
- Verificăm perioada de valabilitate, apartenența la aceeași sală și programul permis. În această versiune nu restrângem separat ședințele/facilitățile după plan.
- Nu există limită lunară de participări sau sistem de credite în cerința curentă.
- Capacitatea reprezintă limita de participanți a unei ședințe.
- Descrierea unui plan este text pentru oameni. Drepturile efective sunt relații și date structurate, nu expresii extrase din descriere.

### Interval orar — decizie confirmată

Acces „până la 17:00” înseamnă că persoana trebuie să încheie vizita până la 17:00, nu doar să intre înainte.

| Situație | Comportament |
|---|---|
| Intrare la 16:00, abonament valabil | Poate fi permisă, cu termen de ieșire 17:00 |
| Intrare la 17:00 | Refuzată |
| Ședință 16:00–17:00 | Se încadrează în limita orară |
| Ședință 16:30–17:30 | Nu se încadrează |
| Check-out la 17:15 | Înregistrăm ora reală; vizita a depășit limita |

Orele sunt configurabile per plan; 17:00 este doar exemplul discutat. Calculăm „azi” și programul în fusul orar al sălii. Propunere tehnică: momentele concrete se păstrează în UTC și se afișează local, iar regulile zilnice se interpretează în fusul sălii.

La rezervare verificăm intervalul ședinței, nu ora apăsării butonului. Poți rezerva seara o ședință eligibilă de mâine dimineață. Valabilitatea abonamentului trebuie să acopere participarea, nu doar momentul rezervării.

### Condiții de rezervare

1. Utilizator autentificat.
2. Ședința există în sala vizată.
3. Ședința nu este anulată și nu a început.
4. Utilizatorul are un abonament eligibil, pentru sala corectă și intervalul ședinței.
5. Planul permite accesul la ședință.
6. Întreaga ședință respectă intervalul orar permis.
7. Există locuri disponibile.
8. Utilizatorul nu are deja o rezervare activă la aceeași ședință.

Rezervările simultane nu trebuie să depășească capacitatea: verificarea și ocuparea locului se proiectează ca o operație atomică, cu tranzacție și controlul concurenței. Soluția concretă se explică înainte de implementare.

### Check-in și check-out

- O vizită conține utilizatorul, sala, abonamentul care permite accesul, intrarea și ieșirea.
- La check-in validăm accesul la momentul actual și creăm vizita.
- La check-out actualizăm aceeași vizită cu ora reală.
- Fără check-out nu știm durata finală; afișăm vizita ca deschisă, nu inventăm ora ieșirii.
- Durata finală este diferența dintre ieșire și intrare; nu este necesară inițial o coloană separată pentru minute.
- Check-out-ul rămâne disponibil după expirare sau depășirea orei; membrul trebuie să poată încheia vizita.
- Putem constata depășirea, dar aplicația fără hardware nu poate obliga fizic persoana să plece și nici demonstra singură prezența fizică.
- Nu închidem fictiv vizita la 17:00. Nu adăugăm penalizări sau notificări automate fără o decizie nouă.
- Prevenirea unui al doilea check-in cât există o vizită deschisă în aceeași sală este o regulă tehnică propusă, de confirmat înainte de implementare.

Checkout-ul de plată și check-out-ul unei vizite sunt operații diferite.

## 7. Plăți și cumpărarea abonamentului

Decizie 2026-11-24: pentru prezentare folosim simulatorul propriu Laravel, cu flux funcțional și fără bani reali sau credențiale bancare. maib/MIA rămâne posibilă integrare ulterioară, fără să blocheze termenul.

Fluxul urmărit:

1. Membrul alege sala și planul.
2. Backendul determină suma și moneda din datele proprii.
3. Se înregistrează încercarea de plată și se afișează checkout-ul simulat, marcat ca atare.
4. În local/testing, utilizatorul alege un rezultat de test. Backendul verifică actorul, plata, mediul și tranziția permisă; implementarea FakePaymentGateway furnizează rezultatul. Nu acceptăm arbitrar preț sau membership_id din frontend.
5. Backendul verifică plata și activează abonamentul o singură dată.
6. Membrul vede abonamentul și poate folosi accesul oferit.

Gestionăm și plata refuzată, abandonată sau încă în așteptare. Pagina de succes din browser nu este suficientă pentru activare. Confirmările repetate nu trebuie să dubleze abonamentul sau activarea.

Păstrăm suma și moneda achitate în istoricul plății. Schimbarea prețului planului nu rescrie istoricul.

PaymentGateway separă serviciul de cumpărare de implementarea simulată. Pentru maib va fi necesar un adaptor propriu, acces de test și confirmări verificate conform documentației băncii. Simulatorul nu dovedește integrarea bancară și nu se transformă în plăți reale doar prin schimbarea credențialelor.

Nu presupunem în această etapă reînnoire automată, plăți recurente, distribuirea banilor către proprietari, comisioane de platformă sau rambursări automate. Abonamentul de acces nu implică obligatoriu un subscription recurent la procesator.

## 8. Schema conceptuală a relațiilor

```text
User (proprietar)
  └── deține mai multe Gym
        ├── oferă mai multe Facility
        ├── oferă mai multe MembershipPlan
        │     ├── include facilități
        │     ├── permite acces la ședințe
        │     └── este cumpărat prin mai multe Membership
        └── programează mai multe TrainingSession
              └── are mai multe Booking

User (membru)
  ├── are mai multe Membership, fiecare pentru planul unei săli
  ├── are Payment pentru cumpărări
  ├── are Booking pentru ședințe
  ├── are Visit cu check-in și check-out
  └── are Week → Workout → Exercise → Set
```

Un membru este identificat global prin `users`. Propunere: relația lui comercială cu sala este reprezentată inițial de abonamente, rezervări și vizite, fără o tabelă suplimentară de înscriere la sală. O înscriere separată se adaugă numai dacă definim un flux care o cere.

## 9. Baza de date — propunere de lucru

Această secțiune descrie ce date anticipăm că sunt necesare. Nu se execută automat. Înainte de fiecare migration verificăm codul și schema reală și explicăm câmpurile, tipurile, cheile și ștergerile. Tipurile exacte, indexurile și unele denumiri rămân de stabilit atunci.

### 9.1 `users` — existent

Identitatea comună membrilor și proprietarilor: `id`, `name`, `email`, `password`, câmpuri de autentificare și timestamps. Rolurile și permisiunile Spatie există în proiect; structura lor exactă și utilizarea pentru proprietari se verifică înainte de schimbări.

Nu adăugăm un `gym_id` unic pe utilizator care să-l limiteze la o singură sală.

### 9.2 `gyms` — propus

| Câmp | Scop |
|---|---|
| `id` | Identificarea sălii |
| `owner_id` | Proprietarul, referință la `users` |
| `name` | Denumirea |
| `description` | Prezentarea sălii |
| `city`, `address` | Localizarea |
| `timezone` | Interpretarea programului și a zilei curente |
| `contact_email`, `phone` | Contact, dacă este necesar |
| `is_active` | Disponibilitatea în catalog, propunere pentru dezactivare |
| timestamps | Creare și actualizare |

Programul săptămânal este implementat în `gym_opening_hours`: gym_id, day_of_week (1–7), opens_at, closes_at, timestamps și pereche unică sală–zi. Un interval pe zi; ambele ore null înseamnă închis. Excepțiile de sărbători și programul nocturn sunt amânate. Full Time respectă acest program, nu înseamnă 24/7. Fotografiile sunt o cerință confirmată; un logo distinct nu este încă cerut.

#### Fotografii pentru sală — `gym_photos`, tabelă suplimentară propusă

O sală poate avea mai multe fotografii. Propunere de câmpuri: `id`, `gym_id`, `path`, `sort_order`, timestamps. Relație: Gym hasMany GymPhoto. Alegerea copertei (de exemplu prima fotografie din ordinea galeriei) rămâne de confirmat.

Fișierele se păstrează în storage; baza de date conține calea și eventual identificarea discului dacă va fi necesară. API-ul furnizează URL-uri utilizabile de client. Înainte de upload stabilim validarea fișierelor și drepturile proprietarului; pentru prezentare se pot pregăti fotografii demonstrative fără a implementa panoul administrativ.

### 9.3 `facilities` — propus

Propunerea minimă este o facilitate definită în contextul unei săli: `id`, `gym_id`, `name`, opțional `is_active`, timestamps. De exemplu, piscina din Sala A este distinctă de piscina din Sala B.

Nu cerem coloană de descriere în prima versiune. Dacă alegem ulterior un catalog global de tipuri de facilități, vom revizui această reprezentare înainte de migration, nu vom construi simultan ambele variante.

### 9.4 `membership_plans` — implementat, 2026-11-12

| Câmp | Scop |
|---|---|
| `id`, `gym_id` | Planul și sala căreia îi aparține |
| `name`, `description` | Denumirea și oferta explicată membrului |
| `image_path` | O singură imagine de prezentare a planului, cale către fișier |
| `price_amount`, `currency` | Preț și monedă, fără calcule financiare cu float |
| `duration_days` | Durata confirmată: 30, 90, 180 sau 360 de zile |
| `allowed_days` | Lista zilelor ISO permise, păstrată ca JSON |
| `access_ends_at` | Limită orară opțională; null înseamnă până la închiderea sălii |
| `is_active` | Dacă poate fi oferit spre cumpărare |
| timestamps | Creare și actualizare |

Accesul începe la deschiderea sălii pentru ziua respectivă și se termină la cea mai apropiată limită: închidere, access_ends_at sau expirarea abonamentului. Nu adăugăm limită lunară de participări. Prețul este un întreg în unități monetare minore. Pentru istoricul abonamentelor, ștergerea unui plan referit este restricționată.

### 9.5 `facility_membership_plan` — pivot propus

`membership_plan_id`, `facility_id`, cu pereche unică. Reprezintă facilitățile incluse în plan. Ambele capete trebuie să aparțină aceleiași săli.

### 9.6 `training_sessions` — baza de consultare implementată în cod, 2026-11-24

Migration pregătită, fără rulare pe baza locală MySQL. Implementate: id, gym_id, name, description obligatorie, starts_at/ends_at UTC, capacity, cancelled_at, timestamps și index compus gym_id/starts_at. Câmpurile opționale despre antrenor din propunerea de mai jos și statusul separat nu au fost adăugate. Anularea este reprezentată prin cancelled_at. Mai jos rămâne schema conceptuală cu aceste distincții.

| Câmp | Scop |
|---|---|
| `id`, `gym_id` | Ședința și sala |
| `name`, `description` | Denumire și descriere necesară |
| `starts_at`, `ends_at` | Intervalul concret; durata se poate calcula |
| `capacity` | Numărul maxim de participanți |
| `trainer_name`, `trainer_details` | Informații opționale despre antrenor |
| `status` | Propunere pentru programată/anulată; valorile se stabilesc la implementare |
| `cancelled_at` | Momentul anulării, dacă folosim această reprezentare |
| timestamps | Creare și actualizare |

Nu folosim tabela existentă `workouts` pentru aceste ședințe. Locurile disponibile se calculează din capacitate și rezervările active, nu sunt un număr modificat arbitrar în UI.

### 9.7 `membership_plan_training_session` — propunere istorică, amânată

`membership_plan_id`, `training_session_id`, cu pereche unică. Reprezintă explicit ce planuri permit accesul la fiecare ședință, conform cerinței inițiale.

Un plan și o ședință trebuie să fie din aceeași sală. Facilitățile incluse și accesul la o ședință sunt drepturi distincte; nu deducem automat accesul la toate clasele dintr-un text precum „Full Access”. Dacă alegem reguli pe categorii de ședințe în loc de legare explicită, decidem înainte de implementarea pivotului.

### 9.8 `memberships` — implementat, 2026-11-12

| Câmp | Scop |
|---|---|
| `id`, `user_id` | Abonamentul și titularul |
| `membership_plan_id` | Oferta cumpărată |
| `starts_at`, `ends_at` | Perioada efectivă a accesului |
| `plan_name`, `price_amount`, `currency`, `duration_days`, `allowed_days`, `access_ends_at` | Copia condițiilor de la activare |
| `cancelled_at` | Moment opțional de anulare; nu există endpoint de anulare în acest pas |
| `is_demo` | Marcaj pentru datele demonstrative; implicit false în tabelă |
| timestamps | Creare și actualizare |

Sala se poate obține prin plan; un `gym_id` suplimentar se adaugă numai cu o justificare și validarea consistenței. Expirarea se verifică și din date, nu exclusiv dintr-un status actualizat periodic.

Implementarea păstrează o copie a condițiilor planului. Starea este calculată la citire: scheduled înainte de început, active până la expirare exclusiv, expired de la expirare și cancelled de la cancelled_at. Nu există coloană status sau job care să actualizeze periodic expirarea. Nu există încă activare prin plată; crearea demo este izolată la local/testing.

### 9.9 `bookings` — propus

`id`, `user_id`, `training_session_id`, `membership_id`, `status`, `cancelled_at`, timestamps.

Păstrăm abonamentul care a justificat rezervarea. Nu permitem două rezervări active ale aceluiași utilizator la aceeași ședință. Alegem la implementare cum reprezentăm re-rezervarea după anulare și ce constrângeri SQL sunt potrivite; un index unic necondiționat trebuie corelat cu această alegere.

### 9.10 `visits` — propus

| Câmp | Scop |
|---|---|
| `id`, `user_id`, `gym_id` | Vizita, membrul și sala |
| `membership_id` | Abonamentul care a permis intrarea |
| `checked_in_at` | Ora reală a intrării declarate |
| `checked_out_at` | Ora reală a ieșirii; goală până la check-out |
| `allowed_until_at` | Propunere: termenul de ieșire calculat la intrare, util pentru istoricul depășirilor |
| timestamps | Creare și actualizare |

Aceasta urmărește vizita în sală. Nu înregistrează implicit fiecare trecere prin ușa piscinei sau saunei. Nu avem durată finală fără ieșire. O valoare de tip `allowed_until_at` trebuie calculată inclusiv cu expirarea abonamentului și programul aplicabil, nu fixată global la 17:00.

### 9.11 `payments` — propus

`id`, `user_id`, `gym_id`, `membership_plan_id`, `membership_id` opțional, `provider`, `provider_reference`, `amount`, `currency`, `status`, `paid_at`, timestamps.

Propunere: plata poate exista înainte de crearea abonamentului activ, de aceea legătura la membership poate fi inițial goală. După confirmare se leagă de abonamentul rezultat. Pot exista mai multe încercări; modelarea precisă se aliniază procesatorului ales.

Referințele externe trebuie să poată preveni procesarea duplicată. Nu păstrăm date brute de card sau secretele procesatorului în aceste înregistrări. Suma se determină server-side, iar stările și câmpurile exacte depind de integrare.

### 9.12 Jurnalul și tabelele tehnice existente

```text
users → weeks → workouts → exercises → sets
```

Din migrations inspectate există:

- `weeks`: `user_id`, `name`, timestamps.
- `workouts`: `week_id`, `name`, timestamps.
- `exercises`: `workout_id`, `name`, timestamps, plus extensii WorkoutX în migrations ulterioare.
- `sets`: `exercise_id`, `kg`, `reps`, timestamps.
- Tabele tehnice pentru autentificare/Sanctum, roluri și permisiuni, cache, jobs și sesiuni.

Acestea descriu fișierele migration, nu confirmă starea live a MySQL. Jurnalul rămâne privat și nu devine automat vizibil proprietarilor sălilor. Conectarea unei participări la un Workout personal este o extensie ulterioară.

## 10. Integritate și acces între săli

- Cerință explicită: frontendul protejează toate paginile și subpaginile administrative prin verificarea sesiunii și a rolului autorizat, inclusiv la accesarea directă a URL-ului sau refresh. Nu este suficient să ascundem meniul. Utilizatorul neautentificat este trimis la login, iar membrul fără drepturi nu vede conținutul administrativ și primește acces refuzat sau este redirecționat în zona sa. În timpul verificării nu afișăm temporar conținutul protejat.
- Protecția frontendului nu înlocuiește autorizarea API-ului: fiecare operație administrativă verifică autentificarea, dreptul și proprietatea sălii. Nu descărcăm date administrative pentru membri fără drepturi. Rolul de proprietar nu oferă acces la datele altor proprietari. Panoul general, dacă va fi definit, are drepturi distincte de panoul proprietarului.

- Resursa accesată trebuie verificată în contextul sălii, nu doar după ID-ul primit.
- Proprietarul nu poate administra sala altui proprietar.
- Membrul nu poate vedea abonamentele, rezervările sau vizitele altui membru.
- Un plan nu poate oferi facilități sau ședințe dintr-o altă sală printr-o asociere accidentală.
- Un abonament nu poate justifica o rezervare sau o intrare în sala greșită.
- Relațiile și cheile străine nu înlocuiesc automat toate aceste verificări; le proiectăm și le testăm explicit.
- Nu alegem implicit ștergere în cascadă pentru date comerciale, plăți sau istoric. Regulile de anulare/dezactivare/ștergere se stabilesc înainte de fiecare migration și operație administrativă.

## 11. Administrarea ulterioară

După fluxul membrului adăugăm API-ul și interfața necesare proprietarului:

- Gestionarea propriilor săli și a datelor lor.
- Gestionarea facilităților și planurilor.
- Crearea, editarea și anularea ședințelor.
- Configurarea accesului planurilor la facilități și ședințe.
- Consultarea rezervărilor, vizitelor și abonamentelor pentru propriile săli.
- Gestionarea abonamentelor și plăților în limitele operațiilor stabilite.

Metodele administrative primesc Form Requests și autorizare; nu expunem create/update/delete doar fiindcă există un resource controller. Administrarea nu trebuie să ofere implicit acces la jurnalul personal.

## 12. Direcția frontend

- Decizie 2026-11-24: versiunea web pe telefon este suficientă pentru 20 decembrie; aplicația rulează local. Extindem frontendul React existent și API-ul Laravel. Capacitor, distribuția Android/iOS și hostingul public nu fac parte din această livrare. CRM-ul web rămâne ulterior.

- Română; interfață responsive pentru desktop și mobil.
- Navigare clară între săli, program, abonamente, rezervări, vizite și jurnal.
- Sala vizată trebuie să fie vizibilă în fluxurile de acces și cumpărare.
- Datele demonstrative Lovable nu reprezintă backend implementat; le înlocuim prin API pe măsură ce construim fluxurile.
- Stări de încărcare, liste goale, erori și confirmări clare.
- Drepturile de acces sunt explicate și în UI, dar sunt aplicate în backend.
- Culorile, stilul cardurilor și forma selectorului zilei sunt alegeri de design flexibile. Galeria sălii și imaginea unică a planului sunt funcționalități confirmate; prezentarea vizuală și imaginile concrete rămân de ales.
- Prima livrare nu are nevoie de comutator demonstrativ spre un panou administrativ care nu este implementat.

## 13. Ordinea propusă de implementare și învățare

### Organizarea endpointurilor noi — direcție confirmată

Decizie finală curentă a utilizatorului, după recomandarea leadului: Service Layer. Controllerele coordonează HTTP și apelează servicii dedicate care grupează operații înrudite, de exemplu BookingService.create/cancel și VisitService.checkIn/checkOut. Folosim Form Requests, Policies și API Resources. Nu generăm Actions sau Queries pentru fiecare endpoint și nu implementăm Onion complet sau repository-uri pentru fiecare model. Păstrăm serviciile concentrate, fără un serviciu global cu toate funcțiile și fără straturi care doar retransmit aceleași argumente. Actions pot fi evaluate ulterior pentru operații care justifică extragerea, fără duplicarea aceleiași logici.

- Controller: primește intrarea validată, coordonează autorizarea/apelul operației și returnează Resource-ul.
- Form Request: validarea datelor HTTP.
- Policy/middleware: autorizare, inclusiv proprietatea sălii; operațiile reutilizabile trebuie să aibă și un contract clar privind actorul și autorizarea, fără bypass prin apelare din alt context.
- Servicii de aplicație (de exemplu BookingService): coordonează regulile, interogările necesare operației și tranzacțiile; pot avea mai multe metode pentru operații înrudite. Nu presupunem o corespondență obligatorie de un serviciu per tabelă. Pentru operații simple păstrăm nivelul de separare proporțional, fără clase suplimentare inutile.
- Model: relații, casts și comportamentele datelor care îi aparțin.
- API Resource: reprezentarea JSON a rezultatului.
- Servicii/adaptoare: integrarea procesatoarelor și a altor sisteme externe.

Proprietarul și membrul pot avea controllere și operații distincte dacă scopul sau datele diferă; serviciile pot reutiliza logica comună. Nu este nevoie de o interfață, repository și DTO pentru fiecare clasă doar pentru a completa straturi. Serviciile concrete pot folosi Eloquent; aceasta este o organizare pragmatică, nu o implementare strictă Onion independentă de framework. Un folder nou se explică înainte de generarea fișierelor.

Separarea îmbunătățește întreținerea și testarea. Scalarea performanței cere separat interogări potrivite, indexuri, controlul concurenței, cache/cozi și infrastructură după necesitate; mutarea codului nu accelerează singură endpointurile. Jurnalul existent se refactorizează țintit când modificăm operațiile lui, fără o rescriere generală care blochează prezentarea.

### Ordinea funcționalităților

Actualizare de lucru 2026-11-23: lista de mai jos descrie dependențele funcționale, nu dovedește că fiecare pas este deja executat. Jurnalul, sălile, programul sălii și consultarea planurilor/abonamentelor există; clasele programate, rezervările, vizitele și plata nu există încă în codul inspectat.

### Legătura practică cu etapele cursului

| Funcționalitate | Concepte care se aplică pe parcurs |
|---|---|
| Jurnal, catalog și filtrare | 11.1 accessors/mutators/casts, 11.2 scopes, 11.3 agregări; 11.7 interogări/indexuri și N+1 |
| Clase, eligibilitate și rezervări | Relații Eloquent, Service Layer din 12 adaptat arhitecturii alese; 11.6 tranzacții și controlul concurenței; enums/stări din 12.3–12.4 când sunt utile |
| Plăți și activarea abonamentelor | Contracte/bindings din 10, tranzacții din 11.6, enum-uri/tranziții și idempotență în simulator; 13.5 webhooks bancare rămâne pentru integrarea externă ulterioară |
| Confirmări și operații în fundal | 13.1–13.3 events/listeners/notificări/mail și elementele necesare din 14 pentru cozi, retry și scheduling |
| Profil, imagini și căutare | Părțile relevante din 16 pentru storage, validare, căutare și localizare; alegem instrumentele după nevoie, nu impunem Scout/Cashier/MediaLibrary fără evaluare |
| Orice flux livrat | 15 — teste relevante, factories, fakes/mocking și Pint; păstrăm PHPUnit și infrastructura existentă |
| Integrarea aplicației complete | 20 ca proiect practic adaptat Fitline; 18 după fluxuri; infrastructura minimă necesară demonstrației se decide separat |

Aceasta este maparea de lucru la produs, nu declararea tuturor subpunctelor drept învățate sau promisiunea instalării pachetelor din PDF. Reverb, Socialite, Horizon, custom casts și celelalte extensii se aleg explicit dacă rezolvă o nevoie. Încheiem o parte din curs când a fost explicată/aplicată, nu doar fiindcă există cod generat.

### Succesiunea funcțională anterioară — calendarul 2026-11-24 are prioritate

1. Explicăm și definitivăm `Gym` și relația cu proprietarul; generăm model/migration/factory/seeder și verificăm.
2. Facilități și listarea sălilor cu date demonstrative din MySQL.
3. Planuri și drepturi de acces, cu programul permis clar definit.
4. Ședințe și filtrarea pe data selectată.
5. Abonamente și verificarea eligibilității; putem pregăti date de test înainte de integrarea plății.
6. Rezervări, anulare, capacitate și concurență.
7. Vizite, check-in/check-out, durată și limită orară.
8. Cumpărare cu simulator Laravel și activare unică; din 2026-11-24 acest flux este mutat la început, conform calendarului actualizat de sus.
9. Integrarea frontendului se face progresiv pentru fluxurile gata, inclusiv jurnalul existent.
10. Verificarea completă a demonstrației și corectări, cu timp păstrat pentru integrare.

Ordinea poate fi ajustată după alegerea procesatorului și dependențele interfeței. Testele se adaugă pentru fiecare regulă importantă pe parcurs. Conceptele cursului se explică înainte de cod și se aplică pe aceste operații; refactorizarea justificată nu înseamnă reconstruirea aplicației.

Etapa 10.1 a fost verificată practic. Din 10.2 au fost demonstrate auto-resolution, singleton, interfața, implements, binding și injectarea în controller. `scoped`, contextual binding și restul cursului nu sunt marcate finalizate. Exercițiul catalogului fals a fost amânat pentru prioritizarea produsului.

## 14. Întrebări rămase înainte de implementarea părților respective

- Disponibilitatea zilnică; ținta este 20 decembrie 2026, web pe telefon/local, fără obligație de deploy online.
- Pentru integrarea comercială ulterioară: procesatorul/banca, țara contului comercial și destinația încasărilor. Nu blochează simulatorul local; moneda datelor demonstrative este MDL.
- Activarea în simulator este legată de rezultatul procesat în backend; durata fixă în zile este confirmată. Alegem momentul de început pentru cumpărare și politica suprapunerii înainte de implementare.
- Dacă pot exista simultan mai multe abonamente valabile ale aceluiași membru în aceeași sală și cum alegem unul pentru acces.
- Condițiile deja cumpărate când proprietarul modifică oferta planului.
- Programul săptămânal și restricțiile planurilor sunt confirmate mai sus; sărbătorile și intervalele nocturne nu sunt incluse.
- Termenul de anulare, re-rezervarea și efectul anulării/modificării unei ședințe asupra rezervărilor.
- Efectul anulării unui abonament sau al schimbării drepturilor asupra rezervărilor existente.
- Politica vizitelor rămase deschise și cine le poate corecta ulterior; nu auto-completăm ieșiri fictive.
- Mecanismul de check-in/check-out din UI pentru demonstrație; nu presupunem dovada prezenței fizice prin simplul buton.
- Relația proprietar–săli există prin `users → gyms.owner_id`. Pentru viitorul CRM trebuie definit separat personalul/adminii/antrenorii per sală și atribuirea antrenorilor la ședințe; nu presupunem coproprietate sau drepturi globale pentru un simplu rol.
- Drepturile plan–ședință/plan–facilitate sunt amânate explicit la 2026-11-24; alegerea pivoturilor sau categoriilor nu blochează rezervările actuale.

Nu transformăm aceste întrebări în cerințe suplimentare implementate automat. Le rezolvăm când devin necesare, păstrând documentul actualizat.

## 15. Istoricul implementării înainte de 2026-11-12

Verificare istorică: 2026-11-11. Pentru starea curentă și schema MySQL verificată, vezi secțiunea de decizii și implementare din 2026-11-12 și LEARNING_PROGRESS.md.

- Există jurnalul API cu modelele User, Week, Workout, Exercise, Set, rute, validări și policies. Nu toate operațiile au fost confirmate funcționale; detaliile și erorile cunoscute sunt în `LEARNING_PROGRESS.md`.
- Există autentificare Sanctum și pachetul Spatie pentru roluri/permisiuni.
- Există `ExerciseCatalog`, implementarea WorkoutX și binding-ul în provider; rezolvarea interfeței a fost verificată de utilizator.
- Controllerul catalogului cere interfața. Apelul extern a produs mesajul configurat pentru ConnectionException; disponibilitatea WorkoutX nu este confirmată.
- Exercițiul TraceRequest și răspunsul personalizat 404 au fost verificate.
- Gym a fost generat: există modelul, migration-ul `2026_11_11_115552_create_gyms_table.php`, factory și seeder. Migration-ul este completat cu owner_id și câmpurile de prezentare/localizare/contact, timezone și is_active; sintaxa a fost verificată. migrate:status confirmă migration-ul gyms executat, batch 5. Gym::owner() și User::ownedGyms() sunt implementate, cu sintaxa verificată; navigarea pe date nu este încă testată. Fillable și cast boolean pentru is_active sunt scrise în model la ultima citire. Factory și seeder sunt încă schelete. Fotografiile și image_path ale planurilor sunt planificate, fără tabele sau cod implementate.
- Noile funcții multi-sală, planuri, cumpărare, rezervări și vizite din acest document sunt planificate, nu implementate.

## 16. Regula de actualizare a documentelor

- Cerințe și model de date: actualizăm acest document.
- Brief de interfață/Lovable: sincronizăm `rewpise.md` cu deciziile relevante pentru UI.
- Lecția curentă, cod implementat și verificări: actualizăm `LEARNING_PROGRESS.md`.
- Păstrăm distincția între decizie confirmată, propunere, cod scris și comportament verificat.
- Nu modificăm fișierele aplicației sau schema bazei doar pentru că am actualizat planul.
