<?php

namespace App\DataFixtures;

use App\Entity\Site;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $role = [
            ['ROLE_USER'],
            ['ROLE_ADMIN']
        ];

        $teams = [
            'justine',
            'alex',
            'antoine',
            'ghislain'
        ];

        foreach ($teams as $i => $team) {
            $user = new User();

            $user->setPseudo($team);
            $user->setName('nom ' . $team);
            $user->setFirstName('prénom : ' . $team);
            $user->setPassword('$2y$13$ZayE0.fuVPDs9oN0HXzR/.EYcgaP9JehTxqZirZ4GNbEnwQlrVxwC');

            $phoneNumber = $faker->numerify('0#########'); // Génère un numéro avec 10 chiffres
            $user->setPhone($phoneNumber);

            $user->setEmail($team . '@campus-eni.fr');
            $user->setRoles(['ROLE_ADMIN']);
            $user->setIsActive(true);
            $user->setIsVerified(true);

            $site = $this->getReference('site_' . rand(0, 3), Site::class);
            $user->setSite($site);

            $manager->persist($user);
            $this->setReference('user_' . $i, $user);
        }

        for ($i = 4; $i < 15; $i++) {
            $user = new User();

            $user->setPseudo($faker->userName());
            $user->setName($faker->lastName());
            $user->setFirstName($faker->firstName());
            $user->setPassword('$2y$13$ZayE0.fuVPDs9oN0HXzR/.EYcgaP9JehTxqZirZ4GNbEnwQlrVxwC');

            $phoneNumber = $faker->numerify('0#########'); // Génère un numéro avec 10 chiffres
            $user->setPhone($phoneNumber);

            $user->setEmail($faker->email());
            $user->setRoles($role[array_rand($role)]);
            $user->setPhoto($faker->image());
            $user->setIsActive($faker->boolean());
            $user->setIsVerified(true);

            $site = $this->getReference('site_' . rand(0, 3), Site::class);
            $user->setSite($site);

            $manager->persist($user);
            $this->setReference('user_' . $i, $user);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            SiteFixtures::class
        ];
    }
}
