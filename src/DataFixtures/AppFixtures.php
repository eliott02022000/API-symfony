<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        $faker = Faker\Factory::create();

        // on créé 30 personnes
        for ($i = 0; $i < 30; $i++) {
            $user = new User();
            $user->setFirstname($faker->firstname);
            $user->setLastname($faker->lastname);
            $user->setEmail($faker->email);
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($faker->password);

            $manager->persist($user);
            $manager->flush();
        }

    }
}
