<?php declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;

class GlobalsTest extends TestCase
{
  // -------------------------------------------------------------------------
  // isWin()
  // -------------------------------------------------------------------------

  public function testIsWin_returnsBool(): void
  {
    $this->assertIsBool(isWin());
  }

  public function testIsWin_matchesPhpOs(): void
  {
    $expected = strncasecmp(PHP_OS, 'WIN', 3) === 0;
    $this->assertSame($expected, isWin());
  }

  // -------------------------------------------------------------------------
  // array_change_key_case_recursive()
  // -------------------------------------------------------------------------

  public function testArrayChangeKeyCaseRecursive_lowercasesTopLevel(): void
  {
    $input  = ['FOO' => 1, 'BAR' => 2];
    $result = array_change_key_case_recursive($input, CASE_LOWER);
    $this->assertArrayHasKey('foo', $result);
    $this->assertArrayHasKey('bar', $result);
  }

  public function testArrayChangeKeyCaseRecursive_lowercasesNested(): void
  {
    $input  = ['OUTER' => ['INNER' => 'value']];
    $result = array_change_key_case_recursive($input, CASE_LOWER);
    $this->assertArrayHasKey('outer', $result);
    $this->assertArrayHasKey('inner', $result['outer']);
    $this->assertSame('value', $result['outer']['inner']);
  }

  public function testArrayChangeKeyCaseRecursive_uppercaseOption(): void
  {
    $input  = ['foo' => ['bar' => 1]];
    $result = array_change_key_case_recursive($input, CASE_UPPER);
    $this->assertArrayHasKey('FOO', $result);
    $this->assertArrayHasKey('BAR', $result['FOO']);
  }

  public function testArrayChangeKeyCaseRecursive_emptyArray(): void
  {
    $this->assertSame([], array_change_key_case_recursive([]));
  }

  public function testArrayChangeKeyCaseRecursive_preservesValues(): void
  {
    $input  = ['Key' => 'Some Value'];
    $result = array_change_key_case_recursive($input);
    $this->assertSame('Some Value', $result['key']);
  }

  // -------------------------------------------------------------------------
  // http_build_url()
  // -------------------------------------------------------------------------

  public function testHttpBuildUrl_roundTripsFullUrl(): void
  {
    $url    = 'https://user:pass@example.com:8080/path/to/page?foo=bar#section';
    $result = http_build_url($url);
    $this->assertStringContainsString('example.com', $result);
    $this->assertStringContainsString('/path/to/page', $result);
  }

  public function testHttpBuildUrl_replacesHost(): void
  {
    $url    = 'https://old.example.com/path';
    $result = http_build_url($url, ['host' => 'new.example.com']);
    $this->assertStringContainsString('new.example.com', $result);
    $this->assertStringNotContainsString('old.example.com', $result);
  }

  public function testHttpBuildUrl_joinsQueryString(): void
  {
    $url    = 'https://example.com/path?a=1';
    $result = http_build_url($url, ['query' => 'b=2'], HTTP_URL_JOIN_QUERY);
    $this->assertStringContainsString('a=1', $result);
    $this->assertStringContainsString('b=2', $result);
  }

  public function testHttpBuildUrl_stripsPort(): void
  {
    $url    = 'https://example.com:9000/path';
    $result = http_build_url($url, [], HTTP_URL_STRIP_PORT);
    $this->assertStringNotContainsString(':9000', $result);
  }

  public function testHttpBuildUrl_acceptsArrayInput(): void
  {
    $parts  = ['scheme' => 'https', 'host' => 'example.com', 'path' => '/api'];
    $result = http_build_url($parts);
    $this->assertSame('https://example.com/api', $result);
  }

  // -------------------------------------------------------------------------
  // str_pattern_match()
  // -------------------------------------------------------------------------

  public function testStrPatternMatch_plusPrefixMatches(): void
  {
    $this->assertTrue(str_pattern_match('hello world', '+hello'));
  }

  public function testStrPatternMatch_plusPrefixNoMatch(): void
  {
    $this->assertFalse(str_pattern_match('hello world', '+missing'));
  }

  public function testStrPatternMatch_noPrefixTreatedAsPlus(): void
  {
    $this->assertTrue(str_pattern_match('hello world', 'hello'));
  }

  public function testStrPatternMatch_minusPrefixExcludes(): void
  {
    $this->assertTrue(str_pattern_match('hello world', '-missing'));
  }

  public function testStrPatternMatch_minusPrefixFailsOnMatch(): void
  {
    $this->assertFalse(str_pattern_match('hello world', '-hello'));
  }

  public function testStrPatternMatch_combinedPatterns(): void
  {
    $this->assertTrue(str_pattern_match('hello world', '+hello -missing'));
    $this->assertFalse(str_pattern_match('hello world', '+hello -world'));
  }

  public function testStrPatternMatch_emptyHaystackReturnsFalse(): void
  {
    $this->assertFalse(str_pattern_match('', '+anything'));
  }

  public function testStrPatternMatch_emptyPatternReturnsFalse(): void
  {
    $this->assertFalse(str_pattern_match('hello', ''));
  }

  public function testStrPatternMatch_nullHaystackReturnsFalse(): void
  {
    $this->assertFalse(str_pattern_match(null, '+anything'));
  }

  // -------------------------------------------------------------------------
  // responseJsonError()
  // -------------------------------------------------------------------------

  public function testResponseJsonError_returnsResponse(): void
  {
    $result = responseJsonError('Bad Request', 'Something went wrong', 400, 'ERR_001');
    $this->assertInstanceOf(Response::class, $result);
  }

  public function testResponseJsonError_setsStatusCode(): void
  {
    $result = responseJsonError('Not Found', 'Resource missing', 404);
    $this->assertSame(404, $result->getStatusCode());
  }

  public function testResponseJsonError_bodyContainsErrorResult(): void
  {
    $result = responseJsonError('Bad Request', 'Invalid input', 400, 42);
    $body   = json_decode((string) $result->getBody(), true);

    $this->assertSame('ERROR',         $body['result']);
    $this->assertSame('Bad Request',   $body['title']);
    $this->assertSame('Invalid input', $body['message']);
    $this->assertSame(42,              $body['code']);
  }

  public function testResponseJsonError_contentTypeIsJson(): void
  {
    $result = responseJsonError('Error', 'msg', 500);
    $this->assertSame('application/json', $result->getHeaderLine('Content-Type'));
  }

  public function testResponseJsonError_defaultHttpCodeIs400(): void
  {
    $result = responseJsonError('Error', 'msg');
    $this->assertSame(400, $result->getStatusCode());
  }
}
