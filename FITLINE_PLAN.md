# Fitline — planul aplicației

Actualizat: 2026-11-11.

Document principal pentru cerințele produsului, structura funcțională și schema propusă a bazei de date. Îl actualizăm când se schimbă deciziile. `LEARNING_PROGRESS.md` urmărește învățarea și verificările; `rewpise.md` păstrează brief-ul pentru design/Lovable.

Numele produsului a fost schimbat din Repwise în Fitline. Numele folderelor și al bazei de date nu se schimbă automat odată cu marca. Fișierul de brief `rewpise.md` își păstrează numele cerut inițial, dar conținutul descrie Fitline.

**Statut:** cerințele de mai jos descriu ce construim. Tabelele, câmpurile, numele de modele și soluțiile marcate ca propuneri nu sunt migrations aprobate sau funcționalități deja implementate. În caz de contradicție cu un brief sau cu istoricul discuțiilor, deciziile confirmate din acest document au prioritate; starea implementării se verifică în cod.

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

Ținta discutată este aproximativ o lună; data exactă și timpul zilnic disponibil nu au fost stabilite. Aceasta este o prioritizare, nu o garanție de termen.

### Inclus

- Interfața pentru membri, conectată la API-ul Laravel.
- Înregistrare, autentificare și profil de bază.
- Catalogul sălilor și detaliile fiecărei săli.
- Fotografii pentru săli (galerie) și o singură imagine de prezentare pentru fiecare plan de abonament. Numărul minim de imagini și obligativitatea la creare nu sunt încă stabilite.
- Facilități și planuri de abonament per sală.
- Programul pe zile, detalii și descrieri ale ședințelor.
- Cumpărarea unui abonament printr-o integrare funcțională în mediul de test al procesatorului.
- Activarea abonamentului după verificarea confirmării plății.
- Lista abonamentelor proprii și perioadele de acces.
- Rezervare, lista rezervărilor proprii și anulare.
- Check-in, check-out și durata vizitelor.
- Verificarea valabilității, facilităților și programului permis de abonament.
- Jurnalul personal existent, păstrat și expus în interfață.
- Teste relevante pentru plăți, acces, rezervări și separarea datelor.

### Amânat

- Panoul proprietarului/administratorului sălii.
- Panoul unui administrator general al platformei.
- CRUD-ul administrativ care nu este necesar fluxurilor livrate acum.
- Încasarea banilor reali; pregătim integrarea în sandbox-ul furnizorului ales.
- AI — etapa 17 a cursului.
- Aprofundarea performanței și observabilității — etapa 18, după fluxurile principale.
- DevOps avansat — etapa 19. Dacă prezentarea cere acces online, un deploy de bază devine necesar separat.
- Control fizic cu turnicheți, scanere sau alte dispozitive.
- Penalizări, liste de așteptare și rambursări automate, fără cerințe noi.
- Calendare și rezervări separate pentru utilizarea piscinei sau saunei.

### Pregătirea datelor fără panou administrativ

Propunere acceptată ca direcție de lucru: seeders pentru proprietari demonstrativi, săli, facilități, planuri și ședințe. Pot exista și abonamente pentru teste, dar fluxul prezentat de cumpărare trebuie să funcționeze prin procesatorul de test.

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
- Verificăm perioada de valabilitate, facilitățile/ședințele incluse și programul permis.
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

Pentru prezentare folosim mediul de test al unui procesator real, cu flux funcțional și fără bani reali. Procesatorul sau banca nu au fost alese.

Fluxul urmărit:

1. Membrul alege sala și planul.
2. Backendul determină suma și moneda din datele proprii.
3. Se înregistrează încercarea de plată și se începe checkout-ul procesatorului.
4. Procesatorul confirmă rezultatul către backend prin mecanismul documentat de el, de regulă webhook verificat.
5. Backendul verifică plata și activează abonamentul o singură dată.
6. Membrul vede abonamentul și poate folosi accesul oferit.

Gestionăm și plata refuzată, abandonată sau încă în așteptare. Pagina de succes din browser nu este suficientă pentru activare. Confirmările repetate nu trebuie să dubleze abonamentul sau activarea.

