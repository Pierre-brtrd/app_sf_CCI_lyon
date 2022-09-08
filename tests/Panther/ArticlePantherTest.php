<?php

namespace App\Tests\Panther;

use Facebook\WebDriver\WebDriverBy;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Component\Panther\PantherTestCase;

class ArticlePantherTest extends PantherTestCase
{
    protected $client;

    protected $databaseTool;

    protected function setUp(): void
    {
        $this->client = self::createPantherClient();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
        $this->databaseTool->loadAliceFixture([
            \dirname(__DIR__) . '/Fixtures/UserTestFixtures.yaml',
            \dirname(__DIR__) . '/Fixtures/TagTestFixtures.yaml',
            \dirname(__DIR__) . '/Fixtures/ArticleTestFixtures.yaml',
        ]);
    }

    public function testArticleNumberWithoutInterractionsPage()
    {
        $crawler = $this->client->request('GET', '/article/liste');

        $this->assertCount(6, $crawler->filter('.blog-list .blog-card'));
    }

    public function testArticleShowMoreButton()
    {
        $crawler = $this->client->request('GET', '/article/liste');

        $this->client->waitFor('.btn-show-more', 2);

        $this->client->executeScript("document.querySelector('.btn-show-more').click()");

        $this->client->waitForEnabled('.btn-show-more', 2);

        $crawler = $this->client->refreshCrawler();

        $this->assertCount(12, $crawler->filter('.blog-list .blog-card'));
    }

    public function testArticleSearchAjaxTitle()
    {
        $crawler = $this->client->request('GET', '/article/liste');

        $this->client->waitFor('.form-filter', 2);

        $search = $this->client->findElement(WebDriverBy::cssSelector('#query'));
        $search->sendKeys('Titre-3');

        $this->client->waitFor('.content-response', 4);

        // For flip content delay
        sleep(1);

        $crawler = $this->client->refreshCrawler();

        $this->assertCount(1, $crawler->filter('.blog-list .blog-card'));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->databaseTool);
    }
}
