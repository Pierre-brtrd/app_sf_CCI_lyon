<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Utils\AssertTestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends KernelTestCase
{
    use AssertTestTrait;

    /**
     * @var AbstractDatabaseTool
     */
    protected $databaseTool;

    protected function setUp(): void
    {
        parent::setUp();

        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    public function testRepositoryCount()
    {
        $users = $this->databaseTool->loadAliceFixture([
            \dirname(__DIR__).'/Fixtures/UserTestFixtures.yaml',
        ]);

        $users = self::getContainer()->get(UserRepository::class)->count([]);

        $this->assertSame(7, $users);
    }

    public function getUser()
    {
        return (new User())
            ->setEmail('test@example.com')
            ->setNom('test')
            ->setPrenom('test')
            ->setAddress('xx rue de test')
            ->setZipCode('75000')
            ->setVille('Paris')
            ->setRoles(['ROLE_EDITOR'])
            ->setPassword('Test1234');
    }

    public function testValideUserEntity()
    {
        $this->assertHasErrors($this->getUser());
    }

    public function testNonUniqueEmailUser()
    {
        $user = $this->getUser()
            ->setEmail('editor@test.com');

        $this->assertHasErrors($user, 1);
    }

    public function testInvalideEmailUser()
    {
        $user = $this->getUser()
            ->setEmail('editor');

        $this->assertHasErrors($user, 1);
    }

    public function testInvalidePasswordUser()
    {
        $user = $this->getUser()
            ->setPassword('erfa');

        $this->assertHasErrors($user, 1);
    }

    public function testMaxLengthPrenomUser()
    {
        $user = $this->getUser()
            ->setPrenom('erfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfa');

        $this->assertHasErrors($user, 1);
    }

    public function testMaxLengthNomUser()
    {
        $user = $this->getUser()
            ->setNom('erfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfa');

        $this->assertHasErrors($user, 1);
    }

    public function testMaxLengthAddressUser()
    {
        $user = $this->getUser()
            ->setAddress('erfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfa');

        $this->assertHasErrors($user, 1);
    }

    public function testMaxLengthVilleUser()
    {
        $user = $this->getUser()
            ->setVille('erfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfaerfa');

        $this->assertHasErrors($user, 1);
    }

    public function testInvalideZipCodeTextUser()
    {
        $user = $this->getUser()
            ->setZipCode('hgjdsfjkghzf');

        $this->assertHasErrors($user, 1);
    }

    public function testInvalideZipCodeMinNumberUser()
    {
        $user = $this->getUser()
            ->setZipCode('1234');

        $this->assertHasErrors($user, 1);
    }

    public function testInvalideZipCodeMaxNumberUser()
    {
        $user = $this->getUser()
            ->setZipCode('123412365');

        $this->assertHasErrors($user, 1);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->databaseTool);
    }
}
