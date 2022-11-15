<?php

namespace App\DataFixtures;

use App\Entity\Coach;
use App\Entity\Place;
use App\Entity\RatePlaces;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    /**
     * @var Generator
     */

    private Generator $faker;

/**
 * class pour hasheant le mdp
 * @var UserPasswordHasherInterface
 */
private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->faker = Factory::create("fr_FR");
        $this->userPasswordHasher = $userPasswordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        $userNumber = 10;

        //Authneticated Users
        for($i=0; $i < $userNumber; $i++){
            $userUser = new User();
            $password = $this->faker->password(2, 6);
            $userUser->setUsername($this->faker->userName() . '@' . $password)
            ->setRoles(["ROLE_USER"])
            ->setPassword($this->userPasswordHasher->hashPassword($userUser, $password));
            $manager->persist($userUser);
        }

        //Authneticated Users
        $adminUser = new User();
        $password = "password";
        $adminUser->setUsername('admin')
        ->setRoles(["ROLE_ADMIN"])
        ->setPassword($this->userPasswordHasher->hashPassword($adminUser, $password));
        $manager->persist($adminUser);
    

        // $product = new Product();
        // $manager->persist($product);

        for ($i=0; $i < 20 ; $i++) {
            $place = New Place();

            $place->setPlaceName($this->faker->randomElements(["BasicFit","FitnessPark"])[0]);
            $place->setPlaceAddress($this->faker->streetAddress());
            $place->setPlaceCity($this->faker->randomElements(["Lyon","Paris","Marseille","Bordeaux","Brest","Strasbourg","Dijon","Nice","Nantes","Clermont-Ferrand","Toulon","Toulouse"])[0]);
            $place->setPlaceType($this->faker->randomElements(["Salle de musculation","Parc de street workout","Salle de crossfit"])[0]);
            //$place->setPlaceRate($this->faker->numberBetween(0, 5 ));
            $place->setStatus("ON");
            $place->setDept($this->faker->numberBetween(1, 95 ));

            $coach = New Coach();

            $coach->setCoachPhoneNumber($this->faker->PhoneNumber());
            $coach->setCoachFullName($this->faker->Name());
            $coach->setStatus("ON");

            $manager->persist($coach);
            $manager->flush();

            $place->setCoach($coach);

            $manager->persist($place);
            $manager->flush();

            for ($j=0; $j < 3 ; $j++) {
                $ratePlaces = New RatePlaces();

                $ratePlaces->setIdPlace($place);
                $ratePlaces->setIdUser($userUser);
                $ratePlaces->setRate($this->faker->numberBetween(0, 5 ));
    
                $manager->persist($ratePlaces);
                $manager->flush();
            }
            
        }
    }
}
