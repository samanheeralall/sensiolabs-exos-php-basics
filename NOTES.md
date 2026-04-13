# Notes — Formation PHP Basics

## Passage par copie / référence — le vrai fonctionnement sous le capot

### Les 3 mots clés : zval, refcount, copy-on-write

- Chaque variable PHP est un **zval** (structure C interne) qui contient : la valeur, le type, un refcount
- `$a = 5; $b = $a;` → PAS de copie. Les deux pointent vers le même zval, refcount = 2
- La copie ne se fait QUE quand on modifie → c'est le **copy-on-write (COW)**
- PHP dit "passage par copie" mais c'est en réalité du "passage par partage avec copie paresseuse"

### Passage par référence (&)

- `$b = &$a;` → le zval est marqué `is_ref = true`
- Plus de COW : toute modification est partagée, c'est voulu
- COW et références sont incompatibles → si les deux coexistent, PHP force une séparation (copie immédiate)

### Conséquence pratique

- Passer un gros tableau à une fonction par copie n'est PAS un problème de perf tant que la fonction ne le modifie pas (COW protège)
- Le `&` n'est utile que si on VEUT modifier la variable originale

## readonly et getters/setters

- `public readonly` → getter ET setter inutiles (accès direct, immutable)
- `private readonly` → getter utile (seul moyen d'accéder), setter inutile
- Convention Symfony : `public readonly` pour les DTOs/Value Objects, getters sur les entités Doctrine

## EventDispatcher — le pattern Observer

- C'est juste un tableau associatif : clé = nom d'événement, valeur = liste de callables
- `addListener` = stocker une fonction dans le tableau
- `dispatch` = parcourir le tableau et appeler chaque fonction
- `$eventName ??= $event::class` → si pas de nom fourni, on utilise le FQCN de l'objet comme nom
- `dispatch` retourne l'event pour qu'on puisse lire les modifications faites par les listeners

## Closures et Callables

### callable = "est-ce que ça s'appelle avec () ?"

C'est un pseudo-type, pas un vrai type. Ça désigne tout ce qui peut être exécuté avec des parenthèses.

### Les 6 formes de callable

```php
// 1. String → nom d'une fonction
$callable = 'strlen';

// 2. Closure (fonction anonyme)
$callable = function ($name) { return $name; };

// 3. Arrow function (PHP 7.4+)
$callable = fn ($name) => $name;

// 4. Array [objet, méthode] → méthode d'instance
$callable = [$myObject, 'myMethod'];

// 5. Array [classe, méthode] → méthode statique
$callable = ['MyClass', 'myStaticMethod'];

// 6. Objet avec __invoke()
$callable = new MyInvokableClass();
```

Seules les formes 2 et 3 sont des Closures. Toutes les 6 sont des callables.

### Closure vs Arrow function

- Closure : multi-lignes, doit utiliser `use` pour capturer les variables extérieures
- Arrow function : une seule expression, capture automatique sans `use`, return implicite

```php
$message = 'Hello';

// Closure — il faut `use`
$closure = function ($name) use ($message) {
    return sprintf('%s %s', $message, $name);
};

// Arrow — capture automatique
$arrow = fn ($name) => sprintf('%s %s', $message, $name);
```

### `use` par copie vs par référence

```php
$count = 0;

// Copie — l'original ne bouge pas
$fn = function() use ($count) { $count++; };
$fn();
echo $count; // 0

// Référence — même variable
$fn = function() use (&$count) { $count++; };
$fn();
echo $count; // 1
```

Lié directement au COW : `use` sans `&` = copie paresseuse, `use (&$var)` = référence.

### `__invoke()` — le plus important pour Symfony

Quand une classe implémente `__invoke()`, ses instances deviennent callables.

Utilisé partout dans Symfony :
- Controllers invokables (une seule action)
- Message handlers Messenger
- Event listeners avec une seule méthode

### First-class callable syntax (PHP 8.1+)

```php
// Avant
$callable = [$this, 'myMethod'];
$callable = 'strlen';

// Maintenant
$callable = $this->myMethod(...);
$callable = strlen(...);
```

Symfony privilégie cette syntaxe dans les routes, listeners, etc.

### Nouveautés certif Symfony 8 vs Symfony 7

- Messenger est devenu un topic dédié (transports, handlers, workers, retries, middleware, events)
- Enums PHP ajoutés comme sujet
- Attributes remplacent les annotations
- "Namespaces" et "SPL" retirés de la liste officielle
- PHP couvert jusqu'à 8.4

## Namespaces — fallback

- Fonctions et constantes : PHP remonte au namespace global si pas trouvé → fallback ✅
- Classes : PAS de fallback → il faut `use` ou `\` devant → ❌

## Finally — exécution garantie

- `finally` s'exécute TOUJOURS, même si un `return` est dans le `try` ou le `catch`
- PHP prépare le return, exécute le `finally`, puis effectue le return
- Si `finally` contient un `return`, il ÉCRASE celui du `try`/`catch`