Păstrăm suma și moneda achitate în istoricul plății. Schimbarea prețului planului nu rescrie istoricul.

Obiectivul este să putem trece ulterior în producție la același furnizor. Nu garantăm că este suficientă doar schimbarea credențialelor: pot fi necesare activarea contului, configurări și verificări de producție. Un alt furnizor poate necesita alt adaptor/cod.

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

Programul de funcționare este necesar pentru definirea accesului „full time”, dar reprezentarea lui pe zile și excepții trebuie decisă separat. Nu presupunem că sala funcționează 24/7. Fotografiile sunt o cerință confirmată; un logo distinct nu este încă cerut.

#### Fotografii pentru sală — `gym_photos`, tabelă suplimentară propusă

O sală poate avea mai multe fotografii. Propunere de câmpuri: `id`, `gym_id`, `path`, `sort_order`, timestamps. Relație: Gym hasMany GymPhoto. Alegerea copertei (de exemplu prima fotografie din ordinea galeriei) rămâne de confirmat.

Fișierele se păstrează în storage; baza de date conține calea și eventual identificarea discului dacă va fi necesară. API-ul furnizează URL-uri utilizabile de client. Înainte de upload stabilim validarea fișierelor și drepturile proprietarului; pentru prezentare se pot pregăti fotografii demonstrative fără a implementa panoul administrativ.

### 9.3 `facilities` — propus

Propunerea minimă este o facilitate definită în contextul unei săli: `id`, `gym_id`, `name`, opțional `is_active`, timestamps. De exemplu, piscina din Sala A este distinctă de piscina din Sala B.

Nu cerem coloană de descriere în prima versiune. Dacă alegem ulterior un catalog global de tipuri de facilități, vom revizui această reprezentare înainte de migration, nu vom construi simultan ambele variante.

### 9.4 `membership_plans` — propus

| Câmp | Scop |
|---|---|
| `id`, `gym_id` | Planul și sala căreia îi aparține |
| `name`, `description` | Denumirea și oferta explicată membrului |
| `image_path` | O singură imagine de prezentare a planului, cale către fișier |
| `price_amount`, `currency` | Preț și monedă, fără calcule financiare cu float |
| `duration_days` | Propunere pentru durata oferită; zile versus luni calendaristice rămâne de decis |
| `access_starts_at`, `access_ends_at` | Propunere pentru un interval zilnic simplu |
| `is_active` | Dacă poate fi oferit spre cumpărare |
| timestamps | Creare și actualizare |

Semnificația valorilor goale pentru acces și relația cu programul sălii trebuie definite. Program diferit în fiecare zi, acces nocturn sau mai multe intervale sunt cazuri de discutat, nu sunt deja implementate. Nu adăugăm câmp pentru număr lunar de participări.

### 9.5 `facility_membership_plan` — pivot propus

`membership_plan_id`, `facility_id`, cu pereche unică. Reprezintă facilitățile incluse în plan. Ambele capete trebuie să aparțină aceleiași săli.

### 9.6 `training_sessions` — propus

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

### 9.7 `membership_plan_training_session` — pivot propus

`membership_plan_id`, `training_session_id`, cu pereche unică. Reprezintă explicit ce planuri permit accesul la fiecare ședință, conform cerinței inițiale.

Un plan și o ședință trebuie să fie din aceeași sală. Facilitățile incluse și accesul la o ședință sunt drepturi distincte; nu deducem automat accesul la toate clasele dintr-un text precum „Full Access”. Dacă alegem reguli pe categorii de ședințe în loc de legare explicită, decidem înainte de implementarea pivotului.

### 9.8 `memberships` — propus

| Câmp | Scop |
|---|---|
| `id`, `user_id` | Abonamentul și titularul |
| `membership_plan_id` | Oferta cumpărată |
| `starts_at`, `ends_at` | Perioada efectivă a accesului |
| `status` | Starea comercială/activarea, valori încă de stabilit |
| timestamps | Creare și actualizare |

Sala se poate obține prin plan; un `gym_id` suplimentar se adaugă numai cu o justificare și validarea consistenței. Expirarea se verifică și din date, nu exclusiv dintr-un status actualizat periodic.

