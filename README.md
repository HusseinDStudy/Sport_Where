# ⚠ Informations

Toutes les méthodes marche sur POSTMAN avec la nomenclature snake_case par contre sur l'api doc problème de nomenclature (modèle en camelCase à la place de snake_case) sur les méthode POST et PUT. Pour l'api doc la methode POST place manque le idCoach.

# 💪 Sport'Where

API permettant de localiser les lieux sportifs ( salles de sport, crossfit, parcs de street workout, etc ).

# 🖥️ Gérer son environnement

Créer un fichier ".env.local" à la racine du projet et mettre les informations de votre ".env".

# ⬇️ Installation

git clone https://github.com/HusseinDStudy/Sport_Where.git

# 📖 Usages

Ouvrir le terminal et faire "composer install"

Pousser la base de données en local avec de fausses données avec appFixtures.

- php bin/console d:d:c -> ( doctrine:database:create ) et créer la base de données
- php bin/cponsole d:f:l -> ( doctrine:fixtures:load ) afin de pousser les fausses données

Puis démarrer le server Symfony avec "symfony serve"

⚠Rappel: La nomenclature des retours doit aussi etre en snake_case

## Url de documentation

http://127.0.0.1:8000/api/doc

## Les Comptes de Connexions

Compte Admin [Real]:

```json
{
    "username": "admin",
    "password": "password"
}
```

Compte User [Format]:

```json
{
    "username": "xxxx",
    "password": "userName@xxxx"
}
```

## La Bonne Nomenclature pour les BodyRequest:

#### Post/Put Coach

```json
{
    "coach_phone_number": "02 20 12 48 12",
    "coach_full_name": "Maurice Jean"
}
```

#### Post/Put Place

```json
{
    "place_name":"bhahaha",
    "place_address":"testaddress",
    "place_city":"testcity",
    "place_type":"testplacetype",
    "place_rate": 4,
    "idCoach": 30,
    "dept": 69
}
```

# 💻 Groupe de travail

Noel Thomas -> JLWear

Tardy Guilhem -> GuilhemTrd

Dajani Hussein -> HusseinDStudy
