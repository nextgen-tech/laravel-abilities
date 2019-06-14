# Laravel Abilities

Spis treści

* [Instalacja](#instalacja)
    * [composer](#composer)
    * [Service Provider](#service-provider)
    * [Middleware](#middleware)
    * [Kopiowanie plików](#kopiowanie-plików)
    * [Migracje](#migracje)
* [Konfiguracja](#konfiguracja)
* [Definicja uprawnień](#definicja-uprawnień)
    * [Grupy uprawnień](#grupy-uprawnień)
    * [Pojedyncze uprawnienie](#pojedyncze-uprawnienie)
        * [Aliasy](#aliasy)
        * [Własna funkcja](#własna-funkcja)
    * [Zasoby uprawnień](#zasoby-uprawnień)
* [Sprawdzanie uprawnień](#sprawdzanie-uprawnień)

## Instalacja

### composer

W terminalu wpisz

```sh
composer require nextgen-tech/laravel-abilities:^1.0
```

### Service Provider

> Jeśli aplikacja korzysta z Laravela w wersji 5.5 lub wyższej to możesz pominąć ten krok.

W pliku `config/app.php` do listy providerów dopisz:

```php
'providers' => [
    ...
    NGT\Laravel\Abilities\AbilityServiceProvider::class
]
```

### Middleware

W pliku `app/Http/Kernel.php` do grupy `web` należy dopisać:

```php
protected $middlewareGroups = [
    'web' => [
        ...
        \NGT\Laravel\Abilities\Middleware\CheckUserAbilities::class
    ]
]
```

### Kopiowanie plików

Aby skopiować z paczki niezbędne pliki wykonaj następujące polecenie:

```sh
php artisan vendor:publish --provider="NGT\\Laravel\\Abilities\\AbilityServiceProvider"
```

Można także opublikować część plików podając jeden z tagów: `config`, `translations`, `models` lub `migrations`:

```sh
php artisan vendor:publish --provider="NGT\\Laravel\\Abilities\\AbilityServiceProvider" --tag=config
```

### Migracje

Uruchom migracje

```sh
php artisan migrate
```

## Konfiguracja

Konfiguracja paczki znajduje się w pliku `config/abilities.php`.

```php
return [
    'path'   => base_path('routes/abilities.php'), // Ścieżka do definicji uprawnień

    'models' => [
        'user'               => App\User::class, // Model użytkowników
        'user_ability'       => App\UserAbility::class, // Model uprawnień użytkowników 

        'user_group'         => App\UserGroup::class, // Model grup użytkowników
        'user_group_ability' => App\UserGroupAbility::class, // Model uprawnień grup użytkowników
    ],
];
```

## Definicja uprawnień

Domyślnie uprawnienia znajdują się w pliku `routes/abilities.php`. Ścieżkę tę można zmienić w konfiguracji.

Definiować uprawnienia należy za pomocą fasady `NGT\Laravel\Abilities\Facades\Ability` lub instancji klasy `NGT\Laravel\Abilities\AbilityRegistrar`.

### Grupy uprawnień

```php
Ability::group(array $attributes, callable $abilities)
```

Grupy definiujemy za pomocą metody `group`. Grupa może składać się z wyświetlanej nazwy (`label`) oraz przedrostka (`prefix`), ale żadna z tych opcji nie jest obowiązkowa. Grupa może także zawierać w sobie inne grupy. Dane grupy są przekazywane do podrzędnych elementów znajdujących się w anonimowej funkcji.

```php
Ability::group(['label' => 'Panel administracyjny', 'prefix' => 'admin'], function() {
    ...
})
```

### Pojedyncze uprawnienie

```php
Ability::define(string $slug, string $label, array $options = [])
```

Każde uprawnienie posiada swoją uproszczoną nazwe (`$slug`) oraz wyświetlaną nazwe (`$label`). Dodatkowo można zdefiniować opcje (`$options`).

#### Aliasy

Każde uprawnienie może posiadać aliasy. Najlepszym przykładem są akcje `create-store` oraz `edit-update`. Aby zarządzać nimi jako jednym uprawnieniem można je zdefiniować w następujący sposób:

```php
Ability::define('create', 'Tworzenie', [
    'aliases' => ['store'],
]);
```

#### Własna funkcja

Aby uprawnienie nie korzystało z domyślnej metody weryfikacji można przekazać do niego funkcję. Funkcja ta zostanie wykonana zamiast domyślnej.

```php
Ability::define('index', 'Wyświetlanie', [
    'callback' => function($user, $ability) {
        return $user->is_activated;
    }
])
```

### Zasoby uprawnień

```php
Ability::resource(string $prefix, string $label, array $options = [])
```

Podobnie jak w routingu, w uprawnieniach również można definiować zasoby. Podczas tworzenia zasobu należy podać przedrostek grupy (`$prefix`) oraz wyświetlaną nazwę grupy (`$label`).

```php
Ability::resource('user', 'Użytkownicy');
```

Dodatkowo można przekazać opcje wykluczace lub dopuszczające tylko wybrane uprawnienia.

```php
Ability::resource('user', 'Użytkownicy', [
    'only' => ['index', 'create', 'edit'],
]);

Ability::resource('user', 'Użytkownicy', [
    'except' => ['show', 'destroy'],
]);
```

## Sprawdzanie uprawnień

Kaźde uprawnienie można sprawdzać za pomocą standardowych metod klasy `Gate`:

```php
Gate::allows('admin.user.index');
@can('admin.user.index');
Route::get('/', 'UserController@index')->middleware('can:admin.user.index');
```

Aby kontroler automatycznie sprawdzał uprawnienia do danych akcji można zdefiniować w nim listę powiązań między akcjami a uprawnieniami

```php
class UserController extends Controller
{
    ...
    
    public $actionAbilities = [
        'index'  => 'admin.user.index',
        'create' => 'admin.user.create',
    ];
    
    ...
}