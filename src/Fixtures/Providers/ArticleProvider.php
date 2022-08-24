<?php

namespace App\Fixtures\Providers;

use DateTimeImmutable;
use Faker\Factory;
use Faker\Generator;

class ArticleProvider
{
    public function __construct(
        private Generator $faker
    ) {
        $this->faker = Factory::create('fr_FR');
    }

    public function generateLoremDesc(): string
    {
        return file_get_contents('https://loripsum.net/api/10/long/headers/link/ul/dl');
    }

    public function generateDate(): DateTimeImmutable
    {
        return DateTimeImmutable::createFromMutable($this->faker->dateTimeThisYear());
    }
}
