# Fitline — brief pentru design și Lovable

Fotografii: fiecare sală are o galerie de fotografii, cu o imagine principală în listă și acces la galerie în detalii. Fiecare plan de abonament are o singură imagine de prezentare, afișată în card și în detalii. Abonamentele cumpărate pot reutiliza imaginea planului. Nu sunt fotografii de profil ale utilizatorilor și nu se cer galerii pentru fiecare plan. Administrarea/upload-ul va urma drepturile proprietarului; pentru prima prezentare imaginile pot fi pregătite ca date demonstrative.

Acces administrativ: când se implementează panoul proprietarului, toate rutele și subpaginile sale trebuie protejate prin autentificare și verificarea rolului, inclusiv la acces direct prin URL și refresh. Membrul obișnuit nu poate afișa paginile administrative doar cunoscând adresa. Ascunderea meniului nu este suficientă. Afișează o stare de verificare a sesiunii înainte de conținutul protejat și refuză/redirecționează accesul neautorizat. Datele și operațiile sunt protejate separat în API, inclusiv prin verificarea proprietății sălii. Acest lucru nu aduce panoul administrativ în livrarea inițială.

Planul principal al aplicației, rolurile, funcționalitățile și schema propusă sunt în [FITLINE_PLAN.md](FITLINE_PLAN.md). În caz de diferențe cu promptul inițial de mai jos, deciziile confirmate din planul principal au prioritate. Secțiunile de prompt păstrează și viziunea ulterioară; nu toate sunt cerințe pentru prezentare.

NOTĂ DE PRIORITATE PENTRU PREZENTARE (actualizată la 2026-11-11)

Programul ședințelor: pagina afișează inițial ședințele din ziua curentă, calculată în fusul orar al sălii. Include un selector de zile (luni, marți, miercuri etc.) cu data calendaristică și ziua selectată evidențiate; poate fi dropdown sau taburi, forma finală rămâne flexibilă. Selectarea unei zile afișează ședințele acelei date, în ordine cronologică, pentru sala vizată. Dacă lista este goală, afișează un mesaj clar. Fiecare ședință are o descriere scurtă, vizibilă în detalii. Acest program este separat de antrenamentele din jurnalul personal.

Descrieri: planul de abonament explică facilitățile incluse și condițiile de acces. Nu sunt necesare descrieri proprii pentru fiecare facilitate în prima versiune. Facilitățile rămân entități/drepturi de acces gestionate din date; textul descrierii planului nu înlocuiește regulile structurate din backend.

Proprietari: un proprietar poate deține mai multe săli, fiecare cu propriile planuri și ședințe. Propunerea tehnică este un utilizator-proprietar legat de săli; un model separat GymOwner nu este încă stabilit. Drepturile administrative trebuie verificate față de sala vizată. Includerea CRUD-ului administrativ și a panoului proprietarului înainte de prezentare rămâne de prioritizat, nu este confirmată prin simpla adăugare a relației de proprietate.

Vizite: include check-in și check-out pentru durata prezenței în sală, separat de checkout-ul de plată. Propunere: o vizită cu ora intrării și ora ieșirii; fără ieșire, durata finală este necunoscută. Regula confirmată pentru acces până la 17:00: membrul trebuie să încheie vizita până la 17:00. Întreaga ședință rezervată trebuie să încapă în intervalul permis. Check-out-ul ulterior limitei salvează ora reală; nu marca automat persoana ca ieșită la 17:00. Nu adăuga penalizări sau hardware fără cerințe noi.

Cerință suplimentară confirmată pentru livrarea de prezentare: cumpărarea abonamentelor în aplicația de membru printr-o integrare funcțională în mediul de test al procesatorului, fără bani reali. Restricțiile orare ale planului se aplică atât vizitei în sală, cât și rezervării ședințelor. Exemplu: pentru un plan cu acces până la 17:00, intrarea este refuzată de la 17:00 inclusiv, iar vizita trebuie încheiată până la acea oră. Intervalele sunt configurabile per plan, nu fixe în cod. Ședința trebuie să se încadreze integral în intervalul permis. Nu presupune integrare cu hardware de control acces. Regulile efective se verifică în backend, nu doar prin dezactivarea unui buton în interfață. Procesatorul/banca nu sunt încă alese; trecerea în producție necesită configurarea și activarea cerute de furnizor, nu este garantată doar prin schimbarea credențialelor.

