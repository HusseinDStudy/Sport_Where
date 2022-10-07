<?php

namespace App\DataFixtures;

use App\Entity\Place;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Faker\Factory;

class AppFixtures extends Fixture
{
    /**
     * @var Generator
     */

    private Generator $faker;
    public function __construct()
    {
        $this->faker = Factory::create("fr_FR");
    }
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        for ($i=0; $i < 20 ; $i++) {
            $place = New Place();

            $place->setPlaceName($this->faker->randomElements(["BasicFit","FitnessPark"])[0]);
            $place->setPlaceAddress($this->faker->streetAddress());
            $place->setPlaceCity($this->faker->randomElements(["Lyon","Paris","Marseille","Bordeaux","Brest","Strasbourg","Dijon","Nice","Nantes","Clermont-Ferrand","Toulon","Toulouse"])[0]);
            $place->setPlaceType($this->faker->randomElements(["Salle de musculation","Parc de street workout","Salle de crossfit"])[0]);
            $place->setPlaceRate($this->faker->numberBetween(0, 5 ));
            $place->setStatus("ON");

            $manager->persist($place);
            $manager->flush();
        }
    }
}
