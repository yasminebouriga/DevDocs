<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Tag;
use App\Entity\Tutorial;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private SluggerInterface $slugger
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // 1. Users
        $users = [];
        $admin = new User();
        $admin->setEmail('admin@devdocs.tn')
            ->setPseudonyme('AdminDev')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->hasher->hashPassword($admin, 'password123'));
        $manager->persist($admin);
        $users[] = $admin;

        for ($i = 1; $i <= 3; $i++) {
            $user = new User();
            $user->setEmail("dev$i@devdocs.tn")
                ->setPseudonyme($faker->userName())
                ->setPassword($this->hasher->hashPassword($user, 'password123'));
            $manager->persist($user);
            $users[] = $user;
        }

        // 2. 5 Categories
        $catNames = ['PHP & Symfony', 'Frontend & CSS', 'DevOps & Docker', 'Base de données', 'Sécurité Web'];
        $categories = [];
        foreach ($catNames as $name) {
            $cat = new Category();
            $cat->setName($name)->setDescription($faker->sentence(10));
            $manager->persist($cat);
            $categories[] = $cat;
        }

        // 3. 10 Tags
        $tagNames = ['Débutant', 'Avancé', 'Performance', 'Sécurité', 'API', 'Docker', 'Twig', 'ORM', 'Git', 'Architecture'];
        $tags = [];
        foreach ($tagNames as $name) {
            $tag = new Tag();
            $tag->setName($name);
            $manager->persist($tag);
            $tags[] = $tag;
        }

        // 4. 15 Tutorials
        $tutorials = [];
        for ($i = 1; $i <= 15; $i++) {
            $tuto = new Tutorial();
            $title = $faker->sentence(6);
            $tuto->setTitle($title)
                ->setSlug(strtolower($this->slugger->slug($title)) . '-' . $i)
                ->setContent(implode("\n\n", $faker->paragraphs(4)))
                ->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-30 days', 'now')))
                ->setAuthor($faker->randomElement($users))
                ->setCategory($faker->randomElement($categories));

            foreach ($faker->randomElements($tags, rand(2, 4)) as $randomTag) {
                $tuto->addTag($randomTag);
            }
            $manager->persist($tuto);
            $tutorials[] = $tuto;
        }

        // 5. 35 Comments
        for ($i = 0; $i < 35; $i++) {
            $comment = new Comment();
            $comment->setContent($faker->paragraph(2))
                ->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-15 days', 'now')))
                ->setAuthor($faker->randomElement($users))
                ->setTutorial($faker->randomElement($tutorials));
            $manager->persist($comment);
        }

        $manager->flush();
    }
}