Prima livrare pentru prezentare este experiența de membru cu mai multe săli. Nu include panoul administratorului de sală sau al unui administrator general. Secțiunile administrative din descrierea de mai jos reprezintă viziunea viitoare și nu sunt obligatorii pentru această livrare. În prototipul pentru prezentare nu este necesar comutatorul către rolul de administrator.

Decizie confirmată pentru facilități: planurile pot include combinații diferite de acces la zona fitness, piscină, saună și clase de grup. Facilitățile nu sunt același lucru cu ședințele programate. Rezervările se implementează pentru ședințe cu dată, oră și capacitate; nu se cer calendare de rezervare separate pentru piscină sau saună în această livrare. Facilitățile și combinațiile planurilor sunt gestionate din date.

Interfața pentru membri va fi conectată ulterior la API-ul Laravel pentru autentificare, program, abonamente și rezervări. Datele demonstrative din prototip nu reprezintă funcționalități backend deja implementate. Modul de pregătire a datelor inițiale și activarea abonamentelor pentru demonstrație se stabilesc separat; lipsa unui panou admin nu elimină regulile de acces și rezervare.

DESCRIEREA PRODUSULUI ȘI VIZIUNEA PE TERMEN LUNG

Creează designul și prototipul frontend pentru Fitline, o platformă de fitness care conectează membrii cu mai multe săli și le permite să gestioneze abonamente, rezervări și jurnalul personal de antrenament.

Interfața trebuie să fie în limba română, modernă, coerentă și complet responsive. Vreau un produs credibil și utilizabil, cu pagini conectate și interacțiuni demonstrabile.

Aplicația are deja un backend Laravel în dezvoltare. Pentru acest prototip folosește date demonstrative și o structură frontend pregătită pentru integrarea ulterioară cu un API. Nu este necesar să implementezi un backend nou, autentificare reală sau plăți reale.

1. IDEEA PRODUSULUI

Fitline are două componente complementare:

A. Platformă pentru săli:
- Descoperirea sălilor și a ședințelor programate.
- Abonamente specifice fiecărei săli.
- Rezervarea locurilor la ședințe.
- Administrarea programului și a membrilor de către administratorii sălii.

B. Jurnal personal:
- Organizarea și urmărirea antrenamentelor individuale.
- Structura: utilizator → săptămâni → antrenamente → exerciții → seturi.
- Fiecare set conține repetări și greutatea utilizată.

Un antrenament din jurnal și o ședință programată la o sală sunt concepte diferite. Păstrează-le distincte în navigare, formulare și denumiri. Nu transforma automat o rezervare într-un antrenament personal.

2. UTILIZATORI ȘI ACCES

Membrul:
- Are un cont personal.
- Explorează săli și programele acestora.
- Consultă și achiziționează ulterior abonamente.
- Rezervă ședințe eligibile.
- Își vede și își anulează rezervările.
- Își gestionează jurnalul personal.
- Consultă notificări și profilul propriu.

Administratorul unei săli:
- Gestionează datele sălii sale.
- Creează, editează și anulează ședințe.
- Configurează planurile de abonament.
- Stabilește ce planuri oferă acces la fiecare ședință.
- Consultă membrii, abonamentele și rezervările sălii.
- Activează inițial manual abonamente pentru membri.
- Va consulta ulterior plățile și notificările relevante.

Fiecare sală are propriile date și propria administrare. Un administrator nu trebuie să vadă datele altor săli prin simplul fapt că are rolul de admin.

Nu crea în această versiune un panou de super admin al platformei. Nu crea un rol separat de antrenor; informațiile despre antrenor sunt opționale în cadrul ședințelor.

Pentru prototip, include un comutator demonstrativ între experiența de membru și cea de administrator.

3. EXPERIENȚA CU MAI MULTE SĂLI

Include un catalog public de săli și o pagină de prezentare pentru fiecare sală.

Pagina unei săli poate include:
- Nume, logo și imagine de copertă.
- Descriere.
- Localizare și informații de contact.
- Programul ședințelor.
- Planurile de abonament.
- Informații relevante despre facilități.

În paginile de program, abonament și rezervare trebuie să fie clar cărei săli îi aparțin datele.

Pentru prototip, permite explorarea mai multor săli din același cont. Prezintă această experiență flexibil, fără a presupune că un abonament este valabil automat la toate sălile.

