<?php declare(strict_types=1);

namespace Pdsinterop\Solid\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HelloWorldController extends AbstractController
{
    final public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {

        $body = <<<'HTML'
<h1 class="title">Hello, World!</h1>
<p>The following main URL are available on this server:</p>
<ul>
  <li>
    <a href="/.well-known/openid-configuration"><code>/.well-known/openid-configuration</code></a>
    <span>Openid</span>
  </li>
  <li>
    <a href="/authorize"><code>/authorize</code></a>
    <span>Authorize</span>
  </li>
  <li>
    <a href="/jwks"><code>/jwks</code></a>
    <span>Jwks</span>
  </li>
  <li>
    <a href="/login/"><code>/login/</code></a><sup>1, 3</sup>
    <span>Login</span>
  </li>
  <li>
    <a href="/profile/"><code>/profile/</code></a><sup>1</sup>
    <span>Profile</span>
  </li>
  <li>
    <a href="/profile/card"><code>/profile/card</code></a><sup>2</sup>
    <span>Card</span>
  </li>
  <li>
    <code>/register</code><sup>4</sup>
    <span>Register</span>
  </li>
  <li>
    <code>/sharing/{clientId}</code><sup>3</sup>
    <span>Approval</span>
  </li>
  <li>
    <a href="/storage/"><code>/storage/{path}</code></a><sup>3</sup>
    <span>Resource</span>
  </li>
  <li>
    <code>/token/</code><sup>1, 4</sup>
    <span>Token</span>
  </li>
</ul>

<section class="is-info is-light is-size-7 notification">
  <h2>Footnotes</h2>
  <ol>
    <li>Also available without trailing slash <code>/</code></li>
    <li>A file extension can be added to force a specific format. For instance <code>/profile/card</code> can be
      requested as <code>/profile/card.ttl</code> to request a Turtle file, or <code>/profile/card.json</code> to
      request a JSON-LD file
    </li>
    <li>This URL also accepts POST requests</li>
    <li>This URL <em>only</em> accepts POST requests</li>
  </ol>
</section>
HTML;

        return $this->createTemplateResponse($body);
    }
}
