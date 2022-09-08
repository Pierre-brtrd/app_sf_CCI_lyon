<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Exception;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security Controller Test for testing security.
 */
class SecurityControllerTest extends WebTestCase
{
    protected $client;

    protected $databaseTool;

    protected $userRepository;

    protected function setUp(): void
    {
        $this->client = self::createClient();

        $this->userRepository = self::getContainer()->get(UserRepository::class);

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
        $this->databaseTool->loadAliceFixture([
            \dirname(__DIR__).'/Fixtures/UserTestFixtures.yaml',
        ]);
    }

    public function testLoginPageResponse()
    {
        $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
    }

    public function testHeading1LoginPage()
    {
        $this->client->request('GET', '/login');
        $this->assertSelectorTextContains('h1', 'connecter');
    }

    public function testAdminArticleNotConnected()
    {
        $this->client->request('GET', '/admin/article');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    public function testAdminUserNotConnected()
    {
        $this->client->request('GET', '/admin/user');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    public function testAdminArticleBadLoggedIn()
    {
        $user = $this->userRepository->find(3);

        $this->client->loginUser($user);

        $this->client->request('GET', '/admin/article');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminUserBadLoggedIn()
    {
        $user = $this->userRepository->find(3);

        $this->client->loginUser($user);

        $this->client->request('GET', '/admin/user');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminArticleGoodLoggedIn()
    {
        $userAdmin = $this->userRepository->findOneByEmail('pierre@test.com');

        $this->client->loginUser($userAdmin);

        $this->client->request('GET', '/admin/article');

        $this->assertResponseIsSuccessful();
    }

    public function testAdminUserGoodLoggedIn()
    {
        $userAdmin = $this->userRepository->findOneByEmail('pierre@test.com');

        $this->client->loginUser($userAdmin);

        $this->client->request('GET', '/admin/user');

        $this->assertResponseIsSuccessful();
    }

    public function testAdminArticleLoggedInEditorUser()
    {
        $userAdmin = $this->userRepository->findOneByEmail('editor@test.com');

        $this->client->loginUser($userAdmin);

        $this->client->request('GET', '/admin/article');

        $this->assertResponseIsSuccessful();
    }

    public function testAdminCategroieLoggedInEditorUser()
    {
        $userAdmin = $this->userRepository->findOneByEmail('editor@test.com');

        $this->client->loginUser($userAdmin);

        $this->client->request('GET', '/admin/categorie');

        $this->assertResponseIsSuccessful();
    }

    public function testAdminUserLoggedInEditorUserBadCredentials()
    {
        $userAdmin = $this->userRepository->findOneByEmail('editor@test.com');

        $this->client->loginUser($userAdmin);

        $this->client->request('GET', '/admin/user');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testRegisterPageResponse()
    {
        $this->client->request('GET', '/register');
        $this->assertResponseIsSuccessful();
    }

    public function testHeading1ResgiterPage()
    {
        $this->client->request('GET', '/register');
        $this->assertSelectorTextContains('h1', 'inscrire');
    }

    public function testSubmitValideRegisterFormPage()
    {
        $crawler = $this->client->request('GET', '/register');
        $form = $crawler->selectButton('S\'incrire')->form([
            'registration_form[prenom]' => 'John',
            'registration_form[nom]' => 'Doe',
            'registration_form[email]' => 'john@example.com',
            'registration_form[password][first]' => 'Test1234',
            'registration_form[password][second]' => 'Test1234',
            'registration_form[address]' => 'XX rue de test',
            'registration_form[zipCode]' => '75000',
            'registration_form[ville]' => 'Paris',
        ]);

        $this->client->submit($form);

        $newUser = $this->userRepository->findOneByEmail('john@example.com');

        if (!$newUser) {
            throw new Exception('User not created');
        }

        $this->assertResponseRedirects();
    }

    public function testSubmitInvalideEmailTypeRegisterFormPage()
    {
        $crawler = $this->client->request('GET', '/register');
        $form = $crawler->selectButton('S\'incrire')->form([
            'registration_form[prenom]' => 'John',
            'registration_form[nom]' => 'Doe',
            'registration_form[email]' => 'john@example',
            'registration_form[password][first]' => 'Test1234',
            'registration_form[password][second]' => 'Test1234',
            'registration_form[address]' => 'XX rue de test',
            'registration_form[zipCode]' => '75000',
            'registration_form[ville]' => 'Paris',
        ]);

        $this->client->submit($form);
        $this->assertSelectorTextContains('.invalid-feedback', 'Veuillez rentrer un email valide.');
    }

    public function testSubmitNonUniqueEmailRegisterFormPage()
    {
        $crawler = $this->client->request('GET', '/register');
        $form = $crawler->selectButton('S\'incrire')->form([
            'registration_form[prenom]' => 'John',
            'registration_form[nom]' => 'Doe',
            'registration_form[email]' => 'pierre@test.com',
            'registration_form[password][first]' => 'Test1234',
            'registration_form[password][second]' => 'Test1234',
            'registration_form[address]' => 'XX rue de test',
            'registration_form[zipCode]' => '75000',
            'registration_form[ville]' => 'Paris',
        ]);

        $this->client->submit($form);
        $this->assertSelectorTextContains('.invalid-feedback', 'Cet email est déjà utilisé par un autre compte');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->databaseTool);
    }
}