Un plan „Full Access” aparține unei săli și nu înseamnă acces global la întreaga platformă.

4. PAGINILE MEMBRULUI

Dashboard:
- Salut personalizat.
- Următoarea rezervare.
- Abonamente active și perioadele lor de valabilitate.
- Acces rapid la program și jurnal.
- Activitate recentă.

Explorează săli:
- Carduri pentru săli.
- Căutare după nume și localizare.
- Acces la pagina fiecărei săli.

Program:
- Vizualizare săptămânală și listă pentru mobil.
- Navigare între zile și săptămâni.
- Filtre după sală, dată și denumirea ședinței.
- Carduri cu denumire, sală, dată, oră, durată, antrenor opțional și locuri disponibile.

Detaliile unei ședințe:
- Denumire și descriere.
- Sala și localizarea.
- Data, ora și durata.
- Informații opționale despre antrenor.
- Capacitatea și locurile disponibile.
- Abonamentele care permit accesul.
- Buton de rezervare sau motivul pentru care rezervarea nu este disponibilă.

Rezervările mele:
- Rezervări viitoare și istoric.
- Sala, ședința, data, ora și starea.
- Anularea unei rezervări printr-un dialog de confirmare.
- Confirmare vizibilă după rezervare sau anulare.

Abonamentele mele:
- Sala și planul asociat.
- Data de început și data de expirare.
- Stare: activ, viitor sau expirat.
- Descrierea accesului oferit.
- Acces la planurile disponibile pentru sala respectivă.

Profil și notificări:
- Date personale și preferințe de bază.
- Listă de notificări citite și necitite.
- Exemple: rezervare confirmată, ședință anulată, abonament aproape de expirare.

5. REGULILE REZERVĂRII

Interfața trebuie să poată reprezenta următoarele condiții:

- Utilizatorul trebuie să fie autentificat.
- Trebuie să aibă un abonament valabil pentru data ședinței.
- Planul său trebuie să permită accesul la ședință.
- Trebuie să existe locuri disponibile.
- Nu poate avea două rezervări active la aceeași ședință.
- Nu poate rezerva o ședință anulată sau deja începută.

Arată mesaje clare, de exemplu:
- „Ai deja o rezervare la această ședință.”
- „Nu mai sunt locuri disponibile.”
- „Abonamentul tău nu include această ședință.”
- „Abonamentul expiră înainte de data ședinței.”
- „Ședința a fost anulată.”

Nu inventa liste de așteptare, penalizări, rambursări automate sau termene fixe de anulare. Aceste reguli nu sunt încă stabilite.

6. ABONAMENTE ȘI ACCES

Planurile sunt gestionate din date, nu sunt categorii fixe ale aplicației.

Exemple demonstrative:
- Fitness.
- Fitness + Yoga.
- Full Access.

Fiecare sală își poate crea propriile denumiri, descrieri, prețuri și perioade de valabilitate.

Un abonament stabilește:
- La ce ședințe are acces membrul.
- În ce perioadă este valabil.

Nu există limită lunară de participări. Nu afișa credite, „ședințe rămase”, bare de consum sau limite de tipul „8 participări pe lună”.

Capacitatea reprezintă numărul maxim de participanți la o ședință, nu o limită a abonamentului.

7. JURNALUL PERSONAL

Construiește o zonă separată, numită „Jurnalul meu”.

Flux:
- Utilizatorul creează o săptămână.
- În săptămână adaugă antrenamente.
- În fiecare antrenament adaugă exerciții.
- Pentru fiecare exercițiu adaugă seturi cu kg și repetări.

Exemplu:
Săptămâna 9–15 noiembrie
→ Antrenament Upper Body
→ Împins la piept
→ Set 1: 40 kg × 12 repetări
→ Set 2: 45 kg × 10 repetări

Include:
- Listă de săptămâni.
- Detaliile săptămânii cu antrenamentele sale.
- Detaliile antrenamentului.
- Adăugare și editare de exerciții.
- Introducerea rapidă a seturilor.
- Confirmare înainte de ștergere.
- Căutare într-un catalog demonstrativ de exerciții.

Pe mobil, introducerea greutății și repetărilor trebuie să fie rapidă, cu controale ușor de apăsat în timpul antrenamentului.

