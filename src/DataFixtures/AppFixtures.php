<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Singer;
use App\Entity\Disc;
use App\Entity\Song;
use App\Entity\User;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;
    private $faker;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->faker = Factory::create('fr_FR');
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('user@songapi.com');
        $user->setRoles(["ROLE_USER"]);

        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
        $manager->persist($user);

        $userAdmin = new User();
        $userAdmin->setEmail('admin@songapi.com');
        $userAdmin->setRoles(["ROLE_ADMIN"]);

        $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, 'password'));
        $manager->persist($userAdmin);

        for ($i = 0; $i < 10; $i++) {
            $singer = new Singer;
            $gender = $this->faker->randomElement(['male', 'female']);
            $singer->setFullName($this->faker->name($gender));
            $manager->persist($singer);
            $listSinger[] = $singer;
        }

        for ($i = 0; $i < 10; $i++) {
            $disc = new Disc;
            $randWords = rand(1, 6);
            $disc->setDiscName($this->faker->sentence($randWords));
            $manager->persist($disc);
            $listDisc[] = $disc;
        }

        for ($i = 0; $i < 25; $i++) {
            $song = new Song;
            $randWords = rand(1, 6);
            $song->setTitle($this->faker->sentence($randWords));
            $randGenre = ['pop', 'rock', 'classical', 'hip-hop', 'jazz', 'country'];
            $song->setGenre($randGenre[array_rand($randGenre)]);
            $duration = $this->faker->numberBetween(1, 360);
            if ($duration >= 60) {
                $minutes = floor($duration / 60);
                $seconds = $duration % 60;
                $duration = $minutes . 'm' . $seconds . 's';
            } else {
                $duration = $duration . 's';
            }
            $song->setDuration($duration);
            $song->setPublishedYear($this->faker->dateTimeBetween("-54 years", "now")->format('Y'));
            $song->setSinger($listSinger[array_rand($listSinger)]);
            $song->setDisc($listDisc[array_rand($listDisc)]);
            $manager->persist($song);
        }

        $manager->flush();
    }
}
