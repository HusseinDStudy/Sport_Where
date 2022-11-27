# ⚠ Informations

- Amuser vous

# 💪 Sport'Where

API permettant de localiser les lieux sportifs ( salles de sport, crossfit, parcs de street workout, etc ).

# 🖥️ Gérer son environnement

Créer un fichier ".env.local" à la racine du projet et mettre les informations de votre ".env".

# ⬇️ Installation

git clone https://github.com/HusseinDStudy/Sport_Where.git

# 📖 Usages

Ouvrir le terminal et faire ```composer install```

Crée ses clés privés et publics.

- ```php bin/console lexik:jwt:generate-keypair```

Pousser la base de données en local avec de fausses données avec appFixtures.

- Créer la base de données:
  - ```php bin/console d:d:c``` -> ( doctrine:database:create )
- Mettre à jour le schema de la base de données
  - ```php bin/console d:s:u --force```
- Afin de pousser les fausses données
  - ```php bin/cponsole d:f:l``` -> ( doctrine:fixtures:load )

Puis démarrer le server Symfony avec "symfony serve"

## Url de documentation

```http://127.0.0.1:8000/api/doc```

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

# 💻 Groupe de travail

Dajani Hussein -> HusseinDStudy

Noel Thomas -> JLWear

Tardy Guilhem -> GuilhemTrd
