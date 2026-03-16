<?php declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Classes\Managers\SessionManager;

class SessionManagerTest extends TestCase
{
  protected SessionManager $manager;

  public function setUp(): void
  {
    $this->manager = new SessionManager();
  }

  // -------------------------------------------------------------------------
  // authenticate()
  // -------------------------------------------------------------------------

  public function testAuthenticate_returnsArray(): void
  {
    $result = $this->manager->authenticate('user@example.com', 'secret');
    $this->assertIsArray($result);
  }

  public function testAuthenticate_hasRequiredKeys(): void
  {
    $result = $this->manager->authenticate('user@example.com', 'secret');
    $this->assertArrayHasKey('uuid',        $result);
    $this->assertArrayHasKey('email',       $result);
    $this->assertArrayHasKey('roles',       $result);
    $this->assertArrayHasKey('permissions', $result);
  }

  public function testAuthenticate_uuidIsNonEmptyString(): void
  {
    $result = $this->manager->authenticate('testuser', 'pass');
    $this->assertIsString($result['uuid']);
    $this->assertNotEmpty($result['uuid']);
  }

  public function testAuthenticate_rolesIsArray(): void
  {
    $result = $this->manager->authenticate('testuser', 'pass');
    $this->assertIsArray($result['roles']);
  }

  public function testAuthenticate_permissionsIsArray(): void
  {
    $result = $this->manager->authenticate('testuser', 'pass');
    $this->assertIsArray($result['permissions']);
  }

  // -------------------------------------------------------------------------
  // fetchSession()
  // -------------------------------------------------------------------------

  public function testFetchSession_returnsArray(): void
  {
    $result = $this->manager->fetchSession('test-session-id');
    $this->assertIsArray($result);
  }

  public function testFetchSession_hasRequiredKeys(): void
  {
    $result = $this->manager->fetchSession('test-session-id');
    $this->assertArrayHasKey('sessionid',   $result);
    $this->assertArrayHasKey('csrf_token',  $result);
    $this->assertArrayHasKey('expires_dt',  $result);
  }

  public function testFetchSession_sessionIdIsNonEmptyString(): void
  {
    $result = $this->manager->fetchSession('test-session-id');
    $this->assertIsString($result['sessionid']);
    $this->assertNotEmpty($result['sessionid']);
  }

  public function testFetchSession_expiresDateIsInFuture(): void
  {
    $result    = $this->manager->fetchSession('test-session-id');
    $expiresAt = new \DateTime($result['expires_dt']);
    $now       = new \DateTime('now', new \DateTimeZone('UTC'));
    $this->assertGreaterThan($now, $expiresAt);
  }

  // -------------------------------------------------------------------------
  // createSession()
  // -------------------------------------------------------------------------

  public function testCreateSession_returnsArray(): void
  {
    $result = $this->manager->createSession(['account_uuid' => 'abc-123']);
    $this->assertIsArray($result);
  }

  public function testCreateSession_hasRequiredKeys(): void
  {
    $result = $this->manager->createSession(['account_uuid' => 'abc-123']);
    $this->assertArrayHasKey('account_uuid', $result);
    $this->assertArrayHasKey('sessionid',    $result);
    $this->assertArrayHasKey('csrf_token',   $result);
    $this->assertArrayHasKey('expires_dt',   $result);
  }

  public function testCreateSession_propagatesAccountUuid(): void
  {
    $result = $this->manager->createSession(['account_uuid' => 'my-uuid-value']);
    $this->assertSame('my-uuid-value', $result['account_uuid']);
  }

  public function testCreateSession_sessionIdIsNonEmptyString(): void
  {
    $result = $this->manager->createSession();
    $this->assertIsString($result['sessionid']);
    $this->assertNotEmpty($result['sessionid']);
  }

  public function testCreateSession_csrfTokenIsNonEmptyString(): void
  {
    $result = $this->manager->createSession();
    $this->assertIsString($result['csrf_token']);
    $this->assertNotEmpty($result['csrf_token']);
  }

  public function testCreateSession_expiresDateIsInFuture(): void
  {
    $result    = $this->manager->createSession();
    $expiresAt = new \DateTime($result['expires_dt']);
    $now       = new \DateTime('now', new \DateTimeZone('UTC'));
    $this->assertGreaterThan($now, $expiresAt);
  }

  public function testCreateSession_generatesUniqueSessionIds(): void
  {
    $a = $this->manager->createSession();
    $b = $this->manager->createSession();
    $this->assertNotSame($a['sessionid'], $b['sessionid']);
  }

  public function testCreateSession_generatesUniqueCsrfTokens(): void
  {
    $a = $this->manager->createSession();
    $b = $this->manager->createSession();
    $this->assertNotSame($a['csrf_token'], $b['csrf_token']);
  }

  // -------------------------------------------------------------------------
  // updateSession()
  // -------------------------------------------------------------------------

  public function testUpdateSession_returnsUnchangedSession(): void
  {
    $session = ['sessionid' => 'sid', 'csrf_token' => 'tok', 'expires_dt' => '+1h'];
    $result  = $this->manager->updateSession($session);
    $this->assertSame($session, $result);
  }

  // -------------------------------------------------------------------------
  // deleteSession()
  // -------------------------------------------------------------------------

  public function testDeleteSession_returnsUnchangedSession(): void
  {
    $session = ['sessionid' => 'sid', 'csrf_token' => 'tok', 'expires_dt' => '+1h'];
    $result  = $this->manager->deleteSession($session);
    $this->assertSame($session, $result);
  }
}
