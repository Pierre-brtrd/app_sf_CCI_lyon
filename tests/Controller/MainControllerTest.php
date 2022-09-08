<?php

namespace App\Tests\Controller;

use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MainControllerTest extends WebTestCase
{
    protected $client;

    protected $databaseTool;

    protected function setUp(): void
    {
        $this->client = self::createClient();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
        $this->databaseTool->loadAliceFixture([
            \dirname(__DIR__).'/Fixtures/UserTestFixtures.yaml',
            \dirname(__DIR__).'/Fixtures/ArticleTestFixtures.yaml',
        ]);
    }

    public function testHomePageResponse()
    {
        $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();
    }

    public function testHomePageHeading1()
    {
        $this->client->request('GET', '/');
        $this->assertSelectorTextContains('h1', 'Bienvenue sur le blog développé en Symfony');
    }

    public function testNumberArticleHomePage()
    {
        $crawler = $this->client->request('GET', '/');

        $this->assertCount(6, $crawler->filter('.blog-card'));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->databaseTool);
    }
}
