# Laravel — ghidul meu de recapitulare pe Fitline

Actualizat: **24 noiembrie 2026**. Backend verificat în sesiunile de lucru: Laravel **13.31.0**, PHP **8.4**.

Acesta este ghidul pentru reamintirea conceptelor, cu exemple din proiect. [LEARNING_PROGRESS.md](../LEARNING_PROGRESS.md) păstrează istoricul și punctul de reluare; [FITLINE_PLAN.md](../FITLINE_PLAN.md) păstrează cerințele produsului. Aici nu repetăm întregul istoric.

**Cum citesc etichetele:**

- **În proiect** — exemplul există în fișierele indicate. Nu înseamnă că orice variantă a conceptului a fost exersată.
- **Doar explicat** — exemplu discutat pentru înțelegere; nu trebuie presupus că este implementat.
- **Rămas** — subiect încă neaprofundat, fără un exemplu prezentat ca muncă terminată.

## Cuprins

- [10.1 Ciclul de viață al unui request](#101-ciclul-de-viață-al-unui-request)
- [10.2 Service Container](#102-service-container)
- [10.3 Service Providers](#103-service-providers)
- [10.4 Facades, Contracts și injectare](#104-facades-contracts-și-injectare)
- [10.5 PHP Attributes](#105-php-attributes)
- [11.1 Accessors, mutators și casts](#111-accessors-mutators-și-casts)
- [11.2 Query scopes](#112-query-scopes)
- [Fundamente folosite în exemple](#fundamente-folosite-în-exemple)
- [Subiecte aplicate anticipat și ce rămâne](#subiecte-aplicate-anticipat-și-ce-rămâne)
- [Ce aleg și când](#ce-aleg-și-când)

## 10.1 Ciclul de viață al unui request

**Ideea:** cererea trece prin pornirea aplicației, middleware, rută și controller, apoi răspunsul se întoarce către client.

```text
public/index.php → bootstrap/app.php → inițializare și middleware
→ rută și model binding → controller → răspuns
→ revenire prin middleware → client
```

### Exemplul nostru — TraceRequest

**În proiect:** [TraceRequest.php](../app/Http/Middleware/TraceRequest.php).

```php
Log::info('TraceRequest: inainte');
$response = $next($request);
Log::info('TraceRequest: dupa');

return $response;
```

`$next($request)` lasă cererea să continue. Codul de după el se execută la revenirea răspunsului. În exercițiu am observat logurile înainte/după pe lista săptămânilor. Middleware-ul este atașat acelei rute, nu tuturor rutelor de detalii.

### Configurarea aplicației

**În proiect:** [bootstrap/app.php](../bootstrap/app.php).

| Configurare | Ce face | Exemplul nostru |
|---|---|---|
| `withRouting()` | Indică fișierele de rute | `api: __DIR__.'/../routes/api.php'` |
| `withMiddleware()` | Configurează middleware și aliasuri | Aliasul `role` pentru middleware-ul Spatie |
| `withExceptions()` | Configurează tratarea excepțiilor | Răspuns JSON pentru API și mesajul `Resource not found` cu 404 |

Un alias doar dă un nume middleware-ului; nu îl atașează automat unei rute. Logul intern nu este răspunsul JSON trimis utilizatorului.

**Comandă de reamintire, fără modificări:**

```bash
php artisan route:list --path=api/v1/weeks -vv
```

Arată ruta, controllerul și middleware-urile rutei; nu este inventarul complet al tuturor middleware-urilor globale.

## 10.2 Service Container

**Ideea:** containerul construiește obiecte, rezolvă dependențele lor și le furnizează codului care le cere.

### Rezolvare automată și injectare

**În proiect:** [MembershipController.php](../app/Http/Controllers/MembershipController.php).

```php
public function index(PaginationRequest $request, MembershipService $service)
```

Laravel furnizează serviciul prin parametrul tipizat. Nu scriem `new MembershipService()` în controller. Pentru această clasă concretă nu a fost necesar un binding propriu.

**De reținut:** `use App\Services\MembershipService` identifică numele clasei; importul nu creează obiectul.

### Interfață și binding

**În proiect:** [ExerciseCatalog.php](../app/Services/ExerciseCatalog.php), [WorkoutXExerciseService.php](../app/Services/WorkoutXExerciseService.php), [AppServiceProvider.php](../app/Providers/AppServiceProvider.php).

```php
$this->app->bind(
    ExerciseCatalog::class,
    WorkoutXExerciseService::class,
);
```

Interfața declară operațiile `find()` și `search()`. Clasa `WorkoutXExerciseService implements ExerciseCatalog` le implementează. Controllerul cere interfața, iar containerul oferă implementarea configurată:

```php
public function index(Request $request, ExerciseCatalog $workoutx)
```

**De ce:** putem schimba implementarea din binding fără să rescriem controllerul, dacă noua clasă respectă contractul. Nu înseamnă că integrarea unui furnizor nou se realizează fără cod.

### bind, singleton și scoped

| Variantă | Ideea | Ce am făcut |
|---|---|---|
| Rezolvare normală / `bind` | În mod obișnuit, o instanță nouă la fiecare rezolvare, dacă nu am cerut partajare | Compararea a două rezolvări a dat `false`; binding-ul catalogului există |
| `singleton` | Aceeași instanță reutilizată de acel container | Exercițiu temporar în Tinker: comparația a dat `true`; fără singleton permanent adăugat |
| `scoped` | Aceeași instanță în același ciclu de request/job, resetată între cicluri gestionate de Laravel | Doar explicat; nu am creat un context global pentru sala curentă |

Un singleton nu este automat un singur obiect comun tuturor serverelor, proceselor și utilizatorilor.

### Contextual binding

**Doar explicat:** regulă generală plus o excepție pentru un anumit consumator.

```text
Regulă generală: când se cere contractul, oferă implementarea obișnuită.
Excepție: when(ControllerulX) → needs(Contractul) → give(ImplementareaY).
```

Nu am adăugat clase fictive în proiect pentru această demonstrație. Contextual binding decide ce dependență furnizează containerul; nu este autorizare și nu izolează singur datele sălilor.

## 10.3 Service Providers

**Ideea:** clase prin care înregistrăm și inițializăm servicii/configurări ale aplicației.

**În proiect:** [AppServiceProvider.php](../app/Providers/AppServiceProvider.php).

```php
public function register(): void
{
    $this->app->bind(ExerciseCatalog::class, WorkoutXExerciseService::class);
}
```

| Metodă | Ce punem acolo | Exemplul nostru |
|---|---|---|
| `register()` | Înregistrarea binding-urilor | Contractul `ExerciseCatalog` → implementarea WorkoutX |
| `boot()` | Inițializări după înregistrarea providerelor | Definirea rate limiter-ului `api` |

Lista providerelor aplicației este în [bootstrap/providers.php](../bootstrap/providers.php). Un provider nu este controller și nu primește cererea API ca o acțiune HTTP.

### Providers deferred

**Doar explicat:** un provider poate implementa `DeferrableProvider`, iar `provides()` declară serviciile sale. Laravel îl poate amâna până când este cerut unul dintre acele servicii. Nu am creat `PaymentServiceProvider` sau `ReportingServiceProvider` în proiect.

## 10.4 Facades, Contracts și injectare

### Facade

**Ideea:** acces cu sintaxă de apel static la un serviciu gestionat prin container.

**În proiect:**

```php
Log::info('TraceRequest: inainte');
Gate::authorize('view', $record);
DB::transaction(function (): void {
    // Operațiile care trebuie salvate împreună.
});
```

`Log`, `Gate` și `DB` din exemple sunt facade-uri Laravel. `Gym::query()` și `Week::find(...)` folosesc mecanismul Eloquent al modelului; nu sunt facade-uri din `Illuminate\Support\Facades`.

### Contracts și injectare

**În proiect:** contractul propriu `ExerciseCatalog`, cerut de controller. Contractele Laravel sunt interfețe ale serviciilor frameworkului. Nu trebuie să inventăm o interfață pentru fiecare serviciu al aplicației.

| Alegere | Exemplu | De ce |
|---|---|---|
| Clasă concretă injectată | `MembershipService $service` | Serviciu al aplicației, dependență vizibilă |
| Contract injectat | `ExerciseCatalog $workoutx` | Implementare aleasă din container |
| Facade Laravel | `Gate::authorize(...)` | Apel convenabil către serviciul frameworkului |

**Real-time facades — doar explicat:** importul cu prefix `Facades\...` permite apelarea unei clase/depedențe prin mecanismul de facade. Nu am înlocuit injectarea catalogului cu un real-time facade.

### Mocking

**Doar explicat prin acest exemplu:**

```php
Cache::expects('get')
    ->with('gym_name')
    ->andReturn('Arena');
```

În contextul testului, apelul așteptat `Cache::get('gym_name')` primește `Arena`. Este configurarea unui mock; nu scrie `Arena` în cache-ul real. Exemplul nu a fost adăugat ca test nou de cache.

### Tranzacții — aprofundare pornită de la DB

**În proiect:** [GymOpeningHourService.php](../app/Services/GymOpeningHourService.php).

```php
DB::transaction(function () use ($gym, $validated): void {
    foreach ($validated['hours'] as $day) {
        $gym->openingHours()->updateOrCreate(
            ['day_of_week' => $day['day_of_week']],
            ['opens_at' => $day['opens_at'], 'closes_at' => $day['closes_at']],
        );
    }
});
```

Cele șapte zile trebuie salvate împreună. La succes se face commit; o excepție care iese din callback provoacă rollback. `return false` nu cere automat rollback. O tranzacție nu înlocuiește validarea sau autorizarea și nu este necesară pentru orice update simplu.

### Practica PaymentGateway

**Recapitulare conceptuală, fără implementarea separată a exercițiului:**

```text
Controller → PaymentGateway → FakeGateway sau StripeGateway
                           ales prin Service Provider
```

Schimbăm binding-ul în provider, nu implementarea în controller. Exemplul real analog este `ExerciseCatalog`. Nu avem prin acest exercițiu o integrare de plăți finalizată.

## 10.5 PHP Attributes

**Ideea:** sintaxa `#[...]` atașează informații structurate pe care PHP sau frameworkul le interpretează.

### Authorize — exemplul implementat

**În proiect:** [WeekController.php](../app/Http/Controllers/WeekController.php), cu importul `Illuminate\Routing\Attributes\Controllers\Authorize`.

```php
#[Authorize('update', 'week')]
public function update(UpdateWeekRequest $request, Week $week)
```

```text
'week' → parametrul {week} din rută
      → obiect Week prin model binding
      → middleware-ul de autorizare
      → Gate
      → WeekPolicy::update(utilizatorul curent, obiectul Week)
```

Policy-ul conține regula:

```php
return $user->id === $week->user_id;
```

Attribute-ul declară verificarea înainte de metoda controllerului. Alternativa în interior este `Gate::authorize('update', $week)`. Nu cerem ambele pentru aceeași verificare. La creare folosim `#[Authorize('create', Week::class)]`, fiindcă obiectul încă nu există.

### Celelalte exemple discutate

**Doar explicate; nu adăugate automat în aplicație:**

| Attribute | Exemplu | Ce rețin |
|---|---|---|
| `Middleware` | `#[Middleware('auth:sanctum')]` pe controller | Atașează middleware; în proiect autentificarea este deja pe grupul de rute |
| `UsePolicy` | `#[UsePolicy(WeekPolicy::class)]` pe model | Asociază explicit policy-ul; nu cere singur autorizarea. La noi convenția Week → WeekPolicy funcționează |
| `Storage` | `#[Storage('public')]` lângă un parametru `Filesystem` | Containerul injectează discul ales; nu salvează automat un fișier |
| `Config` | `#[Config('app.name')]` lângă un parametru string | Injectează configurația; nu este obligatoriu în locul oricărui `config(...)` |
| `Deprecated` | `#[\Deprecated('Folosește metoda nouă')]` | PHP 8.4 emite `E_USER_DEPRECATED` la utilizare; păstrăm o tranziție pentru apelurile vechi |
| `Tries` | `#[Tries(3)]` pe job | Maximum trei încercări în total, cu oprire la succes |
| `Backoff` | `#[Backoff(10)]` pe job | Întârziere înainte de reîncercarea după excepție |
| `Timeout` | `#[Timeout(60)]` pe job | Limită pentru o încercare, nu pentru toate împreună; workerul are nevoie de PCNTL pentru acest mecanism |
| `FailOnTimeout` | `#[FailOnTimeout]` pe job | La timeout jobul este marcat eșuat, fără retry automat pentru acel timeout |
| `StopOnFirstFailure` | Attribute pe Form Request | Oprește validarea celorlalte câmpuri după primul eșec |
| `FailOnUnknownFields` | Attribute pe Form Request | Respinge câmpurile care nu sunt declarate în regulile requestului |

Jobul [CreateWorkoutReport.php](../app/Jobs/CreateWorkoutReport.php) există, dar `handle()` doar scrie un log; exemplele de attributes nu înseamnă că generarea unui raport real sau retry-ul au fost puse în funcțiune.

## 11.1 Accessors, mutators și casts

### Trei sensuri care ne-au încurcat

| Termen | Exemplu | Sens |
|---|---|---|
| PHP Attribute | `#[Authorize(...)]` | Metadate pentru PHP/framework |
| Atribut Eloquent | `$set->kg`, `$set->volume` | Valoare a modelului, stocată sau calculată |
| Clasa Eloquent `Attribute` | `Attribute::make(get: ...)` | Configurează citirea/atribuirea unui atribut Eloquent |

`Illuminate\Database\Eloquent\Casts\Attribute` nu este același mecanism cu sintaxa `#[...]`.

### Accessor — get

**În proiect:** [Set.php](../app/Models/Set.php).

```php
protected function volume(): Attribute
{
    return Attribute::make(
        get: fn (): float => $this->kg * $this->reps,
    );
}
```

```php
$set->volume; // 60 kg × 10 repetări → 600
```

`get` configurează citirea. `fn` este funcția anonimă scurtă; `()` înseamnă fără parametri; `: float` este tipul rezultatului; expresia după `=>` este returnată automat. Metoda `volume(): Attribute` returnează configurația, nu direct numărul. Eloquent folosește acea configurație când citim proprietatea.

Nu este o coloană nouă și calculul seriei nu face un nou SELECT. O metodă obișnuită `calculateVolume()` ar fi și ea corectă; accessor-ul este comod când tratăm rezultatul ca un câmp, inclusiv la serializare. Formatarea pur vizuală „sală — oraș” poate rămâne în frontend.

### De la serie la totalul antrenamentului

**În proiect:** [Exercise.php](../app/Models/Exercise.php), [Workout.php](../app/Models/Workout.php).

```php
// Getter-ul exercițiului:
get: fn (): float => $this->sets->sum('volume')

// Getter-ul antrenamentului:
get: fn (): float => $this->exercises->sum('volume')
```

`sum('volume')` primește numele proprietății: parcurge colecția și adună `$set->volume` sau `$exercise->volume`. `$this` este obiectul clasei în care suntem. Nu citim `$this->volume` în getter-ul propriei valori `volume`.

### Includerea în JSON

**În proiect, pe Set:**

```php
protected $appends = ['volume'];
```

Include volumul la serializarea instanțelor Set. Numele `$appends` este citit de mecanismul Eloquent; `protected` nu produce singur acest efect.

**În proiect, doar în detaliile antrenamentului:** [WorkoutController.php](../app/Http/Controllers/WorkoutController.php).

```php
$workout->load('exercises.sets');
$workout->append('volume');

return response()->json($workout);
```

`append()` configurează această instanță pentru serializare; nu salvează valoarea în BD. Nu avem `$appends` global pentru Exercise sau Workout. Totalul exercițiului este folosit intern, fără expunere automată în JSON. Încărcăm relațiile înaintea calculului ca să evităm interogările separate pentru fiecare exercițiu.

### Mutator — set

**Doar explicat; nu adăugat în User:**

```php
protected function email(): Attribute
{
    return Attribute::make(
        set: fn (string $value): string => strtolower($value),
    );
}
```

```php
$user->email = 'Bogdan@Example.COM'; // modelul primește bogdan@example.com
$user->save(); // abia aici persistăm schimbarea în BD
```

Transformarea se produce la atribuire în model. Nu este o regulă de validare și nu modifică automat înregistrările vechi.

### Casts

**În proiect:** [Exercise.php](../app/Models/Exercise.php), [Gym.php](../app/Models/Gym.php), [Membership.php](../app/Models/Membership.php).

```php
// Exercise:
'instructions' => 'array',

// Gym:
'is_active' => 'boolean',

// Membership:
'starts_at' => 'immutable_datetime',
```

| Cast | Utilitate |
|---|---|
| `array` | Lucrăm cu array PHP în loc să repetăm json_decode/json_encode pentru instrucțiuni |
| `boolean` | Lucrăm cu true/false pentru starea sălii |
| `immutable_datetime` | Lucrăm cu obiecte CarbonImmutable pentru perioada abonamentului |

Cast este mecanismul general, nu sinonim cu boolean. El nu schimbă singur schema BD. CarbonImmutable întoarce un obiect nou la operații precum adăugarea zilelor; păstrăm momentele concrete în UTC și afișăm după fusul relevant.

**Rămas din 11.1:** custom casts, enum casts, AsCollection și value objects — nu au fost încă aprofundate practic.

## 11.2 Query scopes

**Ideea:** filtre reutilizabile pentru interogări; nu sunt calcule de proprietăți și nu sunt requesturi HTTP.

### Scope local fără parametri

**În proiect:** [Gym.php](../app/Models/Gym.php); analog și în [MembershipPlan.php](../app/Models/MembershipPlan.php).

```php
#[Scope]
protected function active(Builder $query): void
{
    $query->where('is_active', true);
}
```

```php
Gym::query()->active()->get();
```

`#[Scope]` marchează metoda ca scope; importurile sunt `Illuminate\Database\Eloquent\Attributes\Scope` și `Illuminate\Database\Eloquent\Builder`. Eloquent furnizează Builder-ul. Condiția este adăugată interogării; `get()` sau `paginate()` o execută ulterior. Rezultatul este același ca la scrierea directă a acelui `where`.

### Scope local cu parametru

**Scris manual și conectat la API:**

```php
#[Scope]
protected function inCity(Builder $query, string $city): void
{
    $query->where('city', $city);
}
```

```php
Gym::query()->active()->inCity('Chișinău')->get();
```

Noi transmitem orașul; Eloquent furnizează `$query`. Nu apelăm `inCity($query, 'Chișinău')` și nu căutăm parțial denumiri cu acest scope. Este egalitate pe `city`.

### Traseul complet al filtrului din API

```text
GET /api/v1/gyms?city=Chișinău
→ ListGymsRequest validează city
→ GymController citește cityFilter()
→ GymService aplică active() și, dacă există oraș, inCity($city)
→ paginate()
→ GymResource și răspuns JSON
```

Fișiere: [ListGymsRequest.php](../app/Http/Requests/ListGymsRequest.php), [GymController.php](../app/Http/Controllers/GymController.php), [GymService.php](../app/Services/GymService.php).

Filtrul este opțional. Gol/null înseamnă fără filtru de oraș. String de maximum 255 de caractere; array sau text prea lung → 422. Paginarea păstrează `city` și `per_page` prin `appends()` pe paginator. Acest `appends()` pentru parametri de link este diferit de `$appends`/`append()` pe model pentru câmpuri JSON.

### Scope global și eliminarea lui punctuală

**Doar explicat la 24 noiembrie; nu adăugat în Gym:**

```php
protected static function booted(): void
{
    static::addGlobalScope('active', function (Builder $query): void {
        $query->where('is_active', true);
    });
}
```

Dacă acest exemplu ar fi înregistrat, `Gym::all()` ar exclude implicit inactivele. `Gym::query()->where('city', 'Bălți')->get()` ar returna numai activele din Bălți. Scope-ul global se adaugă automat interogărilor Eloquent pentru model, nu tuturor interogărilor SQL ale aplicației.

```php
Gym::query()
    ->withoutGlobalScope('active')
    ->where('city', 'Bălți')
    ->get();
```

Elimină doar scope-ul global numit `active`, pentru acea interogare; filtrul orașului rămâne. Exemplu de mecanism Laravel bazat pe scope global: Soft Deletes, încă neimplementat ca exercițiu propriu.

**Decizia proiectului:** păstrăm `active()` local, fiindcă administrarea viitoare trebuie să poată consulta și inactivele. Nu introducem acum filtre globale automate după utilizator sau sala curentă.

## Fundamente folosite în exemple

Acestea sunt recapitulări din codul existent și discuțiile noastre, nu o declarare retroactivă a fiecărui subpunct din cursul inițial drept evaluat separat.

### Relații Eloquent

**În proiect:** jurnalul `User → Week → Workout → Exercise → Set`.

```php
// Exercise are mai multe serii:
return $this->hasMany(Set::class);

// Set aparține unui exercițiu:
return $this->belongsTo(Exercise::class);
```

`$exercise->sets()` construiește o interogare de relație; `$exercise->sets` obține colecția obiectelor. Dacă relația nu este încărcată, citirea poate declanșa o interogare.

### Eager loading și N+1

**În proiect:** `WorkoutController::show()` încarcă `exercises.sets`; lista săptămânilor încarcă `workouts`.

```php
$workout->load('exercises.sets');
```

Încărcăm relațiile necesare în grup, evitând câte o interogare pentru fiecare obiect din listă. Nu încărcăm toate relațiile pretutindeni doar pentru că există. `ExerciseController::index()` conține și el `with('sets')`, deși am discutat că necesitatea lui depinde de datele afișate.

### Migrations și chei externe

**În proiect:** [migration-ul sets](../database/migrations/2026_09_11_094503_create_sets_table.php).

```php
$table->foreignId('exercise_id')->constrained()->cascadeOnDelete();
$table->integer('reps');
$table->decimal('kg', 5, 2);
```

Migration-ul descrie schema. Cheia externă leagă seria de exercițiu; în acest caz ștergerea exercițiului șterge seriile. Nu alegem aceeași politică automat pentru viitoarele plăți și istoric comercial.

### fillable și associate

**În proiect:** `Set::$fillable` permite atribuirea în masă pentru `kg` și `reps`. Lista nu este validare și nici autorizare.

```php
$membership->user()->associate($user);
```

**Explicat anterior:** asocierea completează cheia străină din obiect. Persistarea necesită `save()` sau o operație care salvează. Crearea datelor demo a fost mutată ulterior în factory/seeder, nu păstrată ca operație demo în serviciul aplicației.

### Form Requests

**În proiect:** fișierele din `app/Http/Requests`; exemplu [ListGymsRequest.php](../app/Http/Requests/ListGymsRequest.php).

```php
class ListGymsRequest extends PaginationRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'city' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
```

`PaginationRequest` extinde `FormRequest`; moștenim regulile generale și adăugăm filtrul specific sălilor. Înainte să execute controllerul, Laravel verifică autorizarea requestului și validarea. Date invalide într-o cerere JSON → 422; metoda controllerului nu rulează.

Validarea poate fi scrisă și în controller prin `$request->validate(...)`. Form Request o organizează separat. Validarea din frontend ajută utilizatorul, dar backendul trebuie să valideze independent.

### Autentificare și autorizare

**În proiect:** Sanctum pe grupul API, Policies pentru drepturile asupra obiectelor.

```php
->middleware('auth:sanctum')

Gate::authorize('view', $record);
```

Autentificare = cine este utilizatorul. Autorizare = ce poate face asupra acelui obiect. Faptul că un utilizator este autentificat nu îi oferă săptămânile sau abonamentele altuia.

### API Resources și Service Layer

**În proiect:** [MembershipController.php](../app/Http/Controllers/MembershipController.php), [MembershipService.php](../app/Services/MembershipService.php), [MembershipResource.php](../app/Http/Resources/MembershipResource.php).

```php
$record = $service->findForUser($request->user(), (int) $membership);
Gate::authorize('view', $record);

return new MembershipResource($record);
```

Serviciul obține datele/operează regulile. Policy-ul autorizează. Resource-ul stabilește câmpurile și forma JSON. `toArray()` produce array-ul, iar mecanismul `JsonResource` construiește răspunsul. Pentru liste folosim `Resource::collection(...)`.

### Factories, seeders și teste

**În proiect:** [MembershipFactory.php](../database/factories/MembershipFactory.php), [MembershipSeeder.php](../database/seeders/MembershipSeeder.php), [FrontendIntegrationTest.php](../tests/Feature/FrontendIntegrationTest.php), [GymApiTest.php](../tests/Feature/GymApiTest.php).

```php
Exercise::factory()
    ->count(2)
    ->hasSets(2, ['kg' => 62.5, 'reps' => 3]);
```

Factory-ul descrie date de test; în test este folosit în relația antrenamentului și salvat prin `create()`. Seeder-ul pregătește date demonstrative, nu este endpoint de cumpărare a abonamentului.

```php
->assertJsonPath('volume', 750)
->assertJsonPath('exercises.0.sets.0.volume', 187.5)
```

Patru serii de 187,5 dau totalul 750. Alte teste verifică proprietatea datelor, excluderea inactivelor, validarea orașului și păstrarea filtrului pe pagina următoare. Pentru filtrarea orașului am scris testele înaintea implementării: eșec așteptat → implementare → teste trecute, exemplu de ciclu red/green din TDD.

```bash
php artisan test --compact tests/Feature/GymApiTest.php
php artisan test --compact tests/Feature/FrontendIntegrationTest.php --filter=test_workout_details_include_exercises_and_sets_in_one_response
```

Aceste comenzi folosesc configurația de test SQLite în memorie a proiectului. Rezultatele istorice sunt în progres; ghidul nu reprezintă o nouă rulare a testelor.

## Subiecte aplicate anticipat și ce rămâne

| Subpunct din curs | Ce avem până acum | Ce nu declarăm încă parcurs complet |
|---|---|---|
| 11.6 Tranzacții | `DB::transaction()` pentru programul sălii | lockForUpdate, soft deletes și pruning |
| 11.7 Interogări și strict mode | Prevenirea N+1, test pentru numărul interogărilor săptămânilor | EXPLAIN, aprofundarea indexurilor, exercițiul strict mode |
| 12 Arhitectură | Service Layer, Requests, Policies, Resources | Toate variantele Actions/DTO/enums/state machines; nu instalăm automat pachetele din PDF |
| 13–14 Evenimente și cozi | Exemple conceptuale de retry și jobul de log existent | Fluxuri complete de events/listeners/notificări, worker și scheduling de produs |
| 15 Testare | PHPUnit, factories/relații, aserțiuni JSON și teste de acces; Http::fake în testele WorkoutX existente | Toate instrumentele avansate, browser testing, mutation testing și analiză statică |
| 16 Storage și integrare externă | Fotografii demo pentru săli și HTTP Client WorkoutX cu timeout | Upload complet de avatar, căutare avansată și procesator de plăți funcțional |

**Până aici cursul reluat a ajuns la 11.2.** Subpunctele 11.3–11.5 nu sunt prezentate ca lecții încheiate. Etapa 17 este amânată, 18 vine după fluxurile funcționale, iar aprofundarea 19 este amânată. Viitoarele lecții se adaugă aici când sunt explicate/exersate.

## Ce aleg și când

| Am nevoie să… | Mecanism | Exemplul meu |
|---|---|---|
| Citesc o valoare calculată a unui obiect | Accessor | `$set->volume` |
| Transform valoarea atribuită unui câmp | Mutator | Email lowercase, doar exemplu |
| Lucrez cu tipul PHP potrivit | Cast | `instructions` ca array |
| Reutilizez o condiție de interogare | Scope local | `Gym::query()->active()` |
| Aplic implicit un filtru pe model | Scope global | Exemplu conceptual; nu îl impunem sălilor |
| Validez datele HTTP | Form Request | `ListGymsRequest` |
| Verific dreptul asupra unui obiect | Policy + Gate/Authorize | Modificarea săptămânii proprii |
| Furnizez o dependență | Container | `MembershipService $service` |
| Aleg implementarea unui contract | Binding în provider | `ExerciseCatalog` → WorkoutX |
| Controlez ce trimite API-ul | Resource / serializare explicită | `MembershipResource`, `append('volume')` |
| Coordonez o operație cu reguli | Service Layer | `MembershipAccessService` |

**Traseu de reținut:** request validat → controller → autorizare și serviciu → modele/interogări → Resource sau serializare → JSON. Ordinea concretă a autorizării depinde de folosirea middleware-ului/attribute-ului sau a unui apel explicit în controller.