Jurnalul este personal și nu trebuie prezentat automat administratorului sălii.

8. PANOUL ADMINISTRATORULUI

Creează un spațiu administrativ distinct, cu numele sălii active vizibil permanent.

Dashboard:
- Ședințele de astăzi.
- Rezervări pentru perioada selectată.
- Abonamente active.
- Grad de ocupare a ședințelor.
- Acțiuni rapide: creează ședință, creează plan, activează abonament.

Gestionarea ședințelor:
- Calendar și listă.
- Creare și editare.
- Denumire, descriere, dată, oră, durată și capacitate.
- Informații opționale despre antrenor.
- Selectarea planurilor care permit accesul.
- Anularea ședinței cu dialog de confirmare.
- Lista rezervărilor și participanților.

Gestionarea planurilor:
- Listă de planuri.
- Formular de creare și editare.
- Denumire, descriere, preț și durată de valabilitate.
- Prezentarea clară a ședințelor la care oferă acces.

Membri și abonamente:
- Căutare membri.
- Detaliile unui membru în contextul sălii.
- Abonamente și rezervări relevante pentru acea sală.
- Activarea manuală a unui abonament: membru, plan, început și expirare.

9. FUNCȚIONALITĂȚI VIITOARE

Pregătește vizual extensii pentru:
- Plăți online pentru abonamente.
- Istoricul plăților și statusul lor.
- Confirmări și notificări prin email și în aplicație.
- Actualizarea disponibilității locurilor în timp real.
- Rapoarte de participare și ocupare pentru administrator.

Plățile din prototip trebuie să fie simulate și marcate ca demonstrative. Nu solicita date reale de card și nu presupune că integrarea Stripe există deja.

Funcțiile AI sunt o posibilitate ulterioară de studiu, nu o cerință a acestei versiuni. Nu adăuga un chatbot sau recomandări AI fără un scop definit.

10. DIRECȚIE VIZUALĂ

Propunere de stil:
- Aspect modern, sportiv și profesionist.
- Fundaluri neutre, cu accent verde sau lime.
- Contrast bun și tipografie lizibilă.
- Spațiere generoasă și ierarhie vizuală clară.
- Carduri curate și etichete discrete pentru stări.
- Fotografii de fitness folosite cu măsură.
- Evită aglomerarea, efectele decorative excesive și aspectul de joc.

Pe desktop:
- Navigare laterală pentru zona autentificată.
- Spațiu suficient pentru calendare și tabele.

Pe mobil:
- Navigare simplă, cu acces rapid la program, rezervări și jurnal.
- Formulare și dialoguri adaptate ecranului.
- Acțiuni principale ușor de accesat.

Verdele de accent nu trebuie să fie singura modalitate de a comunica o stare. Folosește și text sau pictograme.

11. STĂRI ȘI INTERACȚIUNI

Include:
- Loading și skeletons.
- Liste goale cu explicații și acțiuni utile.
- Erori de validare lângă câmpuri.
- Mesaje pentru acces refuzat.
- Confirmări după operații.
- Dialoguri pentru anulare și ștergere.
- Stări pentru abonament expirat, ședință completă și ședință anulată.
- Focus vizibil și navigare accesibilă cu tastatura.

Folosește date demonstrative coerente:
- Cel puțin trei săli diferite.
- Planuri diferite pentru fiecare sală.
- Ședințe cu grade diferite de ocupare.
- Rezervări și abonamente în mai multe stări.
- Un jurnal personal cu câteva săptămâni și antrenamente.

Datele trebuie să demonstreze că abonamentele, ședințele și rezervările aparțin unor săli precise.

12. FLUXURI CARE TREBUIE SĂ FUNCȚIONEZE ÎN PROTOTIP

Membru:
Explorează o sală
→ vede programul
→ deschide o ședință
→ verifică eligibilitatea
→ rezervă
→ vede rezervarea în „Rezervările mele”.

Administrator:
Deschide panoul sălii
→ creează un plan de abonament
→ creează o ședință și selectează planurile eligibile
→ activează manual un abonament pentru un membru
→ consultă rezervările.

Jurnal:
Creează o săptămână
→ adaugă un antrenament
→ adaugă un exercițiu
→ completează seturile.

Prioritizează claritatea acestor fluxuri. Paginile și butoanele trebuie să fie conectate, iar starea demonstrativă să se actualizeze după acțiuni.
