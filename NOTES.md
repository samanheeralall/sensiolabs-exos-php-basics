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
