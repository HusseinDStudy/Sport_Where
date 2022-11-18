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


# 💻 Groupe de travail
Noel Thomas -> JLwear 

Tardy Guilhem -> GuilhemTrd 

Dajani Hussein -> HusseinDStudy 



Urgence le commit Hateoas for all casse les methodes POST PUT DELETE  pour COACH ET PLACE (post place)