Decizie necesară: dacă modificările unui plan afectează abonamentele deja cumpărate. Pentru a păstra condițiile cumpărate putem folosi o copie a drepturilor sau versiuni de plan, dar nu introducem încă o soluție fără explicație și alegere.

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

- Clarificare de produs: membrul va folosi o aplicație instalabilă pe telefon; panoul proprietarului va fi web. Nu considerăm simpla accesare din browser drept livrarea mobilă cerută. Capacitor cu frontend React a fost discutat ca opțiune de reutilizare a UI-ului; tehnologia și platformele țintă nu sunt încă alese definitiv. Backendul Laravel rămâne pe server și expune API-ul. Alegerea/build-ul clientului mobil se abordează ulterior, fără a opri dezvoltarea backendului acum.

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

1. Explicăm și definitivăm `Gym` și relația cu proprietarul; generăm model/migration/factory/seeder și verificăm.
2. Facilități și listarea sălilor cu date demonstrative din MySQL.
3. Planuri și drepturi de acces, cu programul permis clar definit.
4. Ședințe și filtrarea pe data selectată.
5. Abonamente și verificarea eligibilității; putem pregăti date de test înainte de integrarea plății.
6. Rezervări, anulare, capacitate și concurență.
7. Vizite, check-in/check-out, durată și limită orară.
8. Integrarea procesatorului în sandbox și activarea după confirmarea plății; fluxul de cumpărare face parte din prezentare.
9. Integrarea frontendului se face progresiv pentru fluxurile gata, inclusiv jurnalul existent.
10. Verificarea completă a demonstrației și corectări, cu timp păstrat pentru integrare.

Ordinea poate fi ajustată după alegerea procesatorului și dependențele interfeței. Testele se adaugă pentru fiecare regulă importantă pe parcurs. Conceptele cursului se explică înainte de cod și se aplică pe aceste operații; refactorizarea justificată nu înseamnă reconstruirea aplicației.

Etapa 10.1 a fost verificată practic. Din 10.2 au fost demonstrate auto-resolution, singleton, interfața, implements, binding și injectarea în controller. `scoped`, contextual binding și restul cursului nu sunt marcate finalizate. Exercițiul catalogului fals a fost amânat pentru prioritizarea produsului.

## 14. Întrebări rămase înainte de implementarea părților respective

- Data exactă a prezentării, disponibilitatea zilnică și dacă demonstrația trebuie să fie online.
- Procesatorul/banca, țara contului comercial și moneda; cui îi revin încasările în versiunea reală cu mai mulți proprietari.
- Când începe un abonament: imediat după plată sau la o dată aleasă; durata în zile sau luni calendaristice.
- Dacă pot exista simultan mai multe abonamente valabile ale aceluiași membru în aceeași sală și cum alegem unul pentru acces.
- Condițiile deja cumpărate când proprietarul modifică oferta planului.
- Programul sălii și al planului pe zile, weekenduri, sărbători și intervale peste miezul nopții, dacă le includem.
- Termenul de anulare, re-rezervarea și efectul anulării/modificării unei ședințe asupra rezervărilor.
- Efectul anulării unui abonament sau al schimbării drepturilor asupra rezervărilor existente.
- Politica vizitelor rămase deschise și cine le poate corecta ulterior; nu auto-completăm ieșiri fictive.
- Mecanismul de check-in/check-out din UI pentru demonstrație; nu presupunem dovada prezenței fizice prin simplul buton.
- Confirmarea strategiei simple de proprietate `users → gyms.owner_id`; coproprietari și personal delegat nu sunt cerințe curente.
- Alegerea finală a pivotului explicit plan–ședință versus reguli pe categorii, înainte de implementarea accesului.

Nu transformăm aceste întrebări în cerințe suplimentare implementate automat. Le rezolvăm când devin necesare, păstrând documentul actualizat.

## 15. Ce este implementat efectiv la ultima verificare

Verificare fișiere: 2026-11-11. Nu a fost inspectată în această operație schema live MySQL.

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
